'use strict';

(function ($) {
    jQuery(document).ready(function ($) {
        // Slide down accordion functionality
        $('.slide-down-trigger').on('click', function() {
            const $slideDownItem = $(this).closest('.slide-down-wrapper');
            const $slideDownContent = $slideDownItem.find('.slide-down-content');
            
            // Toggle active class
            $slideDownItem.toggleClass('active');
            $(this).toggleClass('active');    

            // Slide toggle the answer
            $slideDownContent.slideToggle(300);
            
            
        });
    });
})(jQuery);
