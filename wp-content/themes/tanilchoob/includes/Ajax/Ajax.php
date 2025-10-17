<?php

namespace TanilChoob\Theme\Ajax;

use TanilChoob\Theme\Bootstrap;

class Ajax
{
    public function __construct() {
        $this->load_dependencies();
    }

    private function load_dependencies() {
        foreach ( glob( Bootstrap::$path . 'includes/Ajax/*.php' ) as $filename ) {
            if($filename != "Ajax.php")
                include_once $filename;
        }
    }

}