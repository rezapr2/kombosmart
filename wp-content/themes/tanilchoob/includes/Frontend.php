<?php


namespace TanilChoob\Theme;


class Frontend
{


	/**
	 * Frontend constructor.
	 */
	public function __construct()
	{
		add_action('wp_enqueue_scripts', [$this, 'enqueue_scripts']);
		add_filter('mod_rewrite_rules', [$this, 'fix_security_headers']);
		// Customize WooCommerce breadcrumb classes
		add_filter('woocommerce_breadcrumb_defaults', [$this, 'wc_breadcrumb_defaults']);
		// Disable WooCommerce core styles
		add_filter('woocommerce_enqueue_styles', '__return_empty_array');
		// Dequeue WooCommerce Blocks styles after they are enqueued
		add_action('wp_enqueue_scripts', [$this, 'dequeue_wc_block_styles'], 100);

		// Woo single product script is heavy and we have custom UI; keep it dequeued.
		add_action('wp_enqueue_scripts', function(){ if (is_product()) { wp_dequeue_script('wc-single-product'); } }, 100);

		// Allow product reviews without requiring email (only affects product comments submission).
		add_filter('pre_option_require_name_email', [$this, 'maybe_disable_name_email_requirement']);

		// Capture product option adjustments and apply to cart item price
		add_filter('woocommerce_add_cart_item_data', [$this, 'capture_product_option_adjustments'], 10, 3);
		add_action('woocommerce_before_calculate_totals', [$this, 'apply_product_option_adjustments'], 10);
		add_filter('woocommerce_get_item_data', [$this, 'render_cart_item_option_data'], 10, 2);
		add_action('woocommerce_checkout_create_order_line_item', [$this, 'add_order_item_meta'], 10, 4);

	}

	/**
	 * Disable WordPress name/email requirement for product reviews only.
	 * This avoids server-side validation errors after removing the email field from the review form.
	 *
	 * @param mixed $pre The short-circuit value for the option.
	 * @return mixed '0' to disable requirement during product review submission, or original $pre otherwise.
	 */
	public function maybe_disable_name_email_requirement($pre)
	{
		if (isset($_POST['comment_post_ID'])) {
			$post_id = absint($_POST['comment_post_ID']);
			if ($post_id && get_post_type($post_id) === 'product') {
				// Return '0' to tell WP that name/email is NOT required for this submission.
				return '0';
			}
		}
		// Fall back to the actual option value for non-product contexts.
		return $pre;
	}

	public function enqueue_scripts()
	{
		if (! is_admin()) {
			global $wp_query;

			$css_relative_path = '/assets/frontend/dist/css/styles.min.css';
			$js_relative_path  = '/assets/frontend/dist/js/scripts.min.js';

			$css_version = filemtime(get_theme_file_path($css_relative_path));
			$js_version  = filemtime(get_theme_file_path($js_relative_path));

			wp_enqueue_style('tanilchoob', get_template_directory_uri() . $css_relative_path, [], $css_version);

			wp_enqueue_script('scripts', get_template_directory_uri() . $js_relative_path, ['jquery'], $js_version);
			wp_localize_script('scripts', 'tanilchoob', [
				'ajax' => [
					'url'          => admin_url('admin-ajax.php'),
					'nonce' => wp_create_nonce('ajax-nonce'),
					'posts'        => json_encode($wp_query->query_vars), // everything about your loop is here
					'current_page' => get_query_var('paged') ? get_query_var('paged') : 1,
					'max_page'     => $wp_query->max_num_pages,
					'loading'      => __('Loading...', 'tanilchoob'),
					'loadMore'     => __('Load more', 'tanilchoob'),
				],
			]);
		}
	}
	
	public function fix_security_headers($rules)
	{
		$new_rules = <<<EOD
<IfModule mod_headers.c>
	Header always set Strict-Transport-Security "max-age=31536000; includeSubDomains"
	Header set X-Frame-Options "SAMEORIGIN"
	Header set Referrer-Policy "strict-origin-when-cross-origin"
    Header set Permissions-Policy "sync-xhr=()"  
	Header set X-Content-Type-Options "nosniff"
	Header set Content-Security-Policy "frame-ancestors 'self'"
</IfModule>

Options -Indexes
EOD;
		return $rules . $new_rules;
	}

	public function wc_breadcrumb_defaults($args)
	{
		// Add custom classes to the breadcrumb wrapper element
		$classes = 'breadcrumb yekan-16 py-20';
		$args['wrap_before'] = '<div class="container"><nav class="' . $classes . '" aria-label="breadcrumb">';
		$args['wrap_after']  = '</nav></div>';
		$args['delimiter']  = '<span class="color-black-30">&nbsp;/&nbsp;</span>';
		
		return $args;
	}

	public function dequeue_wc_block_styles()
	{
		// Known WooCommerce Blocks style handles
		wp_dequeue_style('wc-blocks-style');
		wp_dequeue_style('wc-blocks-style-product-query');
		// WooCommerce inline style handle
		wp_dequeue_style('woocommerce-inline');
	}

	/**
	 * Store selected product option adjustments into cart item data
	 */
	public function capture_product_option_adjustments($cart_item_data, $product_id, $variation_id)
	{
		if (!empty($_POST['product_option_adjustments'])) {
			$raw = wp_unslash($_POST['product_option_adjustments']);
			$decoded = json_decode($raw, true);
			if (is_array($decoded)) {
				$cart_item_data['tc_option_adjustments'] = $decoded;
				$base_product = wc_get_product($variation_id ? $variation_id : $product_id);
				$cart_item_data['tc_base_price'] = $base_product ? (float) $base_product->get_price() : null;
			}
		}
		return $cart_item_data;
	}

	/**
	 * Apply price adjustments to cart items before totals are calculated
	 */
	public function apply_product_option_adjustments($cart)
	{
		if (is_admin() && !defined('DOING_AJAX')) { return; }
		if (empty($cart)) { return; }
		foreach ($cart->get_cart() as $key => $item) {
			if (!empty($item['tc_option_adjustments']) && is_array($item['tc_option_adjustments'])) {
				$sum = 0.0;
				foreach ($item['tc_option_adjustments'] as $opt) {
					$sum += isset($opt['amount']) ? (float) $opt['amount'] : 0.0;
				}
				$base = isset($item['tc_base_price']) && $item['tc_base_price'] !== null ? (float) $item['tc_base_price'] : (float) $item['data']->get_price();
				$new_price = $base + $sum;
				if ($new_price < 0) { $new_price = 0; }
				$item['data']->set_price($new_price);
			}
		}
	}

	/**
	 * Display selected options under cart item name in cart/checkout
	 */
	public function render_cart_item_option_data($item_data, $cart_item)
	{
		if (!empty($cart_item['tc_option_adjustments']) && is_array($cart_item['tc_option_adjustments'])) {
			foreach ($cart_item['tc_option_adjustments'] as $opt) {
				$amount = isset($opt['amount']) ? (float) $opt['amount'] : 0.0;
				$sign = $amount >= 0 ? '+' : '-';
				$item_data[] = [
					'name' => isset($opt['label']) ? $opt['label'] : __('گزینه', 'tanilchoob'),
					'value' => $sign . ' ' . wc_price(abs($amount)),
					'display' => $sign . ' ' . wc_price(abs($amount)),
				];
			}
		}
		return $item_data;
	}

	/**
	 * Persist selected options to order item meta
	 */
	public function add_order_item_meta($item, $cart_item_key, $values, $order)
	{
		if (!empty($values['tc_option_adjustments']) && is_array($values['tc_option_adjustments'])) {
			foreach ($values['tc_option_adjustments'] as $opt) {
				$amount = isset($opt['amount']) ? (float) $opt['amount'] : 0.0;
				$sign = $amount >= 0 ? '+' : '-';
				$item->add_meta_data(
					isset($opt['label']) ? $opt['label'] : __('گزینه', 'tanilchoob'),
					$sign . ' ' . wc_price(abs($amount))
				);
			}
		}
	}
}
