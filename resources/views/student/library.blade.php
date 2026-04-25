{{-- resources/views/student/library.blade.php --}}
@extends('layouts.student')

@section('title', 'Library')

@section('content')
<div class="animate-fadeInUp">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <i class="fas fa-book-open"></i> Library Books
                    <a href="{{ route('student.library.issued') }}" class="float-end btn btn-sm btn-primary">My Issued Books</a>
                </div>
                <div class="card-body">
                    <div class="row mb-4">
                        <div class="col-md-4 mb-2">
                            <input type="text" id="searchInput" class="form-control" placeholder="Search by title, author, ISBN...">
                        </div>
                        <div class="col-md-3 mb-2">
                            <select id="categoryFilter" class="form-select">
                                <option value="">All Categories</option>
                                @foreach($categories as $cat)
                                    <option value="{{ $cat }}">{{ ucfirst($cat) }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-2 mb-2">
                            <select id="availabilityFilter" class="form-select">
                                <option value="">All Books</option>
                                <option value="available">Available Only</option>
                            </select>
                        </div>
                    </div>
                    
                    <div class="row g-4">
                        @forelse($books as $book)
                            <div class="col-lg-3 col-md-4 col-sm-6 book-item" 
                                 data-category="{{ $book->category }}" 
                                 data-available="{{ $book->available_quantity > 0 ? 'available' : '' }}">
                                <div class="card h-100">
                                    <div class="card-body text-center">
                                        <div class="mb-3">
                                            <i class="fas fa-book fa-3x text-primary"></i>
                                        </div>
                                        <h6 class="fw-bold mb-1">{{ Str::limit($book->title, 30) }}</h6>
                                        <small class="text-muted">{{ $book->author }}</small>
                                        <div class="mt-2">
                                            <span class="badge bg-secondary">{{ ucfirst($book->category) }}</span>
                                        </div>
                                        <div class="mt-2">
                                            @if($book->available_quantity > 0)
                                                <span class="badge bg-success">Available ({{ $book->available_quantity }})</span>
                                            @else
                                                <span class="badge bg-danger">Not Available</span>
                                            @endif
                                        </div>
                                    </div>
                                    <div class="card-footer bg-white border-0 pb-3">
                                        <a href="{{ route('student.library.books.show', $book) }}" class="btn btn-outline-primary btn-sm w-100">
                                            View Details <i class="fas fa-arrow-right ms-1"></i>
                                        </a>
                                    </div>
                                </div>
                            </div>
                        @empty
                            <div class="col-12 text-center py-5">
                                <i class="fas fa-book-open fa-4x text-muted mb-3"></i>
                                <p class="text-muted">No books available.</p>
                            </div>
                        @endforelse
                    </div>
                    
                    <div class="mt-4">
                        {{ $books->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    $(document).ready(function() {
        function filterBooks() {
            var search = $('#searchInput').val().toLowerCase();
            var category = $('#categoryFilter').val();
            var availability = $('#availabilityFilter').val();
            
            $('.book-item').each(function() {
                var title = $(this).find('h6').text().toLowerCase();
                var author = $(this).find('small').first().text().toLowerCase();
                var bookCategory = $(this).data('category');
                var available = $(this).data('available');
                
                var matchesSearch = title.includes(search) || author.includes(search);
                var matchesCategory = !category || bookCategory === category;
                var matchesAvailability = !availability || available === availability;
                
                if (matchesSearch && matchesCategory && matchesAvailability) {
                    $(this).show();
                } else {
                    $(this).hide();
                }
            });
        }
        
        $('#searchInput').on('keyup', filterBooks);
        $('#categoryFilter').on('change', filterBooks);
        $('#availabilityFilter').on('change', filterBooks);
    });
</script>
@endpush