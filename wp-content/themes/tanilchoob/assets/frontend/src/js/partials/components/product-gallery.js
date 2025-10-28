'use strict';

(function ($) {
    jQuery(document).ready(function ($) {
        // Initialize product gallery with Swiper
        if ($('.product-gallery-container').length) {
            // Initialize thumbnail slider
            var galleryThumbs = new Swiper('.gallery-thumbs', {
                spaceBetween: 10,
                slidesPerView: 4,
                direction: 'vertical',
                watchSlidesVisibility: true,
                watchSlidesProgress: true,
                breakpoints: {
                    // when window width is <= 768px
                    768: {
                        direction: 'vertical',
                        slidesPerView: 3,
                    }
                }
            });
            
            // Initialize main slider
            var galleryMain = new Swiper('.gallery-main', {
                spaceBetween: 10,
                navigation: {
                    nextEl: '.swiper-button-next',
                    prevEl: '.swiper-button-prev',
                },
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
