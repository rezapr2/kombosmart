<?php

namespace TanilChoob\Theme\OptionPage;

use TanilChoob\Theme\Bootstrap;

class OptionPage
{
    public function __construct() {
        $this->load_dependencies();
    }

    private function load_dependencies() {
        if (function_exists('acf_add_options_page')) {
            foreach (glob(Bootstrap::$path . 'includes/OptionPage/*.php') as $filename) {
                if ($filename != "OptionPage.php")
                    include_once $filename;
            }
        }
    }
}