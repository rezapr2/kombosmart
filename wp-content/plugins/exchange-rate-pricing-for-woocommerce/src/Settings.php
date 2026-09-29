<?php
/**
 * Plugin settings access.
 *
 * @package ExchangeRatePricing
 */

namespace ExchangeRatePricing;

defined( 'ABSPATH' ) || exit;

/**
 * Reads the plugin settings and converts between the entry unit and the store currency.
 *
 * Money amounts (rate, fixed markup, rounding step) are always stored in the store
 * currency. Stores selling in Iranian Rial can choose to type and see them in Toman.
 */
final class Settings {

	const OPTION = 'erpfw_settings';

	/**
	 * Settings that change calculated prices. Changing any of them triggers a recalculation.
	 */
	const PRICE_AFFECTING_KEYS = array(
		'base_currency',
		'default_mode',
		'markup_percent',
		'markup_fixed',
		'category_conflict',
		'rounding_step',
		'rounding_mode',
		'sale_mode',
	);

	/**
	 * Default settings.
	 *
	 * @return array
	 */
	public static function defaults() {
		return array(
			'base_currency'     => 'USD',
			'rate_provider'     => 'manual',
			'amount_unit'       => 'toman',
			'stale_days'        => 3,
			'show_admin_bar'    => 'yes',
			'default_mode'      => 'manual',
			'sale_mode'         => 'both',
			'markup_percent'    => '0',
			'markup_fixed'      => '0',
			'category_conflict' => 'highest',
			'rounding_step'     => '0',
			'rounding_mode'     => 'up',
			'batch_size'        => 50,
			'delete_data'       => 'no',
		);
	}

	/**
	 * All settings merged with defaults.
	 *
	 * @return array
	 */
	public static function all() {
		$saved = get_option( self::OPTION, array() );

		return array_merge( self::defaults(), is_array( $saved ) ? $saved : array() );
	}

	/**
	 * A single setting.
	 *
	 * @param string $key Setting key.
	 * @return mixed
	 */
	public static function get( $key ) {
		$all = self::all();

		return isset( $all[ $key ] ) ? $all[ $key ] : null;
	}

	/**
	 * The foreign currency products are priced in.
	 *
	 * @return string
	 */
	public static function base_currency() {
		$currency = (string) self::get( 'base_currency' );

		return '' !== $currency ? $currency : 'USD';
	}

	/**
	 * Whether the store currency supports entering amounts in Toman.
	 *
	 * @return bool
	 */
	public static function supports_toman() {
		return 'IRR' === get_woocommerce_currency();
	}

	/**
	 * How many store currency units one entry unit is worth.
	 *
	 * @param string|null $unit Unit to use; defaults to the saved one.
	 * @return int
	 */
	public static function unit_factor( $unit = null ) {
		$unit = null === $unit ? self::get( 'amount_unit' ) : $unit;

		return ( 'toman' === $unit && self::supports_toman() ) ? 10 : 1;
	}

	/**
	 * Convert an amount typed by the user into the store currency.
	 *
	 * @param float|string $amount Amount in the entry unit.
	 * @return float
	 */
	public static function to_store( $amount ) {
		return (float) $amount * self::unit_factor();
	}

	/**
	 * Convert a store currency amount into the entry unit for display.
	 *
	 * @param float|string $amount Amount in the store currency.
	 * @return float
	 */
	public static function to_display( $amount ) {
		return (float) $amount / self::unit_factor();
	}

	/**
	 * Label of the entry unit, e.g. "Toman" or the store currency symbol.
	 *
	 * @return string
	 */
	public static function unit_label() {
		if ( 10 === self::unit_factor() ) {
			return __( 'Toman', 'exchange-rate-pricing-for-woocommerce' );
		}

		return Format::currency_symbol( get_woocommerce_currency() );
	}

	/**
	 * Number of prices recalculated per background batch.
	 *
	 * @return int
	 */
	public static function batch_size() {
		$size = (int) self::get( 'batch_size' );

		return (int) apply_filters( 'erpfw_batch_size', max( 5, min( 500, $size ? $size : 50 ) ) );
	}

	/**
	 * Capability required to manage rates and settings.
	 *
	 * @return string
	 */
	public static function capability() {
		return (string) apply_filters( 'erpfw_manage_capability', 'manage_woocommerce' );
	}

	/**
	 * URL of the settings tab.
	 *
	 * @return string
	 */
	public static function page_url() {
		return admin_url( 'admin.php?page=wc-settings&tab=erpfw' );
	}
}
