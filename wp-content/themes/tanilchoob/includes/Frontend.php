<?php


namespace TanilChoob\Theme;


class Frontend {


	/**
	 * Frontend constructor.
	 */
	public function __construct() {
		add_action( 'wp_enqueue_scripts', [ $this, 'enqueue_scripts' ] );
        add_filter('mod_rewrite_rules', [$this, 'fix_security_headers']);
	}

	public function enqueue_scripts() {
		if ( ! is_admin() ) {
			global $wp_query;

			$css_relative_path = '/assets/frontend/dist/css/styles.min.css';
			$js_relative_path  = '/assets/frontend/dist/js/scripts.min.js';

			$css_version = filemtime( get_theme_file_path( $css_relative_path ) );
			$js_version  = filemtime( get_theme_file_path( $js_relative_path ) );

			wp_enqueue_style( 'tanilchoob', get_template_directory_uri() . $css_relative_path, [], $css_version );

			wp_enqueue_script( 'scripts', get_template_directory_uri() . $js_relative_path, [ 'jquery' ], $js_version );
			wp_localize_script( 'scripts', 'tanilchoob', [
				'ajax' => [
					'url'          => admin_url( 'admin-ajax.php' ),
					'nonce' => wp_create_nonce( 'ajax-nonce' ),
					'posts'        => json_encode( $wp_query->query_vars ), // everything about your loop is here
					'current_page' => get_query_var( 'paged' ) ? get_query_var( 'paged' ) : 1,
					'max_page'     => $wp_query->max_num_pages,
					'loading'      => __( 'Loading...', 'tanilchoob' ),
					'loadMore'     => __( 'Load more', 'tanilchoob' ),
				],
			] );


		}
	}

    public function fix_security_headers($rules)
    {
        $new_rules = <<<EOD
<IfModule mod_headers.c>
	Header always set Strict-Transport-Security "max-age=31536000; includeSubDomains"
	Header set X-Frame-Options "SAMEORIGIN"
	Header set Referrer-Policy "strict-origin-when-cross-origin"
    Header set Permissions-Policy "sync-xhr=()"  
	Header set X-Content-Type-Options "nosniff"
	Header set Content-Security-Policy "frame-ancestors 'self'"
</IfModule>

Options -Indexes
EOD;
        return $rules . $new_rules;
    }
}
