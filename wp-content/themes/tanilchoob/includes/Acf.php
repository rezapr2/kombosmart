<?php

namespace TanilChoob\Theme;

class ACF {
    public function __construct(){
//        // use default language for acf options page
//        add_filter('acf/settings/current_language', function () {
//            return false;
//        });

//        add_action( 'admin_init', [$this, 'force_redirect_to_the__all__version_of_global_options']);
    }

    public function force_redirect_to_the__all__version_of_global_options() {
        // correct page
        global $pagenow ;
        if($pagenow === "admin.php" && isset($_GET['page']) && $_GET['page'] === "acf-options-general-options") { // global-options is the menu_slug you defined in acf_add_options_page
            // lang not 'all'?
            if(ICL_LANGUAGE_CODE !== 'all') {
                // manipulate query (set lang to "all")
                $query = $_GET;
                $query['lang'] = 'all';
                $query_result = http_build_query($query);
                // redirect and die
                wp_redirect(get_admin_url() . 'admin.php?' . $query_result);
                die();
            }
        }
    }
}
