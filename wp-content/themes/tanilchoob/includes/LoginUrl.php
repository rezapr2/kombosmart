<?php


namespace TanilChoob\Theme;

/**
 * Relocate the WordPress login / admin entry point to a custom slug.
 *
 * The default `wp-login.php` is served from `/hi-tanil` instead, and direct
 * access to `wp-login.php` is blocked with a 404. Logged-out visitors hitting
 * `wp-admin` are sent to the new login URL.
 */
class LoginUrl {

	/**
	 * Custom login slug. Change this value to relocate the login page.
	 *
	 * @var string
	 */
	private $slug = 'hi-tanil';

	/**
	 * Whether the current request is being routed through wp-login.php.
	 *
	 * @var bool
	 */
	private $wp_login_php = false;

	public function __construct() {
		// NOTE: `plugins_loaded` fires before a theme's functions.php is loaded,
		// so it can never run from within a theme. `after_setup_theme` is the
		// earliest hook available to us that still runs before WordPress routes
		// the request (init / wp_loaded / template_redirect all come later).
		add_action( 'after_setup_theme', [ $this, 'intercept_request' ], 1 );
		add_action( 'wp_loaded', [ $this, 'wp_loaded' ] );

		add_filter( 'site_url', [ $this, 'site_url' ], 10, 4 );
		add_filter( 'network_site_url', [ $this, 'network_site_url' ], 10, 3 );
		add_filter( 'wp_redirect', [ $this, 'wp_redirect' ], 10, 2 );

		add_filter( 'login_url', [ $this, 'login_url' ], 10, 3 );
		add_filter( 'logout_url', [ $this, 'filter_default_arg' ], 10, 1 );
		add_filter( 'lostpassword_url', [ $this, 'filter_default_arg' ], 10, 1 );
		add_filter( 'register_url', [ $this, 'filter_default_arg' ], 10, 1 );

		// Stop core from helpfully redirecting /admin, /dashboard, /login to wp-admin.
		remove_action( 'template_redirect', 'wp_redirect_admin_locations', 1000 );

		// Flush rewrite rules when the theme is activated.
		add_action( 'after_switch_theme', [ $this, 'flush_rules' ] );
	}

	/**
	 * Flush rewrite rules so the relocated login slug resolves immediately
	 * after the theme is activated.
	 */
	public function flush_rules() {
		flush_rewrite_rules();
	}

	/**
	 * The configured login slug.
	 *
	 * @return string
	 */
	private function new_login_slug() {
		return $this->slug;
	}

	/**
	 * Full URL to the relocated login page.
	 *
	 * @param string|null $scheme
	 * @return string
	 */
	public function new_login_url( $scheme = null ) {
		$url = home_url( '/', $scheme );

		if ( get_option( 'permalink_structure' ) ) {
			return user_trailingslashit( $url . $this->new_login_slug() );
		}

		return $url . '?' . $this->new_login_slug();
	}

	/**
	 * Intercept the request early to map the custom slug onto wp-login.php and
	 * to mask the real wp-login.php behind an unmatchable request.
	 */
	public function intercept_request() {
		global $pagenow;

		if ( ! isset( $_SERVER['REQUEST_URI'] ) ) {
			return;
		}

		$request = parse_url( rawurldecode( $_SERVER['REQUEST_URI'] ) );
		$path    = isset( $request['path'] ) ? untrailingslashit( $request['path'] ) : '';

		$is_wp_login = strpos( rawurldecode( $_SERVER['REQUEST_URI'] ), 'wp-login.php' ) !== false
		               || $path === untrailingslashit( site_url( 'wp-login', 'relative' ) );

		if ( $is_wp_login && ! is_admin() ) {
			// Real wp-login.php request: route it to an unmatchable URL so it 404s.
			$this->wp_login_php     = true;
			$_SERVER['REQUEST_URI'] = '/' . str_repeat( '-/', 10 );
			$pagenow                = 'index.php';

			return;
		}

		$matches_slug = $path === untrailingslashit( home_url( $this->new_login_slug(), 'relative' ) )
		                || ( ! get_option( 'permalink_structure' )
		                     && isset( $_GET[ $this->new_login_slug() ] )
		                     && '' === $_GET[ $this->new_login_slug() ] );

		if ( $matches_slug ) {
			$pagenow = 'wp-login.php';
		}
	}

	/**
	 * Once everything is loaded, either render the login page for the custom
	 * slug, block logged-out admin access, or let the 404 happen.
	 */
	public function wp_loaded() {
		global $pagenow;

		$request = parse_url( rawurldecode( $_SERVER['REQUEST_URI'] ) );
		$path    = isset( $request['path'] ) ? $request['path'] : '';

		// Logged-out users may not reach wp-admin; hide it behind a 404 entirely.
		if ( is_admin()
		     && ! is_user_logged_in()
		     && ! ( defined( 'DOING_AJAX' ) && DOING_AJAX )
		     && 'admin-post.php' !== $pagenow
		     && '/wp-admin/options.php' !== $path ) {
			$this->render_404();
		}

		// Direct hit on the real wp-login.php: render a 404 instead of the login form.
		if ( $this->wp_login_php ) {
			$this->render_404();
		}

		if ( 'wp-login.php' === $pagenow ) {
			// Normalise to the trailing-slashed slug when pretty permalinks are on.
			if ( get_option( 'permalink_structure' )
			     && isset( $request['path'] )
			     && $request['path'] !== user_trailingslashit( $request['path'] )
			     && ! isset( $_POST['wp-submit'] ) ) {
				wp_safe_redirect( $this->new_login_url()
				                  . ( ! empty( $_SERVER['QUERY_STRING'] ) ? '?' . $_SERVER['QUERY_STRING'] : '' ) );
				die;
			}

			global $error, $interim_login, $action, $user_login;

			@require_once ABSPATH . 'wp-login.php';
			die;
		}
	}

	/**
	 * Render the theme's 404 page and stop. Used to hide the real wp-login.php.
	 */
	private function render_404() {
		global $wp_query, $pagenow;

		$pagenow = 'index.php';

		if ( ! defined( 'WP_USE_THEMES' ) ) {
			define( 'WP_USE_THEMES', true );
		}

		// Run the main query against the (unmatchable) rewritten URI so it 404s.
		wp();

		if ( $wp_query instanceof \WP_Query ) {
			$wp_query->set_404();
		}
		status_header( 404 );
		nocache_headers();

		require_once ABSPATH . WPINC . '/template-loader.php';
		die;
	}

	public function site_url( $url, $path, $scheme, $blog_id ) {
		return $this->filter_wp_login_php( $url, $scheme );
	}

	public function network_site_url( $url, $path, $scheme ) {
		return $this->filter_wp_login_php( $url, $scheme );
	}

	public function wp_redirect( $location, $status ) {
		return $this->filter_wp_login_php( $location );
	}

	public function login_url( $login_url, $redirect, $force_reauth ) {
		return $this->filter_wp_login_php( $login_url );
	}

	public function filter_default_arg( $url ) {
		return $this->filter_wp_login_php( $url );
	}

	/**
	 * Swap any wp-login.php URL for the relocated login URL, preserving query args.
	 *
	 * @param string      $url
	 * @param string|null $scheme
	 * @return string
	 */
	public function filter_wp_login_php( $url, $scheme = null ) {
		if ( strpos( $url, 'wp-login.php' ) === false ) {
			return $url;
		}

		if ( is_ssl() ) {
			$scheme = 'https';
		}

		$args = explode( '?', $url );

		if ( isset( $args[1] ) ) {
			parse_str( $args[1], $query );

			return add_query_arg( $query, $this->new_login_url( $scheme ) );
		}

		return $this->new_login_url( $scheme );
	}
}
