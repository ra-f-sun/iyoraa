<?php
/**
 * Database Exception
 *
 * Thrown when database operations fail.
 *
 * @package WPHelpZone\Iyoraa\Exceptions
 */

namespace WPHelpZone\Iyoraa\Exceptions;

/**
 * Database Exception class.
 */
class DatabaseException extends IyoraaException {

	/**
	 * Constructor.
	 *
	 * @param string $operation Database operation (insert, update, delete, etc.).
	 * @param string $table     Table name.
	 * @param string $details   Additional details.
	 */
	public function __construct( string $operation, string $table = '', string $details = '' ) {
		$message = sprintf( 'Database %s operation failed', $operation );
		
		if ( $table ) {
			$message .= " on table {$table}";
		}
		
		if ( $details ) {
			$message .= ": {$details}";
		}
		
		parent::__construct(
			$message,
			500,
			[
				'operation' => $operation,
				'table'     => $table,
				'details'   => $details,
			]
		);
	}

	/**
	 * Get error code for WP_Error.
	 *
	 * @return string Error code.
	 */
	protected function get_error_code(): string {
		return 'database_error';
	}
}
