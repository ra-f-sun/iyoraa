<?php
/**
 * Patient Repository
 *
 * Handles database operations for patients.
 *
 * @package WPHelpZone\Iyoraa\Repositories
 */

namespace WPHelpZone\Iyoraa\Repositories;

/**
 * Patient Repository class.
 */
class PatientRepository extends BaseRepository {

	/**
	 * Table name.
	 *
	 * @var string
	 */
	protected $table = 'iyoraa_patients';

	/**
	 * Find patient by patient_id.
	 *
	 * @param string $patient_id Patient ID (HOS-YYYY-####).
	 * @return array|null Patient data or null.
	 */
	public function find_by_patient_id( string $patient_id ): ?array {
		$cache_key = "patient_id_{$patient_id}";
		$cached    = wp_cache_get( $cache_key, $this->cache_group );

		if ( false !== $cached ) {
			return $cached;
		}

		$result = $this->wpdb->get_row(
			$this->wpdb->prepare(
				"SELECT * FROM {$this->get_table_name()} WHERE patient_id = %s",
				$patient_id
			),
			ARRAY_A
		);

		if ( $result ) {
			wp_cache_set( $cache_key, $result, $this->cache_group, $this->cache_ttl );
		}

		return $result;
	}

	/**
	 * Search patients by name, phone, or patient ID.
	 *
	 * @param string $query Search query.
	 * @param int    $limit Maximum results.
	 * @return array Array of patients.
	 */
	public function search( string $query, int $limit = 20 ): array {
		$like = '%' . $this->wpdb->esc_like( $query ) . '%';

		return $this->wpdb->get_results(
			$this->wpdb->prepare(
				"SELECT * FROM {$this->get_table_name()} 
				 WHERE full_name LIKE %s 
				 OR phone LIKE %s 
				 OR patient_id LIKE %s
				 ORDER BY created_at DESC
				 LIMIT %d",
				$like,
				$like,
				$like,
				$limit
			),
			ARRAY_A
		) ?? [];
	}

	/**
	 * Count active patients.
	 *
	 * @return int Active patient count.
	 */
	public function count_active(): int {
		$cache_key = 'count_active';
		$cached    = wp_cache_get( $cache_key, $this->cache_group );

		if ( false !== $cached ) {
			return (int) $cached;
		}

		$count = (int) $this->wpdb->get_var(
			"SELECT COUNT(*) FROM {$this->get_table_name()} 
			 WHERE status = 'active'"
		);

		wp_cache_set( $cache_key, $count, $this->cache_group, $this->cache_ttl );

		return $count;
	}

	/**
	 * Get patients by status.
	 *
	 * @param string $status Patient status.
	 * @param int    $limit  Maximum results.
	 * @param int    $offset Offset for pagination.
	 * @return array Array of patients.
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
				 ORDER BY created_at DESC
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
	 * Get recent patients.
	 *
	 * @param int $limit Number of patients to return.
	 * @return array Array of recent patients.
	 */
	public function get_recent( int $limit = 10 ): array {
		return $this->wpdb->get_results(
			$this->wpdb->prepare(
				"SELECT * FROM {$this->get_table_name()} 
				 ORDER BY created_at DESC 
				 LIMIT %d",
				$limit
			),
			ARRAY_A
		) ?? [];
	}

	/**
	 * Check if patient ID exists.
	 *
	 * @param string $patient_id Patient ID.
	 * @return bool True if exists.
	 */
	public function patient_id_exists( string $patient_id ): bool {
		$count = (int) $this->wpdb->get_var(
			$this->wpdb->prepare(
				"SELECT COUNT(*) FROM {$this->get_table_name()} 
				 WHERE patient_id = %s",
				$patient_id
			)
		);

		return $count > 0;
	}

	/**
	 * Get patients with pagination info.
	 *
	 * @param int $page     Current page number.
	 * @param int $per_page Items per page.
	 * @return array Array with patients and pagination data.
	 */
	public function paginate( int $page = 1, int $per_page = 20 ): array {
		$offset = ( $page - 1 ) * $per_page;
		$total  = $this->count_active();

		$patients = $this->find_by_status( 'active', $per_page, $offset );

		return [
			'patients'     => $patients,
			'total'        => $total,
			'per_page'     => $per_page,
			'current_page' => $page,
			'total_pages'  => ceil( $total / $per_page ),
		];
	}

	/**
	 * Clear cache when record is modified.
	 *
	 * @param int|null $id Record ID.
	 * @return void
	 */
	protected function clear_cache( ?int $id = null ): void {
		parent::clear_cache( $id );
		wp_cache_delete( 'count_active', $this->cache_group );
	}
}
