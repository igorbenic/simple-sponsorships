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
		add_action( 'save_post', array( $this, 'save_sponsor_to_content' ), 20, 2 );

		add_filter( 'manage_sponsors_posts_columns', array( $this, 'columns' ), 90 );
		add_action( 'manage_sponsors_posts_custom_column' , array( $this, 'custom_column' ), 10, 2 );

		add_filter( 'display_post_states', array( $this, 'show_inactive_status' ), 20, 2 );
	}

	/**
	 * Managing Sponsor Columns.
	 *
	 * @param array $columns Array of columns.
	 *
	 * @return mixed
	 */
	public function columns( $columns ) {

		unset( $columns['date'] );
		unset( $columns['title'] );

		if ( isset( $columns['wp_sponsors_logo'] ) ) {
			unset( $columns['wp_sponsors_logo'] );
		}
		$new_columns = array();
		$new_columns['cb']   = $columns['cb'];
		$new_columns['ss-logo'] = __( 'Logo', 'simple-sponsorships' );
		$new_columns['title'] = __( 'Sponsor', 'simple-sponsorships' );
		$new_columns['qty'] = __( 'Avl. Quantity', 'simple-sponsorships' );

		foreach ( $columns as $column_slug => $column_title ) {
			if ( isset( $new_columns[ $column_slug ] ) ) {
				continue;
			}

			$new_columns[ $column_slug ] = $column_title;
		}

		return $new_columns;
	}

	/**
	 * Used for displaying custom data.
	 *
	 * @param string $column Column.
	 * @param integer $post_id Sponsor ID.
	 */
	public function custom_column( $column, $post_id ) {
		switch ( $column ) {
			case 'qty':
				$sponsor = new Sponsor( $post_id, false );
				$qty     = $sponsor->get_available_quantity();
				echo '<button class="button button-secondary button-small ss-button-action" data-success="updateSponsorQuantityColumnOnAjax" data-sponsor="' . $post_id . '" data-action="ss_add_quantity_sponsor" type="button">+</button>';
				echo '<span class="ss-badge ss-qty-' . $qty . '">' . $qty . '</span>';
				echo '<button class="button button-secondary button-small ss-button-action" data-success="updateSponsorQuantityColumnOnAjax" data-sponsor="' . $post_id . '"  data-action="ss_remove_quantity_sponsor" type="button">-</button>';
				break;
			case 'ss-logo':
				echo get_the_post_thumbnail( $post_id, array( 0, 50 ) );
				break;
		}
		do_action( 'ss_sponsors_column_' . $column, $post_id );
	}

	/**
	 * Show if a sponsor is inactive.
	 *
	 * @param array    $states Array of all states.
	 * @param \WP_Post $post Post object.
	 *
	 * @return array
	 */
	public function show_inactive_status( $states, $post ) {

		if ( 'sponsors' === get_post_type( $post ) && 'ss-inactive' === $post->post_status ) {
			$states[] = __( 'Inactive', 'simple-sponsorships' );
		}

		return $states;
	}

	/**
	 * Saving the Sponsor.
	 *
	 * @param $post_id
	 * @param $post
	 */
	public function save_sponsor_to_content( $post_id, $post ) {

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

		$post_types = ss_get_content_types();

		if ( ! in_array( get_post_type( $post ), array_keys( $post_types ), true ) ) {
			return;
		}

		$placeholder = isset( $_POST['ss_hide_placeholder'] ) ? true : false;

		if ( $placeholder ) {
			update_post_meta( $post_id, '_ss_hide_placeholder', '1' );
		} else{
			delete_post_meta( $post_id, '_ss_hide_placeholder' );
		}

		$ss_content_availability = isset( $_POST['ss_content_availability'] ) ? absint( $_POST['ss_content_availability'] ) : 0;
		update_post_meta( $post_id, '_ss_availability', $ss_content_availability );

		$sponsors = isset( $_POST['ss_sponsors'] ) ? sanitize_text_field( $_POST['ss_sponsors'] ) : false;

		if ( false === $sponsors ) {
			return;
		}

		$sponsors          = $sponsors ? explode(',', $sponsors ) : array();
		$previous_sponsors = ss_get_sponsors_for_content( $post_id );
		if ( ! $previous_sponsors ) {
			$previous_sponsors = array();
		}
		ss_update_sponsors_for_content( $post_id, $sponsors );

		$new_sponsors = array_diff( $sponsors, $previous_sponsors  );
		foreach ( $new_sponsors as $sponsor_id ) {
			$sponsor = new Sponsor( $sponsor_id, false );
			$sponsor->remove_available_quantity( 1 );
		}

		// We have some previous sponsors.
		if ( $previous_sponsors ) {
			if ( ! is_array( $previous_sponsors ) ) {
				$previous_sponsors = array( $previous_sponsors );
			}
			$removed_sponsors = array_diff( $previous_sponsors, $sponsors );
			if ( $removed_sponsors ) {
				foreach ( $removed_sponsors as $sponsor_id ) {
					$sponsor = new Sponsor( $sponsor_id, false );
					$sponsor->add_available_quantity( 1 );
				}
			}
		}

		do_action( 'ss_sponsor_post_saved_for_content', $sponsors, $post );
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
	 * Register the metabox for other content so we can assign sponsors to them.
	 */
	public function register_content_metaboxes() {
		$post_types = ss_get_content_types();

		foreach ( $post_types as $post_type => $post_label ) {
			add_meta_box( 'content-sponsor', __( 'Sponsors', 'simple-sponsorships' ), array( $this, 'metabox_sponsors' ), $post_type, 'side' );
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
			),
			'sponsoring' => array(
				'title' => __( 'Sponsoring', 'simple-sponsorships' ),
				'callback' => array( $this, 'metabox_sponsoring' ),
			),
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
	public function metabox_sponsoring( $post ) {
		$fields = $this->get_metabox_sponsoring_fields();
		include_once 'views/sponsors/metabox-sponsoring.php';
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
		$fields = array_merge( $fields, $this->get_metabox_sponsoring_fields() );
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

	/**
	 * @return array
	 */
	public function get_metabox_sponsoring_fields() {
		$fields = apply_filters( 'metabox_sponsoring_fields', array(
			'_available_qty' => array(
				'id'    => '_available_qty',
				'name'  => '_available_qty',
				'title' => __( 'Available Quantity', 'simple-sponsorships' ),
				'desc'  => __( 'This is the quantity that is still unused and how many times this Sponsor can still be added to content', 'simple-sponsorships' ),
				'type'  => 'number',
			),
		));

		return $fields;
	}
}

new Sponsors();