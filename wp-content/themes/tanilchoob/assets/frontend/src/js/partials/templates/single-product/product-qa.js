'use strict';

(function ($) {
    jQuery(document).ready(function ($) {
        var $form = $('#product-qa-form');
        if (!$form.length) return;

        var $message = $form.find('.qa-form-message');
        var $submit = $form.find('button[type="submit"]');

        function setMessage(text, isError) {
            if (!$message.length) return;
            $message.text(text);
            $message.toggleClass('error', !!isError);
        }

        function disableForm(disabled) {
            $submit.prop('disabled', disabled);
        }

        function getFormData() {
            return {
                product_id: $form.find('input[name="product_id"]').val(),
                qa_name: $form.find('input[name="qa_name"]').val(),
                qa_question: $form.find('textarea[name="qa_question"]').val()
            };
        }

        function postQuestion(extra) {
            var payload = $.extend({}, getFormData(), {
                action: 'tanilchoob_product_question_submit',
                nonce: (window.tanilchoob && window.tanilchoob.ajax && window.tanilchoob.ajax.nonce) ? window.tanilchoob.ajax.nonce : '',
                r_version: 3
            }, extra || {});

            return $.post(window.tanilchoob.ajax.url, payload);
        }

        function hasRecaptcha() {
            return window.tanilchoob && window.tanilchoob.recaptcha && window.tanilchoob.recaptcha.site_key;
        }

        function ensureRecaptchaScript(cb) {
            if (!hasRecaptcha()) { cb(); return; }
            if (window.grecaptcha && window.grecaptcha.execute) { cb(); return; }
            var src = 'https://www.google.com/recaptcha/api.js?render=' + window.tanilchoob.recaptcha.site_key;
            var s = document.createElement('script');
            s.src = src;
            s.async = true;
            s.onload = cb;
            document.head.appendChild(s);
        }

        function fetchTokenAndSubmit() {
            if (!hasRecaptcha()) {
                return postQuestion({});
            }
            return window.grecaptcha.execute(window.tanilchoob.recaptcha.site_key, { action: 'tanilchoob_product_question_submit' })
                .then(function (token) {
                    return postQuestion({ recaptcha_token: token });
                });
        }

        $form.on('submit', function (e) {
            e.preventDefault();
            setMessage('', false);
            disableForm(true);

            ensureRecaptchaScript(function () {
                fetchTokenAndSubmit()
                    .done(function (res) {
                        if (res && res.success) {
                            setMessage(res.data && res.data.message ? res.data.message : 'ارسال شد.', false);
                            $form[0].reset();
                        } else {
                            var msg = (res && res.data && res.data.message) ? res.data.message : 'خطا در ارسال پرسش.';
                            setMessage(msg, true);
                        }
                    })
                    .fail(function () {
                        setMessage('خطا در ارتباط با سرور.', true);
                    })
                    .always(function () {
                        disableForm(false);
                    });
            });
        });
    });
})(jQuery);