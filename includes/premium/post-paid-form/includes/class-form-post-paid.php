<?php
/**
 * A class to handle the Post Paid processes for Sponsors.
 */

namespace Simple_Sponsorships\Post_Paid_Form;
use Simple_Sponsorships\Form;
use Simple_Sponsorships\Sponsor;

/**
 * Class Form_Post_Paid
 *
 * @package Simple_Sponsorships\Post_Paid_Form
 */
class Form_Post_Paid extends Form {

	/**
	 * Slug used for filtering and such.
	 *
	 * @var string
	 */
	protected $slug = 'form_post_paid';

	/**
	 * @var null|Sponsor
	 */
	protected $sponsor = null;

	/**
	 * @param $sponsor
	 */
	public function set_sponsor( $sponsor ) {
		$this->sponsor = $sponsor;
	}
	/**
	 * Process the Form.
	 */
	public function process() {

		if ( ! isset( $_POST['ss_post_paid_form_nonce'] )
			|| ! wp_verify_nonce( $_POST['ss_post_paid_form_nonce'], 'ss_post_paid_form' ) ) {
			ss_add_notice( __( 'Something went wrong. Try again later.', 'simple-sponsorships' ), 'error' );
			return;
		}

		// Validate Form Field Data.
		$data = $this->process_data();

		$sponsorship_id = isset( $_POST['ss_sponsorship_id'] ) ? absint( $_POST['ss_sponsorship_id'] ) : 0;

		if ( ! $sponsorship_id ) {
			ss_add_notice( __( 'No Sponsorship.', 'simple-sponsorships' ), 'error' );
		}

		$sponsor_id = isset( $_POST['ss_sponsor_id'] ) ? absint( $_POST['ss_sponsor_id'] ) : 0;

		if ( ! $sponsor_id ) {
			ss_add_notice( __( 'No Sponsor.', 'simple-sponsorships' ), 'error' );
		}

		if ( $data && empty( ss_get_notices( 'error' ) ) ) {

			$sponsor = ss_get_sponsor( $sponsor_id );

			foreach ( $data as $key => $value ) {
				$sponsor->update_data( $key, $value );
			}

			if ( isset( $_FILES['_thumbnail_id_file'] ) && $_FILES['_thumbnail_id_file']['name'] ) {
				require_once( ABSPATH . 'wp-admin/includes/image.php' );
				require_once( ABSPATH . 'wp-admin/includes/file.php' );
				require_once( ABSPATH . 'wp-admin/includes/media.php' );

				$attachment_id = media_handle_upload( '_thumbnail_id_file', $sponsor_id );

				if ( is_wp_error( $attachment_id ) ) {
					ss_add_notice( $attachment_id->get_error_message(), 'error' );
				} else {
					$sponsor->update_data( '_thumbnail_id', $attachment_id );
				}
			}
		}
	}

	/**
	 * Return the fields for Form Sponsors.
	 */
	public function get_fields() {

		$fields = array(
			'post_title' => array(
				'title'    => __( 'Name', 'simple-sponsorships' ),
				'type'     => 'text',
				'required' => true,
			),
			'_email' => array(
				'title'    => __( 'Email', 'simple-sponsorships' ),
				'type'     => 'text',
				'required' => true,
			),
			'_website' => array(
				'title'    => __( 'Website', 'simple-sponsorships' ),
				'type'     => 'text',
				'required' => true,
			),
			'post_content' => array(
				'title'    => __( 'Post Content', 'simple-sponsorships' ),
				'type'     => 'textarea',
				'required' => false,
			),
			'_thumbnail_id' => array(
				'title'    => __( 'Logo', 'simple-sponsorships' ),
				'type'     => 'sponsor_logo',
				'required' => false,
			),
		);

		if ( null !== $this->sponsor ) {
			foreach ( $fields as $field_id => $field ) {
				$fields[ $field_id ]['value'] = $this->sponsor->get_data( $field_id );
			}
		}

		return apply_filters( 'ss_form_post_paid_fields', $fields, $this );
	}
}