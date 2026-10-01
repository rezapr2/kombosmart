<?php
/**
 * Activation, deactivation and database setup.
 *
 * @package RateMint
 */

namespace RateMint;

defined( 'ABSPATH' ) || exit;

/**
 * Creates and upgrades the plugin's database table.
 */
final class Installer {

	const DB_VERSION        = '1';
	const DB_VERSION_OPTION = 'erpfw_db_version';

	/**
	 * Activation hook.
	 */
	public static function activate() {
		self::install();
	}

	/**
	 * Deactivation hook: stop background work. Product prices stay as they are.
	 */
	public static function deactivate() {
		if ( function_exists( 'as_unschedule_all_actions' ) ) {
			as_unschedule_all_actions( Recalculator::HOOK );
		}

		delete_option( Recalculator::LOCK );
	}

	/**
	 * Install or upgrade the database schema when the stored version is behind.
	 */
	public static function maybe_upgrade() {
		if ( self::DB_VERSION !== get_option( self::DB_VERSION_OPTION ) ) {
			self::install();
		}
	}

	/**
	 * Create the rate history table.
	 */
	private static function install() {
		global $wpdb;

		require_once ABSPATH . 'wp-admin/includes/upgrade.php';

		$table   = Rates::table();
		$collate = $wpdb->get_charset_collate();

		dbDelta(
			"CREATE TABLE {$table} (
				id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
				currency varchar(10) NOT NULL,
				store_currency varchar(10) NOT NULL,
				rate decimal(24,8) NOT NULL,
				previous_rate decimal(24,8) DEFAULT NULL,
				source varchar(40) NOT NULL DEFAULT '',
				user_id bigint(20) unsigned NOT NULL DEFAULT 0,
				created_at datetime NOT NULL,
				PRIMARY KEY  (id),
				KEY currency_created (currency,created_at)
			) {$collate};"
		);

		update_option( self::DB_VERSION_OPTION, self::DB_VERSION );
	}
}
