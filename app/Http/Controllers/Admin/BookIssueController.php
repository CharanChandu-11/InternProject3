<?php
// app/Http/Controllers/Admin/BookIssueController.php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Book;
use App\Models\BookIssue;
use App\Models\Student;
use App\Models\User;
use Illuminate\Http\Request;

class BookIssueController extends Controller
{
    /**
     * Display a listing of book issues.
     */
    public function index(Request $request)
    {
        $query = BookIssue::with(['book', 'issuable.user']);

        // Filter by status
        if ($request->has('status') && $request->status) {
            $query->where('status', $request->status);
        }

        // Filter by type (student/employee/teacher)
        if ($request->has('issuable_type') && $request->issuable_type) {
            $query->where('issuable_type', 'App\\Models\\' . ucfirst($request->issuable_type));
        }

        // Search by book title or issuer name
        if ($request->has('search') && $request->search) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->whereHas('book', function($bq) use ($search) {
                    $bq->where('title', 'like', "%{$search}%")
                       ->orWhere('isbn', 'like', "%{$search}%");
                })->orWhereHas('issuable.user', function($uq) use ($search) {
                    $uq->where('name', 'like', "%{$search}%")
                       ->orWhere('email', 'like', "%{$search}%");
                });
            });
        }

        $issues = $query->orderBy('created_at', 'desc')->paginate(20);

        return view('admin.book-issues.index', compact('issues'));
    }

    /**
     * Show the form for creating a new book issue.
     */
    public function create(Request $request)
    {
        $books = Book::where('available_quantity', '>', 0)
            ->orderBy('title')
            ->get();
        
        $students = Student::with('user')->orderBy('admission_number')->get();
        $teachers = User::where('user_type', 'teacher')->with('profile')->orderBy('name')->get();
        $employees = User::where('user_type', 'employee')->with('profile')->orderBy('name')->get();

        $selectedType = $request->type ?? 'student';
        $selectedId = $request->id ?? null;

        return view('admin.book-issues.create', compact('books', 'students', 'teachers', 'employees', 'selectedType', 'selectedId'));
    }

    /**
     * Store a newly created book issue.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'book_id' => 'required|exists:books,id',
            'issuable_type' => 'required|in:student,teacher,employee',
            'issuable_id' => 'required|integer',
            'due_date' => 'required|date|after:today',
            'remarks' => 'nullable|string|max:500',
        ]);

        $book = Book::findOrFail($validated['book_id']);

        if ($book->available_quantity <= 0) {
            return back()->with('error', 'No copies available for this book.');
        }

        // Check if the user already has overdue books
        $issuableType = 'App\\Models\\' . ucfirst($validated['issuable_type']);
        $existingOverdue = BookIssue::where('issuable_type', $issuableType)
            ->where('issuable_id', $validated['issuable_id'])
            ->where('status', 'overdue')
            ->exists();

        if ($existingOverdue) {
            return back()->with('error', 'This user has overdue books. Please return them first.');
        }

        // Create issue
        $issue = BookIssue::create([
            'book_id' => $validated['book_id'],
            'issuable_type' => $issuableType,
            'issuable_id' => $validated['issuable_id'],
            'issue_date' => now(),
            'due_date' => $validated['due_date'],
            'status' => 'issued',
            'remarks' => $validated['remarks'],
        ]);

        // Decrease available quantity
        $book->decrement('available_quantity');

        return redirect()->route('admin.book-issues.index')->with('success', 'Book issued successfully.');
    }

    /**
     * Display the specified book issue.
     */
    public function show(BookIssue $bookIssue)
    {
        $bookIssue->load(['book', 'issuable.user']);
        
        return view('admin.book-issues.show', compact('bookIssue'));
    }

    /**
     * Show the form for editing the specified book issue.
     */
    public function edit(BookIssue $bookIssue)
    {
        if ($bookIssue->status == 'returned') {
            return redirect()->route('admin.book-issues.index')->with('error', 'Cannot edit returned issues.');
        }

        return view('admin.book-issues.edit', compact('bookIssue'));
    }

    /**
     * Update the specified book issue.
     */
    public function update(Request $request, BookIssue $bookIssue)
    {
        if ($bookIssue->status == 'returned') {
            return back()->with('error', 'Cannot edit returned issues.');
        }

        $validated = $request->validate([
            'due_date' => 'required|date',
            'remarks' => 'nullable|string|max:500',
        ]);

        $bookIssue->update($validated);

        return redirect()->route('admin.book-issues.show', $bookIssue)->with('success', 'Issue updated successfully.');
    }

    /**
     * Remove the specified book issue (cancel pending issue).
     */
    public function destroy(BookIssue $bookIssue)
    {
        if ($bookIssue->status == 'returned') {
            return back()->with('error', 'Cannot delete returned issues.');
        }

        // If not returned, return the book to inventory
        if (in_array($bookIssue->status, ['issued', 'overdue'])) {
            $bookIssue->book->increment('available_quantity');
        }

        $bookIssue->delete();

        return redirect()->route('admin.book-issues.index')->with('success', 'Issue cancelled successfully.');
    }

    /**
     * Return a book (quick return from list)
     */
    public function return(BookIssue $bookIssue)
    {
        if ($bookIssue->status == 'returned') {
            return back()->with('error', 'Book already returned.');
        }

        $bookIssue->update([
            'return_date' => now(),
            'status' => 'returned',
            'late_fee' => $bookIssue->calculateLateFee(),
        ]);

        $bookIssue->book->increment('available_quantity');

        return redirect()->route('admin.book-issues.index')->with('success', 'Book returned successfully.');
    }

    /**
     * Get issuer details via AJAX for the create form.
     */
    public function getIssuer(Request $request)
    {
        $request->validate([
            'type' => 'required|in:student,teacher,employee',
            'id' => 'required|integer',
        ]);

        $model = 'App\\Models\\' . ucfirst($request->type);
        $issuer = $model::with('user')->findOrFail($request->id);

        return response()->json([
            'name' => $issuer->user->name,
            'email' => $issuer->user->email,
            'phone' => $issuer->user->phone,
            'address' => $issuer->user->address,
        ]);
    }

    /**
     * Get issuers list via AJAX.
     */
    public function getIssuers(Request $request)
    {
        $request->validate([
            'type' => 'required|in:student,teacher,employee',
        ]);

        $model = 'App\\Models\\' . ucfirst($request->type);
        
        if ($request->type == 'student') {
            $issuers = $model::with('user')->orderBy('admission_number')->get()
                ->map(function($item) {
                    return [
                        'id' => $item->id,
                        'name' => $item->user->name . ' (' . $item->admission_number . ')',
                        'email' => $item->user->email,
                    ];
                });
        } else {
            $issuers = $model::with('user')->orderBy('user_id')->get()
                ->map(function($item) {
                    return [
                        'id' => $item->id,
                        'name' => $item->user->name,
                        'email' => $item->user->email,
                    ];
                });
        }

        return response()->json($issuers);
    }
}