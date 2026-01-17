<?php
/**
 * Base Repository
 *
 * Provides common database operations for all repositories.
 *
 * @package WPHelpZone\Iyoraa\Repositories
 */

namespace WPHelpZone\Iyoraa\Repositories;

use WPHelpZone\Iyoraa\Exceptions\DatabaseException;

/**
 * Base Repository class.
 */
abstract class BaseRepository {

	/**
	 * WordPress database object.
	 *
	 * @var \wpdb
	 */
	protected $wpdb;

	/**
	 * Table name (without prefix).
	 *
	 * @var string
	 */
	protected $table;

	/**
	 * Cache group.
	 *
	 * @var string
	 */
	protected $cache_group = 'iyoraa';

	/**
	 * Cache TTL in seconds.
	 *
	 * @var int
	 */
	protected $cache_ttl = 3600; // 1 hour.

	/**
	 * Constructor.
	 */
	public function __construct() {
		global $wpdb;
		$this->wpdb = $wpdb;
	}

	/**
	 * Find record by ID.
	 *
	 * @param int $id Record ID.
	 * @return array|null Record data or null if not found.
	 */
	public function find( int $id ): ?array {
		$cache_key = $this->get_cache_key( $id );
		$cached    = wp_cache_get( $cache_key, $this->cache_group );

		if ( false !== $cached ) {
			return $cached;
		}

		$result = $this->wpdb->get_row(
			$this->wpdb->prepare(
				"SELECT * FROM {$this->get_table_name()} WHERE id = %d",
				$id
			),
			ARRAY_A
		);

		if ( $result ) {
			wp_cache_set( $cache_key, $result, $this->cache_group, $this->cache_ttl );
		}

		return $result;
	}

	/**
	 * Get all records.
	 *
	 * @param int $limit  Maximum number of records.
	 * @param int $offset Offset for pagination.
	 * @return array Array of records.
	 */
	public function all( int $limit = 100, int $offset = 0 ): array {
		$cache_key = "all_{$limit}_{$offset}";
		$cached    = wp_cache_get( $cache_key, $this->cache_group );

		if ( false !== $cached ) {
			return $cached;
		}

		$results = $this->wpdb->get_results(
			$this->wpdb->prepare(
				"SELECT * FROM {$this->get_table_name()} 
				 ORDER BY id DESC 
				 LIMIT %d OFFSET %d",
				$limit,
				$offset
			),
			ARRAY_A
		);

		wp_cache_set( $cache_key, $results, $this->cache_group, $this->cache_ttl );

		return $results ?? [];
	}

	/**
	 * Create new record.
	 *
	 * @param array $data Record data.
	 * @return int Inserted record ID.
	 * @throws DatabaseException If insert fails.
	 */
	public function create( array $data ): int {
		$result = $this->wpdb->insert(
			$this->get_table_name(),
			$data
		);

		if ( false === $result ) {
			throw new DatabaseException( 'insert', $this->table, $this->wpdb->last_error );
		}

		$id = $this->wpdb->insert_id;
		$this->clear_cache( $id );

		return $id;
	}

	/**
	 * Update record.
	 *
	 * @param int   $id   Record ID.
	 * @param array $data Updated data.
	 * @return bool True on success.
	 * @throws DatabaseException If update fails.
	 */
	public function update( int $id, array $data ): bool {
		$result = $this->wpdb->update(
			$this->get_table_name(),
			$data,
			[ 'id' => $id ]
		);

		if ( false === $result ) {
			throw new DatabaseException( 'update', $this->table, $this->wpdb->last_error );
		}

		$this->clear_cache( $id );

		return true;
	}

	/**
	 * Delete record.
	 *
	 * @param int $id Record ID.
	 * @return bool True on success.
	 * @throws DatabaseException If delete fails.
	 */
	public function delete( int $id ): bool {
		$result = $this->wpdb->delete(
			$this->get_table_name(),
			[ 'id' => $id ]
		);

		if ( false === $result ) {
			throw new DatabaseException( 'delete', $this->table, $this->wpdb->last_error );
		}

		$this->clear_cache( $id );

		return true;
	}

	/**
	 * Count all records.
	 *
	 * @return int Total count.
	 */
	public function count(): int {
		$cache_key = 'count_all';
		$cached    = wp_cache_get( $cache_key, $this->cache_group );

		if ( false !== $cached ) {
			return (int) $cached;
		}

		$count = (int) $this->wpdb->get_var(
			"SELECT COUNT(*) FROM {$this->get_table_name()}"
		);

		wp_cache_set( $cache_key, $count, $this->cache_group, $this->cache_ttl );

		return $count;
	}

	/**
	 * Get full table name with prefix.
	 *
	 * @return string Table name with prefix.
	 */
	protected function get_table_name(): string {
		return $this->wpdb->prefix . $this->table;
	}

	/**
	 * Get cache key for record.
	 *
	 * @param int $id Record ID.
	 * @return string Cache key.
	 */
	protected function get_cache_key( int $id ): string {
		return "{$this->table}_{$id}";
	}

	/**
	 * Clear cache for record.
	 *
	 * @param int|null $id Record ID or null to clear all.
	 * @return void
	 */
	protected function clear_cache( ?int $id = null ): void {
		if ( $id ) {
			wp_cache_delete( $this->get_cache_key( $id ), $this->cache_group );
		}
		
		// Clear list caches.
		wp_cache_delete( 'count_all', $this->cache_group );
		wp_cache_flush();
	}

	/**
	 * Begin transaction.
	 *
	 * @return void
	 */
	protected function begin_transaction(): void {
		$this->wpdb->query( 'START TRANSACTION' );
	}

	/**
	 * Commit transaction.
	 *
	 * @return void
	 */
	protected function commit(): void {
		$this->wpdb->query( 'COMMIT' );
	}

	/**
	 * Rollback transaction.
	 *
	 * @return void
	 */
	protected function rollback(): void {
		$this->wpdb->query( 'ROLLBACK' );
	}
}
