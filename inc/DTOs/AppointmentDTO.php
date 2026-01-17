<?php
/**
 * Appointment Data Transfer Object
 *
 * Type-safe container for appointment data.
 *
 * @package WPHelpZone\Iyoraa\DTOs
 */

namespace WPHelpZone\Iyoraa\DTOs;

/**
 * Appointment DTO class.
 */
class AppointmentDTO {

	/**
	 * Appointment ID (database).
	 *
	 * @var int
	 */
	public $id;

	/**
	 * Appointment ID (APT-YYYY-####).
	 *
	 * @var string
	 */
	public $appointment_id;

	/**
	 * Patient ID (database).
	 *
	 * @var int
	 */
	public $patient_id;

	/**
	 * Patient name (denormalized for display).
	 *
	 * @var string|null
	 */
	public $patient_name;

	/**
	 * Doctor ID (database).
	 *
	 * @var int
	 */
	public $doctor_id;

	/**
	 * Doctor name (denormalized for display).
	 *
	 * @var string|null
	 */
	public $doctor_name;

	/**
	 * Appointment date (Y-m-d).
	 *
	 * @var string
	 */
	public $appointment_date;

	/**
	 * Appointment time (H:i:s).
	 *
	 * @var string
	 */
	public $appointment_time;

	/**
	 * Duration in minutes.
	 *
	 * @var int
	 */
	public $duration;

	/**
	 * Appointment type (consultation, follow-up, emergency, etc.).
	 *
	 * @var string
	 */
	public $appointment_type;

	/**
	 * Status (scheduled, confirmed, in-progress, completed, cancelled, no-show).
	 *
	 * @var string
	 */
	public $status;

	/**
	 * Purpose/reason for appointment.
	 *
	 * @var string|null
	 */
	public $purpose;

	/**
	 * Appointment notes.
	 *
	 * @var string|null
	 */
	public $notes;

	/**
	 * Reminder sent flag.
	 *
	 * @var bool
	 */
	public $reminder_sent;

	/**
	 * Created at timestamp.
	 *
	 * @var string
	 */
	public $created_at;

	/**
	 * Updated at timestamp.
	 *
	 * @var string
	 */
	public $updated_at;

	/**
	 * Create DTO from array.
	 *
	 * @param array $data Appointment data.
	 * @return self
	 */
	public static function from_array( array $data ): self {
		$dto = new self();

		$dto->id                = isset( $data['id'] ) ? (int) $data['id'] : null;
		$dto->appointment_id    = $data['appointment_id'] ?? '';
		$dto->patient_id        = isset( $data['patient_id'] ) ? (int) $data['patient_id'] : null;
		$dto->patient_name      = $data['patient_name'] ?? null;
		$dto->doctor_id         = isset( $data['doctor_id'] ) ? (int) $data['doctor_id'] : null;
		$dto->doctor_name       = $data['doctor_name'] ?? null;
		$dto->appointment_date  = $data['appointment_date'] ?? '';
		$dto->appointment_time  = $data['appointment_time'] ?? '';
		$dto->duration          = isset( $data['duration'] ) ? (int) $data['duration'] : 30;
		$dto->appointment_type  = $data['appointment_type'] ?? 'consultation';
		$dto->status            = $data['status'] ?? 'scheduled';
		$dto->purpose           = $data['purpose'] ?? null;
		$dto->notes             = $data['notes'] ?? null;
		$dto->reminder_sent     = ! empty( $data['reminder_sent'] );
		$dto->created_at        = $data['created_at'] ?? '';
		$dto->updated_at        = $data['updated_at'] ?? '';

		return $dto;
	}

	/**
	 * Convert DTO to array.
	 *
	 * @return array
	 */
	public function to_array(): array {
		return [
			'id'                => $this->id,
			'appointment_id'    => $this->appointment_id,
			'patient_id'        => $this->patient_id,
			'patient_name'      => $this->patient_name,
			'doctor_id'         => $this->doctor_id,
			'doctor_name'       => $this->doctor_name,
			'appointment_date'  => $this->appointment_date,
			'appointment_time'  => $this->appointment_time,
			'duration'          => $this->duration,
			'appointment_type'  => $this->appointment_type,
			'status'            => $this->status,
			'purpose'           => $this->purpose,
			'notes'             => $this->notes,
			'reminder_sent'     => $this->reminder_sent,
			'created_at'        => $this->created_at,
			'updated_at'        => $this->updated_at,
		];
	}

	/**
	 * Check if appointment is upcoming.
	 *
	 * @return bool
	 */
	public function is_upcoming(): bool {
		$appointment_datetime = strtotime( $this->appointment_date . ' ' . $this->appointment_time );
		return $appointment_datetime > time() && in_array( $this->status, [ 'scheduled', 'confirmed' ], true );
	}

	/**
	 * Check if appointment is today.
	 *
	 * @return bool
	 */
	public function is_today(): bool {
		return $this->appointment_date === gmdate( 'Y-m-d' );
	}

	/**
	 * Check if appointment is past.
	 *
	 * @return bool
	 */
	public function is_past(): bool {
		$appointment_datetime = strtotime( $this->appointment_date . ' ' . $this->appointment_time );
		return $appointment_datetime < time();
	}

	/**
	 * Check if appointment is completed.
	 *
	 * @return bool
	 */
	public function is_completed(): bool {
		return 'completed' === $this->status;
	}

	/**
	 * Check if appointment is cancelled.
	 *
	 * @return bool
	 */
	public function is_cancelled(): bool {
		return 'cancelled' === $this->status;
	}

	/**
	 * Check if appointment is active (not cancelled or completed).
	 *
	 * @return bool
	 */
	public function is_active(): bool {
		return ! in_array( $this->status, [ 'cancelled', 'completed', 'no-show' ], true );
	}

	/**
	 * Get appointment datetime as timestamp.
	 *
	 * @return int
	 */
	public function get_timestamp(): int {
		return strtotime( $this->appointment_date . ' ' . $this->appointment_time );
	}

	/**
	 * Get formatted appointment date.
	 *
	 * @param string $format Date format.
	 * @return string
	 */
	public function get_formatted_date( string $format = 'M d, Y' ): string {
		return gmdate( $format, strtotime( $this->appointment_date ) );
	}

	/**
	 * Get formatted appointment time.
	 *
	 * @param string $format Time format.
	 * @return string
	 */
	public function get_formatted_time( string $format = 'g:i A' ): string {
		return gmdate( $format, strtotime( $this->appointment_time ) );
	}

	/**
	 * Get end time of appointment.
	 *
	 * @return string
	 */
	public function get_end_time(): string {
		$start_timestamp = strtotime( $this->appointment_time );
		$end_timestamp   = $start_timestamp + ( $this->duration * 60 );
		return gmdate( 'H:i:s', $end_timestamp );
	}

	/**
	 * Get appointment duration in hours and minutes.
	 *
	 * @return string
	 */
	public function get_duration_formatted(): string {
		$hours   = floor( $this->duration / 60 );
		$minutes = $this->duration % 60;

		if ( $hours > 0 && $minutes > 0 ) {
			return sprintf( '%dh %dm', $hours, $minutes );
		} elseif ( $hours > 0 ) {
			return sprintf( '%dh', $hours );
		} else {
			return sprintf( '%dm', $minutes );
		}
	}

	/**
	 * Get status label with color.
	 *
	 * @return array
	 */
	public function get_status_info(): array {
		$statuses = [
			'scheduled'   => [ 'label' => 'Scheduled', 'color' => 'blue' ],
			'confirmed'   => [ 'label' => 'Confirmed', 'color' => 'green' ],
			'in-progress' => [ 'label' => 'In Progress', 'color' => 'purple' ],
			'completed'   => [ 'label' => 'Completed', 'color' => 'gray' ],
			'cancelled'   => [ 'label' => 'Cancelled', 'color' => 'red' ],
			'no-show'     => [ 'label' => 'No Show', 'color' => 'orange' ],
		];

		return $statuses[ $this->status ] ?? [ 'label' => ucfirst( $this->status ), 'color' => 'gray' ];
	}
}
