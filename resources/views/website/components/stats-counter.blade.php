<section class="stats-section py-5">
    <div class="container">
        <div class="row">
            <div class="col-lg-3 col-md-6" data-aos="fade-up">
                <div class="stat-item">
                    <div class="stat-number">
                        <span class="counter" data-target="500">0</span>+
                    </div>
                    <div class="stat-label">Students</div>
                </div>
            </div>
            
            <div class="col-lg-3 col-md-6" data-aos="fade-up" data-aos-delay="100">
                <div class="stat-item">
                    <div class="stat-number">
                        <span class="counter" data-target="50">0</span>+
                    </div>
                    <div class="stat-label">Teachers</div>
                </div>
            </div>
            
            <div class="col-lg-3 col-md-6" data-aos="fade-up" data-aos-delay="200">
                <div class="stat-item">
                    <div class="stat-number">
                        <span class="counter" data-target="30">0</span>+
                    </div>
                    <div class="stat-label">Classrooms</div>
                </div>
            </div>
            
            <div class="col-lg-3 col-md-6" data-aos="fade-up" data-aos-delay="300">
                <div class="stat-item">
                    <div class="stat-number">
                        <span class="counter" data-target="15">0</span>+
                    </div>
                    <div class="stat-label">Awards</div>
                </div>
            </div>
        </div>
    </div>
</section>

@push('scripts')
<script>
    // Counter Animation
    $(document).ready(function() {
        var counters = $('.counter');
        var countersLoaded = false;
        
        function startCounters() {
            counters.each(function() {
                var $this = $(this);
                var target = $this.data('target');
                
                $({count: 0}).animate({count: target}, {
                    duration: 2000,
                    easing: 'swing',
                    step: function() {
                        $this.text(Math.floor(this.count));
                    },
                    complete: function() {
                        $this.text(this.count);
                    }
                });
            });
        }
        
        // Check if counters are in viewport
        function isInViewport(element) {
            var rect = element.getBoundingClientRect();
            return (
                rect.top >= 0 &&
                rect.left >= 0 &&
                rect.bottom <= (window.innerHeight || document.documentElement.clientHeight) &&
                rect.right <= (window.innerWidth || document.documentElement.clientWidth)
            );
        }
        
        $(window).on('scroll', function() {
            if (!countersLoaded) {
                var firstCounter = counters[0];
                if (firstCounter && isInViewport(firstCounter)) {
                    startCounters();
                    countersLoaded = true;
                }
            }
        });
    });
</script>
@endpush

@push('styles')
<style>
    .stats-section {
        background: linear-gradient(135deg, #007bff 0%, #0056b3 100%);
        color: white;
    }
    .stat-item {
        text-align: center;
        padding: 20px;
    }
    .stat-number {
        font-size: 48px;
        font-weight: 700;
        margin-bottom: 10px;
        line-height: 1;
    }
    .stat-label {
        font-size: 18px;
        text-transform: uppercase;
        letter-spacing: 2px;
        opacity: 0.9;
    }
</style>
@endpush