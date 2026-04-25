{{-- resources/views/admin/events/show.blade.php --}}
@extends('layouts.admin')

@section('title', $event->title)

@section('content')
<div class="animate-fadeInUp">
    <div class="card">
        <div class="card-header">
            <i class="fas fa-calendar-alt me-2"></i> Event Details
            <div class="float-end">
                <a href="{{ route('admin.events.edit', $event) }}" class="btn btn-sm btn-primary">
                    <i class="fas fa-edit me-1"></i> Edit
                </a>
            </div>
        </div>
        <div class="card-body">
            <div class="row">
                @if($event->image)
                    <div class="col-md-4">
                        <img src="{{ Storage::url($event->image) }}" alt="{{ $event->title }}" class="img-fluid rounded mb-3">
                    </div>
                    <div class="col-md-8">
                @else
                    <div class="col-md-12">
                @endif
                    <h2 class="mb-3">{{ $event->title }}</h2>
                    
                    <div class="row mb-4">
                        <div class="col-md-6">
                            <div class="info-box p-3 bg-light rounded mb-3">
                                <h6><i class="fas fa-tag me-2 text-primary"></i> Type</h6>
                                @php
                                    $typeColors = [
                                        'academic' => 'primary',
                                        'cultural' => 'info',
                                        'sports' => 'success',
                                        'meeting' => 'warning',
                                        'field_trip' => 'secondary',
                                        'holiday' => 'dark',
                                    ];
                                @endphp
                                <span class="badge bg-{{ $typeColors[$event->type] ?? 'secondary' }} fs-6 px-3 py-2">
                                    {{ ucfirst(str_replace('_', ' ', $event->type)) }}
                                </span>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="info-box p-3 bg-light rounded mb-3">
                                <h6><i class="fas fa-users me-2 text-primary"></i> Audience</h6>
                                <span class="badge bg-info fs-6 px-3 py-2">{{ ucfirst($event->audience) }}</span>
                            </div>
                        </div>
                    </div>

                    <div class="row mb-4">
                        <div class="col-md-6">
                            <div class="info-box p-3 bg-light rounded">
                                <h6><i class="fas fa-calendar me-2 text-primary"></i> Dates</h6>
                                <p class="mb-1"><strong>Start:</strong> {{ $event->start_date->format('F j, Y') }}</p>
                                <p class="mb-1"><strong>End:</strong> {{ $event->end_date->format('F j, Y') }}</p>
                                @if($event->start_time)
                                    <p class="mb-1"><strong>Time:</strong> {{ \Carbon\Carbon::parse($event->start_time)->format('h:i A') }} - {{ \Carbon\Carbon::parse($event->end_time)->format('h:i A') }}</p>
                                @endif
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="info-box p-3 bg-light rounded">
                                <h6><i class="fas fa-map-marker-alt me-2 text-primary"></i> Venue</h6>
                                <p class="mb-0">{{ $event->venue }}</p>
                            </div>
                        </div>
                    </div>

                    <div class="card mt-3">
                        <div class="card-header bg-light">
                            <h6 class="mb-0"><i class="fas fa-align-left me-2"></i> Description</h6>
                        </div>
                        <div class="card-body">
                            {!! nl2br(e($event->description)) !!}
                        </div>
                    </div>
                </div>
            </div>

            <div class="mt-4 d-flex justify-content-between">
                <a href="{{ route('admin.events.index') }}" class="btn btn-secondary">
                    <i class="fas fa-arrow-left me-1"></i> Back to List
                </a>
                <form action="{{ route('admin.events.destroy', $event) }}" method="POST" class="d-inline">
                    @csrf @method('DELETE')
                    <button type="submit" class="btn btn-danger" onclick="return confirm('Delete this event?')">
                        <i class="fas fa-trash me-1"></i> Delete Event
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection