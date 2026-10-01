'use strict';

(function ($) {
    jQuery(document).ready(function ($) {
        // Tab switching functionality
        $('.tab-item').on('click', function() {
            $container = $(this).closest('.tab-contents');
            // Remove active class from all tabs
            $container.find('.tab-item').removeClass('active');

            // Add active class to clicked tab
            $(this).addClass('active');
            
            // Get the tab ID
            const tabId = $(this).attr('id');
            // Get the corresponding content ID
            const contentId = tabId + '-content';
            
            // Hide all tab content
            $container.find('.tab-content-item').removeClass('active').hide();
            // Show the selected tab content
            $('#' + contentId).addClass('active').fadeIn();
        });

        // Mobile Accordion functionality
        $('.accordion-tab-trigger').on('click', function() {
            const targetId = $(this).data('target');
            const $target = $('#' + targetId);
            const $container = $(this).closest('.tab-contents');
            const $trigger = $(this);
            
            if ($(this).hasClass('active')) {
                $(this).removeClass('active');
                $target.slideUp().removeClass('active');
            } else {
                // Close others
                $container.find('.accordion-tab-trigger').removeClass('active');
                $container.find('.tab-content-item').slideUp().removeClass('active');
                
                // Open this one
                $(this).addClass('active');
                $target.slideDown().addClass('active');
                
                // Scroll to the tab after it opens
                setTimeout(function() {
                    $('html, body').animate({
                        scrollTop: $trigger.offset().top - 100
                    }, 400);
                }, 300);
            }
        });
    });
})(jQuery);
