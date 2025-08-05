/**
 * NPK Valpeliste JavaScript - Produksjon v1.9.1
 */

jQuery(document).ready(function($) {
    
    // Initialize NPK functionality
    window.npkRefreshData = function() {
        const button = $('.npk-refresh-btn');
        const originalText = button.text();
        
        // Show loading state
        button.prop('disabled', true).text('Oppdaterer...');
        
        // Show loading indicator
        if (!$('.npk-loading').length) {
            $('.npk-valpeliste').prepend('<div class="npk-loading">Henter fresh data fra NPK API...</div>');
        }
        
        $.ajax({
            url: npk_ajax.ajax_url,
            type: 'POST',
            data: {
                action: 'npk_refresh_data',
                nonce: npk_ajax.nonce
            },
            success: function(response) {
                if (response.success) {
                    // Reload page to show fresh data
                    location.reload();
                } else {
                    showNotification('Feil ved oppdatering: ' + response.data, 'error');
                }
            },
            error: function() {
                showNotification('Kunne ikke koble til server', 'error');
            },
            complete: function() {
                $('.npk-loading').remove();
                button.prop('disabled', false).text(originalText);
            }
        });
    };
    
    // Show notification helper
    function showNotification(message, type) {
        const notification = $('<div class="npk-notification npk-' + type + '">' + message + '</div>');
        
        $('body').append(notification);
        
        // Auto-hide after 5 seconds
        setTimeout(function() {
            notification.fadeOut(function() {
                $(this).remove();
            });
        }, 5000);
        
        // Manual close on click
        notification.on('click', function() {
            $(this).fadeOut(function() {
                $(this).remove();
            });
        });
    }
    
    // Auto-refresh functionality (optional)
    if (typeof npkAutoRefresh !== 'undefined' && npkAutoRefresh > 0) {
        setInterval(function() {
            npkRefreshData();
        }, npkAutoRefresh * 60000); // Convert minutes to milliseconds
    }
    
    // Badge hover effects
    $('.badge').on('mouseenter', function() {
        $(this).css('transform', 'scale(1.05)');
    }).on('mouseleave', function() {
        $(this).css('transform', 'scale(1)');
    });
    
    // Smooth scrolling for internal links
    $('a[href^="#"]').on('click', function(e) {
        e.preventDefault();
        const target = $(this.hash);
        if (target.length) {
            $('html, body').animate({
                scrollTop: target.offset().top - 50
            }, 500);
        }
    });
    
    // Responsive table handling
    function handleResponsiveTables() {
        $('.npk-litters').each(function() {
            const container = $(this);
            if (container.width() < 600) {
                container.addClass('npk-mobile');
            } else {
                container.removeClass('npk-mobile');
            }
        });
    }
    
    // Initial responsive check
    handleResponsiveTables();
    
    // Responsive check on window resize
    $(window).on('resize', function() {
        clearTimeout(window.resizeTimer);
        window.resizeTimer = setTimeout(handleResponsiveTables, 250);
    });
    
    // Print functionality
    window.npkPrint = function() {
        window.print();
    };
    
    // Search functionality (if needed)
    $('.npk-search').on('input', function() {
        const searchTerm = $(this).val().toLowerCase();
        
        $('.npk-litter-card').each(function() {
            const card = $(this);
            const text = card.text().toLowerCase();
            
            if (text.includes(searchTerm)) {
                card.show();
            } else {
                card.hide();
            }
        });
    });
    
    // Initialize tooltips if any
    if ($.fn.tooltip) {
        $('[data-toggle="tooltip"]').tooltip();
    }
    
});

// CSS for notifications
jQuery(document).ready(function($) {
    if (!$('#npk-notification-styles').length) {
        $('head').append(`
            <style id="npk-notification-styles">
                .npk-notification {
                    position: fixed;
                    top: 20px;
                    right: 20px;
                    padding: 15px 20px;
                    border-radius: 5px;
                    color: white;
                    font-weight: 500;
                    z-index: 9999;
                    cursor: pointer;
                    box-shadow: 0 4px 6px rgba(0,0,0,0.1);
                    transition: all 0.3s ease;
                }
                
                .npk-notification.npk-success {
                    background: #10b981;
                }
                
                .npk-notification.npk-error {
                    background: #ef4444;
                }
                
                .npk-notification.npk-info {
                    background: #3b82f6;
                }
                
                .npk-notification:hover {
                    transform: translateY(-2px);
                    box-shadow: 0 6px 12px rgba(0,0,0,0.15);
                }
            </style>
        `);
    }
});
