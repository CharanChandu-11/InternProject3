{{-- resources/views/student/library/show.blade.php --}}
@extends('layouts.student')

@section('title', $book->title)

@section('content')
<div class="animate-fadeInUp">
    <div class="card">
        <div class="card-header">
            <i class="fas fa-book me-2"></i> Book Details
            <div class="float-end">
                <a href="{{ route('student.library.books') }}" class="btn btn-sm btn-secondary">
                    <i class="fas fa-arrow-left me-1"></i> Back to Catalog
                </a>
            </div>
        </div>
        <div class="card-body">
            <div class="row">
                <!-- Book Cover -->
                <div class="col-md-3 text-center">
                    <div class="book-cover-large mb-3">
                        <i class="fas fa-book-open fa-6x text-primary"></i>
                    </div>
                    <div class="mt-3">
                        @if($book->available_quantity > 0)
                            <span class="badge bg-success fs-6">Available</span>
                            <span class="ms-2">{{ $book->available_quantity }} / {{ $book->quantity }} copies available</span>
                        @else
                            <span class="badge bg-danger fs-6">Currently Unavailable</span>
                        @endif
                    </div>
                </div>
                
                <!-- Book Details -->
                <div class="col-md-9">
                    <h3>{{ $book->title }}</h3>
                    <div class="row mt-3">
                        <div class="col-md-6">
                            <div class="info-row">
                                <strong><i class="fas fa-user me-2 text-primary"></i> Author:</strong>
                                <span>{{ $book->author }}</span>
                            </div>
                            <div class="info-row mt-2">
                                <strong><i class="fas fa-building me-2 text-primary"></i> Publisher:</strong>
                                <span>{{ $book->publisher ?? 'N/A' }}</span>
                            </div>
                            <div class="info-row mt-2">
                                <strong><i class="fas fa-calendar me-2 text-primary"></i> Publication Year:</strong>
                                <span>{{ $book->publication_year ?? 'N/A' }}</span>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="info-row">
                                <strong><i class="fas fa-barcode me-2 text-primary"></i> ISBN:</strong>
                                <span>{{ $book->isbn ?? 'N/A' }}</span>
                            </div>
                            <div class="info-row mt-2">
                                <strong><i class="fas fa-tag me-2 text-primary"></i> Category:</strong>
                                <span class="badge bg-info">{{ ucfirst($book->category) }}</span>
                            </div>
                            <div class="info-row mt-2">
                                <strong><i class="fas fa-map-marker-alt me-2 text-primary"></i> Shelf Location:</strong>
                                <span>{{ $book->shelf_location ?? 'N/A' }}</span>
                            </div>
                        </div>
                    </div>
                    
                    @if($book->description)
                        <div class="mt-3">
                            <strong><i class="fas fa-align-left me-2 text-primary"></i> Description:</strong>
                            <p class="mt-2">{{ $book->description }}</p>
                        </div>
                    @endif
                    
                    @if($currentIssue)
                        <div class="alert alert-info mt-3">
                            <i class="fas fa-info-circle me-2"></i>
                            Currently issued to: <strong>{{ $currentIssue->issuable->user->name }}</strong>
                            @if($currentIssue->due_date)
                                (Due: {{ $currentIssue->due_date->format('d M, Y') }})
                            @endif
                        </div>
                    @endif
                    
                    <div class="mt-4">
                        @if($alreadyIssued)
                            <div class="alert alert-warning">
                                <i class="fas fa-exclamation-triangle me-2"></i>
                                You have already issued this book. Please return it before issuing again.
                            </div>
                        @elseif($book->available_quantity > 0)
                            <button class="btn btn-primary btn-lg" onclick="requestIssue({{ $book->id }})">
                                <i class="fas fa-hand-holding-heart me-2"></i> Request to Issue
                            </button>
                        @else
                            <button class="btn btn-secondary btn-lg" disabled>
                                <i class="fas fa-ban me-2"></i> Not Available
                            </button>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Related Books -->
    @if($relatedBooks->count() > 0)
        <div class="card mt-4">
            <div class="card-header">
                <i class="fas fa-layer-group me-2"></i> Related Books
            </div>
            <div class="card-body">
                <div class="row">
                    @foreach($relatedBooks as $related)
                        <div class="col-md-3 col-sm-6 mb-3">
                            <div class="card h-100">
                                <div class="card-body text-center">
                                    <i class="fas fa-book fa-3x text-primary mb-2"></i>
                                    <h6 class="card-title text-truncate">{{ $related->title }}</h6>
                                    <p class="small text-muted">{{ Str::limit($related->author, 20) }}</p>
                                    <a href="{{ route('student.library.books.show', $related) }}" class="btn btn-sm btn-outline-primary">
                                        View Details
                                    </a>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    @endif
</div>

<!-- Issue Request Modal -->
<div class="modal fade" id="issueModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Request to Issue Book</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p>Are you sure you want to request this book?</p>
                <div id="bookInfo"></div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <form action="" method="POST" id="issueForm">
                    @csrf
                    <button type="submit" class="btn btn-primary">Confirm Request</button>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
    .book-cover-large {
        background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
        padding: 40px;
        border-radius: 15px;
    }
    .info-row {
        padding: 8px 0;
        border-bottom: 1px solid #f0f0f0;
    }
    .fa-6x {
        font-size: 6rem;
    }
</style>
@endpush

@push('scripts')
<script>
    function requestIssue(bookId) {
        var bookTitle = '{{ $book->title }}';
        var bookAuthor = '{{ $book->author }}';
        
        $('#bookInfo').html(`
            <div class="alert alert-info">
                <strong>Title:</strong> ${bookTitle}<br>
                <strong>Author:</strong> ${bookAuthor}
            </div>
        `);
        
        $('#issueForm').attr('action', '/student/library/request/' + bookId);
        $('#issueModal').modal('show');
    }
</script>
@endpush