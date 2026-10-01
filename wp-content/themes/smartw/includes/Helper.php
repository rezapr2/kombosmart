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

    public static function jalali_date(int $timestamp): string
    {
        $j = \Jalaali\Jalaali::toJalaali(
            (int) gmdate('Y', $timestamp),
            (int) gmdate('m', $timestamp),
            (int) gmdate('d', $timestamp)
        );
        return sprintf('%04d/%02d/%02d', $j['jy'], $j['jm'], $j['jd']);
    }

    /**
     * Color tones used for cards, badges and tiles (see .tone-* in variables/colors.scss).
     */
    public static function tones(): array
    {
        return ['violet', 'pink', 'cyan', 'orange', 'lime', 'yellow'];
    }

    /**
     * Tone for a product category: the term's "tone" field if set, otherwise a stable
     * pick based on the term id so each category keeps the same color everywhere.
     */
    public static function term_tone($term_id): string
    {
        $term_id = (int) $term_id;
        $tones   = self::tones();
        if (!$term_id) {
            return $tones[0];
        }

        $tone = function_exists('get_field') ? get_field('tone', 'product_cat_' . $term_id) : '';
        if ($tone && in_array($tone, $tones, true)) {
            return $tone;
        }

        return $tones[$term_id % count($tones)];
    }

    /**
     * The category a product is shown under: Yoast's primary category when set,
     * otherwise the deepest assigned category (skipping "Uncategorized").
     */
    public static function product_primary_term($product_id)
    {
        $product_id = (int) $product_id;

        if (class_exists('\WPSEO_Primary_Term')) {
            $primary = (new \WPSEO_Primary_Term('product_cat', $product_id))->get_primary_term();
            if ($primary) {
                $term = get_term($primary, 'product_cat');
                if ($term && !is_wp_error($term)) {
                    return $term;
                }
            }
        }

        $terms = get_the_terms($product_id, 'product_cat');
        if (!$terms || is_wp_error($terms)) {
            return null;
        }

        $default = (int) get_option('default_product_cat');
        $terms   = array_filter($terms, function ($term) use ($default) {
            return (int) $term->term_id !== $default;
        });
        if (!$terms) {
            return null;
        }

        // Prefer a child category (more specific) over its parent.
        usort($terms, function ($a, $b) {
            return count(get_ancestors($b->term_id, 'product_cat')) <=> count(get_ancestors($a->term_id, 'product_cat'));
        });

        return reset($terms);
    }

    /**
     * Top-level product categories with the most products (excluding "Uncategorized").
     * Used as fallback content for homepage sections that have no ACF data yet.
     */
    public static function top_product_categories(int $limit): array
    {
        $terms = get_terms([
            'taxonomy'   => 'product_cat',
            'parent'     => 0,
            'hide_empty' => true,
            'orderby'    => 'count',
            'order'      => 'DESC',
            'number'     => $limit + 1,
            'exclude'    => [(int) get_option('default_product_cat')],
        ]);

        return is_wp_error($terms) ? [] : array_slice($terms, 0, $limit);
    }

    /**
     * Inline SVG from dist/images that can be resized with CSS. The gulp imagemin step strips
     * viewBox, so rebuild it from width/height (otherwise resizing crops the icon).
     */
    public static function svg_icon(string $name): string
    {
        $svg = (string) self::file_get_contents(self::getAssetPath('dist/images/' . $name . '.svg'));
        if ($svg && stripos($svg, 'viewBox') === false
            && preg_match('/<svg[^>]*\swidth="([\d.]+)"[^>]*\sheight="([\d.]+)"/i', $svg, $m)) {
            $svg = preg_replace('/<svg\b/i', '<svg viewBox="0 0 ' . $m[1] . ' ' . $m[2] . '"', $svg, 1);
        }
        return $svg;
    }

    /**
     * tel: link for "call for price" buttons: the contact box phone link when set, otherwise
     * the first number in the footer phone numbers (Persian digits converted). '' if none.
     */
    public static function store_phone_link(): string
    {
        $contact_box = self::get_options_field('contact_box');
        if (!empty($contact_box['phone_link'])) {
            return (string) $contact_box['phone_link'];
        }

        $numbers = wp_strip_all_tags((string) self::get_options_field('phone_numbers'));
        $numbers = strtr($numbers, array_combine(
            ['۰', '۱', '۲', '۳', '۴', '۵', '۶', '۷', '۸', '۹'],
            ['0', '1', '2', '3', '4', '5', '6', '7', '8', '9']
        ));
        if (preg_match('/\+?\d[\d\s-]{6,}\d/', $numbers, $m)) {
            return 'tel:' . preg_replace('/[^\d+]/', '', $m[0]);
        }

        return '';
    }

    /**
     * Store WhatsApp chat link: the social networks link when set, otherwise the contact box
     * link. A bare "https://wa.me/" placeholder (no number) counts as empty. '' if none.
     */
    public static function whatsapp_link(): string
    {
        $socials     = self::get_options_field('social_networks');
        $contact_box = self::get_options_field('contact_box');

        foreach ([$socials['whatsapp_link'] ?? '', $contact_box['whatsapp_link'] ?? ''] as $link) {
            $link = trim((string) $link);
            if ($link && untrailingslashit(preg_replace('#^https?://#', '', $link)) !== 'wa.me') {
                return $link;
            }
        }

        return '';
    }

    /**
     * Permalink of the "نکات خرید کالای استوک" page (slug `stock-terms`), or '' if it
     * doesn't exist. Linked from the single product page and checkout when a cart/product
     * is "used" condition.
     */
    public static function stock_terms_url(): string
    {
        static $url = null;
        if ($url === null) {
            $page = get_page_by_path('stock-terms');
            $url = $page ? get_permalink($page) : '';
        }
        return $url;
    }

    /**
     * Badge data for a product that isn't new (ACF "product_condition"), or null for new
     * products so they stay unbadged.
     */
    public static function product_condition_badge($product_id): ?array
    {
        $badges = [
            'used'        => ['label' => 'استوک', 'tone' => 'orange'],
            'refurbished' => ['label' => 'بازسازی‌شده', 'tone' => 'cyan'],
        ];
        $condition = function_exists('get_field') ? get_field('product_condition', (int) $product_id) : '';

        return $badges[$condition] ?? null;
    }

    /**
     * Allow only <strong> and <br> in editor-provided titles (used to color part of a title).
     */
    public static function title_html($title): string
    {
        return wp_kses((string) $title, ['strong' => [], 'br' => []]);
    }
}
