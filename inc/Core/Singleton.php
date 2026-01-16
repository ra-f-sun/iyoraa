<?php
/**
 * Singleton Base Class
 *
 * @package WPHelpZone\Iyoraa\Core
 */

namespace WPHelpZone\Iyoraa\Core;

/**
 * Singleton abstract class.
 *
 * Provides singleton pattern implementation for manager classes.
 */
abstract class Singleton {


	/**
	 * Singleton instances.
	 *
	 * @var array
	 */
	protected static $instances = array();

	/**
	 * Constructor.
	 *
	 * Protected to prevent direct instantiation.
	 */
	protected function __construct() {
		// Override in child classes if needed.
	}

	/**
	 * Get singleton instance.
	 *
	 * @return static Singleton instance.
	 */
	final public static function instance() {
		$class = static::class;

		if ( ! isset( self::$instances[ $class ] ) ) {
			self::$instances[ $class ] = new static();
		}

		return self::$instances[ $class ];
	}

	/**
	 * Prevent cloning.
	 */
	final private function __clone() {
		// Cloning is not allowed.
	}

	/**
	 * Prevent unserialization.
	 *
	 * @throws \Exception When trying to unserialize.
	 */
	final public function __wakeup() {
		throw new \Exception( 'Cannot unserialize singleton' );
	}
}
