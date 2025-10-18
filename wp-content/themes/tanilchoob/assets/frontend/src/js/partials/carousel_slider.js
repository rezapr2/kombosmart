'use strict';

(function ($) {
    // Countdown timer function
    function initCountdownTimers() {
        $('.countdown-timer').each(function() {
            const $timer = $(this);
            const endDate = new Date($timer.data('end-date')).getTime();
            
            // Update the countdown every second
            const countdownInterval = setInterval(function() {
                // Get current date and time
                const now = new Date().getTime();
                
                // Calculate the time remaining
                const timeRemaining = endDate - now;
                
                // Calculate days, hours, minutes, and seconds
                const days = Math.floor(timeRemaining / (1000 * 60 * 60 * 24));
                const hours = Math.floor((timeRemaining % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
                const minutes = Math.floor((timeRemaining % (1000 * 60 * 60)) / (1000 * 60));
                const seconds = Math.floor((timeRemaining % (1000 * 60)) / 1000);
                
                // Display the result
                $timer.find('.countdown-timer__days').text(days < 10 ? '0' + days : days);
                $timer.find('.countdown-timer__hours').text(hours < 10 ? '0' + hours : hours);
                $timer.find('.countdown-timer__minutes').text(minutes < 10 ? '0' + minutes : minutes);
                $timer.find('.countdown-timer__seconds').text(seconds < 10 ? '0' + seconds : seconds);
                
                // If the countdown is finished, display a message
                if (timeRemaining < 0) {
                    clearInterval(countdownInterval);
                    $timer.html('<p>پایان یافته</p>');
                }
            }, 1000);
        });
    }

    jQuery(document).ready(function ($) {
        // Initialize countdown timers
        initCountdownTimers();
        
        if ($('.carousel_slider-wrapper').length > 0) {
            // Initialize each carousel slider separately
            $('.carousel_slider-wrapper').each(function(index) {
                const $wrapper = $(this);
                const $slider = $wrapper.find('.carousel_slider');
                
                // Get custom settings from data attributes
                const slidesPerView = $wrapper.data('slidesperview') || 2.2;
                
                // Get navigation elements within this specific wrapper
                const $nextButton = $wrapper.find('.button-next');
                const $prevButton = $wrapper.find('.button-prev');
                
                const carouselSlider = new Swiper($slider[0], {
                    // Basic settings
                    loop: true,
                    autoplay: {
                        delay: 5000,
                        disableOnInteraction: false,
                        pauseOnMouseEnter: true,
                    },
                    speed: 800,
                    effect: 'slide',
                    fadeEffect: {
                        crossFade: true,
                    },

                    // Pagination
                    pagination: false,

                    // Navigation arrows
                    navigation: {
                        nextEl: $nextButton[0],
                        prevEl: $prevButton[0],
                    },

                    // Use the data attribute value or default
                    slidesPerView: slidesPerView,
                    spaceBetween: 24,

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
                });
            });
        }
    });
})(jQuery);
