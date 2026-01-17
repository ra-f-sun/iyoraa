<?php
/**
 * Plugin Activator
 *
 * @package WPHelpZone\Iyoraa\Core
 */

namespace WPHelpZone\Iyoraa\Core;

/**
 * Activator class.
 *
 * Handles plugin activation tasks.
 */
class Activator {



	/**
	 * Activate the plugin.
	 */
	public static function activate() {
		// Create database tables.
		Database::create_tables();

		// Set default options.
		self::set_default_options();

		// Flush rewrite rules.
		flush_rewrite_rules();
	}

	/**
	 * Set default plugin options.
	 */
	private static function set_default_options() {
		add_option( 'iyoraa_version', IYORAA_VERSION );
		add_option( 'iyoraa_license_tier', 'free' );
		add_option( 'iyoraa_license_override', false );
		add_option( 'iyoraa_keep_data_on_uninstall', false );
	}
}
