<?php
/**
 * Patient REST API
 *
 * @package WPHelpZone\Iyoraa
 */

namespace WPHelpZone\Iyoraa\API;

use WPHelpZone\Iyoraa\Core\PatientManager;

/**
 * Patient API class.
 *
 * Registers REST API endpoints for patient operations.
 */
class PatientAPI {



	/**
	 * Patient Manager instance.
	 *
	 * @var PatientManager
	 */
	private static $patient_manager;

	/**
	 * Register patient routes.
	 */
	public static function register_routes() {
		self::$patient_manager = PatientManager::instance();

		// Create patient.
		register_rest_route(
			'iyoraa/v1',
			'/patients',
			[
				'methods'             => 'POST',
				'callback'            => [ self::class, 'create_patient' ],
				'permission_callback' => [ self::class, 'check_permission' ],
				'args'                => self::get_patient_schema(),
			]
		);

		// List patients.
		register_rest_route(
			'iyoraa/v1',
			'/patients',
			[
				'methods'             => 'GET',
				'callback'            => [ self::class, 'list_patients' ],
				'permission_callback' => [ self::class, 'check_read_permission' ],
				'args'                => [
					'page'     => [
						'default'           => 1,
						'sanitize_callback' => 'absint',
					],
					'per_page' => [
						'default'           => 20,
						'sanitize_callback' => 'absint',
					],
				],
			]
		);

		// Get single patient.
		register_rest_route(
			'iyoraa/v1',
			'/patients/(?P<id>\d+)',
			[
				'methods'             => 'GET',
				'callback'            => [ self::class, 'get_patient' ],
				'permission_callback' => [ self::class, 'check_read_permission' ],
			]
		);

		// Update patient.
		register_rest_route(
			'iyoraa/v1',
			'/patients/(?P<id>\d+)',
			[
				'methods'             => 'PUT',
				'callback'            => [ self::class, 'update_patient' ],
				'permission_callback' => [ self::class, 'check_permission' ],
				'args'                => self::get_patient_schema(),
			]
		);

		// Delete patient.
		register_rest_route(
			'iyoraa/v1',
			'/patients/(?P<id>\d+)',
			[
				'methods'             => 'DELETE',
				'callback'            => [ self::class, 'delete_patient' ],
				'permission_callback' => [ self::class, 'check_permission' ],
			]
		);

		// Search patients.
		register_rest_route(
			'iyoraa/v1',
			'/patients/search',
			[
				'methods'             => 'GET',
				'callback'            => [ self::class, 'search_patients' ],
				'permission_callback' => [ self::class, 'check_read_permission' ],
				'args'                => [
					'q'        => [
						'required'          => true,
						'sanitize_callback' => 'sanitize_text_field',
					],
					'page'     => [
						'default'           => 1,
						'sanitize_callback' => 'absint',
					],
					'per_page' => [
						'default'           => 20,
						'sanitize_callback' => 'absint',
					],
				],
			]
		);
	}

	/**
	 * Create patient endpoint.
	 *
	 * @param \WP_REST_Request $request Request object.
	 * @return \WP_REST_Response|\WP_Error Response or error.
	 */
	public static function create_patient( $request ) {
		$data = $request->get_json_params();

		$result = self::$patient_manager->create_patient( $data );

		if ( is_wp_error( $result ) ) {
			return $result;
		}

		return new \WP_REST_Response(
			[
				'success' => true,
				'message' => __( 'Patient created successfully.', 'iyoraa' ),
				'data'    => $result,
			],
			201
		);
	}

	/**
	 * List patients endpoint.
	 *
	 * @param \WP_REST_Request $request Request object.
	 * @return \WP_REST_Response Response.
	 */
	public static function list_patients( $request ) {
		$args = [
			'page'     => $request->get_param( 'page' ),
			'per_page' => $request->get_param( 'per_page' ),
		];

		$result = self::$patient_manager->list_patients( $args );

		return new \WP_REST_Response(
			[
				'success' => true,
				'data'    => $result,
			],
			200
		);
	}

	/**
	 * Get single patient endpoint.
	 *
	 * @param \WP_REST_Request $request Request object.
	 * @return \WP_REST_Response|\WP_Error Response or error.
	 */
	public static function get_patient( $request ) {
		$id = $request->get_param( 'id' );

		$result = self::$patient_manager->get_patient( $id );

		if ( is_wp_error( $result ) ) {
			return $result;
		}

		return new \WP_REST_Response(
			[
				'success' => true,
				'data'    => $result,
			],
			200
		);
	}

	/**
	 * Update patient endpoint.
	 *
	 * @param \WP_REST_Request $request Request object.
	 * @return \WP_REST_Response|\WP_Error Response or error.
	 */
	public static function update_patient( $request ) {
		$id   = $request->get_param( 'id' );
		$data = $request->get_json_params();

		$result = self::$patient_manager->update_patient( $id, $data );

		if ( is_wp_error( $result ) ) {
			return $result;
		}

		return new \WP_REST_Response(
			[
				'success' => true,
				'message' => __( 'Patient updated successfully.', 'iyoraa' ),
				'data'    => $result,
			],
			200
		);
	}

	/**
	 * Delete patient endpoint.
	 *
	 * @param \WP_REST_Request $request Request object.
	 * @return \WP_REST_Response|\WP_Error Response or error.
	 */
	public static function delete_patient( $request ) {
		$id = $request->get_param( 'id' );

		$result = self::$patient_manager->delete_patient( $id );

		if ( is_wp_error( $result ) ) {
			return $result;
		}

		return new \WP_REST_Response(
			[
				'success' => true,
				'message' => __( 'Patient deleted successfully.', 'iyoraa' ),
			],
			200
		);
	}

	/**
	 * Search patients endpoint.
	 *
	 * @param \WP_REST_Request $request Request object.
	 * @return \WP_REST_Response Response.
	 */
	public static function search_patients( $request ) {
		$query = $request->get_param( 'q' );
		$args  = [
			'page'     => $request->get_param( 'page' ),
			'per_page' => $request->get_param( 'per_page' ),
		];

		$result = self::$patient_manager->search_patients( $query, $args );

		return new \WP_REST_Response(
			[
				'success' => true,
				'data'    => $result,
			],
			200
		);
	}

	/**
	 * Check permission callback.
	 *
	 * @return bool True if user can edit, false otherwise.
	 */
	public static function check_permission() {
		return current_user_can( 'edit_posts' );
	}

	/**
	 * Check read permission callback.
	 *
	 * @return bool True if user can read, false otherwise.
	 */
	public static function check_read_permission() {
		return current_user_can( 'read' );
	}

	/**
	 * Get patient schema for validation.
	 *
	 * @return array Schema.
	 */
	private static function get_patient_schema() {
		return [
			'full_name'               => [
				'required'          => true,
				'sanitize_callback' => 'sanitize_text_field',
			],
			'age'                     => [
				'required'          => true,
				'sanitize_callback' => 'absint',
			],
			'gender'                  => [
				'required'          => true,
				'sanitize_callback' => 'sanitize_text_field',
			],
			'phone'                   => [
				'required'          => true,
				'sanitize_callback' => 'sanitize_text_field',
			],
			'email'                   => [
				'sanitize_callback' => 'sanitize_email',
			],
			'address'                 => [
				'sanitize_callback' => 'sanitize_textarea_field',
			],
			'blood_group'             => [
				'sanitize_callback' => 'sanitize_text_field',
			],
			'emergency_contact_name'  => [
				'sanitize_callback' => 'sanitize_text_field',
			],
			'emergency_contact_phone' => [
				'sanitize_callback' => 'sanitize_text_field',
			],
			'medical_history'         => [
				'sanitize_callback' => 'sanitize_textarea_field',
			],
		];
	}
}
