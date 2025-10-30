'use strict';

(function ($) {
    jQuery(document).ready(function ($) {
        // Initialize sub-products with Swiper
        if ($('.sub-products').length) {
            // Initialize thumbnail slider
            var galleryThumbs = new Swiper('.sub-products', {
                slidesPerView: 'auto',
                freeMode: true,
                grabCursor: true,
            });
            
        }
    });
})(jQuery);
