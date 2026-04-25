{{-- resources/views/admin/students/import.blade.php --}}
@extends('layouts.admin')

@section('title', 'Import Students')

@section('content')
<div class="animate-fadeInUp">
    <div class="row">
        <div class="col-lg-8 mx-auto">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <div>
                        <i class="fas fa-upload me-2"></i> Import Students
                    </div>
                    <a href="{{ route('admin.students.index') }}" class="btn btn-sm btn-outline-secondary">
                        <i class="fas fa-arrow-left me-1"></i> Back
                    </a>
                </div>
                <div class="card-body">
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle me-2"></i>
                        <strong>Instructions:</strong>
                        <ul class="mb-0 mt-2">
                            <li>Download the sample template below</li>
                            <li>Fill in student details (name, email, phone, etc.)</li>
                            <li>Upload the completed Excel/CSV file</li>
                            <li>Default password for imported students: <code>password123</code></li>
                        </ul>
                    </div>
                    
                    <div class="text-center mb-4">
                        <a href="{{ route('admin.students.download-template') }}" class="btn btn-success">
                            <i class="fas fa-download me-2"></i> Download Sample Template
                        </a>
                    </div>
                    
                    <form action="{{ route('admin.students.import') }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        <div class="dropzone-area" id="dropzone">
                            <div class="dropzone-content">
                                <i class="fas fa-cloud-upload-alt fa-4x text-primary mb-3"></i>
                                <h6>Drag & Drop Excel/CSV File Here</h6>
                                <p class="text-muted">or click to browse</p>
                                <input type="file" name="file" id="fileInput" accept=".csv,.xlsx,.xls" style="display: none;">
                                <button type="button" class="btn btn-primary" id="browseBtn">
                                    <i class="fas fa-folder-open me-1"></i> Choose File
                                </button>
                            </div>
                        </div>
                        <div id="fileName" class="text-center mt-3 text-muted"></div>
                        
                        <div class="mt-4 text-center">
                            <button type="submit" class="btn btn-primary btn-lg px-5" id="submitBtn" disabled>
                                <i class="fas fa-upload me-2"></i> Import Students
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
    .dropzone-area {
        border: 2px dashed #dee2e6;
        border-radius: 20px;
        padding: 40px;
        text-align: center;
        transition: all 0.3s;
        cursor: pointer;
    }
    
    .dropzone-area.drag-over {
        border-color: #4361ee;
        background: rgba(67, 97, 238, 0.05);
    }
</style>
@endpush

@push('scripts')
<script>
    const dropzone = document.getElementById('dropzone');
    const fileInput = document.getElementById('fileInput');
    const browseBtn = document.getElementById('browseBtn');
    const fileNameDiv = document.getElementById('fileName');
    const submitBtn = document.getElementById('submitBtn');
    
    dropzone.addEventListener('click', () => fileInput.click());
    browseBtn.addEventListener('click', (e) => {
        e.stopPropagation();
        fileInput.click();
    });
    
    dropzone.addEventListener('dragover', (e) => {
        e.preventDefault();
        dropzone.classList.add('drag-over');
    });
    
    dropzone.addEventListener('dragleave', () => {
        dropzone.classList.remove('drag-over');
    });
    
    dropzone.addEventListener('drop', (e) => {
        e.preventDefault();
        dropzone.classList.remove('drag-over');
        const file = e.dataTransfer.files[0];
        handleFile(file);
    });
    
    fileInput.addEventListener('change', (e) => {
        const file = e.target.files[0];
        handleFile(file);
    });
    
    function handleFile(file) {
        if (file) {
            fileNameDiv.innerHTML = `<i class="fas fa-file-excel text-success me-2"></i> ${file.name}`;
            submitBtn.disabled = false;
        }
    }
</script>
@endpush