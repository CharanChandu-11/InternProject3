{{-- resources/views/parent/conversation.blade.php --}}
@extends('layouts.parent')

@section('title', 'Chat with ' . $teacher->name)

@section('content')
<div class="animate-fadeInUp">
    <div class="row">
        <div class="col-lg-8 mx-auto">
            <div class="card">
                <div class="card-header">
                    <div class="d-flex justify-content-between align-items-center">
                        <div class="d-flex align-items-center gap-2">
                            <img src="{{ $teacher->profile_photo_url }}" alt="{{ $teacher->name }}" style="width: 40px; height: 40px; border-radius: 50%; object-fit: cover;">
                            <div>
                                <h6 class="mb-0">{{ $teacher->name }}</h6>
                                <small class="text-muted">{{ $teacher->employee?->designation ?? 'Teacher' }}</small>
                            </div>
                        </div>
                        <a href="{{ route('parent.messages') }}" class="btn btn-sm btn-outline-secondary">
                            <i class="fas fa-arrow-left me-1"></i> Back
                        </a>
                    </div>
                </div>
                <div class="chat-container">
                    <div class="chat-messages" id="chatMessages">
                        @foreach($messages as $message)
                            <div class="message {{ $message->sender_id == Auth::id() ? 'sent' : 'received' }}">
                                <div class="message-bubble">
                                    {{ $message->message }}
                                    <span class="message-time">{{ $message->created_at->format('h:i A') }}</span>
                                </div>
                            </div>
                        @endforeach
                        <div id="scrollTarget"></div>
                    </div>
                    
                    <div class="chat-input">
                        <form action="{{ route('parent.messages.send') }}" method="POST" id="messageForm">
                            @csrf
                            <input type="hidden" name="receiver_id" value="{{ $teacher->id }}">
                            <div class="input-group">
                                <textarea name="message" class="form-control" rows="2" placeholder="Type your message..." required></textarea>
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-paper-plane"></i> Send
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    // Scroll to bottom on page load
    document.getElementById('scrollTarget').scrollIntoView({ behavior: 'smooth' });
    
    // Auto-submit form on Enter (but allow Shift+Enter for new line)
    document.getElementById('messageForm').addEventListener('keydown', function(e) {
        if (e.key === 'Enter' && !e.shiftKey) {
            e.preventDefault();
            this.submit();
        }
    });
    
    // Auto-refresh messages every 10 seconds
    setInterval(function() {
        location.reload();
    }, 10000);
</script>
@endpush

@push('styles')
<style>
    .chat-container {
        height: 500px;
        display: flex;
        flex-direction: column;
        background: #f8f9fa;
        overflow: hidden;
    }
    
    .chat-messages {
        flex: 1;
        overflow-y: auto;
        padding: 20px;
    }
    
    .message {
        margin-bottom: 20px;
        display: flex;
    }
    
    .message.sent {
        justify-content: flex-end;
    }
    
    .message.received {
        justify-content: flex-start;
    }
    
    .message-bubble {
        max-width: 70%;
        padding: 12px 16px;
        border-radius: 18px;
        position: relative;
    }
    
    .message.sent .message-bubble {
        background: linear-gradient(135deg, var(--primary) 0%, var(--secondary) 100%);
        color: white;
        border-bottom-right-radius: 4px;
    }
    
    .message.received .message-bubble {
        background: white;
        color: var(--dark);
        border-bottom-left-radius: 4px;
        box-shadow: 0 2px 5px rgba(0,0,0,0.05);
    }
    
    .message-time {
        font-size: 10px;
        opacity: 0.7;
        margin-top: 5px;
        display: block;
    }
    
    .message.sent .message-time {
        text-align: right;
    }
    
    .chat-input {
        padding: 15px;
        background: white;
        border-top: 1px solid var(--border);
    }
    
    .chat-input textarea {
        resize: none;
    }
</style>
@endpush