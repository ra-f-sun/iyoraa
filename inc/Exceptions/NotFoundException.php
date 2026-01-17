<?php
/**
 * Not Found Exception
 *
 * Thrown when a requested resource is not found.
 *
 * @package WPHelpZone\Iyoraa\Exceptions
 */

namespace WPHelpZone\Iyoraa\Exceptions;

/**
 * Not Found Exception class.
 */
class NotFoundException extends IyoraaException {

	/**
	 * Constructor.
	 *
	 * @param string     $resource Resource type (patient, appointment, etc.).
	 * @param int|string $id       Resource ID.
	 */
	public function __construct( string $resource, $id ) {
		parent::__construct(
			sprintf( '%s with ID %s not found', ucfirst( $resource ), $id ),
			404,
			[
				'resource' => $resource,
				'id'       => $id,
			]
		);
	}

	/**
	 * Get error code for WP_Error.
	 *
	 * @return string Error code.
	 */
	protected function get_error_code(): string {
		return 'resource_not_found';
	}
}
