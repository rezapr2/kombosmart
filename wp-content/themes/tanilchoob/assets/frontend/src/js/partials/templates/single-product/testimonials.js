'use strict';

(function ($) {
    jQuery(document).ready(function ($) {
        // Initialize product gallery with Swiper
        if ($('.testimonial-slider').length) {
            const $wrapper = $(this);

            // Get navigation elements within this specific wrapper
            const $nextButton = $wrapper.find('.button-next');
            const $prevButton = $wrapper.find('.button-prev');
            // Initialize main slider
            var galleryMain = new Swiper('.testimonial-slider', {
                slidesPerView: 1,
                spaceBetween: 50,
                loop: true,
                autoplay: {
                    delay: 2000,
                },
                speed: 500,
                effect: 'slide',
                // Navigation arrows
                navigation: {
                    nextEl: $nextButton[0],
                    prevEl: $prevButton[0],
                },
            });
        }
    });
})(jQuery);
