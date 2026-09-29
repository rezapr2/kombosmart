'use strict';

(function ($) {
    jQuery(document).ready(function ($) {
        var $slider = $('.home-hero__slider');
        if (!$slider.length || $slider.find('.swiper-slide').length < 2) {
            return;
        }

        new Swiper($slider[0], {
            loop: true,
            speed: 800,
            effect: 'fade',
            fadeEffect: {
                crossFade: true,
            },
            autoplay: {
                delay: 6000,
                disableOnInteraction: false,
                pauseOnMouseEnter: true,
            },
            navigation: {
                nextEl: $slider.find('.home-hero__next')[0],
                prevEl: $slider.find('.home-hero__prev')[0],
            },
            pagination: {
                el: $slider.find('.home-hero__pagination')[0],
                type: 'fraction',
            },
            grabCursor: true,
        });
    });
})(jQuery);
