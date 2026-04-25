@extends('website.layouts.app')

@section('title', 'Academics - Smart School ERP')

@section('content')
    <section class="page-header">
        <div class="container">
            <h1>Academics</h1>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="{{ route('website.home') }}">Home</a></li>
                    <li class="breadcrumb-item active">Academics</li>
                </ol>
            </nav>
        </div>
    </section>

    <section class="academics-content py-5">
        <div class="container">
            <div class="row">
                <div class="col-lg-8">
                    <h2>Our Academic Programs</h2>
                    <p>We offer a comprehensive curriculum designed to foster intellectual curiosity, critical thinking, and a love for learning. Our academic programs are aligned with national educational standards and are delivered through innovative teaching methodologies.</p>

                    <div class="programs mt-4">
                        <div class="program-card">
                            <h4>Pre-Primary (Nursery - UKG)</h4>
                            <p>A play-based, experiential learning environment that nurtures young minds through exploration, creativity, and social interaction.</p>
                            <ul>
                                <li>Montessori approach with modern resources</li>
                                <li>Focus on language development, numeracy, and motor skills</li>
                                <li>Qualified early childhood educators</li>
                            </ul>
                        </div>
                        <div class="program-card">
                            <h4>Primary (Class 1 - 5)</h4>
                            <p>Building a strong foundation in core subjects while encouraging curiosity and independent thinking.</p>
                            <ul>
                                <li>Subjects: English, Mathematics, Science, Social Studies, Computer Science, Hindi</li>
                                <li>Project-based learning and hands-on activities</li>
                                <li>Regular assessments and personalized attention</li>
                            </ul>
                        </div>
                        <div class="program-card">
                            <h4>Middle School (Class 6 - 8)</h4>
                            <p>Preparing students for higher academics with a balanced mix of theory and practical applications.</p>
                            <ul>
                                <li>Introduction to subject specialization</li>
                                <li>Laboratory experiments, library research, and co-curricular integration</li>
                                <li>Career guidance and aptitude development</li>
                            </ul>
                        </div>
                        <div class="program-card">
                            <h4>High School (Class 9 - 12)</h4>
                            <p>Rigorous academic training with options for Science, Commerce, and Humanities streams.</p>
                            <ul>
                                <li>CBSE curriculum with extensive exam preparation</li>
                                <li>Choice of elective subjects as per student interest</li>
                                <li>Counseling for higher education and competitive exams</li>
                            </ul>
                        </div>
                    </div>
                </div>
                <div class="col-lg-4">
                    <div class="sidebar-widget">
                        <h4>Academic Calendar</h4>
                        <ul class="calendar-list">
                            <li><i class="fas fa-calendar-alt"></i> Term I: April - September</li>
                            <li><i class="fas fa-calendar-alt"></i> Term II: October - March</li>
                            <li><i class="fas fa-calendar-alt"></i> Summer Break: May - June</li>
                            <li><i class="fas fa-calendar-alt"></i> Winter Break: December 25 - January 5</li>
                        </ul>
                    </div>
                    <div class="sidebar-widget">
                        <h4>Assessment System</h4>
                        <ul>
                            <li>Continuous Comprehensive Evaluation (CCE)</li>
                            <li>Formative Assessments (projects, quizzes, oral tests)</li>
                            <li>Summative Assessments (term exams)</li>
                            <li>Co-curricular and sports participation graded</li>
                        </ul>
                    </div>
                    <div class="sidebar-widget">
                        <h4>Contact Academic Office</h4>
                        <p><i class="fas fa-phone"></i> +91-1234567890</p>
                        <p><i class="fas fa-envelope"></i> academics@smartschool.edu</p>
                    </div>
                </div>
            </div>
        </div>
    </section>
@endsection

@push('styles')
<style>
    .program-card {
        background: #f8f9fa;
        padding: 25px;
        border-radius: 10px;
        margin-bottom: 25px;
        box-shadow: 0 5px 15px rgba(0,0,0,0.05);
        transition: transform 0.3s;
    }
    .program-card:hover {
        transform: translateY(-5px);
    }
    .program-card h4 {
        color: #007bff;
        margin-bottom: 15px;
    }
    .program-card ul {
        padding-left: 20px;
        margin-top: 10px;
    }
    .program-card li {
        margin-bottom: 5px;
    }
    .sidebar-widget {
        background: #f8f9fa;
        padding: 20px;
        border-radius: 10px;
        margin-bottom: 25px;
    }
    .sidebar-widget h4 {
        margin-bottom: 15px;
        padding-bottom: 10px;
        border-bottom: 2px solid #007bff;
    }
    .calendar-list {
        list-style: none;
        padding: 0;
    }
    .calendar-list li {
        margin-bottom: 10px;
    }
    .calendar-list i {
        color: #007bff;
        margin-right: 10px;
        width: 20px;
    }
</style>
@endpush