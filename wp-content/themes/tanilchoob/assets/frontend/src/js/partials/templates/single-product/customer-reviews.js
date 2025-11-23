'use strict';

(function ($) {
    jQuery(document).ready(function ($) {
        var $container = $('#respond .comment-form-rating p.stars');
        if (!$container.length) return;

        var $stars = $container.find('a');
        var $input = $('#rating');

        function setRating(val) {
            if (!$input.length) return;
            $input.val(String(val));
            $stars.each(function (i, el) {
                var $a = $(el);
                $a.toggleClass('filled', i < val);
                $a.attr('aria-checked', i === (val - 1) ? 'true' : 'false');
            });
            $container.addClass('selected');
            $('#commentform .rating-required-error').hide();
        }

        $stars.each(function (idx, el) {
            $(el).on('click', function (e) {
                e.preventDefault();
                setRating(idx + 1);
            });
            $(el).on('keydown', function (e) {
                if (e.key === 'Enter' || e.key === ' ') {
                    e.preventDefault();
                    setRating(idx + 1);
                }
            });
        });

        var $form = $('#commentform');
        if ($form.length && $input.length) {
            $form.on('submit', function (e) {
                var required = $input.data('required') === 1 || $input.data('required') === '1';
                if (required && !$input.val()) {
                    e.preventDefault();
                    var $err = $('#commentform .rating-required-error');
                    if ($err.length) { $err.show(); }
                    if ($stars.length) { $stars.first().focus(); }
                }
            });
        }
    });
})(jQuery);
