{{-- resources/views/parent/children/index.blade.php --}}
@extends('layouts.parent')

@section('title', 'My Children')

@section('content')
<div class="animate-fadeInUp">
    <div class="card">
        <div class="card-header">
            <i class="fas fa-child me-2"></i> My Children
        </div>
        <div class="card-body">
            @if($children->count() > 0)
                <div class="row">
                    @foreach($children as $child)
                        <div class="col-md-6 col-lg-4 mb-4">
                            <div class="card h-100">
                                <div class="card-body text-center">
                                    <img src="{{ $child->user->profile_photo_url }}" alt="Photo" 
                                         class="rounded-circle mb-3" style="width: 100px; height: 100px; object-fit: cover;">
                                    <h5>{{ $child->user->name }}</h5>
                                    <p class="text-muted">{{ $child->admission_number }}</p>
                                    <p><strong>Class:</strong> {{ $child->class_name }}</p>
                                    <p><strong>Section:</strong> {{ $child->section_name }}</p>
                                    <p><strong>Roll No:</strong> {{ $child->roll_number ?? 'N/A' }}</p>
                                    <a href="{{ route('parent.children.show', $child) }}" class="btn btn-primary w-100">
                                        <i class="fas fa-eye me-1"></i> View Details
                                    </a>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            @else
                <div class="alert alert-info text-center">
                    <i class="fas fa-info-circle me-2"></i> No children found.
                </div>
            @endif
        </div>
    </div>
</div>
@endsection