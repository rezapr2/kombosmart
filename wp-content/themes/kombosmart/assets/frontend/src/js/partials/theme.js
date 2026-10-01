'use strict';

(function ($) {
    jQuery(document).ready(function ($) {
        
        // Products Menu Dropdown Toggle
        $('.products-menu-dropdown').on('click', function(e) {
            e.stopPropagation();
            $(this).toggleClass('active');
            $('body').toggleClass('products-menu-open', $(this).hasClass('active'));
        });

        // Close dropdown when clicking outside
        $(document).on('click', function(e) {
            if (!$(e.target).closest('.products-menu-dropdown').length) {
                $('.products-menu-dropdown').removeClass('active');
                $('body').removeClass('products-menu-open');
            }
        });

        $('.products-menu-overlay').on('click', function() {
            $('.products-menu-dropdown').removeClass('active');
            $('body').removeClass('products-menu-open');
        });

        var $aboutDescWrappers = $('.about-desc-readmore');
        if ($aboutDescWrappers.length) {
            function updateAboutDesc() {
                var isMobile = window.innerWidth <= 768;
                $aboutDescWrappers.each(function() {
                    var $wrapper = $(this);
                    var $content = $wrapper.find('.about-desc-content');
                    var $toggle = $wrapper.find('.about-desc-toggle');

                    $toggle.hide();
                    if (!isMobile) {
                        $wrapper.removeClass('is-collapsed is-expanded');
                        return;
                    }

                    if ($wrapper.hasClass('is-expanded')) {
                        return;
                    }

                    var lineHeight = parseFloat(window.getComputedStyle($content[0]).lineHeight);
                    if (!lineHeight || isNaN(lineHeight)) {
                        lineHeight = 24;
                    }
                    var fullHeight = $content[0].scrollHeight;
                    var maxHeight = lineHeight * 6;

                    if (fullHeight > maxHeight + 1) {
                        $wrapper.addClass('is-collapsed');
                        $toggle.show();
                    } else {
                        $wrapper.removeClass('is-collapsed is-expanded');
                    }
                });
            }

            updateAboutDesc();
            $(window).on('resize', updateAboutDesc);

            $(document).on('click', '.about-desc-readmore .about-desc-toggle', function() {
                var $wrapper = $(this).closest('.about-desc-readmore');
                $wrapper.removeClass('is-collapsed').addClass('is-expanded');
                $(this).hide();
            });
        }

    });
})(jQuery);
