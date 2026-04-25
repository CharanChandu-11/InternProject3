{{-- resources/views/admin/books/edit.blade.php --}}
@extends('layouts.admin')

@section('title', 'Edit Book - ' . $book->title)

@section('content')
<div class="animate-fadeInUp">
    <div class="card">
        <div class="card-header">
            <i class="fas fa-edit me-2"></i> Edit Book: {{ $book->title }}
            <div class="float-end">
                <a href="{{ route('admin.books.index') }}" class="btn btn-sm btn-secondary">
                    <i class="fas fa-arrow-left me-1"></i> Back to Books
                </a>
            </div>
        </div>
        <div class="card-body">
            <form action="{{ route('admin.books.update', $book) }}" method="POST">
                @csrf
                @method('PUT')
                
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="title" class="form-label">Title <span class="text-danger">*</span></label>
                        <input type="text" name="title" id="title" class="form-control @error('title') is-invalid @enderror" 
                               value="{{ old('title', $book->title) }}" required>
                        @error('title')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    
                    <div class="col-md-6 mb-3">
                        <label for="isbn" class="form-label">ISBN</label>
                        <input type="text" name="isbn" id="isbn" class="form-control @error('isbn') is-invalid @enderror" 
                               value="{{ old('isbn', $book->isbn) }}">
                        @error('isbn')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    
                    <div class="col-md-6 mb-3">
                        <label for="author" class="form-label">Author <span class="text-danger">*</span></label>
                        <input type="text" name="author" id="author" class="form-control @error('author') is-invalid @enderror" 
                               value="{{ old('author', $book->author) }}" required>
                        @error('author')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    
                    <div class="col-md-6 mb-3">
                        <label for="publisher" class="form-label">Publisher</label>
                        <input type="text" name="publisher" id="publisher" class="form-control @error('publisher') is-invalid @enderror" 
                               value="{{ old('publisher', $book->publisher) }}">
                        @error('publisher')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    
                    <div class="col-md-3 mb-3">
                        <label for="publication_year" class="form-label">Publication Year</label>
                        <input type="number" name="publication_year" id="publication_year" class="form-control @error('publication_year') is-invalid @enderror" 
                               value="{{ old('publication_year', $book->publication_year) }}">
                        @error('publication_year')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    
                    <div class="col-md-3 mb-3">
                        <label for="category" class="form-label">Category <span class="text-danger">*</span></label>
                        <select name="category" id="category" class="form-select @error('category') is-invalid @enderror" required>
                            <option value="">Select Category</option>
                            <option value="fiction" {{ old('category', $book->category) == 'fiction' ? 'selected' : '' }}>Fiction</option>
                            <option value="non_fiction" {{ old('category', $book->category) == 'non_fiction' ? 'selected' : '' }}>Non-Fiction</option>
                            <option value="academic" {{ old('category', $book->category) == 'academic' ? 'selected' : '' }}>Academic</option>
                            <option value="reference" {{ old('category', $book->category) == 'reference' ? 'selected' : '' }}>Reference</option>
                            <option value="children" {{ old('category', $book->category) == 'children' ? 'selected' : '' }}>Children</option>
                            <option value="science" {{ old('category', $book->category) == 'science' ? 'selected' : '' }}>Science</option>
                            <option value="history" {{ old('category', $book->category) == 'history' ? 'selected' : '' }}>History</option>
                            <option value="technology" {{ old('category', $book->category) == 'technology' ? 'selected' : '' }}>Technology</option>
                            <option value="literature" {{ old('category', $book->category) == 'literature' ? 'selected' : '' }}>Literature</option>
                        </select>
                        @error('category')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    
                    <div class="col-md-3 mb-3">
                        <label for="quantity" class="form-label">Total Quantity <span class="text-danger">*</span></label>
                        <input type="number" name="quantity" id="quantity" class="form-control @error('quantity') is-invalid @enderror" 
                               value="{{ old('quantity', $book->quantity) }}" min="1" required>
                        <small class="text-muted">Current available: {{ $book->available_quantity }}</small>
                        @error('quantity')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    
                    <div class="col-md-3 mb-3">
                        <label for="shelf_location" class="form-label">Shelf Location</label>
                        <input type="text" name="shelf_location" id="shelf_location" class="form-control @error('shelf_location') is-invalid @enderror" 
                               value="{{ old('shelf_location', $book->shelf_location) }}">
                        @error('shelf_location')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    
                    <div class="col-md-12 mb-3">
                        <label for="description" class="form-label">Description</label>
                        <textarea name="description" id="description" rows="3" class="form-control @error('description') is-invalid @enderror">{{ old('description', $book->description) }}</textarea>
                        @error('description')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                </div>
                
                <div class="alert alert-info mt-2">
                    <i class="fas fa-info-circle me-2"></i>
                    <strong>Note:</strong> Changing the quantity will update the available quantity accordingly.
                    @if($book->available_quantity < $book->quantity)
                        <br>Currently {{ $book->quantity - $book->available_quantity }} book(s) are issued.
                    @endif
                </div>
                
                <div class="mt-3">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save me-1"></i> Update Book
                    </button>
                    <a href="{{ route('admin.books.index') }}" class="btn btn-secondary">
                        <i class="fas fa-times me-1"></i> Cancel
                    </a>
                    @if($book->available_quantity == $book->quantity && $book->issues()->count() == 0)
                        <button type="button" class="btn btn-danger float-end" data-bs-toggle="modal" data-bs-target="#deleteModal">
                            <i class="fas fa-trash me-1"></i> Delete Book
                        </button>
                    @endif
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Delete Confirmation Modal -->
@if($book->available_quantity == $book->quantity && $book->issues()->count() == 0)
<div class="modal fade" id="deleteModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="{{ route('admin.books.destroy', $book) }}" method="POST">
                @csrf
                @method('DELETE')
                <div class="modal-header">
                    <h5 class="modal-title">Confirm Delete</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <p>Are you sure you want to delete the book <strong>{{ $book->title }}</strong>?</p>
                    <p class="text-danger">This action cannot be undone.</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-danger">Delete Book</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endif
@endsection

@push('scripts')
<script>
    // Warn if quantity is reduced below current issued count
    $('#quantity').on('change', function() {
        var newQty = parseInt($(this).val());
        var currentQty = {{ $book->quantity }};
        var issuedCount = {{ $book->quantity - $book->available_quantity }};
        
        if (newQty < issuedCount) {
            alert('Cannot reduce quantity below the number of issued books (' + issuedCount + ').');
            $(this).val(currentQty);
        }
    });
</script>
@endpush