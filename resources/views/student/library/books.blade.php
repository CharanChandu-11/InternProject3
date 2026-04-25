{{-- resources/views/student/library/books.blade.php --}}
@extends('layouts.student')

@section('title', 'Library - Books')

@section('content')
<div class="animate-fadeInUp">
    <div class="card">
        <div class="card-header">
            <i class="fas fa-book-open me-2"></i> Library Catalog
            <div class="float-end">
                <a href="{{ route('student.library.issued') }}" class="btn btn-sm btn-info">
                    <i class="fas fa-clock me-1"></i> My Issued Books
                </a>
            </div>
        </div>
        <div class="card-body">
            <!-- Search and Filters -->
            <form method="GET" class="mb-4">
                <div class="row g-3">
                    <div class="col-md-4">
                        <div class="input-group">
                            <input type="text" name="search" class="form-control" placeholder="Search by title, author, ISBN..." 
                                   value="{{ request('search') }}">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-search"></i>
                            </button>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <select name="category" class="form-select">
                            <option value="">All Categories</option>
                            @foreach($categories as $category)
                                <option value="{{ $category }}" {{ request('category') == $category ? 'selected' : '' }}>
                                    {{ ucfirst($category) }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-2">
                        <select name="availability" class="form-select">
                            <option value="">All Books</option>
                            <option value="available" {{ request('availability') == 'available' ? 'selected' : '' }}>Available Only</option>
                            <option value="unavailable" {{ request('availability') == 'unavailable' ? 'selected' : '' }}>Unavailable</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <div class="d-flex gap-2">
                            <button type="submit" class="btn btn-primary flex-grow-1">Filter</button>
                            <a href="{{ route('student.library.books') }}" class="btn btn-secondary">Reset</a>
                        </div>
                    </div>
                </div>
            </form>
            
            <!-- Books Grid -->
            <div class="row">
                @forelse($books as $book)
                    <div class="col-md-3 col-sm-6 mb-4">
                        <div class="card h-100 library-card">
                            <div class="card-body">
                                <div class="book-cover mb-3 text-center">
                                    <i class="fas fa-book fa-4x text-primary"></i>
                                </div>
                                <h6 class="card-title text-truncate" title="{{ $book->title }}">
                                    {{ Str::limit($book->title, 35) }}
                                </h6>
                                <p class="card-text small text-muted mb-1">
                                    <i class="fas fa-user me-1"></i> {{ Str::limit($book->author, 25) }}
                                </p>
                                <p class="card-text small text-muted mb-2">
                                    <i class="fas fa-code me-1"></i> {{ $book->isbn ?? 'N/A' }}
                                </p>
                                <div class="mb-2">
                                    @if($book->available_quantity > 0)
                                        <span class="badge bg-success">Available</span>
                                        <small class="text-muted ms-1">{{ $book->available_quantity }} copy(s)</small>
                                    @else
                                        <span class="badge bg-danger">Unavailable</span>
                                    @endif
                                </div>
                                <div class="text-muted small mb-3">
                                    <i class="fas fa-tag me-1"></i> {{ ucfirst($book->category) }}
                                </div>
                            </div>
                            <div class="card-footer bg-white border-0 pb-3">
                                <a href="{{ route('student.library.books.show', $book) }}" class="btn btn-primary btn-sm w-100">
                                    <i class="fas fa-eye me-1"></i> View Details
                                </a>
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="col-12">
                        <div class="alert alert-info text-center">
                            <i class="fas fa-info-circle me-2"></i> No books found.
                        </div>
                    </div>
                @endforelse
            </div>
            
            <!-- Pagination -->
            <div class="d-flex justify-content-center mt-4">
                {{ $books->links() }}
            </div>
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
    .library-card {
        transition: transform 0.2s, box-shadow 0.2s;
        cursor: pointer;
    }
    .library-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 10px 25px rgba(0,0,0,0.1);
    }
    .book-cover {
        background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
        padding: 20px;
        border-radius: 10px;
    }
    .card-title {
        font-weight: 600;
        margin-bottom: 8px;
    }
</style>
@endpush