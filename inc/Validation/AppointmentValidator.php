<?php
/**
 * Appointment Validator
 *
 * Validates appointment data.
 *
 * @package WPHelpZone\Iyoraa\Validation
 */

namespace WPHelpZone\Iyoraa\Validation;

/**
 * Appointment Validator class.
 */
class AppointmentValidator extends Validator {

	/**
	 * Validate appointment data.
	 *
	 * @param array $data Appointment data to validate.
	 * @return array Array of validation errors (empty if valid).
	 */
	public static function validate( array $data ): array {
		$errors = [];

		// Validate patient_id (required).
		if ( empty( $data['patient_id'] ) ) {
			$errors['patient_id'] = __( 'Patient ID is required.', 'iyoraa' );
		} elseif ( ! is_numeric( $data['patient_id'] ) || $data['patient_id'] <= 0 ) {
			$errors['patient_id'] = __( 'Patient ID must be a valid positive integer.', 'iyoraa' );
		}

		// Validate doctor_id (required).
		if ( empty( $data['doctor_id'] ) ) {
			$errors['doctor_id'] = __( 'Doctor ID is required.', 'iyoraa' );
		} elseif ( ! is_numeric( $data['doctor_id'] ) || $data['doctor_id'] <= 0 ) {
			$errors['doctor_id'] = __( 'Doctor ID must be a valid positive integer.', 'iyoraa' );
		}

		// Validate appointment_date (required, valid date format, not in past).
		if ( empty( $data['appointment_date'] ) ) {
			$errors['appointment_date'] = __( 'Appointment date is required.', 'iyoraa' );
		} elseif ( ! self::validate_date( $data['appointment_date'] ) ) {
			$errors['appointment_date'] = __( 'Appointment date must be in Y-m-d format.', 'iyoraa' );
		} elseif ( strtotime( $data['appointment_date'] ) < strtotime( gmdate( 'Y-m-d' ) ) ) {
			$errors['appointment_date'] = __( 'Appointment date cannot be in the past.', 'iyoraa' );
		}

		// Validate appointment_time (required, valid time format, business hours).
		if ( empty( $data['appointment_time'] ) ) {
			$errors['appointment_time'] = __( 'Appointment time is required.', 'iyoraa' );
		} elseif ( ! self::validate_time( $data['appointment_time'] ) ) {
			$errors['appointment_time'] = __( 'Appointment time must be in H:i format (e.g., 14:30).', 'iyoraa' );
		} elseif ( ! self::is_business_hours( $data['appointment_time'] ) ) {
			$errors['appointment_time'] = __( 'Appointment time must be within business hours (8:00 AM - 6:00 PM).', 'iyoraa' );
		}

		// Validate duration (optional, must be positive).
		if ( isset( $data['duration'] ) && ! empty( $data['duration'] ) ) {
			if ( ! is_numeric( $data['duration'] ) || $data['duration'] <= 0 ) {
				$errors['duration'] = __( 'Duration must be a positive number (in minutes).', 'iyoraa' );
			} elseif ( $data['duration'] > 480 ) { // Max 8 hours.
				$errors['duration'] = __( 'Duration cannot exceed 480 minutes (8 hours).', 'iyoraa' );
			}
		}

		// Validate appointment_type (optional, must be valid enum).
		if ( isset( $data['appointment_type'] ) && ! empty( $data['appointment_type'] ) ) {
			$valid_types = [ 'consultation', 'follow-up', 'check-up', 'emergency', 'surgery', 'therapy', 'other' ];
			if ( ! in_array( strtolower( $data['appointment_type'] ), $valid_types, true ) ) {
				$errors['appointment_type'] = __( 'Invalid appointment type. Valid types: consultation, follow-up, check-up, emergency, surgery, therapy, other.', 'iyoraa' );
			}
		}

		// Validate status (optional, must be valid enum).
		if ( isset( $data['status'] ) && ! empty( $data['status'] ) ) {
			$valid_statuses = [ 'scheduled', 'confirmed', 'in-progress', 'completed', 'cancelled', 'no-show' ];
			if ( ! in_array( strtolower( $data['status'] ), $valid_statuses, true ) ) {
				$errors['status'] = __( 'Invalid status. Valid statuses: scheduled, confirmed, in-progress, completed, cancelled, no-show.', 'iyoraa' );
			}
		}

		// Validate purpose (optional, max length).
		if ( isset( $data['purpose'] ) && ! empty( $data['purpose'] ) ) {
			if ( ! self::validate_length( $data['purpose'], 5, 500 ) ) {
				$errors['purpose'] = __( 'Purpose must be between 5 and 500 characters.', 'iyoraa' );
			}
		}

		// Validate notes (optional, max length).
		if ( isset( $data['notes'] ) && ! empty( $data['notes'] ) ) {
			if ( strlen( $data['notes'] ) > 2000 ) {
				$errors['notes'] = __( 'Notes cannot exceed 2000 characters.', 'iyoraa' );
			}
		}

		return $errors;
	}

	/**
	 * Sanitize appointment data.
	 *
	 * @param array $data Appointment data to sanitize.
	 * @return array Sanitized appointment data.
	 */
	public static function sanitize( array $data ): array {
		return [
			'patient_id'        => isset( $data['patient_id'] ) ? absint( $data['patient_id'] ) : 0,
			'doctor_id'         => isset( $data['doctor_id'] ) ? absint( $data['doctor_id'] ) : 0,
			'appointment_date'  => isset( $data['appointment_date'] ) ? sanitize_text_field( $data['appointment_date'] ) : '',
			'appointment_time'  => isset( $data['appointment_time'] ) ? sanitize_text_field( $data['appointment_time'] ) : '',
			'duration'          => isset( $data['duration'] ) ? absint( $data['duration'] ) : 30,
			'appointment_type'  => isset( $data['appointment_type'] ) ? strtolower( sanitize_text_field( $data['appointment_type'] ) ) : 'consultation',
			'status'            => isset( $data['status'] ) ? strtolower( sanitize_text_field( $data['status'] ) ) : 'scheduled',
			'purpose'           => isset( $data['purpose'] ) ? sanitize_text_field( $data['purpose'] ) : '',
			'notes'             => isset( $data['notes'] ) ? sanitize_textarea_field( $data['notes'] ) : '',
			'reminder_sent'     => isset( $data['reminder_sent'] ) ? (bool) $data['reminder_sent'] : false,
		];
	}

	/**
	 * Validate date format (Y-m-d).
	 *
	 * @param string $date Date string.
	 * @return bool
	 */
	private static function validate_date( string $date ): bool {
		$d = \DateTime::createFromFormat( 'Y-m-d', $date );
		return $d && $d->format( 'Y-m-d' ) === $date;
	}

	/**
	 * Validate time format (H:i or H:i:s).
	 *
	 * @param string $time Time string.
	 * @return bool
	 */
	private static function validate_time( string $time ): bool {
		$t1 = \DateTime::createFromFormat( 'H:i', $time );
		$t2 = \DateTime::createFromFormat( 'H:i:s', $time );
		return ( $t1 && $t1->format( 'H:i' ) === $time ) || ( $t2 && $t2->format( 'H:i:s' ) === $time );
	}

	/**
	 * Check if time is within business hours (8 AM - 6 PM).
	 *
	 * @param string $time Time string (H:i or H:i:s).
	 * @return bool
	 */
	private static function is_business_hours( string $time ): bool {
		$timestamp = strtotime( $time );
		$hour      = (int) gmdate( 'G', $timestamp );
		return $hour >= 8 && $hour < 18; // 8 AM to 6 PM.
	}
}
