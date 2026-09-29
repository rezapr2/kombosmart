'use strict';

(function ($) {
    jQuery(document).ready(function ($) {
        if ($('.related-posts-slider').length > 0) {
            const $wrapper = $('.related-posts-slider');

            // Initialize Swiper
            const relatedPostsSlider = new Swiper($wrapper[0], {
                loop: false,
                slidesPerView: 1.5,
                spaceBetween: 5,
                grabCursor: true,
                breakpoints: {
                    768: {
                        slidesPerView: 4,
                        spaceBetween: 7,
                    }
                }
            });
        }
    });
})(jQuery);
