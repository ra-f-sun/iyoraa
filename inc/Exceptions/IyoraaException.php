<?php
/**
 * Base Iyoraa Exception
 *
 * All custom exceptions extend this class.
 *
 * @package WPHelpZone\Iyoraa\Exceptions
 */

namespace WPHelpZone\Iyoraa\Exceptions;

use Exception;
use Throwable;

/**
 * Base Iyoraa Exception class.
 */
class IyoraaException extends Exception {

	/**
	 * Additional context data.
	 *
	 * @var array
	 */
	protected $context = [];

	/**
	 * Constructor.
	 *
	 * @param string         $message  Error message.
	 * @param int            $code     Error code.
	 * @param array          $context  Additional context.
	 * @param Throwable|null $previous Previous exception.
	 */
	public function __construct(
		string $message = '',
		int $code = 0,
		array $context = [],
		?Throwable $previous = null
	) {
		$this->context = $context;
		parent::__construct( $message, $code, $previous );
	}

	/**
	 * Get context data.
	 *
	 * @return array Context data.
	 */
	public function get_context(): array {
		return $this->context;
	}

	/**
	 * Convert to WP_Error.
	 *
	 * @return \WP_Error WordPress error object.
	 */
	public function to_wp_error(): \WP_Error {
		return new \WP_Error(
			$this->get_error_code(),
			$this->getMessage(),
			[
				'status'  => $this->getCode(),
				'context' => $this->context,
			]
		);
	}

	/**
	 * Get error code for WP_Error.
	 *
	 * @return string Error code.
	 */
	protected function get_error_code(): string {
		return 'iyoraa_error';
	}
}
