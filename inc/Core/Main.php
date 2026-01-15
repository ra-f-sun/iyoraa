<?php

/**
 * Main Plugin Bootstrap
 *
 * Single Responsibility: Initialize plugin and register WordPress hooks.
 *
 * @package WPHelpZone\Iyoraa\Core
 */

namespace WPHelpZone\Iyoraa\Core;

use WPHelpZone\Iyoraa\Admin\AdminMenu;
use WPHelpZone\Iyoraa\Admin\AdminAssets;
use WPHelpZone\Iyoraa\API\APIRegistry;

/**
 * Main class - Plugin bootstrap.
 *
 * Initializes the plugin and registers WordPress hooks.
 */
class Main {


	/**
	 * Plugin instance.
	 *
	 * @var Main
	 */
	private static $instance = null;

	/**
	 * Get plugin instance (Singleton).
	 *
	 * @return Main
	 */
	public static function instance() {
		if ( is_null( self::$instance ) ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	/**
	 * Constructor - Initialize hooks only.
	 */
	private function __construct() {
		$this->register_hooks();
	}

	/**
	 * Register all WordPress hooks.
	 */
	private function register_hooks() {
		add_action( 'init', [ $this, 'load_textdomain' ] );
		add_action( 'admin_menu', [ AdminMenu::class, 'register' ] );
		add_action( 'admin_enqueue_scripts', [ AdminAssets::class, 'enqueue' ] );
		add_action( 'rest_api_init', [ APIRegistry::class, 'register_routes' ] );
	}

	/**
	 * Load plugin text domain for translations.
	 */
	public function load_textdomain() {
		load_plugin_textdomain(
			'iyoraa',
			false,
			dirname( IYORAA_BASENAME ) . '/languages'
		);
	}
}
