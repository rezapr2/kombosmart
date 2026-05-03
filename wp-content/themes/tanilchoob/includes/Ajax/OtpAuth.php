<?php

namespace TanilChoob\Theme\Ajax;

use TanilChoob\Theme\Helper;

class OtpAuth {

	const OTP_TTL       = 120; // seconds
	const RATE_LIMIT    = 5;   // max sends per window
	const RATE_WINDOW   = 600; // 10 minutes

	public function __construct() {
		add_action( 'wp_ajax_tanilchoob_otp_send',        [ $this, 'handle_send' ] );
		add_action( 'wp_ajax_nopriv_tanilchoob_otp_send', [ $this, 'handle_send' ] );
		add_action( 'wp_ajax_tanilchoob_otp_verify',        [ $this, 'handle_verify' ] );
		add_action( 'wp_ajax_nopriv_tanilchoob_otp_verify', [ $this, 'handle_verify' ] );
	}

	// ── Send OTP ────────────────────────────────────────────────

	public function handle_send() {
		$this->check_nonce();

		$mobile = $this->normalize_mobile(
			isset( $_POST['mobile'] ) ? sanitize_text_field( wp_unslash( $_POST['mobile'] ) ) : ''
		);

		if ( ! $this->is_valid_mobile( $mobile ) ) {
			wp_send_json_error( [ 'message' => 'شماره موبایل معتبر نیست.' ] );
		}

		$rate_key = 'tc_otp_rate_' . md5( $mobile );
		$attempts = (int) get_transient( $rate_key );
		if ( $attempts >= self::RATE_LIMIT ) {
			wp_send_json_error( [ 'message' => 'تعداد درخواست‌ها بیش از حد مجاز است. لطفاً چند دقیقه صبر کنید.' ] );
		}

		$otp     = sprintf( '%06d', random_int( 100000, 999999 ) );
		$otp_key = 'tc_otp_' . md5( $mobile );
		set_transient( $otp_key, $otp, self::OTP_TTL );
		set_transient( $rate_key, $attempts + 1, self::RATE_WINDOW );

		if ( ! $this->send_sms( $mobile, $otp ) ) {
			wp_send_json_error( [ 'message' => 'خطا در ارسال پیامک. لطفاً دوباره امتحان کنید.' ] );
		}

		wp_send_json_success( [ 'message' => 'کد تأیید ارسال شد.' ] );
	}

	// ── Verify OTP ──────────────────────────────────────────────

	public function handle_verify() {
		$this->check_nonce();

		$mobile = $this->normalize_mobile(
			isset( $_POST['mobile'] ) ? sanitize_text_field( wp_unslash( $_POST['mobile'] ) ) : ''
		);
		$otp = isset( $_POST['otp'] ) ? preg_replace( '/[^0-9]/', '', sanitize_text_field( wp_unslash( $_POST['otp'] ) ) ) : '';

		if ( ! $this->is_valid_mobile( $mobile ) ) {
			wp_send_json_error( [ 'message' => 'شماره موبایل معتبر نیست.' ] );
		}

		if ( strlen( $otp ) !== 6 ) {
			wp_send_json_error( [ 'message' => 'کد تأیید باید ۶ رقم باشد.' ] );
		}

		$otp_key    = 'tc_otp_' . md5( $mobile );
		$stored_otp = get_transient( $otp_key );

		if ( ! $stored_otp || $stored_otp !== $otp ) {
			wp_send_json_error( [ 'message' => 'کد تأیید اشتباه یا منقضی شده است.' ] );
		}

		delete_transient( $otp_key );
		delete_transient( 'tc_otp_rate_' . md5( $mobile ) );

		$user = $this->get_user_by_phone( $mobile );

		if ( ! $user ) {
			$user_id = $this->create_user( $mobile );
			if ( is_wp_error( $user_id ) ) {
				wp_send_json_error( [ 'message' => 'خطا در ایجاد حساب کاربری.' ] );
			}
			$user = get_user_by( 'id', $user_id );
		}

		wp_set_current_user( $user->ID );
		wp_set_auth_cookie( $user->ID, true );
		do_action( 'wp_login', $user->user_login, $user );

		$redirect = apply_filters( 'woocommerce_login_redirect', wc_get_page_permalink( 'myaccount' ), $user );

		wp_send_json_success( [ 'redirect' => $redirect ] );
	}

	// ── Helpers ─────────────────────────────────────────────────

	private function check_nonce() {
		$nonce = isset( $_POST['nonce'] ) ? $_POST['nonce'] : '';
		if ( ! wp_verify_nonce( $nonce, 'ajax-nonce' ) ) {
			wp_send_json_error( [ 'message' => 'درخواست نامعتبر است.' ] );
		}
	}

	/**
	 * Convert Persian/Arabic-Indic digits → ASCII, strip non-digits, normalise prefix.
	 */
	private function normalize_mobile( $input ) {
		$fa = [ '۰','۱','۲','۳','۴','۵','۶','۷','۸','۹','٠','١','٢','٣','٤','٥','٦','٧','٨','٩' ];
		$en = [ '0','1','2','3','4','5','6','7','8','9','0','1','2','3','4','5','6','7','8','9' ];
		$input = str_replace( $fa, $en, $input );
		$input = preg_replace( '/[^0-9+]/', '', $input );

		// +989xxxxxxxxx → 09xxxxxxxxx
		if ( substr( $input, 0, 3 ) === '+98' ) {
			$input = '0' . substr( $input, 3 );
		}
		// 989xxxxxxxxx → 09xxxxxxxxx
		if ( strlen( $input ) === 12 && substr( $input, 0, 2 ) === '98' ) {
			$input = '0' . substr( $input, 2 );
		}

		return $input;
	}

	private function is_valid_mobile( $mobile ) {
		return (bool) preg_match( '/^09[0-9]{9}$/', $mobile );
	}

	private function get_user_by_phone( $mobile ) {
		// WooCommerce stores billing phone in user meta.
		$users = get_users( [
			'meta_key'   => 'billing_phone',
			'meta_value' => $mobile,
			'number'     => 1,
			'fields'     => 'all',
		] );

		if ( ! empty( $users ) ) {
			return $users[0];
		}

		// Fallback: username equals mobile.
		$user = get_user_by( 'login', $mobile );
		return $user ?: null;
	}

	private function create_user( $mobile ) {
		$username = $mobile;
		// Ensure unique username.
		if ( username_exists( $username ) ) {
			$username = $mobile . '_' . wp_generate_password( 4, false );
		}
		$email    = $mobile . '@mobile.user';
		$password = wp_generate_password( 20, true, true );

		$user_id = wp_create_user( $username, $password, $email );
		if ( is_wp_error( $user_id ) ) {
			return $user_id;
		}

		update_user_meta( $user_id, 'billing_phone', $mobile );

		return $user_id;
	}

	private function send_sms( $mobile, $otp ) {
		$api_key             = Helper::get_options_field( 'sms_api_key' );
		$sms_base_url        = 'https://edge.ippanel.com/v1';
		$sms_from_number     = Helper::get_options_field( 'sms_from_number' );
		$otp_validation_number = Helper::get_options_field( 'otp_validation_number' );
		$pattern_code        = $otp_validation_number ? $otp_validation_number['code'] : '';

		// If no API key is configured, log the OTP for local development and return true.
		if ( empty( $api_key ) ) {
			error_log( "[TanilChoob OTP] mobile={$mobile} otp={$otp}" );
			return true;
		}

		$url      = rtrim( $sms_base_url, '/' ) . '/api/send';
		$payload  = wp_json_encode( [
			'sending_type' => 'pattern',
			'from_number'  => $sms_from_number,
			'code'         => $pattern_code,
			'recipients'   => [ $mobile ],
			'params'       => [ 'code' => $otp ],
		] );

		$response = wp_remote_post( $url, [
			'timeout' => 10,
			'headers' => [
				'Content-Type'  => 'application/json',
				'Authorization' => $api_key,
			],
			'body'    => $payload,
		] );

		if ( is_wp_error( $response ) ) {
			error_log( '[TanilChoob OTP] SMS error: ' . $response->get_error_message() );
			return false;
		}

		$status = (int) wp_remote_retrieve_response_code( $response );

		return $status === 200;
	}
}

new OtpAuth();
