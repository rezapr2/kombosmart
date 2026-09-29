<?php
/**
 * Product category markup fields.
 *
 * @package ExchangeRatePricing
 */

namespace ExchangeRatePricing\Admin;

use ExchangeRatePricing\Format;
use ExchangeRatePricing\Pricer;
use ExchangeRatePricing\Recalculator;
use ExchangeRatePricing\Settings;
use WP_Term;

defined( 'ABSPATH' ) || exit;

/**
 * Lets each product category override the store markup. Subcategories inherit from their parent.
 */
final class CategoryFields {

	/**
	 * Register hooks.
	 */
	public static function init() {
		add_action( 'product_cat_add_form_fields', array( __CLASS__, 'add_fields' ), 20 );
		add_action( 'product_cat_edit_form_fields', array( __CLASS__, 'edit_fields' ), 20 );
		add_action( 'created_product_cat', array( __CLASS__, 'save' ) );
		add_action( 'edited_product_cat', array( __CLASS__, 'save' ) );
	}

	/**
	 * Field labels and help texts.
	 *
	 * @return array
	 */
	private static function fields() {
		return array(
			'erpfw_markup_percent' => array(
				'label' => __( 'Exchange rate markup (%)', 'exchange-rate-pricing-for-woocommerce' ),
				'meta'  => Pricer::TERM_MARKUP_PERCENT,
			),
			'erpfw_markup_fixed'   => array(
				/* translators: %s: unit such as Toman. */
				'label' => sprintf( __( 'Exchange rate fixed markup (%s)', 'exchange-rate-pricing-for-woocommerce' ), Settings::unit_label() ),
				'meta'  => Pricer::TERM_MARKUP_FIXED,
			),
		);
	}

	/**
	 * Value shown in an input.
	 *
	 * @param string $name    Field name.
	 * @param int    $term_id Term id.
	 * @return string
	 */
	private static function display_value( $name, $term_id ) {
		$fields = self::fields();
		$value  = $term_id ? (string) get_term_meta( $term_id, $fields[ $name ]['meta'], true ) : '';

		if ( '' === $value ) {
			return '';
		}

		return wc_format_localized_decimal( 'erpfw_markup_fixed' === $name ? Settings::to_display( $value ) : $value );
	}

	/**
	 * Fields on the "Add new category" form.
	 */
	public static function add_fields() {
		foreach ( self::fields() as $name => $field ) {
			?>
			<div class="form-field term-<?php echo esc_attr( $name ); ?>-wrap">
				<label for="<?php echo esc_attr( $name ); ?>"><?php echo esc_html( $field['label'] ); ?></label>
				<input type="text" inputmode="decimal" name="<?php echo esc_attr( $name ); ?>" id="<?php echo esc_attr( $name ); ?>" value="" />
				<p class="description"><?php esc_html_e( 'Leave empty to use the parent category or store markup.', 'exchange-rate-pricing-for-woocommerce' ); ?></p>
			</div>
			<?php
		}
	}

	/**
	 * Fields on the "Edit category" form.
	 *
	 * @param WP_Term $term Category.
	 */
	public static function edit_fields( $term ) {
		foreach ( self::fields() as $name => $field ) {
			?>
			<tr class="form-field term-<?php echo esc_attr( $name ); ?>-wrap">
				<th scope="row"><label for="<?php echo esc_attr( $name ); ?>"><?php echo esc_html( $field['label'] ); ?></label></th>
				<td>
					<input type="text" inputmode="decimal" name="<?php echo esc_attr( $name ); ?>" id="<?php echo esc_attr( $name ); ?>" value="<?php echo esc_attr( self::display_value( $name, $term->term_id ) ); ?>" />
					<p class="description"><?php esc_html_e( 'Leave empty to use the parent category or store markup.', 'exchange-rate-pricing-for-woocommerce' ); ?></p>
				</td>
			</tr>
			<?php
		}
	}

	/**
	 * Save the markup and recalculate prices when it changed.
	 *
	 * @param int $term_id Term id.
	 */
	public static function save( $term_id ) {
		// phpcs:disable WordPress.Security.NonceVerification.Missing -- WordPress verified the term form nonce before these hooks.
		if ( ! isset( $_POST['erpfw_markup_percent'] ) && ! isset( $_POST['erpfw_markup_fixed'] ) ) {
			return;
		}

		if ( ! current_user_can( 'edit_term', $term_id ) ) {
			return;
		}

		$changed = false;

		foreach ( self::fields() as $name => $field ) {
			if ( ! isset( $_POST[ $name ] ) ) {
				continue;
			}

			$value = Format::parse_float( sanitize_text_field( wp_unslash( $_POST[ $name ] ) ) );

			if ( null === $value ) {
				$value = '';
			} else {
				$value = wc_format_decimal( 'erpfw_markup_fixed' === $name ? Settings::to_store( $value ) : $value );
			}

			$old = (string) get_term_meta( $term_id, $field['meta'], true );

			if ( $old === $value ) {
				continue;
			}

			$changed = true;

			if ( '' === $value ) {
				delete_term_meta( $term_id, $field['meta'] );
			} else {
				update_term_meta( $term_id, $field['meta'], $value );
			}
		}
		// phpcs:enable

		if ( $changed ) {
			Recalculator::schedule( 'category' );
		}
	}
}
