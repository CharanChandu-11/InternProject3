<?php
// app/Http/Controllers/Admin/BookController.php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Book;
use App\Models\BookIssue;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class BookController extends Controller
{
    /**
     * Display a listing of books.
     */
    public function index(Request $request)
    {
        $query = Book::query();

        // Filter by category
        if ($request->has('category') && $request->category) {
            $query->where('category', $request->category);
        }

        // Search by title, author, ISBN
        if ($request->has('search') && $request->search) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                  ->orWhere('author', 'like', "%{$search}%")
                  ->orWhere('isbn', 'like', "%{$search}%")
                  ->orWhere('publisher', 'like', "%{$search}%");
            });
        }

        // Filter by availability
        if ($request->has('availability')) {
            if ($request->availability == 'available') {
                $query->where('available_quantity', '>', 0);
            } elseif ($request->availability == 'unavailable') {
                $query->where('available_quantity', 0);
            }
        }

        $books = $query->orderBy('title')->paginate(20);
        
        // Get unique categories for filter dropdown
        $categories = Book::select('category')->distinct()->pluck('category');

        return view('admin.books.index', compact('books', 'categories'));
    }

    /**
     * Show the form for creating a new book.
     */
    public function create()
    {
        return view('admin.books.create');
    }

    /**
     * Store a newly created book.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'isbn' => 'nullable|string|unique:books,isbn|max:20',
            'author' => 'required|string|max:255',
            'publisher' => 'nullable|string|max:255',
            'publication_year' => 'nullable|integer|min:1900|max:' . date('Y'),
            'category' => 'required|string|max:100',
            'quantity' => 'required|integer|min:1',
            'shelf_location' => 'nullable|string|max:50',
            'description' => 'nullable|string',
        ]);

        $validated['available_quantity'] = $validated['quantity'];
        
        Book::create($validated);

        return redirect()->route('admin.books.index')->with('success', 'Book added successfully.');
    }

    /**
     * Display the specified book.
     */
    public function show(Book $book)
    {
        // Get issue history for this book
        $issues = BookIssue::where('book_id', $book->id)
            ->with(['issuable.user', 'issuable'])
            ->latest()
            ->paginate(10);

        return view('admin.books.show', compact('book', 'issues'));
    }

    /**
     * Show the form for editing the specified book.
     */
    public function edit(Book $book)
    {
        return view('admin.books.edit', compact('book'));
    }

    /**
     * Update the specified book.
     */
    public function update(Request $request, Book $book)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'isbn' => 'nullable|string|unique:books,isbn,' . $book->id . '|max:20',
            'author' => 'required|string|max:255',
            'publisher' => 'nullable|string|max:255',
            'publication_year' => 'nullable|integer|min:1900|max:' . date('Y'),
            'category' => 'required|string|max:100',
            'quantity' => 'required|integer|min:1',
            'shelf_location' => 'nullable|string|max:50',
            'description' => 'nullable|string',
        ]);

        $oldQuantity = $book->quantity;
        $newQuantity = $validated['quantity'];
        
        // Adjust available quantity based on quantity change
        $difference = $newQuantity - $oldQuantity;
        $validated['available_quantity'] = $book->available_quantity + $difference;
        
        if ($validated['available_quantity'] < 0) {
            return back()->withErrors(['quantity' => 'Cannot reduce quantity below currently issued books.']);
        }

        $book->update($validated);

        return redirect()->route('admin.books.index')->with('success', 'Book updated successfully.');
    }

    /**
     * Remove the specified book.
     */
    public function destroy(Book $book)
    {
        // Check if book has any active issues
        $activeIssues = BookIssue::where('book_id', $book->id)
            ->whereIn('status', ['issued', 'overdue'])
            ->count();

        if ($activeIssues > 0) {
            return back()->with('error', 'Cannot delete book with active issues.');
        }

        $book->delete();

        return redirect()->route('admin.books.index')->with('success', 'Book deleted successfully.');
    }

    /**
     * Export books to Excel.
     */
    public function export()
    {
        // Export logic here
        return redirect()->back()->with('info', 'Export functionality coming soon.');
    }

    /**
     * Return a book.
     */
    public function returnBook(Request $request, Book $book)
    {
        $request->validate([
            'issue_id' => 'required|exists:book_issues,id',
            'condition' => 'nullable|string|max:255',
        ]);

        $issue = BookIssue::findOrFail($request->issue_id);
        
        if ($issue->status == 'returned') {
            return back()->with('error', 'Book already returned.');
        }

        $issue->update([
            'return_date' => now(),
            'status' => 'returned',
            'remarks' => $request->condition,
            'late_fee' => $issue->calculateLateFee(),
        ]);

        // Increase available quantity
        $book->increment('available_quantity');

        return redirect()->route('admin.books.show', $book)->with('success', 'Book returned successfully.');
    }
}