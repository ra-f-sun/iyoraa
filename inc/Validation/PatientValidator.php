<?php
/**
 * Patient Validator
 *
 * Validates patient data before save.
 *
 * @package WPHelpZone\Iyoraa\Validation
 */

namespace WPHelpZone\Iyoraa\Validation;

/**
 * Patient Validator class.
 */
class PatientValidator extends Validator {

	/**
	 * Validate patient data.
	 *
	 * @param array $data Patient data to validate.
	 * @return bool True if valid, false otherwise.
	 */
	public function validate( array $data ): bool {
		$this->clear_errors();

		// Full name.
		if ( ! $this->validate_required( $data['full_name'] ?? '', 'full_name' ) ) {
			return false;
		}
		$this->validate_length( $data['full_name'] ?? '', 'full_name', 2, 200 );

		// Age.
		if ( ! $this->validate_required( $data['age'] ?? '', 'age' ) ) {
			return false;
		}
		if ( $this->validate_numeric( $data['age'] ?? '', 'age' ) ) {
			$this->validate_age( (int) $data['age'], 'age' );
		}

		// Gender.
		if ( ! $this->validate_required( $data['gender'] ?? '', 'gender' ) ) {
			return false;
		}
		$this->validate_gender( $data['gender'] ?? '', 'gender' );

		// Phone.
		if ( ! $this->validate_required( $data['phone'] ?? '', 'phone' ) ) {
			return false;
		}
		$this->validate_phone( $data['phone'] ?? '', 'phone' );

		// Email (optional).
		if ( ! empty( $data['email'] ) ) {
			$this->validate_email( $data['email'], 'email' );
		}

		// Address (optional).
		if ( ! empty( $data['address'] ) ) {
			$this->validate_length( $data['address'], 'address', 0, 500 );
		}

		// Blood group (optional).
		if ( ! empty( $data['blood_group'] ) ) {
			$this->validate_blood_group( $data['blood_group'], 'blood_group' );
		}

		// Emergency contact name (optional).
		if ( ! empty( $data['emergency_contact_name'] ) ) {
			$this->validate_length( $data['emergency_contact_name'], 'emergency_contact_name', 2, 200 );
		}

		// Emergency contact phone (optional).
		if ( ! empty( $data['emergency_contact_phone'] ) ) {
			$this->validate_phone( $data['emergency_contact_phone'], 'emergency_contact_phone' );
		}

		// Medical history (optional).
		if ( ! empty( $data['medical_history'] ) ) {
			$this->validate_length( $data['medical_history'], 'medical_history', 0, 5000 );
		}

		return ! $this->has_errors();
	}

	/**
	 * Sanitize patient data.
	 *
	 * @param array $data Raw patient data.
	 * @return array Sanitized data.
	 */
	public function sanitize( array $data ): array {
		return [
			'full_name'               => sanitize_text_field( $data['full_name'] ?? '' ),
			'age'                     => absint( $data['age'] ?? 0 ),
			'gender'                  => in_array( $data['gender'] ?? '', [ 'male', 'female', 'other' ], true )
				? $data['gender']
				: 'other',
			'phone'                   => sanitize_text_field( $data['phone'] ?? '' ),
			'email'                   => sanitize_email( $data['email'] ?? '' ),
			'address'                 => sanitize_textarea_field( $data['address'] ?? '' ),
			'blood_group'             => $this->sanitize_blood_group( $data['blood_group'] ?? '' ),
			'emergency_contact_name'  => sanitize_text_field( $data['emergency_contact_name'] ?? '' ),
			'emergency_contact_phone' => sanitize_text_field( $data['emergency_contact_phone'] ?? '' ),
			'medical_history'         => sanitize_textarea_field( $data['medical_history'] ?? '' ),
		];
	}

	/**
	 * Sanitize blood group value.
	 *
	 * @param string $group Blood group.
	 * @return string Sanitized blood group or empty string.
	 */
	private function sanitize_blood_group( string $group ): string {
		$valid = [ 'A+', 'A-', 'B+', 'B-', 'O+', 'O-', 'AB+', 'AB-' ];
		return in_array( $group, $valid, true ) ? $group : '';
	}
}
