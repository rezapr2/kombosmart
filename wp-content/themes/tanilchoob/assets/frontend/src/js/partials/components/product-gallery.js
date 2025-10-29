'use strict';

(function ($) {
    jQuery(document).ready(function ($) {
        // Initialize product gallery with Swiper
        if ($('.product-gallery-container').length) {
            // Initialize thumbnail slider
            var galleryThumbs = new Swiper('.gallery-thumbs', {
                slidesPerView: 'auto',
                direction: 'vertical',
                watchSlidesVisibility: true,
                watchSlidesProgress: true,
            });
            
            // Initialize main slider
            var galleryMain = new Swiper('.gallery-main', {
                slidesPerView: 1,
                spaceBetween: 10,
                thumbs: {
                    swiper: galleryThumbs
                },
                zoom: {
                    maxRatio: 2,
                    toggle: true
                }
            });
            
            // Add click event to thumbnails
            $('.gallery-thumbs .swiper-slide').on('click', function() {
                var index = $(this).index();
                galleryMain.slideTo(index);
            });
        }
    });
})(jQuery);
