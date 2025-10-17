<?php


namespace TanilChoob\Theme;

use TanilChoob\Theme\AdminMenu\AdminMenu;
use TanilChoob\Theme\Ajax\Ajax;
use TanilChoob\Theme\GutenbergBlock\GutenbergBlocks;
use TanilChoob\Theme\OptionPage\OptionPage;
use TanilChoob\Theme\PostType\PostTypes;
use TanilChoob\Theme\Taxonomy\Taxonomy;

class Backend {

	public function __construct() {
		$this->load_dependencies();
		$this->initializer();

        add_filter( 'rest_authentication_errors', [$this, 'disable_rest_api'], 0 );

        add_filter( 'rest_endpoints', function( $endpoints ) {
            if ( isset( $endpoints['/wp/v2/users'] ) ) unset( $endpoints['/wp/v2/users'] );
            if ( isset( $endpoints['/wp/v2/users/(?P<id>[\d]+)'] ) ) unset( $endpoints['/wp/v2/users/(?P<id>[\d]+)'] );
            return $endpoints;
        });

        add_filter('posts_distinct', [$this, 'cf_search_distinct'] );
        add_filter('posts_join', [$this, 'cf_search_join'] );
        add_filter('posts_where', [$this, 'cf_search_where'] );


        // Disable auto-update emails for core updates.
        add_filter( 'auto_core_update_send_email', '__return_false' );

        // Disable auto-update emails for plugins.
        add_filter( 'auto_plugin_update_send_email', '__return_false' );

        // Disable auto-update emails for themes.
        add_filter( 'auto_theme_update_send_email', '__return_false' );

        add_filter('wpseo_metabox_prio', function() { return 'low'; });

    }

	private function load_dependencies() {
		foreach ( glob( Bootstrap::$path . 'includes/Abstracts/*.php' ) as $filename ) {
			include_once $filename;
		}

		require_once get_template_directory().'/includes/OptionPage/OptionPage.php';
		require_once get_template_directory().'/includes/AdminMenu/AdminMenu.php';
        require_once get_template_directory().'/includes/Ajax/Ajax.php';
        require_once get_template_directory().'/includes/GutenbergBlock/GutenbergBlocks.php';
        require_once get_template_directory().'/includes/PostType/PostTypes.php';
        require_once get_template_directory().'/includes/Taxonomy/Taxonomy.php';
//        require_once get_template_directory().'/includes/Authentication.php';
	}

	private function initializer() {
		new AdminMenu();
        new Ajax();
        new GutenbergBlocks();
        new OptionPage();
        new PostTypes();
        new Taxonomy();
//        new Authentication();
	}

    public function disable_rest_api($result) {
        if ( ! empty( $result ) ) {
            return $result;
        }
        if ( ! is_user_logged_in() ) {
            return new \WP_Error( 'rest_not_logged_in', 'You are not currently logged in.', array( 'status' => 401 ) );
        }

        $user = wp_get_current_user();
        $roles = array('editor', 'administrator');
        if( !array_intersect($roles, $user->roles ) )
            return new \WP_Error( 'rest_not_logged_in', 'You are not currently logged in.', array( 'status' => 401 ) );

        return $result;
    }

    /**
     * Join posts and postmeta tables
     *
     * http://codex.wordpress.org/Plugin_API/Filter_Reference/posts_join
     */
    function cf_search_join( $join ) {
        global $wpdb;
        global $pagenow;

        if ( is_admin() && is_search() && 'edit.php' == $pagenow ) {
            $join .=' LEFT JOIN '.$wpdb->postmeta. ' as cf_left_pm ON '. $wpdb->posts . '.ID = cf_left_pm.post_id ';
        }

        return $join;
    }


    /**
     * Modify the search query with posts_where
     *
     * http://codex.wordpress.org/Plugin_API/Filter_Reference/posts_where
     */
    function cf_search_where( $where ) {
        global $pagenow, $wpdb;

        if ( is_search() && is_admin() && 'edit.php' == $pagenow ) {
            $where = preg_replace(
                "/\(\s*".$wpdb->posts.".post_title\s+LIKE\s*(\'[^\']+\')\s*\)/",
                "(".$wpdb->posts.".post_title LIKE $1) OR (cf_left_pm.meta_value LIKE $1)", $where );
        }

        return $where;
    }

    /**
     * Prevent duplicates
     *
     * http://codex.wordpress.org/Plugin_API/Filter_Reference/posts_distinct
     */
    function cf_search_distinct( $where ) {
        global $wpdb;
        global $pagenow;

        if ( is_search() && is_admin() && 'edit.php' == $pagenow ) {
            return "DISTINCT";
        }

        return $where;
    }
}
