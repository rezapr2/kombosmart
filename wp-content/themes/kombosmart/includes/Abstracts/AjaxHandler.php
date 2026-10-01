<?php

namespace TanilChoob\Theme\Abstracts;

use TanilChoob\Theme\Helper;

abstract class AjaxHandler {
	abstract public function handler();

	/**
	 * MetaBox constructor.
	 */
	public function __construct( $id ) {
		$id = sprintf( 'tanilchoob_%s', $id );

		add_action( 'wp_ajax_' . $id, [ $this, 'handler' ] );

		if ( $this->is_public() ) {
			add_action( 'wp_ajax_nopriv_' . $id, [ $this, 'handler' ] );
		}
	}

	public function handle_nonce() {
		$nonce = $_POST['nonce'];
		if ( ! wp_verify_nonce( $nonce, 'ajax-nonce' ) ) {
			wp_send_json_error( $this->get_error_message('invalid_nonce_code') );
		}
	}

	protected function get_input( $var, $default = false, $callback = 'sanitize_text_field' ) {
		$data = isset( $var ) && ! empty( $var ) ? call_user_func( $callback, $var ) : $default;

		return $data;
	}

	protected function validate_recaptcha() {
		$google     = Helper::get_options_field( 'google' );
		$version   = isset($_POST['r_version']) && !empty($_POST['r_version']) ? $_POST['r_version'] : 3;
		$secret_key = $version == 2 ? $google['recaptcha_secret_key_v2'] : $google['recaptcha_secret_key'];
		$score = isset($google['score']) && !empty($google['score']) ? $google['score'] : 0.5;

		if ( $secret_key ) {
			$token    = $_POST['recaptcha_token'];
			$action   = $_POST['action'];
			$response = file_get_contents( "https://www.recaptcha.net/recaptcha/api/siteverify?secret=$secret_key&response=" . $token );
			$response = json_decode( $response );
			if ($version == 2 && $response->success) {
				return true;
			}

			if ( $response->success && $response->action == $action && $response->score >= $score ) {
				$is_valid = true;
			} else {
				$is_valid = false;
			}

			if ( ! $is_valid ) {
				wp_send_json_error( $this->get_error_message('invalid_recaptcha_token').'RECAPTCHA_HANDLER_FAILED' );
			}
		}

		return true;
	}

	public function is_public() {
		return true;
	}

	public function get_error_message($item) {
		$messages = get_field('backend_validation', 'options');
		if (isset($messages[$item]) && is_array($messages[$item])) {
			foreach ( $messages[ $item ] as $message ) {
				if ($message['current_use']) {
				    return $message['message'];
				}
		    }
		}

		return '';
	}
}