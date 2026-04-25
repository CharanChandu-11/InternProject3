{{-- resources/views/admin/events/index.blade.php --}}
@extends('layouts.admin')

@section('title', 'Events')

@section('content')
<div class="animate-fadeInUp">
    <div class="card">
        <div class="card-header">
            <i class="fas fa-calendar-alt me-2"></i> Event Management
            <div class="float-end">
                <a href="{{ route('admin.events.create') }}" class="btn btn-sm btn-primary">
                    <i class="fas fa-plus me-1"></i> Add Event
                </a>
            </div>
        </div>
        <div class="card-body">
            <form method="GET" class="mb-4">
                <div class="row g-3">
                    <div class="col-md-3">
                        <select name="type" class="form-select">
                            <option value="">All Types</option>
                            @foreach($eventTypes as $type)
                                <option value="{{ $type }}" {{ request('type') == $type ? 'selected' : '' }}>
                                    {{ ucfirst(str_replace('_', ' ', $type)) }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-3">
                        <select name="status" class="form-select">
                            <option value="">All Status</option>
                            <option value="upcoming" {{ request('status') == 'upcoming' ? 'selected' : '' }}>Upcoming</option>
                            <option value="ongoing" {{ request('status') == 'ongoing' ? 'selected' : '' }}>Ongoing</option>
                            <option value="completed" {{ request('status') == 'completed' ? 'selected' : '' }}>Completed</option>
                        </select>
                    </div>
                    <div class="col-md-4">
                        <input type="text" name="search" class="form-control" placeholder="Search by title, venue..." value="{{ request('search') }}">
                    </div>
                    <div class="col-md-2">
                        <button type="submit" class="btn btn-primary w-100">Filter</button>
                    </div>
                </div>
            </form>
            
            <div class="table-responsive">
                <table class="table table-bordered datatable">
                    <thead>
                        <tr>
                            <th>Title</th>
                            <th>Type</th>
                            <th>Dates</th>
                            <th>Venue</th>
                            <th>Audience</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($events as $event)
                        <tr>
                            <td class="fw-bold">{{ Str::limit($event->title, 40) }}</td>
                            <td>
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
                                <span class="badge bg-{{ $typeColors[$event->type] ?? 'secondary' }}">
                                    {{ ucfirst(str_replace('_', ' ', $event->type)) }}
                                </span>
                            </td>
                            <td>
                                <div>{{ $event->start_date->format('M d, Y') }}</div>
                                @if($event->start_date != $event->end_date)
                                    <small class="text-muted">to {{ $event->end_date->format('M d, Y') }}</small>
                                @endif
                                @if($event->start_time)
                                    <small class="text-muted d-block">{{ \Carbon\Carbon::parse($event->start_time)->format('h:i A') }}</small>
                                @endif
                            </td>
                            <td>{{ Str::limit($event->venue, 30) }}</td>
                            <td>
                                <span class="badge bg-info">{{ ucfirst($event->audience) }}</span>
                            </td>
                            <td>
                                @php
                                    $now = now();
                                    $status = 'upcoming';
                                    $statusColor = 'info';
                                    
                                    if ($event->start_date <= $now && $event->end_date >= $now) {
                                        $status = 'ongoing';
                                        $statusColor = 'success';
                                    } elseif ($event->end_date < $now) {
                                        $status = 'completed';
                                        $statusColor = 'secondary';
                                    }
                                @endphp
                                <span class="badge bg-{{ $statusColor }}">{{ ucfirst($status) }}</span>
                            </td>
                            <td>
                                <a href="{{ route('admin.events.show', $event) }}" class="btn btn-sm btn-info">
                                    <i class="fas fa-eye"></i>
                                </a>
                                <a href="{{ route('admin.events.edit', $event) }}" class="btn btn-sm btn-primary">
                                    <i class="fas fa-edit"></i>
                                </a>
                                <form action="{{ route('admin.events.destroy', $event) }}" method="POST" class="d-inline">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('Delete this event?')">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </form>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="7" class="text-center text-muted py-4">No events found.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            
            {{ $events->links() }}
        </div>
    </div>
</div>
@endsection