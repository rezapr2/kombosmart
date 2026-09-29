<?php
/**
 * Plugin bootstrap.
 *
 * @package ExchangeRatePricing
 */

namespace ExchangeRatePricing;

defined( 'ABSPATH' ) || exit;

/**
 * Wires all components together.
 */
final class Plugin {

	/**
	 * Singleton instance.
	 *
	 * @var Plugin|null
	 */
	private static $instance = null;

	/**
	 * Get the instance, booting the plugin on first call.
	 *
	 * @return Plugin
	 */
	public static function instance() {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}

		return self::$instance;
	}

	/**
	 * Register hooks.
	 */
	private function __construct() {
		if ( ! class_exists( 'WooCommerce' ) ) {
			add_action( 'admin_notices', array( $this, 'missing_woocommerce_notice' ) );
			return;
		}

		add_action( 'init', array( $this, 'load_textdomain' ) );
		add_action( 'admin_init', array( Installer::class, 'maybe_upgrade' ) );
		add_action( 'erpfw_rate_updated', array( $this, 'on_rate_updated' ) );
		add_action( 'update_option_' . Settings::OPTION, array( $this, 'on_settings_updated' ), 10, 2 );
		add_action( 'add_option_' . Settings::OPTION, array( $this, 'on_settings_added' ), 10, 2 );
		add_filter( 'plugin_action_links_' . plugin_basename( ERPFW_FILE ), array( $this, 'action_links' ) );

		Recalculator::init();
		Orders::init();
		CsvImportExport::init();
		Admin\RateWidget::init();

		if ( is_admin() ) {
			Admin\Admin::init();
		}

		if ( defined( 'WP_CLI' ) && WP_CLI ) {
			\WP_CLI::add_command( 'erpfw', CLI::class );
		}
	}

	/**
	 * Load bundled translations. Translations from translate.wordpress.org take priority.
	 */
	public function load_textdomain() {
		load_plugin_textdomain( 'exchange-rate-pricing-for-woocommerce', false, dirname( plugin_basename( ERPFW_FILE ) ) . '/languages' );
	}

	/**
	 * Recalculate when the base currency rate changes.
	 *
	 * @param string $currency Currency whose rate changed.
	 */
	public function on_rate_updated( $currency ) {
		if ( Settings::base_currency() === $currency ) {
			Recalculator::schedule( 'rate' );
		}
	}

	/**
	 * Recalculate when a price-affecting setting changes.
	 *
	 * @param mixed $old_value Previous settings.
	 * @param mixed $new_value New settings.
	 */
	public function on_settings_updated( $old_value, $new_value ) {
		$old = array_merge( Settings::defaults(), is_array( $old_value ) ? $old_value : array() );
		$new = array_merge( Settings::defaults(), is_array( $new_value ) ? $new_value : array() );

		foreach ( Settings::PRICE_AFFECTING_KEYS as $key ) {
			if ( (string) $old[ $key ] !== (string) $new[ $key ] ) {
				Recalculator::schedule( 'settings' );
				return;
			}
		}
	}

	/**
	 * First save of the settings.
	 *
	 * @param string $option Option name.
	 * @param mixed  $value  Settings.
	 */
	public function on_settings_added( $option, $value ) {
		$this->on_settings_updated( array(), $value );
	}

	/**
	 * Add a Settings link on the Plugins screen.
	 *
	 * @param array $links Action links.
	 * @return array
	 */
	public function action_links( $links ) {
		array_unshift(
			$links,
			sprintf( '<a href="%s">%s</a>', esc_url( Settings::page_url() ), esc_html__( 'Settings', 'exchange-rate-pricing-for-woocommerce' ) )
		);

		return $links;
	}

	/**
	 * Notice shown when WooCommerce is not active.
	 */
	public function missing_woocommerce_notice() {
		if ( ! current_user_can( 'activate_plugins' ) ) {
			return;
		}

		printf(
			'<div class="notice notice-error"><p>%s</p></div>',
			esc_html__( 'Exchange Rate Pricing for WooCommerce needs WooCommerce to be installed and active.', 'exchange-rate-pricing-for-woocommerce' )
		);
	}
}
