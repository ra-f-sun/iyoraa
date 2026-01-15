<?php

/**
 * Uninstall script
 *
 * Fired when the plugin is uninstalled.
 *
 * @package WPHelpZone\Iyoraa
 */

// Exit if accessed directly or not during uninstall.
if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
	exit;
}

global $wpdb;

// Option to keep data on uninstall (default: false - delete everything).
$keep_data = get_option( 'iyoraa_keep_data_on_uninstall', false );

if ( $keep_data ) {
	// Just remove options, keep database tables.
	delete_option( 'iyoraa_version' );
	delete_option( 'iyoraa_db_version' );
	delete_option( 'iyoraa_license_tier' );
	delete_option( 'iyoraa_license_key' );
	delete_option( 'iyoraa_license_override' );
	return;
}

// Drop all plugin tables.
$tables = [
	$wpdb->prefix . 'iyoraa_patients',
	$wpdb->prefix . 'iyoraa_patient_history',
	$wpdb->prefix . 'iyoraa_patient_consents',
	$wpdb->prefix . 'iyoraa_appointments',
	$wpdb->prefix . 'iyoraa_beds',
	$wpdb->prefix . 'iyoraa_ipd_admissions',
	$wpdb->prefix . 'iyoraa_ipd_packages',
	$wpdb->prefix . 'iyoraa_lab_tests',
	$wpdb->prefix . 'iyoraa_lab_catalog',
	$wpdb->prefix . 'iyoraa_ot_bookings',
	$wpdb->prefix . 'iyoraa_prescriptions',
	$wpdb->prefix . 'iyoraa_invoices',
	$wpdb->prefix . 'iyoraa_payments',
	$wpdb->prefix . 'iyoraa_medicines',
	$wpdb->prefix . 'iyoraa_staff',
	$wpdb->prefix . 'iyoraa_attendance',
	$wpdb->prefix . 'iyoraa_audit_log',
	$wpdb->prefix . 'iyoraa_error_log',
	$wpdb->prefix . 'iyoraa_db_version',
];

foreach ( $tables as $table ) {
	$wpdb->query( "DROP TABLE IF EXISTS {$table}" );
}

// Delete all plugin options.
$wpdb->query( "DELETE FROM {$wpdb->options} WHERE option_name LIKE 'iyoraa_%'" );

// Clear any cached data.
wp_cache_flush();
