{{-- resources/views/student/library-show.blade.php --}}
@extends('layouts.student')

@section('title', 'Book Details')

@section('content')
<div class="row">
    <div class="col-lg-8 mx-auto">
        <div class="card">
            <div class="card-header">
                <i class="fas fa-book me-2"></i> {{ $book->title }}
                <a href="{{ route('student.library.books') }}" class="btn btn-sm btn-secondary float-end">Back to Library</a>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-4 text-center">
                        <i class="fas fa-book fa-5x text-primary"></i>
                    </div>
                    <div class="col-md-8">
                        <p><strong>Author:</strong> {{ $book->author }}</p>
                        <p><strong>ISBN:</strong> {{ $book->isbn }}</p>
                        <p><strong>Publisher:</strong> {{ $book->publisher ?? 'N/A' }}</p>
                        <p><strong>Publication Year:</strong> {{ $book->publication_year ?? 'N/A' }}</p>
                        <p><strong>Category:</strong> {{ ucfirst($book->category) }}</p>
                        <p><strong>Shelf Location:</strong> {{ $book->shelf_location ?? 'N/A' }}</p>
                        <p><strong>Availability:</strong>
                            @if($book->available_quantity > 0)
                                <span class="badge bg-success">{{ $book->available_quantity }} copy/copies available</span>
                            @else
                                <span class="badge bg-danger">Currently Unavailable</span>
                            @endif
                        </p>
                        @if($book->description)
                            <p><strong>Description:</strong><br>{{ $book->description }}</p>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection