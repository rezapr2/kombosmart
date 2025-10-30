'use strict';

(function ($) {
    jQuery(document).ready(function ($) {
        // FAQ accordion functionality
        $('.faq-question').on('click', function() {
            const $faqItem = $(this).parent('.faq-item');
            const $faqAnswer = $(this).next('.faq-answer');
            
            // Toggle active class
            $faqItem.toggleClass('active');
            
            // Toggle +/- sign
            const $toggle = $(this).find('.faq-toggle');
            
            // Slide toggle the answer
            $faqAnswer.slideToggle(300);
            
            // Close other open FAQs (optional - comment out if you want multiple open at once)
            $('.faq-item').not($faqItem).removeClass('active');
            $('.faq-answer').not($faqAnswer).slideUp(300);
            $('.faq-item').not($faqItem).find('.faq-toggle').text('+');
            
            // Update the toggle text
            $toggle.text($faqItem.hasClass('active') ? '-' : '+');
        });
    });
})(jQuery);
