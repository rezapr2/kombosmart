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

    public static function get_list_of_regions_and_municipalities()
    {
        return [
            "Aveiro" => [
                "Águeda",
                "Anadia",
                "Aveiro",
                "Espinho",
                "Estarreja",
                "Ílhavo",
                "Mealhada",
                "Oliveira de Azeméis",
                "Oliveira do Bairro",
                "Ovar",
                "Santa Maria da Feira",
                "São João da Madeira",
                "Vagos",
                "Vale de Cambra"
            ],
            "Beja" => [
                "Beja",
                "Moura",
                "Odemira",
                "Serpa"
            ],
            "Braga" => [
                "Amares",
                "Barcelos",
                "Braga",
                "Cabeceiras de Basto",
                "Celorico de Basto",
                "Esposende",
                "Fafe",
                "Guimarães",
                "Póvoa de Lanhoso",
                "Vila Nova de Famalicão",
                "Vila Verde",
                "Vizela"
            ],
            "Bragança" => [
                "Bragança",
                "Mogadouro",
                "Vimioso"
            ],
            "Castelo Branco" => [
                "Castelo Branco",
                "Covilhã",
                "Fundão",
                "Proença-a-Nova"
            ],
            "Coimbra" => [
                "Coimbra",
                "Condeixa-a-Nova",
                "Figueira da Foz",
                "Lousã",
                "Mira",
                "Montemor-o-Velho",
                "Soure"
            ],
            "Évora" => [
                "Estremoz",
                "Évora",
                "Redondo"
            ],
            "Faro" => [
                "Albufeira",
                "Faro",
                "Lagoa (Algarve)",
                "Lagos",
                "Loulé",
                "Olhão",
                "Portimão",
                "Silves",
                "Tavira",
                "Vila Real de Santo António"
            ],
            "Guarda" => [
                "Guarda",
                "Trancoso"
            ],
            "Ilha da Madeira" => [
                "Calheta (Madeira)",
                "Funchal",
                "Machico",
                "Ribeira Brava"
            ],
            "Ilha de Santa Maria" => [
                "Vila do Porto"
            ],
            "Ilha de São Jorge" => [
                "Calheta (São Jorge)"
            ],
            "Ilha de São Miguel" => [
                "Ponta Delgada",
                "Ribeira Grande"
            ],
            "Ilha do Faial" => [
                "Horta"
            ],
            "Ilha do Pico" => [
                "Madalena"
            ],
            "Ilha Terceira" => [
                "Angra do Heroísmo"
            ],
            "Leiria" => [
                "Alcobaça",
                "Alvaiázere",
                "Ansião",
                "Batalha",
                "Bombarral",
                "Caldas da Rainha",
                "Leiria",
                "Marinha Grande",
                "Nazaré",
                "Peniche",
                "Pombal",
                "Porto de Mós"
            ],
            "Lisboa" => [
                "Alenquer",
                "Amadora",
                "Arruda dos Vinhos",
                "Azambuja",
                "Cascais",
                "Lisboa",
                "Loures",
                "Mafra",
                "Odivelas",
                "Oeiras",
                "Sintra",
                "Torres Vedras",
                "Vila Franca de Xira"
            ],
            "Portalegre" => [
                "Elvas",
                "Portalegre"
            ],
            "Porto" => [
                "Amarante",
                "Felgueiras",
                "Gondomar",
                "Lousada",
                "Maia",
                "Marco de Canaveses",
                "Matosinhos",
                "Paços de Ferreira",
                "Paredes",
                "Penafiel",
                "Porto",
                "Póvoa de Varzim",
                "Santo Tirso",
                "Trofa",
                "Valongo",
                "Vila do Conde",
                "Vila Nova de Gaia"
            ],
            "Santarém" => [
                "Abrantes",
                "Almeirim",
                "Benavente",
                "Cartaxo",
                "Entroncamento",
                "Ourém",
                "Rio Maior",
                "Santarém",
                "Tomar",
                "Torres Novas"
            ],
            "Setúbal" => [
                "Alcácer do Sal",
                "Alcochete",
                "Almada",
                "Barreiro",
                "Grândola",
                "Moita",
                "Montijo",
                "Palmela",
                "Santiago do Cacém",
                "Seixal",
                "Sesimbra",
                "Setúbal",
                "Sines"
            ],
            "Viana do Castelo" => [
                "Arcos de Valdevez",
                "Caminha",
                "Monção",
                "Ponte da Barca",
                "Ponte de Lima",
                "Valença",
                "Viana do Castelo"
            ],
            "Vila Real" => [
                "Chaves",
                "Murça",
                "Vila Real"
            ],
            "Viseu" => [
                "Castro Daire",
                "Lamego",
                "Mangualde",
                "Nelas",
                "Santa Comba Dão",
                "São Pedro do Sul",
                "Tondela",
                "Viseu"
            ]
        ];
    }

    public static function import_regions_and_municipalities($regions_taxonomy, $municipalities_taxonomy, $log=false)
    {
        ini_set( "memory_limit", "2048M" );
        ini_set( 'max_execution_time', '3600' );
        set_time_limit( 3600 );


        self::clear_taxonomy_items( $regions_taxonomy );
        self::clear_taxonomy_items( $municipalities_taxonomy );

        $list = self::get_list_of_regions_and_municipalities();

        foreach ( $list as $region => $municipalities ) {
            if($log) error_log('$region: ' . $region);
            $region_taxonomy = wp_insert_term( $region, $regions_taxonomy );

            foreach ( $municipalities as $municipality ) {
                if($log) error_log('$municipality: ' . $municipality);
                $municipality_taxonomy = wp_insert_term( $municipality, $municipalities_taxonomy );
                $municipality_term_id = $municipality_taxonomy['term_id'];

                update_field( 'region', $region_taxonomy['term_id'], 'term_' . $municipality_term_id );
            }
        }
    }
}
