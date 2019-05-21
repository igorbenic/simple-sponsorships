<?php
/**
 * A class to handle templates.
 * Most of the methods were copied from Easy Digital Downloads.
 *
 * @package Simple_Sponsorships
 */

namespace Simple_Sponsorships;

if ( ! defined( 'ABSPATH' ) ) {
	return;
}

/**
 * Class Templates
 *
 * @package Simple_Sponsorships
 */
class Templates {

	/**
	 * The template directory path.
	 *
	 * @return string
	 */
	public static function templates_dir() {
		return SS_PLUGIN_PATH . '/templates';
	}

	/**
	 * The template URL path.
	 *
	 * @return string
	 */
	public static function templates_url() {
		return SS_PLUGIN_URL . '/templates';
	}

	/**
	 * Returns the template directory name.
	 *
	 * Themes can filter this by using the edd_templates_dir filter.
	 *
	 * @return string
	 */
	public static function theme_template_dir_name() {
		return trailingslashit( apply_filters( 'ss_templates_dir', 'ss_templates' ) );
	}

	/**
	 * Returns a list of paths to check for template locations
	 *
	 * @return mixed|void
	 */
	public static function theme_template_paths() {

		$template_dir = self::theme_template_dir_name();

		$file_paths = array(
			1 => trailingslashit( get_stylesheet_directory() ) . $template_dir,
			10 => trailingslashit( get_template_directory() ) . $template_dir,
			100 => self::templates_dir()
		);

		$file_paths = apply_filters( 'ss_template_paths', $file_paths );

		// sort the file paths based on priority
		ksort( $file_paths, SORT_NUMERIC );

		return array_map( 'trailingslashit', $file_paths );
	}

	/**
	 * Locate a template within the Theme or this plugin.
	 *
	 * Taken from Easy Digital Downloads.
	 *
	 * @param string|array $template_names Template file(s) to search for, in order.
	 * @param bool $load If true the template file will be loaded if it is found.
	 * @param bool $require_once Whether to require_once or require. Default true.
	 *   Has no effect if $load is false.
	 *
	 * @return string The template filename if one is located.
	 */
	public static function locate_template( $template_names, $load = false, $require_once = true ) {
		// No file found yet
		$located = false;

		// Try to find a template file
		foreach ( (array) $template_names as $template_name ) {

			// Continue if template is empty
			if ( empty( $template_name ) )
				continue;

			// Trim off any slashes from the template name
			$template_name = ltrim( $template_name, '/' );

			// try locating this template file by looping through the template paths
			foreach( self::theme_template_paths() as $template_path ) {

				if( file_exists( $template_path . $template_name ) ) {
					$located = $template_path . $template_name;
					break;
				}
			}

			if( $located ) {
				break;
			}
		}

		if ( ( true == $load ) && ! empty( $located ) )
			load_template( $located, $require_once );

		return $located;
	}

	/**
	 * Retrieves a template part
	 *
	 *
	 * Taken from Easy Digital Downloads.
	 *
	 * @param string $slug
	 * @param string $name Optional. Default null
	 * @param bool   $load
	 *
	 * @return string
	 *
	 * @uses edd_locate_template()
	 * @uses load_template()
	 * @uses get_template_part()
	 */
	public static function get_template_part( $slug, $name = null, $args = array(), $load = false ) {
		// Execute code for this part
		do_action( 'get_template_part_' . $slug, $slug, $name, $args );

		$load_template = apply_filters( 'ss_allow_template_part_' . $slug . '_' . $name, true );
		if ( false === $load_template ) {
			return '';
		}

		// Setup possible parts
		$templates = array();
		if ( isset( $name ) )
			$templates[] = $slug . '-' . $name . '.php';
		$templates[] = $slug . '.php';

		// Allow template parts to be filtered
		$templates = apply_filters( 'ss_get_template_part', $templates, $slug, $name );

		$template = self::locate_template( $templates, $load, false );
		if ( $template ) {
			include $template;
		}
		// Return the part that is found
		return $template;
	}

	/**
	 * Get the file content.
	 *
	 * @param $path
	 * @param $id Can be used to make it filterable.
	 */
	public static function get_file_contents( $path, $id = '' ) {

		$path    = apply_filters( 'ss_get_file_contents_path', $path, $id );
		$content = '';

		if ( $path ) {
			$content = file_get_contents( $path );
		}

		return apply_filters( 'ss_get_file_contents', $content, $path, $id );
	}
}