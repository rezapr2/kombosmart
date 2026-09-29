<?php
/**
 * Exchange rate storage and history.
 *
 * @package ExchangeRatePricing
 */

namespace ExchangeRatePricing;

use WP_Error;

defined( 'ABSPATH' ) || exit;

/**
 * Current rates live in an option; every change is logged in a history table.
 *
 * A rate is how many store currency units one foreign unit is worth,
 * e.g. 1 USD = 1,000,000 IRR (shown to the user as 100,000 Toman).
 */
final class Rates {

	const OPTION = 'erpfw_rates';

	/**
	 * History table name.
	 *
	 * @return string
	 */
	public static function table() {
		global $wpdb;

		return $wpdb->prefix . 'erpfw_rate_history';
	}

	/**
	 * Current rate entry for a currency.
	 *
	 * Returns null when no rate is set, or when the store currency changed since
	 * the rate was saved (the old rate would be off by the currency difference).
	 *
	 * @param string $currency Currency code; defaults to the base currency.
	 * @return array|null { rate, updated_at, source, store_currency, user_id }
	 */
	public static function get( $currency = '' ) {
		$currency = $currency ? $currency : Settings::base_currency();
		$rates    = get_option( self::OPTION, array() );

		if ( ! is_array( $rates ) || empty( $rates[ $currency ]['rate'] ) ) {
			return null;
		}

		$entry = $rates[ $currency ];

		if ( ! isset( $entry['store_currency'] ) || get_woocommerce_currency() !== $entry['store_currency'] ) {
			return null;
		}

		return $entry;
	}

	/**
	 * Current rate as a number.
	 *
	 * @param string $currency Currency code; defaults to the base currency.
	 * @return float|null
	 */
	public static function get_rate( $currency = '' ) {
		$entry = self::get( $currency );

		return $entry ? (float) $entry['rate'] : null;
	}

	/**
	 * Save a new rate.
	 *
	 * @param string $currency Currency code.
	 * @param float  $rate     Rate in store currency units.
	 * @param string $source   Provider id, e.g. "manual".
	 * @param int    $user_id  User who made the change, 0 for automated sources.
	 * @return bool|WP_Error True when the rate changed, false when it was the same.
	 */
	public static function set( $currency, $rate, $source = 'manual', $user_id = 0 ) {
		global $wpdb;

		$currency = strtoupper( sanitize_key( $currency ) );
		$rate     = (float) $rate;

		if ( '' === $currency ) {
			return new WP_Error( 'erpfw_invalid_currency', __( 'Invalid currency.', 'exchange-rate-pricing-for-woocommerce' ) );
		}

		if ( ! is_finite( $rate ) || $rate <= 0 ) {
			return new WP_Error( 'erpfw_invalid_rate', __( 'The exchange rate must be a number greater than zero.', 'exchange-rate-pricing-for-woocommerce' ) );
		}

		$rates    = get_option( self::OPTION, array() );
		$rates    = is_array( $rates ) ? $rates : array();
		$previous = self::get( $currency );
		$old_rate = $previous ? (float) $previous['rate'] : null;
		$changed  = null === $old_rate || abs( $old_rate - $rate ) > 0.000001;

		$rates[ $currency ] = array(
			'rate'           => $rate,
			'updated_at'     => time(),
			'source'         => sanitize_key( $source ),
			'store_currency' => get_woocommerce_currency(),
			'user_id'        => (int) $user_id,
		);

		update_option( self::OPTION, $rates );

		if ( ! $changed ) {
			return false;
		}

		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery -- Custom history table.
		$wpdb->insert(
			self::table(),
			array(
				'currency'       => $currency,
				'store_currency' => get_woocommerce_currency(),
				'rate'           => $rate,
				'previous_rate'  => $old_rate,
				'source'         => sanitize_key( $source ),
				'user_id'        => (int) $user_id,
				'created_at'     => current_time( 'mysql', true ),
			),
			array( '%s', '%s', '%f', '%f', '%s', '%d', '%s' )
		);

		/**
		 * Fires after an exchange rate changed.
		 *
		 * @param string     $currency Currency code.
		 * @param float      $rate     New rate in store currency units.
		 * @param float|null $old_rate Previous rate, null if none.
		 * @param string     $source   Provider id.
		 */
		do_action( 'erpfw_rate_updated', $currency, $rate, $old_rate, $source );

		return true;
	}

	/**
	 * Recent rate changes, newest first.
	 *
	 * @param string $currency Currency code; defaults to the base currency.
	 * @param int    $limit    Number of rows.
	 * @return array
	 */
	public static function history( $currency = '', $limit = 20 ) {
		global $wpdb;

		$currency = $currency ? $currency : Settings::base_currency();
		$table    = self::table();

		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Custom history table, admin only.
		$rows = $wpdb->get_results(
			$wpdb->prepare(
				'SELECT * FROM %i WHERE currency = %s ORDER BY id DESC LIMIT %d',
				$table,
				$currency,
				max( 1, (int) $limit )
			),
			ARRAY_A
		);

		return is_array( $rows ) ? $rows : array();
	}

	/**
	 * Whether the current rate is older than the configured number of days.
	 *
	 * @return bool
	 */
	public static function is_stale() {
		$days  = (int) Settings::get( 'stale_days' );
		$entry = self::get();

		if ( $days <= 0 || ! $entry ) {
			return false;
		}

		return ( time() - (int) $entry['updated_at'] ) > $days * DAY_IN_SECONDS;
	}
}
