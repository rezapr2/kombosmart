<?php


namespace TanilChoob\Theme;

use TanilChoob\Theme\AdminMenu\AdminMenu;
use TanilChoob\Theme\Ajax\Ajax;
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

        // Show tc_option_adjustments clearly in the WooCommerce admin order detail
        add_filter( 'woocommerce_hidden_order_itemmeta', [ $this, 'unhide_option_adjustment_meta' ] );

        // Transform variation attribute labels in admin order meta display
        add_filter( 'woocommerce_order_item_display_meta_key', [ $this, 'transform_variation_meta_label' ], 10, 3 );
        add_filter( 'woocommerce_attribute_label', [ $this, 'transform_attribute_label' ], 10, 3 );

        // Show national code, fixed phone and payment note on the admin order edit screen
        add_action( 'woocommerce_admin_order_data_after_billing_address', [ $this, 'render_order_extra_billing_meta' ] );
        add_action( 'woocommerce_admin_order_data_after_billing_address', [ $this, 'render_order_payment_note' ] );

        add_filter( 'woocommerce_show_admin_notice', function ( $show, $notice ) {
            if ( 'template_files' === $notice ) {
                return false;
            }

            return $show;
        }, 10, 2 );

    }

    public function render_order_extra_billing_meta( \WC_Order $order ) {
        $nationalcode = $order->get_meta( '_tc_billing_nationalcode' );
        $fixedphone   = $order->get_meta( '_tc_billing_fixedphone' );

        if ( ! $nationalcode && ! $fixedphone ) {
            return;
        }
        ?>
        <?php if ( $fixedphone ) : ?>
            <p>
                <strong><?php esc_html_e( 'تلفن ثابت:', 'tanilchoob' ); ?></strong>
                <?php echo esc_html( $fixedphone ); ?>
            </p>
        <?php endif; ?>
        <?php if ( $nationalcode ) : ?>
            <p>
                <strong><?php esc_html_e( 'کد ملی:', 'tanilchoob' ); ?></strong>
                <?php echo esc_html( $nationalcode ); ?>
            </p>
        <?php endif; ?>
        <?php
    }

    public function render_order_payment_note( \WC_Order $order ) {
        $note = $order->get_meta( '_tc_payment_note' );

        if ( ! $note ) {
            return;
        }
        ?>
        <p>
            <strong><?php esc_html_e( 'توضیحات سفارش:', 'tanilchoob' ); ?></strong>
            <?php echo esc_html( $note ); ?>
        </p>
        <?php
    }

    public function unhide_option_adjustment_meta( array $hidden ): array {
        // Remove any option-adjustment keys from the hidden list (they have no _ prefix so
        // WC shouldn't hide them, but this ensures they always stay visible in admin).
        return array_values( array_filter( $hidden, function( $key ) {
            return str_starts_with( $key, '_' );
        } ) );
    }

    public function transform_variation_meta_label( $display_key, $meta, $item ) {
        // Check if this is a variation attribute (starts with pa_ or attribute_)
        if ( strpos( $meta->key, 'pa_' ) === 0 || strpos( $meta->key, 'attribute_pa_' ) === 0 || strpos( $meta->key, 'attribute_' ) === 0 ) {
            $product = $item->get_product();
            if ( $product ) {
                // Remove 'attribute_' prefix if present
                $attribute_name = str_replace( 'attribute_', '', $meta->key );
                
                // Use wc_attribute_label to get the proper label
                $label = wc_attribute_label( $attribute_name, $product );
                
                if ( $label && $label !== $attribute_name ) {
                    return $label;
                }
            }
        }
        
        return $display_key;
    }

    public function transform_attribute_label( $label, $name, $product ) {
        // If the label is the same as the name (not transformed), try to get proper label
        if ( $label === $name && ( strpos( $name, 'pa_' ) === 0 || strpos( $name, 'attribute_pa_' ) === 0 ) ) {
            $attribute_name = str_replace( 'attribute_', '', $name );
            
            // Try to get the attribute taxonomy
            $taxonomy = wc_attribute_taxonomy_name( str_replace( 'pa_', '', $attribute_name ) );
            
            if ( taxonomy_exists( $taxonomy ) ) {
                $attribute_taxonomies = wc_get_attribute_taxonomies();
                foreach ( $attribute_taxonomies as $tax ) {
                    if ( wc_attribute_taxonomy_name( $tax->attribute_name ) === $taxonomy ) {
                        return $tax->attribute_label ? $tax->attribute_label : $tax->attribute_name;
                    }
                }
            }
        }
        
        return $label;
    }

	private function load_dependencies() {
		foreach ( glob( Bootstrap::$path . 'includes/Abstracts/*.php' ) as $filename ) {
			include_once $filename;
		}

		require_once get_template_directory().'/includes/OptionPage/OptionPage.php';
		require_once get_template_directory().'/includes/AdminMenu/AdminMenu.php';
        require_once get_template_directory().'/includes/Ajax/Ajax.php';
        require_once get_template_directory().'/includes/PostType/PostTypes.php';
        require_once get_template_directory().'/includes/Taxonomy/Taxonomy.php';
//        require_once get_template_directory().'/includes/Authentication.php';
	}

	private function initializer() {
		new AdminMenu();
        new Ajax();
        new OptionPage();
        new PostTypes();
        new Taxonomy();
//        new Authentication();
	}

    public function disable_rest_api($result) {
        if ( ! empty( $result ) ) {
            return $result;
        }
        // Contact Form 7 submits (and loads its schema) over REST; its routes must stay public
        // or every CF7 form (newsletter, contact, warranty) fails for guests.
        $route = isset( $GLOBALS['wp']->query_vars['rest_route'] ) ? (string) $GLOBALS['wp']->query_vars['rest_route'] : '';
        if ( strpos( $route, '/contact-form-7/' ) === 0 ) {
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

/**
 * -----------------------------------------------------------------------------
 * Custom WooCommerce Category & Tag WYSIWYG Editor
 * -----------------------------------------------------------------------------
 */

/**
 * 1. Hide the default plain text description field on the Product Category & Tag Edit screens.
 */
add_action( 'admin_head', __NAMESPACE__ . '\hide_default_taxonomy_description' );
function hide_default_taxonomy_description() {
    $screen = get_current_screen();
    // Check if we are on the WooCommerce product category OR product tag edit screen
    if ( $screen && in_array( $screen->id, array( 'edit-product_cat', 'edit-product_tag' ), true ) ) {
        echo '<style>.term-description-wrap { display: none; }</style>';
    }
}

/**
 * 2. Add a Rich Text Editor (WYSIWYG) to the Product Category & Tag Edit screens.
 */
add_action( 'product_cat_edit_form_fields', __NAMESPACE__ . '\add_wysiwyg_to_taxonomy_description', 10, 2 );
add_action( 'product_tag_edit_form_fields', __NAMESPACE__ . '\add_wysiwyg_to_taxonomy_description', 10, 2 );
function add_wysiwyg_to_taxonomy_description( $term, $taxonomy ) {
    ?>
    <tr class="form-field custom-term-description-wrap">
        <th scope="row"><label for="cat_description">توضیح (Description)</label></th>
        <td>
            <?php
            $settings = array(
                'wpautop'       => true,
                'media_buttons' => true,
                'textarea_name' => 'description', // This forces WordPress to save it automatically
                'textarea_rows' => 10,
                'teeny'         => false
            );
            
            // Output the WordPress editor
            $content = htmlspecialchars_decode( $term->description );
            wp_editor( $content, 'cat_description', $settings );
            ?>
            <p class="description">توضیح به طور پیش‌فرض پررنگ نیست؛ با این حال، برخی از پوسته‌ها ممکن است آن را نمایش دهند.</p>
        </td>
    </tr>
    <?php
}