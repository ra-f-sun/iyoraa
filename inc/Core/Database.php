<?php
/**
 * Database Schema Manager
 *
 * Creates and manages all 19 database tables.
 * All tables are created on Day 1, even if unused in FREE tier.
 *
 * @package WPHelpZone\Iyoraa\Core
 */

namespace WPHelpZone\Iyoraa\Core;

/**
 * Database class.
 *
 * Manages database schema creation and migrations.
 */
class Database extends Singleton {

	/**
	 * Database version.
	 */
	const DB_VERSION = '1.0.0';

	/**
	 * Table name constants (without prefix).
	 */
	const DB_VERSION_TABLE       = 'iyoraa_db_version';
	const PATIENTS_TABLE         = 'iyoraa_patients';
	const PATIENT_HISTORY_TABLE  = 'iyoraa_patient_history';
	const PATIENT_CONSENTS_TABLE = 'iyoraa_patient_consents';
	const APPOINTMENTS_TABLE     = 'iyoraa_appointments';
	const BEDS_TABLE             = 'iyoraa_beds';
	const IPD_ADMISSIONS_TABLE   = 'iyoraa_ipd_admissions';
	const IPD_PACKAGES_TABLE     = 'iyoraa_ipd_packages';
	const LAB_TESTS_TABLE        = 'iyoraa_lab_tests';
	const LAB_CATALOG_TABLE      = 'iyoraa_lab_catalog';
	const OT_BOOKINGS_TABLE      = 'iyoraa_ot_bookings';
	const PRESCRIPTIONS_TABLE    = 'iyoraa_prescriptions';
	const INVOICES_TABLE         = 'iyoraa_invoices';
	const PAYMENTS_TABLE         = 'iyoraa_payments';
	const MEDICINES_TABLE        = 'iyoraa_medicines';
	const STAFF_TABLE            = 'iyoraa_staff';
	const ATTENDANCE_TABLE       = 'iyoraa_attendance';
	const AUDIT_LOG_TABLE        = 'iyoraa_audit_log';
	const ERROR_LOG_TABLE        = 'iyoraa_error_log';

	/**
	 * Get table name with WordPress prefix.
	 *
	 * @param string $table Table name constant.
	 * @return string Full table name with prefix.
	 */
	public function get_table_name( $table ) {
		global $wpdb;
		return $wpdb->prefix . $table;
	}

	/**
	 * Create all database tables.
	 */
	public static function create_tables() {
		global $wpdb;

		$charset_collate = $wpdb->get_charset_collate();
		$prefix          = $wpdb->prefix . 'iyoraa_';

		require_once ABSPATH . 'wp-admin/includes/upgrade.php';

		// Store current db version.
		self::create_version_table( $prefix, $charset_collate );

		// Create all 19 tables.
		self::create_patients_table( $prefix, $charset_collate );
		self::create_patient_history_table( $prefix, $charset_collate );
		self::create_patient_consents_table( $prefix, $charset_collate );
		self::create_appointments_table( $prefix, $charset_collate );
		self::create_beds_table( $prefix, $charset_collate );
		self::create_ipd_admissions_table( $prefix, $charset_collate );
		self::create_ipd_packages_table( $prefix, $charset_collate );
		self::create_lab_tests_table( $prefix, $charset_collate );
		self::create_lab_catalog_table( $prefix, $charset_collate );
		self::create_ot_bookings_table( $prefix, $charset_collate );
		self::create_prescriptions_table( $prefix, $charset_collate );
		self::create_invoices_table( $prefix, $charset_collate );
		self::create_payments_table( $prefix, $charset_collate );
		self::create_medicines_table( $prefix, $charset_collate );
		self::create_staff_table( $prefix, $charset_collate );
		self::create_attendance_table( $prefix, $charset_collate );
		self::create_audit_log_table( $prefix, $charset_collate );
		self::create_error_log_table( $prefix, $charset_collate );

		// Record database version.
		self::record_version();
	}

	/**
	 * Create version tracking table.
	 *
	 * @param string $prefix          Database table prefix.
	 * @param string $charset_collate Database charset collation.
	 */
	private static function create_version_table( $prefix, $charset_collate ) {
		$sql = "CREATE TABLE {$prefix}db_version (
            id INT PRIMARY KEY AUTO_INCREMENT,
            version VARCHAR(10) NOT NULL,
            applied_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            description TEXT,
            INDEX idx_version (version)
        ) $charset_collate;";

		dbDelta( $sql );
	}

	/**
	 * Create patients table [MVP].
	 *
	 * Optimized indexes for common queries:
	 * - idx_patient_id: Quick patient ID lookup
	 * - idx_phone: Phone number search
	 * - idx_status_created: List active patients ordered by date
	 * - idx_email: Email lookup (for PRO features)
	 * - idx_search: FULLTEXT search on name, phone, patient_id
	 * - idx_age_gender: Demographics filtering
	 * - idx_blood_group: Blood group queries (for emergencies)
	 *
	 * @param string $prefix          Database table prefix.
	 * @param string $charset_collate Database charset collation.
	 */
	private static function create_patients_table( $prefix, $charset_collate ) {
		$sql = "CREATE TABLE {$prefix}patients (
            id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            patient_id VARCHAR(20) UNIQUE NOT NULL COMMENT 'HOS-2026-0001',
            full_name VARCHAR(200) NOT NULL,
            age INT NOT NULL,
            gender ENUM('male', 'female', 'other') NOT NULL,
            phone VARCHAR(20) NOT NULL,
            email VARCHAR(100),
            address TEXT,
            blood_group VARCHAR(10),
            emergency_contact_name VARCHAR(100),
            emergency_contact_phone VARCHAR(20),
            medical_history TEXT,
            status ENUM('active', 'inactive') DEFAULT 'active',
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            INDEX idx_patient_id (patient_id),
            INDEX idx_phone (phone),
            INDEX idx_status_created (status, created_at DESC),
            INDEX idx_email (email),
            INDEX idx_age_gender (age, gender),
            INDEX idx_blood_group (blood_group),
            FULLTEXT idx_search (full_name, phone, patient_id)
        ) $charset_collate;";

		dbDelta( $sql );
	}

	/**
	 * Create patient history table [PRO-B].
	 *
	 * @param string $prefix          Database table prefix.
	 * @param string $charset_collate Database charset collation.
	 */
	private static function create_patient_history_table( $prefix, $charset_collate ) {
		$sql = "CREATE TABLE {$prefix}patient_history (
            id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            patient_id BIGINT UNSIGNED NOT NULL,
            record_type ENUM('diagnosis', 'allergy', 'surgery', 'medication', 'note') NOT NULL,
            title VARCHAR(200) NOT NULL,
            description TEXT,
            recorded_date DATE,
            recorded_by BIGINT UNSIGNED COMMENT 'Doctor/Staff ID',
            attachments TEXT COMMENT 'JSON array of file URLs [PRO-B]',
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            INDEX idx_patient_type (patient_id, record_type),
            INDEX idx_recorded_date (recorded_date),
            INDEX idx_recorded_by (recorded_by)
        ) $charset_collate;";

		dbDelta( $sql );
	}

	/**
	 * Create patient consents table [ENT].
	 *
	 * @param string $prefix          Database table prefix.
	 * @param string $charset_collate Database charset collation.
	 */
	private static function create_patient_consents_table( $prefix, $charset_collate ) {
		$sql = "CREATE TABLE {$prefix}patient_consents (
            id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            patient_id BIGINT UNSIGNED NOT NULL,
            consent_type ENUM('data_storage', 'data_processing', 'communication', 'research') NOT NULL,
            consent_given BOOLEAN DEFAULT FALSE,
            consent_date DATETIME,
            consent_withdrawn_date DATETIME,
            ip_address VARCHAR(45),
            consent_document_url VARCHAR(255) COMMENT 'Signed consent form',
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            INDEX idx_patient_consent (patient_id, consent_type),
            UNIQUE KEY unique_patient_consent (patient_id, consent_type)
        ) $charset_collate;";

		dbDelta( $sql );
	}

	/**
	 * Create appointments table [MVP].
	 *
	 * Optimized indexes:
	 * - idx_appointment_id: Quick appointment lookup
	 * - idx_doctor_date_time: Doctor's daily schedule (most common query)
	 * - idx_patient_date: Patient appointment history
	 * - idx_status_date: List by status and date
	 * - idx_payment_status: Unpaid appointments report
	 * - idx_created_at: Recent appointments
	 *
	 * @param string $prefix          Database table prefix.
	 * @param string $charset_collate Database charset collation.
	 */
	private static function create_appointments_table( $prefix, $charset_collate ) {
		$sql = "CREATE TABLE {$prefix}appointments (
            id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            appointment_id VARCHAR(20) UNIQUE NOT NULL COMMENT 'APT-2026-0001',
            patient_id BIGINT UNSIGNED NOT NULL,
            doctor_id BIGINT UNSIGNED NOT NULL,
            appointment_date DATE NOT NULL,
            appointment_time TIME NOT NULL,
            duration INT DEFAULT 15 COMMENT 'Minutes',
            status ENUM('scheduled', 'confirmed', 'completed', 'cancelled', 'no_show') DEFAULT 'scheduled',
            reason VARCHAR(255),
            notes TEXT,
            consultation_fee DECIMAL(10, 2),
            payment_status ENUM('pending', 'paid', 'partial') DEFAULT 'pending',
            is_recurring BOOLEAN DEFAULT FALSE COMMENT '[PRO-B]',
            recurrence_pattern VARCHAR(50) COMMENT 'weekly, monthly, etc [PRO-B]',
            recurrence_parent_id BIGINT UNSIGNED COMMENT 'If this is recurring instance',
            created_by BIGINT UNSIGNED COMMENT 'Who booked',
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            INDEX idx_appointment_id (appointment_id),
            INDEX idx_doctor_date_time (doctor_id, appointment_date, appointment_time),
            INDEX idx_patient_date (patient_id, appointment_date DESC),
            INDEX idx_status_date (status, appointment_date),
            INDEX idx_payment_status (payment_status),
            INDEX idx_created_at (created_at DESC)
        ) $charset_collate;";

		dbDelta( $sql );
	}

	/**
	 * Create beds table [PRO-S].
	 *
	 * @param string $prefix          Database table prefix.
	 * @param string $charset_collate Database charset collation.
	 */
	private static function create_beds_table( $prefix, $charset_collate ) {
		$sql = "CREATE TABLE {$prefix}beds (
            id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            bed_number VARCHAR(20) UNIQUE NOT NULL COMMENT 'C1, C2, C3, W1-W5',
            bed_type ENUM('cabin', 'ward') NOT NULL,
            bed_name VARCHAR(100) COMMENT 'VIP Cabin 1, General Ward Bed 3',
            floor VARCHAR(20),
            daily_rate DECIMAL(10, 2) NOT NULL,
            status ENUM('available', 'occupied', 'maintenance', 'reserved') DEFAULT 'available',
            features TEXT COMMENT 'AC, TV, Private bathroom',
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            INDEX idx_bed_number (bed_number),
            INDEX idx_type_status (bed_type, status)
        ) $charset_collate;";

		dbDelta( $sql );
	}

	/**
	 * Create IPD admissions table [PRO-S].
	 *
	 * @param string $prefix          Database table prefix.
	 * @param string $charset_collate Database charset collation.
	 */
	private static function create_ipd_admissions_table( $prefix, $charset_collate ) {
		$sql = "CREATE TABLE {$prefix}ipd_admissions (
            id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            admission_id VARCHAR(20) UNIQUE NOT NULL COMMENT 'IPD-2026-0001',
            patient_id BIGINT UNSIGNED NOT NULL,
            admission_date DATETIME NOT NULL,
            discharge_date DATETIME,
            bed_id BIGINT UNSIGNED NOT NULL,
            package_id BIGINT UNSIGNED COMMENT 'NULL if pay-as-go [PRO-B]',
            admission_type ENUM('emergency', 'planned') DEFAULT 'planned',
            admitting_doctor_id BIGINT UNSIGNED NOT NULL,
            diagnosis TEXT,
            status ENUM('admitted', 'discharged', 'transferred') DEFAULT 'admitted',
            total_amount DECIMAL(10, 2) DEFAULT 0,
            paid_amount DECIMAL(10, 2) DEFAULT 0,
            due_amount DECIMAL(10, 2) DEFAULT 0,
            discharge_summary TEXT COMMENT 'Detailed discharge notes [PRO-B]',
            admitted_by BIGINT UNSIGNED,
            discharged_by BIGINT UNSIGNED,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            INDEX idx_admission_id (admission_id),
            INDEX idx_patient_status (patient_id, status),
            INDEX idx_bed_id (bed_id),
            INDEX idx_admission_date (admission_date),
            INDEX idx_status (status)
        ) $charset_collate;";

		dbDelta( $sql );
	}

	/**
	 * Create IPD packages table [PRO-B].
	 *
	 * @param string $prefix          Database table prefix.
	 * @param string $charset_collate Database charset collation.
	 */
	private static function create_ipd_packages_table( $prefix, $charset_collate ) {
		$sql = "CREATE TABLE {$prefix}ipd_packages (
            id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            package_name VARCHAR(200) NOT NULL,
            duration_days INT NOT NULL,
            package_price DECIMAL(10, 2) NOT NULL,
            included_services TEXT COMMENT 'JSON array of included services',
            excluded_services TEXT COMMENT 'JSON array of excluded services',
            description TEXT,
            is_active BOOLEAN DEFAULT TRUE,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            INDEX idx_active (is_active)
        ) $charset_collate;";

		dbDelta( $sql );
	}

	/**
	 * Create lab tests table [PRO-S].
	 *
	 * @param string $prefix          Database table prefix.
	 * @param string $charset_collate Database charset collation.
	 */
	private static function create_lab_tests_table( $prefix, $charset_collate ) {
		$sql = "CREATE TABLE {$prefix}lab_tests (
            id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            test_id VARCHAR(20) UNIQUE NOT NULL COMMENT 'LAB-2026-0001',
            patient_id BIGINT UNSIGNED NOT NULL,
            test_category ENUM('blood', 'urine', 'stool', 'imaging', 'other') NOT NULL,
            test_name VARCHAR(200) NOT NULL COMMENT 'CBC, TSH, LFT, USG',
            test_code VARCHAR(50) COMMENT 'Internal code',
            ordered_by BIGINT UNSIGNED COMMENT 'Doctor ID',
            ordered_date DATETIME NOT NULL,
            sample_collected BOOLEAN DEFAULT FALSE,
            collection_date DATETIME,
            collected_by BIGINT UNSIGNED,
            result_entered BOOLEAN DEFAULT FALSE,
            result_date DATETIME,
            result_entered_by BIGINT UNSIGNED,
            result_data TEXT COMMENT 'JSON test results with parameters',
            report_url VARCHAR(255) COMMENT 'PDF report',
            status ENUM('ordered', 'sample_collected', 'in_progress', 'completed', 'cancelled') DEFAULT 'ordered',
            test_fee DECIMAL(10, 2) NOT NULL,
            payment_status ENUM('pending', 'paid') DEFAULT 'pending',
            notes TEXT,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            INDEX idx_test_id (test_id),
            INDEX idx_patient_status (patient_id, status),
            INDEX idx_test_category (test_category),
            INDEX idx_ordered_date (ordered_date),
            INDEX idx_status (status)
        ) $charset_collate;";

		dbDelta( $sql );
	}

	/**
	 * Create lab catalog table [PRO-S].
	 *
	 * @param string $prefix          Database table prefix.
	 * @param string $charset_collate Database charset collation.
	 */
	private static function create_lab_catalog_table( $prefix, $charset_collate ) {
		$sql = "CREATE TABLE {$prefix}lab_catalog (
            id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            test_code VARCHAR(50) UNIQUE NOT NULL,
            test_name VARCHAR(200) NOT NULL,
            test_category ENUM('blood', 'urine', 'stool', 'imaging', 'other') NOT NULL,
            normal_range VARCHAR(200) COMMENT 'Reference ranges',
            parameters TEXT COMMENT 'JSON test parameters [PRO-B]',
            price DECIMAL(10, 2) NOT NULL,
            is_active BOOLEAN DEFAULT TRUE,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            INDEX idx_category_active (test_category, is_active),
            INDEX idx_test_code (test_code),
            FULLTEXT idx_test_name (test_name)
        ) $charset_collate;";

		dbDelta( $sql );
	}

	/**
	 * Create OT bookings table [PRO-B].
	 *
	 * @param string $prefix          Database table prefix.
	 * @param string $charset_collate Database charset collation.
	 */
	private static function create_ot_bookings_table( $prefix, $charset_collate ) {
		$sql = "CREATE TABLE {$prefix}ot_bookings (
            id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            booking_id VARCHAR(20) UNIQUE NOT NULL COMMENT 'OT-2026-0001',
            patient_id BIGINT UNSIGNED NOT NULL,
            surgery_date DATE NOT NULL,
            surgery_time TIME NOT NULL,
            estimated_duration INT COMMENT 'Minutes',
            surgery_type VARCHAR(200) NOT NULL,
            surgeon_id BIGINT UNSIGNED NOT NULL,
            anesthetist_id BIGINT UNSIGNED,
            assistant_ids TEXT COMMENT 'JSON array of doctor IDs',
            pre_operative_notes TEXT,
            surgery_notes TEXT,
            post_operative_notes TEXT,
            complications TEXT,
            status ENUM('scheduled', 'in_progress', 'completed', 'cancelled') DEFAULT 'scheduled',
            surgery_fee DECIMAL(10, 2),
            anesthesia_fee DECIMAL(10, 2),
            ot_charges DECIMAL(10, 2),
            total_charges DECIMAL(10, 2),
            payment_status ENUM('pending', 'paid', 'partial') DEFAULT 'pending',
            booked_by BIGINT UNSIGNED,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            INDEX idx_booking_id (booking_id),
            INDEX idx_surgery_date (surgery_date),
            INDEX idx_surgeon_id (surgeon_id),
            INDEX idx_status (status)
        ) $charset_collate;";

		dbDelta( $sql );
	}

	/**
	 * Create prescriptions table [PRO-B].
	 *
	 * @param string $prefix          Database table prefix.
	 * @param string $charset_collate Database charset collation.
	 */
	private static function create_prescriptions_table( $prefix, $charset_collate ) {
		$sql = "CREATE TABLE {$prefix}prescriptions (
            id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            prescription_id VARCHAR(20) UNIQUE NOT NULL COMMENT 'RX-2026-0001',
            patient_id BIGINT UNSIGNED NOT NULL,
            doctor_id BIGINT UNSIGNED NOT NULL,
            visit_date DATE NOT NULL,
            visit_type ENUM('opd', 'ipd', 'emergency') NOT NULL,
            chief_complaint TEXT,
            diagnosis TEXT,
            medications TEXT COMMENT 'JSON array of medicines with dosage',
            investigations TEXT COMMENT 'JSON tests ordered',
            advice TEXT,
            follow_up_date DATE,
            template_used VARCHAR(100) COMMENT 'Template name if used',
            generated_pdf VARCHAR(255) COMMENT 'PDF file path',
            status ENUM('draft', 'issued', 'cancelled') DEFAULT 'issued',
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            INDEX idx_prescription_id (prescription_id),
            INDEX idx_patient_doctor (patient_id, doctor_id),
            INDEX idx_visit_date (visit_date),
            INDEX idx_doctor (doctor_id)
        ) $charset_collate;";

		dbDelta( $sql );
	}

	/**
	 * Create invoices table [MVP] - CRITICAL.
	 *
	 * Optimized indexes for financial reports:
	 * - idx_invoice_id: Quick invoice lookup
	 * - idx_payment_status_date: Outstanding invoices report (most common)
	 * - idx_patient_date: Patient invoice history
	 * - idx_due_date_status: Overdue invoices alert
	 * - idx_type_date: Revenue by type report
	 * - idx_issued_by: Staff performance tracking
	 *
	 * @param string $prefix          Database table prefix.
	 * @param string $charset_collate Database charset collation.
	 */
	private static function create_invoices_table( $prefix, $charset_collate ) {
		$sql = "CREATE TABLE {$prefix}invoices (
            id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            invoice_id VARCHAR(20) UNIQUE NOT NULL COMMENT 'INV-2026-0001',
            patient_id BIGINT UNSIGNED NOT NULL,
            invoice_date DATE NOT NULL,
            invoice_type ENUM('opd', 'ipd', 'lab', 'ot', 'pharmacy', 'other') NOT NULL,
            reference_id BIGINT UNSIGNED COMMENT 'Related appointment/admission/test ID',
            items TEXT NOT NULL COMMENT 'JSON array of line items with details',
            subtotal DECIMAL(10, 2) NOT NULL,
            discount_percentage DECIMAL(5, 2) DEFAULT 0 COMMENT '[PRO-B]',
            discount_amount DECIMAL(10, 2) DEFAULT 0,
            tax_amount DECIMAL(10, 2) DEFAULT 0,
            total_amount DECIMAL(10, 2) NOT NULL,
            paid_amount DECIMAL(10, 2) DEFAULT 0,
            due_amount DECIMAL(10, 2) NOT NULL,
            payment_status ENUM('unpaid', 'partial', 'paid') DEFAULT 'unpaid',
            due_date DATE,
            notes TEXT,
           issued_by BIGINT UNSIGNED NOT NULL COMMENT 'Staff who created invoice',
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            INDEX idx_invoice_id (invoice_id),
            INDEX idx_payment_status_date (payment_status, invoice_date DESC),
            INDEX idx_patient_date (patient_id, invoice_date DESC),
            INDEX idx_due_date_status (due_date, payment_status),
            INDEX idx_type_date (invoice_type, invoice_date),
            INDEX idx_issued_by (issued_by)
        ) $charset_collate;";

		dbDelta( $sql );
	}

	/**
	 * Create payments table [MVP] - CRITICAL.
	 *
	 * Optimized indexes for payment tracking:
	 * - idx_payment_id: Quick payment lookup
	 * - idx_invoice_id: Invoice payment history
	 * - idx_payment_date: Daily collection report
	 * - idx_method_date: Payment method analysis
	 * - idx_received_by_date: Staff collection tracking
	 * - idx_patient_date: Patient payment history
	 *
	 * @param string $prefix          Database table prefix.
	 * @param string $charset_collate Database charset collation.
	 */
	private static function create_payments_table( $prefix, $charset_collate ) {
		$sql = "CREATE TABLE {$prefix}payments (
            id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            payment_id VARCHAR(20) UNIQUE NOT NULL COMMENT 'PAY-2026-0001',
            invoice_id BIGINT UNSIGNED NOT NULL,
            patient_id BIGINT UNSIGNED NOT NULL,
            payment_date DATETIME NOT NULL,
            amount DECIMAL(10, 2) NOT NULL,
            payment_method ENUM('cash', 'card', 'bank_transfer', 'mobile_banking', 'bkash', 'nagad', 'other') NOT NULL,
            transaction_reference VARCHAR(100) COMMENT 'Bank/gateway reference',
            receipt_number VARCHAR(50),
            notes TEXT,
            received_by BIGINT UNSIGNED NOT NULL COMMENT 'Staff who received payment',
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            INDEX idx_payment_id (payment_id),
            INDEX idx_invoice_id (invoice_id),
            INDEX idx_payment_date (payment_date DESC),
            INDEX idx_method_date (payment_method, payment_date),
            INDEX idx_received_by_date (received_by, payment_date DESC),
            INDEX idx_patient_date (patient_id, payment_date DESC)
        ) $charset_collate;";

		dbDelta( $sql );
	}

	/**
	 * Create medicines table [PRO-B].
	 *
	 * @param string $prefix          Database table prefix.
	 * @param string $charset_collate Database charset collation.
	 */
	private static function create_medicines_table( $prefix, $charset_collate ) {
		$sql = "CREATE TABLE {$prefix}medicines (
            id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            medicine_code VARCHAR(50) UNIQUE,
            medicine_name VARCHAR(200) NOT NULL,
            generic_name VARCHAR(200),
            category VARCHAR(100) COMMENT 'Antibiotic, Painkiller, etc',
            manufacturer VARCHAR(200),
            unit_type VARCHAR(50) COMMENT 'Tablet, Capsule, Syrup',
            strength VARCHAR(50) COMMENT '500mg, 10ml',
            purchase_price DECIMAL(10, 2),
            current_stock INT DEFAULT 0,
            minimum_stock INT DEFAULT 10,
            expiry_date DATE,
            storage_location VARCHAR(100),
            status ENUM('active', 'discontinued', 'out_of_stock') DEFAULT 'active',
            notes TEXT,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            INDEX idx_medicine_code (medicine_code),
            INDEX idx_status (status),
            INDEX idx_expiry_date (expiry_date),
            INDEX idx_stock_alert (current_stock, minimum_stock),
            FULLTEXT idx_name (medicine_name, generic_name)
        ) $charset_collate;";

		dbDelta( $sql );
	}

	/**
	 * Create staff table [ALL].
	 *
	 * @param string $prefix          Database table prefix.
	 * @param string $charset_collate Database charset collation.
	 */
	private static function create_staff_table( $prefix, $charset_collate ) {
		$sql = "CREATE TABLE {$prefix}staff (
            id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            staff_id VARCHAR(20) UNIQUE NOT NULL COMMENT 'EMP-2026-0001',
            wp_user_id BIGINT UNSIGNED COMMENT 'Link to WordPress user',
            staff_type ENUM('doctor', 'nurse', 'receptionist', 'lab_assistant', 'manager', 'guard', 'other') NOT NULL,
            first_name VARCHAR(100) NOT NULL,
            last_name VARCHAR(100) NOT NULL,
            specialization VARCHAR(200) COMMENT 'For doctors',
            qualification VARCHAR(200),
            phone VARCHAR(20) NOT NULL,
            email VARCHAR(100),
            address TEXT,
            date_of_birth DATE,
            joining_date DATE NOT NULL,
            salary DECIMAL(10, 2) COMMENT '[PRO-B]',
            duty_schedule TEXT COMMENT 'JSON working hours/days [PRO-B]',
            biometric_id VARCHAR(50) COMMENT 'For attendance [ENT]',
            status ENUM('active', 'on_leave', 'resigned', 'terminated') DEFAULT 'active',
            photo_url VARCHAR(255),
            documents TEXT COMMENT 'JSON certificate URLs',
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            INDEX idx_staff_id (staff_id),
            INDEX idx_wp_user_id (wp_user_id),
            INDEX idx_staff_type (staff_type),
            INDEX idx_status (status),
            INDEX idx_biometric_id (biometric_id)
        ) $charset_collate;";

		dbDelta( $sql );
	}

	/**
	 * Create attendance table [PRO-B].
	 *
	 * @param string $prefix          Database table prefix.
	 * @param string $charset_collate Database charset collation.
	 */
	private static function create_attendance_table( $prefix, $charset_collate ) {
		$sql = "CREATE TABLE {$prefix}attendance (
            id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            staff_id BIGINT UNSIGNED NOT NULL,
            attendance_date DATE NOT NULL,
            check_in TIME,
            check_out TIME,
            total_hours DECIMAL(5, 2),
            status ENUM('present', 'absent', 'half_day', 'on_leave') NOT NULL,
            biometric_verified BOOLEAN DEFAULT FALSE COMMENT '[ENT]',
            notes TEXT,
            marked_by BIGINT UNSIGNED COMMENT 'Manager who marked',
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            UNIQUE KEY unique_staff_date (staff_id, attendance_date),
            INDEX idx_attendance_date (attendance_date),
            INDEX idx_status (status),
            INDEX idx_staff_date (staff_id, attendance_date)
        ) $charset_collate;";

		dbDelta( $sql );
	}

	/**
	 * Create audit log table [ALL] - CRITICAL.
	 *
	 * Optimized indexes for security and compliance:
	 * - idx_user_action_date: User activity tracking
	 * - idx_entity_date: Entity change history
	 * - idx_action_date: Action-specific reports
	 * - idx_created_at: Chronological audit trail
	 * - idx_ip_address: IP-based security analysis
	 *
	 * @param string $prefix          Database table prefix.
	 * @param string $charset_collate Database charset collation.
	 */
	private static function create_audit_log_table( $prefix, $charset_collate ) {
		$sql = "CREATE TABLE {$prefix}audit_log (
            id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            user_id BIGINT UNSIGNED NOT NULL COMMENT 'Staff ID',
            action VARCHAR(100) NOT NULL COMMENT 'invoice_created, payment_received',
            entity_type VARCHAR(50) NOT NULL COMMENT 'invoice, payment, prescription',
            entity_id BIGINT UNSIGNED,
            old_data TEXT COMMENT 'JSON before state',
            new_data TEXT COMMENT 'JSON after state',
            ip_address VARCHAR(45),
            user_agent TEXT,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            INDEX idx_user_action_date (user_id, action, created_at DESC),
            INDEX idx_entity_date (entity_type, entity_id, created_at DESC),
            INDEX idx_action_date (action, created_at DESC),
            INDEX idx_created_at (created_at DESC),
            INDEX idx_ip_address (ip_address)
        ) $charset_collate;";

		dbDelta( $sql );
	}

	/**
	 * Create error log table [ALL].
	 *
	 * Optimized indexes for debugging and monitoring:
	 * - idx_level_date: Error severity tracking
	 * - idx_created_at: Chronological error log
	 * - idx_user_id: User-specific error patterns
	 *
	 * @param string $prefix          Database table prefix.
	 * @param string $charset_collate Database charset collation.
	 */
	private static function create_error_log_table( $prefix, $charset_collate ) {
		$sql = "CREATE TABLE {$prefix}error_log (
            id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            level ENUM('info', 'warning', 'error', 'critical') NOT NULL,
            message TEXT NOT NULL,
            context TEXT COMMENT 'JSON context data',
            user_id BIGINT UNSIGNED,
            ip_address VARCHAR(45),
            user_agent TEXT,
            url VARCHAR(255),
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            INDEX idx_level_date (level, created_at DESC),
            INDEX idx_created_at (created_at DESC),
            INDEX idx_user_id (user_id)
        ) $charset_collate;";

		dbDelta( $sql );
	}

	/**
	 * Record database version.
	 */
	private static function record_version() {
		global $wpdb;

		$table = $wpdb->prefix . 'iyoraa_db_version';

		// Prepare and execute insert query following WordPress coding standards.
		// phpcs:disable WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.PreparedSQL.NotPrepared
		$wpdb->query(
			$wpdb->prepare(
				sprintf(
					'INSERT INTO %s (version, description) VALUES (%%s, %%s)',
					$table // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared -- Table names cannot be parameterized.
				),
				self::DB_VERSION,
				'Initial database schema - All 19 tables created'
			)
		);
		// phpcs:enable WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.PreparedSQL.NotPrepared

		update_option( 'iyoraa_db_version', self::DB_VERSION );
	}

	/**
	 * Get resource limit for a specific resource type.
	 *
	 * @param string $resource_type Resource type (patients, appointments, etc).
	 * @return int Limit value (-1 = unlimited).
	 */
	public static function get_resource_limit( $resource_type ) {
		return LicenseManager::get_resource_limit( $resource_type );
	}
}
