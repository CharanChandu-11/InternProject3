{{-- resources/views/parent/messages.blade.php --}}
@extends('layouts.parent')

@section('title', 'Messages')

@section('content')
<div class="animate-fadeInUp">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <i class="fas fa-envelope me-2"></i> Conversations
                </div>
                <div class="card-body">
                    @if(count($formattedConversations) > 0)
                        @foreach($formattedConversations as $conv)
                            <div class="conversation-card" onclick="window.location='{{ route('parent.messages.conversation', $conv['user']['id']) }}'">
                                <div class="d-flex align-items-center gap-3">
                                    <img src="{{ $conv['user']['profile_photo'] }}" alt="{{ $conv['user']['name'] }}" class="conversation-avatar">
                                    <div class="flex-grow-1">
                                        <div class="d-flex justify-content-between align-items-center">
                                            <h6 class="mb-0">{{ $conv['user']['name'] }}</h6>
                                            <small class="text-muted">{{ $conv['last_message']['created_at'] }}</small>
                                        </div>
                                        <p class="text-muted small mb-0">{{ $conv['user']['designation'] ?? 'Teacher' }}</p>
                                        <p class="mb-0 small mt-1">
                                            @if($conv['last_message']['is_from_me'])
                                                <i class="fas fa-reply me-1 text-muted"></i> You:
                                            @endif
                                            {{ Str::limit($conv['last_message']['message'], 60) }}
                                        </p>
                                    </div>
                                    @if($conv['unread_count'] > 0)
                                        <span class="badge bg-danger rounded-pill">{{ $conv['unread_count'] }}</span>
                                    @endif
                                    <i class="fas fa-chevron-right text-muted"></i>
                                </div>
                            </div>
                        @endforeach
                    @else
                        <div class="text-center py-5">
                            <i class="fas fa-envelope fa-4x text-muted mb-3"></i>
                            <p class="text-muted">No conversations yet.</p>
                            <a href="{{ route('parent.teachers') }}" class="btn btn-primary">Message a Teacher</a>
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
    .conversation-card {
        background: white;
        border-radius: 15px;
        padding: 15px;
        margin-bottom: 10px;
        transition: all 0.3s;
        border: 1px solid var(--border);
        cursor: pointer;
    }
    
    .conversation-card:hover {
        transform: translateX(5px);
        background: #f8f9fa;
        border-color: var(--primary);
    }
    
    .conversation-avatar {
        width: 50px;
        height: 50px;
        border-radius: 50%;
        object-fit: cover;
    }
</style>
@endpush