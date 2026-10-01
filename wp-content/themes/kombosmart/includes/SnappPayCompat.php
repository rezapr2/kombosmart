<?php
namespace TanilChoob\Theme;

defined( 'ABSPATH' ) || exit;

/**
 * Non-invasive compatibility fix for the vendor SnappPay gateway plugin,
 * applied via a filter instead of editing the plugin's files directly
 * (which would be overwritten on the next plugin update).
 */
class SnappPayCompat {

	public function __construct() {
		add_filter( 'option_woocommerce_WC_Gateway_SnappPay_settings', [ $this, 'fix_message_option_typo' ] );
	}

	/**
	 * WC_Gateway_SnappPay::set_message() reads settings keys named
	 * "{status}_massage" (a typo for "_message"), so the admin-configured
	 * success/failed/cancelled messages are never found there and it
	 * silently falls back to the raw technical error string instead.
	 * Mirror the correctly-named values onto the misspelled keys it reads.
	 */
	public function fix_message_option_typo( $settings ) {
		if ( ! is_array( $settings ) ) {
			return $settings;
		}

		foreach ( [ 'success', 'failed', 'cancelled' ] as $status ) {
			if ( ! empty( $settings[ $status . '_message' ] ) ) {
				$settings[ $status . '_massage' ] = $settings[ $status . '_message' ];
			}
		}

		return $settings;
	}
}
