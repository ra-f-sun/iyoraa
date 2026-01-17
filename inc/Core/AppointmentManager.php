<?php
/**
 * Appointment Manager
 *
 * @package WPHelpZone\Iyoraa
 */

namespace WPHelpZone\Iyoraa\Core;

use WPHelpZone\Iyoraa\Validation\AppointmentValidator;
use WPHelpZone\Iyoraa\Exceptions\ValidationException;
use WPHelpZone\Iyoraa\Exceptions\DatabaseException;
use WPHelpZone\Iyoraa\Exceptions\NotFoundException;
use WPHelpZone\Iyoraa\Repositories\AppointmentRepository;
use WPHelpZone\Iyoraa\DTOs\AppointmentDTO;

/**
 * Appointment Manager class.
 *
 * Handles all appointment CRUD operations and business logic.
 */
class AppointmentManager extends Singleton {

	/**
	 * Appointment repository instance.
	 *
	 * @var AppointmentRepository
	 */
	private $repository;

	/**
	 * Initialize manager.
	 */
	protected function init() {
		$this->repository = new AppointmentRepository();
	}

	/**
	 * Create a new appointment.
	 *
	 * @param array $data Appointment data.
	 * @return AppointmentDTO|WP_Error Appointment DTO or error.
	 */
	public function create_appointment( $data ) {
		try {
			// Check appointment limit for FREE tier.
			if ( ! $this->can_add_appointment() ) {
				return new \WP_Error(
					'appointment_limit_reached',
					__( 'Monthly appointment limit reached. Upgrade to PRO for unlimited appointments.', 'iyoraa' ),
					[ 'status' => 403 ]
				);
			}

			// Validate appointment data.
			$errors = AppointmentValidator::validate( $data );
			if ( ! empty( $errors ) ) {
				return new \WP_Error(
					'validation_failed',
					implode( ' ', $errors ),
					[ 'status' => 400, 'errors' => $errors ]
				);
			}

			// Sanitize input data.
			$sanitized_data = AppointmentValidator::sanitize( $data );

			// Check for appointment conflicts.
			if ( $this->has_appointment_conflict( 
				$sanitized_data['doctor_id'], 
				$sanitized_data['appointment_date'], 
				$sanitized_data['appointment_time'], 
				$sanitized_data['duration'] 
			) ) {
				return new \WP_Error(
					'appointment_conflict',
					__( 'This time slot is already booked. Please choose a different time.', 'iyoraa' ),
					[ 'status' => 409 ]
				);
			}

			// Generate unique appointment ID.
			$sanitized_data['appointment_id'] = $this->generate_appointment_id();
			$sanitized_data['status']          = 'scheduled';
			$sanitized_data['reminder_sent']   = false;
			$sanitized_data['created_at']      = current_time( 'mysql' );
			$sanitized_data['updated_at']      = current_time( 'mysql' );

			// Create appointment using repository.
			$appointment_id = $this->repository->create( $sanitized_data );

			// Log activity.
			$this->log_activity( 'appointment_created', $appointment_id );

			// Return AppointmentDTO.
			$appointment_data = $this->repository->find( $appointment_id );
			return AppointmentDTO::from_array( $appointment_data );

		} catch ( DatabaseException $e ) {
			return new \WP_Error(
				'db_insert_error',
				__( 'Failed to create appointment.', 'iyoraa' ),
				[ 'status' => 500 ]
			);
		} catch ( \Exception $e ) {
			return new \WP_Error(
				'unexpected_error',
				$e->getMessage(),
				[ 'status' => 500 ]
			);
		}
	}

	/**
	 * Get an appointment by ID.
	 *
	 * @param int $id Appointment database ID.
	 * @return AppointmentDTO|WP_Error Appointment DTO or error.
	 */
	public function get_appointment( $id ) {
		try {
			$appointment_data = $this->repository->find( $id );
			
			if ( ! $appointment_data ) {
				return new \WP_Error(
					'appointment_not_found',
					__( 'Appointment not found.', 'iyoraa' ),
					[ 'status' => 404 ]
				);
			}

			return AppointmentDTO::from_array( $appointment_data );

		} catch ( \Exception $e ) {
			return new \WP_Error(
				'unexpected_error',
				$e->getMessage(),
				[ 'status' => 500 ]
			);
		}
	}

	/**
	 * Update an appointment.
	 *
	 * @param int   $id   Appointment database ID.
	 * @param array $data Appointment data to update.
	 * @return AppointmentDTO|WP_Error Updated appointment DTO or error.
	 */
	public function update_appointment( $id, $data ) {
		try {
			// Check if appointment exists.
			$existing = $this->repository->find( $id );
			if ( ! $existing ) {
				return new \WP_Error(
					'appointment_not_found',
					__( 'Appointment not found.', 'iyoraa' ),
					[ 'status' => 404 ]
				);
			}

			// Validate appointment data.
			$errors = AppointmentValidator::validate( $data );
			if ( ! empty( $errors ) ) {
				return new \WP_Error(
					'validation_failed',
					implode( ' ', $errors ),
					[ 'status' => 400, 'errors' => $errors ]
				);
			}

			// Sanitize input data.
			$sanitized_data = AppointmentValidator::sanitize( $data );

			// Check for appointment conflicts (exclude current appointment).
			if ( $this->has_appointment_conflict( 
				$sanitized_data['doctor_id'], 
				$sanitized_data['appointment_date'], 
				$sanitized_data['appointment_time'], 
				$sanitized_data['duration'],
				$id
			) ) {
				return new \WP_Error(
					'appointment_conflict',
					__( 'This time slot is already booked. Please choose a different time.', 'iyoraa' ),
					[ 'status' => 409 ]
				);
			}

			$sanitized_data['updated_at'] = current_time( 'mysql' );

			// Update appointment using repository.
			$this->repository->update( $id, $sanitized_data );

			// Log activity.
			$this->log_activity( 'appointment_updated', $id );

			// Return updated AppointmentDTO.
			$updated_data = $this->repository->find( $id );
			return AppointmentDTO::from_array( $updated_data );

		} catch ( DatabaseException $e ) {
			return new \WP_Error(
				'db_update_error',
				__( 'Failed to update appointment.', 'iyoraa' ),
				[ 'status' => 500 ]
			);
		} catch ( \Exception $e ) {
			return new \WP_Error(
				'unexpected_error',
				$e->getMessage(),
				[ 'status' => 500 ]
			);
		}
	}

	/**
	 * Cancel an appointment.
	 *
	 * @param int $id Appointment database ID.
	 * @return bool|WP_Error True on success or error.
	 */
	public function cancel_appointment( $id ) {
		try {
			// Check if appointment exists.
			$existing = $this->repository->find( $id );
			if ( ! $existing ) {
				return new \WP_Error(
					'appointment_not_found',
					__( 'Appointment not found.', 'iyoraa' ),
					[ 'status' => 404 ]
				);
			}

			// Update status to cancelled.
			$this->repository->update( $id, [
				'status'     => 'cancelled',
				'updated_at' => current_time( 'mysql' ),
			] );

			// Log activity.
			$this->log_activity( 'appointment_cancelled', $id );

			return true;

		} catch ( DatabaseException $e ) {
			return new \WP_Error(
				'db_update_error',
				__( 'Failed to cancel appointment.', 'iyoraa' ),
				[ 'status' => 500 ]
			);
		} catch ( \Exception $e ) {
			return new \WP_Error(
				'unexpected_error',
				$e->getMessage(),
				[ 'status' => 500 ]
			);
		}
	}

	/**
	 * Delete an appointment.
	 *
	 * @param int $id Appointment database ID.
	 * @return bool|WP_Error True on success or error.
	 */
	public function delete_appointment( $id ) {
		try {
			// Check if appointment exists.
			$existing = $this->repository->find( $id );
			if ( ! $existing ) {
				return new \WP_Error(
					'appointment_not_found',
					__( 'Appointment not found.', 'iyoraa' ),
					[ 'status' => 404 ]
				);
			}

			// Delete appointment.
			$this->repository->delete( $id );

			// Log activity.
			$this->log_activity( 'appointment_deleted', $id );

			return true;

		} catch ( DatabaseException $e ) {
			return new \WP_Error(
				'db_delete_error',
				__( 'Failed to delete appointment.', 'iyoraa' ),
				[ 'status' => 500 ]
			);
		} catch ( \Exception $e ) {
			return new \WP_Error(
				'unexpected_error',
				$e->getMessage(),
				[ 'status' => 500 ]
			);
		}
	}

	/**
	 * List appointments with pagination.
	 *
	 * @param array $args Query arguments.
	 * @return array Appointments data with pagination info.
	 */
	public function list_appointments( $args = [] ) {
		$defaults = [
			'page'       => 1,
			'per_page'   => 20,
			'status'     => null,
			'patient_id' => null,
			'doctor_id'  => null,
			'date_from'  => null,
			'date_to'    => null,
		];

		$args   = wp_parse_args( $args, $defaults );
		$offset = ( $args['page'] - 1 ) * $args['per_page'];

		// Get appointments based on filters.
		if ( $args['patient_id'] ) {
			$appointments = $this->repository->find_by_patient( $args['patient_id'], $args['per_page'], $offset );
		} elseif ( $args['doctor_id'] ) {
			$appointments = $this->repository->find_by_doctor( $args['doctor_id'], $args['per_page'], $offset );
		} elseif ( $args['status'] ) {
			$appointments = $this->repository->find_by_status( $args['status'], $args['per_page'], $offset );
		} elseif ( $args['date_from'] && $args['date_to'] ) {
			$appointments = $this->repository->find_by_date_range( $args['date_from'], $args['date_to'] );
		} else {
			$appointments = $this->repository->all( $args['per_page'], $offset );
		}

		// Convert to DTOs.
		$appointment_dtos = array_map( function( $appointment ) {
			return AppointmentDTO::from_array( $appointment )->to_array();
		}, $appointments );

		$total = $this->repository->count();

		return [
			'appointments' => $appointment_dtos,
			'total'        => $total,
			'page'         => (int) $args['page'],
			'per_page'     => (int) $args['per_page'],
			'total_pages'  => ceil( $total / $args['per_page'] ),
		];
	}

	/**
	 * Get today's appointments.
	 *
	 * @param int|null $doctor_id Optional doctor ID filter.
	 * @return array Array of today's appointments.
	 */
	public function get_today_appointments( $doctor_id = null ) {
		$appointments = $this->repository->get_today_appointments( $doctor_id );

		return array_map( function( $appointment ) {
			return AppointmentDTO::from_array( $appointment )->to_array();
		}, $appointments );
	}

	/**
	 * Get upcoming appointments.
	 *
	 * @param int $limit Maximum results.
	 * @return array Array of upcoming appointments.
	 */
	public function get_upcoming_appointments( $limit = 10 ) {
		$appointments = $this->repository->get_upcoming( $limit );

		return array_map( function( $appointment ) {
			return AppointmentDTO::from_array( $appointment )->to_array();
		}, $appointments );
	}

	/**
	 * Update appointment status.
	 *
	 * @param int    $id     Appointment ID.
	 * @param string $status New status.
	 * @return bool|WP_Error True on success or error.
	 */
	public function update_status( $id, $status ) {
		try {
			$valid_statuses = [ 'scheduled', 'confirmed', 'in-progress', 'completed', 'cancelled', 'no-show' ];
			
			if ( ! in_array( $status, $valid_statuses, true ) ) {
				return new \WP_Error(
					'invalid_status',
					__( 'Invalid appointment status.', 'iyoraa' ),
					[ 'status' => 400 ]
				);
			}

			$this->repository->update( $id, [
				'status'     => $status,
				'updated_at' => current_time( 'mysql' ),
			] );

			$this->log_activity( 'appointment_status_changed', $id );

			return true;

		} catch ( DatabaseException $e ) {
			return new \WP_Error(
				'db_update_error',
				__( 'Failed to update appointment status.', 'iyoraa' ),
				[ 'status' => 500 ]
			);
		}
	}

	/**
	 * Get appointment statistics.
	 *
	 * @return array Statistics array.
	 */
	public function get_statistics() {
		return $this->repository->get_statistics();
	}

	/**
	 * Check if can add more appointments (FREE tier limit).
	 *
	 * @return bool True if can add, false otherwise.
	 */
	private function can_add_appointment() {
		// Get current month's appointment count.
		$start_date = gmdate( 'Y-m-01' );
		$end_date   = gmdate( 'Y-m-t' );
		
		$current_count = count( $this->repository->find_by_date_range( $start_date, $end_date ) );
		$limit         = LicenseManager::get_resource_limit( 'appointments' );

		// -1 means unlimited (PRO tiers).
		if ( -1 === $limit ) {
			return true;
		}

		return $current_count < $limit;
	}

	/**
	 * Generate unique appointment ID.
	 *
	 * Format: APT-YYYY-####
	 *
	 * @return string Appointment ID.
	 */
	private function generate_appointment_id() {
		global $wpdb;

		$year   = gmdate( 'Y' );
		$prefix = 'APT-' . $year . '-';
		$table  = $wpdb->prefix . 'iyoraa_appointments';

		// Get the last appointment ID for this year.
		$last_id = $wpdb->get_var(
			$wpdb->prepare(
				"SELECT appointment_id FROM {$table} 
				WHERE appointment_id LIKE %s 
				ORDER BY id DESC LIMIT 1",
				$prefix . '%'
			)
		);

		if ( $last_id ) {
			// Extract number and increment.
			$number = (int) str_replace( $prefix, '', $last_id );
			++$number;
		} else {
			// First appointment of the year.
			$number = 1;
		}

		return $prefix . str_pad( $number, 4, '0', STR_PAD_LEFT );
	}

	/**
	 * Check if appointment has conflict.
	 *
	 * @param int    $doctor_id        Doctor ID.
	 * @param string $appointment_date Appointment date.
	 * @param string $appointment_time Appointment time.
	 * @param int    $duration         Duration in minutes.
	 * @param int    $exclude_id       Appointment ID to exclude (for updates).
	 * @return bool True if conflict exists, false otherwise.
	 */
	private function has_appointment_conflict( $doctor_id, $appointment_date, $appointment_time, $duration, $exclude_id = null ) {
		return $this->repository->has_conflict( $doctor_id, $appointment_date, $appointment_time, $duration, $exclude_id );
	}

	/**
	 * Log appointment activity to audit log.
	 *
	 * @param string $action         Action type.
	 * @param int    $appointment_id Appointment ID.
	 */
	private function log_activity( $action, $appointment_id ) {
		global $wpdb;

		$user_id = get_current_user_id();
		$table   = $wpdb->prefix . 'iyoraa_audit_log';

		$wpdb->query(
			$wpdb->prepare(
				"INSERT INTO {$table} 
				(user_id, action, entity_type, entity_id, ip_address, user_agent, created_at) 
				VALUES (%d, %s, %s, %d, %s, %s, %s)",
				$user_id,
				$action,
				'appointment',
				$appointment_id,
				$this->get_client_ip(),
				$this->get_user_agent(),
				current_time( 'mysql' )
			)
		);
	}

	/**
	 * Get sanitized client IP address.
	 *
	 * @return string IP address.
	 */
	private function get_client_ip() {
		$ip = '';

		if ( isset( $_SERVER['REMOTE_ADDR'] ) ) {
			$ip = filter_var(
				wp_unslash( $_SERVER['REMOTE_ADDR'] ),
				FILTER_VALIDATE_IP
			);
		}

		return $ip ? $ip : '0.0.0.0';
	}

	/**
	 * Get sanitized user agent string.
	 *
	 * @return string User agent.
	 */
	private function get_user_agent() {
		if ( ! isset( $_SERVER['HTTP_USER_AGENT'] ) ) {
			return '';
		}

		return substr(
			sanitize_text_field( wp_unslash( $_SERVER['HTTP_USER_AGENT'] ) ),
			0,
			255
		);
	}
}
