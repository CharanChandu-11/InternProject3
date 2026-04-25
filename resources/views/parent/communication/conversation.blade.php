{{-- resources/views/parent/communication/conversation.blade.php --}}
@extends('layouts.parent')

@section('title', 'Conversation with ' . $teacher->name)

@section('content')
<div class="animate-fadeInUp">
    <div class="card">
        <div class="card-header">
            <i class="fas fa-envelope me-2"></i> Conversation with {{ $teacher->name }}
            <div class="float-end">
                <a href="{{ route('parent.messages') }}" class="btn btn-sm btn-secondary">
                    <i class="fas fa-arrow-left me-1"></i> Back
                </a>
            </div>
        </div>
        <div class="card-body">
            <div class="chat-container" style="height: 400px; overflow-y: auto; border: 1px solid #e2e8f0; border-radius: 10px; padding: 15px; margin-bottom: 20px;">
                @foreach($messages as $message)
                    <div class="mb-3 {{ $message->sender_id == Auth::id() ? 'text-end' : '' }}">
                        <div class="d-inline-block p-3 rounded {{ $message->sender_id == Auth::id() ? 'bg-primary text-white' : 'bg-light' }}" style="max-width: 70%;">
                            <p class="mb-0">{{ $message->message }}</p>
                            <small class="opacity-75">{{ $message->created_at->format('h:i A, d M Y') }}</small>
                        </div>
                    </div>
                @endforeach
            </div>
            
            <form action="{{ route('parent.messages.send') }}" method="POST">
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
@endsection

@push('scripts')
<script>
    // Scroll chat to bottom
    const chatContainer = document.querySelector('.chat-container');
    if (chatContainer) {
        chatContainer.scrollTop = chatContainer.scrollHeight;
    }
</script>
@endpush