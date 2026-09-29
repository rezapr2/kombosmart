'use strict';

(function ($) {
    // Convert Persian/Arabic-Indic digits to ASCII
    function toEnDigits(str) {
        return str
            .replace(/[۰-۹]/g, function (d) { return d.charCodeAt(0) - 0x06F0; })
            .replace(/[٠-٩]/g, function (d) { return d.charCodeAt(0) - 0x0660; });
    }

    function isValidMobile(mobile) {
        return /^09[0-9]{9}$/.test(mobile);
    }

    function showMessage($form, message, isError) {
        var $msg = $form.find('.otp-messages');
        $msg.removeClass('otp-messages--error otp-messages--success');
        $msg.addClass(isError ? 'otp-messages--error' : 'otp-messages--success');
        $msg.text(message).show();
    }

    function clearMessage($form) {
        $form.find('.otp-messages').text('').hide();
    }

    function setLoading($btn, loading) {
        $btn.prop('disabled', loading);
        if (loading) {
            $btn.data('original-text', $btn.text());
            $btn.text('لطفاً صبر کنید...');
        } else {
            $btn.text($btn.data('original-text') || $btn.text());
        }
    }

    // ── Countdown timer ─────────────────────────────────────

    function startCountdown($form) {
        var seconds = 120;
        var $countdown  = $form.find('.otp-countdown');
        var $timer      = $form.find('.otp-timer');
        var $resendBtn  = $form.find('.otp-resend-btn');

        $timer.show();
        $resendBtn.addClass('otp-resend-btn--hidden');

        clearInterval($form.data('otp-timer-id'));

        var intervalId = setInterval(function () {
            seconds--;
            var m = String(Math.floor(seconds / 60)).padStart(2, '0');
            var s = String(seconds % 60).padStart(2, '0');
            $countdown.text(m + ':' + s);

            if (seconds <= 0) {
                clearInterval(intervalId);
                $timer.hide();
                $resendBtn.removeClass('otp-resend-btn--hidden');
            }
        }, 1000);

        $form.data('otp-timer-id', intervalId);
    }

    // ── Show step ────────────────────────────────────────────

    function goToStep($form, step) {
        $form.find('.otp-step').each(function () {
            var $step = $(this);
            if (parseInt($step.data('step'), 10) === step) {
                $step.removeClass('otp-step--hidden');
            } else {
                $step.addClass('otp-step--hidden');
            }
        });
        clearMessage($form);
    }

    // ── Send OTP ─────────────────────────────────────────────

    function sendOtp($form) {
        var rawMobile   = toEnDigits($form.find('.otp-mobile-input').val().trim());
        var $sendBtn    = $form.find('.otp-send-btn');

        if (!isValidMobile(rawMobile)) {
            showMessage($form, 'لطفاً یک شماره موبایل معتبر وارد کنید (مثال: ۰۹۱۲۳۴۵۶۷۸۹)', true);
            return;
        }

        setLoading($sendBtn, true);
        clearMessage($form);

        $.ajax({
            url: tanilchoob.ajax.url,
            type: 'POST',
            data: {
                action: 'tanilchoob_otp_send',
                nonce:  tanilchoob.ajax.nonce,
                mobile: rawMobile,
            },
            success: function (response) {
                setLoading($sendBtn, false);
                if (response.success) {
                    $form.data('otp-mobile', rawMobile);
                    $form.find('.otp-mobile-display').text(rawMobile);
                    goToStep($form, 2);
                    $form.find('.otp-code-input').val('').focus();
                    startCountdown($form);
                } else {
                    showMessage($form, response.data.message || 'خطایی رخ داد.', true);
                }
            },
            error: function () {
                setLoading($sendBtn, false);
                showMessage($form, 'خطا در ارتباط با سرور. لطفاً دوباره امتحان کنید.', true);
            },
        });
    }

    // ── Verify OTP ───────────────────────────────────────────

    function verifyOtp($form) {
        var mobile     = $form.data('otp-mobile');
        var otp        = toEnDigits($form.find('.otp-code-input').val().trim());
        var $verifyBtn = $form.find('.otp-verify-btn');

        if (!otp || otp.length !== 6 || !/^[0-9]{6}$/.test(otp)) {
            showMessage($form, 'لطفاً کد ۶ رقمی را وارد کنید.', true);
            return;
        }

        setLoading($verifyBtn, true);
        clearMessage($form);

        $.ajax({
            url: tanilchoob.ajax.url,
            type: 'POST',
            data: {
                action: 'tanilchoob_otp_verify',
                nonce:  tanilchoob.ajax.nonce,
                mobile: mobile,
                otp:    otp,
            },
            success: function (response) {
                if (response.success) {
                    showMessage($form, 'ورود موفقیت‌آمیز بود. در حال انتقال...', false);
                    window.location.href = response.data.redirect;
                } else {
                    setLoading($verifyBtn, false);
                    showMessage($form, response.data.message || 'خطایی رخ داد.', true);
                }
            },
            error: function () {
                setLoading($verifyBtn, false);
                showMessage($form, 'خطا در ارتباط با سرور. لطفاً دوباره امتحان کنید.', true);
            },
        });
    }

    // ── Bootstrap ────────────────────────────────────────────

    jQuery(document).ready(function ($) {
        // Send OTP on button click
        $(document).on('click', '.otp-send-btn', function () {
            sendOtp($(this).closest('.otp-form'));
        });

        // Allow pressing Enter in mobile input to trigger send
        $(document).on('keydown', '.otp-mobile-input', function (e) {
            if (e.key === 'Enter') {
                e.preventDefault();
                sendOtp($(this).closest('.otp-form'));
            }
        });

        // Resend OTP
        $(document).on('click', '.otp-resend-btn', function () {
            var $form = $(this).closest('.otp-form');
            sendOtp($form);
        });

        // Verify OTP on button click
        $(document).on('click', '.otp-verify-btn', function () {
            verifyOtp($(this).closest('.otp-form'));
        });

        // Allow pressing Enter in OTP input to trigger verify
        $(document).on('keydown', '.otp-code-input', function (e) {
            if (e.key === 'Enter') {
                e.preventDefault();
                verifyOtp($(this).closest('.otp-form'));
            }
        });

        // Back to step 1
        $(document).on('click', '.otp-back-btn', function () {
            var $form = $(this).closest('.otp-form');
            clearInterval($form.data('otp-timer-id'));
            goToStep($form, 1);
        });

        // Only allow numeric input in OTP field (including Persian numerals → converted on verify)
        $(document).on('input', '.otp-code-input', function () {
            var val = toEnDigits($(this).val()).replace(/[^0-9]/g, '');
            $(this).val(val);
        });
    });
})(jQuery);
