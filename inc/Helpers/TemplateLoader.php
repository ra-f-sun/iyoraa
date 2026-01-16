<?php
/**
 * Template Loader Helper
 *
 * Handles loading of template files with variable extraction.
 *
 * @package WPHelpZone\Iyoraa\Helpers
 */

namespace WPHelpZone\Iyoraa\Helpers;

/**
 * Template Loader class.
 *
 * Loads template files with variable extraction.
 */
class TemplateLoader {


	/**
	 * Load a template file.
	 *
	 * @param string $template_name Template file path relative to templates directory.
	 * @param array  $args Variables to extract for use in template.
	 */
	public static function load( $template_name, $args = [] ) {
		$template_path = IYORAA_PATH . 'templates/' . $template_name;

		if ( ! file_exists( $template_path ) ) {
			wp_die(
				sprintf(
					/* translators: %s: template file name */
					esc_html__( 'Template file not found: %s', 'iyoraa' ),
					esc_html( $template_name )
				)
			);
		}

		// Extract variables for template.
		if ( ! empty( $args ) ) {
			extract( $args, EXTR_SKIP ); // phpcs:ignore WordPress.PHP.DontExtract.extract_extract
		}

		include $template_path;
	}
}
