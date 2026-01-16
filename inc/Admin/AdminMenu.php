<?php
/**
 * Admin Menu Manager
 *
 * Handles registration of WordPress admin menus.
 *
 * @package WPHelpZone\Iyoraa\Admin
 */

namespace WPHelpZone\Iyoraa\Admin;

/**
 * Admin Menu class.
 *
 * Handles WordPress admin menu registration.
 */
class AdminMenu {


	/**
	 * Register admin menus.
	 */
	public static function register() {
		self::register_main_menu();
		self::register_license_submenu();
	}

	/**
	 * Register main menu page.
	 */
	private static function register_main_menu() {
		add_menu_page(
			__( 'Iyoraa HMS', 'iyoraa' ),
			__( 'Iyoraa', 'iyoraa' ),
			'edit_posts',
			'iyoraa',
			[ AdminPages::class, 'render_main_page' ],
			'dashicons-heart',
			25
		);
	}

	/**
	 * Register license settings submenu.
	 */
	private static function register_license_submenu() {
		add_submenu_page(
			'iyoraa',
			__( 'License Settings', 'iyoraa' ),
			__( 'License', 'iyoraa' ),
			'manage_options',
			'iyoraa-license',
			[ AdminPages::class, 'render_license_page' ]
		);
	}
}
