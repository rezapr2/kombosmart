<?php


namespace TanilChoob\Theme;

class Authentication {

	public function __construct() {
		add_action( 'template_redirect', [ $this, 'auth' ] );
	}

	public function auth() {
		if (!current_user_can('administrator')) {
			$is_logged_in = isset( $_COOKIE['ea_token'] ) && ! empty( $_COOKIE['ea_token'] ) && wp_is_uuid( $_COOKIE['ea_token'] );
			$login_page_object = get_field( 'login_page', 'options' );
			$register_page_object = get_field( 'register_page', 'options' );
			$complete_page_object = get_field( 'register_complete_page', 'options' );
			$current_object_id = get_queried_object_id();

			if ( ! $is_logged_in ) {
				global $wp;
				if ( $current_object_id != $login_page_object->ID && $current_object_id != $register_page_object->ID && $current_object_id != $complete_page_object->ID ) {
					$login_page_url = get_permalink( $login_page_object->ID );
					$current_page   = home_url( $wp->request );
					$redirect_url   = add_query_arg( 'referer', $current_page, $login_page_url );
					wp_redirect( $redirect_url );
				}
			} else {
				if ( $current_object_id == $login_page_object->ID ) {
					wp_redirect( home_url() );
				}
			}
		}
	}

}
