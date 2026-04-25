{{-- resources/views/super-admin/students/import.blade.php --}}
@extends('layouts.super-admin')

@section('title', 'Import Students')

@section('content')
<div class="animate-fadeInUp">
    <div class="card">
        <div class="card-header">
            <i class="fas fa-upload me-2"></i> Import Students
        </div>
        <div class="card-body">
            <div class="alert alert-info">
                <i class="fas fa-info-circle me-2"></i> 
                Please upload an Excel file (.xlsx or .csv) with the following columns:
                <ul class="mt-2 mb-0">
                    <li><strong>name</strong> - Student full name (required)</li>
                    <li><strong>email</strong> - Email address (required, unique)</li>
                    <li><strong>phone</strong> - Phone number (required)</li>
                    <li><strong>date_of_birth</strong> - Date of birth (YYYY-MM-DD)</li>
                    <li><strong>gender</strong> - Gender (male/female/other)</li>
                    <li><strong>admission_date</strong> - Admission date (YYYY-MM-DD)</li>
                    <li><strong>roll_number</strong> - Roll number (optional)</li>
                    <li><strong>previous_school</strong> - Previous school (optional)</li>
                    <li><strong>previous_grade</strong> - Previous grade (optional)</li>
                    <li><strong>address</strong> - Address (optional)</li>
                    <li><strong>blood_group</strong> - Blood group (optional)</li>
                </ul>
                <p class="mt-2 mb-0 text-danger">Note: Class, Section, and Academic Year will be selected from the form below for all imported students.</p>
            </div>
            
            <form action="{{ route('super-admin.students.import') }}" method="POST" enctype="multipart/form-data">
                @csrf
                
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Class <span class="text-danger">*</span></label>
                        <select name="class_id" class="form-control @error('class_id') is-invalid @enderror" id="classSelect" required>
                            <option value="">Select Class</option>
                            @foreach($classes as $class)
                                <option value="{{ $class->id }}">{{ $class->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Section <span class="text-danger">*</span></label>
                        <select name="section_id" class="form-control @error('section_id') is-invalid @enderror" id="sectionSelect" required>
                            <option value="">Select Section</option>
                        </select>
                    </div>
                    
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Academic Year <span class="text-danger">*</span></label>
                        <select name="academic_year_id" class="form-control @error('academic_year_id') is-invalid @enderror" required>
                            <option value="">Select Academic Year</option>
                            @foreach($academicYears as $year)
                                <option value="{{ $year->id }}">{{ $year->name }} @if($year->is_current) (Current) @endif</option>
                            @endforeach
                        </select>
                    </div>
                    
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Excel File <span class="text-danger">*</span></label>
                        <input type="file" name="file" class="form-control @error('file') is-invalid @enderror" accept=".xlsx,.csv" required>
                    </div>
                </div>
                
                <div class="mt-3">
                    <a href="{{ route('super-admin.students.export') }}" class="btn btn-sm btn-success">
                        <i class="fas fa-download me-1"></i> Download Sample Template
                    </a>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-upload me-1"></i> Import Students
                    </button>
                    <a href="{{ route('super-admin.students.index') }}" class="btn btn-secondary">
                        <i class="fas fa-times me-1"></i> Cancel
                    </a>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    $('#classSelect').change(function() {
        var classId = $(this).val();
        if (classId) {
            $.ajax({
                url: '{{ url("admin/sections/by-class") }}/' + classId,
                type: 'GET',
                success: function(data) {
                    var sectionSelect = $('#sectionSelect');
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