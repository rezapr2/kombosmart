'use strict';

(function ($) {
    jQuery(document).ready(function ($) {
        var $btn = $('#load-more-category-posts');
        if (!$btn.length) return;
        var $container = $('.main_posts');
        var $spinner = $btn.find('.spinner');
        var loading = false;

        $btn.on('click', function () {
            if (loading) return;
            var page = parseInt($btn.data('page'), 10) || 1;
            var max = parseInt($btn.data('max'), 10) || 1;
            var next = page + 1;
            if (next > max) return;

            loading = true;
            if ($spinner.length) $spinner.removeClass('hidden');

            var urlStr;
            try {
                var url = new URL(window.location.href);
                url.searchParams.set('paged', String(next));
                urlStr = url.toString();
            } catch (e) {
                var sep = window.location.href.indexOf('?') === -1 ? '?' : '&';
                urlStr = window.location.href + sep + 'paged=' + String(next);
            }

            $.get(urlStr)
                .done(function (html) {
                    try {
                        var doc = new DOMParser().parseFromString(html, 'text/html');
                        var $next = $(doc).find('.main_posts').first();
                        if ($next.length) {
                            var $cards = $next.find('.blog-card');
                            if ($cards.length && $container.length) {
                                $container.append($cards);
                                $btn.parent().before($cards);
                                $btn.data('page', next);
                                if (next >= max) {
                                    $btn.closest('.load-more-container').remove();
                                }
                            } else {
                                $btn.closest('.load-more-container').remove();
                            }
                        } else {
                            $btn.closest('.load-more-container').remove();
                        }
                    } catch (err) {
                        $btn.closest('.load-more-container').remove();
                    }
                })
                .always(function () {
                    loading = false;
                    if ($spinner.length) $spinner.addClass('hidden');
                });
        });
    });
})(jQuery);
