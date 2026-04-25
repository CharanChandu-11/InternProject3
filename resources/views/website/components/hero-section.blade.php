<section class="hero-section">
    <div class="hero-slider">
        <div class="hero-slide" style="background-image: url('{{ asset('images/hero-1.jpg') }}')">
            <div class="container">
                <div class="hero-content" data-aos="fade-up">
                    <h1>Welcome to Smart School</h1>
                    <p>Empowering students with knowledge, skills, and values for a bright future.</p>
                    <div class="hero-buttons">
                        <a href="{{ route('website.about') }}" class="btn btn-primary btn-lg">Learn More</a>
                        <a href="{{ route('website.contact') }}" class="btn btn-outline-light btn-lg">Contact Us</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

@push('styles')
<style>
    .hero-section {
        position: relative;
        height: 600px;
        overflow: hidden;
    }
    .hero-slide {
        height: 600px;
        background-size: cover;
        background-position: center;
        position: relative;
        display: flex;
        align-items: center;
    }
    .hero-slide::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background: rgba(0,0,0,0.5);
    }
    .hero-content {
        position: relative;
        color: white;
        text-align: center;
        max-width: 800px;
        margin: 0 auto;
        padding: 0 20px;
    }
    .hero-content h1 {
        font-size: 48px;
        font-weight: 700;
        margin-bottom: 20px;
        text-shadow: 2px 2px 4px rgba(0,0,0,0.3);
    }
    .hero-content p {
        font-size: 18px;
        margin-bottom: 30px;
        text-shadow: 1px 1px 2px rgba(0,0,0,0.3);
    }
    .hero-buttons .btn {
        margin: 0 10px;
        min-width: 150px;
    }
    @media (max-width: 768px) {
        .hero-section, .hero-slide {
            height: 400px;
        }
        .hero-content h1 {
            font-size: 32px;
        }
        .hero-content p {
            font-size: 16px;
        }
    }
</style>
@endpush