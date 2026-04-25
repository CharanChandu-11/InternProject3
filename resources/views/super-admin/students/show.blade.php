{{-- resources/views/super-admin/students/show.blade.php --}}
@extends('layouts.super-admin')

@section('title', 'Student Details - ' . $student->user->name)

@section('content')
<div class="animate-fadeInUp">
    <div class="card">
        <div class="card-header">
            <i class="fas fa-user-graduate me-2"></i> Student Details: {{ $student->user->name }}
            <div class="float-end">
                <a href="{{ route('super-admin.students.edit', $student) }}" class="btn btn-sm btn-primary">
                    <i class="fas fa-edit me-1"></i> Edit
                </a>
                <a href="{{ route('super-admin.students.id-card', $student) }}" class="btn btn-sm btn-secondary" target="_blank">
                    <i class="fas fa-id-card me-1"></i> ID Card
                </a>
                <a href="{{ route('super-admin.students.index') }}" class="btn btn-sm btn-secondary">
                    <i class="fas fa-arrow-left me-1"></i> Back
                </a>
            </div>
        </div>
        <div class="card-body">
            <div class="row">
                <!-- Profile Section -->
                <div class="col-md-3 text-center">
                    <img src="{{ $student->user->profile_photo_url }}" alt="{{ $student->user->name }}" 
                         style="width: 150px; height: 150px; border-radius: 50%; object-fit: cover; border: 3px solid #4361ee;">
                    <h4 class="mt-3">{{ $student->user->name }}</h4>
                    <p class="text-muted">{{ $student->admission_number }}</p>
                    <span class="badge bg-{{ $student->user->is_active ? 'success' : 'danger' }} fs-6">
                        {{ $student->user->is_active ? 'Active' : 'Inactive' }}
                    </span>
                </div>
                
                <!-- Personal Details -->
                <div class="col-md-9">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="info-box">
                                <h6><i class="fas fa-envelope me-2 text-primary"></i> Email</h6>
                                <p>{{ $student->user->email }}</p>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="info-box">
                                <h6><i class="fas fa-phone me-2 text-primary"></i> Phone</h6>
                                <p>{{ $student->user->phone ?? 'N/A' }}</p>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="info-box">
                                <h6><i class="fas fa-calendar-alt me-2 text-primary"></i> Date of Birth</h6>
                                <p>{{ $student->user->profile?->date_of_birth?->format('F j, Y') ?? 'N/A' }}</p>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="info-box">
                                <h6><i class="fas fa-venus-mars me-2 text-primary"></i> Gender</h6>
                                <p>{{ ucfirst($student->user->profile?->gender ?? 'N/A') }}</p>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="info-box">
                                <h6><i class="fas fa-tint me-2 text-primary"></i> Blood Group</h6>
                                <p>{{ $student->user->profile?->blood_group ?? 'N/A' }}</p>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="info-box">
                                <h6><i class="fas fa-map-marker-alt me-2 text-primary"></i> Address</h6>
                                <p>{{ $student->user->address ?? 'N/A' }}</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Academic Details -->
            <div class="row mt-4">
                <div class="col-md-12">
                    <h5 class="border-bottom pb-2">Academic Information</h5>
                    <div class="row">
                        <div class="col-md-3">
                            <strong>Class:</strong> {{ $student->class->name ?? 'N/A' }}
                        </div>
                        <div class="col-md-3">
                            <strong>Section:</strong> {{ $student->section->name ?? 'N/A' }}
                        </div>
                        <div class="col-md-3">
                            <strong>Roll Number:</strong> {{ $student->roll_number ?? 'N/A' }}
                        </div>
                        <div class="col-md-3">
                            <strong>Academic Year:</strong> {{ $student->academicYear->name ?? 'N/A' }}
                        </div>
                        <div class="col-md-3">
                            <strong>Admission Date:</strong> {{ $student->admission_date->format('d-m-Y') }}
                        </div>
                        <div class="col-md-3">
                            <strong>Previous School:</strong> {{ $student->previous_school ?? 'N/A' }}
                        </div>
                        <div class="col-md-3">
                            <strong>Previous Grade:</strong> {{ $student->previous_grade ?? 'N/A' }}
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Parents Information -->
            <div class="row mt-4">
                <div class="col-md-12">
                    <h5 class="border-bottom pb-2">Parents Information</h5>
                    @if($student->parents->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-sm">
                                <thead>
                                    <tr><th>Name</th><th>Relationship</th><th>Email</th><th>Phone</th><th>Occupation</th></tr>
                                </thead>
                                <tbody>
                                    @foreach($student->parents as $parent)
                                    <tr>
                                        <td>{{ $parent->name }}</td>
                                        <td>{{ ucfirst($parent->pivot->relationship) }}</td>
                                        <td>{{ $parent->email }}</td>
                                        <td>{{ $parent->phone }}</td>
                                        <td>{{ $parent->parent?->occupation ?? 'N/A' }}</td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <p class="text-muted">No parents linked.</p>
                    @endif
                </div>
            </div>
            
            <!-- Statistics -->
            <div class="row mt-4">
                <div class="col-md-4">
                    <div class="card bg-light">
                        <div class="card-body text-center">
                            <h6>Attendance</h6>
                            <h3>{{ $attendanceSummary['percentage'] }}%</h3>
                            <small>{{ $attendanceSummary['present'] }} / {{ $attendanceSummary['total_days'] }} days present</small>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card bg-light">
                        <div class="card-body text-center">
                            <h6>Fee Status</h6>
                            <h3 class="text-{{ $feeSummary['due'] > 0 ? 'danger' : 'success' }}">
                                {{ $feeSummary['due'] > 0 ? 'Due' : 'Paid' }}
                            </h3>
                            <small>Due: {{ $feeSummary['due_formatted'] }}</small>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card bg-light">
                        <div class="card-body text-center">
                            <h6>Recent Performance</h6>
                            <h3>{{ $recentResults->avg('percentage') }}%</h3>
                            <small>Average of last {{ $recentResults->count() }} exams</small>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Promote Button -->
            <div class="row mt-3">
                <div class="col-md-12">
                    <button type="button" class="btn btn-warning" data-bs-toggle="modal" data-bs-target="#promoteModal">
                        <i class="fas fa-arrow-up me-1"></i> Promote to Next Class
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Promote Modal -->
<div class="modal fade" id="promoteModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="{{ route('super-admin.students.promote', $student) }}" method="POST">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title">Promote Student</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">New Class <span class="text-danger">*</span></label>
                        <select name="new_class_id" class="form-control" required>
                            <option value="">Select Class</option>
                            @foreach(\App\Models\Classes::where('numeric_name', '>', $student->class->numeric_name ?? 0)->get() as $class)
                                <option value="{{ $class->id }}">{{ $class->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">New Section <span class="text-danger">*</span></label>
                        <select name="new_section_id" class="form-control" required>
                            <option value="">Select Section</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">New Academic Year <span class="text-danger">*</span></label>
                        <select name="new_academic_year_id" class="form-control" required>
                            @foreach(\App\Models\AcademicYear::where('start_date', '>', now())->get() as $year)
                                <option value="{{ $year->id }}">{{ $year->name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Promote</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
    .info-box {
        background: #f8f9fa;
        padding: 12px;
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

@push('scripts')
<script>
    $('select[name="new_class_id"]').change(function() {
        var classId = $(this).val();
        if (classId) {
            $.ajax({
                url: '{{ url("admin/sections/by-class") }}/' + classId,
                type: 'GET',
                success: function(data) {
                    var sectionSelect = $('select[name="new_section_id"]');
                    sectionSelect.empty();
                    sectionSelect.append('<option value="">Select Section</option>');
                    $.each(data, function(key, section) {
                        sectionSelect.append('<option value="' + section.id + '">' + section.name + '</option>');
                    });
                }
            });
        }
    });
</script>
@endpush