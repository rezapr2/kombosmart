(function ($) {
    jQuery(document).ready(function ($) {
        
        let blogSlider = null;

        function initBlogSlider() {
            const sliderContainer = $('.blog-top-posts-slider');
            
            if (sliderContainer.length === 0) return;

            if (window.innerWidth < 768) {
                if (!blogSlider) {
                    blogSlider = new Swiper('.blog-top-posts-slider', {
                        slidesPerView: 1,
                        spaceBetween: 10,
                        centeredSlides: true,
                        loop: true,
                        autoplay: {
                            delay: 3000,
                            disableOnInteraction: false,
                        },
                        pagination: {
                            el: '.swiper-pagination',
                            clickable: true,
                        },
                    });
                }
            } else {
                if (blogSlider) {
                    blogSlider.destroy(true, true);
                    blogSlider = null;
                }
            }
        }

        // Init on load
        initBlogSlider();

        // Init on resize
        $(window).on('resize', function() {
            initBlogSlider();
        });
        
    });
})(jQuery);
