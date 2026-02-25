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
        });

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
