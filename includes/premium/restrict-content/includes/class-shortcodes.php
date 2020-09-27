<?php


namespace Simple_Sponsorships\Restrict_Content;


use Simple_Sponsorships\Templates;

class Shortcodes {

	/**
	 * Registering shortcodes
	 */
	public function register() {
		add_shortcode( 'ss_restrict_content', array( $this, 'restrict_content' ) );
	}

	/**
	 * Restrict Content
	 *
	 * @param        $atts
	 * @param string $content
	 */
	public function restrict_content( $atts, $content = '' ) {
		$atts = shortcode_atts(
			array(
				'type' => 'package',
				'id'   => 0,
			),
			$atts,
			'ss_restrict_content'
		);

		if ( Plugin::has_access_to( absint( $atts['id'] ), $atts['type'] ) ) {
			return $content;
		}

		$sponsor_page = ss_get_option( 'sponsor_page', 0 );
		if ( $sponsor_page ) {
			$sponsor_page = get_permalink( $sponsor_page );
			$title        = '';
			if ( 'package' === $atts['type'] ) {
				$package      = ss_get_package( $atts['id'] );
				$title        = $package->get_title( true );
				$sponsor_page = add_query_arg( 'package', $atts['id'], $sponsor_page );
			} else {
				$title        = get_the_title( $atts['id'] );
				$sponsor_page = add_query_arg( 'ss_content_id', $atts['id'], $sponsor_page );
			}
			$atts['link']  = $sponsor_page;
			$atts['title'] = $title;
		}
		ob_start();
		Templates::get_template_part( 'shortcode/restrict-content', null, $atts );
		return ob_get_clean();
	}
}
