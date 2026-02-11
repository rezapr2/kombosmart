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

    });
})(jQuery);
