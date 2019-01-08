<?php
/**
 * Admin side of Sponsors
 */

namespace Simple_Sponsorships\Admin;

use Simple_Sponsorships\Sponsor;

if ( ! defined( 'ABSPATH' ) ) {
	return;
}

/**
 * Class Sponsors
 *
 * @package Simple_Sponsorships\Admin
 */
class Sponsors {

	/**
	 * Sponsors constructor.
	 */
	public function __construct() {
		add_action( 'add_meta_boxes', array( $this, 'register_sponsor_metaboxes' ) );
		add_action( 'add_meta_boxes', array( $this, 'register_content_metaboxes' ) );
		add_action( 'save_post', array( $this, 'save_sponsor' ), 20, 2 );
	}

	/**
	 * Saving the Sponsor.
	 *
	 * @param $post_id
	 * @param $post
	 */
	public function save_sponsor( $post_id, $post ) {

		if ( 'sponsors' !== get_post_type( $post ) ) {
			return;
		}

		if ( wp_is_post_autosave( $post ) ) {
			return;
		}

		if ( wp_is_post_revision( $post ) ) {
			return;
		}

		if ( wp_doing_ajax() ) {
			return;
		}

		if ( ! isset( $_POST['ss_sponsor_nonce'] ) ) {
			return;
		}

		if ( ! wp_verify_nonce( $_POST['ss_sponsor_nonce'], 'ss_sponsor_nonce' ) ) {
			return;
		}

		$sponsor = new Sponsor( $post_id );

		$fields = $this->get_all_metabox_fields();

		foreach ( $fields as $field_name => $field ) {
			if ( 'checkbox' === $field['type'] && ! isset( $_POST[ $field_name ] ) ) {
				$sponsor->delete_data( $field_name );
			} elseif ( isset( $_POST[ $field_name ] ) ) {
				$sponsor->update_data( $field_name, sanitize_text_field( $_POST[ $field_name ] ) );
			}
		}


		do_action( 'ss_sponsor_post_save', $sponsor, $post );
	}

	/**
	 * Register the metabox for other content.
	 */
	public function register_content_metaboxes() {
		$post_types = ss_get_option( 'content_types', array( 'post' => 'Posts', 'page' => 'Page' ) );

		foreach ( $post_types as $post_type => $post_label ) {
			add_meta_box( 'content-sponsor', __( 'Add Sponsors', 'simple-sponsorships' ), array( $this, 'metabox_sponsors' ), $post_type, 'side' );
		}
	}

	/**
	 * Register metaboxes for Sponsors.
	 */
	public function register_sponsor_metaboxes() {

		$metaboxes = $this->get_metaboxes();

		if ( $metaboxes ) {
			foreach ( $metaboxes as $metabox_id => $metabox ) {
				add_meta_box(
					$metabox_id,
					$metabox['title'],
					$metabox['callback'],
					'sponsors',
					$metabox['context'],
					$metabox['priority'] );
			}
		}
	}

	/**
	 * Get all metaboxes to register for sponsors.
	 */
	public function get_metaboxes() {
		$metaboxes = apply_filters( 'ss_sponsors_metaboxes', array(
			'sponsor-info' => array(
				'title' => __( 'Information', 'simple-sponsorships' ),
				'callback' => array( $this, 'metabox_info' ),
			)
		));

		if ( ! $metaboxes ) { return array(); }

		return array_map( array( $this, 'parse_metabox' ), $metaboxes );
	}

	/**
	 * Parse the metabox array with defaults.
	 *
	 * @param array $metabox
	 *
	 * @return array
	 */
	public function parse_metabox( $metabox ) {
		return wp_parse_args( $metabox, array(
			'title'    => '',
			'callback' => '',
			'context'  => 'advanced',
			'priority' => 'default'
			));
	}

	/**
	 * Metabox info.
	 *
	 * @param \WP_Post $post Post object.
	 */
	public function metabox_info( $post ) {
		$fields = $this->get_metabox_info_fields();
		include_once 'views/sponsors/metabox-info.php';
	}

	/**
	 * Metabox info.
	 *
	 * @param \WP_Post $post Post object.
	 */
	public function metabox_sponsors( $post ) {
		include_once 'views/sponsors/metabox-content.php';
	}

	/**
	 * Get all metabox fields.
	 *
	 * @return array
	 */
	public function get_all_metabox_fields() {
		$fields = $this->get_metabox_info_fields();
		return $fields;
	}

	/**
	 * @return array
	 */
	public function get_metabox_info_fields() {
		$fields = apply_filters( 'metabox_info_fields', array(
			'_email' => array(
				'title' => __( 'Email', 'simple-sponsorships' ),
				'type'  => 'email',
				'name'  => '_email',
				'id'    => 'ss_email'
			),
			'_website' => array(
				'title' => __( 'Website', 'simple-sponsorships' ),
				'type'  => 'url',
				'name'  => '_website',
				'id'    => 'ss_web'
			),
			'_company' => array(
				'title' => __( 'Company', 'simple-sponsorships' ),
				'type'  => 'text',
				'name'  => '_company',
				'id'    => 'ss_company'
			),
		));

		return $fields;
	}
}

new Sponsors();