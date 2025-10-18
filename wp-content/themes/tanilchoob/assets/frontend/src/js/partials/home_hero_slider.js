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

        }
    });
})(jQuery);
