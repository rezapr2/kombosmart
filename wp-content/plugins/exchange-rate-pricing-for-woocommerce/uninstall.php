<?php
/**
 * Uninstall: remove plugin data only when the "Remove data" setting is on.
 *
 * Store prices (WooCommerce's own price fields) and order records are always kept.
 *
 * @package ExchangeRatePricing
 */

defined( 'WP_UNINSTALL_PLUGIN' ) || exit;

/**
 * Delete this site's plugin data if the site opted in.
 */
function erpfw_uninstall_site() {
	global $wpdb;

	$settings = get_option( 'erpfw_settings', array() );

	if ( ! is_array( $settings ) || empty( $settings['delete_data'] ) || 'yes' !== $settings['delete_data'] ) {
		return;
	}

	foreach ( array( 'erpfw_settings', 'erpfw_rates', 'erpfw_recalc_state', 'erpfw_recalc_lock', 'erpfw_db_version' ) as $option ) {
		delete_option( $option );
	}

	// phpcs:disable WordPress.DB.DirectDatabaseQuery -- One-time cleanup of plugin data.
	$wpdb->query( $wpdb->prepare( 'DROP TABLE IF EXISTS %i', $wpdb->prefix . 'erpfw_rate_history' ) );
	$wpdb->query( $wpdb->prepare( "DELETE FROM {$wpdb->postmeta} WHERE meta_key LIKE %s", $wpdb->esc_like( '_erpfw_' ) . '%' ) );
	$wpdb->query( $wpdb->prepare( "DELETE FROM {$wpdb->termmeta} WHERE meta_key LIKE %s", $wpdb->esc_like( 'erpfw_' ) . '%' ) );
	// phpcs:enable

	if ( function_exists( 'as_unschedule_all_actions' ) ) {
		as_unschedule_all_actions( 'erpfw_recalculate_batch' );
	}

	wp_cache_flush();
}

if ( is_multisite() ) {
	foreach ( get_sites( array( 'fields' => 'ids' ) ) as $erpfw_site_id ) {
		switch_to_blog( $erpfw_site_id );
		erpfw_uninstall_site();
		restore_current_blog();
	}
} else {
	erpfw_uninstall_site();
}
