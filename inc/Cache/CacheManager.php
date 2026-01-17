<?php
/**
 * Cache Manager
 *
 * Centralized caching using WordPress transients.
 *
 * @package WPHelpZone\Iyoraa\Cache
 */

namespace WPHelpZone\Iyoraa\Cache;

/**
 * Cache Manager class.
 */
class CacheManager {

	/**
	 * Cache group prefix.
	 *
	 * @var string
	 */
	private $group = 'iyoraa';

	/**
	 * Default TTL in seconds.
	 *
	 * @var int
	 */
	private $default_ttl = 3600; // 1 hour.

	/**
	 * Get cached value.
	 *
	 * @param string $key Cache key.
	 * @return mixed|false Cached value or false if not found.
	 */
	public function get( string $key ) {
		return wp_cache_get( $key, $this->group );
	}

	/**
	 * Set cache value.
	 *
	 * @param string   $key   Cache key.
	 * @param mixed    $value Value to cache.
	 * @param int|null $ttl   Time to live in seconds (null = default).
	 * @return bool True on success.
	 */
	public function set( string $key, $value, ?int $ttl = null ): bool {
		$ttl = $ttl ?? $this->default_ttl;
		return wp_cache_set( $key, $value, $this->group, $ttl );
	}

	/**
	 * Delete cached value.
	 *
	 * @param string $key Cache key.
	 * @return bool True on success.
	 */
	public function delete( string $key ): bool {
		return wp_cache_delete( $key, $this->group );
	}

	/**
	 * Flush all cache in group.
	 *
	 * @return bool True on success.
	 */
	public function flush(): bool {
		return wp_cache_flush();
	}

	/**
	 * Remember value (get from cache or execute callback).
	 *
	 * @param string   $key      Cache key.
	 * @param callable $callback Function to execute if cache miss.
	 * @param int|null $ttl      Time to live in seconds.
	 * @return mixed Cached or computed value.
	 */
	public function remember( string $key, callable $callback, ?int $ttl = null ) {
		$value = $this->get( $key );

		if ( false !== $value ) {
			return $value;
		}

		$value = $callback();
		$this->set( $key, $value, $ttl );

		return $value;
	}

	/**
	 * Get transient value (persistent cache).
	 *
	 * @param string $key Transient key.
	 * @return mixed|false Transient value or false.
	 */
	public function get_transient( string $key ) {
		return get_transient( $this->get_transient_key( $key ) );
	}

	/**
	 * Set transient value (persistent cache).
	 *
	 * @param string   $key   Transient key.
	 * @param mixed    $value Value to store.
	 * @param int|null $ttl   Time to live in seconds.
	 * @return bool True on success.
	 */
	public function set_transient( string $key, $value, ?int $ttl = null ): bool {
		$ttl = $ttl ?? $this->default_ttl;
		return set_transient( $this->get_transient_key( $key ), $value, $ttl );
	}

	/**
	 * Delete transient.
	 *
	 * @param string $key Transient key.
	 * @return bool True on success.
	 */
	public function delete_transient( string $key ): bool {
		return delete_transient( $this->get_transient_key( $key ) );
	}

	/**
	 * Remember transient value.
	 *
	 * @param string   $key      Transient key.
	 * @param callable $callback Function to execute if not cached.
	 * @param int|null $ttl      Time to live in seconds.
	 * @return mixed Cached or computed value.
	 */
	public function remember_transient( string $key, callable $callback, ?int $ttl = null ) {
		$value = $this->get_transient( $key );

		if ( false !== $value ) {
			return $value;
		}

		$value = $callback();
		$this->set_transient( $key, $value, $ttl );

		return $value;
	}

	/**
	 * Get full transient key with prefix.
	 *
	 * @param string $key Base key.
	 * @return string Full transient key.
	 */
	private function get_transient_key( string $key ): string {
		return "{$this->group}_{$key}";
	}

	/**
	 * Clear cache by pattern (for object cache that supports it).
	 *
	 * @param string $pattern Cache key pattern.
	 * @return void
	 */
	public function delete_by_pattern( string $pattern ): void {
		// WordPress core doesn't support pattern deletion.
		// This would need Redis/Memcached with proper support.
		// For now, we just flush the entire cache.
		wp_cache_flush();
	}

	/**
	 * Get cache statistics (if supported).
	 *
	 * @return array Cache stats.
	 */
	public function get_stats(): array {
		// WordPress core doesn't provide cache stats.
		// This would need object cache plugin like Redis/Memcached.
		return [
			'enabled'     => wp_using_ext_object_cache(),
			'backend'     => wp_using_ext_object_cache() ? 'external' : 'database',
			'group'       => $this->group,
			'default_ttl' => $this->default_ttl,
		];
	}

	/**
	 * Warm cache with multiple keys.
	 *
	 * @param array    $keys     Array of cache keys.
	 * @param callable $callback Function that returns array of values keyed by cache key.
	 * @param int|null $ttl      Time to live.
	 * @return array Array of cached values.
	 */
	public function warm( array $keys, callable $callback, ?int $ttl = null ): array {
		$values = $callback( $keys );

		foreach ( $values as $key => $value ) {
			$this->set( $key, $value, $ttl );
		}

		return $values;
	}
}
