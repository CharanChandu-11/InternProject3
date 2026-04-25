<?php
// app/Http/Controllers/Api/Student/LibraryController.php

namespace App\Http\Controllers\Api\Student;

use App\Http\Controllers\Api\BaseController;
use App\Models\Book;
use App\Models\BookIssue;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class LibraryController extends BaseController
{
    /**
     * Display list of books with search and filters (paginated)
     */
    public function books(Request $request)
    {
        $query = Book::query();

        // Search
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                  ->orWhere('author', 'like', "%{$search}%")
                  ->orWhere('isbn', 'like', "%{$search}%")
                  ->orWhere('publisher', 'like', "%{$search}%");
            });
        }

        // Filter by category
        if ($request->filled('category')) {
            $query->where('category', $request->category);
        }

        // Filter by availability
        if ($request->filled('availability')) {
            if ($request->availability == 'available') {
                $query->where('available_quantity', '>', 0);
            } elseif ($request->availability == 'unavailable') {
                $query->where('available_quantity', 0);
            }
        }

        $books = $query->orderBy('title')->paginate($request->per_page ?? 12);

        // Get unique categories for filter
        $categories = Book::select('category')
            ->distinct()
            ->whereNotNull('category')
            ->pluck('category');

        return $this->sendResponse([
            'books' => $books->items(),
            'categories' => $categories,
            'pagination' => [
                'current_page' => $books->currentPage(),
                'last_page' => $books->lastPage(),
                'per_page' => $books->perPage(),
                'total' => $books->total(),
            ],
        ], 'Books retrieved successfully');
    }

    /**
     * Display single book details
     */
    public function show(Book $book)
    {
        $book->load(['issues' => function($q) {
            $q->where('status', 'issued')->with('issuable.user');
        }]);

        $currentIssue = $book->issues->first();
        $student = Auth::user()->student;

        $alreadyIssued = BookIssue::where('book_id', $book->id)
            ->where('issuable_type', 'App\Models\Student')
            ->where('issuable_id', $student->id)
            ->whereIn('status', ['issued', 'overdue'])
            ->exists();

        $relatedBooks = Book::where('category', $book->category)
            ->where('id', '!=', $book->id)
            ->take(4)
            ->get();

        return $this->sendResponse([
            'book' => [
                'id' => $book->id,
                'title' => $book->title,
                'isbn' => $book->isbn,
                'author' => $book->author,
                'publisher' => $book->publisher,
                'publication_year' => $book->publication_year,
                'category' => $book->category,
                'description' => $book->description,
                'shelf_location' => $book->shelf_location,
                'quantity' => $book->quantity,
                'available_quantity' => $book->available_quantity,
                'is_available' => $book->available_quantity > 0,
            ],
            'current_issue' => $currentIssue ? [
                'student_name' => $currentIssue->issuable->user->name,
                'issue_date' => $currentIssue->issue_date->toDateString(),
                'due_date' => $currentIssue->due_date?->toDateString(),
            ] : null,
            'already_issued' => $alreadyIssued,
            'related_books' => $relatedBooks->map(fn($b) => [
                'id' => $b->id,
                'title' => $b->title,
                'author' => $b->author,
                'available' => $b->available_quantity > 0,
            ]),
        ], 'Book details retrieved');
    }

    /**
     * Display books issued to the student
     */
    public function issued(Request $request)
    {
        $student = Auth::user()->student;

        $query = BookIssue::where('issuable_type', 'App\Models\Student')
            ->where('issuable_id', $student->id)
            ->with(['book']);

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $issuedBooks = $query->orderBy('issue_date', 'desc')->paginate($request->per_page ?? 10);

        $stats = [
            'total_issued' => BookIssue::where('issuable_type', 'App\Models\Student')
                ->where('issuable_id', $student->id)
                ->count(),
            'currently_issued' => BookIssue::where('issuable_type', 'App\Models\Student')
                ->where('issuable_id', $student->id)
                ->whereIn('status', ['issued', 'overdue'])
                ->count(),
            'overdue' => BookIssue::where('issuable_type', 'App\Models\Student')
                ->where('issuable_id', $student->id)
                ->where('status', 'overdue')
                ->count(),
            'returned' => BookIssue::where('issuable_type', 'App\Models\Student')
                ->where('issuable_id', $student->id)
                ->where('status', 'returned')
                ->count(),
        ];

        return $this->sendResponse([
            'issued_books' => $issuedBooks->items(),
            'statistics' => $stats,
            'pagination' => [
                'current_page' => $issuedBooks->currentPage(),
                'last_page' => $issuedBooks->lastPage(),
                'per_page' => $issuedBooks->perPage(),
                'total' => $issuedBooks->total(),
            ],
        ], 'Issued books retrieved');
    }

    /**
     * Get book categories with counts for filter
     */
    public function getCategories()
    {
        $categories = Book::select('category', DB::raw('count(*) as total'))
            ->groupBy('category')
            ->orderBy('category')
            ->get();

        return $this->sendResponse($categories, 'Categories retrieved');
    }

    /**
     * Get featured books (available, limited to 6)
     */
    public function featured()
    {
        $featuredBooks = Book::where('available_quantity', '>', 0)
            ->orderBy('title')
            ->take(6)
            ->get()
            ->map(fn($book) => [
                'id' => $book->id,
                'title' => $book->title,
                'author' => $book->author,
                'available' => true,
            ]);

        return $this->sendResponse($featuredBooks, 'Featured books retrieved');
    }

    /**
     * Request to issue a book (creates a pending request)
     */
    public function requestIssue(Book $book)
    {
        $student = Auth::user()->student;

        if ($book->available_quantity <= 0) {
            return $this->sendError('This book is currently not available', [], 422);
        }

        $alreadyIssued = BookIssue::where('book_id', $book->id)
            ->where('issuable_type', 'App\Models\Student')
            ->where('issuable_id', $student->id)
            ->whereIn('status', ['issued', 'overdue'])
            ->exists();

        if ($alreadyIssued) {
            return $this->sendError('You have already issued this book. Please return it first.', [], 422);
        }

        // Create a request record (status = 'requested')
        // You can implement an approval workflow. For now, we create a pending request.
        $request = BookIssue::create([
            'book_id' => $book->id,
            'issuable_type' => 'App\Models\Student',
            'issuable_id' => $student->id,
            'issue_date' => null,
            'due_date' => null,
            'status' => 'requested', // Needs librarian approval
            'remarks' => 'Requested by student via API',
        ]);

        // Optionally notify librarian
        // Notification::send(...)

        return $this->sendResponse([
            'request_id' => $request->id,
            'message' => 'Book request submitted successfully. Please wait for librarian approval.',
        ], 'Request submitted');
    }
}