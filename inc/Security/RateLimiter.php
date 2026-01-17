<?php
/**
 * Rate Limiter
 *
 * Prevents API abuse by limiting request frequency.
 *
 * @package WPHelpZone\Iyoraa\Security
 */

namespace WPHelpZone\Iyoraa\Security;

/**
 * Rate Limiter class.
 *
 * Implements request throttling to prevent API abuse.
 */
class RateLimiter {

	/**
	 * Maximum requests allowed in the time window.
	 *
	 * @var int
	 */
	private $max_requests = 60;

	/**
	 * Time window in seconds.
	 *
	 * @var int
	 */
	private $window = 60;

	/**
	 * Cache group for rate limiting.
	 *
	 * @var string
	 */
	private $cache_group = 'iyoraa_rate_limit';

	/**
	 * Constructor.
	 *
	 * @param int $max_requests Maximum requests allowed (default: 60).
	 * @param int $window Time window in seconds (default: 60).
	 */
	public function __construct( int $max_requests = 60, int $window = 60 ) {
		$this->max_requests = $max_requests;
		$this->window       = $window;
	}

	/**
	 * Check if request is within rate limit.
	 *
	 * @param string $identifier User ID or IP address.
	 * @return bool True if within limit, false if exceeded.
	 */
	public function check_limit( string $identifier ): bool {
		$key   = 'rate_limit_' . md5( $identifier );
		$count = get_transient( $key );

		// First request in this window.
		if ( false === $count ) {
			set_transient( $key, 1, $this->window );
			return true;
		}

		// Limit exceeded.
		if ( $count >= $this->max_requests ) {
			return false;
		}

		// Increment counter.
		set_transient( $key, $count + 1, $this->window );
		return true;
	}

	/**
	 * Get current request count for identifier.
	 *
	 * @param string $identifier User ID or IP address.
	 * @return int Number of requests made in current window.
	 */
	public function get_request_count( string $identifier ): int {
		$key   = 'rate_limit_' . md5( $identifier );
		$count = get_transient( $key );

		return $count ? (int) $count : 0;
	}

	/**
	 * Get remaining requests for identifier.
	 *
	 * @param string $identifier User ID or IP address.
	 * @return int Number of requests remaining.
	 */
	public function get_remaining_requests( string $identifier ): int {
		$count     = $this->get_request_count( $identifier );
		$remaining = $this->max_requests - $count;

		return max( 0, $remaining );
	}

	/**
	 * Reset rate limit for identifier.
	 *
	 * @param string $identifier User ID or IP address.
	 * @return bool True on success.
	 */
	public function reset_limit( string $identifier ): bool {
		$key = 'rate_limit_' . md5( $identifier );
		return delete_transient( $key );
	}

	/**
	 * Get identifier for current request.
	 *
	 * Uses user ID if logged in, otherwise IP address.
	 *
	 * @return string Identifier.
	 */
	public function get_identifier(): string {
		$user_id = get_current_user_id();

		if ( $user_id ) {
			return 'user_' . $user_id;
		}

		return 'ip_' . $this->get_client_ip();
	}

	/**
	 * Get client IP address.
	 *
	 * @return string IP address.
	 */
	private function get_client_ip(): string {
		// Check for proxy headers first.
		if ( ! empty( $_SERVER['HTTP_CLIENT_IP'] ) ) {
			return sanitize_text_field( wp_unslash( $_SERVER['HTTP_CLIENT_IP'] ) );
		}

		if ( ! empty( $_SERVER['HTTP_X_FORWARDED_FOR'] ) ) {
			// X-Forwarded-For can contain multiple IPs, take the first one.
			$ip_list = explode( ',', sanitize_text_field( wp_unslash( $_SERVER['HTTP_X_FORWARDED_FOR'] ) ) );
			return trim( $ip_list[0] );
		}

		if ( ! empty( $_SERVER['REMOTE_ADDR'] ) ) {
			return sanitize_text_field( wp_unslash( $_SERVER['REMOTE_ADDR'] ) );
		}

		return 'unknown';
	}

	/**
	 * Check if identifier is currently blocked.
	 *
	 * @param string $identifier User ID or IP address.
	 * @return bool True if blocked.
	 */
	public function is_blocked( string $identifier ): bool {
		$count = $this->get_request_count( $identifier );
		return $count >= $this->max_requests;
	}

	/**
	 * Get time until rate limit resets.
	 *
	 * @param string $identifier User ID or IP address.
	 * @return int Seconds until reset, 0 if not limited.
	 */
	public function get_reset_time( string $identifier ): int {
		$key = 'rate_limit_' . md5( $identifier );
		$ttl = get_option( '_transient_timeout_' . $key );

		if ( ! $ttl ) {
			return 0;
		}

		$time_left = $ttl - time();
		return max( 0, $time_left );
	}
}
