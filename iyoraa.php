<?php
/**
 * Plugin Name: Iyoraa - Hospital Management System
 * Plugin URI: https://iyoraa.com
 * Description: Complete hospital management solution for WordPress - Patient management, OPD, IPD, Laboratory, Billing, and more.
 * Version: 1.0.0
 * Requires at least: 5.8
 * Requires PHP: 7.4
 * Author: Rafsun Jani
 * Author URI: https://wphelpzone.com
 * License: GPL v2 or later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: iyoraa
 * Domain Path: /languages
 *
 * @package WPHelpZone\Iyoraa
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// Define plugin constants.
if ( ! defined( 'IYORAA_VERSION' ) ) {
	define( 'IYORAA_VERSION', '1.0.0' );
}
if ( ! defined( 'IYORAA_PATH' ) ) {
	define( 'IYORAA_PATH', plugin_dir_path( __FILE__ ) );
}
if ( ! defined( 'IYORAA_URL' ) ) {
	define( 'IYORAA_URL', plugin_dir_url( __FILE__ ) );
}
if ( ! defined( 'IYORAA_BASENAME' ) ) {
	define( 'IYORAA_BASENAME', plugin_basename( __FILE__ ) );
}

// Require Composer autoloader.
if ( file_exists( IYORAA_PATH . 'vendor/autoload.php' ) ) {
	require_once IYORAA_PATH . 'vendor/autoload.php';
}

// Activation hook.
register_activation_hook(
	__FILE__,
	function () {
		require_once IYORAA_PATH . 'inc/Core/Activator.php';
		WPHelpZone\Iyoraa\Core\Activator::activate();
	}
);

// Deactivation hook.
register_deactivation_hook(
	__FILE__,
	function () {
		require_once IYORAA_PATH . 'inc/Core/Deactivator.php';
		WPHelpZone\Iyoraa\Core\Deactivator::deactivate();
	}
);

// Initialize the plugin.
add_action(
	'plugins_loaded',
	function () {
		if ( class_exists( 'WPHelpZone\\Iyoraa\\Core\\Main' ) ) {
			WPHelpZone\Iyoraa\Core\Main::instance();
		}
	}
);
