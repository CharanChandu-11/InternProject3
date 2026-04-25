{{-- resources/views/parent/teachers.blade.php --}}
@extends('layouts.parent')

@section('title', 'Teachers')

@section('content')
<div class="animate-fadeInUp">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <i class="fas fa-chalkboard-user me-2"></i> Teachers
                </div>
                <div class="card-body">
                    @if($teachers->count() > 0)
                        <div class="row">
                            @foreach($teachers as $teacher)
                                <div class="col-md-6 col-lg-4 mb-4">
                                    <div class="teacher-card">
                                        <div class="d-flex align-items-center gap-3 mb-3">
                                            <img src="{{ $teacher['profile_photo'] }}" alt="{{ $teacher['name'] }}" class="teacher-avatar">
                                            <div>
                                                <h5 class="mb-0">{{ $teacher['name'] }}</h5>
                                                <p class="text-muted mb-0 small">{{ $teacher['designation'] ?? 'Teacher' }}</p>
                                            </div>
                                        </div>
                                        <div class="mb-3">
                                            <p class="small text-muted mb-1"><i class="fas fa-envelope me-2"></i> {{ $teacher['email'] }}</p>
                                            <p class="small text-muted mb-0"><i class="fas fa-phone me-2"></i> {{ $teacher['phone'] ?? 'N/A' }}</p>
                                        </div>
                                        @if($teacher['last_message'])
                                            <div class="bg-light rounded-3 p-2 mb-3">
                                                <small class="text-muted">Last message:</small>
                                                <p class="small mb-0">{{ Str::limit($teacher['last_message']->message, 80) }}</p>
                                                <small class="text-muted">{{ $teacher['last_message']->created_at->diffForHumans() }}</small>
                                            </div>
                                        @endif
                                        <a href="{{ route('parent.messages.conversation', $teacher['id']) }}" class="btn btn-primary btn-sm w-100">
                                            <i class="fas fa-envelope me-2"></i> 
                                            Message {{ $teacher['name'] }}
                                            @if($teacher['unread_count'] > 0)
                                                <span class="badge bg-danger ms-2">{{ $teacher['unread_count'] }}</span>
                                            @endif
                                        </a>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <div class="text-center py-5">
                            <i class="fas fa-chalkboard-user fa-4x text-muted mb-3"></i>
                            <p class="text-muted">No teachers assigned to your children.</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
    .teacher-card {
        background: white;
        border-radius: 20px;
        padding: 20px;
        transition: all 0.3s;
        border: 1px solid var(--border);
        height: 100%;
    }
    
    .teacher-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 15px 40px rgba(0,0,0,0.1);
        border-color: var(--primary);
    }
    
    .teacher-avatar {
        width: 60px;
        height: 60px;
        border-radius: 50%;
        object-fit: cover;
        border: 2px solid var(--primary);
    }
</style>
@endpush