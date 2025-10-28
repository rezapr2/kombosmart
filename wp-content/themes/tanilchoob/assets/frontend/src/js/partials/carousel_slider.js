'use strict';

(function ($) {
   

    jQuery(document).ready(function ($) {
        
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
