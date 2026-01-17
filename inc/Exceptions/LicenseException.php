<?php
/**
 * License Exception
 *
 * Thrown when license validation fails or features are locked.
 *
 * @package WPHelpZone\Iyoraa\Exceptions
 */

namespace WPHelpZone\Iyoraa\Exceptions;

/**
 * License Exception class.
 */
class LicenseException extends IyoraaException {

	/**
	 * Constructor.
	 *
	 * @param string $feature      Feature name.
	 * @param string $required_tier Required tier.
	 */
	public function __construct( string $feature, string $required_tier = 'PRO' ) {
		parent::__construct(
			sprintf( '%s requires %s license', $feature, $required_tier ),
			403,
			[
				'feature'       => $feature,
				'required_tier' => $required_tier,
				'upgrade_url'   => 'https://iyoraa.com/pricing',
			]
		);
	}

	/**
	 * Get error code for WP_Error.
	 *
	 * @return string Error code.
	 */
	protected function get_error_code(): string {
		return 'feature_locked';
	}
}
