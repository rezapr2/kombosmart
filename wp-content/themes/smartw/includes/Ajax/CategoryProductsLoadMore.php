<?php

namespace TanilChoob\Theme\Ajax;

use TanilChoob\Theme\Abstracts\AjaxHandler;

class CategoryProductsLoadMore extends AjaxHandler
{
    public function __construct()
    {
        parent::__construct('category_products_load');
    }

    public function handler()
    {
        $this->handle_nonce();

        $paged = isset($_POST['paged']) ? intval($_POST['paged']) : 1;
        $query_vars_raw = isset($_POST['query_vars']) ? wp_unslash($_POST['query_vars']) : '';
        $orderby_raw = isset($_POST['orderby']) ? sanitize_text_field($_POST['orderby']) : '';

        // Decode the base query vars coming from the initial archive page
        $base_args = array();
        if (!empty($query_vars_raw)) {
            $decoded = json_decode($query_vars_raw, true);
            if (is_array($decoded)) {
                $base_args = $decoded;
            }
        }

        // Ensure we target products and the correct page
        $args = array_merge($base_args, array(
            'post_type'              => 'product',
            'paged'                  => max(1, $paged),
            'no_found_rows'          => true,
            'cache_results'          => false,
            'update_post_meta_cache' => false,
            'update_post_term_cache' => false,
        ));

        // Map our custom orderby options to WooCommerce ordering args
        $orderby = $this->normalize_orderby($orderby_raw);
        $order   = $this->normalize_order($orderby_raw);

        if (function_exists('WC') && WC()->query) {
            $ordering = WC()->query->get_catalog_ordering_args($orderby, $order);
            if (!empty($ordering['orderby'])) { $args['orderby'] = $ordering['orderby']; }
            if (!empty($ordering['order']))   { $args['order']   = $ordering['order']; }
            if (!empty($ordering['meta_key'])){ $args['meta_key']= $ordering['meta_key']; }
        } else {
            // Fallback ordering
            $args['orderby'] = 'date';
            $args['order']   = 'DESC';
        }

        // Execute the query and render product cards
        $q = new \WP_Query($args);

        ob_start();
        if ($q->have_posts()) {
            while ($q->have_posts()) {
                $q->the_post();
                get_template_part('template-parts/cards/product-card', null, ['post_id' => get_the_ID()]);
            }
        }
        wp_reset_postdata();
        $html = ob_get_clean();

        wp_send_json_success([
            'html' => $html,
            'page' => $args['paged'],
            'count' => isset($q->post_count) ? intval($q->post_count) : 0,
        ]);
    }


    private function normalize_orderby($val)
    {
        $val = strtolower((string)$val);
        switch ($val) {
            case 'price':
            case 'price-desc':
                return 'price';
            case 'popularity':
                return 'popularity';
            case 'rating':
                return 'rating';
            case 'date':
                return 'date';
            default:
                return 'menu_order';
        }
    }

    private function normalize_order($val)
    {
        $val = strtolower((string)$val);
        if ($val === 'price-desc') {
            return 'DESC';
        }
        // Default order DESC for date/popularity/rating, ASC for price
        return in_array($val, ['price']) ? 'ASC' : 'DESC';
    }
}

// Initialize handler
new CategoryProductsLoadMore();