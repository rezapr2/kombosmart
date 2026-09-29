<?php
/**
 * Manual rate provider.
 *
 * @package ExchangeRatePricing
 */

namespace ExchangeRatePricing\RateProviders;

use WP_Error;

defined( 'ABSPATH' ) || exit;

/**
 * Rates are typed in by a shop manager.
 */
final class ManualProvider implements RateProviderInterface {

	/**
	 * {@inheritDoc}
	 */
	public function get_id() {
		return 'manual';
	}

	/**
	 * {@inheritDoc}
	 */
	public function get_label() {
		return __( 'Manual entry', 'exchange-rate-pricing-for-woocommerce' );
	}

	/**
	 * {@inheritDoc}
	 */
	public function is_automatic() {
		return false;
	}

	/**
	 * {@inheritDoc}
	 */
	public function fetch_rates( array $currencies, $store_currency ) {
		return new WP_Error( 'erpfw_manual_provider', __( 'Manual rates are entered by hand.', 'exchange-rate-pricing-for-woocommerce' ) );
	}
}
