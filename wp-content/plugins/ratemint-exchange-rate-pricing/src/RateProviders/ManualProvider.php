<?php
/**
 * Manual rate provider.
 *
 * @package RateMint
 */

namespace RateMint\RateProviders;

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
		return __( 'Manual entry', 'ratemint-exchange-rate-pricing' );
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
		return new WP_Error( 'erpfw_manual_provider', __( 'Manual rates are entered by hand.', 'ratemint-exchange-rate-pricing' ) );
	}
}
