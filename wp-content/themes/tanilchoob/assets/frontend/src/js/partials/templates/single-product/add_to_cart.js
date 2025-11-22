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
                var sVal = normalize(selections[key]);
                // Must have a concrete selection and match the variation's attribute value
                if (!sVal || sVal !== vVal) {
                    return false;
                }
            }
            return true;
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
                if (match && match.price_html) {
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
    });
})(jQuery);
