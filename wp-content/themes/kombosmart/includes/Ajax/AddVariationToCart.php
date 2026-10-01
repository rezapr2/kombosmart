<?php

namespace TanilChoob\Theme\Ajax;

use TanilChoob\Theme\Abstracts\AjaxHandler;

class AddVariationToCart extends AjaxHandler
{
    public function __construct()
    {
        parent::__construct('add_variation_to_cart');
    }

    public function handler()
    {
        wc_maybe_define_constant('WOOCOMMERCE_CART', true);

        $variation_id = absint($_POST['variation_id'] ?? 0);
        $quantity     = max(1, wc_stock_amount(wp_unslash($_POST['quantity'] ?? 1)));

        if (!$variation_id) {
            wp_send_json(['error' => true, 'product_url' => '']);
            return;
        }

        $variation_product = wc_get_product($variation_id);
        if (!$variation_product || $variation_product->get_type() !== 'variation') {
            wp_send_json(['error' => true, 'product_url' => '']);
            return;
        }

        $product_id = $variation_product->get_parent_id();

        // Build variation attributes: start with stored variation attrs (non-any),
        // then overlay with user-posted attribute_* values (covers "any" slots).
        $variation = [];
        $parent    = wc_get_product($product_id);

        if ($parent) {
            foreach ($parent->get_attributes() as $attribute) {
                if (!$attribute->get_variation()) {
                    continue;
                }

                $key = 'attribute_' . sanitize_title($attribute->get_name());

                if (isset($_POST[$key])) {
                    if ($attribute->is_taxonomy()) {
                        $variation[$key] = sanitize_title(wp_unslash($_POST[$key]));
                    } else {
                        $variation[$key] = html_entity_decode(
                            wc_clean(wp_unslash($_POST[$key])),
                            ENT_QUOTES,
                            get_bloginfo('charset')
                        );
                    }
                }
            }
        }

        $passed_validation = apply_filters(
            'woocommerce_add_to_cart_validation',
            true,
            $product_id,
            $quantity,
            $variation_id,
            $variation
        );

        if (!$passed_validation || false === WC()->cart->add_to_cart($product_id, $quantity, $variation_id, $variation)) {
            $notices = wc_get_notices('error');
            wc_clear_notices();

            $messages = array_values(array_filter(
                array_map(fn($n) => isset($n['notice']) ? wp_strip_all_tags($n['notice']) : '', $notices)
            ));

            wp_send_json([
                'error'       => true,
                'product_url' => get_permalink($product_id),
                'messages'    => $messages,
            ]);
            return;
        }

        ob_start();
        woocommerce_mini_cart();
        $mini_cart = ob_get_clean();

        wp_send_json([
            'fragments' => apply_filters(
                'woocommerce_add_to_cart_fragments',
                ['div.widget_shopping_cart_content' => '<div class="widget_shopping_cart_content">' . $mini_cart . '</div>']
            ),
            'cart_hash' => WC()->cart->get_cart_hash(),
        ]);
    }
}

// Initialize handler
new AddVariationToCart();
