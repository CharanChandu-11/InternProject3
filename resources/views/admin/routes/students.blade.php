{{-- resources/views/admin/transport/routes/students.blade.php --}}
@extends('layouts.admin')

@section('title', 'Route Students')

@section('content')
<div class="animate-fadeInUp">
    <div class="card">
        <div class="card-header">
            <i class="fas fa-users me-2"></i> Students on Route: {{ $transportRoute->route_name }}
            <div class="float-end">
                <a href="{{ route('admin.transport-routes.index') }}" class="btn btn-sm btn-secondary">
                    <i class="fas fa-arrow-left me-1"></i> Back to Routes
                </a>
            </div>
        </div>
        <div class="card-body">
            <div class="alert alert-info">
                <i class="fas fa-info-circle me-2"></i> Route: {{ $transportRoute->route_number }} - {{ $transportRoute->route_name }}
            </div>
            
            <div class="table-responsive">
                <table class="table table-bordered datatable">
                    <thead>
                        <tr>
                            <th>Admission No</th>
                            <th>Student Name</th>
                            <th>Class</th>
                            <th>Section</th>
                            <th>Stop Name</th>
                            <th>Pickup Time</th>
                            <th>Drop Time</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($students as $studentTransport)
                        <tr>
                            <td>{{ $studentTransport->student->admission_number }}</td>
                            <td>{{ $studentTransport->student->user->name }}</td>
                            <td>{{ $studentTransport->student->class->name }}</td>
                            <td>{{ $studentTransport->student->section->name }}</td>
                            <td>{{ $studentTransport->stop->stop_name }}</td>
                            <td>{{ \Carbon\Carbon::parse($studentTransport->stop->pickup_time)->format('h:i A') }}</td>
                            <td>{{ \Carbon\Carbon::parse($studentTransport->stop->drop_time)->format('h:i A') }}</td>
                            <td>
                                <span class="badge bg-{{ $studentTransport->is_active ? 'success' : 'secondary' }}">
                                    {{ $studentTransport->is_active ? 'Active' : 'Inactive' }}
                                </span>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="8" class="text-center text-muted">No students assigned to this route.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection