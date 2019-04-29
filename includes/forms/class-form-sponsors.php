<?php
/**
 * A class to handle the Submissions for Sponsors.
 */

namespace Simple_Sponsorships;
use Simple_Sponsorships\DB\DB_Sponsorships;

/**
 * Class Form_Sponsors
 *
 * @package Simple_Sponsorships
 */
class Form_Sponsors extends Form {

	/**
	 * Slug used for filtering and such.
	 *
	 * @var string
	 */
	protected $slug = 'form_sponsors';

	/**
	 * Process the Form.
	 */
	public function process() {
		$data = $this->process_data();

		if ( $data ) {
			$this->create_sponsorship( $data );
		}
	}

	/**
	 * Create a Sponsorship.
	 *
	 * @param array   $posted_data
	 * @param boolean $redirect
	 */
	public function create_sponsorship( $posted_data, $redirect = true ) {

		$args = array();

		if ( isset( $posted_data['package'] ) ) {
			$args['package'] = absint( $posted_data['package'] );
			$package         = ss_get_package( $args['package'] );
			$args['amount']  = floatval( $package->get_data('price' ) );
		}

		$sponsorship_id = ss_create_sponsorship( $args );
		$db             = new DB_Sponsorships();

		if ( $sponsorship_id ) {

			$meta_data = $this->unset_sponsorship_columns( $posted_data );

			foreach ( $meta_data as $key => $value ) {
				$db->add_meta( $sponsorship_id, '_' . $key, $value );
			}

			do_action( 'ss_sponsor_form_sponsorship_created', $sponsorship_id );

			$sponsorship_page = ss_get_option( 'sponsorship_page', 0 );

			if ( $sponsorship_page && $redirect ) {
				$sponsorship = new Sponsorship( $sponsorship_id );
				$redirect = get_permalink( $sponsorship_page );
				$redirect = add_query_arg( 'sponsorship-key', $sponsorship->get_data( 'ss_key' ), $redirect );
				wp_safe_redirect( $redirect );
				exit;
			}

			return $sponsorship_id;

		} else {
			ss_add_notice( __( 'Sponsorship could not be created. Try contacting the site owner through email.', 'simple-sponsorships' ), 'error' );
		}

		return false;
	}

	/**
	 * Remove data saved in table columns and some others as we don't need them in meta.
	 *
	 * @param $data
	 *
	 * @return array
	 */
	public function unset_sponsorship_columns( $data ) {

		$non_meta = apply_filters( 'ss_sponsor_form_non_meta', array(
			'status',
			'amount',
			'subtotal',
			'currency',
			'gateway',
			'transaction_id',
			'package',
			'sponsor',
			'date',
			'key',
			'ss_key',
			'sponsor_terms',
		));

		foreach ( $non_meta as $column ) {
			if ( isset( $data[ $column ] ) ) {
				unset( $data[ $column ] );
			}
		}

		return $data;
	}

	/**
	 * Return the fields for Form Sponsors.
	 */
	public function get_fields() {
		$packages = ss_get_available_packages();
		$package_options = array();
		if ( $packages ) {
			$package_options[0] = __( 'Select a Package', 'simple-sponsorships' );
			foreach( $packages as $package ) {
				$package_options[ $package->get_data( 'ID' ) ] = $package->get_data( 'title' ) . ' (' . $package->get_price_formatted() . ')';
			}
		}
		$fields = array(
			'sponsor_name' => array(
				'title'    => __( 'Your Name', 'simple-sponsorships' ),
				'type'     => 'text',
				'required' => true,
			),
			'email' => array(
				'title'    => __( 'Your Email', 'simple-sponsorships' ),
				'type'     => 'email',
				'required' => true,
			),
			'website' => array(
				'title'    => __( 'Website', 'simple-sponsorships' ),
				'type'     => 'url',
				'required' => true,
			),
			'company' => array(
				'title'    => __( 'Company Name', 'simple-sponsorships' ),
				'type'     => 'text',
				'required' => false,
			),
			'package' => array(
				'title'             => __( 'Sponsorship', 'simple-sponsorships' ),
				'required'          => true,
				'type'              => 'select',
				'options'           => $package_options,
				'validate'          => array( $this, 'is_valid_package' ),
				'not_valid_message' => __( 'Please select a %s', 'simple-sponsorship' ),
			),
			'sponsor_terms' => array(
				'title'    => __( 'Terms and Conditions', 'simple-sponsorships' ),
				'required' => true,
				'type'     => 'checkbox',
				'desc'     => __( 'I have read the terms and conditions', 'simple-sponsorships' ),
			),
		);

		return apply_filters( 'ss_form_sponsors_fields', $fields );
	}

	/**
	 * Check if the package is a valid one.
	 *
	 * @param $package
	 */
	public function is_valid_package( $package ) {

		if ( ! absint( $package ) ) {
			return false;
		}

		$package = ss_get_package( $package );

		if ( ! $package->is_available() ) {
			return false;
		}

		return true;
	}

}