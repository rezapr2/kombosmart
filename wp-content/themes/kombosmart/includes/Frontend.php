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
		// Preload above-the-fold fonts early to break the CSS->font critical request chain.
		// Priority 1 prints these before the stylesheet (styles print at wp_head priority 8).
		add_action('wp_head', [$this, 'preload_fonts'], 1);
		add_action('wp_head', [$this, 'print_meta_description'], 1);
		add_filter('woocommerce_structured_data_product', [$this, 'product_schema_condition_and_model'], 10, 2);
		add_filter('woocommerce_structured_data_breadcrumblist', [$this, 'maybe_drop_wc_breadcrumb_schema']);
		add_filter('mod_rewrite_rules', [$this, 'fix_security_headers']);
		// Customize WooCommerce breadcrumb classes
		add_filter('woocommerce_breadcrumb_defaults', [$this, 'wc_breadcrumb_defaults']);
		// Disable WooCommerce core styles
		add_filter('woocommerce_enqueue_styles', '__return_empty_array');
		// Dequeue WooCommerce Blocks styles after they are enqueued
		add_action('wp_enqueue_scripts', [$this, 'dequeue_wc_block_styles'], 100);
		// Dashicons isn't used by the theme frontend; drop it unless the admin bar needs it
		add_action('wp_enqueue_scripts', [$this, 'dequeue_dashicons'], 100);
		// WooCommerce Blocks styles (wc-blocks-style and per-block wc-blocks-style-*) are
		// enqueued lazily during block render, long after wp_enqueue_scripts has fired, so a
		// dequeue can't catch them. Suppress them at print time instead, which always works.
		add_filter('style_loader_tag', [$this, 'remove_wc_block_style_tags'], 10, 2);

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
		add_filter( 'wp_schema_pro_schema_product', [$this, 'inject_custom_offers_schema'], 10, 3 );
		add_action( 'wp_head', [$this, 'inject_standalone_video_schema'], 99 );
		add_action( 'wp_head', [$this, 'inject_blog_post_video_schema'], 99 );
		add_action( 'wp_head', [$this, 'inject_blog_post_article_schema'], 99 );
		add_action( 'wp_head', [$this, 'inject_taxonomy_video_schema'], 99 );
	//	add_filter( 'woocommerce_add_cart_item_data',   [ $this, 'correct_cart_options_to_rials' ], 20, 3 );
		add_filter( 'wpseo_robots', [$this, 'noindex_paginated_pages'] );
		add_action( 'template_redirect', [$this, 'noindex_rss_feeds'] );
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
						$image_url = $image_id ? wp_get_attachment_image_url( $image_id, 'woocommerce_gallery_thumbnail' ) : wc_placeholder_img_src( 'woocommerce_gallery_thumbnail' );
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

	/**
	 * Preload the above-the-fold web fonts to break the CSS->font critical request chain.
	 *
	 * The stylesheet's @font-face rules are only discovered after the CSS is downloaded and
	 * parsed, so LCP text waits on a serial CSS->font chain. Emitting a preload in <head>
	 * (before the stylesheet prints) lets the browser fetch the primary weights in parallel
	 * with the CSS. Only Regular (body) and Bold (headings) are above the fold; the decorative
	 * Thin weight is intentionally left to load on demand.
	 *
	 * crossorigin is required even for same-origin fonts: fonts are always fetched in CORS
	 * mode, so without it the preload would not match the @font-face request and the file
	 * would be downloaded twice.
	 */
	public function preload_fonts()
	{
		if (is_admin()) {
			return;
		}

		$fonts_uri = get_template_directory_uri() . '/assets/frontend/dist/fonts/';
		$preload   = [
			'YekanBakhFaNum-Regular.woff2',
			'YekanBakhFaNum-Bold.woff2',
			'YekanBakhFaNum-Thin.woff2',
		];

		foreach ($preload as $file) {
			printf(
				'<link rel="preload" href="%s" as="font" type="font/woff2" crossorigin>' . "\n",
				esc_url($fonts_uri . $file)
			);
		}
	}

	/**
	 * Print a meta description when no SEO plugin is handling it.
	 *
	 * Uses the `_sw_meta_description` post meta when set, otherwise the excerpt (a product's
	 * short description) or the term description, trimmed to ~160 characters. Does nothing
	 * when Yoast SEO or Rank Math is active, so it never duplicates their tag.
	 */
	public function print_meta_description()
	{
		if (is_admin() || defined('WPSEO_VERSION') || class_exists('RankMath')) {
			return;
		}

		$description = '';
		if (is_front_page()) {
			$description = get_bloginfo('description');
		} elseif (is_singular()) {
			$post_id     = get_queried_object_id();
			$description = get_post_meta($post_id, '_sw_meta_description', true) ?: get_the_excerpt($post_id);
		} elseif (is_tax() || is_category() || is_tag()) {
			$description = term_description();
		}

		$description = trim(preg_replace('/\s+/u', ' ', wp_strip_all_tags((string) $description)));
		if ($description === '') {
			return;
		}
		if (mb_strlen($description) > 160) {
			$description = rtrim(mb_substr($description, 0, 157)) . '…';
		}

		printf('<meta name="description" content="%s">' . "\n", esc_attr($description));
	}

	/**
	 * Add itemCondition (from the product's "وضعیت کالا" field) and model to WooCommerce's
	 * Product JSON-LD. Stock laptops must not be advertised to Google as new.
	 */
	public function product_schema_condition_and_model($markup, $product)
	{
		$conditions = [
			'new'         => 'https://schema.org/NewCondition',
			'used'        => 'https://schema.org/UsedCondition',
			'refurbished' => 'https://schema.org/RefurbishedCondition',
		];
		$condition = function_exists('get_field') ? get_field('product_condition', $product->get_id()) : '';
		if ($condition && isset($conditions[$condition]) && !empty($markup['offers'])) {
			foreach ($markup['offers'] as &$offer) {
				$offer['itemCondition'] = $conditions[$condition];
			}
			unset($offer);
		}

		$model = function_exists('get_field') ? get_field('product_model', $product->get_id()) : '';
		if ($model) {
			$markup['model'] = $model;
		}

		return $markup;
	}

	/**
	 * Yoast SEO already outputs a BreadcrumbList in its schema graph; drop WooCommerce's
	 * duplicate while Yoast is active.
	 */
	public function maybe_drop_wc_breadcrumb_schema($markup)
	{
		return defined('WPSEO_VERSION') ? [] : $markup;
	}

	public function enqueue_scripts()
	{
		if (! is_admin()) {
			global $wp_query;

			$css_relative_path = '/assets/frontend/dist/css/styles.min.css';
			$js_relative_path  = '/assets/frontend/dist/js/scripts.min.js';

			$css_version = filemtime(get_theme_file_path($css_relative_path));
			$js_version  = filemtime(get_theme_file_path($js_relative_path));

			wp_enqueue_style('kombosmart', get_template_directory_uri() . $css_relative_path, [], $css_version);

			// Conditionally enqueue standalone CSS for specific page templates
			$template_slug = function_exists('get_page_template_slug') ? get_page_template_slug() : '';

			if (is_category()) {
				$template_slug = 'blog.php';
			}

			if ($template_slug) {
				// Expect template slugs like 'page-templates/my-account.php'
				$basename = basename($template_slug, '.php');
				$pt_css_rel = '/assets/frontend/dist/css/page-templates/' . $basename . '.css';
				$pt_css_abs = get_theme_file_path($pt_css_rel);
				if (file_exists($pt_css_abs)) {
					$pt_ver = filemtime($pt_css_abs);
					wp_enqueue_style('kombosmart-' . $basename, get_template_directory_uri() . $pt_css_rel, ['kombosmart'], $pt_ver);
				}
			}

			// Conditionally enqueue standalone CSS bundles for theme template types
			// (built by gulp scssTemplatesBuild into dist/css/templates/). Each loads only on its page type.
			$template_bundles = [];
			if ($template_slug === 'templates/homepage.php' || is_front_page()) {
				$template_bundles[] = 'homepage';
			}
			if (is_singular('post')) {
				$template_bundles[] = 'single-post';
			}
			if (function_exists('is_product') && is_product()) {
				$template_bundles[] = 'single-product';
			}
			if (function_exists('is_product_category') && is_product_category()) {
				$template_bundles[] = 'taxonomy-product_cat';
			}
			foreach ($template_bundles as $bundle) {
				$tb_css_rel = '/assets/frontend/dist/css/templates/' . $bundle . '.css';
				$tb_css_abs = get_theme_file_path($tb_css_rel);
				if (file_exists($tb_css_abs)) {
					wp_enqueue_style('kombosmart-' . $bundle, get_template_directory_uri() . $tb_css_rel, ['kombosmart'], filemtime($tb_css_abs));
				}
			}

			wp_enqueue_script('scripts', get_template_directory_uri() . $js_relative_path, ['jquery'], $js_version);
			wp_localize_script('scripts', 'kombosmart', [
				'ajax' => [
					'url'          => admin_url('admin-ajax.php'),
					'nonce' => wp_create_nonce('ajax-nonce'),
					'posts'        => json_encode($wp_query->query_vars), // everything about your loop is here
					'current_page' => get_query_var('paged') ? get_query_var('paged') : 1,
					'max_page'     => $wp_query->max_num_pages,
					'loading'      => __('Loading...', 'kombosmart'),
					'loadMore'     => __('Load more', 'kombosmart'),
				],
			]);

			// Conditionally enqueue standalone JS bundles for theme template types
			// (built by gulp jsTemplatesBuild into dist/js/templates/). Each loads only on
			// its page type and depends on the main 'scripts' handle (jQuery, Swiper, and
			// the localized kombosmart/tcCheckout globals are attached there).
			$js_bundles = [];
			if (is_category()) {
				$js_bundles[] = 'category';
			}
			if (is_singular('post')) {
				$js_bundles[] = 'single-post';
			}
			if (function_exists('is_product') && is_product()) {
				$js_bundles[] = 'single-product';
			}
			if (function_exists('is_product_category') && is_product_category()) {
				$js_bundles[] = 'taxonomy-product_cat';
			}
			if ((function_exists('is_checkout') && is_checkout()) || (function_exists('is_cart') && is_cart()) || (function_exists('is_account_page') && is_account_page())) {
				$js_bundles[] = 'ordering-process';
			}
			foreach ($js_bundles as $bundle) {
				$tb_js_rel = '/assets/frontend/dist/js/templates/' . $bundle . '.js';
				$tb_js_abs = get_theme_file_path($tb_js_rel);
				if (file_exists($tb_js_abs)) {
					wp_enqueue_script('kombosmart-' . $bundle, get_template_directory_uri() . $tb_js_rel, ['jquery', 'scripts'], filemtime($tb_js_abs), true);
				}
			}
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
		$args['home'] = get_bloginfo('name');
		
		return $args;
	}

	public function dequeue_wc_block_styles()
	{
		// Known WooCommerce Blocks style handles (catches anything enqueued before this runs;
		// late, render-time enqueues are handled by remove_wc_block_style_tags()).
		wp_dequeue_style('wc-blocks-style');
		wp_dequeue_style('wc-blocks-style-product-query');
		// WooCommerce inline style handle
		wp_dequeue_style('woocommerce-inline');
	}

	/**
	 * Remove the core dashicons stylesheet from the theme frontend.
	 *
	 * The theme UI doesn't use dashicons. The one frontend consumer that does is the
	 * admin bar (shown to logged-in users), so keep the stylesheet whenever the admin
	 * bar is rendering and drop it otherwise.
	 */
	public function dequeue_dashicons()
	{
		if (is_admin_bar_showing()) {
			return;
		}
		wp_dequeue_style('dashicons');
		wp_deregister_style('dashicons');
	}

	/**
	 * Suppress WooCommerce Blocks stylesheet tags at print time.
	 *
	 * Block styles are registered as wc-blocks-style and wc-blocks-style-* and enqueued
	 * lazily while blocks render (after wp_enqueue_scripts), so dequeue alone misses them.
	 * Filtering the printed tag removes them no matter when they are enqueued. In RTL these
	 * print as wc-blocks-rtl.css under the wc-blocks-style handle.
	 *
	 * @param string $tag    The full <link> tag for the stylesheet.
	 * @param string $handle The stylesheet handle.
	 * @return string Empty string to drop the tag, otherwise the original tag.
	 */
	public function remove_wc_block_style_tags($tag, $handle)
	{
		if (strpos($handle, 'wc-blocks-style') === 0) {
			return '';
		}
		return $tag;
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
					'name' => isset($opt['label']) ? $opt['label'] : __('گزینه', 'kombosmart'),
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
					isset($opt['label']) ? $opt['label'] : __('گزینه', 'kombosmart'),
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

	/**
     * Force Yoast SEO to noindex paginated pages AND WooCommerce sorting/filtering parameters.
     */
    public function noindex_paginated_pages( $robots ) {
        // Check if it's a paginated page, OR if the URL contains WooCommerce sorting parameters
        if ( is_paged() || ( get_query_var('paged') > 1 ) || isset($_GET['orderby']) || isset($_GET['shop_view']) ) {
            return 'noindex, follow';
        }
        return $robots;
    }

	/**
	* Send an X-Robots-Tag HTTP header to noindex RSS feeds.
	*/
	public function noindex_rss_feeds() {
		if ( is_feed() && ! is_admin() ) {
			header( 'X-Robots-Tag: noindex, follow', true );
		}
	}	

	/**
	 * Inject custom price, availability, brand, shipping, return policy, and reviews into Schema Pro.
	 */
	public function inject_custom_offers_schema( $schema, $data, $post ) {
		$post_id = is_object( $post ) ? $post->ID : ( ( is_array( $post ) && isset( $post['ID'] ) ) ? $post['ID'] : get_the_ID() );
		
		if ( ! $post_id ) {
			return $schema;
		}

		$product = wc_get_product( $post_id );
		if ( ! $product ) {
			return $schema;
		}

		// --- 1. BRAND SCHEMA ---
		$schema['brand'] = array(
			'@type' => 'Brand',
			'name'  => get_bloginfo( 'name' ),
		);

		// --- 2. STOCK & SHIPPING TIME LOGIC ---
		if ( ! $product->is_in_stock() ) {
			$price        = '0';
			$availability = 'http://schema.org/OutOfStock';
			$handling_min = 0;
			$handling_max = 0;
		} else {
			// موجود و آماده ارسال (24 الی 48 ساعت)
			$price        = $product->get_price() ?: '0';
			$availability = 'http://schema.org/InStock';
			$handling_min = 1;
			$handling_max = 2;
		}

		// --- 3. SHIPPING DETAILS (Array Format for Schema Pro) ---
		$shipping_details_array = array(
			array(
				'@type'               => 'OfferShippingDetails',
				'shippingDestination' => array(
					array(
						'@type'          => 'DefinedRegion',
						'addressCountry' => 'IR',
					)
				),
				'deliveryTime'        => array(
					'@type'        => 'ShippingDeliveryTime',
					'handlingTime' => array(
						'@type'    => 'QuantitativeValue',
						'minValue' => $handling_min,
						'maxValue' => $handling_max,
						'unitCode' => 'DAY',
					),
					'transitTime'  => array(
						'@type'    => 'QuantitativeValue',
						'minValue' => 1,
						'maxValue' => 3,
						'unitCode' => 'DAY',
					),
				),
			)
		);

		// --- 4. RETURN POLICY (Array Format for Schema Pro) ---
		$return_policy_array = array(
			array(
				'@type'                => 'MerchantReturnPolicy',
				'applicableCountry'    => 'IR',
				'returnPolicyCategory' => 'https://schema.org/MerchantReturnFiniteReturnWindow',
				'merchantReturnDays'   => 7,
				'returnMethod'         => 'https://schema.org/ReturnByMail',
				'returnFees'           => 'https://schema.org/FreeReturn',
				'merchantReturnLink'   => Helper::get_options_field( 'warranty_link' ) ?: home_url( '/' ),
			)
		);

		// --- 5. INJECT OFFERS DATA ---
		if ( ! isset( $schema['offers'] ) ) {
			$schema['offers'] = array( '@type' => 'Offer' );
		}

		if ( isset( $schema['offers']['@type'] ) ) {
			$schema['offers']['price']                   = $price;
			$schema['offers']['priceCurrency']           = get_woocommerce_currency();
			$schema['offers']['availability']            = $availability;
			$schema['offers']['url']                     = $product->get_permalink();
			$schema['offers']['shippingDetails']         = $shipping_details_array;
			$schema['offers']['hasMerchantReturnPolicy'] = $return_policy_array;
		} elseif ( is_array( $schema['offers'] ) ) {
			foreach ( $schema['offers'] as $key => $offer ) {
				$schema['offers'][$key]['price']                   = $price;
				$schema['offers'][$key]['priceCurrency']           = get_woocommerce_currency();
				$schema['offers'][$key]['availability']            = $availability;
				$schema['offers'][$key]['shippingDetails']         = $shipping_details_array;
				$schema['offers'][$key]['hasMerchantReturnPolicy'] = $return_policy_array;
				if ( ! isset( $schema['offers'][$key]['url'] ) ) {
					$schema['offers'][$key]['url'] = $product->get_permalink();
				}
			}
		}

		// --- 6. REVIEWS & AGGREGATE RATING LOGIC ---
		$rating_count   = $product->get_rating_count();
		$average_rating = $product->get_average_rating();

		if ( $rating_count > 0 && $average_rating > 0 ) {
			$schema['aggregateRating'] = array(
				'@type'       => 'AggregateRating',
				'ratingValue' => $average_rating,
				'reviewCount' => $rating_count,
			);

			$comments = get_comments( array(
				'post_id' => $post_id,
				'status'  => 'approve',
				'type'    => 'review',
			) );

			if ( ! empty( $comments ) ) {
				$reviews_array = array();
				
				foreach ( $comments as $comment ) {
					$rating = get_comment_meta( $comment->comment_ID, 'rating', true );
					if ( ! empty( $rating ) ) {
						$reviews_array[] = array(
							'@type'        => 'Review',
							'reviewRating' => array(
								'@type'       => 'Rating',
								'ratingValue' => $rating,
							),
							'author'       => array(
								'@type' => 'Person',
								'name'  => ! empty( $comment->comment_author ) ? $comment->comment_author : 'کاربر',
							),
							'reviewBody'   => wp_strip_all_tags( $comment->comment_content ),
							'datePublished'=> get_comment_date( 'c', $comment->comment_ID ),
						);
					}
				}
				
				if ( ! empty( $reviews_array ) ) {
					$schema['review'] = $reviews_array;
				}
			}
		}

		return $schema;
	}
	
	/**
	 * Output an independent VideoObject schema for self-hosted product videos.
	 */
	public function inject_standalone_video_schema() {
		if ( ! function_exists( 'is_product' ) || ! is_product() ) {
			return;
		}

		$post_id = get_the_ID();
		$product = wc_get_product( $post_id );
		
		if ( ! $product ) {
			return;
		}

		$video_url = get_field('video_gallery_url', $post_id);
		
		if ( empty( $video_url ) ) {
			return;
		}

		// Ensure we extract the raw URL string
		$actual_video_url = is_array( $video_url ) && isset( $video_url['url'] ) ? $video_url['url'] : $video_url;

		if ( is_string( $actual_video_url ) && trim( $actual_video_url ) !== '' ) {
			
			$image_id  = $product->get_image_id();
			$thumbnail = $image_id ? wp_get_attachment_image_url( $image_id, 'full' ) : '';

			if ( $thumbnail ) {
				$schema = array(
					'@context'     => 'https://schema.org',
					'@type'        => 'VideoObject',
					'name'         => $product->get_name() . ' - ویدیو معرفی', 
					'description'  => 'ویدیو بررسی و معرفی ' . $product->get_name(), 
					'thumbnailUrl' => $thumbnail,
					'uploadDate'   => get_the_date( 'c', $post_id ),
					'contentUrl'   => $actual_video_url 
				);

				// Print the JSON-LD directly into the HTML head
				echo "\n<!-- Product Video Schema -->\n";
				echo '<script type="application/ld+json">' . wp_json_encode( $schema ) . '</script>';
				echo "\n<!-- / Product Video Schema -->\n";
			}
		}
	}

	/**
	 * Output an independent VideoObject schema for blog posts.
	 */
	public function inject_blog_post_video_schema() {
		// Only run on individual blog posts
		if ( ! is_singular( 'post' ) ) {
			return;
		}

		$post_id = get_the_ID();
		
		// Pull the specific ACF field used in single-post.php
		$top_video = get_field('top_video', $post_id);
		
		if ( empty( $top_video ) || empty( $top_video['video_link'] ) ) {
			return;
		}

		$actual_video_url = $top_video['video_link'];

		if ( is_string( $actual_video_url ) && trim( $actual_video_url ) !== '' ) {
			
			// Try to get the specific video poster image first
			$thumbnail = '';
			if ( ! empty( $top_video['video_imge']['url'] ) ) {
				$thumbnail = $top_video['video_imge']['url'];
			} elseif ( has_post_thumbnail( $post_id ) ) {
				// Fallback to the main blog post thumbnail
				$thumbnail = get_the_post_thumbnail_url( $post_id, 'full' );
			}

			if ( $thumbnail ) {
				$post_title = get_the_title( $post_id );
				
				$schema = array(
					'@context'     => 'https://schema.org',
					'@type'        => 'VideoObject',
					'name'         => $post_title . ' - ویدیو', 
					'description'  => 'ویدیو مربوط به مقاله ' . $post_title, 
					'thumbnailUrl' => $thumbnail,
					'uploadDate'   => get_the_date( 'c', $post_id ),
					'contentUrl'   => $actual_video_url 
				);

				// Print the JSON-LD directly into the HTML head
				echo "\n<!-- Blog Post Video Schema -->\n";
				echo '<script type="application/ld+json">' . wp_json_encode( $schema ) . '</script>';
				echo "\n<!-- / Blog Post Video Schema -->\n";
			}
		}
	}

	/**
	 * Inject BlogPosting and Organization schema for single blog posts.
	 */
	public function inject_blog_post_article_schema() {
		// Only run on individual blog post pages
		if ( ! is_singular( 'post' ) ) {
			return;
		}

		$post_id   = get_the_ID();
		$permalink = get_permalink( $post_id );
		$home_url  = home_url( '/' );
		$org_id    = rtrim( $home_url, '/' ) . '/#organization';

		// 1. Fetch Featured Image details (URL, Width, Height)
		$image_schema = null;
		if ( has_post_thumbnail( $post_id ) ) {
			$thumb_id   = get_post_thumbnail_id( $post_id );
			$thumb_data = wp_get_attachment_image_src( $thumb_id, 'full' );
			if ( $thumb_data ) {
				$image_schema = array(
					'@type'  => 'ImageObject',
					'url'    => $thumb_data[0],
					'width'  => $thumb_data[1],
					'height' => $thumb_data[2],
				);
			}
		}

		// 2. Fetch Logo details for Organization
		$logo_url = '';
		$custom_logo_id = get_theme_mod( 'custom_logo' );
		if ( $custom_logo_id ) {
			$logo_data = wp_get_attachment_image_src( $custom_logo_id, 'full' );
			if ( $logo_data ) {
				$logo_url = $logo_data[0];
			}
		}
		if ( ! $logo_url ) {
			// Fallback to site icon or brand logo
			$logo_url = get_site_icon_url();
		}

		// 3. Build Organization Entity (Brand)
		$org_schema = array(
			'@type' => 'Organization',
			'@id'   => $org_id,
			'name'  => get_bloginfo( 'name' ),
			'url'   => $home_url,
		);

		if ( $logo_url ) {
			$org_schema['logo'] = array(
				'@type' => 'ImageObject',
				'url'   => $logo_url,
			);
		}

		// 4. Build BlogPosting Entity
		$article_schema = array(
			'@type'            => 'BlogPosting',
			'@id'              => rtrim( $permalink, '/' ) . '/#article',
			'headline'         => get_the_title( $post_id ),
			'url'              => $permalink,
			'datePublished'    => get_the_date( 'c', $post_id ),
			'dateModified'     => get_the_modified_date( 'c', $post_id ),
			'mainEntityOfPage' => array(
				'@type' => 'WebPage',
				'@id'   => $permalink,
			),
			'author'           => array(
				'@id' => $org_id,
			),
			'publisher'        => array(
				'@id' => $org_id,
			),
		);

		if ( $image_schema ) {
			$article_schema['image'] = $image_schema;
		}

		// 5. Output via @graph
		$schema_graph = array(
			'@context' => 'https://schema.org',
			'@graph'   => array(
				$org_schema,
				$article_schema,
			),
		);

		echo "\n<!-- Blog Article & Organization Schema -->\n";
		echo '<script type="application/ld+json">' . wp_json_encode( $schema_graph, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES ) . '</script>';
		echo "\n<!-- / Blog Article & Organization Schema -->\n";
	}
	
	/**
	 * Output an independent VideoObject schema for product category and tag archives.
	 */
	public function inject_taxonomy_video_schema() {
		// Only run on product category or product tag archives
		if ( ! is_tax( 'product_cat' ) && ! is_tax( 'product_tag' ) ) {
			return;
		}

		$term = get_queried_object();
		if ( ! $term || ! isset( $term->term_id ) ) {
			return;
		}

		// Pull the specific ACF field used in products-video-button.php
		$video_url = get_field( 'products_video_url', $term );
		
		if ( empty( $video_url ) ) {
			return;
		}

		$actual_video_url = is_array( $video_url ) && isset( $video_url['url'] ) ? $video_url['url'] : $video_url;

		if ( is_string( $actual_video_url ) && trim( $actual_video_url ) !== '' ) {
			
			$thumbnail = '';
			
			// 1. Try to get the WooCommerce category/tag thumbnail
			$thumb_id  = get_term_meta( $term->term_id, 'thumbnail_id', true );
			if ( $thumb_id ) {
				$thumb_data = wp_get_attachment_image_src( $thumb_id, 'full' );
				if ( $thumb_data ) {
					$thumbnail = $thumb_data[0];
				}
			}
			
			// 2. Fallback to site logo if the category doesn't have a thumbnail
			if ( ! $thumbnail ) {
				$custom_logo_id = get_theme_mod( 'custom_logo' );
				if ( $custom_logo_id ) {
					$logo_data = wp_get_attachment_image_src( $custom_logo_id, 'full' );
					if ( $logo_data ) {
						$thumbnail = $logo_data[0];
					}
				}
			}

			// 3. Final fallback to WordPress Site Icon
			if ( ! $thumbnail ) {
				$thumbnail = get_site_icon_url();
			}

			if ( $thumbnail ) {
				$term_name = single_term_title( '', false );
				
				$schema = array(
					'@context'     => 'https://schema.org',
					'@type'        => 'VideoObject',
					'name'         => $term_name . ' - ویدیو معرفی', 
					'description'  => 'ویدیو بررسی و معرفی ' . $term_name, 
					'thumbnailUrl' => $thumbnail,
					'uploadDate'   => gmdate( 'c' ), // Taxonomies lack publish dates, so we use the current timestamp
					'contentUrl'   => $actual_video_url 
				);

				// Print the JSON-LD directly into the HTML head
				echo "\n<!-- Taxonomy Video Schema -->\n";
				echo '<script type="application/ld+json">' . wp_json_encode( $schema, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES ) . '</script>';
				echo "\n<!-- / Taxonomy Video Schema -->\n";
			}
		}
	}
}
