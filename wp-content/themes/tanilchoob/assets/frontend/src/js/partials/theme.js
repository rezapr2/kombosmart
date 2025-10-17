'use strict';

(function ($) {
    jQuery(document).ready(function ($) {
        if ($('.hero-slider').length > 0) {
            const heroSlider = new Swiper('.hero-slider', {
                // Basic settings
                loop: true,
                autoplay: {
                    delay: 5000,
                    disableOnInteraction: false,
                    pauseOnMouseEnter: true,
                },
                speed: 800,
                effect: 'fade',
                fadeEffect: {
                    crossFade: true,
                },

                // Pagination
                pagination: false,

                // Navigation arrows
                navigation: {
                    nextEl: '.hero-slider .swiper-button-next',
                    prevEl: '.hero-slider .swiper-button-prev',
                },

                slidesPerView: 1,
                spaceBetween: 0,

                // Accessibility
                a11y: {
                    prevSlideMessage: 'Previous slide',
                    nextSlideMessage: 'Next slide',
                    firstSlideMessage: 'This is the first slide',
                    lastSlideMessage: 'This is the last slide',
                    paginationBulletMessage: 'Go to slide {{index}}',
                },

                // Keyboard control
                keyboard: {
                    enabled: true,
                    onlyInViewport: true,
                },

                // Mouse wheel control
                mousewheel: {
                    enabled: false,
                },

                // Touch settings
                touchRatio: 1,
                touchAngle: 45,
                grabCursor: true,

                // Lazy loading
                lazy: {
                    loadPrevNext: true,
                    loadPrevNextAmount: 1,
                },

                // Callbacks
                on: {
                    init: function () {
                        console.log('Hero slider initialized');
                        // Add fade-in animation to first slide content
                        $(
                            '.hero-slider .swiper-slide-active .hero-slider-item'
                        ).addClass('animate-fade-in');
                    },
                    slideChange: function () {
                        // Remove animation class from all slides
                        $('.hero-slider .hero-slider-item').removeClass(
                            'animate-fade-in'
                        );

                        // Add animation class to active slide
                        setTimeout(() => {
                            $(
                                '.hero-slider .swiper-slide-active .hero-slider-item'
                            ).addClass('animate-fade-in');
                        }, 100);
                    },
                    autoplayStart: function () {
                        $('.hero-slider-wrapper').addClass('autoplay-active');
                    },
                    autoplayStop: function () {
                        $('.hero-slider-wrapper').removeClass(
                            'autoplay-active'
                        );
                    },
                },
            });

            // Pause autoplay on hover
            $('.hero-slider').hover(
                function () {
                    heroSlider.autoplay.stop();
                },
                function () {
                    heroSlider.autoplay.start();
                }
            );

            // Handle button clicks with smooth transitions
            $('.hero-slider .btn').on('click', function (e) {
                const $btn = $(this);
                $btn.addClass('btn-clicked');

                setTimeout(() => {
                    $btn.removeClass('btn-clicked');
                }, 300);
            });


            // Keyboard navigation
            $(document).on('keydown', function (e) {
                if ($('.hero-slider:hover').length > 0) {
                    if (e.keyCode === 37) {
                        // Left arrow
                        heroSlider.slidePrev();
                    } else if (e.keyCode === 39) {
                        // Right arrow
                        heroSlider.slideNext();
                    } else if (e.keyCode === 32) {
                        // Spacebar
                        e.preventDefault();
                        if (heroSlider.autoplay.running) {
                            heroSlider.autoplay.stop();
                        } else {
                            heroSlider.autoplay.start();
                        }
                    }
                }
            });

            // Touch gestures for mobile
            let touchStartX = 0;
            let touchEndX = 0;

            $('.hero-slider').on('touchstart', function (e) {
                touchStartX = e.originalEvent.changedTouches[0].screenX;
            });

            $('.hero-slider').on('touchend', function (e) {
                touchEndX = e.originalEvent.changedTouches[0].screenX;
                handleSwipe();
            });

            function handleSwipe() {
                const swipeThreshold = 50;
                const diff = touchStartX - touchEndX;

                if (Math.abs(diff) > swipeThreshold) {
                    if (diff > 0) {
                        heroSlider.slideNext();
                    } else {
                        heroSlider.slidePrev();
                    }
                }
            }

            // Resize handler
            $(window).on('resize', function () {
                if (heroSlider) {
                    heroSlider.update();
                }
            });

            // Visibility change handler (pause when tab is not active)
            document.addEventListener('visibilitychange', function () {
                if (document.hidden) {
                    heroSlider.autoplay.stop();
                } else {
                    heroSlider.autoplay.start();
                }
            });
        }
    });
})(jQuery);
