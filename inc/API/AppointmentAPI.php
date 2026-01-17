<?php
/**
 * Appointment REST API
 *
 * @package WPHelpZone\Iyoraa
 */

namespace WPHelpZone\Iyoraa\API;

use WPHelpZone\Iyoraa\Core\AppointmentManager;
use WPHelpZone\Iyoraa\Security\RateLimiter;

/**
 * Appointment API class.
 *
 * Registers REST API endpoints for appointment operations.
 */
class AppointmentAPI {

	/**
	 * Appointment Manager instance.
	 *
	 * @var AppointmentManager
	 */
	private static $appointment_manager;

	/**
	 * Register appointment routes.
	 */
	public static function register_routes() {
		self::$appointment_manager = AppointmentManager::instance();

		// Create appointment.
		register_rest_route(
			'iyoraa/v1',
			'/appointments',
			[
				'methods'             => 'POST',
				'callback'            => [ self::class, 'create_appointment' ],
				'permission_callback' => [ self::class, 'check_permission' ],
				'args'                => self::get_appointment_schema(),
			]
		);

		// List appointments.
		register_rest_route(
			'iyoraa/v1',
			'/appointments',
			[
				'methods'             => 'GET',
				'callback'            => [ self::class, 'list_appointments' ],
				'permission_callback' => [ self::class, 'check_read_permission' ],
				'args'                => [
					'page'       => [
						'default'           => 1,
						'sanitize_callback' => 'absint',
					],
					'per_page'   => [
						'default'           => 20,
						'sanitize_callback' => 'absint',
					],
					'status'     => [
						'sanitize_callback' => 'sanitize_text_field',
					],
					'patient_id' => [
						'sanitize_callback' => 'absint',
					],
					'doctor_id'  => [
						'sanitize_callback' => 'absint',
					],
					'date_from'  => [
						'sanitize_callback' => 'sanitize_text_field',
					],
					'date_to'    => [
						'sanitize_callback' => 'sanitize_text_field',
					],
				],
			]
		);

		// Get single appointment.
		register_rest_route(
			'iyoraa/v1',
			'/appointments/(?P<id>\d+)',
			[
				'methods'             => 'GET',
				'callback'            => [ self::class, 'get_appointment' ],
				'permission_callback' => [ self::class, 'check_read_permission' ],
			]
		);

		// Update appointment.
		register_rest_route(
			'iyoraa/v1',
			'/appointments/(?P<id>\d+)',
			[
				'methods'             => 'PUT',
				'callback'            => [ self::class, 'update_appointment' ],
				'permission_callback' => [ self::class, 'check_permission' ],
				'args'                => self::get_appointment_schema(),
			]
		);

		// Delete appointment.
		register_rest_route(
			'iyoraa/v1',
			'/appointments/(?P<id>\d+)',
			[
				'methods'             => 'DELETE',
				'callback'            => [ self::class, 'delete_appointment' ],
				'permission_callback' => [ self::class, 'check_permission' ],
			]
		);

		// Cancel appointment.
		register_rest_route(
			'iyoraa/v1',
			'/appointments/(?P<id>\d+)/cancel',
			[
				'methods'             => 'POST',
				'callback'            => [ self::class, 'cancel_appointment' ],
				'permission_callback' => [ self::class, 'check_permission' ],
			]
		);

		// Update appointment status.
		register_rest_route(
			'iyoraa/v1',
			'/appointments/(?P<id>\d+)/status',
			[
				'methods'             => 'PUT',
				'callback'            => [ self::class, 'update_status' ],
				'permission_callback' => [ self::class, 'check_permission' ],
				'args'                => [
					'status' => [
						'required'          => true,
						'sanitize_callback' => 'sanitize_text_field',
					],
				],
			]
		);

		// Get today's appointments.
		register_rest_route(
			'iyoraa/v1',
			'/appointments/today',
			[
				'methods'             => 'GET',
				'callback'            => [ self::class, 'get_today_appointments' ],
				'permission_callback' => [ self::class, 'check_read_permission' ],
				'args'                => [
					'doctor_id' => [
						'sanitize_callback' => 'absint',
					],
				],
			]
		);

		// Get upcoming appointments.
		register_rest_route(
			'iyoraa/v1',
			'/appointments/upcoming',
			[
				'methods'             => 'GET',
				'callback'            => [ self::class, 'get_upcoming_appointments' ],
				'permission_callback' => [ self::class, 'check_read_permission' ],
				'args'                => [
					'limit' => [
						'default'           => 10,
						'sanitize_callback' => 'absint',
					],
				],
			]
		);

		// Get appointment statistics.
		register_rest_route(
			'iyoraa/v1',
			'/appointments/statistics',
			[
				'methods'             => 'GET',
				'callback'            => [ self::class, 'get_statistics' ],
				'permission_callback' => [ self::class, 'check_read_permission' ],
			]
		);

		// Calendar view - get appointments by date range.
		register_rest_route(
			'iyoraa/v1',
			'/appointments/calendar',
			[
				'methods'             => 'GET',
				'callback'            => [ self::class, 'get_calendar_appointments' ],
				'permission_callback' => [ self::class, 'check_read_permission' ],
				'args'                => [
					'start_date' => [
						'required'          => true,
						'sanitize_callback' => 'sanitize_text_field',
					],
					'end_date'   => [
						'required'          => true,
						'sanitize_callback' => 'sanitize_text_field',
					],
					'doctor_id'  => [
						'sanitize_callback' => 'absint',
					],
				],
			]
		);
	}

	/**
	 * Create appointment endpoint.
	 *
	 * @param \WP_REST_Request $request Request object.
	 * @return \WP_REST_Response|\WP_Error Response or error.
	 */
	public static function create_appointment( $request ) {
		$data = $request->get_json_params();

		$result = self::$appointment_manager->create_appointment( $data );

		if ( is_wp_error( $result ) ) {
			return $result;
		}

		// Convert DTO to array for API response.
		$appointment_data = is_object( $result ) && method_exists( $result, 'to_array' )
			? $result->to_array()
			: $result;

		return new \WP_REST_Response(
			[
				'success' => true,
				'message' => __( 'Appointment created successfully.', 'iyoraa' ),
				'data'    => $appointment_data,
			],
			201
		);
	}

	/**
	 * List appointments endpoint.
	 *
	 * @param \WP_REST_Request $request Request object.
	 * @return \WP_REST_Response Response.
	 */
	public static function list_appointments( $request ) {
		$args = [
			'page'       => $request->get_param( 'page' ),
			'per_page'   => $request->get_param( 'per_page' ),
			'status'     => $request->get_param( 'status' ),
			'patient_id' => $request->get_param( 'patient_id' ),
			'doctor_id'  => $request->get_param( 'doctor_id' ),
			'date_from'  => $request->get_param( 'date_from' ),
			'date_to'    => $request->get_param( 'date_to' ),
		];

		$result = self::$appointment_manager->list_appointments( $args );

		return new \WP_REST_Response(
			[
				'success' => true,
				'data'    => $result,
			],
			200
		);
	}

	/**
	 * Get single appointment endpoint.
	 *
	 * @param \WP_REST_Request $request Request object.
	 * @return \WP_REST_Response|\WP_Error Response or error.
	 */
	public static function get_appointment( $request ) {
		$id = $request->get_param( 'id' );

		$result = self::$appointment_manager->get_appointment( $id );

		if ( is_wp_error( $result ) ) {
			return $result;
		}

		// Convert DTO to array for API response.
		$appointment_data = is_object( $result ) && method_exists( $result, 'to_array' )
			? $result->to_array()
			: $result;

		return new \WP_REST_Response(
			[
				'success' => true,
				'data'    => $appointment_data,
			],
			200
		);
	}

	/**
	 * Update appointment endpoint.
	 *
	 * @param \WP_REST_Request $request Request object.
	 * @return \WP_REST_Response|\WP_Error Response or error.
	 */
	public static function update_appointment( $request ) {
		$id   = $request->get_param( 'id' );
		$data = $request->get_json_params();

		$result = self::$appointment_manager->update_appointment( $id, $data );

		if ( is_wp_error( $result ) ) {
			return $result;
		}

		// Convert DTO to array for API response.
		$appointment_data = is_object( $result ) && method_exists( $result, 'to_array' )
			? $result->to_array()
			: $result;

		return new \WP_REST_Response(
			[
				'success' => true,
				'message' => __( 'Appointment updated successfully.', 'iyoraa' ),
				'data'    => $appointment_data,
			],
			200
		);
	}

	/**
	 * Delete appointment endpoint.
	 *
	 * @param \WP_REST_Request $request Request object.
	 * @return \WP_REST_Response|\WP_Error Response or error.
	 */
	public static function delete_appointment( $request ) {
		$id = $request->get_param( 'id' );

		$result = self::$appointment_manager->delete_appointment( $id );

		if ( is_wp_error( $result ) ) {
			return $result;
		}

		return new \WP_REST_Response(
			[
				'success' => true,
				'message' => __( 'Appointment deleted successfully.', 'iyoraa' ),
			],
			200
		);
	}

	/**
	 * Cancel appointment endpoint.
	 *
	 * @param \WP_REST_Request $request Request object.
	 * @return \WP_REST_Response|\WP_Error Response or error.
	 */
	public static function cancel_appointment( $request ) {
		$id = $request->get_param( 'id' );

		$result = self::$appointment_manager->cancel_appointment( $id );

		if ( is_wp_error( $result ) ) {
			return $result;
		}

		return new \WP_REST_Response(
			[
				'success' => true,
				'message' => __( 'Appointment cancelled successfully.', 'iyoraa' ),
			],
			200
		);
	}

	/**
	 * Update appointment status endpoint.
	 *
	 * @param \WP_REST_Request $request Request object.
	 * @return \WP_REST_Response|\WP_Error Response or error.
	 */
	public static function update_status( $request ) {
		$id     = $request->get_param( 'id' );
		$status = $request->get_param( 'status' );

		$result = self::$appointment_manager->update_status( $id, $status );

		if ( is_wp_error( $result ) ) {
			return $result;
		}

		return new \WP_REST_Response(
			[
				'success' => true,
				'message' => __( 'Appointment status updated successfully.', 'iyoraa' ),
			],
			200
		);
	}

	/**
	 * Get today's appointments endpoint.
	 *
	 * @param \WP_REST_Request $request Request object.
	 * @return \WP_REST_Response Response.
	 */
	public static function get_today_appointments( $request ) {
		$doctor_id = $request->get_param( 'doctor_id' );

		$appointments = self::$appointment_manager->get_today_appointments( $doctor_id );

		return new \WP_REST_Response(
			[
				'success' => true,
				'data'    => $appointments,
			],
			200
		);
	}

	/**
	 * Get upcoming appointments endpoint.
	 *
	 * @param \WP_REST_Request $request Request object.
	 * @return \WP_REST_Response Response.
	 */
	public static function get_upcoming_appointments( $request ) {
		$limit = $request->get_param( 'limit' );

		$appointments = self::$appointment_manager->get_upcoming_appointments( $limit );

		return new \WP_REST_Response(
			[
				'success' => true,
				'data'    => $appointments,
			],
			200
		);
	}

	/**
	 * Get appointment statistics endpoint.
	 *
	 * @param \WP_REST_Request $request Request object.
	 * @return \WP_REST_Response Response.
	 */
	public static function get_statistics( $request ) {
		$statistics = self::$appointment_manager->get_statistics();

		return new \WP_REST_Response(
			[
				'success' => true,
				'data'    => $statistics,
			],
			200
		);
	}

	/**
	 * Get calendar appointments endpoint.
	 *
	 * @param \WP_REST_Request $request Request object.
	 * @return \WP_REST_Response Response.
	 */
	public static function get_calendar_appointments( $request ) {
		$start_date = $request->get_param( 'start_date' );
		$end_date   = $request->get_param( 'end_date' );
		$doctor_id  = $request->get_param( 'doctor_id' );

		$args = [
			'date_from' => $start_date,
			'date_to'   => $end_date,
		];

		if ( $doctor_id ) {
			$args['doctor_id'] = $doctor_id;
		}

		$result = self::$appointment_manager->list_appointments( $args );

		// Format for calendar view (FullCalendar.js compatible).
		$calendar_events = array_map(
			function ( $appointment ) {
				return [
					'id'              => $appointment['id'],
					'title'           => $appointment['purpose'] ?? __( 'Appointment', 'iyoraa' ),
					'start'           => $appointment['appointment_date'] . 'T' . $appointment['appointment_time'],
					'end'             => self::calculate_end_time( $appointment['appointment_date'], $appointment['appointment_time'], $appointment['duration'] ),
					'backgroundColor' => self::get_status_color( $appointment['status'] ),
					'borderColor'     => self::get_status_color( $appointment['status'] ),
					'extendedProps'   => [
						'appointment_id' => $appointment['appointment_id'],
						'patient_id'     => $appointment['patient_id'],
						'doctor_id'      => $appointment['doctor_id'],
						'status'         => $appointment['status'],
						'notes'          => $appointment['notes'] ?? '',
					],
				];
			},
			$result['appointments']
		);

		return new \WP_REST_Response(
			[
				'success' => true,
				'data'    => $calendar_events,
			],
			200
		);
	}

	/**
	 * Check permission callback.
	 *
	 * Verifies user capability and rate limiting.
	 *
	 * @param \WP_REST_Request $request Request object.
	 * @return bool|\WP_Error True if allowed, WP_Error if denied.
	 */
	public static function check_permission( $request ) {
		// Check user capability.
		if ( ! current_user_can( 'edit_posts' ) ) {
			return new \WP_Error(
				'rest_forbidden',
				__( 'You do not have permission to perform this action.', 'iyoraa' ),
				[ 'status' => 403 ]
			);
		}

		// Check rate limit.
		$rate_limiter = new RateLimiter( 60, 60 ); // 60 requests per minute.
		$identifier   = $rate_limiter->get_identifier();

		if ( ! $rate_limiter->check_limit( $identifier ) ) {
			$reset_time = $rate_limiter->get_reset_time( $identifier );

			return new \WP_Error(
				'rate_limit_exceeded',
				sprintf(
					/* translators: %d: seconds until rate limit resets */
					__( 'Rate limit exceeded. Please try again in %d seconds.', 'iyoraa' ),
					$reset_time
				),
				[
					'status'     => 429,
					'reset_time' => $reset_time,
					'remaining'  => 0,
				]
			);
		}

		// Add rate limit headers to response.
		add_filter(
			'rest_post_dispatch',
			function ( $result, $server, $request ) use ( $rate_limiter, $identifier ) {
				$result->header( 'X-RateLimit-Limit', 60 );
				$result->header( 'X-RateLimit-Remaining', $rate_limiter->get_remaining_requests( $identifier ) );
				$result->header( 'X-RateLimit-Reset', time() + $rate_limiter->get_reset_time( $identifier ) );
				return $result;
			},
			10,
			3
		);

		return true;
	}

	/**
	 * Check read permission callback.
	 *
	 * @param \WP_REST_Request $request Request object.
	 * @return bool|\WP_Error True if allowed, WP_Error if denied.
	 */
	public static function check_read_permission( $request ) {
		// Check user capability - must be logged in and have edit_posts.
		if ( ! is_user_logged_in() || ! current_user_can( 'edit_posts' ) ) {
			return new \WP_Error(
				'rest_forbidden',
				__( 'You do not have permission to view this content.', 'iyoraa' ),
				[ 'status' => 403 ]
			);
		}

		// Apply rate limiting for read operations too (more lenient).
		$rate_limiter = new RateLimiter( 120, 60 ); // 120 requests per minute for reads.
		$identifier   = $rate_limiter->get_identifier();

		if ( ! $rate_limiter->check_limit( $identifier ) ) {
			$reset_time = $rate_limiter->get_reset_time( $identifier );

			return new \WP_Error(
				'rate_limit_exceeded',
				sprintf(
					/* translators: %d: seconds until rate limit resets */
					__( 'Rate limit exceeded. Please try again in %d seconds.', 'iyoraa' ),
					$reset_time
				),
				[ 'status' => 429 ]
			);
		}

		return true;
	}

	/**
	 * Get appointment schema for validation.
	 *
	 * @return array Schema.
	 */
	private static function get_appointment_schema() {
		return [
			'patient_id'       => [
				'required'          => true,
				'sanitize_callback' => 'absint',
			],
			'doctor_id'        => [
				'required'          => true,
				'sanitize_callback' => 'absint',
			],
			'appointment_date' => [
				'required'          => true,
				'sanitize_callback' => 'sanitize_text_field',
			],
			'appointment_time' => [
				'required'          => true,
				'sanitize_callback' => 'sanitize_text_field',
			],
			'duration'         => [
				'default'           => 30,
				'sanitize_callback' => 'absint',
			],
			'appointment_type' => [
				'default'           => 'consultation',
				'sanitize_callback' => 'sanitize_text_field',
			],
			'purpose'          => [
				'sanitize_callback' => 'sanitize_textarea_field',
			],
			'notes'            => [
				'sanitize_callback' => 'sanitize_textarea_field',
			],
		];
	}

	/**
	 * Calculate end time for appointment.
	 *
	 * @param string $date     Date string.
	 * @param string $time     Time string.
	 * @param int    $duration Duration in minutes.
	 * @return string ISO 8601 formatted end time.
	 */
	private static function calculate_end_time( $date, $time, $duration ) {
		$datetime = \DateTime::createFromFormat( 'Y-m-d H:i:s', $date . ' ' . $time );
		if ( ! $datetime ) {
			return '';
		}

		$datetime->modify( "+{$duration} minutes" );
		return $datetime->format( 'Y-m-d\TH:i:s' );
	}

	/**
	 * Get color code for appointment status.
	 *
	 * @param string $status Status.
	 * @return string Color code.
	 */
	private static function get_status_color( $status ) {
		$colors = [
			'scheduled'   => '#3788d8',
			'confirmed'   => '#28a745',
			'in-progress' => '#ffc107',
			'completed'   => '#6c757d',
			'cancelled'   => '#dc3545',
			'no-show'     => '#6c757d',
		];

		return $colors[ $status ] ?? '#3788d8';
	}
}
