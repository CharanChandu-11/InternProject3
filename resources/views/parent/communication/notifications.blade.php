{{-- resources/views/parent/communication/notifications.blade.php --}}
@extends('layouts.parent')

@section('title', 'Notifications')

@section('content')
<div class="animate-fadeInUp">
    <div class="card">
        <div class="card-header">
            <i class="fas fa-bell me-2"></i> Notifications
        </div>
        <div class="card-body">
            @if($notifications->count() > 0)
                <div class="list-group">
                    @foreach($notifications as $notification)
                        <div class="list-group-item {{ !$notification->is_read ? 'bg-light' : '' }}">
                            <div class="d-flex justify-content-between">
                                <h6 class="mb-1">{{ $notification->title }}</h6>
                                <small>{{ $notification->created_at->diffForHumans() }}</small>
                            </div>
                            <p class="mb-1">{{ $notification->message }}</p>
                            @if(!$notification->is_read)
                                <form action="{{ route('parent.notifications.read', $notification) }}" method="POST" class="d-inline">
                                    @csrf
                                    <button type="submit" class="btn btn-sm btn-primary">Mark as Read</button>
                                </form>
                            @endif
                        </div>
                    @endforeach
                </div>
                {{ $notifications->links() }}
            @else
                <div class="alert alert-info text-center">
                    <i class="fas fa-info-circle me-2"></i> No notifications.
                </div>
            @endif
        </div>
    </div>
</div>
@endsection