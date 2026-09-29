<?php
/**
 * Admin wiring: assets, notices and recalculation endpoints.
 *
 * @package ExchangeRatePricing
 */

namespace ExchangeRatePricing\Admin;

use ExchangeRatePricing\Pricer;
use ExchangeRatePricing\Rates;
use ExchangeRatePricing\Recalculator;
use ExchangeRatePricing\Settings;

defined( 'ABSPATH' ) || exit;

/**
 * Loads the admin components.
 */
final class Admin {

	/**
	 * Register hooks.
	 */
	public static function init() {
		add_filter( 'woocommerce_get_settings_pages', array( __CLASS__, 'add_settings_page' ) );
		add_action( 'admin_enqueue_scripts', array( __CLASS__, 'enqueue' ) );
		add_action( 'admin_notices', array( __CLASS__, 'notices' ) );
		add_action( 'wp_ajax_erpfw_recalculate', array( __CLASS__, 'ajax_recalculate' ) );
		add_action( 'wp_ajax_erpfw_recalc_status', array( __CLASS__, 'ajax_recalc_status' ) );

		ProductFields::init();
		CategoryFields::init();
		ProductList::init();
	}

	/**
	 * Register the WooCommerce settings tab.
	 *
	 * @param array $pages Settings pages.
	 * @return array
	 */
	public static function add_settings_page( $pages ) {
		$pages[] = new SettingsPage();

		return $pages;
	}

	/**
	 * Whether the current screen is the plugin's settings tab.
	 *
	 * @return bool
	 */
	public static function is_settings_screen() {
		$screen = function_exists( 'get_current_screen' ) ? get_current_screen() : null;

		// phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Reading the current tab only.
		$tab = isset( $_GET['tab'] ) ? sanitize_key( wp_unslash( $_GET['tab'] ) ) : '';

		return $screen && 'woocommerce_page_wc-settings' === $screen->id && 'erpfw' === $tab;
	}

	/**
	 * Enqueue assets on product and settings screens.
	 */
	public static function enqueue() {
		$screen = get_current_screen();
		$id     = $screen ? $screen->id : '';

		if ( ! in_array( $id, array( 'product', 'edit-product' ), true ) && ! self::is_settings_screen() ) {
			return;
		}

		wp_enqueue_style( 'erpfw-admin', ERPFW_URL . 'assets/css/admin.css', array(), ERPFW_VERSION );
		wp_enqueue_script( 'erpfw-admin', ERPFW_URL . 'assets/js/admin.js', array( 'jquery' ), ERPFW_VERSION, true );
		wp_localize_script(
			'erpfw-admin',
			'erpfwAdmin',
			array(
				'ajaxUrl'     => admin_url( 'admin-ajax.php' ),
				'nonce'       => wp_create_nonce( 'erpfw_admin' ),
				'defaultMode' => Pricer::effective_mode( Pricer::MODE_DEFAULT ),
				'decimalSep'  => wc_get_price_decimal_separator(),
				'i18n'        => array(
					'calculating' => __( 'Calculating…', 'exchange-rate-pricing-for-woocommerce' ),
					'failed'      => __( 'Could not calculate the price.', 'exchange-rate-pricing-for-woocommerce' ),
					'locked'      => __( 'Calculated from the exchange rate. Edit the base currency price instead.', 'exchange-rate-pricing-for-woocommerce' ),
				),
			)
		);
	}

	/**
	 * Missing or outdated rate notices on relevant screens.
	 */
	public static function notices() {
		if ( ! current_user_can( Settings::capability() ) ) {
			return;
		}

		$screen = get_current_screen();

		if ( ! $screen || ! in_array( $screen->id, array( 'dashboard', 'edit-product', 'product', 'woocommerce_page_wc-settings' ), true ) ) {
			return;
		}

		// phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Display-only result flag of the rate form.
		$result = isset( $_GET['erpfw_rate'] ) ? sanitize_key( wp_unslash( $_GET['erpfw_rate'] ) ) : '';

		if ( 'updated' === $result ) {
			self::notice( 'success', __( 'The exchange rate was saved. Prices are being recalculated in the background.', 'exchange-rate-pricing-for-woocommerce' ) );
		} elseif ( 'error' === $result ) {
			self::notice( 'error', __( 'The exchange rate could not be saved. Enter a number greater than zero.', 'exchange-rate-pricing-for-woocommerce' ) );
		}

		$entry = Rates::get();
		$link  = self::is_settings_screen() ? '' : sprintf( ' <a href="%s">%s</a>', esc_url( Settings::page_url() ), esc_html__( 'Update the rate', 'exchange-rate-pricing-for-woocommerce' ) );

		if ( ! $entry ) {
			if ( self::is_settings_screen() || Recalculator::count_products() > 0 ) {
				self::notice(
					'warning',
					sprintf(
						/* translators: %s: currency code such as USD. */
						esc_html__( 'Exchange Rate Pricing: no %s exchange rate is set yet, so exchange-rate priced products keep their current prices.', 'exchange-rate-pricing-for-woocommerce' ),
						esc_html( Settings::base_currency() )
					) . $link,
					false
				);
			}
		} elseif ( Rates::is_stale() ) {
			self::notice(
				'warning',
				sprintf(
					/* translators: 1: currency code such as USD, 2: time span such as "3 days". */
					esc_html__( 'Exchange Rate Pricing: the %1$s rate was last updated %2$s ago.', 'exchange-rate-pricing-for-woocommerce' ),
					esc_html( Settings::base_currency() ),
					esc_html( human_time_diff( (int) $entry['updated_at'] ) )
				) . $link,
				false
			);
		}
	}

	/**
	 * Print an admin notice.
	 *
	 * @param string $type    success, warning or error.
	 * @param string $message Message.
	 * @param bool   $escape  Whether the message still needs escaping.
	 */
	private static function notice( $type, $message, $escape = true ) {
		printf(
			'<div class="notice notice-%1$s"><p>%2$s</p></div>',
			esc_attr( $type ),
			$escape ? esc_html( $message ) : wp_kses_post( $message )
		);
	}

	/**
	 * Start a recalculation from the settings page.
	 */
	public static function ajax_recalculate() {
		check_ajax_referer( 'erpfw_admin', 'nonce' );

		if ( ! current_user_can( Settings::capability() ) ) {
			wp_send_json_error( null, 403 );
		}

		wp_send_json_success( self::state_payload( Recalculator::schedule( 'manual' ) ) );
	}

	/**
	 * Report progress, processing a batch while the settings page is open.
	 */
	public static function ajax_recalc_status() {
		check_ajax_referer( 'erpfw_admin', 'nonce' );

		if ( ! current_user_can( Settings::capability() ) ) {
			wp_send_json_error( null, 403 );
		}

		$state = Recalculator::state();

		if ( 'running' === $state['status'] ) {
			$state = Recalculator::process_batch();
		}

		wp_send_json_success( self::state_payload( $state ) );
	}

	/**
	 * Recalculation state for JavaScript.
	 *
	 * @param array $state Run state.
	 * @return array
	 */
	private static function state_payload( array $state ) {
		$total = max( 1, (int) $state['total'] );

		return array(
			'status'  => $state['status'],
			'percent' => 'done' === $state['status'] ? 100 : (int) floor( 100 * (int) $state['processed'] / $total ),
			'summary' => self::recalc_summary( $state ),
		);
	}

	/**
	 * One-line description of the recalculation state.
	 *
	 * @param array $state Run state.
	 * @return string
	 */
	public static function recalc_summary( array $state ) {
		switch ( $state['status'] ) {
			case 'running':
				return sprintf(
					/* translators: 1: products checked so far, 2: total products. */
					__( 'Recalculating prices… %1$s of %2$s products checked.', 'exchange-rate-pricing-for-woocommerce' ),
					number_format_i18n( (int) $state['processed'] ),
					number_format_i18n( (int) $state['total'] )
				);

			case 'done':
				return sprintf(
					/* translators: 1: time span such as "5 minutes", 2: products checked, 3: products updated, 4: products without a base price. */
					__( 'Last run %1$s ago. Checked: %2$s · Updated: %3$s · Missing a base price: %4$s', 'exchange-rate-pricing-for-woocommerce' ),
					human_time_diff( (int) $state['finished_at'] ),
					number_format_i18n( (int) $state['processed'] ),
					number_format_i18n( (int) $state['updated'] ),
					number_format_i18n( (int) $state['missing'] )
				);

			case 'no_rate':
				return __( 'Waiting for an exchange rate.', 'exchange-rate-pricing-for-woocommerce' );

			default:
				return __( 'Prices have not been recalculated yet.', 'exchange-rate-pricing-for-woocommerce' );
		}
	}
}
