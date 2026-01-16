<?php

/**
 * Plugin Deactivator
 *
 * @package WPHelpZone\Iyoraa\Core
 */

namespace WPHelpZone\Iyoraa\Core;

/**
 * Deactivator class.
 *
 * Handles plugin deactivation tasks.
 */
class Deactivator {



	/**
	 * Deactivate the plugin.
	 */
	public static function deactivate() {
		// Flush rewrite rules.
		flush_rewrite_rules();

		// Clear scheduled cron jobs (if any).
		wp_clear_scheduled_hook( 'iyoraa_daily_backup' );
		wp_clear_scheduled_hook( 'iyoraa_archive_audit_log' );
	}
}
