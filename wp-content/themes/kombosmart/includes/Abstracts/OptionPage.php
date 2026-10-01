<?php

namespace TanilChoob\Theme\Abstracts;

abstract class OptionPage {
    abstract protected function register();

    public function __construct() {
        add_action( 'admin_menu', [$this, 'register'], 1);
    }
}