/**
 * Yucca Club - UI Enhancements & Polish (jQuery-based)
 * Progressive enhancement layer - gracefully degrades if jQuery fails
 * All core functionality works without this file
 */

// Wait for DOM and ensure jQuery is loaded
(function() {
    'use strict';
    
    // Check if jQuery is available
    if (typeof jQuery === 'undefined') {
        console.warn('jQuery not loaded - UI enhancements disabled (core functionality still works)');
        return;
    }
    
    // jQuery is available - proceed with enhancements
    jQuery(document).ready(function($) {
        
        // ============================================
        // 1. SMOOTH SCROLLING
        // ============================================
        
        // Smooth scroll to anchor links
        $('a[href^="#"]:not([href="#"])').on('click', function(e) {
            const target = $(this.getAttribute('href'));
            if (target.length) {
                e.preventDefault();
                $('html, body').stop().animate({
                    scrollTop: target.offset().top - 80
                }, 600, 'swing');
            }
        });
        
        // Back to top button smooth scroll
        $('#back-to-top').on('click', function(e) {
            e.preventDefault();
            $('html, body').stop().animate({ scrollTop: 0 }, 600);
        });
        
        
        // ============================================
        // 2. FADE-IN ANIMATIONS
        // ============================================
        
        // Fade in elements as they enter viewport
        function fadeInOnScroll() {
            $('.fade-in').each(function() {
                const elementTop = $(this).offset().top;
                const elementBottom = elementTop + $(this).outerHeight();
                const viewportTop = $(window).scrollTop();
                const viewportBottom = viewportTop + $(window).height();
                
                if (elementBottom > viewportTop && elementTop < viewportBottom) {
                    $(this).addClass('visible');
                }
            });
        }
        
        // Initial check
        fadeInOnScroll();
        
        // Check on scroll (throttled)
        let scrollTimeout;
        $(window).on('scroll', function() {
            clearTimeout(scrollTimeout);
            scrollTimeout = setTimeout(fadeInOnScroll, 100);
        });
        
        
        // ============================================
        // 3. FORM ENHANCEMENTS
        // ============================================
        
        // Add focus/blur animations to form inputs
        $('.form-input, input[type="text"], input[type="email"], input[type="password"], textarea').each(function() {
            $(this)
                .on('focus', function() {
                    $(this).parent().addClass('input-focused');
                })
                .on('blur', function() {
                    $(this).parent().removeClass('input-focused');
                    if ($(this).val()) {
                        $(this).parent().addClass('input-filled');
                    } else {
                        $(this).parent().removeClass('input-filled');
                    }
                });
        });
        
        // Float labels for better UX
        $('.form-input').each(function() {
            if ($(this).val()) {
                $(this).parent().addClass('input-filled');
            }
        });
        
        
        // ============================================
        // 4. IMAGE LAZY LOADING ENHANCEMENT
        // ============================================
        
        // Add loading state to images
        $('img[data-src]').each(function() {
            const $img = $(this);
            $img.addClass('lazy-loading');
            
            // Create intersection observer fallback
            const img = new Image();
            img.onload = function() {
                $img.attr('src', $img.data('src')).removeClass('lazy-loading').addClass('lazy-loaded');
            };
            img.onerror = function() {
                $img.addClass('lazy-error');
            };
            img.src = $img.data('src');
        });
        
        
        // ============================================
        // 5. MODAL ENHANCEMENTS
        // ============================================
        
        // Add smooth transitions to modals
        $('.modal-overlay').each(function() {
            $(this).on('transitionend', function() {
                if (!$(this).hasClass('visible')) {
                    $(this).css('display', 'none');
                }
            });
        });
        
        // Close modal on ESC key
        $(document).on('keydown', function(e) {
            if (e.key === 'Escape' || e.keyCode === 27) {
                $('.modal-overlay.visible').removeClass('visible');
            }
        });
        
        // Close modal on backdrop click (enhanced)
        $('.modal-overlay').on('click', function(e) {
            if ($(e.target).hasClass('modal-overlay')) {
                $(this).removeClass('visible');
            }
        });
        
        
        // ============================================
        // 6. BUTTON RIPPLE EFFECT
        // ============================================
        
        $('.cta-button, button, .btn').on('click', function(e) {
            const $button = $(this);
            const $ripple = $('<span class="ripple"></span>');
            
            const diameter = Math.max($button.outerWidth(), $button.outerHeight());
            const radius = diameter / 2;
            
            const offset = $button.offset();
            const x = e.pageX - offset.left - radius;
            const y = e.pageY - offset.top - radius;
            
            $ripple.css({
                width: diameter,
                height: diameter,
                left: x + 'px',
                top: y + 'px'
            });
            
            $button.append($ripple);
            
            setTimeout(function() {
                $ripple.remove();
            }, 600);
        });
        
        
        // ============================================
        // 7. SMOOTH HEIGHT TRANSITIONS
        // ============================================
        
        // FAQ accordions with smooth height
        $('.faq-question').on('click', function() {
            const $answer = $(this).next('.faq-answer');
            const isOpen = $(this).attr('aria-expanded') === 'true';
            
            if (isOpen) {
                $answer.slideUp(300);
                $(this).attr('aria-expanded', 'false');
            } else {
                // Close other open FAQs
                $('.faq-question[aria-expanded="true"]')
                    .attr('aria-expanded', 'false')
                    .next('.faq-answer')
                    .slideUp(300);
                
                $answer.slideDown(300);
                $(this).attr('aria-expanded', 'true');
            }
        });
        
        
        // ============================================
        // 8. CARD HOVER EFFECTS
        // ============================================
        
        $('.story-card, .guide-card, .pricing-card, .post-card').hover(
            function() {
                $(this).addClass('hover-active');
            },
            function() {
                $(this).removeClass('hover-active');
            }
        );
        
        
        // ============================================
        // 9. PARALLAX SCROLLING (SUBTLE)
        // ============================================
        
        if ($('.hero-section').length) {
            let lastScrollTop = 0;
            $(window).on('scroll', function() {
                const scrollTop = $(this).scrollTop();
                const offset = scrollTop * 0.5;
                
                $('.hero-section').css({
                    'transform': 'translateY(' + offset + 'px)'
                });
                
                lastScrollTop = scrollTop;
            });
        }
        
        
        // ============================================
        // 10. LOADING STATE MANAGEMENT
        // ============================================
        
        // Add loading states to AJAX operations
        $(document).ajaxStart(function() {
            $('body').addClass('loading');
        }).ajaxStop(function() {
            $('body').removeClass('loading');
        });
        
        
        // ============================================
        // 11. TOOLTIP ENHANCEMENTS
        // ============================================
        
        // Simple tooltips for elements with title attribute
        $('[title]').each(function() {
            const $el = $(this);
            const title = $el.attr('title');
            
            if (title && title.trim()) {
                $el.data('original-title', title);
                $el.removeAttr('title'); // Prevent default browser tooltip
                
                $el.on('mouseenter', function(e) {
                    const $tooltip = $('<div class="custom-tooltip"></div>').text(title);
                    $('body').append($tooltip);
                    
                    const offset = $el.offset();
                    $tooltip.css({
                        top: offset.top - $tooltip.outerHeight() - 10,
                        left: offset.left + ($el.outerWidth() / 2) - ($tooltip.outerWidth() / 2)
                    });
                    
                    setTimeout(function() {
                        $tooltip.addClass('visible');
                    }, 10);
                });
                
                $el.on('mouseleave', function() {
                    $('.custom-tooltip').removeClass('visible');
                    setTimeout(function() {
                        $('.custom-tooltip').remove();
                    }, 200);
                });
            }
        });
        
        
        // ============================================
        // 12. INFINITE SCROLL PREPARATION
        // ============================================
        
        // Detect when user is near bottom of page
        let isLoadingMore = false;
        
        $(window).on('scroll', function() {
            if (isLoadingMore) return;
            
            const scrollPosition = $(window).scrollTop() + $(window).height();
            const pageHeight = $(document).height();
            
            if (scrollPosition > pageHeight - 500) {
                // User is near bottom - trigger custom event for pages to handle
                $(document).trigger('nearPageBottom');
            }
        });
        
        
        // ============================================
        // 13. COPY TO CLIPBOARD ENHANCEMENT
        // ============================================
        
        $('.copy-button, [data-copy]').on('click', function(e) {
            e.preventDefault();
            const textToCopy = $(this).data('copy') || $(this).attr('href');
            
            if (navigator.clipboard && textToCopy) {
                navigator.clipboard.writeText(textToCopy).then(function() {
                    // Show temporary success message
                    const $msg = $('<span class="copy-success">Copied!</span>');
                    $(e.target).append($msg);
                    
                    setTimeout(function() {
                        $msg.fadeOut(function() {
                            $(this).remove();
                        });
                    }, 2000);
                });
            }
        });
        
        
        // ============================================
        // 14. STICKY HEADER ENHANCEMENT
        // ============================================
        
        let lastScroll = 0;
        const $header = $('.site-header');
        
        if ($header.length) {
            $(window).on('scroll', function() {
                const currentScroll = $(this).scrollTop();
                
                if (currentScroll > 100) {
                    $header.addClass('scrolled');
                } else {
                    $header.removeClass('scrolled');
                }
                
                // Hide header on scroll down, show on scroll up
                if (currentScroll > lastScroll && currentScroll > 200) {
                    $header.addClass('header-hidden');
                } else {
                    $header.removeClass('header-hidden');
                }
                
                lastScroll = currentScroll;
            });
        }
        
        
        // ============================================
        // 15. PRELOAD CRITICAL IMAGES
        // ============================================
        
        // Preload images marked as critical
        $('[data-preload]').each(function() {
            const src = $(this).data('preload');
            const img = new Image();
            img.src = src;
        });
        
        
        // ============================================
        // 16. SMART FORM VALIDATION
        // ============================================
        
        // Real-time validation feedback
        $('input[type="email"]').on('blur', function() {
            const email = $(this).val();
            const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            
            if (email && !emailRegex.test(email)) {
                $(this).addClass('invalid');
            } else {
                $(this).removeClass('invalid');
            }
        });
        
        $('input[type="password"]').on('input', function() {
            const password = $(this).val();
            const $strengthIndicator = $(this).siblings('.password-strength');
            
            if ($strengthIndicator.length) {
                let strength = 0;
                if (password.length >= 8) strength++;
                if (password.match(/[a-z]/) && password.match(/[A-Z]/)) strength++;
                if (password.match(/\d/)) strength++;
                if (password.match(/[^a-zA-Z\d]/)) strength++;
                
                $strengthIndicator
                    .removeClass('weak medium strong')
                    .addClass(['weak', 'weak', 'medium', 'strong'][strength] || '');
            }
        });
        
        
        // ============================================
        // 17. DROPDOWN MENU ENHANCEMENTS
        // ============================================
        
        $('.dropdown, .mobile-dropdown').each(function() {
            const $dropdown = $(this);
            let closeTimeout;
            
            $dropdown.on('mouseenter', function() {
                clearTimeout(closeTimeout);
                $(this).addClass('open');
            });
            
            $dropdown.on('mouseleave', function() {
                closeTimeout = setTimeout(function() {
                    $dropdown.removeClass('open');
                }, 200);
            });
        });
        
        
        // ============================================
        // 18. ACCESSIBILITY ENHANCEMENTS
        // ============================================
        
        // Skip to main content link
        $('body').prepend('<a href="#main-content" class="skip-link">Skip to main content</a>');
        
        // Focus management for modals
        $('.modal-overlay').on('show', function() {
            $(this).find('input, button, a, textarea').first().focus();
        });
        
        // Trap focus inside modal when open
        $(document).on('keydown', function(e) {
            if (e.key === 'Tab') {
                const $visibleModal = $('.modal-overlay.visible');
                if ($visibleModal.length) {
                    const $focusable = $visibleModal.find('button, a, input, textarea, select');
                    const $first = $focusable.first();
                    const $last = $focusable.last();
                    
                    if (e.shiftKey && document.activeElement === $first[0]) {
                        e.preventDefault();
                        $last.focus();
                    } else if (!e.shiftKey && document.activeElement === $last[0]) {
                        e.preventDefault();
                        $first.focus();
                    }
                }
            }
        });
        
        
        // ============================================
        // 19. PERFORMANCE OPTIMIZATIONS
        // ============================================
        
        // Debounce window resize events
        let resizeTimeout;
        $(window).on('resize', function() {
            clearTimeout(resizeTimeout);
            resizeTimeout = setTimeout(function() {
                $(window).trigger('optimizedResize');
            }, 250);
        });
        
        
        // ============================================
        // 20. CUSTOM EVENTS FOR EXTENSIBILITY
        // ============================================
        
        // Trigger custom events that other scripts can listen to
        $(window).on('load', function() {
            $(document).trigger('yuccaClubReady');
        });
        
        // Announce when enhancements are loaded
        $(document).trigger('yuccaClubEnhanced');
        
        console.log('✨ Yucca Club UI Enhancements loaded successfully');
    });
    
})();

// ============================================
// GLOBAL UTILITY FUNCTIONS (No jQuery required)
// ============================================

// These work even if jQuery fails to load

window.YuccaClub = window.YuccaClub || {
    
    // Debounce function for performance
    debounce: function(func, wait) {
        let timeout;
        return function executedFunction(...args) {
            const later = () => {
                clearTimeout(timeout);
                func(...args);
            };
            clearTimeout(timeout);
            timeout = setTimeout(later, wait);
        };
    },
    
    // Throttle function for performance
    throttle: function(func, limit) {
        let inThrottle;
        return function(...args) {
            if (!inThrottle) {
                func.apply(this, args);
                inThrottle = true;
                setTimeout(() => inThrottle = false, limit);
            }
        };
    },
    
    // Check if element is in viewport
    isInViewport: function(element) {
        const rect = element.getBoundingClientRect();
        return (
            rect.top >= 0 &&
            rect.left >= 0 &&
            rect.bottom <= (window.innerHeight || document.documentElement.clientHeight) &&
            rect.right <= (window.innerWidth || document.documentElement.clientWidth)
        );
    },
    
    // Smooth scroll to element (vanilla JS fallback)
    scrollTo: function(element, offset = 0) {
        const target = typeof element === 'string' ? document.querySelector(element) : element;
        if (target) {
            const top = target.getBoundingClientRect().top + window.pageYOffset - offset;
            window.scrollTo({ top: top, behavior: 'smooth' });
        }
    }
};



