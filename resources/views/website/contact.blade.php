@extends('website.layouts.app')

@section('title', 'Contact Us - Smart School ERP')

@section('content')
    <section class="page-header">
        <div class="container">
            <h1>Contact Us</h1>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="{{ route('website.home') }}">Home</a></li>
                    <li class="breadcrumb-item active">Contact</li>
                </ol>
            </nav>
        </div>
    </section>

    <section class="contact-content py-5">
        <div class="container">
            <div class="row">
                <div class="col-lg-6 mb-4">
                    <h2>Get In Touch</h2>
                    <p>Have questions? We're here to help. Send us a message and we'll respond within 24 hours.</p>

                    <form action="{{ route('website.contact.submit') }}" method="POST" id="contactForm">
                        @csrf
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <input type="text" name="name" class="form-control" placeholder="Your Name" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <input type="email" name="email" class="form-control" placeholder="Your Email" required>
                            </div>
                        </div>
                        <div class="mb-3">
                            <input type="tel" name="phone" class="form-control" placeholder="Phone Number" required>
                        </div>
                        <div class="mb-3">
                            <textarea name="message" class="form-control" rows="5" placeholder="Your Message" required></textarea>
                        </div>
                        <button type="submit" class="btn btn-primary">Send Message</button>
                    </form>
                </div>

                <div class="col-lg-6">
                    <div class="contact-info">
                        <h2>Contact Information</h2>
                        <div class="info-item">
                            <i class="fas fa-map-marker-alt"></i>
                            <div>
                                <h5>Address</h5>
                                <p>{{ $school->address ?? '123 School Street, City, State 12345' }}</p>
                            </div>
                        </div>
                        <div class="info-item">
                            <i class="fas fa-phone-alt"></i>
                            <div>
                                <h5>Phone</h5>
                                <p>{{ $school->phone ?? '+1234567890' }}</p>
                            </div>
                        </div>
                        <div class="info-item">
                            <i class="fas fa-envelope"></i>
                            <div>
                                <h5>Email</h5>
                                <p>{{ $school->email ?? 'info@smartschool.com' }}</p>
                            </div>
                        </div>
                        <div class="info-item">
                            <i class="fas fa-clock"></i>
                            <div>
                                <h5>Office Hours</h5>
                                <p>Monday - Friday: 8:00 AM - 4:00 PM<br>Saturday: 8:00 AM - 12:00 PM</p>
                            </div>
                        </div>
                    </div>

                    <div class="map mt-4">
                        <iframe src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3024.2219901290355!2d-74.00369368400567!3d40.71312937933058!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x89c25a316bbf13a9%3A0x4b2f4e8c9a2f4b2f!2sNew%20York%2C%20NY!5e0!3m2!1sen!2sus!4v1644924799815!5m2!1sen!2sus" width="100%" height="300" style="border:0;" allowfullscreen="" loading="lazy"></iframe>
                    </div>
                </div>
            </div>
        </div>
    </section>
@endsection

@push('styles')
<style>
    .contact-info .info-item {
        display: flex;
        margin-bottom: 25px;
        align-items: flex-start;
    }
    .contact-info .info-item i {
        font-size: 24px;
        color: #007bff;
        margin-right: 15px;
        margin-top: 5px;
    }
    .contact-info .info-item h5 {
        margin-bottom: 5px;
        font-size: 16px;
    }
    .contact-info .info-item p {
        margin-bottom: 0;
        color: #6c757d;
    }
    .map {
        border-radius: 10px;
        overflow: hidden;
        box-shadow: 0 5px 15px rgba(0,0,0,0.1);
    }
    #contactForm .form-control {
        border: 1px solid #ddd;
        padding: 12px;
        border-radius: 5px;
    }
    #contactForm .form-control:focus {
        border-color: #007bff;
        box-shadow: none;
    }
</style>
@endpush

@push('scripts')
<script>
    $('#contactForm').on('submit', function(e) {
        e.preventDefault();
        var formData = $(this).serialize();
        
        $.ajax({
            url: $(this).attr('action'),
            type: 'POST',
            data: formData,
            success: function(response) {
                alert('Message sent successfully! We will get back to you soon.');
                $('#contactForm')[0].reset();
            },
            error: function(xhr) {
                alert('Something went wrong. Please try again.');
            }
        });
    });
</script>
@endpush