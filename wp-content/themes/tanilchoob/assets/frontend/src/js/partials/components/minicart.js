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

        // Remove item from cart
        jQuery('.minicart__item-remove').on('click', function () {
            console.log('Removing item from cart');
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
                    var count = res.data.cart_count || 0;
                    var $badge = $trigger.find('.minicart-badge');
                    if (count > 0) {
                        if ($badge.length) { $badge.text(count); }
                        else { $trigger.append('<span class="minicart-badge">' + count + '</span>'); }
                    } else {
                        $badge.remove();
                    }
                    $(document.body).trigger('wc_fragments_refreshed');
                } else {
                    $btn.removeClass('is-loading');
                }
            }).fail(function () {
                $btn.removeClass('is-loading');
            });
        });

        // Refresh badge count when WooCommerce fragments are updated
        $(document.body).on('wc_fragments_refreshed wc_fragments_loaded', function () {
            var count = typeof wc_cart_fragments_params !== 'undefined'
                ? parseInt($('.minicart__list .minicart__item').length)
                : 0;
            var $badge = $trigger.find('.minicart-badge');
            if (count > 0) {
                if ($badge.length) {
                    $badge.text(count);
                } else {
                    $trigger.append('<span class="minicart-badge">' + count + '</span>');
                }
            } else {
                $badge.remove();
            }
        });

    });
})(jQuery);
