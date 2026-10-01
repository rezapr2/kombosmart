<?php
/**
 * PSR-4 style autoloader for the plugin namespace.
 *
 * @package RateMint
 */

namespace RateMint;

defined( 'ABSPATH' ) || exit;

/**
 * Maps RateMint\Foo\Bar to src/Foo/Bar.php.
 */
final class Autoloader {

	/**
	 * Register the autoloader.
	 */
	public static function register() {
		spl_autoload_register( array( __CLASS__, 'load' ) );
	}

	/**
	 * Load a class file if it belongs to this plugin.
	 *
	 * @param string $class_name Fully qualified class name.
	 */
	public static function load( $class_name ) {
		$prefix = __NAMESPACE__ . '\\';

		if ( 0 !== strpos( $class_name, $prefix ) ) {
			return;
		}

		$relative = substr( $class_name, strlen( $prefix ) );
		$file     = ERPFW_PATH . 'src/' . str_replace( '\\', '/', $relative ) . '.php';

		if ( is_readable( $file ) ) {
			require_once $file;
		}
	}
}
