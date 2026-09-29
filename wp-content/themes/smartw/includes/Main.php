<?php
namespace TanilChoob\Theme;


use TanilChoob\Theme\PostType\PostTypes;

class Main {

    /**
     * The single instance of the class.
     *
     * @var \TanilChoob\Theme\Main
     */
    protected static $_instance = null;

	private static $version;

	/**
	 * Main constructor.
	 */
	public function __construct() {
		$theme = wp_get_theme();
		self::$version = $theme->get('Version');
		$this->load_dependencies();
		$this->initializer();
	}

    /**
     * Main Class Instance.
     *
     * Ensures only one instance of this class is loaded or can be loaded.
     *
     * @static
     * @return \TanilChoob\Theme\Main - Main instance.
     */
    public static function instance() {
        if ( is_null( self::$_instance ) ) {
            self::$_instance = new self();
        }

        return self::$_instance;
    }

	private function load_dependencies() {
		require_once get_template_directory().'/includes/Jalaali.php';
		require_once get_template_directory().'/includes/Helper.php';
		require_once get_template_directory().'/includes/Setup.php';
		require_once get_template_directory().'/includes/LoginUrl.php';
		require_once get_template_directory().'/includes/Frontend.php';
		require_once get_template_directory().'/includes/Backend.php';
		require_once get_template_directory().'/includes/Acf.php';
		require_once get_template_directory().'/includes/WC_Product_BackOffice.php';
		require_once get_template_directory().'/includes/Walker_Nav_Menu_Custom.php';
		require_once get_template_directory().'/includes/MyAccount.php';
		require_once get_template_directory().'/includes/SnappPayCompat.php';
    }

	private function initializer() {
		new Setup();
		new LoginUrl();
		new Frontend();
		new Backend();
		new ACF();
		new WC_Product_BackOffice();
		new MyAccount();
		new SnappPayCompat();
	}

	/**
	 * @return string
	 */
	public static function getVersion() {
		return self::$version;
	}

}
