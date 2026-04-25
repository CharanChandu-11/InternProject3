{{-- resources/views/admin/students/edit.blade.php --}}
@extends('layouts.admin')

@section('title', 'Edit Student - ' . $student->user->name)

@section('content')
<div class="animate-fadeInUp">
    <div class="row">
        <div class="col-lg-10 mx-auto">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <div>
                        <i class="fas fa-edit me-2"></i> Edit Student: {{ $student->user->name }}
                    </div>
                    <a href="{{ route('admin.students.show', $student) }}" class="btn btn-sm btn-outline-secondary">
                        <i class="fas fa-arrow-left me-1"></i> Back to Profile
                    </a>
                </div>
                <div class="card-body">
                    <form action="{{ route('admin.students.update', $student) }}" method="POST">
                        @csrf
                        @method('PUT')
                        
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Full Name <span class="text-danger">*</span></label>
                                <input type="text" name="name" class="form-control" value="{{ old('name', $student->user->name) }}" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Email <span class="text-danger">*</span></label>
                                <input type="email" name="email" class="form-control" value="{{ old('email', $student->user->email) }}" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Phone <span class="text-danger">*</span></label>
                                <input type="text" name="phone" class="form-control" value="{{ old('phone', $student->user->phone) }}" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Date of Birth <span class="text-danger">*</span></label>
                                <input type="date" name="date_of_birth" class="form-control" value="{{ old('date_of_birth', $student->user->profile?->date_of_birth?->format('Y-m-d')) }}" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Gender <span class="text-danger">*</span></label>
                                <select name="gender" class="form-select" required>
                                    <option value="male" {{ old('gender', $student->user->profile?->gender) == 'male' ? 'selected' : '' }}>Male</option>
                                    <option value="female" {{ old('gender', $student->user->profile?->gender) == 'female' ? 'selected' : '' }}>Female</option>
                                    <option value="other" {{ old('gender', $student->user->profile?->gender) == 'other' ? 'selected' : '' }}>Other</option>
                                </select>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Blood Group</label>
                                <select name="blood_group" class="form-select">
                                    <option value="">Select Blood Group</option>
                                    @foreach(['A+', 'A-', 'B+', 'B-', 'AB+', 'AB-', 'O+', 'O-'] as $bg)
                                        <option value="{{ $bg }}" {{ old('blood_group', $student->user->profile?->blood_group) == $bg ? 'selected' : '' }}>
                                            {{ $bg }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Class <span class="text-danger">*</span></label>
                                <select name="class_id" class="form-select" id="classSelect" required>
                                    <option value="">Select Class</option>
                                    @foreach($classes as $class)
                                        <option value="{{ $class->id }}" {{ old('class_id', $student->class_id) == $class->id ? 'selected' : '' }}>
                                            {{ $class->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Section <span class="text-danger">*</span></label>
                                <select name="section_id" class="form-select" id="sectionSelect" required>
                                    <option value="">Select Section</option>
                                    @foreach($student->class->sections as $section)
                                        <option value="{{ $section->id }}" {{ old('section_id', $student->section_id) == $section->id ? 'selected' : '' }}>
                                            {{ $section->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Roll Number</label>
                                <input type="text" name="roll_number" class="form-control" value="{{ old('roll_number', $student->roll_number) }}">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Academic Year</label>
                                <select name="academic_year_id" class="form-select" required>
                                    @foreach($academicYears as $year)
                                        <option value="{{ $year->id }}" {{ old('academic_year_id', $student->academic_year_id) == $year->id ? 'selected' : '' }}>
                                            {{ $year->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-12 mb-3">
                                <label class="form-label">Address</label>
                                <textarea name="address" class="form-control" rows="3">{{ old('address', $student->user->address) }}</textarea>
                            </div>
                            <div class="col-12 mb-3">
                                <label class="form-label">Parents {{ implode(', ', $selectedParents) }}</label>
                                <select name="parent_ids[]" class="form-select select2" multiple>
                                    @foreach($parents as $parent)
                                        <option value="{{ $parent->id }}" {{ in_array($parent->id, $selectedParents) ? 'selected' : '' }}>
                                            {{ $parent->name }} ({{ $parent->email }}) | ({{ $parent->phone }}) - {{ $parent->parent->parent_type ?? 'Parent' }}
                                        </option>
                                    @endforeach
                                </select>
                                <small class="text-muted">Hold Ctrl to select multiple</small>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Status</label>
                                <select name="status" class="form-select">
                                    <option value="1" {{ $student->user->is_active ? 'selected' : '' }}>Active</option>
                                    <option value="0" {{ !$student->user->is_active ? 'selected' : '' }}>Inactive</option>
                                </select>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">New Password</label>
                                <input type="password" name="password" class="form-control">
                                <small class="text-muted">Leave blank to keep current password</small>
                            </div>
                        </div>
                        
                        <div class="text-center">
                            <button type="submit" class="btn btn-primary btn-lg px-5">
                                <i class="fas fa-save me-2"></i> Update Student
                            </button>
                            <a href="{{ route('admin.students.show', $student) }}" class="btn btn-secondary btn-lg px-5 ms-2">Cancel</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    // Update sections when class changes
    const sectionsByClass = @json($classes->mapWithKeys(function($class) {
        return [$class->id => $class->sections->map(function($section) {
            return ['id' => $section->id, 'name' => $section->name];
        })];
    }));
    
    document.getElementById('classSelect').addEventListener('change', function() {
        const classId = this.value;
        const sectionSelect = document.getElementById('sectionSelect');
        const currentSection = '{{ $student->section_id }}';
        
        sectionSelect.innerHTML = '<option value="">Select Section</option>';
        
        if (classId && sectionsByClass[classId]) {
            sectionsByClass[classId].forEach(section => {
                const option = document.createElement('option');
                option.value = section.id;
                option.textContent = section.name;
                if (section.id == currentSection) {
                    option.selected = true;
                }
                sectionSelect.appendChild(option);
            });
        }
    });
</script>
@endpush