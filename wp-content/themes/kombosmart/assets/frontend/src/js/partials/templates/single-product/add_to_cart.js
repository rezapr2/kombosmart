'use strict';

(function ($) {
    jQuery(document).ready(function ($) {
        // Sync custom radio inputs with WooCommerce's hidden selects so
        // the built-in variation script can update price and availability.

        function updateRadioUI($radio) {
            var $group = $radio.closest('.variations-radio-group');
            $group.find('.variation-radio-item').removeClass('checked');
            $radio.closest('.variation-radio-item').addClass('checked');
        }

        function syncRadioToSelect($radio) {
            var sanitizedAttr = ($radio.attr('name') || '').replace(/^tanil_attribute_/, '');
            var value = $radio.val();
            var $form = $radio.closest('form.variations_form');
            var $select = $form.find('select.tanil-hidden-select[name="attribute_' + sanitizedAttr + '"]');

            if ($select.length) {
                $select.val(value).trigger('change');
            } 
        }

        function parseVariations($form) {
            var data = $form.data('product_variations');
            if (typeof data === 'string') {
                try {
                    data = JSON.parse(data);
                } catch (e) {
                    data = [];
                }
            }
            return Array.isArray(data) ? data : [];
        }

        function getSelectedAttributes($form) {
            var selected = {};
            $form.find('select.tanil-hidden-select').each(function () {
                var name = $(this).attr('name');
                var val = $(this).val();
                if (name) {
                    selected[name] = (val || '').toString();
                }
            });
            return selected;
        }

        function normalize(val) {
            return (val || '').toString().toLowerCase();
        }

        function isMatch(variationAttrs, selections) {
            for (var key in variationAttrs) {
                if (!variationAttrs.hasOwnProperty(key)) continue;
                var vVal = normalize(variationAttrs[key]);
                // Treat empty variation attribute as wildcard (no constraint)
                if (!vVal) {
                    continue;
                }
                var sVal = normalize(selections[key]);
                // Must have a concrete selection and match the variation's non-empty attribute value
                if (!sVal || sVal !== vVal) {
                    return false;
                }
            }
            return true;
        }

        // Helpers to merge option adjustments into displayed price
        function tcParsePriceFromEl($el) {
            var txt = ($el.text() || '').replace(/[^0-9.\-]/g, '');
            // Remove thousands separators; keep decimal point
            var n = parseFloat(txt);
            return isNaN(n) ? null : n;
        }
        function tcGetCurrencySymbol($priceBox, match) {
            var s = $priceBox.find('.woocommerce-Price-currencySymbol').first().text();
            if (!s && match && match.price_html) {
                var tmp = jQuery('<div>').html(match.price_html);
                s = tmp.find('.woocommerce-Price-currencySymbol').first().text();
            }
            return s || '';
        }
        function tcRenderPrice(amount, symbol, regularAmount) {
            var formatted = tcFormatNumber(amount);
            var symbolHtml = symbol ? '<span class="woocommerce-Price-currencySymbol">' + symbol + '</span>' : '';
            if (regularAmount !== null && typeof regularAmount !== 'undefined' && regularAmount > amount) {
                var regularFormatted = tcFormatNumber(regularAmount);
                return '<span class="price flex flex-col"><span class="woocommerce-Price-amount amount"><bdi>' + formatted + symbolHtml + '</bdi></span><del><span class="woocommerce-Price-amount amount regular yekan-18 color-black-70"><bdi>' + regularFormatted + symbolHtml + '</bdi></span></del> </span>';
            }
            return '<span class="price"><span class="woocommerce-Price-amount amount"><bdi>' + formatted + symbolHtml + '</bdi></span></span>';
        }
        function tcGetOptionsSum() {
            var sum = 0;
            $('.product-options .adj-checkbox:checked').each(function(){
                var amt = parseFloat($(this).attr('data-amount') || '0');
                sum += isNaN(amt) ? 0 : amt;
            });
            return sum;
        }

        function updatePriceFromSelection($form) {
            var variations = parseVariations($form);
            var selections = getSelectedAttributes($form);
            var match = null;
            for (var i = 0; i < variations.length; i++) {
                var v = variations[i];
                if (v && v.attributes && isMatch(v.attributes, selections)) {
                    match = v;
                    break;
                }
            }

            var $priceBox = $form.find('.tanil-variation-price');
            if ($priceBox.length) {
                var symbol = tcGetCurrencySymbol($priceBox, match);
                var basePrice = null;
                var regularPrice = null;
                if (match && typeof match.display_price !== 'undefined') {
                    basePrice = parseFloat(match.display_price);
                }
                if (match && typeof match.display_regular_price !== 'undefined') {
                    regularPrice = parseFloat(match.display_regular_price);
                }
                if (basePrice === null) {
                    basePrice = tcParsePriceFromEl($priceBox);
                }
                var finalPrice = null;
                var optSum = tcGetOptionsSum();
                if (basePrice !== null) {
                    finalPrice = basePrice + optSum;
                    if (regularPrice !== null) {
                        regularPrice = regularPrice + optSum;
                    }
                    $priceBox.html(tcRenderPrice(finalPrice, symbol, regularPrice));
                } else if (match && match.price_html) {
                    $priceBox.html(match.price_html);
                } else {
                    var defaultHtml = $priceBox.data('defaultHtml');
                    if (typeof defaultHtml !== 'undefined') {
                        $priceBox.html(defaultHtml);
                    }
                }
            }
        }

        // Handle radio changes: update UI and sync to hidden select
        $(document).on('change', '.tanil-variation-radio', function () {
            var $radio = $(this);
            updateRadioUI($radio);
            syncRadioToSelect($radio);
            var $form = $radio.closest('form.variations_form');
            // Ask Woo to re-check variations and also update our price box immediately.
            $form.trigger('check_variations');
            updatePriceFromSelection($form);
        });

        // Initialize: ensure pre-checked radios are synced (covers default selections)
        $('form.variations_form').each(function () {
            var $form = $(this);
            // Cache default price HTML for resets
            var $priceBox = $form.find('.tanil-variation-price');
            if ($priceBox.length) {
                $priceBox.data('defaultHtml', $priceBox.html());
            }
            $form.find('.tanil-variation-radio:checked').each(function () {
                syncRadioToSelect($(this));
            });
            // Initialize price based on any defaults
            $form.trigger('check_variations');
            updatePriceFromSelection($form);
        });


        // Optional safety: hide built-in variation block and restore custom price when data is reset
        $('form.variations_form').on('reset_data', function () {
            var $form = $(this);
            $form.find('.single_variation_wrap .single_variation').hide();
            var $priceBox = $form.find('.tanil-variation-price');
            if ($priceBox.length) {
                var defaultHtml = $priceBox.data('defaultHtml');
                if (typeof defaultHtml !== 'undefined') {
                    $priceBox.html(defaultHtml);
                }
            }
        });

        // Ensure price section becomes visible when a variation is found and update custom price box
        $('form.variations_form').on('found_variation', function (event, variation) {
            var $form = $(this);
            $form.find('.single_variation_wrap .single_variation').show();
            var $priceBox = $form.find('.tanil-variation-price');
            if ($priceBox.length && variation && variation.price_html) {
                $priceBox.html(variation.price_html);
            }
            // Store the resolved variation so the AJAX handler can read it
            if (variation && variation.variation_id) {
                $form.data('tc_resolved_variation', variation);
                $form.find('input.variation_id').val(variation.variation_id);
            }
        });

        // ----- AJAX Add to Cart + Modal -----

        function injectCartModal() {
            if ($('#tc-cart-modal').length) return;
            $('body').append(
                '<div id="tc-cart-modal" class="tc-cart-modal" role="dialog" aria-modal="true">' +
                    '<div class="tc-cart-modal__overlay"></div>' +
                    '<div class="tc-cart-modal__box">' +
                        '<div class="tc-cart-modal__header">' +
                            '<div class="tc-cart-modal__success">' +
                                '<svg width="20" height="20" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg"><circle cx="10" cy="10" r="9" stroke="#1F4A8A" stroke-width="1.5"/><path d="M6 10l3 3 5-5" stroke="#1F4A8A" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/></svg>' +
                                '<span class="tc-cart-modal__success-text yekan-20 color-primary bold">کالا به سبد خرید اضافه شد</span>' +
                            '</div>' +
                            '<div class="tc-cart-modal__error-header">' +
                                '<svg width="20" height="20" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg"><circle cx="10" cy="10" r="9" stroke="#c0392b" stroke-width="1.5"/><path d="M10 6v5" stroke="#c0392b" stroke-width="1.5" stroke-linecap="round"/><circle cx="10" cy="14" r="0.75" fill="#c0392b"/></svg>' +
                                '<span class="yekan-20 bold">خطا در افزودن به سبد خرید</span>' +
                            '</div>' +
                            '<button class="tc-cart-modal__close" aria-label="بستن">' +
                                '<svg width="20" height="20" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M15 5L5 15M5 5l10 10" stroke="#2f2f2f" stroke-width="1.5" stroke-linecap="round"/></svg>' +
                            '</button>' +
                        '</div>' +
                        '<div class="tc-cart-modal__product">' +
                            '<img class="tc-cart-modal__product-img" src="" alt="" />' +
                            '<div class="tc-cart-modal__product-info">' +
                                '<p class="tc-cart-modal__product-name yekan-18"></p>' +
                                '<p class="tc-cart-modal__product-price yekan-20 color-primary bold"></p>' +
                            '</div>' +
                        '</div>' +
                        '<p class="tc-cart-modal__error-body yekan-16"></p>' +
                        '<a class="tc-cart-modal__view-cart yekan-18" href="#">مشاهده سبد خرید</a>' +
                    '</div>' +
                '</div>'
            );
        }

        function openCartModal(productName, productPrice, productImg) {
            var $modal = $('#tc-cart-modal');
            $modal.find('.tc-cart-modal__product-name').text(productName);
            $modal.find('.tc-cart-modal__product-price').text(productPrice);
            $modal.find('.tc-cart-modal__product-img').attr('src', productImg).attr('alt', productName);
            if (typeof wc_add_to_cart_params !== 'undefined' && wc_add_to_cart_params.cart_url) {
                $modal.find('.tc-cart-modal__view-cart').attr('href', wc_add_to_cart_params.cart_url);
            }
            $modal.removeClass('is-error').addClass('is-open');
            $('body').addClass('tc-modal-open');
        }

        function openErrorModal(message) {
            var $modal = $('#tc-cart-modal');
            $modal.find('.tc-cart-modal__error-body').text(message);
            $modal.addClass('is-open is-error');
            $('body').addClass('tc-modal-open');
        }

        function closeCartModal() {
            $('#tc-cart-modal').removeClass('is-open is-error');
            $('body').removeClass('tc-modal-open');
        }

        injectCartModal();

        $(document).on('click', '.tc-cart-modal__close, .tc-cart-modal__overlay', function () {
            closeCartModal();
        });

        $(document).on('keydown', function (e) {
            if (e.key === 'Escape') closeCartModal();
        });

        // Shared success/error handling for variation and simple add-to-cart requests.
        function handleCartResponse($btn, response) {
            $btn.removeClass('tc-loading');

            if (response && response.error) {
                var msg = (response.messages && response.messages.length)
                    ? response.messages.join('\n')
                    : 'خطایی رخ داد. لطفاً دوباره تلاش کنید.';
                openErrorModal(msg);
                return;
            }

            if (response && response.fragments) {
                $.each(response.fragments, function (key, value) {
                    $(key).replaceWith(value);
                });
                $(document.body).trigger('wc_fragments_refreshed');
            }

            var productName = $('.product-title').first().text().trim();
            var productPrice = $('.tanil-variation-price .woocommerce-Price-amount').last().text().trim();
            var productImg = $('.gallery-main .swiper-slide').first().find('img').attr('src') || '';

            openCartModal(productName, productPrice, productImg);
        }

        function handleCartError($btn) {
            $btn.removeClass('tc-loading');
            openErrorModal('خطا در ارتباط با سرور. لطفاً دوباره تلاش کنید.');
        }

        $(document).on('submit', 'form.cart', function (e) {
            var $form = $(this);
            var $btn = $form.find('.single_add_to_cart_button');

            if (!$btn.length || $btn.hasClass('disabled')) return;

            // Simple products: post to WooCommerce's own AJAX endpoint and show the same modal.
            // Without wc_add_to_cart_params (AJAX add-to-cart disabled) the form submits normally.
            if (!$form.hasClass('variations_form')) {
                if (typeof wc_add_to_cart_params === 'undefined') return;

                e.preventDefault();
                if ($btn.hasClass('tc-loading')) return;
                $btn.addClass('tc-loading');
                tcUpdateOptionsUI();

                $.ajax({
                    type: 'POST',
                    url: wc_add_to_cart_params.wc_ajax_url.toString().replace('%%endpoint%%', 'add_to_cart'),
                    data: {
                        product_id: absInt($btn.val()),
                        quantity: absInt($form.find('input[name="quantity"]').val()) || 1,
                        product_option_adjustments: $form.find('input[name="product_option_adjustments"]').val() || '[]'
                    },
                    success: function (response) {
                        if (response && response.error) {
                            // WooCommerce returns only product_url on failure; notices are shown there.
                            window.location = response.product_url || window.location.href;
                            return;
                        }
                        handleCartResponse($btn, response);
                    },
                    error: function () { handleCartError($btn); }
                });
                return;
            }

            e.preventDefault();

            if ($btn.hasClass('tc-loading')) return;

            // Resolve variation from stored found_variation data or fall back to variations scan
            var resolvedVariation = $form.data('tc_resolved_variation') || null;
            if (!resolvedVariation) {
                var variations = parseVariations($form);
                var selections = getSelectedAttributes($form);
                for (var i = 0; i < variations.length; i++) {
                    if (variations[i] && variations[i].attributes && isMatch(variations[i].attributes, selections)) {
                        resolvedVariation = variations[i];
                        break;
                    }
                }
            }

            var variationId = resolvedVariation ? resolvedVariation.variation_id : 0;
            if (!variationId) return; // no variation selected yet

            $btn.addClass('tc-loading');

            var quantity = absInt($form.find('input[name="quantity"]').val()) || 1;

            tcUpdateOptionsUI();

            var parentProductId = absInt($form.data('product_id'));

            var postData = {
                action:       'tanilchoob_add_variation_to_cart',
                variation_id: variationId,
                quantity:     quantity,
                product_option_adjustments: $form.find('input[name="product_option_adjustments"]').val() || '[]'
            };

            // Append every attribute_* value from the hidden selects
            $form.find('select.tanil-hidden-select').each(function () {
                var name = $(this).attr('name');
                if (name) {
                    postData[name] = $(this).val() || '';
                }
            });

            var ajaxUrl = (typeof kombosmart !== 'undefined' && kombosmart.ajax && kombosmart.ajax.url)
                ? kombosmart.ajax.url
                : '/wp-admin/admin-ajax.php';

            $.ajax({
                type: 'POST',
                url: ajaxUrl,
                data: postData,
                success: function (response) { handleCartResponse($btn, response); },
                error: function () { handleCartError($btn); }
            });
        });

        function absInt(val) {
            var n = parseInt(val, 10);
            return isNaN(n) ? 0 : Math.abs(n);
        }

        // ----- Product Options (price adjustments) -----
        // Moved from template to JS: handles checkboxes that add/remove cost
        function tcFormatNumber(n) {
            try { return new Intl.NumberFormat('fa-IR').format(n); } catch (e) { return String(n).replace(/\B(?=(\d{3})+(?!\d))/g, ','); }
        }

        function tcCollectSelected() {
            var selected = []; var sum = 0;
            $('.product-options .adj-checkbox').each(function () {
                var $b = $(this);
                if ($b.prop('checked')) {
                    var amt = parseFloat($b.attr('data-amount') || '0');
                    selected.push({ id: $b.attr('data-id'), label: $b.attr('data-label'), amount: amt });
                    sum += amt;
                }
            });
            return { selected: selected, sum: sum };
        }

        function tcEnsureHiddenInputs() {
            $('form.cart').each(function () {
                var $f = $(this);
                if (!$f.find('input[name="product_option_adjustments"]').length) {
                    $('<input>', { type: 'hidden', name: 'product_option_adjustments' }).appendTo($f);
                }
            });
        }

        function tcUpdateOptionsUI() {
            var data = tcCollectSelected();
            $('form.cart input[name="product_option_adjustments"]').val(JSON.stringify(data.selected));
            // Also refresh the displayed product price to include option sum
            $('form.variations_form').each(function(){
                updatePriceFromSelection($(this));
            });
        }

        // Bind events
        $(document).on('change', '.product-options .adj-checkbox', function () {
            tcUpdateOptionsUI();
        });

        // Initialize on ready
        tcEnsureHiddenInputs();
        tcUpdateOptionsUI();
        $('form.cart').on('submit', function () { tcUpdateOptionsUI(); });
    });
})(jQuery);
