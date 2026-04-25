<footer class="main-footer">
    <div class="container">
        <div class="row">
            <div class="col-lg-4 col-md-6">
                <div class="footer-widget">
                    <h4>About Us</h4>
                    <p>{{ $school->about_school ?? 'We are committed to providing quality education and nurturing young minds to become future leaders.' }}</p>
                    <div class="footer-social">
                        <a href="#"><i class="fab fa-facebook-f"></i></a>
                        <a href="#"><i class="fab fa-twitter"></i></a>
                        <a href="#"><i class="fab fa-instagram"></i></a>
                        <a href="#"><i class="fab fa-youtube"></i></a>
                    </div>
                </div>
            </div>
            
            <div class="col-lg-2 col-md-6">
                <div class="footer-widget">
                    <h4>Quick Links</h4>
                    <ul class="footer-links">
                        <li><a href="{{ route('website.home') }}">Home</a></li>
                        <li><a href="{{ route('website.about') }}">About Us</a></li>
                        <li><a href="{{ route('website.admissions') }}">Admissions</a></li>
                        <li><a href="{{ route('website.faculty') }}">Faculty</a></li>
                        <li><a href="{{ route('website.contact') }}">Contact</a></li>
                    </ul>
                </div>
            </div>
            
            <div class="col-lg-3 col-md-6">
                <div class="footer-widget">
                    <h4>Contact Info</h4>
                    <ul class="contact-info">
                        <li>
                            <i class="fas fa-map-marker-alt"></i>
                            {{ $school->address ?? '123 School Street, City, State 12345' }}
                        </li>
                        <li>
                            <i class="fas fa-phone"></i>
                            <a href="tel:{{ $school->phone ?? '+1234567890' }}">{{ $school->phone ?? '+1234567890' }}</a>
                        </li>
                        <li>
                            <i class="fas fa-envelope"></i>
                            <a href="mailto:{{ $school->email ?? 'info@smartschool.com' }}">{{ $school->email ?? 'info@smartschool.com' }}</a>
                        </li>
                    </ul>
                </div>
            </div>
            
            <div class="col-lg-3 col-md-6">
                <div class="footer-widget">
                    <h4>Newsletter</h4>
                    <p>Subscribe to get updates about events and news</p>
                    <form class="newsletter-form" id="newsletterForm">
                        @csrf
                        <div class="input-group">
                            <input type="email" class="form-control" placeholder="Your Email" required>
                            <button class="btn btn-primary" type="submit">
                                <i class="fas fa-paper-plane"></i>
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
    
    <div class="footer-bottom">
        <div class="container">
            <div class="row">
                <div class="col-md-6">
                    <p class="copyright">
                        &copy; {{ date('Y') }} {{ $school->school_name ?? 'Smart School' }}. All rights reserved.
                    </p>
                </div>
                <div class="col-md-6 text-md-end">
                    <ul class="footer-bottom-links">
                        <li><a href="#">Privacy Policy</a></li>
                        <li><a href="#">Terms of Use</a></li>
                        <li><a href="#">Sitemap</a></li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</footer>