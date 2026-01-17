<?php
/**
 * Base Validator
 *
 * Provides common validation methods for all entities.
 *
 * @package WPHelpZone\Iyoraa\Validation
 */

namespace WPHelpZone\Iyoraa\Validation;

/**
 * Base Validator class.
 *
 * Contains reusable validation logic.
 */
class Validator {

	/**
	 * Validation errors.
	 *
	 * @var array
	 */
	protected $errors = [];

	/**
	 * Validate email format.
	 *
	 * @param string $email Email address to validate.
	 * @param string $field Field name for error message.
	 * @return bool True if valid.
	 */
	protected function validate_email( string $email, string $field = 'email' ): bool {
		if ( ! empty( $email ) && ! is_email( $email ) ) {
			$this->add_error( $field, "{$field} must be a valid email address" );
			return false;
		}
		return true;
	}

	/**
	 * Validate required field.
	 *
	 * @param mixed  $value Value to check.
	 * @param string $field Field name.
	 * @return bool True if not empty.
	 */
	protected function validate_required( $value, string $field ): bool {
		if ( empty( $value ) && $value !== '0' && $value !== 0 ) {
			$this->add_error( $field, "{$field} is required" );
			return false;
		}
		return true;
	}

	/**
	 * Validate string length.
	 *
	 * @param string $value  Value to check.
	 * @param string $field  Field name.
	 * @param int    $min    Minimum length.
	 * @param int    $max    Maximum length.
	 * @return bool True if within range.
	 */
	protected function validate_length( string $value, string $field, int $min = 0, int $max = PHP_INT_MAX ): bool {
		$length = strlen( $value );
		
		if ( $length < $min ) {
			$this->add_error( $field, "{$field} must be at least {$min} characters" );
			return false;
		}
		
		if ( $length > $max ) {
			$this->add_error( $field, "{$field} must not exceed {$max} characters" );
			return false;
		}
		
		return true;
	}

	/**
	 * Validate numeric value.
	 *
	 * @param mixed  $value Value to check.
	 * @param string $field Field name.
	 * @return bool True if numeric.
	 */
	protected function validate_numeric( $value, string $field ): bool {
		if ( ! is_numeric( $value ) ) {
			$this->add_error( $field, "{$field} must be a number" );
			return false;
		}
		return true;
	}

	/**
	 * Validate number range.
	 *
	 * @param int|float $value Value to check.
	 * @param string    $field Field name.
	 * @param int|float $min   Minimum value.
	 * @param int|float $max   Maximum value.
	 * @return bool True if within range.
	 */
	protected function validate_range( $value, string $field, $min = PHP_INT_MIN, $max = PHP_INT_MAX ): bool {
		if ( $value < $min || $value > $max ) {
			$this->add_error( $field, "{$field} must be between {$min} and {$max}" );
			return false;
		}
		return true;
	}

	/**
	 * Validate value is in allowed list.
	 *
	 * @param mixed  $value   Value to check.
	 * @param string $field   Field name.
	 * @param array  $allowed Allowed values.
	 * @return bool True if in list.
	 */
	protected function validate_in( $value, string $field, array $allowed ): bool {
		if ( ! in_array( $value, $allowed, true ) ) {
			$allowed_str = implode( ', ', $allowed );
			$this->add_error( $field, "{$field} must be one of: {$allowed_str}" );
			return false;
		}
		return true;
	}

	/**
	 * Validate phone number format.
	 *
	 * @param string $phone Phone number.
	 * @param string $field Field name.
	 * @return bool True if valid format.
	 */
	protected function validate_phone( string $phone, string $field = 'phone' ): bool {
		// Remove common formatting characters.
		$cleaned = preg_replace( '/[^0-9+]/', '', $phone );
		
		// Must be 10-20 digits (supports international).
		if ( strlen( $cleaned ) < 10 || strlen( $cleaned ) > 20 ) {
			$this->add_error( $field, "{$field} must be 10-20 digits" );
			return false;
		}
		
		return true;
	}

	/**
	 * Validate age.
	 *
	 * @param int    $age   Age value.
	 * @param string $field Field name.
	 * @return bool True if valid age.
	 */
	protected function validate_age( int $age, string $field = 'age' ): bool {
		if ( $age < 0 || $age > 150 ) {
			$this->add_error( $field, "{$field} must be between 0 and 150" );
			return false;
		}
		return true;
	}

	/**
	 * Validate blood group.
	 *
	 * @param string $blood_group Blood group value.
	 * @param string $field       Field name.
	 * @return bool True if valid blood group.
	 */
	protected function validate_blood_group( string $blood_group, string $field = 'blood_group' ): bool {
		if ( empty( $blood_group ) ) {
			return true; // Optional field.
		}
		
		$valid_groups = [ 'A+', 'A-', 'B+', 'B-', 'O+', 'O-', 'AB+', 'AB-' ];
		return $this->validate_in( $blood_group, $field, $valid_groups );
	}

	/**
	 * Validate gender.
	 *
	 * @param string $gender Gender value.
	 * @param string $field  Field name.
	 * @return bool True if valid gender.
	 */
	protected function validate_gender( string $gender, string $field = 'gender' ): bool {
		$valid_genders = [ 'male', 'female', 'other' ];
		return $this->validate_in( $gender, $field, $valid_genders );
	}

	/**
	 * Add validation error.
	 *
	 * @param string $field   Field name.
	 * @param string $message Error message.
	 * @return void
	 */
	protected function add_error( string $field, string $message ): void {
		if ( ! isset( $this->errors[ $field ] ) ) {
			$this->errors[ $field ] = [];
		}
		$this->errors[ $field ][] = $message;
	}

	/**
	 * Get all validation errors.
	 *
	 * @return array Errors grouped by field.
	 */
	public function get_errors(): array {
		return $this->errors;
	}

	/**
	 * Check if validation has errors.
	 *
	 * @return bool True if errors exist.
	 */
	public function has_errors(): bool {
		return ! empty( $this->errors );
	}

	/**
	 * Get first error message.
	 *
	 * @return string|null First error message or null.
	 */
	public function get_first_error(): ?string {
		if ( empty( $this->errors ) ) {
			return null;
		}
		
		$first_field = array_key_first( $this->errors );
		return $this->errors[ $first_field ][0] ?? null;
	}

	/**
	 * Clear all errors.
	 *
	 * @return void
	 */
	public function clear_errors(): void {
		$this->errors = [];
	}

	/**
	 * Sanitize data.
	 *
	 * @param array $data Raw data.
	 * @return array Sanitized data.
	 */
	public function sanitize( array $data ): array {
		$sanitized = [];
		
		foreach ( $data as $key => $value ) {
			if ( is_string( $value ) ) {
				$sanitized[ $key ] = sanitize_text_field( $value );
			} elseif ( is_int( $value ) ) {
				$sanitized[ $key ] = absint( $value );
			} elseif ( is_array( $value ) ) {
				$sanitized[ $key ] = $this->sanitize( $value );
			} else {
				$sanitized[ $key ] = $value;
			}
		}
		
		return $sanitized;
	}
}
