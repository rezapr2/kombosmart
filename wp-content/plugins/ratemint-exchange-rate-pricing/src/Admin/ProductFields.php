<?php
/**
 * Product and variation edit fields.
 *
 * @package RateMint
 */

namespace RateMint\Admin;

use RateMint\Format;
use RateMint\Pricer;
use RateMint\Rates;
use RateMint\Settings;
use WC_Product;
use WC_Product_Variable;
use WC_Product_Variation;

defined( 'ABSPATH' ) || exit;

/**
 * Adds the pricing mode, markup and base currency price fields to WooCommerce's product data panel.
 */
final class ProductFields {

	/**
	 * Posted field name => meta key for base prices.
	 */
	const PRICE_FIELDS = array(
		'erpfw_regular_price' => Pricer::META_REGULAR,
		'erpfw_sale_price'    => Pricer::META_SALE,
		'erpfw_sale_percent'  => Pricer::META_SALE_PERCENT,
	);

	/**
	 * Register hooks.
	 */
	public static function init() {
		add_action( 'woocommerce_product_options_general_product_data', array( __CLASS__, 'mode_fields' ) );
		add_action( 'woocommerce_product_options_pricing', array( __CLASS__, 'price_fields' ) );
		add_action( 'woocommerce_variation_options_pricing', array( __CLASS__, 'variation_fields' ), 10, 3 );
		add_action( 'woocommerce_admin_process_product_object', array( __CLASS__, 'save_product' ) );
		add_action( 'woocommerce_process_product_meta', array( __CLASS__, 'after_save_product' ), 50 );
		add_action( 'woocommerce_admin_process_variation_object', array( __CLASS__, 'save_variation' ), 10, 2 );
		add_action( 'woocommerce_ajax_save_product_variations', array( __CLASS__, 'after_save_variations' ) );
		add_action( 'wp_ajax_erpfw_preview', array( __CLASS__, 'ajax_preview' ) );
	}

	/**
	 * The product being edited.
	 *
	 * @return WC_Product|null
	 */
	private static function current_product() {
		global $product_object;

		return $product_object instanceof WC_Product ? $product_object : null;
	}

	/**
	 * Localized value for a decimal input.
	 *
	 * @param mixed $value Stored value.
	 * @return string
	 */
	private static function input_value( $value ) {
		return '' === (string) $value ? '' : wc_format_localized_decimal( $value );
	}

	/**
	 * Mode and markup fields, shown for simple, external and variable products.
	 */
	public static function mode_fields() {
		$product = self::current_product();
		$base    = Settings::base_currency();

		/* translators: %s: currency code such as USD. */
		$foreign_label = sprintf( __( 'Exchange rate (%s)', 'ratemint-exchange-rate-pricing' ), $base );
		$manual_label  = __( 'Manual price', 'ratemint-exchange-rate-pricing' );
		$default_label = Pricer::MODE_FOREIGN === Pricer::effective_mode( Pricer::MODE_DEFAULT ) ? $foreign_label : $manual_label;

		$inherited = $product ? Pricer::resolve_markup(
			$product,
			array(
				'markup_percent' => '',
				'markup_fixed'   => '',
			)
		) : array(
			'percent' => (float) Settings::get( 'markup_percent' ),
			'fixed'   => (float) Settings::get( 'markup_fixed' ),
		);

		echo '<div class="options_group erpfw-options show_if_simple show_if_external show_if_variable">';

		woocommerce_wp_select(
			array(
				'id'          => 'erpfw_mode',
				'value'       => $product ? (string) $product->get_meta( Pricer::META_MODE ) : '',
				'label'       => __( 'Pricing', 'ratemint-exchange-rate-pricing' ),
				'options'     => array(
					/* translators: %s: default pricing mode name. */
					''        => sprintf( __( 'Store default (%s)', 'ratemint-exchange-rate-pricing' ), $default_label ),
					'foreign' => $foreign_label,
					'manual'  => $manual_label,
				),
				'desc_tip'    => true,
				'description' => __( 'Exchange rate: the store price is calculated from a base currency price and updated automatically when the rate changes.', 'ratemint-exchange-rate-pricing' ),
			)
		);

		echo '<div class="erpfw-foreign-only">';

		woocommerce_wp_text_input(
			array(
				'id'                => 'erpfw_markup_percent',
				'value'             => $product ? self::input_value( $product->get_meta( Pricer::META_MARKUP_PERCENT ) ) : '',
				'label'             => __( 'Markup (%)', 'ratemint-exchange-rate-pricing' ),
				'placeholder'       => wc_format_localized_decimal( $inherited['percent'] ),
				'class'             => 'short',
				'custom_attributes' => array( 'inputmode' => 'decimal' ),
				'desc_tip'          => true,
				'description'       => __( 'Leave empty to use the category or store markup (shown as placeholder).', 'ratemint-exchange-rate-pricing' ),
			)
		);

		$fixed = $product ? (string) $product->get_meta( Pricer::META_MARKUP_FIXED ) : '';

		woocommerce_wp_text_input(
			array(
				'id'                => 'erpfw_markup_fixed',
				'value'             => '' === $fixed ? '' : wc_format_localized_decimal( Settings::to_display( $fixed ) ),
				/* translators: %s: unit such as Toman. */
				'label'             => sprintf( __( 'Fixed markup (%s)', 'ratemint-exchange-rate-pricing' ), Settings::unit_label() ),
				'placeholder'       => wc_format_localized_decimal( Settings::to_display( $inherited['fixed'] ) ),
				'class'             => 'short',
				'custom_attributes' => array( 'inputmode' => 'decimal' ),
				'desc_tip'          => true,
				'description'       => __( 'Leave empty to use the category or store value (shown as placeholder).', 'ratemint-exchange-rate-pricing' ),
			)
		);

		echo '</div></div>';
	}

	/**
	 * Base price fields for simple and external products.
	 */
	public static function price_fields() {
		$product = self::current_product();

		echo '<div class="erpfw-price-fields erpfw-foreign-only">';

		foreach ( self::price_field_definitions() as $name => $definition ) {
			woocommerce_wp_text_input(
				array(
					'id'                => $name,
					'value'             => $product ? self::input_value( $product->get_meta( self::PRICE_FIELDS[ $name ] ) ) : '',
					'label'             => $definition['label'],
					'class'             => 'short',
					'custom_attributes' => array( 'inputmode' => 'decimal' ),
				)
			);
		}

		self::preview_markup( 'simple' );

		echo '</div>';
	}

	/**
	 * Base price fields for a variation.
	 *
	 * @param int      $loop           Variation index.
	 * @param array    $variation_data Variation data.
	 * @param \WP_Post $variation      Variation post.
	 */
	public static function variation_fields( $loop, $variation_data, $variation ) {
		$product = wc_get_product( $variation->ID );
		$classes = array( 'form-row-first', 'form-row-last' );
		$index   = 0;

		echo '<div class="erpfw-variation-fields erpfw-foreign-only">';

		foreach ( self::price_field_definitions() as $name => $definition ) {
			$short = substr( $name, strlen( 'erpfw_' ) );

			woocommerce_wp_text_input(
				array(
					'id'                => "erpfw_variable_{$short}_{$loop}",
					'name'              => "erpfw_variable_{$short}[{$loop}]",
					'value'             => $product ? self::input_value( $product->get_meta( self::PRICE_FIELDS[ $name ] ) ) : '',
					'label'             => $definition['label'],
					'wrapper_class'     => 'form-row ' . $classes[ $index++ % 2 ],
					'custom_attributes' => array( 'inputmode' => 'decimal' ),
				)
			);
		}

		self::preview_markup( 'variation', (int) $loop );

		echo '</div>';
	}

	/**
	 * Price fields allowed by the sale mode setting.
	 *
	 * @return array Field name => [ label ].
	 */
	private static function price_field_definitions() {
		$symbol    = Format::currency_symbol( Settings::base_currency() );
		$sale_mode = (string) Settings::get( 'sale_mode' );
		$fields    = array(
			'erpfw_regular_price' => array(
				/* translators: %s: currency symbol such as $. */
				'label' => sprintf( __( 'Base regular price (%s)', 'ratemint-exchange-rate-pricing' ), $symbol ),
			),
		);

		if ( 'percent' !== $sale_mode ) {
			$fields['erpfw_sale_price'] = array(
				/* translators: %s: currency symbol such as $. */
				'label' => sprintf( __( 'Base sale price (%s)', 'ratemint-exchange-rate-pricing' ), $symbol ),
			);
		}

		if ( 'price' !== $sale_mode ) {
			$fields['erpfw_sale_percent'] = array(
				'label' => __( 'Discount (%)', 'ratemint-exchange-rate-pricing' ),
			);
		}

		return $fields;
	}

	/**
	 * Calculated price preview placeholder, filled by JavaScript.
	 *
	 * @param string $context simple or variation.
	 * @param int    $loop    Variation index.
	 */
	private static function preview_markup( $context, $loop = 0 ) {
		printf(
			'<p class="form-field %1$s erpfw-preview" %2$s><label>%3$s</label><span class="erpfw-preview__value">—</span><span class="erpfw-preview__details"></span></p>',
			'variation' === $context ? 'form-row form-row-full' : '',
			'variation' === $context ? 'data-loop="' . esc_attr( (string) $loop ) . '"' : 'data-erpfw-preview="simple"',
			esc_html__( 'Store price', 'ratemint-exchange-rate-pricing' )
		);
	}

	/**
	 * Clean a posted base price or percent.
	 *
	 * @param mixed  $raw  Posted value.
	 * @param string $meta Meta key the value is for.
	 * @return string
	 */
	private static function clean_price( $raw, $meta ) {
		$value = Format::parse_float( $raw );

		if ( null === $value || $value < 0 ) {
			return '';
		}

		if ( Pricer::META_SALE_PERCENT === $meta ) {
			$value = min( 100, $value );
		}

		return wc_format_decimal( $value );
	}

	/**
	 * Save fields before WooCommerce saves the product, and set calculated prices
	 * on the object so the product is saved only once.
	 *
	 * @param WC_Product $product Product being saved.
	 */
	public static function save_product( $product ) {
		if ( ! isset( $_POST['erpfw_mode'], $_POST['woocommerce_meta_nonce'] ) || ! wp_verify_nonce( sanitize_key( wp_unslash( $_POST['woocommerce_meta_nonce'] ) ), 'woocommerce_save_data' ) ) {
			return;
		}

		if ( ! current_user_can( 'edit_product', $product->get_id() ) ) {
			return;
		}

		$mode = sanitize_key( wp_unslash( $_POST['erpfw_mode'] ) );
		$mode = in_array( $mode, array( Pricer::MODE_FOREIGN, Pricer::MODE_MANUAL ), true ) ? $mode : Pricer::MODE_DEFAULT;

		$product->update_meta_data( Pricer::META_MODE, $mode );

		if ( isset( $_POST['erpfw_markup_percent'] ) ) {
			$product->update_meta_data( Pricer::META_MARKUP_PERCENT, Format::parse_number( sanitize_text_field( wp_unslash( $_POST['erpfw_markup_percent'] ) ) ) );
		}

		if ( isset( $_POST['erpfw_markup_fixed'] ) ) {
			$fixed = Format::parse_float( sanitize_text_field( wp_unslash( $_POST['erpfw_markup_fixed'] ) ) );
			$product->update_meta_data( Pricer::META_MARKUP_FIXED, null === $fixed ? '' : wc_format_decimal( Settings::to_store( $fixed ) ) );
		}

		if ( $product->is_type( 'variable' ) ) {
			return;
		}

		foreach ( self::PRICE_FIELDS as $field => $meta ) {
			if ( isset( $_POST[ $field ] ) ) {
				$product->update_meta_data( $meta, self::clean_price( sanitize_text_field( wp_unslash( $_POST[ $field ] ) ), $meta ) );
			}
		}

		Pricer::apply_to_object( $product );
	}

	/**
	 * Recalculate variations after a variable product was saved (its mode or markup may have changed).
	 *
	 * @param int $post_id Product id.
	 */
	public static function after_save_product( $post_id ) {
		$product = wc_get_product( $post_id );

		if ( $product && $product->is_type( 'variable' ) && Pricer::is_foreign( $product ) ) {
			Pricer::apply( $product );
		}
	}

	/**
	 * Save variation fields and set calculated prices before WooCommerce saves the variation.
	 *
	 * @param WC_Product_Variation $variation Variation.
	 * @param int                  $i         Variation index in the posted arrays.
	 */
	public static function save_variation( $variation, $i ) {
		// Variations are saved over AJAX; also accept the product form nonce in case WooCommerce saves them there.
		$verified = ( isset( $_POST['security'] ) && wp_verify_nonce( sanitize_key( wp_unslash( $_POST['security'] ) ), 'save-variations' ) )
			|| ( isset( $_POST['woocommerce_meta_nonce'] ) && wp_verify_nonce( sanitize_key( wp_unslash( $_POST['woocommerce_meta_nonce'] ) ), 'woocommerce_save_data' ) );

		if ( ! $verified || ! current_user_can( 'edit_product', $variation->get_parent_id() ) ) {
			return;
		}

		$touched = false;

		foreach ( self::PRICE_FIELDS as $field => $meta ) {
			$key = 'erpfw_variable_' . substr( $field, strlen( 'erpfw_' ) );

			if ( isset( $_POST[ $key ][ $i ] ) ) {
				$variation->update_meta_data( $meta, self::clean_price( sanitize_text_field( wp_unslash( $_POST[ $key ][ $i ] ) ), $meta ) );
				$touched = true;
			}
		}

		if ( $touched ) {
			$parent = wc_get_product( $variation->get_parent_id() );
			Pricer::apply_to_object( $variation, $parent ? $parent : null );
		}
	}

	/**
	 * Resync the parent's price range after variations were saved.
	 *
	 * @param int $product_id Parent product id.
	 */
	public static function after_save_variations( $product_id ) {
		$product = wc_get_product( $product_id );

		if ( $product && Pricer::is_foreign( $product ) ) {
			WC_Product_Variable::sync( $product_id );
		}
	}

	/**
	 * Live preview of calculated prices for unsaved form values.
	 */
	public static function ajax_preview() {
		check_ajax_referer( 'erpfw_admin', 'nonce' );

		$product_id = isset( $_POST['product_id'] ) ? absint( $_POST['product_id'] ) : 0;
		$product    = $product_id ? wc_get_product( $product_id ) : null;

		if ( ! $product || ! current_user_can( 'edit_product', $product_id ) ) {
			wp_send_json_error( array( 'message' => __( 'Product not found.', 'ratemint-exchange-rate-pricing' ) ), 403 );
		}

		if ( ! Rates::get_rate() ) {
			wp_send_json_error( array( 'message' => __( 'Set the exchange rate first.', 'ratemint-exchange-rate-pricing' ) ) );
		}

		$fixed  = Format::parse_float( isset( $_POST['markup_fixed'] ) ? sanitize_text_field( wp_unslash( $_POST['markup_fixed'] ) ) : '' );
		$shared = array(
			'markup_percent' => Format::parse_number( isset( $_POST['markup_percent'] ) ? sanitize_text_field( wp_unslash( $_POST['markup_percent'] ) ) : '' ),
			'markup_fixed'   => null === $fixed ? '' : Settings::to_store( $fixed ),
		);

		if ( isset( $_POST['category_ids'] ) ) {
			$shared['category_ids'] = array_map( 'absint', (array) wp_unslash( $_POST['category_ids'] ) );
		}

		$items  = isset( $_POST['items'] ) && is_array( $_POST['items'] ) ? map_deep( wp_unslash( $_POST['items'] ), 'sanitize_text_field' ) : array();
		$output = array();

		foreach ( $items as $key => $item ) {
			if ( ! is_array( $item ) ) {
				continue;
			}

			$target_id = isset( $item['id'] ) ? absint( $item['id'] ) : 0;
			$target    = $target_id === $product_id ? $product : wc_get_product( $target_id );

			if ( ! $target || ( $target->get_id() !== $product_id && $target->get_parent_id() !== $product_id ) ) {
				continue;
			}

			$overrides = $shared + array(
				'regular'      => Format::parse_number( isset( $item['regular'] ) ? $item['regular'] : '' ),
				'sale'         => Format::parse_number( isset( $item['sale'] ) ? $item['sale'] : '' ),
				'sale_percent' => Format::parse_number( isset( $item['sale_percent'] ) ? $item['sale_percent'] : '' ),
			);

			$parent                        = $target->is_type( 'variation' ) ? $product : null;
			$output[ sanitize_key( $key ) ] = self::preview_payload( $target, $parent, $overrides );
		}

		wp_send_json_success( array( 'items' => $output ) );
	}

	/**
	 * Preview data for one product or variation.
	 *
	 * @param WC_Product      $product   Product or variation.
	 * @param WC_Product|null $parent    Parent of a variation.
	 * @param array           $overrides Unsaved form values.
	 * @return array
	 */
	private static function preview_payload( WC_Product $product, $parent, array $overrides ) {
		$args   = Pricer::calculation_args( $product, $parent, $overrides );
		$prices = Pricer::calculate( $product, $parent, $overrides );

		if ( null === $prices || null === $args ) {
			return array(
				'status' => 'missing',
				'text'   => __( 'Enter a base regular price to calculate.', 'ratemint-exchange-rate-pricing' ),
			);
		}

		$decimals = wc_get_price_decimals();
		$text     = Format::store_amount( $prices['regular'] );

		if ( null !== $prices['sale'] ) {
			/* translators: 1: regular price, 2: sale price. */
			$text = sprintf( __( '%1$s, on sale for %2$s', 'ratemint-exchange-rate-pricing' ), $text, Format::store_amount( $prices['sale'] ) );
		}

		return array(
			'status'  => 'ok',
			'regular' => wc_format_decimal( $prices['regular'], $decimals ),
			'sale'    => null === $prices['sale'] ? '' : wc_format_decimal( $prices['sale'], $decimals ),
			'text'    => $text,
			'details' => sprintf(
				/* translators: 1: exchange rate such as "1 USD = 100,000 Toman", 2: markup percent, 3: fixed markup with unit. */
				__( '%1$s · markup %2$s%% + %3$s', 'ratemint-exchange-rate-pricing' ),
				Format::rate( $args['rate'] ),
				Format::number( $args['markup_percent'] ),
				Format::store_amount( $args['markup_fixed'] )
			),
		);
	}
}
