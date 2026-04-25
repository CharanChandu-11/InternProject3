{{-- resources/views/admin/book-issues/index.blade.php --}}
@extends('layouts.admin')

@section('title', 'Book Issues')

@section('content')
<div class="animate-fadeInUp">
    <div class="card">
        <div class="card-header">
            <i class="fas fa-hand-holding-heart me-2"></i> Book Issues Management
            <div class="float-end">
                <a href="{{ route('admin.book-issues.create') }}" class="btn btn-sm btn-primary">
                    <i class="fas fa-plus me-1"></i> Issue New Book
                </a>
            </div>
        </div>
        <div class="card-body">
            <form method="GET" class="mb-4">
                <div class="row g-3">
                    <div class="col-md-3">
                        <select name="status" class="form-select">
                            <option value="">All Status</option>
                            <option value="issued" {{ request('status') == 'issued' ? 'selected' : '' }}>Issued</option>
                            <option value="overdue" {{ request('status') == 'overdue' ? 'selected' : '' }}>Overdue</option>
                            <option value="returned" {{ request('status') == 'returned' ? 'selected' : '' }}>Returned</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <select name="issuable_type" class="form-select">
                            <option value="">All Types</option>
                            <option value="student" {{ request('issuable_type') == 'student' ? 'selected' : '' }}>Student</option>
                            <option value="teacher" {{ request('issuable_type') == 'teacher' ? 'selected' : '' }}>Teacher</option>
                            <option value="employee" {{ request('issuable_type') == 'employee' ? 'selected' : '' }}>Employee</option>
                        </select>
                    </div>
                    <div class="col-md-4">
                        <input type="text" name="search" class="form-control" placeholder="Search by book title or user name..." value="{{ request('search') }}">
                    </div>
                    <div class="col-md-2">
                        <button type="submit" class="btn btn-primary w-100">Filter</button>
                    </div>
                </div>
            </form>
            
            <div class="table-responsive">
                <table class="table table-bordered datatable">
                    <thead>
                         <tr>
                            <th>ID</th>
                            <th>Book</th>
                            <th>Issued To</th>
                            <th>Type</th>
                            <th>Issue Date</th>
                            <th>Due Date</th>
                            <th>Status</th>
                            <th>Late Fee</th>
                            <th>Actions</th>
                         </tr>
                    </thead>
                    <tbody>
                        @forelse($issues as $issue)
                         <tr>
                            <td>{{ $issue->id }}</td>
                            <td>
                                <strong>{{ $issue->book->title }}</strong><br>
                                <small class="text-muted">By: {{ $issue->book->author }}</small>
                            </td>
                            <td>{{ $issue->issuable->user->name ?? 'N/A' }}</td>
                            <td><span class="badge bg-secondary">{{ ucfirst(str_replace('App\\Models\\', '', $issue->issuable_type)) }}</span></td>
                            <td>{{ $issue->issue_date->format('d M Y') }}</td>
                            <td class="{{ $issue->status == 'overdue' ? 'text-danger fw-bold' : '' }}">
                                {{ $issue->due_date->format('d M Y') }}
                                @if($issue->status == 'overdue')
                                    <br><small>({{ now()->diffInDays($issue->due_date) }} days overdue)</small>
                                @endif
                            </td>
                            <td>
                                @if($issue->status == 'issued')
                                    <span class="badge bg-primary">Issued</span>
                                @elseif($issue->status == 'overdue')
                                    <span class="badge bg-danger">Overdue</span>
                                @else
                                    <span class="badge bg-success">Returned</span>
                                @endif
                            </td>
                            <td>
                                @if($issue->late_fee)
                                    ₹ {{ number_format($issue->late_fee, 2) }}
                                @else
                                    -
                                @endif
                            </td>
                            <td>
                                <a href="{{ route('admin.book-issues.show', $issue) }}" class="btn btn-sm btn-info">
                                    <i class="fas fa-eye"></i>
                                </a>
                                @if(in_array($issue->status, ['issued', 'overdue']))
                                    <form action="{{ route('admin.book-issues.return', $issue) }}" method="POST" class="d-inline">
                                        @csrf
                                        <button type="submit" class="btn btn-sm btn-success" onclick="return confirm('Return this book?')">
                                            <i class="fas fa-undo-alt"></i> Return
                                        </button>
                                    </form>
                                @endif
                             </td>
                          </tr>
                        @empty
                          <tr>
                            <td colspan="9" class="text-center text-muted">No book issues found.</td>
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