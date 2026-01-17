<?php
/**
 * Appointment Repository
 *
 * Handles database operations for appointments.
 *
 * @package WPHelpZone\Iyoraa\Repositories
 */

namespace WPHelpZone\Iyoraa\Repositories;

/**
 * Appointment Repository class.
 */
class AppointmentRepository extends BaseRepository {

	/**
	 * Table name.
	 *
	 * @var string
	 */
	protected $table = 'iyoraa_appointments';

	/**
	 * Find appointment by appointment_id.
	 *
	 * @param string $appointment_id Appointment ID (APT-YYYY-####).
	 * @return array|null Appointment data or null.
	 */
	public function find_by_appointment_id( string $appointment_id ): ?array {
		$cache_key = "appointment_id_{$appointment_id}";
		$cached    = wp_cache_get( $cache_key, $this->cache_group );

		if ( false !== $cached ) {
			return $cached;
		}

		$result = $this->wpdb->get_row(
			$this->wpdb->prepare(
				"SELECT * FROM {$this->get_table_name()} WHERE appointment_id = %s",
				$appointment_id
			),
			ARRAY_A
		);

		if ( $result ) {
			wp_cache_set( $cache_key, $result, $this->cache_group, $this->cache_ttl );
		}

		return $result;
	}

	/**
	 * Get appointments for a patient.
	 *
	 * @param int $patient_id Patient ID.
	 * @param int $limit      Maximum results.
	 * @param int $offset     Offset for pagination.
	 * @return array Array of appointments.
	 */
	public function find_by_patient( int $patient_id, int $limit = 20, int $offset = 0 ): array {
		$cache_key = "patient_{$patient_id}_{$limit}_{$offset}";
		$cached    = wp_cache_get( $cache_key, $this->cache_group );

		if ( false !== $cached ) {
			return $cached;
		}

		$results = $this->wpdb->get_results(
			$this->wpdb->prepare(
				"SELECT * FROM {$this->get_table_name()} 
				 WHERE patient_id = %d
				 ORDER BY appointment_date DESC, appointment_time DESC
				 LIMIT %d OFFSET %d",
				$patient_id,
				$limit,
				$offset
			),
			ARRAY_A
		) ?? [];

		wp_cache_set( $cache_key, $results, $this->cache_group, $this->cache_ttl );

		return $results;
	}

	/**
	 * Get appointments for a doctor.
	 *
	 * @param int $doctor_id Doctor ID.
	 * @param int $limit     Maximum results.
	 * @param int $offset    Offset for pagination.
	 * @return array Array of appointments.
	 */
	public function find_by_doctor( int $doctor_id, int $limit = 20, int $offset = 0 ): array {
		$cache_key = "doctor_{$doctor_id}_{$limit}_{$offset}";
		$cached    = wp_cache_get( $cache_key, $this->cache_group );

		if ( false !== $cached ) {
			return $cached;
		}

		$results = $this->wpdb->get_results(
			$this->wpdb->prepare(
				"SELECT * FROM {$this->get_table_name()} 
				 WHERE doctor_id = %d
				 ORDER BY appointment_date DESC, appointment_time DESC
				 LIMIT %d OFFSET %d",
				$doctor_id,
				$limit,
				$offset
			),
			ARRAY_A
		) ?? [];

		wp_cache_set( $cache_key, $results, $this->cache_group, $this->cache_ttl );

		return $results;
	}

	/**
	 * Get appointments by status.
	 *
	 * @param string $status  Appointment status.
	 * @param int    $limit   Maximum results.
	 * @param int    $offset  Offset for pagination.
	 * @return array Array of appointments.
	 */
	public function find_by_status( string $status, int $limit = 100, int $offset = 0 ): array {
		$cache_key = "status_{$status}_{$limit}_{$offset}";
		$cached    = wp_cache_get( $cache_key, $this->cache_group );

		if ( false !== $cached ) {
			return $cached;
		}

		$results = $this->wpdb->get_results(
			$this->wpdb->prepare(
				"SELECT * FROM {$this->get_table_name()} 
				 WHERE status = %s
				 ORDER BY appointment_date DESC, appointment_time DESC
				 LIMIT %d OFFSET %d",
				$status,
				$limit,
				$offset
			),
			ARRAY_A
		) ?? [];

		wp_cache_set( $cache_key, $results, $this->cache_group, $this->cache_ttl );

		return $results;
	}

	/**
	 * Get appointments by date range.
	 *
	 * @param string $start_date Start date (Y-m-d).
	 * @param string $end_date   End date (Y-m-d).
	 * @param int    $doctor_id  Optional doctor ID filter.
	 * @return array Array of appointments.
	 */
	public function find_by_date_range( string $start_date, string $end_date, ?int $doctor_id = null ): array {
		$cache_key = "date_range_{$start_date}_{$end_date}";
		if ( $doctor_id ) {
			$cache_key .= "_doctor_{$doctor_id}";
		}
		$cached = wp_cache_get( $cache_key, $this->cache_group );

		if ( false !== $cached ) {
			return $cached;
		}

		if ( $doctor_id ) {
			$results = $this->wpdb->get_results(
				$this->wpdb->prepare(
					"SELECT * FROM {$this->get_table_name()} 
					 WHERE appointment_date BETWEEN %s AND %s
					 AND doctor_id = %d
					 ORDER BY appointment_date ASC, appointment_time ASC",
					$start_date,
					$end_date,
					$doctor_id
				),
				ARRAY_A
			) ?? [];
		} else {
			$results = $this->wpdb->get_results(
				$this->wpdb->prepare(
					"SELECT * FROM {$this->get_table_name()} 
					 WHERE appointment_date BETWEEN %s AND %s
					 ORDER BY appointment_date ASC, appointment_time ASC",
					$start_date,
					$end_date
				),
				ARRAY_A
			) ?? [];
		}

		wp_cache_set( $cache_key, $results, $this->cache_group, $this->cache_ttl );

		return $results;
	}

	/**
	 * Get today's appointments.
	 *
	 * @param int|null $doctor_id Optional doctor ID filter.
	 * @return array Array of today's appointments.
	 */
	public function get_today_appointments( ?int $doctor_id = null ): array {
		$today = gmdate( 'Y-m-d' );
		return $this->find_by_date_range( $today, $today, $doctor_id );
	}

	/**
	 * Get upcoming appointments.
	 *
	 * @param int $limit Maximum results.
	 * @return array Array of upcoming appointments.
	 */
	public function get_upcoming( int $limit = 10 ): array {
		$cache_key = "upcoming_{$limit}";
		$cached    = wp_cache_get( $cache_key, $this->cache_group );

		if ( false !== $cached ) {
			return $cached;
		}

		$today = gmdate( 'Y-m-d' );
		$time  = gmdate( 'H:i:s' );

		$results = $this->wpdb->get_results(
			$this->wpdb->prepare(
				"SELECT * FROM {$this->get_table_name()} 
				 WHERE (appointment_date > %s OR (appointment_date = %s AND appointment_time > %s))
				 AND status IN ('scheduled', 'confirmed')
				 ORDER BY appointment_date ASC, appointment_time ASC
				 LIMIT %d",
				$today,
				$today,
				$time,
				$limit
			),
			ARRAY_A
		) ?? [];

		wp_cache_set( $cache_key, $results, $this->cache_group, 300 ); // 5 minutes TTL.

		return $results;
	}

	/**
	 * Check for conflicting appointments.
	 *
	 * Checks if there's an overlapping appointment for the doctor at the given time.
	 *
	 * @param int    $doctor_id        Doctor ID.
	 * @param string $appointment_date Appointment date.
	 * @param string $appointment_time Appointment time.
	 * @param int    $duration         Duration in minutes.
	 * @param int    $exclude_id       Appointment ID to exclude (for updates).
	 * @return bool True if conflict exists, false otherwise.
	 */
	public function has_conflict( int $doctor_id, string $appointment_date, string $appointment_time, int $duration, ?int $exclude_id = null ): bool {
		$start_time = $appointment_time;
		$end_time   = gmdate( 'H:i:s', strtotime( $appointment_time ) + ( $duration * 60 ) );

		$sql = "SELECT COUNT(*) FROM {$this->get_table_name()} 
				WHERE doctor_id = %d 
				AND appointment_date = %s
				AND status NOT IN ('cancelled', 'no-show')
				AND (
					(appointment_time < %s AND DATE_ADD(appointment_time, INTERVAL duration MINUTE) > %s) OR
					(appointment_time >= %s AND appointment_time < %s)
				)";

		$params = [ $doctor_id, $appointment_date, $end_time, $start_time, $start_time, $end_time ];

		if ( $exclude_id ) {
			$sql     .= ' AND id != %d';
			$params[] = $exclude_id;
		}

		$count = (int) $this->wpdb->get_var(
			$this->wpdb->prepare(
				$sql, // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
				...$params
			)
		);

		return $count > 0;
	}

	/**
	 * Count appointments by status.
	 *
	 * @param string $status Appointment status.
	 * @return int Count of appointments.
	 */
	public function count_by_status( string $status ): int {
		$cache_key = "count_status_{$status}";
		$cached    = wp_cache_get( $cache_key, $this->cache_group );

		if ( false !== $cached ) {
			return (int) $cached;
		}

		$count = (int) $this->wpdb->get_var(
			$this->wpdb->prepare(
				"SELECT COUNT(*) FROM {$this->get_table_name()} 
				 WHERE status = %s",
				$status
			)
		);

		wp_cache_set( $cache_key, $count, $this->cache_group, $this->cache_ttl );

		return $count;
	}

	/**
	 * Get appointment statistics.
	 *
	 * @return array Statistics array.
	 */
	public function get_statistics(): array {
		$cache_key = 'statistics';
		$cached    = wp_cache_get( $cache_key, $this->cache_group );

		if ( false !== $cached ) {
			return $cached;
		}

		$stats = [
			'total'       => $this->count(),
			'scheduled'   => $this->count_by_status( 'scheduled' ),
			'confirmed'   => $this->count_by_status( 'confirmed' ),
			'in_progress' => $this->count_by_status( 'in-progress' ),
			'completed'   => $this->count_by_status( 'completed' ),
			'cancelled'   => $this->count_by_status( 'cancelled' ),
			'no_show'     => $this->count_by_status( 'no-show' ),
		];

		wp_cache_set( $cache_key, $stats, $this->cache_group, 600 ); // 10 minutes TTL.

		return $stats;
	}
}
