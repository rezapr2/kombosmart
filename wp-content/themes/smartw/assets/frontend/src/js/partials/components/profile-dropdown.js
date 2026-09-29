'use strict';

(function ($) {
    jQuery(document).ready(function ($) {

        var $trigger = $('#tc-profile-trigger');
        var $dropdown = $('#tc-profile-dropdown');
        var $overlay = $('#tc-profile-overlay');

        function openDropdown() {
            $dropdown.addClass('is-open');
            $overlay.addClass('is-open');
            $trigger.attr('aria-expanded', 'true');
        }

        function closeDropdown() {
            $dropdown.removeClass('is-open');
            $overlay.removeClass('is-open');
            $trigger.attr('aria-expanded', 'false');
        }

        $trigger.on('click', function (e) {
            e.stopPropagation();
            $dropdown.hasClass('is-open') ? closeDropdown() : openDropdown();
        });

        $overlay.on('click', closeDropdown);

        $(document).on('keydown', function (e) {
            if (e.key === 'Escape') closeDropdown();
        });

        $dropdown.on('click', function (e) {
            e.stopPropagation();
        });

    });
})(jQuery);
