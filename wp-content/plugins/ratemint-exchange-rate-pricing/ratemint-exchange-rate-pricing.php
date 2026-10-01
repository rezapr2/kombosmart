<?php
/**
 * Plugin Name:          RateMint – Exchange Rate Pricing for WooCommerce
 * Description:          Price products in a foreign currency such as USD and sell them in your store currency using an exchange rate you control, with markup, rounding and sale rules.
 * Version:              1.0.0
 * Requires at least:    6.5
 * Requires PHP:         7.4
 * Requires Plugins:     woocommerce
 * Author:               Reza Rajabi
 * License:              GPL-2.0-or-later
 * License URI:          https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:          ratemint-exchange-rate-pricing
 * Domain Path:          /languages
 * WC requires at least: 8.0
 * WC tested up to:      10.4
 *
 * @package RateMint
 */

defined( 'ABSPATH' ) || exit;

define( 'ERPFW_VERSION', '1.0.0' );
define( 'ERPFW_FILE', __FILE__ );
define( 'ERPFW_PATH', plugin_dir_path( __FILE__ ) );
define( 'ERPFW_URL', plugin_dir_url( __FILE__ ) );

require_once ERPFW_PATH . 'src/Autoloader.php';
\RateMint\Autoloader::register();
require_once ERPFW_PATH . 'src/functions.php';

register_activation_hook( __FILE__, array( '\RateMint\Installer', 'activate' ) );
register_deactivation_hook( __FILE__, array( '\RateMint\Installer', 'deactivate' ) );

add_action(
	'before_woocommerce_init',
	static function () {
		if ( class_exists( '\Automattic\WooCommerce\Utilities\FeaturesUtil' ) ) {
			\Automattic\WooCommerce\Utilities\FeaturesUtil::declare_compatibility( 'custom_order_tables', __FILE__, true );
			\Automattic\WooCommerce\Utilities\FeaturesUtil::declare_compatibility( 'cart_checkout_blocks', __FILE__, true );
		}
	}
);

add_action( 'plugins_loaded', array( '\RateMint\Plugin', 'instance' ) );
