<section class="features-section py-5">
    <div class="container">
        <div class="section-header text-center" data-aos="fade-up">
            <h6 class="section-subtitle">Why Choose Us</h6>
            <h2 class="section-title">Our Key Features</h2>
        </div>
        
        <div class="row g-4">
            <div class="col-lg-3 col-md-6" data-aos="flip-left">
                <div class="feature-card">
                    <div class="feature-icon">
                        <i class="fas fa-chalkboard-teacher"></i>
                    </div>
                    <h4>Expert Teachers</h4>
                    <p>Qualified and experienced faculty dedicated to student success.</p>
                </div>
            </div>
            
            <div class="col-lg-3 col-md-6" data-aos="flip-left" data-aos-delay="100">
                <div class="feature-card">
                    <div class="feature-icon">
                        <i class="fas fa-laptop"></i>
                    </div>
                    <h4>Smart Classrooms</h4>
                    <p>Modern technology-enabled classrooms for interactive learning.</p>
                </div>
            </div>
            
            <div class="col-lg-3 col-md-6" data-aos="flip-left" data-aos-delay="200">
                <div class="feature-card">
                    <div class="feature-icon">
                        <i class="fas fa-book-reader"></i>
                    </div>
                    <h4>Rich Library</h4>
                    <p>Extensive collection of books, digital resources, and research materials.</p>
                </div>
            </div>
            
            <div class="col-lg-3 col-md-6" data-aos="flip-left" data-aos-delay="300">
                <div class="feature-card">
                    <div class="feature-icon">
                        <i class="fas fa-futbol"></i>
                    </div>
                    <h4>Sports Facilities</h4>
                    <p>Comprehensive sports infrastructure for physical development.</p>
                </div>
            </div>
        </div>
    </div>
</section>

@push('styles')
<style>
    .features-section {
        background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
    }
    .feature-card {
        background: white;
        padding: 30px;
        border-radius: 10px;
        text-align: center;
        box-shadow: 0 5px 20px rgba(0,0,0,0.1);
        transition: transform 0.3s;
        height: 100%;
    }
    .feature-card:hover {
        transform: translateY(-10px);
    }
    .feature-icon {
        width: 80px;
        height: 80px;
        background: #007bff;
        color: white;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        margin: 0 auto 20px;
        font-size: 32px;
    }
    .feature-card h4 {
        margin-bottom: 15px;
        font-size: 20px;
        font-weight: 600;
    }
    .feature-card p {
        color: #6c757d;
        margin: 0;
        line-height: 1.6;
    }
</style>
@endpush