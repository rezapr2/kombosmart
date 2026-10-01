<?php

namespace TanilChoob\Theme\PostType;

use TanilChoob\Theme\Bootstrap;

class PostTypes
{
    public function __construct() {
        $this->load_dependencies();
    }

    private function load_dependencies() {
        foreach ( glob( Bootstrap::$path . 'includes/PostType/*.php' ) as $filename ) {
            if($filename != "PostTypes.php")
                include_once $filename;
        }
    }

}