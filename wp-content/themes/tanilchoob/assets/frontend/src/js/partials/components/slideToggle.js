'use strict';

(function ($) {
    jQuery(document).ready(function ($) {
        // Slide down accordion functionality
        $('.slide-down-trigger').on('click', function() {
            const $slideDownItem = $(this).parent('.slide-down-wrapper');
            const $slideDownContent = $(this).next('.slide-down-content');
            
            // Toggle active class
            $slideDownItem.toggleClass('active');
                        
            // Slide toggle the answer
            $slideDownContent.slideToggle(300);
            
            
        });
    });
})(jQuery);
