{{-- resources/views/student/library/issued.blade.php --}}
@extends('layouts.student')

@section('title', 'My Issued Books')

@section('content')
<div class="animate-fadeInUp">
    <!-- Statistics Cards -->
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="card bg-primary text-white">
                <div class="card-body text-center">
                    <h6 class="card-title">Total Issued</h6>
                    <h2 class="mb-0">{{ $stats['total_issued'] }}</h2>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-success text-white">
                <div class="card-body text-center">
                    <h6 class="card-title">Currently Issued</h6>
                    <h2 class="mb-0">{{ $stats['currently_issued'] }}</h2>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-danger text-white">
                <div class="card-body text-center">
                    <h6 class="card-title">Overdue</h6>
                    <h2 class="mb-0">{{ $stats['overdue'] }}</h2>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-secondary text-white">
                <div class="card-body text-center">
                    <h6 class="card-title">Returned</h6>
                    <h2 class="mb-0">{{ $stats['returned'] }}</h2>
                </div>
            </div>
        </div>
    </div>
    
    <div class="card">
        <div class="card-header">
            <i class="fas fa-clock me-2"></i> My Issued Books
            <div class="float-end">
                <a href="{{ route('student.library.books') }}" class="btn btn-sm btn-primary">
                    <i class="fas fa-search me-1"></i> Browse Books
                </a>
            </div>
        </div>
        <div class="card-body">
            <!-- Filters -->
            <form method="GET" class="mb-4">
                <div class="row g-3">
                    <div class="col-md-3">
                        <select name="status" class="form-select">
                            <option value="">All Status</option>
                            <option value="issued" {{ request('status') == 'issued' ? 'selected' : '' }}>Currently Issued</option>
                            <option value="overdue" {{ request('status') == 'overdue' ? 'selected' : '' }}>Overdue</option>
                            <option value="returned" {{ request('status') == 'returned' ? 'selected' : '' }}>Returned</option>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <button type="submit" class="btn btn-primary w-100">Filter</button>
                    </div>
                    <div class="col-md-2">
                        <a href="{{ route('student.library.issued') }}" class="btn btn-secondary w-100">Reset</a>
                    </div>
                </div>
            </form>
            
            <!-- Issued Books Table -->
            <div class="table-responsive">
                <table class="table table-bordered datatable">
                    <thead class="table-light">
                        <tr>
                            <th>Book Title</th>
                            <th>Author</th>
                            <th>Issue Date</th>
                            <th>Due Date</th>
                            <th>Return Date</th>
                            <th>Status</th>
                            <th>Late Fee</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($issuedBooks as $issue)
                            @php
                                $isOverdue = $issue->status == 'overdue' || ($issue->status == 'issued' && $issue->due_date < now());
                                $daysLeft = $issue->due_date ? now()->diffInDays($issue->due_date, false) : null;
                            @endphp
                            <tr class="{{ $isOverdue ? 'table-danger' : '' }}">
                                <td>
                                    <a href="{{ route('student.library.books.show', $issue->book) }}">
                                        <strong>{{ $issue->book->title }}</strong>
                                    </a>
                                </td>
                                <td>{{ $issue->book->author }}</td>
                                <td>{{ $issue->issue_date->format('d M, Y') }}</td>
                                <td>
                                    {{ $issue->due_date ? $issue->due_date->format('d M, Y') : '-' }}
                                    @if($issue->status == 'issued' && !$isOverdue)
                                        <br><small class="text-success">{{ $daysLeft }} days left</small>
                                    @elseif($isOverdue)
                                        <br><small class="text-danger">Overdue by {{ abs($daysLeft) }} days</small>
                                    @endif
                                </td>
                                <td>{{ $issue->return_date ? $issue->return_date->format('d M, Y') : '-' }}</td>
                                <td>
                                    @if($issue->status == 'issued')
                                        <span class="badge bg-warning">Issued</span>
                                    @elseif($issue->status == 'overdue')
                                        <span class="badge bg-danger">Overdue</span>
                                    @elseif($issue->status == 'returned')
                                        <span class="badge bg-success">Returned</span>
                                    @else
                                        <span class="badge bg-secondary">{{ ucfirst($issue->status) }}</span>
                                    @endif
                                </td>
                                <td>
                                    @if($issue->late_fee)
                                        ₹ {{ number_format($issue->late_fee, 2) }}
                                    @else
                                        -
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center text-muted">
                                    <i class="fas fa-info-circle me-2"></i> No books issued.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </div>
            </div>
            
            {{ $issuedBooks->links() }}
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
    .table td {
        vertical-align: middle;
    }
    .badge {
        font-size: 12px;
        padding: 5px 10px;
    }
</style>
@endpush