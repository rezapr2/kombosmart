'use strict';

(function ($) {
    jQuery(document).ready(function ($) {

        var $trigger = $('#tc-minicart-trigger');
        var $minicart = $('#tc-minicart');
        var $overlay = $('#tc-minicart-overlay');

        function openMinicart() {
            $minicart.addClass('is-open');
            $overlay.addClass('is-open');
            $trigger.attr('aria-expanded', 'true');
        }

        function closeMinicart() {
            $minicart.removeClass('is-open');
            $overlay.removeClass('is-open');
            $trigger.attr('aria-expanded', 'false');
        }

        $trigger.on('click', function (e) {
            e.stopPropagation();
            $minicart.hasClass('is-open') ? closeMinicart() : openMinicart();
        });

        $overlay.on('click', closeMinicart);

        $(document).on('keydown', function (e) {
            if (e.key === 'Escape') closeMinicart();
        });

        // Keep minicart open when clicking inside it
        $minicart.on('click', function (e) {
            e.stopPropagation();
        });

        // Delegate inside $minicart — $(document) never receives the event because
        // $minicart.on('click', …) calls e.stopPropagation() on every click inside it.
        $minicart.on('click', '.minicart__item-remove', function () {
            var $btn = $(this);
            if ($btn.hasClass('is-loading')) return;
            $btn.addClass('is-loading');

            $.post($btn.data('ajax-url'), {
                action:   'tc_remove_cart_item',
                nonce:    $btn.data('nonce'),
                cart_key: $btn.data('cart-key')
            }, function (res) {
                if (res && res.success) {
                    if (res.data.fragments) {
                        $.each(res.data.fragments, function (key, value) {
                            $(key).replaceWith(value);
                        });
                    }
                } else {
                    $btn.removeClass('is-loading');
                }
            }).fail(function () {
                $btn.removeClass('is-loading');
            });
        });

    });
})(jQuery);
