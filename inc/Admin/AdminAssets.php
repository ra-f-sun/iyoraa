<?php

/**
 * Admin Assets Manager
 *
 * Handles enqueuing of admin scripts and styles.
 *
 * @package WPHelpZone\Iyoraa\Admin
 */

namespace WPHelpZone\Iyoraa\Admin;

use WPHelpZone\Iyoraa\Core\LicenseManager;

/**
 * Admin Assets class.
 *
 * Handles enqueuing of admin scripts and styles.
 */
class AdminAssets {


	/**
	 * Enqueue admin scripts and styles.
	 *
	 * @param string $hook Current admin page hook.
	 */
	public static function enqueue( $hook ) {
		// Only load on Iyoraa admin pages.
		if ( ! self::is_iyoraa_page( $hook ) ) {
			return;
		}

		self::enqueue_react_app();
	}

	/**
	 * Check if current page is an Iyoraa admin page.
	 *
	 * @param string $hook Page hook.
	 * @return bool True if Iyoraa page.
	 */
	private static function is_iyoraa_page( $hook ) {
		return strpos( $hook, 'iyoraa' ) !== false;
	}

	/**
	 * Enqueue React application.
	 */
	private static function enqueue_react_app() {
		$asset_file = IYORAA_PATH . 'assets/dist/index.asset.php';

		if ( ! file_exists( $asset_file ) ) {
			return;
		}

		$asset = require $asset_file;

		// Enqueue script.
		wp_enqueue_script(
			'iyoraa-app',
			IYORAA_URL . 'assets/dist/index.js',
			$asset['dependencies'],
			$asset['version'],
			true
		);

		// Enqueue styles.
		wp_enqueue_style(
			'iyoraa-app',
			IYORAA_URL . 'assets/dist/index.css',
			[],
			$asset['version']
		);

		// Localize script data.
		self::localize_script_data();
	}

	/**
	 * Pass data to JavaScript.
	 */
	private static function localize_script_data() {
		wp_localize_script(
			'iyoraa-app',
			'iyoraaData',
			[
				'apiUrl'   => rest_url( 'iyoraa/v1' ),
				'nonce'    => wp_create_nonce( 'wp_rest' ),
				'tier'     => LicenseManager::get_tier(),
				'tierName' => LicenseManager::get_tier_name(),
			]
		);
	}
}
