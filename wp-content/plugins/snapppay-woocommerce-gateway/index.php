<?php
/*
Plugin Name: SnappPay Woocommerce Gateway
Version: 1.4.2
Author: Snapp! Pay
Author URI: https://snapppay.ir/
Update URI: false
Description:  افزونه درگاه پرداخت اسنپ پی ووکامرس
Requires at least: 5.0
Requires PHP: 7.4
WC requires at least: 7.1
WC tested up to: 9.0.2
*/

if (!function_exists('add_filter')) {
	header('Status: 403 Forbidden');
	header('HTTP/1.1 403 Forbidden');
	exit();
}

if (!defined('SNAPP_PAY_PLUGIN')) {
	define('SNAPP_PAY_PLUGIN', plugin_basename(__FILE__));
}

if (!defined('SNAPP_PAY_PLUGIN_FILE')) {
	define('SNAPP_PAY_PLUGIN_FILE', __FILE__);
}

add_action('before_woocommerce_init', 'declare_wc_compatibility');
/**
 * Declare compatibility with WooCommerce features.
 *
 * @return void
 */
function declare_wc_compatibility()
{

	// Declare HPOS compatibility.
	if (class_exists(\Automattic\WooCommerce\Utilities\FeaturesUtil::class)) {
		\Automattic\WooCommerce\Utilities\FeaturesUtil::declare_compatibility('custom_order_tables', __FILE__, true);
	}
}

// remove update plugin notif.
function snapppay_remove_update_notifications($value)
{
	if (isset($value) && is_object($value)) {
		unset($value->response[SNAPP_PAY_PLUGIN]);
	}

	return $value;
}
add_filter('site_transient_update_plugins', 'snapppay_remove_update_notifications');


include_once("functions.php");
include_once("hooks.php");
include_once("class-snapppay-api.php");
include_once("class-wc-gateway-snapppay.php");
