<?php
/**
 * Quick rate update: dashboard widget and admin bar.
 *
 * @package RateMint
 */

namespace RateMint\Admin;

use RateMint\Format;
use RateMint\Rates;
use RateMint\Recalculator;
use RateMint\Settings;
use WP_Admin_Bar;
use WP_Error;

defined( 'ABSPATH' ) || exit;

/**
 * Lets shop managers update the rate in one step from the dashboard or the admin bar.
 */
final class RateWidget {

	/**
	 * Register hooks. Runs on the front end too, for the admin bar.
	 */
	public static function init() {
		add_action( 'wp_dashboard_setup', array( __CLASS__, 'register_widget' ) );
		add_action( 'admin_bar_menu', array( __CLASS__, 'admin_bar' ), 90 );
		add_action( 'admin_enqueue_scripts', array( __CLASS__, 'enqueue' ) );
		add_action( 'wp_enqueue_scripts', array( __CLASS__, 'enqueue' ) );
		add_action( 'wp_ajax_erpfw_update_rate', array( __CLASS__, 'ajax_update_rate' ) );
		add_action( 'admin_post_erpfw_update_rate', array( __CLASS__, 'post_update_rate' ) );
	}

	/**
	 * Whether the current user may change the rate.
	 *
	 * @return bool
	 */
	private static function can_manage() {
		return current_user_can( Settings::capability() );
	}

	/**
	 * Whether the admin bar item should show.
	 *
	 * @return bool
	 */
	private static function show_in_admin_bar() {
		return is_admin_bar_showing() && self::can_manage() && 'yes' === Settings::get( 'show_admin_bar' );
	}

	/**
	 * Enqueue the rate form assets where a form is shown.
	 */
	public static function enqueue() {
		$screen       = is_admin() && function_exists( 'get_current_screen' ) ? get_current_screen() : null;
		$on_dashboard = $screen && 'dashboard' === $screen->id && self::can_manage();

		if ( ! $on_dashboard && ! self::show_in_admin_bar() ) {
			return;
		}

		wp_enqueue_style( 'erpfw-rate', ERPFW_URL . 'assets/css/rate.css', array(), ERPFW_VERSION );
		wp_enqueue_script( 'erpfw-rate', ERPFW_URL . 'assets/js/rate.js', array(), ERPFW_VERSION, true );
		wp_localize_script(
			'erpfw-rate',
			'erpfwRate',
			array(
				'ajaxUrl' => admin_url( 'admin-ajax.php' ),
				'i18n'    => array(
					'saving' => __( 'Saving…', 'ratemint-exchange-rate-pricing' ),
					'error'  => __( 'The exchange rate could not be saved.', 'ratemint-exchange-rate-pricing' ),
				),
			)
		);
	}

	/**
	 * Register the dashboard widget.
	 */
	public static function register_widget() {
		if ( self::can_manage() ) {
			wp_add_dashboard_widget( 'erpfw_rate_widget', __( 'Exchange rate', 'ratemint-exchange-rate-pricing' ), array( __CLASS__, 'render_widget' ) );
		}
	}

	/**
	 * Short label, e.g. "USD 100,000".
	 *
	 * @return string
	 */
	private static function short_label() {
		$currency = Settings::base_currency();
		$entry    = Rates::get( $currency );

		if ( ! $entry ) {
			/* translators: %s: currency code such as USD. */
			return sprintf( __( '%s: no rate', 'ratemint-exchange-rate-pricing' ), $currency );
		}

		return sprintf( '%1$s %2$s', $currency, Format::number( Settings::to_display( $entry['rate'] ) ) );
	}

	/**
	 * "Updated … ago" text.
	 *
	 * @return string
	 */
	private static function updated_text() {
		$entry = Rates::get();

		if ( ! $entry ) {
			return __( 'No rate set yet.', 'ratemint-exchange-rate-pricing' );
		}

		/* translators: %s: time span such as "2 hours". */
		return sprintf( __( 'Updated %s ago.', 'ratemint-exchange-rate-pricing' ), human_time_diff( (int) $entry['updated_at'] ) );
	}

	/**
	 * Print the rate form. Works without JavaScript through admin-post.php.
	 *
	 * @param string $context Unique suffix for element ids.
	 */
	private static function render_form( $context ) {
		$currency = Settings::base_currency();
		$entry    = Rates::get( $currency );
		$value    = $entry ? wc_format_localized_decimal( Settings::to_display( $entry['rate'] ) ) : '';
		$input_id = 'erpfw-rate-' . $context;
		/* translators: %s: currency code such as USD. */
		$prefix = sprintf( __( '1 %s =', 'ratemint-exchange-rate-pricing' ), $currency );

		?>
		<form class="erpfw-rate-form" method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
			<input type="hidden" name="action" value="erpfw_update_rate" />
			<input type="hidden" name="currency" value="<?php echo esc_attr( $currency ); ?>" />
			<input type="hidden" name="erpfw_nonce" value="<?php echo esc_attr( wp_create_nonce( 'erpfw_update_rate' ) ); ?>" />
			<?php wp_referer_field(); ?>
			<label class="erpfw-rate-form__prefix" for="<?php echo esc_attr( $input_id ); ?>"><?php echo esc_html( $prefix ); ?></label>
			<input type="text" inputmode="decimal" name="rate" id="<?php echo esc_attr( $input_id ); ?>" value="<?php echo esc_attr( $value ); ?>" required />
			<span class="erpfw-rate-form__unit"><?php echo esc_html( Settings::unit_label() ); ?></span>
			<button type="submit" class="button button-primary"><?php esc_html_e( 'Update', 'ratemint-exchange-rate-pricing' ); ?></button>
			<span class="erpfw-rate-form__message" role="status" aria-live="polite"></span>
		</form>
		<?php
	}

	/**
	 * Dashboard widget content.
	 */
	public static function render_widget() {
		$entry = Rates::get();
		?>
		<div class="erpfw-rate-widget">
			<p class="erpfw-rate-current"><?php echo esc_html( $entry ? Format::rate( $entry['rate'] ) : self::short_label() ); ?></p>
			<p class="erpfw-rate-updated<?php echo Rates::is_stale() ? ' is-stale' : ''; ?>"><?php echo esc_html( self::updated_text() ); ?></p>
			<?php self::render_form( 'widget' ); ?>
			<p class="erpfw-rate-status"><?php echo esc_html( Admin::recalc_summary( Recalculator::state() ) ); ?></p>
			<p><a href="<?php echo esc_url( Settings::page_url() ); ?>"><?php esc_html_e( 'Exchange rate settings', 'ratemint-exchange-rate-pricing' ); ?></a></p>
		</div>
		<?php
	}

	/**
	 * Admin bar item with a quick update form.
	 *
	 * @param WP_Admin_Bar $admin_bar Admin bar.
	 */
	public static function admin_bar( $admin_bar ) {
		if ( ! self::show_in_admin_bar() ) {
			return;
		}

		$admin_bar->add_node(
			array(
				'id'    => 'erpfw-rate',
				'title' => '<span class="ab-icon" aria-hidden="true"></span><span class="ab-label erpfw-rate-label">' . esc_html( self::short_label() ) . '</span>',
				'href'  => Settings::page_url(),
				'meta'  => array(
					'class' => Rates::is_stale() || ! Rates::get() ? 'erpfw-stale' : '',
					'title' => self::updated_text(),
				),
			)
		);

		ob_start();
		self::render_form( 'bar' );

		$admin_bar->add_node(
			array(
				'parent' => 'erpfw-rate',
				'id'     => 'erpfw-rate-form',
				'title'  => (string) ob_get_clean(),
			)
		);
	}

	/**
	 * Validate and save a submitted rate.
	 *
	 * @param string $currency Submitted currency code.
	 * @param string $rate     Submitted rate in the entry unit.
	 * @return bool|WP_Error True if changed, false if unchanged.
	 */
	private static function save_rate( $currency, $rate ) {
		$currency = '' !== $currency ? strtoupper( $currency ) : Settings::base_currency();
		$value    = Format::parse_float( $rate );

		if ( ! array_key_exists( $currency, get_woocommerce_currencies() ) ) {
			return new WP_Error( 'erpfw_invalid_currency', __( 'Invalid currency.', 'ratemint-exchange-rate-pricing' ) );
		}

		if ( null === $value ) {
			return new WP_Error( 'erpfw_invalid_rate', __( 'The exchange rate must be a number greater than zero.', 'ratemint-exchange-rate-pricing' ) );
		}

		return Rates::set( $currency, Settings::to_store( $value ), 'manual', get_current_user_id() );
	}

	/**
	 * AJAX rate update.
	 */
	public static function ajax_update_rate() {
		check_ajax_referer( 'erpfw_update_rate', 'erpfw_nonce' );

		if ( ! self::can_manage() ) {
			wp_send_json_error( array( 'message' => __( 'You are not allowed to change the exchange rate.', 'ratemint-exchange-rate-pricing' ) ), 403 );
		}

		$result = self::save_rate(
			isset( $_POST['currency'] ) ? sanitize_key( wp_unslash( $_POST['currency'] ) ) : '',
			isset( $_POST['rate'] ) ? sanitize_text_field( wp_unslash( $_POST['rate'] ) ) : ''
		);

		if ( is_wp_error( $result ) ) {
			wp_send_json_error( array( 'message' => $result->get_error_message() ) );
		}

		$entry = Rates::get();

		wp_send_json_success(
			array(
				'message'      => $result
					? __( 'Saved. Prices are being recalculated.', 'ratemint-exchange-rate-pricing' )
					: __( 'The rate is unchanged; marked as up to date.', 'ratemint-exchange-rate-pricing' ),
				'label'        => self::short_label(),
				'rate_text'    => $entry ? Format::rate( $entry['rate'] ) : '',
				'rate_input'   => $entry ? wc_format_localized_decimal( Settings::to_display( $entry['rate'] ) ) : '',
				'updated_text' => self::updated_text(),
			)
		);
	}

	/**
	 * Rate update without JavaScript.
	 */
	public static function post_update_rate() {
		check_admin_referer( 'erpfw_update_rate', 'erpfw_nonce' );

		if ( ! self::can_manage() ) {
			wp_die( esc_html__( 'You are not allowed to change the exchange rate.', 'ratemint-exchange-rate-pricing' ), 403 );
		}

		$result   = self::save_rate(
			isset( $_POST['currency'] ) ? sanitize_key( wp_unslash( $_POST['currency'] ) ) : '',
			isset( $_POST['rate'] ) ? sanitize_text_field( wp_unslash( $_POST['rate'] ) ) : ''
		);
		$referer  = wp_get_referer();
		$redirect = $referer ? $referer : admin_url();

		wp_safe_redirect( add_query_arg( 'erpfw_rate', is_wp_error( $result ) ? 'error' : 'updated', $redirect ) );
		exit;
	}
}
