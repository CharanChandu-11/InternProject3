<?php
// app/Http/Controllers/Api/SuperAdmin/BookIssueController.php

namespace App\Http\Controllers\Api\SuperAdmin;

use App\Http\Controllers\Api\BaseController;
use App\Models\BookIssue;
use Illuminate\Http\Request;

class BookIssueController extends BaseController
{
    public function index()
    {
        $issues = BookIssue::with(['book', 'issuable'])->get();
        return $this->sendResponse($issues, 'Issues retrieved');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'book_id' => 'required|exists:books,id',
            'issuable_type' => 'required|in:App\Models\Student,App\Models\User',
            'issuable_id' => 'required|integer',
            'issue_date' => 'required|date',
            'due_date' => 'required|date|after:issue_date',
        ]);

        $book = \App\Models\Book::find($validated['book_id']);
        if ($book->available_quantity <= 0) {
            return $this->sendError('Book not available', [], 422);
        }

        $issue = BookIssue::create($validated);
        $book->decrement('available_quantity');
        return $this->sendResponse($issue, 'Book issued', 201);
    }

    public function show(BookIssue $bookIssue)
    {
        $bookIssue->load(['book', 'issuable']);
        return $this->sendResponse($bookIssue, 'Issue retrieved');
    }

    public function update(Request $request, BookIssue $bookIssue)
    {
        $validated = $request->validate([
            'due_date' => 'sometimes|date|after:issue_date',
            'return_date' => 'nullable|date',
            'status' => 'sometimes|in:issued,returned,overdue,lost',
            'late_fee' => 'nullable|numeric',
        ]);
        $bookIssue->update($validated);
        if ($bookIssue->status == 'returned' && !$bookIssue->return_date) {
            $bookIssue->update(['return_date' => now()]);
            $bookIssue->book->increment('available_quantity');
        }
        return $this->sendResponse($bookIssue, 'Issue updated');
    }

    public function destroy(BookIssue $bookIssue)
    {
        if ($bookIssue->status != 'returned') {
            $bookIssue->book->increment('available_quantity');
        }
        $bookIssue->delete();
        return $this->sendResponse([], 'Issue deleted');
    }
}