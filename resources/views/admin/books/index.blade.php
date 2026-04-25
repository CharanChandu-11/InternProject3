{{-- resources/views/admin/books/index.blade.php --}}
@extends('layouts.admin')

@section('title', 'Books')

@section('content')
<div class="animate-fadeInUp">
    <div class="card">
        <div class="card-header">
            <i class="fas fa-book me-2"></i> Library Management
            <div class="float-end">
                <a href="{{ route('admin.books.create') }}" class="btn btn-sm btn-primary">
                    <i class="fas fa-plus me-1"></i> Add Book
                </a>
                <a href="{{ route('admin.book-issues.create') }}" class="btn btn-sm btn-info">
                    <i class="fas fa-hand-holding-heart me-1"></i> Issue Book
                </a>
            </div>
        </div>
        <div class="card-body">
            <form method="GET" class="mb-4">
                <div class="row g-3">
                    <div class="col-md-3">
                        <select name="category" class="form-select">
                            <option value="">All Categories</option>
                            @foreach($categories as $cat)
                                <option value="{{ $cat }}" {{ request('category') == $cat ? 'selected' : '' }}>
                                    {{ ucwords(str_replace('_', ' ', $cat)) }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-3">
                        <select name="availability" class="form-select">
                            <option value="">All Books</option>
                            <option value="available" {{ request('availability') == 'available' ? 'selected' : '' }}>Available Only</option>
                            <option value="unavailable" {{ request('availability') == 'unavailable' ? 'selected' : '' }}>Unavailable Only</option>
                        </select>
                    </div>
                    <div class="col-md-4">
                        <input type="text" name="search" class="form-control" placeholder="Search by title, author, ISBN, publisher..." value="{{ request('search') }}">
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
                            <th>ISBN</th>
                            <th>Cover</th>
                            <th>Title</th>
                            <th>Author</th>
                            <th>Category</th>
                            <th>Total/Qty</th>
                            <th>Available</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    
                    <tbody>
                        @forelse($books as $book)
                        <tr>
                            <td class="fw-bold">{{ $book->isbn ?? '-' }}</td>
                            <td>
                                <div style="width: 40px; height: 50px; background: #f0f0f0; border-radius: 4px; display: flex; align-items: center; justify-content: center;">
                                    <i class="fas fa-book fa-2x text-secondary"></i>
                                </div>
                            </td>
                            <td>{{ $book->title }}</td>
                            <td>{{ $book->author }}</td>
                            <td><span class="badge bg-secondary">{{ ucwords(str_replace('_', ' ', $book->category)) }}</span></td>
                            <td class="text-center">{{ $book->quantity }}</td>
                            <td class="text-center">{{ $book->available_quantity }}</td>
                            <td>
                                @if($book->available_quantity > 0)
                                    <span class="badge bg-success">Available</span>
                                @else
                                    <span class="badge bg-danger">Not Available</span>
                                @endif
                            </td>
                            <td>
                                <a href="{{ route('admin.books.show', $book) }}" class="btn btn-sm btn-info">
                                    <i class="fas fa-eye"></i>
                                </a>
                                <a href="{{ route('admin.books.edit', $book) }}" class="btn btn-sm btn-primary">
                                    <i class="fas fa-edit"></i>
                                </a>
                                @if($book->available_quantity == $book->quantity)
                                    <form action="{{ route('admin.books.destroy', $book) }}" method="POST" class="d-inline">
                                        @csrf @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('Delete this book?')">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </form>
                                @endif
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="9" class="text-center text-muted">No books found.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            
            {{ $books->links() }}
        </div>
    </div>
</div>
@endsection