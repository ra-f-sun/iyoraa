<?php
/**
 * Patient Data Transfer Object
 *
 * Type-safe container for patient data.
 *
 * @package WPHelpZone\Iyoraa\DTOs
 */

namespace WPHelpZone\Iyoraa\DTOs;

/**
 * Patient DTO class.
 */
class PatientDTO {

	/**
	 * Patient ID (database).
	 *
	 * @var int
	 */
	public $id;

	/**
	 * Patient ID (HOS-YYYY-####).
	 *
	 * @var string
	 */
	public $patient_id;

	/**
	 * Full name.
	 *
	 * @var string
	 */
	public $full_name;

	/**
	 * Age.
	 *
	 * @var int
	 */
	public $age;

	/**
	 * Gender.
	 *
	 * @var string
	 */
	public $gender;

	/**
	 * Phone number.
	 *
	 * @var string
	 */
	public $phone;

	/**
	 * Email address.
	 *
	 * @var string|null
	 */
	public $email;

	/**
	 * Address.
	 *
	 * @var string|null
	 */
	public $address;

	/**
	 * Blood group.
	 *
	 * @var string|null
	 */
	public $blood_group;

	/**
	 * Emergency contact name.
	 *
	 * @var string|null
	 */
	public $emergency_contact_name;

	/**
	 * Emergency contact phone.
	 *
	 * @var string|null
	 */
	public $emergency_contact_phone;

	/**
	 * Medical history.
	 *
	 * @var string|null
	 */
	public $medical_history;

	/**
	 * Status.
	 *
	 * @var string
	 */
	public $status;

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
	 * Private constructor to force use of factory methods.
	 */
	private function __construct() {
	}

	/**
	 * Create DTO from array data.
	 *
	 * @param array $data Patient data from database.
	 * @return self Patient DTO instance.
	 */
	public static function from_array( array $data ): self {
		$dto = new self();

		$dto->id                       = (int) $data['id'];
		$dto->patient_id               = $data['patient_id'];
		$dto->full_name                = $data['full_name'];
		$dto->age                      = (int) $data['age'];
		$dto->gender                   = $data['gender'];
		$dto->phone                    = $data['phone'];
		$dto->email                    = $data['email'] ?? null;
		$dto->address                  = $data['address'] ?? null;
		$dto->blood_group              = $data['blood_group'] ?? null;
		$dto->emergency_contact_name   = $data['emergency_contact_name'] ?? null;
		$dto->emergency_contact_phone  = $data['emergency_contact_phone'] ?? null;
		$dto->medical_history          = $data['medical_history'] ?? null;
		$dto->status                   = $data['status'];
		$dto->created_at               = $data['created_at'];
		$dto->updated_at               = $data['updated_at'];

		return $dto;
	}

	/**
	 * Convert DTO to array.
	 *
	 * @return array Patient data as array.
	 */
	public function to_array(): array {
		return [
			'id'                      => $this->id,
			'patient_id'              => $this->patient_id,
			'full_name'               => $this->full_name,
			'age'                     => $this->age,
			'gender'                  => $this->gender,
			'phone'                   => $this->phone,
			'email'                   => $this->email,
			'address'                 => $this->address,
			'blood_group'             => $this->blood_group,
			'emergency_contact_name'  => $this->emergency_contact_name,
			'emergency_contact_phone' => $this->emergency_contact_phone,
			'medical_history'         => $this->medical_history,
			'status'                  => $this->status,
			'created_at'              => $this->created_at,
			'updated_at'              => $this->updated_at,
		];
	}

	/**
	 * Check if patient is active.
	 *
	 * @return bool True if active.
	 */
	public function is_active(): bool {
		return $this->status === 'active';
	}

	/**
	 * Get patient age.
	 *
	 * @return int Age.
	 */
	public function get_age(): int {
		return $this->age;
	}

	/**
	 * Get patient full name.
	 *
	 * @return string Full name.
	 */
	public function get_full_name(): string {
		return $this->full_name;
	}

	/**
	 * Get formatted phone number.
	 *
	 * @return string Formatted phone.
	 */
	public function get_phone(): string {
		return $this->phone;
	}

	/**
	 * Check if patient has email.
	 *
	 * @return bool True if email exists.
	 */
	public function has_email(): bool {
		return ! empty( $this->email );
	}

	/**
	 * Check if patient has emergency contact.
	 *
	 * @return bool True if emergency contact exists.
	 */
	public function has_emergency_contact(): bool {
		return ! empty( $this->emergency_contact_name ) || ! empty( $this->emergency_contact_phone );
	}

	/**
	 * Get display name (for UI).
	 *
	 * @return string Display name.
	 */
	public function get_display_name(): string {
		return "{$this->full_name} ({$this->patient_id})";
	}

	/**
	 * Get age group.
	 *
	 * @return string Age group (infant, child, adult, senior).
	 */
	public function get_age_group(): string {
		if ( $this->age < 2 ) {
			return 'infant';
		} elseif ( $this->age < 18 ) {
			return 'child';
		} elseif ( $this->age < 65 ) {
			return 'adult';
		}
		return 'senior';
	}
}
