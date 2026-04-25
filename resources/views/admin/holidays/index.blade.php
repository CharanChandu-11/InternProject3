{{-- resources/views/admin/holidays/index.blade.php --}}
@extends('layouts.admin')

@section('title', 'Holiday Management')

@section('content')
<div class="animate-fadeInUp">
    <div class="card shadow-sm border-0">
        <div class="card-header bg-gradient-primary text-white">
            <i class="fas fa-gift me-2"></i> Holiday Management
            <div class="float-end">
                <a href="{{ route('admin.holidays.create') }}" class="btn btn-sm btn-light me-2">
                    <i class="fas fa-plus me-1"></i> Add Holiday
                </a>
                <a href="{{ route('admin.calendar.index') }}" class="btn btn-sm btn-outline-light">
                    <i class="fas fa-calendar-alt me-1"></i> View Calendar
                </a>
            </div>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered datatable">
                    <thead class="table-light">
                        <tr>
                            <th>Holiday Name</th>
                            <th>Date</th>
                            <th>Day</th>
                            <th>Type</th>
                            <th>Status</th>
                            <th style="width: 150px">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($holidays as $holiday)
                        <tr>
                            <td>
                                <strong>{{ $holiday->name }}</strong>
                                @if($holiday->description)
                                    <br><small class="text-muted">{{ Str::limit($holiday->description, 60) }}</small>
                                @endif
                             </span></td>
                            <td>
                                {{ $holiday->date->format('d-m-Y') }}<br>
                                <small class="text-muted">{{ $holiday->date->format('l') }}</small>
                             </span></td>
                            <td>{{ $holiday->date->format('l') }}</span></td>
                            <td>
                                <span class="badge bg-{{ $holiday->type == 'public' ? 'danger' : ($holiday->type == 'national' ? 'warning' : 'info') }}">
                                    {{ \App\Models\Holiday::TYPES[$holiday->type] ?? $holiday->type }}
                                </span>
                             </span></td>
                            <td>
                                <span class="badge bg-{{ $holiday->is_active ? 'success' : 'secondary' }}">
                                    {{ $holiday->is_active ? 'Active' : 'Inactive' }}
                                </span>
                             </span></td>
                            <td>
                                <a href="{{ route('admin.holidays.edit', $holiday) }}" class="btn btn-sm btn-primary">
                                    <i class="fas fa-edit"></i>
                                </a>
                                <form action="{{ route('admin.holidays.toggle-status', $holiday) }}" method="POST" class="d-inline">
                                    @csrf
                                    <button type="submit" class="btn btn-sm btn-{{ $holiday->is_active ? 'warning' : 'success' }}" 
                                            title="{{ $holiday->is_active ? 'Deactivate' : 'Activate' }}">
                                        <i class="fas fa-{{ $holiday->is_active ? 'ban' : 'check' }}"></i>
                                    </button>
                                </form>
                                <form action="{{ route('admin.holidays.destroy', $holiday) }}" method="POST" class="d-inline delete-form">
                                    @csrf @method('DELETE')
                                    <button type="button" class="btn btn-sm btn-danger delete-btn">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </form>
                             </span></td>
                        </tr>
                        @endforeach
                    </tbody>
                </div>
            </div>
            {{ $holidays->links() }}
        </div>
    </div>
</div>
@endsection