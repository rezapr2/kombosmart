'use strict';

(function ($) {
    jQuery(document).ready(function ($) {
    // Guard: only run on the checkout page
    if (!$('.tc-checkout').length) return;
    var cfg       = window.tcCheckout || {};
    var AJAX_URL  = cfg.ajaxUrl || (window.tanilchoob && tanilchoob.ajax.url) || '';
    var NONCE     = cfg.nonce  || (window.tanilchoob && tanilchoob.ajax.nonce) || '';

    var currentStep = 1;
    var totalSteps  = 4;
    var isLoading   = false;

    // ── Utility ──────────────────────────────────────────────

    function setLoading(loading) {
        isLoading = loading;
        var $btn = $('#tc-checkout-next');
        $btn.prop('disabled', loading);
        if (loading) {
            $btn.data('orig', $btn.text()).text('لطفاً صبر کنید...');
        } else {
            $btn.text($btn.data('orig') || 'ادامه ثبت سفارش');
        }
    }

    function showMsg($el, text, isError) {
        $el.text(text)
           .removeClass('is-error is-success')
           .addClass(isError ? 'is-error' : 'is-success');
    }

    function ajax(action, data, success, error) {
        $.post(AJAX_URL, $.extend({ action: action, nonce: NONCE }, data), function (res) {
            if (res.success) {
                success(res.data);
            } else {
                var msg = (res.data && res.data.message) ? res.data.message : 'خطایی رخ داد.';
                if (typeof error === 'function') error(msg);
                else alert(msg);
            }
        }).fail(function () {
            var msg = 'خطا در ارتباط با سرور. لطفاً دوباره امتحان کنید.';
            if (typeof error === 'function') error(msg);
            else alert(msg);
        });
    }

    // ── Step navigation ──────────────────────────────────────

    function goToStep(n) {
        currentStep = n;

        // Update panels
        $('[data-step-panel]').removeClass('is-active');
        $('[data-step-panel="' + n + '"]').addClass('is-active');

        // Update step indicators
        $('[data-step]').each(function () {
            var s = parseInt($(this).data('step'), 10);
            $(this).removeClass('active done');
            if (s === n)  $(this).addClass('active');
            if (s < n)    $(this).addClass('done');
        });

        // Show/hide footer bar
        if (n === 4) {
            $('#tc-checkout-footer').addClass('is-hidden');
        } else {
            $('#tc-checkout-footer').removeClass('is-hidden');
        }

        // Update next button label on step 3
        if (n === 3) {
            $('#tc-checkout-next').text('ثبت سفارش');
        } else if (n < 4) {
            $('#tc-checkout-next').text('ادامه ثبت سفارش');
        }

        // Show/hide COD warning
        if (n === 3) updateCodWarning();

        window.scrollTo({ top: 0, behavior: 'smooth' });
    }

    // Validate current step before advancing
    function validateStep(n) {
        if (n === 1) {
            // Cart must not be empty (server enforces; JS just checks rows)
            if (!$('.tc-cart-table__row').length) {
                alert('سبد خرید شما خالی است.');
                return false;
            }
            return true;
        }

        if (n === 2) {
            if (!$('.tc-address-radio:checked').length) {
                alert('لطفاً یک آدرس تحویل انتخاب کنید.');
                return false;
            }
            return true;
        }

        if (n === 3) {
            if (!$('.tc-payment-radio:checked').length) {
                alert('لطفاً شیوه پرداخت را انتخاب کنید.');
                return false;
            }
            return true;
        }

        return true;
    }

    // ── Cart operations ──────────────────────────────────────

    function updateCartTotal(total) {
        $('#tc-cart-total').text(total);
    }

    $(document).on('click', '.tc-qty-plus', function () {
        console.log('reasrasr');
        if (isLoading) return;
        var key = $(this).data('key');
        var $val = $('.tc-qty__val[data-key="' + key + '"]');
        var qty = parseInt($val.text(), 10) + 1;
        $val.text(qty);
        updateQty(key, qty);
    });

    $(document).on('click', '.tc-qty-minus', function () {
        if (isLoading) return;
        var key = $(this).data('key');
        var $val = $('.tc-qty__val[data-key="' + key + '"]');
        var qty = Math.max(1, parseInt($val.text(), 10) - 1);
        $val.text(qty);
        updateQty(key, qty);
    });

    function updateQty(key, qty) {
        setLoading(true);
        ajax('tc_cart_update_qty', { cart_item_key: key, qty: qty }, function (data) {
            setLoading(false);
            $('.tc-cart-table__price-val[data-key="' + key + '"]').text(data.line_total);
            updateCartTotal(data.cart_total);
            if (data.is_empty) {
                window.location.reload();
            }
        }, function () { setLoading(false); });
    }

    $(document).on('click', '.tc-cart-remove', function () {
        if (isLoading) return;
        var key = $(this).data('key');
        setLoading(true);
        ajax('tc_cart_remove_item', { cart_item_key: key }, function (data) {
            setLoading(false);
            $('[data-cart-key="' + key + '"]').fadeOut(200, function () { $(this).remove(); });
            updateCartTotal(data.cart_total);
            if (data.is_empty) {
                setTimeout(function () { window.location.reload(); }, 300);
            }
        }, function () { setLoading(false); });
    });

    // ── Address operations ────────────────────────────────────

    function renderAddressCard(addr, checked) {
        var checkedAttr = checked ? 'checked' : '';
        return '<div class="tc-address-card" data-id="' + addr.id + '">' +
            '<label class="tc-address-card__inner">' +
                '<input type="radio" name="tc_selected_address" value="' + addr.id + '" class="tc-address-radio" ' + checkedAttr + '>' +
                '<span class="tc-address-card__check-icon">' +
                    '<svg width="18" height="18" viewBox="0 0 24 24" fill="none"><path d="M4 12l6 6L20 6" stroke="white" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"/></svg>' +
                '</span>' +
                '<div class="tc-address-card__body">' +
                    '<div class="tc-address-card__name"><strong>نام و نام خوادگی تحویل گیرنده :</strong><span>' + esc(addr.first_name + ' ' + addr.last_name) + '</span></div>' +
                    '<div class="tc-address-card__addr"><strong>آدرس :</strong><span>' + esc((addr.city ? addr.city + '، ' : '') + addr.address_1) + '</span></div>' +
                    '<div class="tc-address-card__meta">' +
                        '<span><strong>شماره تماس :</strong> ' + esc(addr.phone) + '</span>' +
                        (addr.postcode ? '<span><strong>کد پستی :</strong> ' + esc(addr.postcode) + '</span>' : '') +
                    '</div>' +
                '</div>' +
            '</label>' +
            '<div class="tc-address-card__actions">' +
                '<button class="tc-btn tc-btn--sm tc-btn--danger-outline tc-address-delete" data-id="' + addr.id + '">' +
                    '<svg width="14" height="14" viewBox="0 0 24 24" fill="none"><path d="M21 5.98c-3.33-.33-6.68-.5-10.02-.5-1.98 0-3.96.1-5.94.3L3 5.98M8.5 4.97l.22-1.31C8.88 2.71 9 2 10.69 2h2.62c1.69 0 1.82.75 1.97 1.67l.22 1.3M18.85 9.14l-.65 10.07C18.09 20.78 18 22 15.21 22H8.79C6 22 5.91 20.78 5.8 19.21L5.15 9.14" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/></svg>' +
                    'حذف آدرس' +
                '</button>' +
                '<button class="tc-btn tc-btn--sm tc-btn--outline tc-address-edit" data-id="' + addr.id + '" data-address=\'' + JSON.stringify(addr) + '\'>' +
                    '<svg width="14" height="14" viewBox="0 0 24 24" fill="none"><path d="M13.26 3.6l-8.21 8.69c-.31.33-.61.98-.67 1.43l-.37 3.24c-.13 1.13.71 1.93 1.83 1.75l3.22-.55c.45-.08 1.08-.41 1.39-.75l8.21-8.69c1.42-1.5 2.06-3.21.63-4.74-1.44-1.54-3.12-.94-4.03.62z" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/></svg>' +
                    'ویرایش آدرس' +
                '</button>' +
            '</div>' +
        '</div>';
    }

    function esc(str) {
        return $('<div>').text(String(str || '')).html();
    }

    function openAddressForm(addr) {
        var editing = addr && addr.id;
        $('#tc-address-form-title').text(editing ? 'ویرایش آدرس' : 'افزودن آدرس جدید');
        $('#tc-address-id').val(editing ? addr.id : '');
        $('#tc-addr-first-name').val(editing ? addr.first_name : '');
        $('#tc-addr-last-name').val(editing ? addr.last_name : '');
        $('#tc-addr-phone').val(editing ? addr.phone : '');
        $('#tc-addr-postcode').val(editing ? addr.postcode : '');
        $('#tc-addr-city').val(editing ? addr.city : '');
        $('#tc-addr-address1').val(editing ? addr.address_1 : '');
        $('#tc-address-msg').text('').removeClass('is-error is-success');
        $('#tc-address-form').addClass('is-open');
        $('html, body').animate({ scrollTop: $('#tc-address-form').offset().top - 80 }, 300);
    }

    function closeAddressForm() {
        $('#tc-address-form').removeClass('is-open');
    }

    $(document).on('click', '#tc-add-address-btn', function () {
        openAddressForm(null);
    });

    $(document).on('click', '#tc-cancel-address-btn', function () {
        closeAddressForm();
    });

    $(document).on('click', '.tc-address-edit', function () {
        var addr = $(this).data('address');
        if (typeof addr === 'string') addr = JSON.parse(addr);
        openAddressForm(addr);
    });

    $(document).on('click', '#tc-save-address-btn', function () {
        var $msg = $('#tc-address-msg');

        var first_name = $.trim($('#tc-addr-first-name').val());
        var last_name  = $.trim($('#tc-addr-last-name').val());
        var phone      = $.trim($('#tc-addr-phone').val());
        var city       = $.trim($('#tc-addr-city').val());
        var address_1  = $.trim($('#tc-addr-address1').val());
        var postcode   = $.trim($('#tc-addr-postcode').val());
        var address_id = $('#tc-address-id').val();

        if (!first_name || !last_name || !phone || !address_1) {
            showMsg($msg, 'لطفاً فیلدهای الزامی (نام، نام خانوادگی، شماره تماس، آدرس) را پر کنید.', true);
            return;
        }

        $(this).prop('disabled', true).text('در حال ذخیره...');

        ajax('tc_address_save', {
            address_id: address_id,
            first_name: first_name,
            last_name:  last_name,
            phone:      phone,
            postcode:   postcode,
            city:       city,
            address_1:  address_1,
        }, function (data) {
            $('#tc-save-address-btn').prop('disabled', false).text('ذخیره آدرس');
            closeAddressForm();

            var addr    = data.address;
            var isEdit  = !!address_id;
            var $list   = $('#tc-addresses-list');

            if (isEdit) {
                var $existing = $list.find('[data-id="' + addr.id + '"]');
                var wasChecked = $existing.find('.tc-address-radio').is(':checked');
                $existing.replaceWith($(renderAddressCard(addr, wasChecked)));
            } else {
                $list.find('.tc-addresses__empty').remove();
                $list.append($(renderAddressCard(addr, !$list.find('.tc-address-radio:checked').length)));
            }
        }, function (msg) {
            $('#tc-save-address-btn').prop('disabled', false).text('ذخیره آدرس');
            showMsg($msg, msg, true);
        });
    });

    $(document).on('click', '.tc-address-delete', function () {
        if (!confirm('آیا از حذف این آدرس مطمئن هستید؟')) return;
        var id   = $(this).data('id');
        var $card = $(this).closest('.tc-address-card');

        ajax('tc_address_delete', { address_id: id }, function () {
            $card.fadeOut(200, function () {
                $(this).remove();
                if (!$('#tc-addresses-list .tc-address-card').length) {
                    $('#tc-addresses-list').html('<p class="tc-addresses__empty">هنوز آدرسی ذخیره نکرده‌اید.</p>');
                } else {
                    // Auto-select first remaining address
                    $('#tc-addresses-list .tc-address-radio').first().prop('checked', true);
                }
            });
        });
    });

    // ── Payment ───────────────────────────────────────────────

    function updateCodWarning() {
        var method = $('input[name="tc_payment_method"]:checked').val();
        if (method === 'cod') {
            $('#tc-cod-warning').removeClass('is-hidden');
        } else {
            $('#tc-cod-warning').addClass('is-hidden');
        }
    }

    $(document).on('change', '.tc-payment-radio', updateCodWarning);

    // ── Place order ──────────────────────────────────────────

    function placeOrder() {
        var addressId     = $('input[name="tc_selected_address"]:checked').val();
        var paymentMethod = $('input[name="tc_payment_method"]:checked').val();
        var notes         = $('#tc-order-notes').val();

        setLoading(true);
        ajax('tc_place_order', {
            address_id:     addressId,
            payment_method: paymentMethod,
            notes:          notes,
        }, function (data) {
            setLoading(false);
            renderInvoice(data);
            goToStep(4);
        }, function (msg) {
            setLoading(false);
            alert(msg);
        });
    }

    // ── Invoice rendering ─────────────────────────────────────

    function renderInvoice(data) {
        $('#tc-order-number').text(data.order_number);

        var addr  = data.address || {};
        var items = data.items   || [];

        var rows = '';
        $.each(items, function (i, item) {
            rows += '<tr>' +
                '<td>' + esc(item.name) + '</td>' +
                '<td style="text-align:center">' + item.qty + '</td>' +
                '<td>' + item.price + ' تومان</td>' +
                '<td>' + item.total + ' تومان</td>' +
            '</tr>';
        });

        var logoUrl  = (window.tcCheckout && tcCheckout.logoUrl) ? tcCheckout.logoUrl : '';
        var logoHtml = logoUrl
            ? '<img src="' + logoUrl + '" alt="تانیل چوب">'
            : '<strong style="font-size:2rem;color:var(--color-primary)">تانیل چوب</strong>';

        var html =
            '<div class="tc-invoice-header">' +
                '<div class="tc-invoice-header__title">پیش فاکتور فروش</div>' +
                logoHtml +
            '</div>' +
            '<div class="tc-invoice-info">' +
                '<div class="tc-invoice-info__group">' +
                    '<div class="tc-invoice-info__row"><strong>نام خریدار :</strong><span>' + esc(addr.first_name + ' ' + addr.last_name) + '</span></div>' +
                    '<div class="tc-invoice-info__row"><strong>شماره تماس :</strong><span>' + esc(addr.phone) + '</span></div>' +
                    (addr.postcode ? '<div class="tc-invoice-info__row"><strong>کد پستی :</strong><span>' + esc(addr.postcode) + '</span></div>' : '') +
                    '<div class="tc-invoice-info__row"><strong>آدرس :</strong><span>' + esc((addr.city ? addr.city + '، ' : '') + addr.address_1) + '</span></div>' +
                '</div>' +
                '<div class="tc-invoice-info__group">' +
                    '<div class="tc-invoice-info__row"><strong>شماره سفارش :</strong><span>' + esc(data.order_number) + '</span></div>' +
                    '<div class="tc-invoice-info__row"><strong>تاریخ :</strong><span>' + esc(data.order_date) + '</span></div>' +
                    '<div class="tc-invoice-info__row"><strong>روش پرداخت :</strong><span>' + esc(data.payment_title) + '</span></div>' +
                    (data.notes ? '<div class="tc-invoice-info__row"><strong>توضیحات :</strong><span>' + esc(data.notes) + '</span></div>' : '') +
                '</div>' +
            '</div>' +
            '<table class="tc-invoice-items">' +
                '<thead><tr><th>محصول</th><th style="text-align:center">مقدار</th><th>قیمت واحد</th><th>قیمت کل</th></tr></thead>' +
                '<tbody>' + rows + '</tbody>' +
            '</table>' +
            '<div class="tc-invoice-total">' +
                '<span>قیمت کل :</span>' +
                '<strong>' + esc(data.total) + '</strong>' +
                '<span>تومان</span>' +
            '</div>';

        $('#tc-invoice').html(html);
    }

    // ── Coupon toggle ─────────────────────────────────────────

    $(document).on('click', '#tc-coupon-toggle', function () {
        $('#tc-coupon-form').toggleClass('is-open');
        if ($('#tc-coupon-form').hasClass('is-open')) {
            $('#tc-coupon-code').focus();
        }
    });

    // ── Coupon ────────────────────────────────────────────────

    function renderAppliedCoupons(coupons) {
        var $list = $('#tc-applied-coupons');
        $list.empty();
        $.each(coupons, function (i, code) {
            $list.append(
                '<div class="tc-coupon-tag" data-coupon="' + esc(code) + '">' +
                    '<span>' + esc(code) + '</span>' +
                    '<button class="tc-coupon-remove" data-coupon="' + esc(code) + '" aria-label="حذف">×</button>' +
                '</div>'
            );
        });
    }

    $(document).on('click', '#tc-apply-coupon-btn', function () {
        var code = $.trim($('#tc-coupon-code').val());
        var $msg = $('#tc-coupon-msg');
        if (!code) {
            showMsg($msg, 'کد تخفیف را وارد کنید.', true);
            return;
        }
        $(this).prop('disabled', true).text('در حال بررسی...');
        ajax('tc_apply_coupon', { coupon_code: code }, function (data) {
            $('#tc-apply-coupon-btn').prop('disabled', false).text('اعمال کد تخفیف');
            $('#tc-coupon-code').val('');
            showMsg($msg, 'کد تخفیف با موفقیت اعمال شد.', false);
            updateCartTotal(data.cart_total);
            renderAppliedCoupons(data.applied_coupons);
        }, function (msg) {
            $('#tc-apply-coupon-btn').prop('disabled', false).text('اعمال کد تخفیف');
            showMsg($msg, msg, true);
        });
    });

    $(document).on('keydown', '#tc-coupon-code', function (e) {
        if (e.key === 'Enter') $('#tc-apply-coupon-btn').trigger('click');
    });

    $(document).on('click', '.tc-coupon-remove', function () {
        var code = $(this).data('coupon');
        ajax('tc_remove_coupon', { coupon_code: code }, function (data) {
            updateCartTotal(data.cart_total);
            renderAppliedCoupons(data.applied_coupons);
            $('#tc-coupon-msg').text('').removeClass('is-error is-success');
        });
    });

    // ── Step indicator click (navigate back to done steps) ────

    $(document).on('click', '[data-step]', function () {
        var s = parseInt($(this).data('step'), 10);
        if (s < currentStep) {
            goToStep(s);
        }
    });

    // ── Next button ───────────────────────────────────────────

    $(document).on('click', '#tc-checkout-next', function () {
        if (isLoading) return;
        if (!validateStep(currentStep)) return;

        if (currentStep === 3) {
            placeOrder();
        } else {
            goToStep(currentStep + 1);
        }
    });

    // ── Init ─────────────────────────────────────────────────

    $(document).ready(function () {
        goToStep(1);
    });
    });
})(jQuery);

