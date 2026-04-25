@extends('website.layouts.app')

@section('title', 'Admissions - Smart School ERP')

@section('content')
    <section class="page-header">
        <div class="container">
            <h1>Admissions</h1>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="{{ route('website.home') }}">Home</a></li>
                    <li class="breadcrumb-item active">Admissions</li>
                </ol>
            </nav>
        </div>
    </section>

    <section class="admissions-content py-5">
        <div class="container">
            <div class="row">
                <div class="col-lg-8">
                    <h2>Admission Process</h2>
                    <p>Lorem ipsum dolor sit amet, consectetur adipiscing elit. Sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris.</p>
                    
                    <div class="steps mt-4">
                        <div class="step-item">
                            <div class="step-number">1</div>
                            <div class="step-content">
                                <h5>Registration</h5>
                                <p>Fill out the online registration form with student and parent details.</p>
                            </div>
                        </div>
                        <div class="step-item">
                            <div class="step-number">2</div>
                            <div class="step-content">
                                <h5>Document Submission</h5>
                                <p>Submit required documents including birth certificate, previous school reports, etc.</p>
                            </div>
                        </div>
                        <div class="step-item">
                            <div class="step-number">3</div>
                            <div class="step-content">
                                <h5>Interaction/Assessment</h5>
                                <p>Student interaction and assessment for grade-appropriate placement.</p>
                            </div>
                        </div>
                        <div class="step-item">
                            <div class="step-number">4</div>
                            <div class="step-content">
                                <h5>Fee Payment & Confirmation</h5>
                                <p>Pay admission fees and confirm enrollment.</p>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-lg-4">
                    <div class="admission-form-card">
                        <h4>Inquiry Form</h4>
                        <form action="{{ route('website.admission.inquiry') }}" method="POST">
                            @csrf
                            <div class="mb-3">
                                <input type="text" name="student_name" class="form-control" placeholder="Student Name" required>
                            </div>
                            <div class="mb-3">
                                <input type="date" name="student_dob" class="form-control" placeholder="Date of Birth" required>
                            </div>
                            <div class="mb-3">
                                <select name="student_gender" class="form-control" required>
                                    <option value="">Gender</option>
                                    <option value="male">Male</option>
                                    <option value="female">Female</option>
                                    <option value="other">Other</option>
                                </select>
                            </div>
                            <div class="mb-3">
                                <input type="text" name="class_applying_for" class="form-control" placeholder="Class Applying For" required>
                            </div>
                            <div class="mb-3">
                                <input type="text" name="parent_name" class="form-control" placeholder="Parent Name" required>
                            </div>
                            <div class="mb-3">
                                <input type="email" name="parent_email" class="form-control" placeholder="Parent Email" required>
                            </div>
                            <div class="mb-3">
                                <input type="tel" name="parent_phone" class="form-control" placeholder="Parent Phone" required>
                            </div>
                            <div class="mb-3">
                                <textarea name="address" class="form-control" rows="3" placeholder="Address" required></textarea>
                            </div>
                            <button type="submit" class="btn btn-primary w-100">Submit Inquiry</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </section>
@endsection

@push('styles')
<style>
    .step-item {
        display: flex;
        margin-bottom: 30px;
    }
    .step-number {
        width: 50px;
        height: 50px;
        background: #007bff;
        color: white;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 24px;
        font-weight: bold;
        margin-right: 20px;
        flex-shrink: 0;
    }
    .step-content h5 {
        margin-bottom: 5px;
        font-size: 18px;
    }
    .step-content p {
        color: #6c757d;
        margin: 0;
    }
    .admission-form-card {
        background: #f8f9fa;
        padding: 30px;
        border-radius: 10px;
        box-shadow: 0 5px 20px rgba(0,0,0,0.1);
    }
    .admission-form-card h4 {
        margin-bottom: 20px;
        text-align: center;
    }
</style>
@endpush