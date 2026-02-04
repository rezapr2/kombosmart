'use strict';

(function ($) {
    jQuery(document).ready(function ($) {
        if ($('.blog-top-posts-slider').length > 0) {
            const $wrapper = $('.blog-top-posts-slider');

            const blogTopSlider = new Swiper($wrapper[0], {
                loop: false,
                slidesPerView: 1.2,
                spaceBetween: 10,
                grabCursor: true,
                pagination: {
                    el: '.swiper-pagination',
                    clickable: true,
                },
                breakpoints: {
                    768: {
                        enabled: false,
                        slidesPerView: 'auto',
                        spaceBetween: 0
                    }
                }
            });
        }
    });
})(jQuery);
