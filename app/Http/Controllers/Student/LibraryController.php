<?php
// app/Http/Controllers/Student/LibraryController.php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\Book;
use App\Models\BookIssue;
use App\Models\Student;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class LibraryController extends Controller
{
    /**
     * Display list of books with search and filters
     */
    public function books(Request $request)
    {
        $query = Book::query();
        
        // Search by title, author, ISBN, or publisher
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
        
        $books = $query->orderBy('title')
            ->paginate(12)
            ->appends($request->query());
        
        // Get unique categories for filter
        $categories = Book::select('category')
            ->distinct()
            ->whereNotNull('category')
            ->pluck('category');
        
        return view('student.library.books', compact('books', 'categories'));
    }
    
    /**
     * Display single book details
     */
    public function show(Book $book)
    {
        $book->load(['issues' => function($q) {
            $q->where('status', 'issued')
              ->with('issuable.user');
        }]);
        
        // Get currently issued to
        $currentIssue = $book->issues->first();
        
        // Get related books (same category)
        $relatedBooks = Book::where('category', $book->category)
            ->where('id', '!=', $book->id)
            ->take(4)
            ->get();
        
        // Check if student has already issued this book
        $student = Auth::user()->student;
        $alreadyIssued = BookIssue::where('book_id', $book->id)
            ->where('issuable_type', 'App\Models\Student')
            ->where('issuable_id', $student->id)
            ->whereIn('status', ['issued', 'overdue'])
            ->exists();
        
        return view('student.library.show', compact('book', 'relatedBooks', 'currentIssue', 'alreadyIssued'));
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
        
        // Filter by status
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        
        $issuedBooks = $query->orderBy('issue_date', 'desc')
            ->paginate(10)
            ->appends($request->query());
        
        // Statistics
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
        
        return view('student.library.issued', compact('issuedBooks', 'stats'));
    }
    
    /**
     * Get book categories for AJAX filter
     */
    public function getCategories()
    {
        $categories = Book::select('category', DB::raw('count(*) as total'))
            ->groupBy('category')
            ->orderBy('category')
            ->get();
        
        return response()->json($categories);
    }
    
    /**
     * Get featured books for dashboard
     */
    public function featured()
    {
        $featuredBooks = Book::where('available_quantity', '>', 0)
            ->orderBy('title')
            ->take(6)
            ->get();
        
        return view('student.library.featured', compact('featuredBooks'));
    }

    /**
     * Request to issue a book
     */
    public function requestIssue(Request $request, Book $book)
    {
        $student = Auth::user()->student;
        
        // Check if book is available
        if ($book->available_quantity <= 0) {
            return redirect()->back()->with('error', 'This book is currently not available.');
        }
        
        // Check if student already has this book issued
        $alreadyIssued = BookIssue::where('book_id', $book->id)
            ->where('issuable_type', 'App\Models\Student')
            ->where('issuable_id', $student->id)
            ->whereIn('status', ['issued', 'overdue'])
            ->exists();
        
        if ($alreadyIssued) {
            return redirect()->back()->with('error', 'You have already issued this book. Please return it first.');
        }
        
        // Create issue request (you can implement approval workflow)
        // For now, direct issue with librarian approval needed
        
        return redirect()->back()->with('success', 'Book request submitted. Please visit the library for approval.');
    }
}