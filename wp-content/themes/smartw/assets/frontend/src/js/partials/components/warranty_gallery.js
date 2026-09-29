'use strict';

(function ($) {
    $(document).ready(function ($) {
        const swiperElement = $('.warranty_gallery .swiper');
        if (!swiperElement.length) return;
        const swiperConfig = {
            speed: 900,
            grabCursor: true,
            centeredSlides: true,
            effect: 'coverflow',
            coverflowEffect: {
                rotate: 0,
                stretch: -20,
                depth: 20,
                modifier: 1,
                scale: .9,
                slideShadows: false,
            },
            loop: true,
            slidesPerView: 2,
            slidesPerGroup: 1,
            initialSlide: 0,
            on: {
                afterInit: function () {
                    this.slidePrev(1500);
                },
            },

        };

        var warrantyGallerySwiper;

        warrantyGallerySwiper = new Swiper(swiperElement[0], swiperConfig);
    });
})(jQuery);

