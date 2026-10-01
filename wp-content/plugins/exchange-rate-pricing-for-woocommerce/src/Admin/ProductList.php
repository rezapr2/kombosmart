<?php
/**
 * Products list column, quick edit and bulk edit.
 *
 * @package ExchangeRatePricing
 */

namespace ExchangeRatePricing\Admin;

use ExchangeRatePricing\Format;
use ExchangeRatePricing\Pricer;
use ExchangeRatePricing\Settings;
use WC_Product;

defined( 'ABSPATH' ) || exit;

/**
 * Shows base prices in the products list and lets them be edited inline.
 */
final class ProductList {

	/**
	 * Register hooks.
	 */
	public static function init() {
		add_filter( 'manage_product_posts_columns', array( __CLASS__, 'add_column' ), 20 );
		add_action( 'manage_product_posts_custom_column', array( __CLASS__, 'render_column' ), 20, 2 );
		add_action( 'woocommerce_product_quick_edit_end', array( __CLASS__, 'quick_edit_fields' ) );
		add_action( 'woocommerce_product_bulk_edit_end', array( __CLASS__, 'bulk_edit_fields' ) );
		add_action( 'woocommerce_product_quick_edit_save', array( __CLASS__, 'quick_edit_save' ) );
		add_action( 'woocommerce_product_bulk_edit_save', array( __CLASS__, 'bulk_edit_save' ) );
	}

	/**
	 * Add the base price column after the price column.
	 *
	 * @param array $columns Columns.
	 * @return array
	 */
	public static function add_column( $columns ) {
		/* translators: %s: currency code such as USD. */
		$label  = sprintf( __( 'Base price (%s)', 'exchange-rate-pricing-for-woocommerce' ), Settings::base_currency() );
		$output = array();

		foreach ( $columns as $key => $value ) {
			$output[ $key ] = $value;

			if ( 'price' === $key ) {
				$output['erpfw_price'] = $label;
			}
		}

		if ( ! isset( $output['erpfw_price'] ) ) {
			$output['erpfw_price'] = $label;
		}

		return $output;
	}

	/**
	 * Render the base price column.
	 *
	 * @param string $column  Column id.
	 * @param int    $post_id Product id.
	 */
	public static function render_column( $column, $post_id ) {
		if ( 'erpfw_price' !== $column ) {
			return;
		}

		$product = wc_get_product( $post_id );

		if ( ! $product || ! in_array( $product->get_type(), Pricer::supported_types(), true ) ) {
			echo '<span class="na">–</span>';
			return;
		}

		if ( ! Pricer::is_foreign( $product ) ) {
			echo '<span class="erpfw-badge">' . esc_html__( 'Manual', 'exchange-rate-pricing-for-woocommerce' ) . '</span>';
		} elseif ( $product->is_type( 'variable' ) ) {
			self::render_variable( $product );
		} else {
			self::render_simple( $product );
		}

		if ( Pricer::is_foreign( $product ) && Pricer::is_missing_price( $product ) ) {
			printf(
				'<br><span class="erpfw-missing"><span class="dashicons dashicons-warning" aria-hidden="true"></span> %s</span>',
				esc_html__( 'Missing base price', 'exchange-rate-pricing-for-woocommerce' )
			);
		}

		// Data for quick edit.
		printf(
			'<div class="hidden erpfw-inline" data-mode="%1$s" data-regular="%2$s" data-sale="%3$s" data-percent="%4$s" data-type="%5$s"></div>',
			esc_attr( (string) $product->get_meta( Pricer::META_MODE ) ),
			esc_attr( self::localized( $product->get_meta( Pricer::META_REGULAR ) ) ),
			esc_attr( self::localized( $product->get_meta( Pricer::META_SALE ) ) ),
			esc_attr( self::localized( $product->get_meta( Pricer::META_SALE_PERCENT ) ) ),
			esc_attr( $product->get_type() )
		);
	}

	/**
	 * Localized decimal or ''.
	 *
	 * @param mixed $value Value.
	 * @return string
	 */
	private static function localized( $value ) {
		return '' === (string) $value ? '' : wc_format_localized_decimal( $value );
	}

	/**
	 * Base price of a simple or external product.
	 *
	 * @param WC_Product $product Product.
	 */
	private static function render_simple( WC_Product $product ) {
		$regular = (string) $product->get_meta( Pricer::META_REGULAR );

		if ( '' === $regular ) {
			return;
		}

		$sale    = (string) $product->get_meta( Pricer::META_SALE );
		$percent = (float) $product->get_meta( Pricer::META_SALE_PERCENT );
		$mode    = (string) Settings::get( 'sale_mode' );

		if ( '' !== $sale && 'percent' !== $mode ) {
			printf( '<del>%1$s</del> <ins>%2$s</ins>', esc_html( Format::foreign( $regular ) ), esc_html( Format::foreign( $sale ) ) );
		} elseif ( $percent > 0 && 'price' !== $mode ) {
			printf( '%1$s <span class="erpfw-badge">−%2$s%%</span>', esc_html( Format::foreign( $regular ) ), esc_html( Format::number( $percent ) ) );
		} else {
			echo esc_html( Format::foreign( $regular ) );
		}
	}

	/**
	 * Base price range of a variable product.
	 *
	 * @param WC_Product $product Variable product.
	 */
	private static function render_variable( WC_Product $product ) {
		$prices = array();

		foreach ( $product->get_children() as $child_id ) {
			$price = get_post_meta( $child_id, Pricer::META_REGULAR, true );

			if ( '' !== (string) $price ) {
				$prices[] = (float) $price;
			}
		}

		if ( ! $prices ) {
			return;
		}

		$min = min( $prices );
		$max = max( $prices );

		echo esc_html( $min === $max ? Format::foreign( $min ) : Format::foreign( $min ) . ' – ' . Format::foreign( $max ) );
	}

	/**
	 * Mode select options.
	 *
	 * @return array
	 */
	private static function mode_options() {
		return array(
			''        => __( 'Store default', 'exchange-rate-pricing-for-woocommerce' ),
			/* translators: %s: currency code such as USD. */
			'foreign' => sprintf( __( 'Exchange rate (%s)', 'exchange-rate-pricing-for-woocommerce' ), Settings::base_currency() ),
			'manual'  => __( 'Manual price', 'exchange-rate-pricing-for-woocommerce' ),
		);
	}

	/**
	 * Quick edit fields.
	 */
	public static function quick_edit_fields() {
		$symbol = Format::currency_symbol( Settings::base_currency() );
		/* translators: %s: currency symbol such as $. */
		$regular_label = sprintf( __( 'Base regular price (%s)', 'exchange-rate-pricing-for-woocommerce' ), $symbol );
		/* translators: %s: currency symbol such as $. */
		$sale_label = sprintf( __( 'Base sale price (%s)', 'exchange-rate-pricing-for-woocommerce' ), $symbol );
		?>
		<div class="erpfw-quick-edit">
			<br class="clear" />
			<h4><?php esc_html_e( 'Exchange rate pricing', 'exchange-rate-pricing-for-woocommerce' ); ?></h4>
			<input type="hidden" name="erpfw_quick_edit" value="1" />
			<label>
				<span class="title"><?php esc_html_e( 'Pricing', 'exchange-rate-pricing-for-woocommerce' ); ?></span>
				<span class="input-text-wrap">
					<select name="erpfw_mode">
						<?php foreach ( self::mode_options() as $value => $label ) : ?>
							<option value="<?php echo esc_attr( $value ); ?>"><?php echo esc_html( $label ); ?></option>
						<?php endforeach; ?>
					</select>
				</span>
			</label>
			<div class="erpfw-quick-prices">
				<label>
					<span class="title"><?php echo esc_html( $regular_label ); ?></span>
					<span class="input-text-wrap"><input type="text" name="erpfw_regular_price" class="text" inputmode="decimal" value="" /></span>
				</label>
				<?php if ( 'percent' !== Settings::get( 'sale_mode' ) ) : ?>
					<label>
						<span class="title"><?php echo esc_html( $sale_label ); ?></span>
						<span class="input-text-wrap"><input type="text" name="erpfw_sale_price" class="text" inputmode="decimal" value="" /></span>
					</label>
				<?php endif; ?>
				<?php if ( 'price' !== Settings::get( 'sale_mode' ) ) : ?>
					<label>
						<span class="title"><?php esc_html_e( 'Discount (%)', 'exchange-rate-pricing-for-woocommerce' ); ?></span>
						<span class="input-text-wrap"><input type="text" name="erpfw_sale_percent" class="text" inputmode="decimal" value="" /></span>
					</label>
				<?php endif; ?>
			</div>
		</div>
		<?php
	}

	/**
	 * Bulk edit fields.
	 */
	public static function bulk_edit_fields() {
		?>
		<div class="erpfw-bulk-edit">
			<br class="clear" />
			<h4><?php esc_html_e( 'Exchange rate pricing', 'exchange-rate-pricing-for-woocommerce' ); ?></h4>
			<label>
				<span class="title"><?php esc_html_e( 'Pricing', 'exchange-rate-pricing-for-woocommerce' ); ?></span>
				<span class="input-text-wrap">
					<select name="erpfw_bulk_mode">
						<option value=""><?php esc_html_e( '— No change —', 'exchange-rate-pricing-for-woocommerce' ); ?></option>
						<option value="default"><?php esc_html_e( 'Store default', 'exchange-rate-pricing-for-woocommerce' ); ?></option>
						<?php foreach ( array_slice( self::mode_options(), 1, null, true ) as $value => $label ) : ?>
							<option value="<?php echo esc_attr( $value ); ?>"><?php echo esc_html( $label ); ?></option>
						<?php endforeach; ?>
					</select>
				</span>
			</label>
			<label>
				<span class="title"><?php esc_html_e( 'Markup (%)', 'exchange-rate-pricing-for-woocommerce' ); ?></span>
				<span class="input-text-wrap">
					<input type="text" name="erpfw_bulk_markup_percent" class="text" inputmode="decimal" value="" placeholder="<?php esc_attr_e( '— No change —', 'exchange-rate-pricing-for-woocommerce' ); ?>" />
				</span>
			</label>
		</div>
		<?php
	}

	/**
	 * Save quick edit fields, then recalculate.
	 *
	 * @param WC_Product $product Product.
	 */
	public static function quick_edit_save( $product ) {
		if ( ! isset( $_REQUEST['woocommerce_quick_edit_nonce'] ) || ! wp_verify_nonce( sanitize_key( wp_unslash( $_REQUEST['woocommerce_quick_edit_nonce'] ) ), 'woocommerce_quick_edit_nonce' ) ) {
			return;
		}

		if ( empty( $_REQUEST['erpfw_quick_edit'] ) || ! $product instanceof WC_Product || ! current_user_can( 'edit_product', $product->get_id() ) ) {
			return;
		}

		if ( isset( $_REQUEST['erpfw_mode'] ) ) {
			$mode = sanitize_key( wp_unslash( $_REQUEST['erpfw_mode'] ) );
			$product->update_meta_data( Pricer::META_MODE, in_array( $mode, array( Pricer::MODE_FOREIGN, Pricer::MODE_MANUAL ), true ) ? $mode : Pricer::MODE_DEFAULT );
		}

		if ( ! $product->is_type( 'variable' ) ) {
			$fields = array(
				'erpfw_regular_price' => Pricer::META_REGULAR,
				'erpfw_sale_price'    => Pricer::META_SALE,
				'erpfw_sale_percent'  => Pricer::META_SALE_PERCENT,
			);

			foreach ( $fields as $field => $meta ) {
				if ( isset( $_REQUEST[ $field ] ) ) {
					$value = Format::parse_float( sanitize_text_field( wp_unslash( $_REQUEST[ $field ] ) ) );
					$product->update_meta_data( $meta, null === $value || $value < 0 ? '' : wc_format_decimal( $value ) );
				}
			}
		}

		$product->save_meta_data();
		Pricer::apply( $product );
	}

	/**
	 * Save bulk edit fields, then recalculate.
	 *
	 * @param WC_Product $product Product.
	 */
	public static function bulk_edit_save( $product ) {
		if ( ! isset( $_REQUEST['woocommerce_quick_edit_nonce'] ) || ! wp_verify_nonce( sanitize_key( wp_unslash( $_REQUEST['woocommerce_quick_edit_nonce'] ) ), 'woocommerce_quick_edit_nonce' ) ) {
			return;
		}

		$mode   = isset( $_REQUEST['erpfw_bulk_mode'] ) ? sanitize_key( wp_unslash( $_REQUEST['erpfw_bulk_mode'] ) ) : '';
		$markup = isset( $_REQUEST['erpfw_bulk_markup_percent'] ) ? Format::parse_number( sanitize_text_field( wp_unslash( $_REQUEST['erpfw_bulk_markup_percent'] ) ) ) : '';

		if ( ( '' === $mode && '' === $markup ) || ! $product instanceof WC_Product || ! current_user_can( 'edit_product', $product->get_id() ) ) {
			return;
		}

		if ( in_array( $mode, array( 'default', Pricer::MODE_FOREIGN, Pricer::MODE_MANUAL ), true ) ) {
			$product->update_meta_data( Pricer::META_MODE, 'default' === $mode ? Pricer::MODE_DEFAULT : $mode );
		}

		if ( '' !== $markup ) {
			$product->update_meta_data( Pricer::META_MARKUP_PERCENT, $markup );
		}

		$product->save_meta_data();
		Pricer::apply( $product );
	}
}
