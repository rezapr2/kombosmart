<?php


namespace TanilChoob\Theme;


class Setup {


	/**
	 * Frontend constructor.
	 */
	public function __construct() {

		add_action( 'after_setup_theme', [ $this, 'setup' ] );
		add_action( 'widgets_init', array( $this, 'widgets' ) );
	}

	public function setup() {
		load_theme_textdomain( 'tanilchoob', get_template_directory() . '/languages' );

		// Title Tag Support
		add_theme_support( 'title-tag' );

		// Posts Thumbnail Support
		add_theme_support( 'post-thumbnails' );

		// Set Post Thumbnail Sizes
		add_image_size( 'archive', 450, 450, true );
//        add_image_size( 'half-image', 960, 960, true );

		// WooCommerce Support
		add_theme_support( 'woocommerce' );


		// Register Nav menus
		register_nav_menus(
			[
				'main_menu'   => __( 'Header main menu', 'tanilchoob' ),
			]
		);

		// HTML5 Support
		add_theme_support( 'html5', [ 'comment-list', 'comment-form', 'search-form', 'gallery', 'caption' ] );


		add_action( 'admin_init', function () {
			// Redirect any user trying to access comments page
			global $pagenow;

			if ( $pagenow === 'edit-comments.php' ) {
				wp_redirect( admin_url() );
				exit;
			}

			// Remove comments metabox from dashboard
			remove_meta_box( 'dashboard_recent_comments', 'dashboard', 'normal' );

			// Disable support for comments and trackbacks in post types
			foreach ( get_post_types() as $post_type ) {
				if ( post_type_supports( $post_type, 'comments' ) ) {
					remove_post_type_support( $post_type, 'comments' );
					remove_post_type_support( $post_type, 'trackbacks' );
				}
			}
		} );

// Close comments on the front-end
		add_filter( 'comments_open', '__return_false', 20, 2 );
		add_filter( 'pings_open', '__return_false', 20, 2 );

// Hide existing comments
		add_filter( 'comments_array', '__return_empty_array', 10, 2 );

// Remove comments page in menu
		add_action( 'admin_menu', function () {
			remove_menu_page( 'edit-comments.php' );
		} );

// Remove comments links from admin bar
		add_action( 'init', function () {
			if ( is_admin_bar_showing() ) {
				remove_action( 'admin_bar_menu', 'wp_admin_bar_comments_menu', 60 );
			}
		} );

        if (!current_user_can('administrator') && !is_admin()) {
            show_admin_bar(false);
        }

        add_action('admin_init', function() {
            if (is_user_logged_in() && !current_user_can('edit_posts') && !(defined('DOING_AJAX') && DOING_AJAX)) {
                wp_redirect(home_url());
                exit;
            }
        });
	}



    public function widgets() {

//        register_sidebar( array(
//            'name'          => __( 'Footer 1', 'tanilchoob' ),
//            'id'            => 'footer-1',
//            'before_widget' => '<div class="widget">',
//            'after_widget'  => '</div>',
//            'before_title'  => '<div class="widget-title">',
//            'after_title'   => '</div>'
//        ) );
//
//        register_sidebar( array(
//            'name'          => __( 'Footer 2', 'tanilchoob' ),
//            'id'            => 'footer-2',
//            'before_widget' => '<div class="widget">',
//            'after_widget'  => '</div>',
//            'before_title'  => '<div class="widget-title">',
//            'after_title'   => '</div>'
//        ) );
//
//        register_sidebar( array(
//            'name'          => __( 'Footer 3', 'tanilchoob' ),
//            'id'            => 'footer-3',
//            'before_widget' => '<div class="widget">',
//            'after_widget'  => '</div>',
//            'before_title'  => '<div class="widget-title">',
//            'after_title'   => '</div>'
//        ) );

    }

}
