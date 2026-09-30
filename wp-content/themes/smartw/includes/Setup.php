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
		load_theme_textdomain( 'smartw', get_template_directory() . '/languages' );

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
				'main_menu'   => __( 'Header main menu', 'smartw' ),
				'product_categories_menu'   => __( 'Product Categories Menu', 'smartw' ),
			]
		);

		// HTML5 Support
		add_theme_support( 'html5', [ 'comment-list', 'comment-form', 'search-form', 'gallery', 'caption' ] );


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
//            'name'          => __( 'Footer 1', 'smartw' ),
//            'id'            => 'footer-1',
//            'before_widget' => '<div class="widget">',
//            'after_widget'  => '</div>',
//            'before_title'  => '<div class="widget-title">',
//            'after_title'   => '</div>'
//        ) );
//
//        register_sidebar( array(
//            'name'          => __( 'Footer 2', 'smartw' ),
//            'id'            => 'footer-2',
//            'before_widget' => '<div class="widget">',
//            'after_widget'  => '</div>',
//            'before_title'  => '<div class="widget-title">',
//            'after_title'   => '</div>'
//        ) );
//
//        register_sidebar( array(
//            'name'          => __( 'Footer 3', 'smartw' ),
//            'id'            => 'footer-3',
//            'before_widget' => '<div class="widget">',
//            'after_widget'  => '</div>',
//            'before_title'  => '<div class="widget-title">',
//            'after_title'   => '</div>'
//        ) );

    }

}
