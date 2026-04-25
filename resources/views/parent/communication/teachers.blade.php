{{-- resources/views/parent/communication/teachers.blade.php --}}
@extends('layouts.parent')

@section('title', 'Teachers')

@section('content')
<div class="animate-fadeInUp">
    <div class="card">
        <div class="card-header">
            <i class="fas fa-chalkboard-user me-2"></i> Teachers
        </div>
        <div class="card-body">
            @if($teachers->count() > 0)
                <div class="row">
                    @foreach($teachers as $item)
                        <div class="col-md-6 col-lg-4 mb-4">
                            <div class="card h-100">
                                <div class="card-body text-center">
                                    <img src="{{ $item['teacher']->profile_photo_url }}" alt="Photo" 
                                         class="rounded-circle mb-3" style="width: 100px; height: 100px; object-fit: cover;">
                                    <h5>{{ $item['teacher']->name }}</h5>
                                    <p class="text-muted">{{ $item['teacher']->employee?->designation ?? 'Teacher' }}</p>
                                    <p class="small">{{ $item['teacher']->employee?->department ?? '' }}</p>
                                    @if($item['unread_count'] > 0)
                                        <span class="badge bg-danger mb-2">{{ $item['unread_count'] }} unread</span>
                                    @endif
                                    <a href="{{ route('parent.messages.conversation', $item['teacher']) }}" class="btn btn-primary w-100">
                                        <i class="fas fa-envelope me-1"></i> Message
                                    </a>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            @else
                <div class="alert alert-info text-center">
                    <i class="fas fa-info-circle me-2"></i> No teachers found.
                </div>
            @endif
        </div>
    </div>
</div>
@endsection