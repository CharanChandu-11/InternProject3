<?php
// app/Http/Controllers/Api/SuperAdmin/BookController.php

namespace App\Http\Controllers\Api\SuperAdmin;

use App\Http\Controllers\Api\BaseController;
use App\Models\Book;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\BooksExport;

class BookController extends BaseController
{
    public function index()
    {
        $books = Book::paginate(20);
        return $this->sendPaginatedResponse($books, 'Books retrieved');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string',
            'isbn' => 'required|unique:books',
            'author' => 'required|string',
            'publisher' => 'nullable|string',
            'publication_year' => 'nullable|integer',
            'category' => 'required|string',
            'quantity' => 'required|integer|min:1',
            'shelf_location' => 'nullable|string',
            'description' => 'nullable|string',
        ]);
        $validated['available_quantity'] = $validated['quantity'];
        $book = Book::create($validated);
        return $this->sendResponse($book, 'Book created', 201);
    }

    public function show(Book $book)
    {
        return $this->sendResponse($book, 'Book retrieved');
    }

    public function update(Request $request, Book $book)
    {
        $validated = $request->validate([
            'title' => 'sometimes|string',
            'isbn' => 'sometimes|unique:books,isbn,' . $book->id,
            'author' => 'sometimes|string',
            'publisher' => 'nullable|string',
            'publication_year' => 'nullable|integer',
            'category' => 'sometimes|string',
            'quantity' => 'sometimes|integer|min:1',
            'shelf_location' => 'nullable|string',
            'description' => 'nullable|string',
        ]);
        if (isset($validated['quantity'])) {
            $validated['available_quantity'] = $validated['quantity'] - ($book->quantity - $book->available_quantity);
        }
        $book->update($validated);
        return $this->sendResponse($book, 'Book updated');
    }

    public function destroy(Book $book)
    {
        $book->delete();
        return $this->sendResponse([], 'Book deleted');
    }

    public function returnBook(Request $request, Book $book)
    {
        $request->validate(['issue_id' => 'required|exists:book_issues,id']);
        $issue = $book->issues()->find($request->issue_id);
        if ($issue && $issue->status == 'issued') {
            $issue->update(['status' => 'returned', 'return_date' => now()]);
            $book->increment('available_quantity');
            return $this->sendResponse($issue, 'Book returned');
        }
        return $this->sendError('Book not issued', [], 400);
    }

    public function export()
    {
        return Excel::download(new BooksExport, 'books.xlsx');
    }
}