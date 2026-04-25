{{-- resources/views/super-admin/subjects/show.blade.php --}}
@extends('layouts.super-admin')

@section('title', 'Subject Details - ' . $subject->name)

@section('content')
<div class="animate-fadeInUp">
    <div class="card">
        <div class="card-header">
            <i class="fas fa-book me-2"></i> Subject Details: {{ $subject->name }}
            <div class="float-end">
                <a href="{{ route('super-admin.subjects.edit', $subject) }}" class="btn btn-sm btn-primary">
                    <i class="fas fa-edit me-1"></i> Edit
                </a>
                <a href="{{ route('super-admin.subjects.index') }}" class="btn btn-sm btn-secondary">
                    <i class="fas fa-arrow-left me-1"></i> Back
                </a>
            </div>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-6">
                    <div class="info-box">
                        <h6><i class="fas fa-tag me-2 text-primary"></i> Subject Name</h6>
                        <p>{{ $subject->name }}</p>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="info-box">
                        <h6><i class="fas fa-barcode me-2 text-primary"></i> Subject Code</h6>
                        <p><strong>{{ $subject->code }}</strong></p>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="info-box">
                        <h6><i class="fas fa-layer-group me-2 text-primary"></i> Subject Type</h6>
                        <p>
                            <span class="badge bg-{{ $subject->type == 'core' ? 'primary' : ($subject->type == 'elective' ? 'info' : ($subject->type == 'language' ? 'success' : 'warning')) }} fs-6">
                                {{ ucfirst($subject->type) }}
                            </span>
                        </p>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="info-box">
                        <h6><i class="fas fa-chalkboard me-2 text-primary"></i> Classes Assigned</h6>
                        <p>{{ $subject->classes()->count() }} class(es)</p>
                    </div>
                </div>
                <div class="col-md-12">
                    <div class="info-box">
                        <h6><i class="fas fa-align-left me-2 text-primary"></i> Description</h6>
                        <p>{{ $subject->description ?? 'No description provided.' }}</p>
                    </div>
                </div>
            </div>
            
            <!-- Assigned Classes -->
            <div class="row mt-4">
                <div class="col-md-12">
                    <h5 class="border-bottom pb-2">Classes Teaching This Subject</h5>
                    @if(count($assignedClasses) > 0)
                        <div class="table-responsive">
                            <table class="table table-sm">
                                <thead>
                                    <tr>
                                        <th>Class</th>
                                        <th>Academic Year</th>
                                        <th>Teacher</th>
                                        <th>Theory Marks</th>
                                        <th>Practical Marks</th>
                                        <th>Lab Required</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($assignedClasses as $assignment)
                                    <tr>
                                        <td>{{ $assignment['class']->name }}</td>
                                        <td>{{ $assignment['class']->academicYear->name ?? 'N/A' }}</td>
                                        <td>
                                            {{ $assignment['teacher']->name ?? 'Not Assigned' }}<br>
                                            <small class="text-muted">{{ $assignment['teacher']->employee->designation ?? 'Teacher' }}</small>
                                        </td>
                                        <td>{{ $assignment['theory_marks'] }}</td>
                                        <td>{{ $assignment['practical_marks'] }}</td>
                                        <td>
                                            @if($assignment['is_lab_required'])
                                                <span class="badge bg-success">Required</span>
                                            @else
                                                <span class="badge bg-secondary">Not Required</span>
                                            @endif
                                        </td>
                                        <td>
                                            <form action="{{ route('super-admin.subjects.remove-from-class', $assignment['class']->pivot->id) }}" method="POST" class="d-inline">
                                                @csrf
                                                @method('DELETE')
                                                <button type="button" class="btn btn-sm btn-danger delete-btn">Remove</button>
                                            </form>
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <p class="text-muted">This subject is not assigned to any class yet.</p>
                    @endif
                </div>
            </div>
            
            <!-- Assign to Class Form -->
            <div class="row mt-4">
                <div class="col-md-12">
                    <h5 class="border-bottom pb-2">Assign to Class</h5>
                    <form action="{{ route('super-admin.subjects.assign-to-class', $subject) }}" method="POST" class="row g-3">
                        @csrf
                        <div class="col-md-3">
                            <select name="class_id" class="form-select" required>
                                <option value="">Select Class</option>
                                @foreach($availableClasses as $class)
                                    <option value="{{ $class->id }}">{{ $class->name }} ({{ $class->academicYear->name ?? 'N/A' }})</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-3">
                            <select name="teacher_id" class="form-select" required>
                                <option value="">Select Teacher</option>
                                @foreach($teachers as $teacher)
                                    <option value="{{ $teacher->id }}">{{ $teacher->name }} ({{ $teacher->employee->designation ?? 'Teacher' }})</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-2">
                            <input type="number" name="theory_marks" class="form-control" placeholder="Theory Marks" value="100">
                        </div>
                        <div class="col-md-2">
                            <input type="number" name="practical_marks" class="form-control" placeholder="Practical Marks" value="0">
                        </div>
                        <div class="col-md-1">
                            <div class="form-check mt-2">
                                <input type="checkbox" name="is_lab_required" class="form-check-input" id="labRequired">
                                <label class="form-check-label" for="labRequired">Lab</label>
                            </div>
                        </div>
                        <div class="col-md-1">
                            <button type="submit" class="btn btn-success w-100">Assign</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
    .info-box {
        background: #f8f9fa;
        padding: 15px;
        border-radius: 8px;
        margin-bottom: 15px;
    }
    .info-box h6 {
        margin-bottom: 8px;
    }
    .info-box p {
        margin-bottom: 0;
        color: #6c757d;
    }
</style>
@endpush