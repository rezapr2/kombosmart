<?php

namespace TanilChoob\Theme\Taxonomy;

use TanilChoob\Theme\Bootstrap;

class Taxonomy
{
    public function __construct() {
        $this->load_dependencies();
    }

    private function load_dependencies() {
        foreach ( glob( Bootstrap::$path . 'includes/Taxonomy/*.php' ) as $filename ) {
            if($filename != "Taxonomy.php")
                include_once $filename;
        }
    }

}