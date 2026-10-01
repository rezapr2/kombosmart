<?php
/**
 * Public helper functions for themes and other plugins.
 *
 * @package RateMint
 */

defined( 'ABSPATH' ) || exit;

if ( ! function_exists( 'erpfw_get_rate' ) ) {
	/**
	 * Current exchange rate in store currency units per foreign unit.
	 *
	 * @param string $currency Currency code; defaults to the base currency.
	 * @return float|null Null when no rate is set.
	 */
	function erpfw_get_rate( $currency = '' ) {
		return \RateMint\Rates::get_rate( $currency );
	}
}

if ( ! function_exists( 'erpfw_convert_to_store_price' ) ) {
	/**
	 * Convert a base currency amount into the store currency.
	 *
	 * Useful for add-ons or extra fees priced in the base currency.
	 *
	 * @param float $amount Amount in the base currency.
	 * @param array $args   Optional. Calculator overrides, e.g. markup_percent, rounding_step.
	 *                      By default the global markup and rounding settings are applied.
	 * @return float|null Store currency amount, or null when no rate is set.
	 */
	function erpfw_convert_to_store_price( $amount, array $args = array() ) {
		$rate = \RateMint\Rates::get_rate();

		if ( ! $rate ) {
			return null;
		}

		$args = array_merge(
			array(
				'rate'           => $rate,
				'markup_percent' => (float) \RateMint\Settings::get( 'markup_percent' ),
				'markup_fixed'   => (float) \RateMint\Settings::get( 'markup_fixed' ),
				'rounding_step'  => (float) \RateMint\Settings::get( 'rounding_step' ),
				'rounding_mode'  => (string) \RateMint\Settings::get( 'rounding_mode' ),
				'decimals'       => wc_get_price_decimals(),
			),
			$args
		);

		return \RateMint\Calculator::convert( (float) $amount, $args );
	}
}
