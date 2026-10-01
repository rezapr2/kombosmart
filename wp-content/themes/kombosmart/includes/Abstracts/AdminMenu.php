<?php

namespace TanilChoob\Theme\Abstracts;

abstract class AdminMenu {
    abstract protected function add_menu();
    abstract protected function render();

    public function __construct() {
        add_action( 'admin_menu', [$this, 'add_menu'], 999);
    }
}