@extends('website.layouts.app')

@section('title', 'Home - Smart School ERP')
@section('meta_description', 'Welcome to Smart School ERP - A premier educational institution dedicated to excellence in education.')
@section('meta_keywords', 'school, education, learning, smart school, best school')

@section('content')
    <!-- Hero Section -->
    @include('website.components.hero-section')

    <!-- Features Section -->
    @include('website.components.features-section')

    <!-- About Section -->
    <section class="about-section py-5">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-lg-6" data-aos="fade-right">
                    <div class="about-image">
                        <img src="{{ asset('images/about-school.jpg') }}" alt="About School" class="img-fluid rounded shadow">
                        <div class="experience-badge">
                            <span class="years">25+</span>
                            <span class="text">Years of Excellence</span>
                        </div>
                    </div>
                </div>
                <div class="col-lg-6" data-aos="fade-left">
                    <div class="about-content">
                        <h6 class="section-subtitle">About Us</h6>
                        <h2 class="section-title">Welcome to Smart School ERP</h2>
                        <p class="about-text">
                            We are committed to providing quality education that nurtures creativity, 
                            critical thinking, and character development in our students. Our holistic 
                            approach to education ensures that every child reaches their full potential.
                        </p>
                        
                        <div class="features-list">
                            <div class="feature-item">
                                <i class="fas fa-check-circle"></i>
                                <span>Qualified and Experienced Teachers</span>
                            </div>
                            <div class="feature-item">
                                <i class="fas fa-check-circle"></i>
                                <span>Modern Infrastructure and Facilities</span>
                            </div>
                            <div class="feature-item">
                                <i class="fas fa-check-circle"></i>
                                <span>Focus on Overall Development</span>
                            </div>
                            <div class="feature-item">
                                <i class="fas fa-check-circle"></i>
                                <span>Parent-Teacher Collaboration</span>
                            </div>
                        </div>
                        
                        <a href="{{ route('website.about') }}" class="btn btn-primary btn-lg mt-4">
                            Read More <i class="fas fa-arrow-right ms-2"></i>
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Stats Counter -->
    @include('website.components.stats-counter')

    <!-- Announcements & Events Section -->
    <section class="announcements-section py-5 bg-light">
        <div class="container">
            <div class="row">
                <div class="col-lg-6" data-aos="fade-up">
                    <div class="section-header">
                        <h6 class="section-subtitle">Stay Updated</h6>
                        <h2 class="section-title">Latest Announcements</h2>
                    </div>
                    <div class="announcements-list">
                        @forelse($announcements as $announcement)
                            <div class="announcement-card">
                                <div class="announcement-date">
                                    <span class="day">{{ $announcement->created_at->format('d') }}</span>
                                    <span class="month">{{ $announcement->created_at->format('M') }}</span>
                                </div>
                                <div class="announcement-content">
                                    <h5>{{ $announcement->title }}</h5>
                                    <p>{{ Str::limit($announcement->content, 100) }}</p>
                                    <a href="#" class="read-more">Read More <i class="fas fa-arrow-right"></i></a>
                                </div>
                            </div>
                        @empty
                            <p class="text-muted">No announcements available.</p>
                        @endforelse
                    </div>
                </div>
                
                <div class="col-lg-6" data-aos="fade-up" data-aos-delay="200">
                    <div class="section-header">
                        <h6 class="section-subtitle">Don't Miss</h6>
                        <h2 class="section-title">Upcoming Events</h2>
                    </div>
                    <div class="events-list">
                        @forelse($events as $event)
                            @include('website.components.event-card', ['event' => $event])
                        @empty
                            <p class="text-muted">No upcoming events.</p>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Gallery Preview Section -->
    <section class="gallery-section py-5">
        <div class="container">
            <div class="section-header text-center" data-aos="fade-up">
                <h6 class="section-subtitle">Our Gallery</h6>
                <h2 class="section-title">Moments Captured</h2>
            </div>
            
            <div class="row g-4 gallery-grid">
                @forelse($gallery as $image)
                    <div class="col-lg-4 col-md-6" data-aos="zoom-in">
                        <div class="gallery-item">
                            <img src="{{ $image->image_url }}" alt="{{ $image->title }}" class="img-fluid">
                            <div class="gallery-overlay">
                                <h5>{{ $image->title }}</h5>
                                <p>{{ $image->description }}</p>
                                <a href="{{ $image->image_url }}" class="btn btn-light btn-sm gallery-zoom">
                                    <i class="fas fa-search-plus"></i>
                                </a>
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="col-12 text-center">
                        <p class="text-muted">No gallery images available.</p>
                    </div>
                @endforelse
            </div>
            
            <div class="text-center mt-4" data-aos="fade-up">
                <a href="{{ route('website.gallery') }}" class="btn btn-outline-primary btn-lg">
                    View All Gallery <i class="fas fa-arrow-right ms-2"></i>
                </a>
            </div>
        </div>
    </section>

    <!-- Testimonials Section -->
    <section class="testimonials-section py-5 bg-light">
        <div class="container">
            <div class="section-header text-center" data-aos="fade-up">
                <h6 class="section-subtitle">Testimonials</h6>
                <h2 class="section-title">What Parents Say</h2>
            </div>
            
            <div class="testimonials-slider">
                <div class="row">
                    <div class="col-md-4" data-aos="fade-right">
                        <div class="testimonial-card">
                            <div class="testimonial-content">
                                <i class="fas fa-quote-left quote-icon"></i>
                                <p>My child has shown remarkable improvement since joining this school. The teachers are very supportive.</p>
                            </div>
                            <div class="testimonial-author">
                                <img src="{{ asset('images/testimonial-1.jpg') }}" alt="Parent">
                                <div class="author-info">
                                    <h5>John Doe</h5>
                                    <p>Parent of Class 5 Student</p>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-md-4" data-aos="fade-up">
                        <div class="testimonial-card">
                            <div class="testimonial-content">
                                <i class="fas fa-quote-left quote-icon"></i>
                                <p>The school provides excellent facilities and focuses on overall development of students.</p>
                            </div>
                            <div class="testimonial-author">
                                <img src="{{ asset('images/testimonial-2.jpg') }}" alt="Parent">
                                <div class="author-info">
                                    <h5>Jane Smith</h5>
                                    <p>Parent of Class 8 Student</p>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-md-4" data-aos="fade-left">
                        <div class="testimonial-card">
                            <div class="testimonial-content">
                                <i class="fas fa-quote-left quote-icon"></i>
                                <p>Great learning environment with modern teaching methods. Highly recommended!</p>
                            </div>
                            <div class="testimonial-author">
                                <img src="{{ asset('images/testimonial-3.jpg') }}" alt="Parent">
                                <div class="author-info">
                                    <h5>Mike Johnson</h5>
                                    <p>Parent of Class 10 Student</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Call to Action -->
    <section class="cta-section py-5">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-lg-8" data-aos="fade-right">
                    <h3>Ready to start your child's journey with us?</h3>
                    <p class="mb-0">Enroll now for the academic year 2024-25. Limited seats available!</p>
                </div>
                <div class="col-lg-4 text-lg-end" data-aos="fade-left">
                    <a href="{{ route('website.admissions') }}" class="btn btn-light btn-lg">
                        Apply Now <i class="fas fa-arrow-right ms-2"></i>
                    </a>
                </div>
            </div>
        </div>
    </section>
@endsection

@push('styles')
<style>
    .about-image {
        position: relative;
    }
    .experience-badge {
        position: absolute;
        bottom: 30px;
        right: 30px;
        background: #007bff;
        color: white;
        padding: 20px;
        border-radius: 10px;
        text-align: center;
    }
    .experience-badge .years {
        font-size: 36px;
        font-weight: bold;
        display: block;
        line-height: 1;
    }
    .experience-badge .text {
        font-size: 14px;
        opacity: 0.9;
    }
    .features-list {
        margin-top: 20px;
    }
    .feature-item {
        margin-bottom: 10px;
    }
    .feature-item i {
        color: #28a745;
        margin-right: 10px;
    }
    .announcement-card {
        display: flex;
        background: white;
        padding: 20px;
        margin-bottom: 15px;
        border-radius: 10px;
        box-shadow: 0 5px 15px rgba(0,0,0,0.05);
        transition: transform 0.3s;
    }
    .announcement-card:hover {
        transform: translateY(-5px);
    }
    .announcement-date {
        text-align: center;
        min-width: 60px;
        margin-right: 15px;
    }
    .announcement-date .day {
        font-size: 24px;
        font-weight: bold;
        color: #007bff;
        display: block;
        line-height: 1;
    }
    .announcement-date .month {
        font-size: 14px;
        text-transform: uppercase;
        color: #6c757d;
    }
    .announcement-content h5 {
        margin-bottom: 5px;
        font-size: 18px;
    }
    .announcement-content p {
        color: #6c757d;
        margin-bottom: 10px;
    }
    .read-more {
        color: #007bff;
        text-decoration: none;
        font-size: 14px;
    }
    .read-more:hover {
        text-decoration: underline;
    }
    .gallery-item {
        position: relative;
        overflow: hidden;
        border-radius: 10px;
        cursor: pointer;
    }
    .gallery-item img {
        width: 100%;
        height: 250px;
        object-fit: cover;
        transition: transform 0.5s;
    }
    .gallery-item:hover img {
        transform: scale(1.1);
    }
    .gallery-overlay {
        position: absolute;
        bottom: -100%;
        left: 0;
        right: 0;
        background: linear-gradient(to top, rgba(0,0,0,0.8), transparent);
        color: white;
        padding: 20px;
        transition: bottom 0.3s;
    }
    .gallery-item:hover .gallery-overlay {
        bottom: 0;
    }
    .testimonial-card {
        background: white;
        padding: 30px;
        border-radius: 10px;
        box-shadow: 0 5px 20px rgba(0,0,0,0.1);
        margin-bottom: 20px;
    }
    .testimonial-content {
        position: relative;
        margin-bottom: 20px;
    }
    .quote-icon {
        color: #007bff;
        font-size: 24px;
        opacity: 0.3;
        margin-bottom: 10px;
    }
    .testimonial-author {
        display: flex;
        align-items: center;
    }
    .testimonial-author img {
        width: 50px;
        height: 50px;
        border-radius: 50%;
        margin-right: 15px;
        object-fit: cover;
    }
    .testimonial-author h5 {
        margin-bottom: 5px;
        font-size: 16px;
    }
    .testimonial-author p {
        color: #6c757d;
        margin: 0;
        font-size: 14px;
    }
    .cta-section {
        background: linear-gradient(135deg, #007bff 0%, #0056b3 100%);
        color: white;
    }
</style>
@endpush