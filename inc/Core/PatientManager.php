<?php
/**
 * Patient Manager
 *
 * @package WPHelpZone\Iyoraa
 */

namespace WPHelpZone\Iyoraa\Core;

/**
 * Patient Manager class.
 *
 * Handles all patient CRUD operations and business logic.
 */
class PatientManager extends Singleton {


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
	 * @return array|WP_Error Patient data with ID or error.
	 */
	public function create_patient( $data ) {
		// Check patient limit for FREE tier.
		if ( ! $this->can_add_patient() ) {
			return new \WP_Error(
				'patient_limit_reached',
				__( 'Patient limit reached. Upgrade to PRO to add more patients.', 'iyoraa' ),
				[ 'status' => 403 ]
			);
		}

		// Validate required fields.
		$validation = $this->validate_patient_data( $data );
		if ( is_wp_error( $validation ) ) {
			return $validation;
		}

		// Generate unique patient ID.
		$patient_id = $this->generate_patient_id();

		global $wpdb;

		$insert_data = [
			'patient_id'              => $patient_id,
			'full_name'               => sanitize_text_field( $data['full_name'] ),
			'age'                     => absint( $data['age'] ),
			'gender'                  => sanitize_text_field( $data['gender'] ),
			'phone'                   => sanitize_text_field( $data['phone'] ),
			'email'                   => isset( $data['email'] ) ? sanitize_email( $data['email'] ) : '',
			'address'                 => isset( $data['address'] ) ? sanitize_textarea_field( $data['address'] ) : '',
			'blood_group'             => isset( $data['blood_group'] ) ? sanitize_text_field( $data['blood_group'] ) : '',
			'emergency_contact_name'  => isset( $data['emergency_contact_name'] ) ? sanitize_text_field( $data['emergency_contact_name'] ) : '',
			'emergency_contact_phone' => isset( $data['emergency_contact_phone'] ) ? sanitize_text_field( $data['emergency_contact_phone'] ) : '',
			'medical_history'         => isset( $data['medical_history'] ) ? sanitize_textarea_field( $data['medical_history'] ) : '',
			'status'                  => 'active',
			'created_at'              => current_time( 'mysql' ),
			'updated_at'              => current_time( 'mysql' ),
		];

		// phpcs:disable WordPress.DB.PreparedSQL.InterpolatedNotPrepared
		// phpcs:disable WordPress.DB.DirectDatabaseQuery.DirectQuery
		// phpcs:disable WordPress.DB.DirectDatabaseQuery.NoCaching
		$result = $wpdb->query(
			$wpdb->prepare(
				"INSERT INTO {$this->get_table()} 
				(patient_id, full_name, age, gender, phone, email, address, blood_group, 
				emergency_contact_name, emergency_contact_phone, medical_history, status, created_at, updated_at) 
				VALUES (%s, %s, %d, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s)",
				$insert_data['patient_id'],
				$insert_data['full_name'],
				$insert_data['age'],
				$insert_data['gender'],
				$insert_data['phone'],
				$insert_data['email'],
				$insert_data['address'],
				$insert_data['blood_group'],
				$insert_data['emergency_contact_name'],
				$insert_data['emergency_contact_phone'],
				$insert_data['medical_history'],
				$insert_data['status'],
				$insert_data['created_at'],
				$insert_data['updated_at']
			)
		);
		// phpcs:enable WordPress.DB.PreparedSQL.InterpolatedNotPrepared
		// phpcs:enable WordPress.DB.DirectDatabaseQuery.DirectQuery
		// phpcs:enable WordPress.DB.DirectDatabaseQuery.NoCaching

		if ( false === $result ) {
			return new \WP_Error(
				'db_insert_error',
				__( 'Failed to create patient.', 'iyoraa' ),
				[ 'status' => 500 ]
			);
		}

		$insert_data['id'] = $wpdb->insert_id;

		// Log activity.
		$this->log_activity( 'patient_created', $insert_data['id'] );

		return $insert_data;
	}

	/**
	 * Get a patient by ID.
	 *
	 * @param int $id Patient database ID.
	 * @return array|WP_Error Patient data or error.
	 */
	public function get_patient( $id ) {
		global $wpdb;

		$table = $this->get_table();

		// phpcs:disable WordPress.DB.PreparedSQL.InterpolatedNotPrepared
		// phpcs:disable WordPress.DB.DirectDatabaseQuery.DirectQuery
		// phpcs:disable WordPress.DB.DirectDatabaseQuery.NoCaching
		$patient = $wpdb->get_row(
			$wpdb->prepare(
				"SELECT * FROM {$table} WHERE id = %d",
				$id
			),
			ARRAY_A
		);
		// phpcs:enable WordPress.DB.PreparedSQL.InterpolatedNotPrepared
	// phpcs:enable WordPress.DB.DirectDatabaseQuery.DirectQuery
	// phpcs:enable WordPress.DB.DirectDatabaseQuery.NoCaching

		if ( ! $patient ) {
			return new \WP_Error(
				'patient_not_found',
				__( 'Patient not found.', 'iyoraa' ),
				[ 'status' => 404 ]
			);
		}

		return $patient;
	}

	/**
	 * Update a patient.
	 *
	 * @param int   $id   Patient database ID.
	 * @param array $data Patient data to update.
	 * @return array|WP_Error Updated patient data or error.
	 */
	public function update_patient( $id, $data ) {
		// Check if patient exists.
		$existing = $this->get_patient( $id );
		if ( is_wp_error( $existing ) ) {
			return $existing;
		}

		// Validate data.
		$validation = $this->validate_patient_data( $data, $id );
		if ( is_wp_error( $validation ) ) {
			return $validation;
		}

		global $wpdb;

		$update_data = [
			'full_name'               => sanitize_text_field( $data['full_name'] ),
			'age'                     => absint( $data['age'] ),
			'gender'                  => sanitize_text_field( $data['gender'] ),
			'phone'                   => sanitize_text_field( $data['phone'] ),
			'email'                   => isset( $data['email'] ) ? sanitize_email( $data['email'] ) : '',
			'address'                 => isset( $data['address'] ) ? sanitize_textarea_field( $data['address'] ) : '',
			'blood_group'             => isset( $data['blood_group'] ) ? sanitize_text_field( $data['blood_group'] ) : '',
			'emergency_contact_name'  => isset( $data['emergency_contact_name'] ) ? sanitize_text_field( $data['emergency_contact_name'] ) : '',
			'emergency_contact_phone' => isset( $data['emergency_contact_phone'] ) ? sanitize_text_field( $data['emergency_contact_phone'] ) : '',
			'medical_history'         => isset( $data['medical_history'] ) ? sanitize_textarea_field( $data['medical_history'] ) : '',
			'updated_at'              => current_time( 'mysql' ),
		];

		// phpcs:disable WordPress.DB.PreparedSQL.InterpolatedNotPrepared
		$result = $wpdb->query(
			$wpdb->prepare(
				"UPDATE {$this->get_table()} 
				SET full_name = %s, age = %d, gender = %s, phone = %s, email = %s, 
					address = %s, blood_group = %s, emergency_contact_name = %s, 
					emergency_contact_phone = %s, medical_history = %s, updated_at = %s 
				WHERE id = %d",
				$update_data['full_name'],
				$update_data['age'],
				$update_data['gender'],
				$update_data['phone'],
				$update_data['email'],
				$update_data['address'],
				$update_data['blood_group'],
				$update_data['emergency_contact_name'],
				$update_data['emergency_contact_phone'],
				$update_data['medical_history'],
				$update_data['updated_at'],
				$id
			)
		);
		// phpcs:enable WordPress.DB.PreparedSQL.InterpolatedNotPrepared
		// phpcs:enable WordPress.DB.DirectDatabaseQuery.DirectQuery
		// phpcs:enable WordPress.DB.DirectDatabaseQuery.NoCaching

		if ( false === $result ) {
			return new \WP_Error(
				'db_update_error',
				__( 'Failed to update patient.', 'iyoraa' ),
				[ 'status' => 500 ]
			);
		}

		// Log activity.
		$this->log_activity( 'patient_updated', $id );

		return $this->get_patient( $id );
	}

	/**
	 * Delete a patient (soft delete - mark as inactive).
	 *
	 * @param int $id Patient database ID.
	 * @return bool|WP_Error True on success or error.
	 */
	public function delete_patient( $id ) {
		// Check if patient exists.
		$existing = $this->get_patient( $id );
		if ( is_wp_error( $existing ) ) {
			return $existing;
		}

		global $wpdb;

		// phpcs:disable WordPress.DB.PreparedSQL.InterpolatedNotPrepared
		$result = $wpdb->query(
			$wpdb->prepare(
				"UPDATE {$this->get_table()} 
				SET status = %s, updated_at = %s 
				WHERE id = %d",
				'inactive',
				current_time( 'mysql' ),
				$id
			)
		);
		// phpcs:enable WordPress.DB.PreparedSQL.InterpolatedNotPrepared

		if ( false === $result ) {
			return new \WP_Error(
				'db_delete_error',
				__( 'Failed to delete patient.', 'iyoraa' ),
				[ 'status' => 500 ]
			);
		}

		// Log activity.
		$this->log_activity( 'patient_deleted', $id );

		return true;
	}

	/**
	 * List patients with pagination.
	 *
	 * @param array $args Query arguments.
	 * @return array Patients data with pagination info.
	 */
	public function list_patients( $args = [] ) {
		global $wpdb;

		$defaults = [
			'page'     => 1,
			'per_page' => 20,
			'status'   => 'active',
			'orderby'  => 'created_at',
			'order'    => 'DESC',
		];

		$args = wp_parse_args( $args, $defaults );

		$offset = ( $args['page'] - 1 ) * $args['per_page'];
		$table  = $this->get_table();

		// Build query.
		$where = $wpdb->prepare( 'WHERE status = %s', $args['status'] );

		// phpcs:disable WordPress.DB.PreparedSQL.InterpolatedNotPrepared
		$patients = $wpdb->get_results(
			$wpdb->prepare(
				"SELECT * FROM {$table} 
				{$where} 
				ORDER BY {$args['orderby']} {$args['order']} 
				LIMIT %d OFFSET %d",
				$args['per_page'],
				$offset
			),
			ARRAY_A
		);

		$total = $wpdb->get_var( "SELECT COUNT(*) FROM {$table} {$where}" );
		// phpcs:enable WordPress.DB.PreparedSQL.InterpolatedNotPrepared

		return [
			'patients'    => $patients,
			'total'       => (int) $total,
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
		global $wpdb;

		$defaults = [
			'page'     => 1,
			'per_page' => 20,
			'status'   => 'active',
		];

		$args = wp_parse_args( $args, $defaults );

		$offset = ( $args['page'] - 1 ) * $args['per_page'];
		$search = '%' . $wpdb->esc_like( $query ) . '%';
		$table  = $this->get_table();

		// phpcs:disable WordPress.DB.PreparedSQL.InterpolatedNotPrepared
		$patients = $wpdb->get_results(
			$wpdb->prepare(
				"SELECT * FROM {$table} 
				WHERE status = %s 
				AND (
					patient_id LIKE %s 
					OR full_name LIKE %s 
					OR phone LIKE %s 
					OR email LIKE %s
				)
				ORDER BY created_at DESC 
				LIMIT %d OFFSET %d",
				$args['status'],
				$search,
				$search,
				$search,
				$search,
				$args['per_page'],
				$offset
			),
			ARRAY_A
		);

		$total = $wpdb->get_var(
			$wpdb->prepare(
				"SELECT COUNT(*) FROM {$table} 
				WHERE status = %s 
				AND (
					patient_id LIKE %s 
					OR full_name LIKE %s 
					OR phone LIKE %s 
					OR email LIKE %s
				)",
				$args['status'],
				$search,
				$search,
				$search,
				$search
			)
		);
		// phpcs:enable WordPress.DB.PreparedSQL.InterpolatedNotPrepared

		return [
			'patients'    => $patients,
			'total'       => (int) $total,
			'page'        => (int) $args['page'],
			'per_page'    => (int) $args['per_page'],
			'total_pages' => ceil( $total / $args['per_page'] ),
		];
	}

	/**
	 * Get total patient count.
	 *
	 * @return int Patient count.
	 */
	public function get_patient_count() {
		global $wpdb;

		$table = $this->get_table();

		// phpcs:disable WordPress.DB.PreparedSQL.InterpolatedNotPrepared
		return (int) $wpdb->get_var(
			$wpdb->prepare(
				"SELECT COUNT(*) FROM {$table} WHERE status = %s",
				'active'
			)
		);
		// phpcs:enable WordPress.DB.PreparedSQL.InterpolatedNotPrepared
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
	 * Validate patient data.
	 *
	 * @param array $data      Patient data.
	 * @param int   $patient_id Patient ID for updates (optional).
	 * @return true|WP_Error True if valid, WP_Error otherwise.
	 */
	private function validate_patient_data( $data, $patient_id = null ) {
		// Required fields.
		$required = [ 'full_name', 'age', 'gender', 'phone' ];

		foreach ( $required as $field ) {
			if ( empty( $data[ $field ] ) ) {
				return new \WP_Error(
					'missing_required_field',
					sprintf(
						/* translators: %s: field name */
						__( 'Missing required field: %s', 'iyoraa' ),
						$field
					),
					[ 'status' => 400 ]
				);
			}
		}

		// Validate age.
		$age = absint( $data['age'] );
		if ( $age < 0 || $age > 150 ) {
			return new \WP_Error(
				'invalid_age',
				__( 'Age must be between 0 and 150.', 'iyoraa' ),
				[ 'status' => 400 ]
			);
		}

		// Validate gender.
		$valid_genders = [ 'male', 'female', 'other' ];
		if ( ! in_array( strtolower( $data['gender'] ), $valid_genders, true ) ) {
			return new \WP_Error(
				'invalid_gender',
				__( 'Gender must be male, female, or other.', 'iyoraa' ),
				[ 'status' => 400 ]
			);
		}

		// Validate phone uniqueness.
		if ( ! $this->is_phone_unique( $data['phone'], $patient_id ) ) {
			return new \WP_Error(
				'duplicate_phone',
				__( 'This phone number is already registered.', 'iyoraa' ),
				[ 'status' => 400 ]
			);
		}

		return true;
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
