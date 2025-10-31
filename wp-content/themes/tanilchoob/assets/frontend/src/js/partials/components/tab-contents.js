'use strict';

(function ($) {
    jQuery(document).ready(function ($) {
        // Tab switching functionality
        $('.tab-item').on('click', function() {
            // Remove active class from all tabs
            $('.tab-item').removeClass('active');
            // Add active class to clicked tab
            $(this).addClass('active');
            
            // Get the tab ID
            const tabId = $(this).attr('id');
            // Get the corresponding content ID
            const contentId = tabId + '-content';
            
            // Hide all tab content
            $('.tab-content-item').removeClass('active').hide();
            // Show the selected tab content
            $('#' + contentId).addClass('active').fadeIn();
        });
    });
})(jQuery);
