'use strict';

(function ($) {
    // Countdown timer function
    function initCountdownTimers() {
        $('.countdown-timer').each(function() {
            const $timer = $(this);
            const endDate = new Date($timer.data('end-date')).getTime();
            
            // Update the countdown every second
            const countdownInterval = setInterval(function() {
                // Get current date and time
                const now = new Date().getTime();
                
                // Calculate the time remaining
                const timeRemaining = endDate - now;
                
                // Calculate days, hours, minutes, and seconds
                const days = Math.floor(timeRemaining / (1000 * 60 * 60 * 24));
                const hours = Math.floor((timeRemaining % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
                const minutes = Math.floor((timeRemaining % (1000 * 60 * 60)) / (1000 * 60));
                const seconds = Math.floor((timeRemaining % (1000 * 60)) / 1000);
                
                // Display the result
                $timer.find('.countdown-timer__days').text(days < 10 ? '0' + days : days);
                $timer.find('.countdown-timer__hours').text(hours < 10 ? '0' + hours : hours);
                $timer.find('.countdown-timer__minutes').text(minutes < 10 ? '0' + minutes : minutes);
                $timer.find('.countdown-timer__seconds').text(seconds < 10 ? '0' + seconds : seconds);
                
                // If the countdown is finished, display a message
                if (timeRemaining < 0) {
                    clearInterval(countdownInterval);
                    $timer.html('<p>پایان یافته</p>');
                }
            }, 1000);
        });
    }

    jQuery(document).ready(function ($) {
        // Initialize countdown timers
        initCountdownTimers();
        
    });
})(jQuery);
