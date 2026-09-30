'use strict';

(function ($) {
    jQuery(document).ready(function ($) {
        $('#load-more-posts').on('click', function () {
            var button = $(this);
            var page = button.data('page');
            var max = button.data('max');
            var next_page = page + 1;

            if (next_page > max) {
                return;
            }

            button.find('.spinner').removeClass('hidden');

            $.ajax({
                url: smartw.ajax.url,
                type: 'POST',
                data: {
                    action: 'tanilchoob_load_more_posts',
                    page: next_page,
                    nonce: smartw.ajax.nonce,
                },
                success: function (response) {
                    if (response) {
                        button.data('page', next_page);
                        button.closest('.main_posts').find('article:last').after(response);
                        button.find('.spinner').addClass('hidden');

                        if (next_page >= max) {
                            button.remove();
                        }
                    } else {
                        button.remove();
                    }
                },
            });
        });
    });
})(jQuery);
