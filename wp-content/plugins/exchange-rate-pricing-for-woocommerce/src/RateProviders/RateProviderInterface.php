<?php
/**
 * Rate provider contract.
 *
 * @package ExchangeRatePricing
 */

namespace ExchangeRatePricing\RateProviders;

defined( 'ABSPATH' ) || exit;

/**
 * A source of exchange rates. Register extra providers with the `erpfw_rate_providers` filter.
 */
interface RateProviderInterface {

	/**
	 * Unique id, e.g. "manual".
	 *
	 * @return string
	 */
	public function get_id();

	/**
	 * Human readable, translated name.
	 *
	 * @return string
	 */
	public function get_label();

	/**
	 * Whether rates are fetched automatically on a schedule.
	 *
	 * @return bool
	 */
	public function is_automatic();

	/**
	 * Fetch current rates.
	 *
	 * @param string[] $currencies     Foreign currency codes.
	 * @param string   $store_currency Store currency code.
	 * @return array|\WP_Error Rates keyed by currency code, in store currency units.
	 */
	public function fetch_rates( array $currencies, $store_currency );
}
