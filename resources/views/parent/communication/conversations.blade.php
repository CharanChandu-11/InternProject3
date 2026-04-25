{{-- resources/views/parent/communication/conversations.blade.php --}}
@extends('layouts.parent')

@section('title', 'Messages')

@section('content')
<div class="animate-fadeInUp">
    <div class="card">
        <div class="card-header">
            <i class="fas fa-envelope me-2"></i> Conversations
        </div>
        <div class="card-body">
            @if($formattedConversations->count() > 0)
                <div class="list-group">
                    @foreach($formattedConversations as $conv)
                        <a href="{{ route('parent.messages.conversation', $conv['teacher']) }}" class="list-group-item list-group-item-action">
                            <div class="d-flex justify-content-between align-items-center">
                                <div class="d-flex align-items-center">
                                    <img src="{{ $conv['teacher']->profile_photo_url }}" alt="Photo" 
                                         class="rounded-circle me-3" style="width: 50px; height: 50px; object-fit: cover;">
                                    <div>
                                        <h6 class="mb-0">{{ $conv['teacher']->name }}</h6>
                                        <small class="text-muted">{{ $conv['last_message']->created_at->diffForHumans() }}</small>
                                        <p class="mb-0 small">{{ Str::limit($conv['last_message']->message, 50) }}</p>
                                    </div>
                                </div>
                                @if($conv['unread_count'] > 0)
                                    <span class="badge bg-danger rounded-pill">{{ $conv['unread_count'] }}</span>
                                @endif
                            </div>
                        </a>
                    @endforeach
                </div>
            @else
                <div class="alert alert-info text-center">
                    <i class="fas fa-info-circle me-2"></i> No conversations yet.
                </div>
            @endif
        </div>
    </div>
</div>
@endsection