{{-- resources/views/admin/transport-routes/students.blade.php --}}
@extends('layouts.admin')

@section('title', 'Students on ' . $transportRoute->route_name)

@section('content')
<div class="animate-fadeInUp">
    <div class="card shadow-sm border-0">
        <div class="card-header bg-gradient-primary text-white d-flex justify-content-between align-items-center">
            <h5 class="mb-0"><i class="fas fa-users me-2"></i> Students Allocated to {{ $transportRoute->route_name }}</h5>
            <a href="{{ route('admin.transport-routes.show', $transportRoute) }}" class="btn btn-sm btn-outline-light"><i class="fas fa-arrow-left"></i> Back to Route</a>
        </div>
        <div class="card-body">
            @if($students->count())
                <div class="table-responsive">
                    <table class="table table-hover align-middle">
                        <thead class="table-light">
                            <tr>
                                <th>Student Name</th>
                                <th>Admission No</th>
                                <th>Stop</th>
                                <th>Pickup Time</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($students as $studentTransport)
                            <tr>
                                <td>{{ $studentTransport->student->user->name ?? 'N/A' }}</td>
                                <td>{{ $studentTransport->student->admission_number ?? 'N/A' }}</td>
                                <td>{{ $studentTransport->stop->stop_name ?? 'N/A' }}</td>
                                <td>{{ optional($studentTransport->stop->pickup_time)->format('h:i A') ?? '-' }}</td>
                                <td>
                                    <span class="badge {{ $studentTransport->is_active ? 'bg-success' : 'bg-secondary' }}">
                                        {{ $studentTransport->is_active ? 'Active' : 'Inactive' }}
                                    </span>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                {{ $students->links() }}
            @else
                <div class="alert alert-info text-center py-5">
                    <i class="fas fa-info-circle fa-3x mb-3 d-block"></i>
                    <h5>No students allocated to this route yet.</h5>
                </div>
            @endif
        </div>
    </div>
</div>
@endsection