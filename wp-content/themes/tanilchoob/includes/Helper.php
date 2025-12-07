<?php


namespace TanilChoob\Theme;


class Helper {
	public static function getAssetPath( $file ) {
		return sprintf( '%s/assets/frontend/%s', get_stylesheet_directory(), $file );
	}

	public static function getAssetUri( $file ) {
		return sprintf( '%s/assets/frontend/dist/%s', get_stylesheet_directory_uri(), $file );
	}

    // is_admin() will return true for admin-ajax.php
    public static function requestIsFrontendAjax()
    {
        $script_filename = isset($_SERVER['SCRIPT_FILENAME']) ? $_SERVER['SCRIPT_FILENAME'] : '';

        //Try to figure out if frontend AJAX request... If we are DOING_AJAX; let's look closer
        if ((defined('DOING_AJAX') && DOING_AJAX)) {
            //From wp-includes/functions.php, wp_get_referer() function.
            //Required to fix: https://core.trac.wordpress.org/ticket/25294
            $ref = '';
            if (!empty($_REQUEST['_wp_http_referer']))
                $ref = wp_unslash($_REQUEST['_wp_http_referer']);
            elseif (!empty($_SERVER['HTTP_REFERER']))
                $ref = wp_unslash($_SERVER['HTTP_REFERER']);

            //If referer does not contain admin URL and we are using the admin-ajax.php endpoint, this is likely a frontend AJAX request
            if (((strpos($ref, admin_url()) === false) && (basename($script_filename) === 'admin-ajax.php')))
                return true;
        }

        //If no checks triggered, we end up here - not an AJAX request.
        return false;
    }

    public static function file_get_contents($url)
    {
        $arrContextOptions = array(
            "ssl" => array(
                "verify_peer" => false,
                "verify_peer_name" => false,
            ),
        );

        return file_get_contents($url, false, stream_context_create($arrContextOptions));
    }

    public static function get_options_field($field_name){
        $value = get_field($field_name, 'option');

        return $value;
    }

    public static function array_unique_multidimensional($arr)
    {
        $arr = array_map("unserialize", array_unique(array_map("serialize", $arr)));
        return $arr;
    }

    public static function remove_gaps_in_array($arr)
    {
        $arr = array_values(array_filter($arr));
        return $arr;
    }

    public static function get_taxonomy_terms_ordered($taxonomy){
        $terms = get_terms(
            [
                'taxonomy'=>$taxonomy,
                'hide_empty'=>false,
            ]
        );

        $count = count($terms);
        for ($i=0; $i<$count; $i++) {
            $terms[$i]->sort_order = get_field('order', $taxonomy.'_'.$terms[$i]->term_id) ?: 1000;
        }
        usort($terms, function ($a, $b) {
            // this function expects that items to be sorted are objects and
            // that the property to sort by is $object->sort_order
            if ($a->sort_order == $b->sort_order) {
                return 0;
            } elseif ($a->sort_order < $b->sort_order) {
                return -1;
            } else {
                return 1;
            }
        });

        return $terms;
    }

    public static function get_taxonomy_selected_terms_ordered($taxonomy, $terms){
        $count = count($terms);
        for ($i=0; $i<$count; $i++) {
            $terms[$i]->sort_order = get_field('order', $taxonomy.'_'.$terms[$i]->term_id) ?: 1000;
        }
        usort($terms, function ($a, $b) {
            // this function expects that items to be sorted are objects and
            // that the property to sort by is $object->sort_order
            if ($a->sort_order == $b->sort_order) {
                return 0;
            } elseif ($a->sort_order < $b->sort_order) {
                return -1;
            } else {
                return 1;
            }
        });

        return $terms;
    }

    public static function get_post_id_by_meta_key_and_value( $key, $value, $post_type='', $post_status = '' ) {
        global $wpdb;

        $query = $wpdb->prepare('select p.id from ' . $wpdb->postmeta . ' as pm inner join '.$wpdb->posts.' as p on pm.post_id=p.id where pm.meta_key = %s and pm.meta_value = %s', $key, $value);
        if(!empty($post_type))
            $query .= $wpdb->prepare(' and p.post_type = %s', $post_type);
        if(!empty($post_status))
            $query .= $wpdb->prepare(' and p.post_status = %s', $post_status);

        $meta = $wpdb->get_results($query);
        if ( is_array( $meta ) ) {
            if(sizeof( $meta ) === 1)
            {
                $meta = $meta[0];
            }
            else {
                return false;
            }
        }

        if ( is_object( $meta ) ) {
            return $meta->id;
        } else {
            return false;
        }
    }

    public static function array_equal($a, $b) {
        return (
            is_array($a)
            && is_array($b)
            && count($a) == count($b)
            && array_diff($a, $b) === array_diff($b, $a)
        );
    }

    public static function multi_dimensional_array_unique_according_to_field($array, $field_key)
    {
        $temp = array_unique(array_column($array, $field_key));
        $unique_arr = array_intersect_key($array, $temp);
        return $unique_arr;
    }

    public static function clear_taxonomy_items( $taxonomy_name ) {
        $terms         = get_terms( array(
            'taxonomy'   => $taxonomy_name,
            'hide_empty' => false
        ) );
        foreach ( $terms as $term ) {
            wp_delete_term( $term->term_id, $taxonomy_name );
        }
    }

    public static function get_product_questions_count($product_id)
    {
        $product_id = intval($product_id);
        if ($product_id <= 0) {
            return 0;
        }

        $query = new \WP_Query([
            'post_type'              => 'product_questions',
            'post_status'            => 'publish',
            'post_parent'            => 0,
            'posts_per_page'         => 1,            // use found_posts for count
            'fields'                 => 'ids',        // lightweight
            'no_found_rows'          => false,        // needed to populate found_posts
            'cache_results'          => false,
            'update_post_meta_cache' => false,
            'update_post_term_cache' => false,
            'meta_query'             => [
                [
                    'key'     => 'product',
                    'value'   => $product_id,
                    'compare' => '=',
                    'type'    => 'NUMERIC',
                ],
            ],
        ]);

        return isset($query->found_posts) ? intval($query->found_posts) : 0;
    }
}
