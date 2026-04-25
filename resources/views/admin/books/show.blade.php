{{-- resources/views/admin/books/show.blade.php --}}
@extends('layouts.admin')

@section('title', $book->title)

@section('content')
<div class="animate-fadeInUp">
    <div class="card mb-4">
        <div class="card-header">
            <i class="fas fa-book me-2"></i> Book Details
            <div class="float-end">
                <a href="{{ route('admin.books.edit', $book) }}" class="btn btn-sm btn-primary">
                    <i class="fas fa-edit"></i> Edit
                </a>
                <a href="{{ route('admin.book-issues.create', ['book_id' => $book->id]) }}" class="btn btn-sm btn-info">
                    <i class="fas fa-hand-holding-heart"></i> Issue This Book
                </a>
                <a href="{{ route('admin.books.index') }}" class="btn btn-sm btn-secondary">
                    <i class="fas fa-arrow-left"></i> Back
                </a>
            </div>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-2 text-center">
                    <div style="width: 150px; height: 180px; background: #f8f9fa; border-radius: 8px; display: flex; align-items: center; justify-content: center; margin: 0 auto;">
                        <i class="fas fa-book fa-5x text-secondary"></i>
                    </div>
                </div>
                <div class="col-md-10">
                    <div class="row">
                        <div class="col-md-6 mb-2">
                            <strong>Title:</strong> {{ $book->title }}
                        </div>
                        <div class="col-md-6 mb-2">
                            <strong>ISBN:</strong> {{ $book->isbn ?? '-' }}
                        </div>
                        <div class="col-md-6 mb-2">
                            <strong>Author:</strong> {{ $book->author }}
                        </div>
                        <div class="col-md-6 mb-2">
                            <strong>Publisher:</strong> {{ $book->publisher ?? '-' }}
                        </div>
                        <div class="col-md-6 mb-2">
                            <strong>Publication Year:</strong> {{ $book->publication_year ?? '-' }}
                        </div>
                        <div class="col-md-6 mb-2">
                            <strong>Category:</strong> 
                            <span class="badge bg-secondary">{{ ucwords(str_replace('_', ' ', $book->category)) }}</span>
                        </div>
                        <div class="col-md-6 mb-2">
                            <strong>Total Copies:</strong> {{ $book->quantity }}
                        </div>
                        <div class="col-md-6 mb-2">
                            <strong>Available Copies:</strong> 
                            <span class="badge bg-{{ $book->available_quantity > 0 ? 'success' : 'danger' }}">
                                {{ $book->available_quantity }}
                            </span>
                        </div>
                        <div class="col-md-6 mb-2">
                            <strong>Shelf Location:</strong> {{ $book->shelf_location ?? '-' }}
                        </div>
                        <div class="col-md-12 mb-2">
                            <strong>Description:</strong><br>
                            <p>{{ $book->description ?? 'No description available.' }}</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="card">
        <div class="card-header">
            <i class="fas fa-history me-2"></i> Issue History
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered">
                    <thead>
                        <tr>
                            <th>Issued To</th>
                            <th>Type</th>
                            <th>Issue Date</th>
                            <th>Due Date</th>
                            <th>Return Date</th>
                            <th>Status</th>
                            <th>Late Fee</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($issues as $issue)
                        <tr>
                            <td>{{ $issue->issuable->user->name ?? 'N/A' }}</td>
                            <td>{{ ucfirst(str_replace('App\\Models\\', '', $issue->issuable_type)) }}</td>
                            <td>{{ $issue->issue_date->format('d M Y') }}</td>
                            <td>{{ $issue->due_date->format('d M Y') }}</td>
                            <td>{{ $issue->return_date?->format('d M Y') ?? '-' }}</td>
                            <td>
                                <span class="badge bg-{{ $issue->status == 'issued' ? 'primary' : ($issue->status == 'overdue' ? 'danger' : 'success') }}">
                                    {{ ucfirst($issue->status) }}
                                </span>
                            </td>
                            <td>{{ $issue->late_fee ? '₹ ' . number_format($issue->late_fee, 2) : '-' }}</td>
                            <td>
                                @if(in_array($issue->status, ['issued', 'overdue']))
                                <form action="{{ route('admin.book-issues.return', $issue) }}" method="POST" onsubmit="return confirm('Return this book?')">
                                    @csrf
                                    <button type="submit" class="btn btn-sm btn-success">Return</button>
                                </form>
                                @endif
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="8" class="text-center text-muted">No issue history found.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            {{ $issues->links() }}
        </div>
    </div>
</div>
@endsection