{{-- resources/views/student/library-issued.blade.php --}}
@extends('layouts.student')

@section('title', 'Issued Books')

@section('content')
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <i class="fas fa-book-reader me-2"></i> My Issued Books
                <a href="{{ route('student.library.books') }}" class="btn btn-sm btn-secondary float-end">Browse Library</a>
            </div>
            <div class="card-body">
                <div class="row mb-4">
                    <div class="col-md-3">
                        <div class="card bg-primary text-white">
                            <div class="card-body text-center">
                                <h3>{{ $stats['total_issued'] }}</h3>
                                <small>Currently Issued</small>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card bg-danger text-white">
                            <div class="card-body text-center">
                                <h3>{{ $stats['overdue'] }}</h3>
                                <small>Overdue</small>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card bg-success text-white">
                            <div class="card-body text-center">
                                <h3>{{ $stats['returned'] }}</h3>
                                <small>Books Read</small>
                            </div>
                        </div>
                    </div>
                </div>
                
                @if($issuedBooks->count() > 0)
                    <div class="table-responsive">
                        <table class="table table-bordered datatable">
                            <thead>
                                 <tr>
                                    <th>Book Title</th>
                                    <th>Author</th>
                                    <th>Issue Date</th>
                                    <th>Due Date</th>
                                    <th>Status</th>
                                    <th>Actions</th>
                                 </tr>
                            </thead>
                            <tbody>
                                @foreach($issuedBooks as $issue)
                                     <tr>
                                        <td>{{ $issue->book->title }}</td>
                                        <td>{{ $issue->book->author }}</td>
                                        <td>{{ $issue->issue_date->format('d M, Y') }}</td>
                                        <td class="{{ $issue->due_date->isPast() && $issue->status != 'returned' ? 'text-danger fw-bold' : '' }}">
                                            {{ $issue->due_date->format('d M, Y') }}
                                        </td>
                                        <td>
                                            @if($issue->status == 'issued')
                                                <span class="badge bg-success">Issued</span>
                                            @elseif($issue->status == 'overdue')
                                                <span class="badge bg-danger">Overdue</span>
                                            @elseif($issue->status == 'returned')
                                                <span class="badge bg-secondary">Returned</span>
                                            @endif
                                        </td>
                                        <td>
                                            <a href="{{ route('student.library.books.show', $issue->book) }}" class="btn btn-sm btn-info">View Book</a>
                                        </td>
                                     </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <p class="text-muted text-center py-4">No books issued yet.</p>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection