<?php
/**
 * Validation Exception
 *
 * Thrown when data validation fails.
 *
 * @package WPHelpZone\Iyoraa\Exceptions
 */

namespace WPHelpZone\Iyoraa\Exceptions;

/**
 * Validation Exception class.
 */
class ValidationException extends IyoraaException {

	/**
	 * Validation errors.
	 *
	 * @var array
	 */
	private $errors;

	/**
	 * Constructor.
	 *
	 * @param array  $errors  Validation errors.
	 * @param string $message Error message.
	 */
	public function __construct( array $errors, string $message = 'Validation failed' ) {
		$this->errors = $errors;
		parent::__construct( $message, 400, [ 'errors' => $errors ] );
	}

	/**
	 * Get validation errors.
	 *
	 * @return array Validation errors.
	 */
	public function get_errors(): array {
		return $this->errors;
	}

	/**
	 * Get error code for WP_Error.
	 *
	 * @return string Error code.
	 */
	protected function get_error_code(): string {
		return 'validation_failed';
	}
}
