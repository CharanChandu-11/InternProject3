// public/js/website.js

$(document).ready(function() {
    // Mobile menu toggle
    $('#navbarMain').on('show.bs.collapse', function() {
        $('body').addClass('menu-open');
    });

    $('#navbarMain').on('hide.bs.collapse', function() {
        $('body').removeClass('menu-open');
    });

    // Dropdown hover effect for desktop
    if ($(window).width() > 991) {
        $('.navbar-nav .dropdown').hover(
            function() {
                $(this).find('.dropdown-menu').first().stop(true, true).delay(100).fadeIn(200);
            },
            function() {
                $(this).find('.dropdown-menu').first().stop(true, true).delay(100).fadeOut(200);
            }
        );
    }

    // Smooth scroll for anchor links
    $('a[href*="#"]:not([href="#"])').click(function() {
        if (location.pathname.replace(/^\//, '') === this.pathname.replace(/^\//, '') 
            && location.hostname === this.hostname) {
            var target = $(this.hash);
            target = target.length ? target : $('[name=' + this.hash.slice(1) + ']');
            if (target.length) {
                $('html, body').animate({
                    scrollTop: target.offset().top - 70
                }, 1000);
                return false;
            }
        }
    });

    // Gallery zoom
    $('.gallery-zoom').on('click', function(e) {
        e.preventDefault();
        var imageUrl = $(this).attr('href');
        showImageModal(imageUrl);
    });

    // Contact form submission
    $('#contactForm').on('submit', function(e) {
        e.preventDefault();
        var formData = $(this).serialize();
        
        $.ajax({
            url: $(this).attr('action'),
            type: 'POST',
            data: formData,
            success: function(response) {
                showNotification('success', 'Message sent successfully!');
                $('#contactForm')[0].reset();
            },
            error: function(xhr) {
                showNotification('error', 'Something went wrong. Please try again.');
            }
        });
    });

    // Admission inquiry form
    $('#admissionInquiryForm').on('submit', function(e) {
        e.preventDefault();
        var formData = $(this).serialize();
        
        $.ajax({
            url: $(this).attr('action'),
            type: 'POST',
            data: formData,
            success: function(response) {
                showNotification('success', 'Inquiry submitted successfully! We will contact you soon.');
                $('#admissionInquiryForm')[0].reset();
            },
            error: function(xhr) {
                showNotification('error', 'Something went wrong. Please try again.');
            }
        });
    });
});

// Image modal function
function showImageModal(imageUrl) {
    var modalHtml = `
        <div class="modal fade" id="imageModal" tabindex="-1">
            <div class="modal-dialog modal-lg modal-dialog-centered">
                <div class="modal-content">
                    <div class="modal-body p-0">
                        <img src="${imageUrl}" class="img-fluid" alt="Gallery Image">
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    </div>
                </div>
            </div>
        </div>
    `;
    
    $('body').append(modalHtml);
    $('#imageModal').modal('show');
    
    $('#imageModal').on('hidden.bs.modal', function() {
        $(this).remove();
    });
}

// Notification function
function showNotification(type, message) {
    var notificationHtml = `
        <div class="notification notification-${type}">
            <div class="notification-content">
                <i class="fas fa-${type === 'success' ? 'check-circle' : 'exclamation-circle'}"></i>
                <span>${message}</span>
            </div>
            <button class="notification-close"><i class="fas fa-times"></i></button>
        </div>
    `;
    
    $('body').append(notificationHtml);
    
    setTimeout(function() {
        $('.notification').fadeOut(300, function() {
            $(this).remove();
        });
    }, 5000);
    
    $('.notification-close').on('click', function() {
        $(this).closest('.notification').fadeOut(300, function() {
            $(this).remove();
        });
    });
}

// Add notification styles
$('<style>')
    .prop('type', 'text/css')
    .html(`
        .notification {
            position: fixed;
            top: 20px;
            right: 20px;
            padding: 15px 20px;
            background: white;
            border-radius: 8px;
            box-shadow: 0 5px 20px rgba(0,0,0,0.2);
            z-index: 9999;
            display: flex;
            align-items: center;
            justify-content: space-between;
            min-width: 300px;
            animation: slideIn 0.3s ease;
        }
        
        .notification-success {
            border-left: 4px solid #28a745;
        }
        
        .notification-error {
            border-left: 4px solid #dc3545;
        }
        
        .notification-content {
            display: flex;
            align-items: center;
        }
        
        .notification-content i {
            margin-right: 10px;
            font-size: 20px;
        }
        
        .notification-success i {
            color: #28a745;
        }
        
        .notification-error i {
            color: #dc3545;
        }
        
        .notification-close {
            background: none;
            border: none;
            cursor: pointer;
            color: #999;
            padding: 0;
            margin-left: 15px;
        }
        
        .notification-close:hover {
            color: #333;
        }
        
        @keyframes slideIn {
            from {
                transform: translateX(100%);
                opacity: 0;
            }
            to {
                transform: translateX(0);
                opacity: 1;
            }
        }
    `)
    .appendTo('head');