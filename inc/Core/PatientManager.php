<?php
/**
 * Patient Manager
 *
 * @package WPHelpZone\Iyoraa
 */

namespace WPHelpZone\Iyoraa\Core;

use WPHelpZone\Iyoraa\Validation\PatientValidator;
use WPHelpZone\Iyoraa\Exceptions\ValidationException;
use WPHelpZone\Iyoraa\Exceptions\DatabaseException;
use WPHelpZone\Iyoraa\Exceptions\NotFoundException;
use WPHelpZone\Iyoraa\Repositories\PatientRepository;
use WPHelpZone\Iyoraa\DTOs\PatientDTO;

/**
 * Patient Manager class.
 *
 * Handles all patient CRUD operations and business logic.
 */
class PatientManager extends Singleton {

	/**
	 * Patient repository instance.
	 *
	 * @var PatientRepository
	 */
	private $repository;

	/**
	 * Initialize manager.
	 */
	protected function init() {
		$this->repository = new PatientRepository();
	}

	/**
	 * Get patients table name.
	 *
	 * @return string Table name with prefix.
	 */
	private function get_table() {
		return Database::instance()->get_table_name( Database::PATIENTS_TABLE );
	}

	/**
	 * Get audit log table name.
	 *
	 * @return string Table name with prefix.
	 */
	private function get_audit_table() {
		return Database::instance()->get_table_name( Database::AUDIT_LOG_TABLE );
	}

	/**
	 * Create a new patient.
	 *
	 * @param array $data Patient data.
	 * @return PatientDTO|WP_Error Patient DTO or error.
	 */
	public function create_patient( $data ) {
		try {
			// Check patient limit for FREE tier.
			if ( ! $this->can_add_patient() ) {
				return new \WP_Error(
					'patient_limit_reached',
					__( 'Patient limit reached. Upgrade to PRO to add more patients.', 'iyoraa' ),
					[ 'status' => 403 ]
				);
			}

			// Validate patient data using PatientValidator.
			$errors = PatientValidator::validate( $data );
			if ( ! empty( $errors ) ) {
				return new \WP_Error(
					'validation_failed',
					implode( ' ', $errors ),
					[ 'status' => 400, 'errors' => $errors ]
				);
			}

			// Sanitize input data using PatientValidator.
			$sanitized_data = PatientValidator::sanitize( $data );

			// Check phone uniqueness (business logic).
			if ( ! $this->is_phone_unique( $sanitized_data['phone'] ) ) {
				return new \WP_Error(
					'duplicate_phone',
					__( 'This phone number is already registered.', 'iyoraa' ),
					[ 'status' => 400 ]
				);
			}

			// Generate unique patient ID.
			$sanitized_data['patient_id'] = $this->generate_patient_id();
			$sanitized_data['status']     = 'active';
			$sanitized_data['created_at'] = current_time( 'mysql' );
			$sanitized_data['updated_at'] = current_time( 'mysql' );

			// Create patient using repository.
			$patient_id = $this->repository->create( $sanitized_data );

			// Log activity.
			$this->log_activity( 'patient_created', $patient_id );

			// Return PatientDTO.
			$patient_data = $this->repository->find( $patient_id );
			return PatientDTO::from_array( $patient_data );

		} catch ( DatabaseException $e ) {
			return new \WP_Error(
				'db_insert_error',
				__( 'Failed to create patient.', 'iyoraa' ),
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
	 * Get a patient by ID.
	 *
	 * @param int $id Patient database ID.
	 * @return PatientDTO|WP_Error Patient DTO or error.
	 */
	public function get_patient( $id ) {
		try {
			$patient_data = $this->repository->find( $id );
			
			if ( ! $patient_data ) {
				return new \WP_Error(
					'patient_not_found',
					__( 'Patient not found.', 'iyoraa' ),
					[ 'status' => 404 ]
				);
			}

			return PatientDTO::from_array( $patient_data );

		} catch ( \Exception $e ) {
			return new \WP_Error(
				'unexpected_error',
				$e->getMessage(),
				[ 'status' => 500 ]
			);
		}
	}

	/**
	 * Update a patient.
	 *
	 * @param int   $id   Patient database ID.
	 * @param array $data Patient data to update.
	 * @return PatientDTO|WP_Error Updated patient DTO or error.
	 */
	public function update_patient( $id, $data ) {
		try {
			// Check if patient exists.
			$existing = $this->repository->find( $id );
			if ( ! $existing ) {
				return new \WP_Error(
					'patient_not_found',
					__( 'Patient not found.', 'iyoraa' ),
					[ 'status' => 404 ]
				);
			}

			// Validate patient data.
			$errors = PatientValidator::validate( $data );
			if ( ! empty( $errors ) ) {
				return new \WP_Error(
					'validation_failed',
					implode( ' ', $errors ),
					[ 'status' => 400, 'errors' => $errors ]
				);
			}

			// Sanitize input data.
			$sanitized_data = PatientValidator::sanitize( $data );

			// Check phone uniqueness (exclude current patient).
			if ( ! $this->is_phone_unique( $sanitized_data['phone'], $id ) ) {
				return new \WP_Error(
					'duplicate_phone',
					__( 'This phone number is already registered.', 'iyoraa' ),
					[ 'status' => 400 ]
				);
			}

			$sanitized_data['updated_at'] = current_time( 'mysql' );

			// Update patient using repository.
			$this->repository->update( $id, $sanitized_data );

			// Log activity.
			$this->log_activity( 'patient_updated', $id );

			// Return updated PatientDTO.
			$updated_data = $this->repository->find( $id );
			return PatientDTO::from_array( $updated_data );

		} catch ( DatabaseException $e ) {
			return new \WP_Error(
				'db_update_error',
				__( 'Failed to update patient.', 'iyoraa' ),
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
	 * Delete a patient (soft delete - mark as inactive).
	 *
	 * @param int $id Patient database ID.
	 * @return bool|WP_Error True on success or error.
	 */
	public function delete_patient( $id ) {
		try {
			// Check if patient exists.
			$existing = $this->repository->find( $id );
			if ( ! $existing ) {
				return new \WP_Error(
					'patient_not_found',
					__( 'Patient not found.', 'iyoraa' ),
					[ 'status' => 404 ]
				);
			}

			// Soft delete - mark as inactive.
			$this->repository->update( $id, [
				'status'     => 'inactive',
				'updated_at' => current_time( 'mysql' ),
			] );

			// Log activity.
			$this->log_activity( 'patient_deleted', $id );

			return true;

		} catch ( DatabaseException $e ) {
			return new \WP_Error(
				'db_delete_error',
				__( 'Failed to delete patient.', 'iyoraa' ),
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
	 * List patients with pagination.
	 *
	 * @param array $args Query arguments.
	 * @return array Patients data with pagination info.
	 */
	public function list_patients( $args = [] ) {
		$defaults = [
			'page'     => 1,
			'per_page' => 20,
			'status'   => 'active',
		];

		$args   = wp_parse_args( $args, $defaults );
		$offset = ( $args['page'] - 1 ) * $args['per_page'];

		// Use repository to get patients.
		$patients = $this->repository->find_by_status( $args['status'], $args['per_page'], $offset );
		$total    = $this->repository->count();

		// Convert to DTOs.
		$patient_dtos = array_map( function( $patient ) {
			return PatientDTO::from_array( $patient )->to_array();
		}, $patients );

		return [
			'patients'    => $patient_dtos,
			'total'       => $total,
			'page'        => (int) $args['page'],
			'per_page'    => (int) $args['per_page'],
			'total_pages' => ceil( $total / $args['per_page'] ),
		];
	}

	/**
	 * Search patients.
	 *
	 * @param string $query Search query.
	 * @param array  $args  Additional arguments.
	 * @return array Search results.
	 */
	public function search_patients( $query, $args = [] ) {
		$defaults = [
			'page'     => 1,
			'per_page' => 20,
		];

		$args = wp_parse_args( $args, $defaults );

		// Use repository to search patients.
		$patients = $this->repository->search( $query, $args['per_page'] );

		// Convert to DTOs.
		$patient_dtos = array_map( function( $patient ) {
			return PatientDTO::from_array( $patient )->to_array();
		}, $patients );

		return [
			'patients'    => $patient_dtos,
			'total'       => count( $patient_dtos ),
			'page'        => (int) $args['page'],
			'per_page'    => (int) $args['per_page'],
			'total_pages' => 1,
		];
	}

	/**
	 * Get total patient count.
	 *
	 * @return int Patient count.
	 */
	public function get_patient_count() {
		return $this->repository->count_active();
	}

	/**
	 * Check if can add more patients (FREE tier limit).
	 *
	 * @return bool True if can add, false otherwise.
	 */
	private function can_add_patient() {
		$current_count = $this->get_patient_count();
		$limit         = LicenseManager::get_resource_limit( 'patients' );

		// -1 means unlimited (PRO tiers).
		if ( -1 === $limit ) {
			return true;
		}

		return $current_count < $limit;
	}

	/**
	 * Generate unique patient ID.
	 *
	 * Format: HOS-YYYY-####
	 *
	 * @return string Patient ID.
	 */
	private function generate_patient_id() {
		global $wpdb;

		$year   = gmdate( 'Y' );
		$prefix = 'HOS-' . $year . '-';
		$table  = $this->get_table();

		// phpcs:disable WordPress.DB.PreparedSQL.InterpolatedNotPrepared
		// Get the last patient ID for this year.
		$last_id = $wpdb->get_var(
			$wpdb->prepare(
				"SELECT patient_id FROM {$table} 
				WHERE patient_id LIKE %s 
				ORDER BY id DESC LIMIT 1",
				$prefix . '%'
			)
		);
		// phpcs:enable WordPress.DB.PreparedSQL.InterpolatedNotPrepared

		if ( $last_id ) {
			// Extract number and increment.
			$number = (int) str_replace( $prefix, '', $last_id );
			++$number;
		} else {
			// First patient of the year.
			$number = 1;
		}

		return $prefix . str_pad( $number, 4, '0', STR_PAD_LEFT );
	}

	/**
	 * Check if phone number is unique.
	 *
	 * @param string $phone      Phone number.
	 * @param int    $patient_id Patient ID to exclude (for updates).
	 * @return bool True if unique, false otherwise.
	 */
	private function is_phone_unique( $phone, $patient_id = null ) {
		global $wpdb;

		$table = $this->get_table();

		// phpcs:disable WordPress.DB.PreparedSQL.InterpolatedNotPrepared
		// phpcs:disable WordPress.DB.DirectDatabaseQuery.DirectQuery
		// phpcs:disable WordPress.DB.DirectDatabaseQuery.NoCaching
		if ( $patient_id ) {
			$count = $wpdb->get_var(
				$wpdb->prepare(
					"SELECT COUNT(*) FROM {$table} 
					WHERE phone = %s AND status = 'active' AND id != %d",
					$phone,
					$patient_id
				)
			);
		} else {
			$count = $wpdb->get_var(
				$wpdb->prepare(
					"SELECT COUNT(*) FROM {$table} 
					WHERE phone = %s AND status = 'active'",
					$phone
				)
			);
		}
		// phpcs:enable WordPress.DB.PreparedSQL.InterpolatedNotPrepared
		// phpcs:enable WordPress.DB.DirectDatabaseQuery.DirectQuery
		// phpcs:enable WordPress.DB.DirectDatabaseQuery.NoCaching

		return 0 === (int) $count;
	}


	/**
	 * Log patient activity to audit log.
	 *
	 * @param string $action     Action type.
	 * @param int    $patient_id Patient ID.
	 */
	private function log_activity( $action, $patient_id ) {
		global $wpdb;

		$user_id = get_current_user_id();

		// phpcs:disable WordPress.DB.PreparedSQL.InterpolatedNotPrepared
		$wpdb->query(
			$wpdb->prepare(
				"INSERT INTO {$this->get_audit_table()} 
				(user_id, action, entity_type, entity_id, ip_address, user_agent, created_at) 
				VALUES (%d, %s, %s, %d, %s, %s, %s)",
				$user_id,
				$action,
				'patient',
				$patient_id,
				$this->get_client_ip(),
				$this->get_user_agent(),
				current_time( 'mysql' )
			)
		);
		// phpcs:enable WordPress.DB.PreparedSQL.InterpolatedNotPrepared
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
