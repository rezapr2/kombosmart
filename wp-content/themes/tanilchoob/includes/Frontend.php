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
		add_filter('woocommerce_product_single_add_to_cart_text', [$this, 'contact_mode_add_to_cart_text']);
		add_filter('woocommerce_product_add_to_cart_text', [$this, 'contact_mode_add_to_cart_text']);
		add_filter('term_description', [$this, 'force_internal_links'], 99);

		// WooCommerce filter to prevent all of its default styles from loading
		add_filter( 'woocommerce_enqueue_styles', '__return_empty_array' );

		add_filter( 'woocommerce_add_to_cart_fragments', [ $this, 'minicart_fragment' ] );
		add_action( 'wp_ajax_tc_remove_cart_item',        [ $this, 'ajax_remove_cart_item' ] );
		add_action( 'wp_ajax_nopriv_tc_remove_cart_item', [ $this, 'ajax_remove_cart_item' ] );

		add_filter( 'woocommerce_currency_symbol',      [ $this, 'change_currency_symbol' ], 9999, 2 );
		add_filter( 'raw_woocommerce_price',            [ $this, 'divide_price_by_10' ], 9999 );
		add_filter( 'woocommerce_available_variation',  [ $this, 'divide_variation_json_by_10' ], 9999, 3 );
	//	add_filter( 'woocommerce_add_cart_item_data',   [ $this, 'correct_cart_options_to_rials' ], 20, 3 );
	}

	public function ajax_remove_cart_item() {
		check_ajax_referer( 'ajax-nonce', 'nonce' );

		$cart_key = sanitize_text_field( $_POST['cart_key'] ?? '' );
		if ( ! $cart_key || ! WC()->cart->remove_cart_item( $cart_key ) ) {
			wp_send_json_error( 'خطا در حذف آیتم' );
		}

		WC()->cart->calculate_totals();

		$count       = WC()->cart->get_cart_contents_count();
		$badge_inner = $count > 0
			? '<span class="minicart-badge absolute flex item-center bg-primary">' . esc_html( $count ) . '</span>'
			: '';

		$fragments = [];
		$fragments['#tc-minicart-dropdown']  = self::render_minicart();
		$fragments['#tc-minicart-badge-wrap'] = '<span id="tc-minicart-badge-wrap">' . $badge_inner . '</span>';

		wp_send_json_success( [
			'fragments'   => $fragments,
			'cart_count'  => $count,
			'cart_total'  => WC()->cart->get_cart_total(),
		] );
	}

	public static function render_minicart(): string {
		$cart     = WC()->cart;
		$items    = $cart ? $cart->get_cart() : [];
		$total    = $cart ? $cart->get_cart_total() : '';
		$checkout = wc_get_checkout_url();
		$nonce    = wp_create_nonce( 'ajax-nonce' );
		$ajax_url = admin_url( 'admin-ajax.php' );

		ob_start();
		?>
		<div class="minicart__dropdown" id="tc-minicart-dropdown">
			<?php if ( empty( $items ) ) : ?>
				<p class="minicart__empty text-center yekan-14 m-0">سبد خرید شما خالی است.</p>
			<?php else : ?>
				<ul class="minicart__list m-0 p-0">
					<?php foreach ( $items as $cart_key => $item ) :
						$product   = $item['data'];
						$image_id  = $product->get_image_id();
						$image_url = $image_id ? wp_get_attachment_image_url( $image_id, 'thumbnail' ) : wc_placeholder_img_src( 'thumbnail' );
						$name      = $product->get_name();
						$price     = wc_price( $item['line_total'] );
					?>
					<li class="minicart__item flex items-center" data-cart-key="<?php echo esc_attr( $cart_key ); ?>">
						<div class="minicart__item-img-wrap flex-shrink-0 overflow-hidden">
							<img class="w-100 h-100 block object-cover" src="<?php echo esc_url( $image_url ); ?>" alt="<?php echo esc_attr( $name ); ?>">
						</div>
						<div class="minicart__item-info flex flex-col flex-1">
							<span class="minicart__item-name block yekan-14 color-black-80 overflow-hidden"><?php echo esc_html( $name ); ?></span>
							<span class="minicart__item-price yekan-12 color-black-50"><?php echo wp_kses_post( $price ); ?> </span>
						</div>
						<button type="button" class="minicart__item-remove flex-shrink-0 flex item-center pointer"
							data-cart-key="<?php echo esc_attr( $cart_key ); ?>"
							data-nonce="<?php echo esc_attr( $nonce ); ?>"
							data-ajax-url="<?php echo esc_url( $ajax_url ); ?>"
							aria-label="حذف از سبد خرید">
							<svg width="18" height="18" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
								<path d="M21 5.98C17.67 5.65 14.32 5.48 10.98 5.48c-1.98 0-3.96.1-5.94.3L3 5.98" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
								<path d="M8.5 4.97l.22-1.31C8.88 2.71 9 2 10.69 2h2.62C15 2 15.13 2.75 15.28 3.67L15.5 4.97" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
								<path d="M18.85 9.14l-.65 10.07C18.09 20.78 18 22 15.21 22H8.79C5.999 22 5.91 20.78 5.8 19.21L5.15 9.14" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
								<path d="M10.33 16.5h3.33M9.5 12.5h5" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
							</svg>
						</button>
					</li>
					<?php endforeach; ?>
				</ul>
				<div class="minicart__total flex items-center justify-between">
					<span class="minicart__total-label color-primary yekan-18">مبلغ قابل پرداخت</span>
					<span class="minicart__total-value color-primary yekan-18"><?php echo wp_kses_post( $total ); ?></span>
				</div>
				<a href="<?php echo esc_url( $checkout ); ?>" class="minicart__checkout-btn block w-100 text-center color-white yekan-18 bold">ثبت سفارش</a>
			<?php endif; ?>
		</div>
		<?php
		return ob_get_clean();
	}

	public function minicart_fragment( array $fragments ): array {
		$fragments['#tc-minicart-dropdown'] = self::render_minicart();

		$count        = WC()->cart ? WC()->cart->get_cart_contents_count() : 0;
		$badge_inner  = $count > 0
			? '<span class="minicart-badge absolute flex item-center bg-primary">' . esc_html( $count ) . '</span>'
			: '';
		$fragments['#tc-minicart-badge-wrap']        = '<span id="tc-minicart-badge-wrap">' . $badge_inner . '</span>';
		$fragments['#tc-minicart-badge-wrap-mobile'] = '<span id="tc-minicart-badge-wrap-mobile">' . $badge_inner . '</span>';

		return $fragments;
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

			// Conditionally enqueue standalone CSS for specific page templates
			$template_slug = function_exists('get_page_template_slug') ? get_page_template_slug() : '';

			if (is_category()) {
				$template_slug = 'blog.php';
			}

			if ($template_slug) {
				// Expect template slugs like 'page-templates/contact-us.php'
				$basename = basename($template_slug, '.php');
				$pt_css_rel = '/assets/frontend/dist/css/page-templates/' . $basename . '.css';
				$pt_css_abs = get_theme_file_path($pt_css_rel);
				if (file_exists($pt_css_abs)) {
					$pt_ver = filemtime($pt_css_abs);
					wp_enqueue_style('tanilchoob-' . $basename, get_template_directory_uri() . $pt_css_rel, ['tanilchoob'], $pt_ver);
				}
			}

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
		$classes = 'breadcrumb yekan-12 md:yekan-16 py-20';
		$args['wrap_before'] = '<div class="container"><nav class="' . $classes . '" aria-label="breadcrumb">';
		$args['wrap_after']  = '</nav></div>';
		$args['delimiter']  = '<span class="color-black-30">&nbsp;/&nbsp;</span>';
		$args['home'] = 'تانیل چوب';
		
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
*/
	public function apply_product_option_adjustments($cart)
	{
		if (is_admin() && !defined('DOING_AJAX')) { return; }
		if (empty($cart)) { return; }
		foreach ($cart->get_cart() as $key => $item) {
			if (!empty($item['tc_option_adjustments']) && is_array($item['tc_option_adjustments'])) {
				$sum_tomans = 0.0;
				foreach ($item['tc_option_adjustments'] as $opt) {
					$sum_tomans += isset($opt['amount']) ? (float) $opt['amount'] : 0.0;
				}
				
				// Base price is extracted in Rials for schema compliance
				$base_rials = isset($item['tc_base_price']) && $item['tc_base_price'] !== null ? (float) $item['tc_base_price'] : (float) $item['data']->get_price();
				
				// Multiply Toman options by 10 to safely add them to the Rial base
				$new_price_rials = $base_rials + ($sum_tomans * 10);
				
				if ($new_price_rials < 0) { $new_price_rials = 0; }
				$item['data']->set_price($new_price_rials);
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
				// Using number_format instead of wc_price to prevent HTML injection in DB
				$item->add_meta_data(
					isset($opt['label']) ? $opt['label'] : __('گزینه', 'tanilchoob'),
					$sign . ' ' . number_format(abs($amount))
				);
			}
		}
	}

	public function contact_mode_add_to_cart_text($text)
	{
		if (Helper::get_options_field('contact_mode')) {
			return 'تماس با ما';
		}
		return $text;
	}

	public function change_currency_symbol( string $currency_symbol, string $currency ): string {
		if ( $currency === 'IRR' ) {
            // Keep "ریال" in the admin dashboard so it accurately matches the undivided DB value
            if ( is_admin() && ! Helper::requestIsFrontendAjax() ) {
                return 'ریال'; 
            }
            return 'تومان';
        }
		return $currency_symbol;
	}

	public function divide_price_by_10( $price ) {
		if ( is_admin() && ! Helper::requestIsFrontendAjax() ) return $price;
		if ( is_numeric( $price ) ) return (float) $price / 10;
		return $price;
	}

	public function divide_variation_json_by_10( array $data, $_product, $_variation ): array {
		if ( is_admin() && ! Helper::requestIsFrontendAjax() ) return $data;
		if ( isset( $data['display_price'] ) ) {
			$data['display_price'] = (float) $data['display_price'] / 10;
		}
		if ( isset( $data['display_regular_price'] ) ) {
			$data['display_regular_price'] = (float) $data['display_regular_price'] / 10;
		}
		return $data;
	}

	public function correct_cart_options_to_rials( array $cart_item_data, int $product_id, int $variation_id ): array {
		if ( isset( $cart_item_data['tc_option_adjustments'] ) && is_array( $cart_item_data['tc_option_adjustments'] ) ) {
			foreach ( $cart_item_data['tc_option_adjustments'] as &$opt ) {
				if ( isset( $opt['amount'] ) ) {
					$opt['amount'] = (float) $opt['amount'] * 10;
				}
			}
		}
		return $cart_item_data;
	}

	public function force_internal_links($content)
	{
		if (is_admin()) {
			return $content;
		}

		$link_class = '\SeoAutomatedLinkBuilding\Link';
		$converter_class = '\SeoAutomatedLinkBuilding\TextConverter';
		if (!class_exists($converter_class) || !class_exists($link_class)) {
			return $content;
		}
		
		$query = call_user_func([$link_class, 'query']);
		$links = $query
			->where('active', true)
			->order_by('priority', 'desc')
			->get();

		if (empty($links)) {
			return $content;
		}

		try {
			$converter = new $converter_class($content);
			$converter->addLinks($links);
			return $converter->getText();
		} catch (\Exception $e) {
			return $content;
		}
	}
}