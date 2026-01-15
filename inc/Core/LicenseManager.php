<?php

/**
 * License Manager
 *
 * Manages license tiers and feature access.
 * MVP: Simple tier management with manual override.
 * Future: API validation with license server.
 *
 * @package WPHelpZone\Iyoraa\Core
 */

namespace WPHelpZone\Iyoraa\Core;

/**
 * License Manager class.
 *
 * Manages license tiers, feature access, and resource limits.
 */
class LicenseManager {



	/**
	 * Get current license tier.
	 *
	 * @return string 'free', 'pro-starter', 'pro-business', or 'enterprise'
	 */
	public static function get_tier() {
		// For MVP, always return 'free' unless manually overridden.
		return get_option( 'iyoraa_license_tier', 'free' );
	}

	/**
	 * Set license tier (admin override for testing).
	 *
	 * @param string $tier The tier to set.
	 * @return bool Success status.
	 */
	public static function set_tier( $tier ) {
		if ( ! current_user_can( 'manage_options' ) ) {
			return false;
		}

		$valid_tiers = [ 'free', 'pro-starter', 'pro-business', 'enterprise' ];
		if ( ! in_array( $tier, $valid_tiers, true ) ) {
			return false;
		}

		update_option( 'iyoraa_license_tier', $tier );
		update_option( 'iyoraa_license_override', true );

		return true;
	}

	/**
	 * Check if a feature is available in current tier.
	 *
	 * @param string $feature The feature to check.
	 * @return bool Whether feature is available.
	 */
	public static function has_feature( $feature ) {
		$tier     = self::get_tier();
		$features = self::get_tier_features( $tier );

		return in_array( $feature, $features, true );
	}

	/**
	 * Check if limit is reached for a resource.
	 *
	 * @param string $resource The resource type (patients, appointments, etc).
	 * @param int    $current_count Current count.
	 * @return bool Whether limit is reached.
	 */
	public static function is_limit_reached( $resource, $current_count ) {
		$tier   = self::get_tier();
		$limits = self::get_tier_limits( $tier );

		if ( ! isset( $limits[ $resource ] ) ) {
			return false; // No limit.
		}

		$limit = $limits[ $resource ];
		if ( -1 === $limit ) {
			return false; // Unlimited.
		}

		return $current_count >= $limit;
	}

	/**
	 * Get features for a tier.
	 *
	 * @param string $tier The tier.
	 * @return array Features available.
	 */
	private static function get_tier_features( $tier ) {
		$features = [
			'free'         => [
				'patients',
				'appointments',
				'billing',
				'basic_reports',
				'audit_log',
			],
			'pro-starter'  => [
				'patients',
				'appointments',
				'billing',
				'basic_reports',
				'audit_log',
				'ipd',
				'laboratory',
				'calendar_view',
				'barcode_cards',
				'pdf_export',
			],
			'pro-business' => [
				'patients',
				'appointments',
				'billing',
				'basic_reports',
				'audit_log',
				'ipd',
				'laboratory',
				'calendar_view',
				'barcode_cards',
				'pdf_export',
				'ot',
				'prescriptions',
				'pharmacy',
				'hr_payroll',
				'notifications',
				'advanced_reports',
				'automated_backup',
			],
			'enterprise'   => [
				// All features.
				'patients',
				'appointments',
				'billing',
				'basic_reports',
				'audit_log',
				'ipd',
				'laboratory',
				'calendar_view',
				'barcode_cards',
				'pdf_export',
				'ot',
				'prescriptions',
				'pharmacy',
				'hr_payroll',
				'notifications',
				'advanced_reports',
				'automated_backup',
				'biometric',
				'whatsapp',
				'patient_portal',
				'gdpr_tools',
				'payment_gateway',
			],
		];

		return $features[ $tier ] ?? $features['free'];
	}

	/**
	 * Get limits for a tier.
	 *
	 * @param string $tier The tier.
	 * @return array Limits (-1 = unlimited).
	 */
	private static function get_tier_limits( $tier ) {
		$limits = [
			'free'         => [
				'patients'             => 100,
				'appointments_monthly' => 50,
				'invoices_monthly'     => 100,
				'staff'                => 5,
				'doctors'              => 2,
				'audit_retention_days' => 30,
			],
			'pro-starter'  => [
				'patients'             => -1,         // Unlimited.
				'appointments_monthly' => -1,
				'invoices_monthly'     => -1,
				'staff'                => 20,
				'doctors'              => -1,
				'beds'                 => 4,
				'lab_test_types'       => 10,
				'audit_retention_days' => 90,
			],
			'pro-business' => [
				'patients'               => -1,
				'appointments_monthly'   => -1,
				'invoices_monthly'       => -1,
				'staff'                  => -1,
				'doctors'                => -1,
				'beds'                   => 8,
				'lab_test_types'         => -1,
				'medicines'              => 500,
				'prescription_templates' => 10,
				'sms_monthly'            => 500,
				'audit_retention_days'   => 365,
			],
			'enterprise'   => [
				// Everything unlimited.
				'patients'               => -1,
				'appointments_monthly'   => -1,
				'invoices_monthly'       => -1,
				'staff'                  => -1,
				'doctors'                => -1,
				'beds'                   => -1,
				'lab_test_types'         => -1,
				'medicines'              => -1,
				'prescription_templates' => -1,
				'sms_monthly'            => -1,
				'audit_retention_days'   => -1, // Unlimited.
			],
		];

		return $limits[ $tier ] ?? $limits['free'];
	}

	/**
	 * Get tier display name.
	 *
	 * @return string Tier name for display.
	 */
	public static function get_tier_name() {
		$tier  = self::get_tier();
		$names = [
			'free'         => __( 'FREE', 'iyoraa' ),
			'pro-starter'  => __( 'PRO STARTER', 'iyoraa' ),
			'pro-business' => __( 'PRO BUSINESS', 'iyoraa' ),
			'enterprise'   => __( 'ENTERPRISE', 'iyoraa' ),
		];

		return $names[ $tier ] ?? $names['free'];
	}
}
