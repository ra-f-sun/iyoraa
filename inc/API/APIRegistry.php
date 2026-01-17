<?php
/**
 * REST API Registry
 *
 * Registers all REST API routes for the plugin.
 *
 * @package WPHelpZone\Iyoraa\API
 */

namespace WPHelpZone\Iyoraa\API;

use WPHelpZone\Iyoraa\Core\LicenseManager;
use WPHelpZone\Iyoraa\API\PatientAPI;
use WPHelpZone\Iyoraa\API\AppointmentAPI;

/**
 * API Registry class.
 *
 * Registers all REST API routes.
 */
class APIRegistry {



	/**
	 * Register all REST API routes.
	 */
	public static function register_routes() {
		// Status endpoint (for testing).
		self::register_status_endpoint();

		// Patient endpoints.
		PatientAPI::register_routes();

		// Appointment endpoints.
		AppointmentAPI::register_routes();

		// Future: Billing endpoints will be registered here in Phase 4.
	}

	/**
	 * Register status endpoint.
	 */
	private static function register_status_endpoint() {
		register_rest_route(
			'iyoraa/v1',
			'/status',
			[
				'methods'             => 'GET',
				'callback'            => [ self::class, 'get_status' ],
				'permission_callback' => [ self::class, 'check_permission' ],
			]
		);
	}

	/**
	 * Get plugin status.
	 *
	 * @return array Status information.
	 */
	public static function get_status() {
		return [
			'status'  => 'active',
			'version' => IYORAA_VERSION,
			'tier'    => LicenseManager::get_tier(),
		];
	}

	/**
	 * Check if user has permission to access API.
	 *
	 * @return bool Permission status.
	 */
	public static function check_permission() {
		return current_user_can( 'edit_posts' );
	}
}
