<?php

/**
 * Admin Page Handlers
 *
 * Handles rendering of admin pages.
 *
 * @package WPHelpZone\Iyoraa\Admin
 */

namespace WPHelpZone\Iyoraa\Admin;

use WPHelpZone\Iyoraa\Core\LicenseManager;
use WPHelpZone\Iyoraa\Helpers\TemplateLoader;

/**
 * Admin Pages class.
 *
 * Handles rendering of admin pages.
 */
class AdminPages {


	/**
	 * Render main admin page (React app mount point).
	 */
	public static function render_main_page() {
		TemplateLoader::load( 'admin/main-page.php' );
	}

	/**
	 * Render license settings page.
	 */
	public static function render_license_page() {
		// Handle form submission.
		self::handle_license_form_submission();

		// Prepare template data.
		$data = self::get_license_page_data();

		// Display notices.
		settings_errors( 'iyoraa_messages' );

		// Load template.
		TemplateLoader::load( 'admin/license-settings.php', $data );
	}

	/**
	 * Handle license form submission.
	 */
	private static function handle_license_form_submission() {
		if ( ! isset( $_POST['iyoraa_tier'] ) ) {
			return;
		}

		if ( ! check_admin_referer( 'iyoraa_set_tier' ) ) {
			return;
		}

		$tier = sanitize_text_field( $_POST['iyoraa_tier'] );

		if ( LicenseManager::set_tier( $tier ) ) {
			add_settings_error(
				'iyoraa_messages',
				'iyoraa_message',
				__( 'Tier updated successfully!', 'iyoraa' ),
				'success'
			);
		}
	}

	/**
	 * Get data for license settings page.
	 *
	 * @return array Template data.
	 */
	private static function get_license_page_data() {
		return [
			'current_tier' => LicenseManager::get_tier(),
			'tier_name'    => LicenseManager::get_tier_name(),
		];
	}
}
