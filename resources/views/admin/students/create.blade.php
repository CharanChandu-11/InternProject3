{{-- resources/views/admin/students/create.blade.php --}}
@extends('layouts.admin')

@section('title', 'Add New Student')

@section('content')
<div class="animate-fadeInUp">
    <div class="row">
        <div class="col-lg-10 mx-auto">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <div>
                        <i class="fas fa-plus-circle me-2"></i> Add New Student
                    </div>
                    <a href="{{ route('admin.students.index') }}" class="btn btn-sm btn-outline-secondary">
                        <i class="fas fa-arrow-left me-1"></i> Back to List
                    </a>
                </div>
                <div class="card-body">
                    <form action="{{ route('admin.students.store') }}" method="POST" id="studentForm">
                        @csrf
                        
                        <!-- Progress Steps -->
                        <div class="steps-progress mb-5">
                            <div class="step active" data-step="1">
                                <div class="step-circle">1</div>
                                <div class="step-label">Personal Info</div>
                            </div>
                            <div class="step" data-step="2">
                                <div class="step-circle">2</div>
                                <div class="step-label">Academic Info</div>
                            </div>
                            <div class="step" data-step="3">
                                <div class="step-circle">3</div>
                                <div class="step-label">Parent Info</div>
                            </div>
                            <div class="step" data-step="4">
                                <div class="step-circle">4</div>
                                <div class="step-label">Review</div>
                            </div>
                        </div>
                        
                        <!-- Step 1: Personal Information -->
                        <div class="step-content active" data-step="1">
                            <div class="section-title mb-4">
                                <i class="fas fa-user-graduate me-2 text-primary"></i>
                                <h5 class="d-inline">Personal Information</h5>
                            </div>
                            
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Full Name <span class="text-danger">*</span></label>
                                    <input type="text" name="name" class="form-control" required>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Email <span class="text-danger">*</span></label>
                                    <input type="email" name="email" class="form-control" required>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Phone <span class="text-danger">*</span></label>
                                    <input type="text" name="phone" class="form-control" required>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Date of Birth <span class="text-danger">*</span></label>
                                    <input type="date" name="date_of_birth" class="form-control" required>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Gender <span class="text-danger">*</span></label>
                                    <select name="gender" class="form-select" required>
                                        <option value="">Select Gender</option>
                                        <option value="male">Male</option>
                                        <option value="female">Female</option>
                                        <option value="other">Other</option>
                                    </select>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Blood Group</label>
                                    <select name="blood_group" class="form-select">
                                        <option value="">Select Blood Group</option>
                                        <option value="A+">A+</option>
                                        <option value="A-">A-</option>
                                        <option value="B+">B+</option>
                                        <option value="B-">B-</option>
                                        <option value="AB+">AB+</option>
                                        <option value="AB-">AB-</option>
                                        <option value="O+">O+</option>
                                        <option value="O-">O-</option>
                                    </select>
                                </div>
                                <div class="col-12 mb-3">
                                    <label class="form-label">Address</label>
                                    <textarea name="address" class="form-control" rows="3"></textarea>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Step 2: Academic Information -->
                        <div class="step-content" data-step="2">
                            <div class="section-title mb-4">
                                <i class="fas fa-graduation-cap me-2 text-primary"></i>
                                <h5 class="d-inline">Academic Information</h5>
                            </div>
                            
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Admission Date <span class="text-danger">*</span></label>
                                    <input type="date" name="admission_date" class="form-control" value="{{ now()->format('Y-m-d') }}" required>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Class <span class="text-danger">*</span></label>
                                    <select name="class_id" class="form-select" id="classSelect" required>
                                        <option value="">Select Class</option>
                                        @foreach($classes as $class)
                                            <option value="{{ $class->id }}">{{ $class->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Section <span class="text-danger">*</span></label>
                                    <select name="section_id" class="form-select" id="sectionSelect" required disabled>
                                        <option value="">First select class</option>
                                    </select>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Academic Year <span class="text-danger">*</span></label>
                                    <select name="academic_year_id" class="form-select" required>
                                        <option value="">Select Academic Year</option>
                                        @foreach($academicYears as $year)
                                            <option value="{{ $year->id }}" {{ $year->is_current ? 'selected' : '' }}>
                                                {{ $year->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Previous School</label>
                                    <input type="text" name="previous_school" class="form-control">
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Previous Grade</label>
                                    <input type="text" name="previous_grade" class="form-control">
                                </div>
                            </div>
                        </div>
                        
                        <!-- Step 3: Parent Information -->
                        <div class="step-content" data-step="3">
                            <div class="section-title mb-4">
                                <i class="fas fa-users me-2 text-primary"></i>
                                <h5 class="d-inline">Parent/Guardian Information</h5>
                            </div>
                            
                            <div class="row">
                                <div class="col-md-12 mb-3">
                                    <label class="form-label w-100">Select Parents</label>
                                    <select name="parent_ids[]" class="form-control form-select select2 w-100" multiple>
                                        @foreach($parents as $parent)
                                            <option value="{{ $parent->id }}">
                                                {{ $parent->name }} ({{ $parent->email }}) - {{ $parent->phone }} {{ $parent->parent->parent_type ?? 'Parent' }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            
                            <div class="alert alert-info">
                                <i class="fas fa-info-circle me-2"></i>
                                <strong>Note:</strong> You can add parents later from the Parent Management section.
                            </div>
                        </div>
                        
                        <!-- Step 4: Review -->
                        <div class="step-content" data-step="4">
                            <div class="section-title mb-4">
                                <i class="fas fa-check-circle me-2 text-primary"></i>
                                <h5 class="d-inline">Review & Submit</h5>
                            </div>
                            
                            <div class="preview-card" id="previewCard">
                                <div class="text-center text-muted py-4">
                                    <i class="fas fa-spinner fa-spin me-2"></i> Loading preview...
                                </div>
                            </div>
                            
                            <div class="alert alert-warning mt-3">
                                <i class="fas fa-exclamation-triangle me-2"></i>
                                <strong>Important:</strong> Default password for new student: <code>password123</code>. Student should change this after first login.
                            </div>
                        </div>
                        
                        <!-- Navigation Buttons -->
                        <div class="step-navigation mt-4">
                            <button type="button" class="btn btn-secondary" id="prevBtn" disabled>
                                <i class="fas fa-arrow-left me-2"></i> Previous
                            </button>
                            <button type="button" class="btn btn-primary" id="nextBtn">
                                Next <i class="fas fa-arrow-right ms-2"></i>
                            </button>
                            <button type="submit" class="btn btn-success" id="submitBtn" style="display: none;">
                                <i class="fas fa-save me-2"></i> Create Student
                            </button>
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
    .steps-progress {
        display: flex;
        justify-content: space-between;
        position: relative;
        margin-bottom: 40px;
    }
    
    .steps-progress::before {
        content: '';
        position: absolute;
        top: 20px;
        left: 0;
        right: 0;
        height: 2px;
        background: #e9ecef;
        z-index: 1;
    }
    
    .step {
        position: relative;
        z-index: 2;
        text-align: center;
        flex: 1;
    }
    
    .step-circle {
        width: 40px;
        height: 40px;
        background: white;
        border: 2px solid #dee2e6;
        border-radius: 50%;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        font-weight: bold;
        color: #6c757d;
        transition: all 0.3s;
    }
    
    .step.active .step-circle {
        background: linear-gradient(135deg, #4361ee 0%, #7209b7 100%);
        border-color: #4361ee;
        color: white;
    }
    
    .step.completed .step-circle {
        background: #28a745;
        border-color: #28a745;
        color: white;
    }
    
    .step-label {
        margin-top: 8px;
        font-size: 12px;
        color: #6c757d;
    }
    
    .step.active .step-label {
        color: #4361ee;
        font-weight: 500;
    }
    
    .step-content {
        display: none;
        animation: fadeIn 0.5s ease;
    }
    
    .step-content.active {
        display: block;
    }
    
    @keyframes fadeIn {
        from {
            opacity: 0;
            transform: translateY(10px);
        }
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }
    
    .section-title {
        padding-bottom: 10px;
        border-bottom: 2px solid var(--border);
    }
    
    .preview-card {
        background: #f8f9fa;
        border-radius: 12px;
        padding: 20px;
    }
    
    .preview-row {
        display: flex;
        margin-bottom: 12px;
        padding-bottom: 12px;
        border-bottom: 1px solid #e9ecef;
    }
    
    .preview-label {
        width: 150px;
        font-weight: 600;
        color: #495057;
    }
    
    .preview-value {
        flex: 1;
        color: #212529;
    }
    .select2-search__field{
        width: 100% !important;
    }
    .select2-container {
        width: 100% !important;
    }
</style>
@endpush

@push('scripts')
<script>
    $('.select2').select2({
        width: '100%'
    });
    // Section dropdown based on class
    const sectionsData = @json($classes->map(function($class) {
        return [
            'class_id' => $class->id,
            'sections' => $class->sections->map(function($section) {
                return ['id' => $section->id, 'name' => $section->name];
            })
        ];
    })->values());
    
    document.getElementById('classSelect').addEventListener('change', function() {
        const classId = this.value;
        const sectionSelect = document.getElementById('sectionSelect');
        sectionSelect.innerHTML = '<option value="">Select Section</option>';
        
        if (classId) {
            const classData = sectionsData.find(data => data.class_id == classId);
            if (classData && classData.sections) {
                classData.sections.forEach(section => {
                    const option = document.createElement('option');
                    option.value = section.id;
                    option.textContent = section.name;
                    sectionSelect.appendChild(option);
                });
                sectionSelect.disabled = false;
            }
        } else {
            sectionSelect.disabled = true;
        }
    });
    
    // Step Navigation
    let currentStep = 1;
    const totalSteps = 4;
    
    function updateSteps() {
        document.querySelectorAll('.step').forEach((step, index) => {
            const stepNum = index + 1;
            if (stepNum < currentStep) {
                step.classList.add('completed');
                step.classList.remove('active');
            } else if (stepNum === currentStep) {
                step.classList.add('active');
                step.classList.remove('completed');
            } else {
                step.classList.remove('active', 'completed');
            }
        });
        
        document.querySelectorAll('.step-content').forEach((content, index) => {
            if (index + 1 === currentStep) {
                content.classList.add('active');
            } else {
                content.classList.remove('active');
            }
        });
        
        const prevBtn = document.getElementById('prevBtn');
        const nextBtn = document.getElementById('nextBtn');
        const submitBtn = document.getElementById('submitBtn');
        
        prevBtn.disabled = currentStep === 1;
        
        if (currentStep === totalSteps) {
            nextBtn.style.display = 'none';
            submitBtn.style.display = 'inline-block';
            updatePreview();
        } else {
            nextBtn.style.display = 'inline-block';
            submitBtn.style.display = 'none';
        }
    }
    
    function validateStep(step) {
        switch(step) {
            case 1:
                const name = document.querySelector('input[name="name"]').value;
                const email = document.querySelector('input[name="email"]').value;
                const phone = document.querySelector('input[name="phone"]').value;
                const dob = document.querySelector('input[name="date_of_birth"]').value;
                const gender = document.querySelector('select[name="gender"]').value;
                
                if (!name) { alert('Please enter student name'); return false; }
                if (!email) { alert('Please enter email'); return false; }
                if (!phone) { alert('Please enter phone number'); return false; }
                if (!dob) { alert('Please enter date of birth'); return false; }
                if (!gender) { alert('Please select gender'); return false; }
                return true;
                
            case 2:
                const classId = document.querySelector('select[name="class_id"]').value;
                const sectionId = document.querySelector('select[name="section_id"]').value;
                const academicYear = document.querySelector('select[name="academic_year_id"]').value;
                
                if (!classId) { alert('Please select class'); return false; }
                if (!sectionId) { alert('Please select section'); return false; }
                if (!academicYear) { alert('Please select academic year'); return false; }
                return true;
                
            default:
                return true;
        }
    }
    
    function updatePreview() {
        const name = document.querySelector('input[name="name"]').value || 'Not provided';
        const email = document.querySelector('input[name="email"]').value || 'Not provided';
        const phone = document.querySelector('input[name="phone"]').value || 'Not provided';
        const dob = document.querySelector('input[name="date_of_birth"]').value || 'Not provided';
        const gender = document.querySelector('select[name="gender"]')?.selectedOptions[0]?.text || 'Not provided';
        const bloodGroup = document.querySelector('select[name="blood_group"]')?.selectedOptions[0]?.text || 'Not provided';
        const address = document.querySelector('textarea[name="address"]').value || 'Not provided';
        const classText = document.querySelector('select[name="class_id"]')?.selectedOptions[0]?.text || 'Not selected';
        const sectionText = document.querySelector('select[name="section_id"]')?.selectedOptions[0]?.text || 'Not selected';
        const academicYear = document.querySelector('select[name="academic_year_id"]')?.selectedOptions[0]?.text || 'Not selected';
        const admissionDate = document.querySelector('input[name="admission_date"]').value || 'Not provided';
        
        const previewHtml = `
            <div class="preview-row">
                <div class="preview-label">Student Name:</div>
                <div class="preview-value">${escapeHtml(name)}</div>
            </div>
            <div class="preview-row">
                <div class="preview-label">Email:</div>
                <div class="preview-value">${escapeHtml(email)}</div>
            </div>
            <div class="preview-row">
                <div class="preview-label">Phone:</div>
                <div class="preview-value">${escapeHtml(phone)}</div>
            </div>
            <div class="preview-row">
                <div class="preview-label">Date of Birth:</div>
                <div class="preview-value">${escapeHtml(dob)}</div>
            </div>
            <div class="preview-row">
                <div class="preview-label">Gender:</div>
                <div class="preview-value">${escapeHtml(gender)}</div>
            </div>
            <div class="preview-row">
                <div class="preview-label">Blood Group:</div>
                <div class="preview-value">${escapeHtml(bloodGroup)}</div>
            </div>
            <div class="preview-row">
                <div class="preview-label">Address:</div>
                <div class="preview-value">${escapeHtml(address)}</div>
            </div>
            <div class="preview-row">
                <div class="preview-label">Class & Section:</div>
                <div class="preview-value">${escapeHtml(classText)} - ${escapeHtml(sectionText)}</div>
            </div>
            <div class="preview-row">
                <div class="preview-label">Academic Year:</div>
                <div class="preview-value">${escapeHtml(academicYear)}</div>
            </div>
            <div class="preview-row">
                <div class="preview-label">Admission Date:</div>
                <div class="preview-value">${escapeHtml(admissionDate)}</div>
            </div>
        `;
        
        document.getElementById('previewCard').innerHTML = previewHtml;
    }
    
    function escapeHtml(text) {
        const div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    }
    
    document.getElementById('nextBtn').addEventListener('click', () => {
        if (validateStep(currentStep)) {
            currentStep++;
            updateSteps();
        }
    });
    
    document.getElementById('prevBtn').addEventListener('click', () => {
        if (currentStep > 1) {
            currentStep--;
            updateSteps();
        }
    });
    
    updateSteps();
</script>
@endpush