'use strict';

(function ($) {
    jQuery(document).ready(function ($) {
        $(document).on('click', '.tc-wishlist-btn', function () {
            var $btn = $(this);
            if ($btn.hasClass('tc-wishlist-loading')) return;
            $btn.addClass('tc-wishlist-loading');
            $.post($btn.data('ajax-url'), {
                action: 'tc_toggle_wishlist',
                nonce: $btn.data('nonce'),
                product_id: $btn.data('product-id')
            }, function (res) {
                $btn.removeClass('tc-wishlist-loading');
                if (res && res.success) {
                    var inWl = res.data.in_wishlist;
                    $btn.toggleClass('is-wishlisted', inWl);
                    $btn.find('svg path').attr('fill', inWl ? 'currentColor' : 'none');
                    $btn.find('span').text(inWl ? 'در علاقه‌مندی‌ها' : 'افزودن به علاقه مندی ها');
                    
                } else if (res && res.data) {
                    alert(res.data);
                }
            });
        });
    });
})(jQuery);
