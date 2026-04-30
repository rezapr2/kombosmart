<?php
/**
 * My Account Dashboard — minimal; greeting is rendered by my-account.php.
 *
 * @see     https://woocommerce.com/document/template-structure/
 * @package WooCommerce\Templates
 * @version 4.4.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

do_action( 'woocommerce_account_dashboard' );
do_action( 'woocommerce_before_my_account' ); // deprecated but kept for plugin compat
do_action( 'woocommerce_after_my_account' );  // deprecated but kept for plugin compat
