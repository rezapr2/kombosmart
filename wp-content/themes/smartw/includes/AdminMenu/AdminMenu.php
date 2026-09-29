<?php

namespace TanilChoob\Theme\AdminMenu;

use TanilChoob\Theme\Bootstrap;

class AdminMenu {

	/**
	 * Ctt constructor.
	 */
	public function __construct() {
		$this->load_dependencies();
	}

	private function load_dependencies() {
		foreach ( glob( Bootstrap::$path . 'includes/AdminMenu/*.php' ) as $filename ) {
			if ($filename != 'AdminMenu.php') {
				include_once $filename;
			}
		}
	}
}