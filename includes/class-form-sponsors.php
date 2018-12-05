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
class Form_Sponsors {

	/**
	 * This will hold errors.
	 *
	 * @var null|\WP_Error
	 */
	private $errors = null;

	/**
	 * Form_Sponsors constructor.
	 */
	public function __construct() {
		$this->errors = new \WP_Error();

		add_action( 'ss_sponsor_form', array( $this, 'process' ) );
	}

	/**
	 * Get posted data from the form.
	 *
	 * Parts copied from WooCommerce.
	 *
	 * @return array of data.
	 */
	public function get_posted_data() {

		$data = array();

		foreach ( self::get_fields() as $key => $field ) {

		    $type = sanitize_title( isset( $field['type'] ) ? $field['type'] : 'text' );

			switch ( $type ) {
				case 'checkbox':
					$value = isset( $_POST[ $key ] ) ? 1 : ''; // WPCS: input var ok, CSRF ok.
					break;
				case 'multiselect':
					$value = isset( $_POST[ $key ] ) ? implode( ', ', ss_clean( wp_unslash( $_POST[ $key ] ) ) ) : ''; // WPCS: input var ok, CSRF ok.
					break;
				case 'textarea':
					$value = isset( $_POST[ $key ] ) ? ss_sanitize_textarea( wp_unslash( $_POST[ $key ] ) ) : ''; // WPCS: input var ok, CSRF ok.
					break;
				case 'password':
					$value = isset( $_POST[ $key ] ) ? wp_unslash( $_POST[ $key ] ) : ''; // WPCS: input var ok, CSRF ok, sanitization ok.
					break;
				default:
					$value = isset( $_POST[ $key ] ) ? ss_clean( wp_unslash( $_POST[ $key ] ) ) : ''; // WPCS: input var ok, CSRF ok.
					break;
			}

			$data[ $key ] = apply_filters( 'ss_process_sponsor_form_' . $type . '_field', apply_filters( 'ss_process_sponsor_form_field_' . $key, $value ) );

		}

		return apply_filters( 'ss_sponsor_form_posted_data', $data );
	}

	/**
	 * Reset all errors.
	 */
	public function reset_errors() {
		$codes = $this->errors->get_error_codes();

		if ( $codes ) {
			foreach ( $codes as $code ) {
				$this->errors->remove( $code );
			}
		}
	}

	/**
	 * Process the Form.
	 */
	public function process() {
		$this->reset_errors();

		$posted_data = $this->get_posted_data();

		$this->validate_fields( $posted_data );

		if ( 0 !== count( $this->errors->get_error_messages( ) ) ) {
			ss_add_notices( $this->errors->get_error_messages( ), 'error' );
			return;
		}

		$this->create_sponsorship( $posted_data );
	}

	/**
	 * Create a Sponsorship.
	 *
	 * @param array $posted_data
	 */
	public function create_sponsorship( $posted_data ) {

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

			if ( $sponsorship_page ) {
				$sponsorship = new Sponsorship( $sponsorship_id );
				$redirect = get_permalink( $sponsorship_page );
				$redirect = add_query_arg( 'sponsorship-key', $sponsorship->get_data( 'ss_key' ), $redirect );
				wp_safe_redirect( $redirect );
				exit;
			}

			/**
			 * @todo Send an email.
			 */
		} else {
			ss_add_notice( __( 'Sponsorship could not be created. Try contacting the site owner through email.', 'simple-sponsorships' ), 'error' );
		}
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
	 * Validate the posted data.
	 *
	 * @param array $data
	 */
	public function validate_fields( &$data ) {
		foreach ( self::get_fields() as $key => $field ) {
			if ( ! isset( $data[ $key ] ) ) {
				continue;
			}

			$required     = isset( $field['required'] ) ? $field['required'] : false;
			$validate     = isset( $field['validate'] ) ? $field['validate'] : '';
			$sanitization = isset( $field['sanitize'] ) ? $field['sanitize'] : '';
			$field_label  = isset( $field['title'] ) ? $field['title'] : '';

			if ( $validate && is_callable( $validate ) ) {
				$is_valid = call_user_func( $validate, $data[ $key ] );
				if ( ! $is_valid ) {
					$validation_message = isset( $field['not_valid_message'] ) ? $field['not_valid_message'] : __( '%s is not valid.', 'simple-sponsorship' );
					$this->errors->add( 'validation', sprintf( $validation_message, '<strong>' . esc_html( $field_label ) . '</strong>' ) );
					continue;
				}
			}

			if ( $sanitization && is_callable( $sanitization ) ) {
				$data[ $key ] = call_user_func( $sanitization, $data[ $key ] );
			}

			if ( $required && '' === $data[ $key ] ) {
				/* translators: %s: field name */
				$this->errors->add( 'required-field', apply_filters( 'ss_required_field_notice', sprintf( __( '%s is a required field.', 'simple-sponsorships' ), '<strong>' . esc_html( $field_label ) . '</strong>' ), $field_label ) );
			}
		}
	}

	/**
	 * Return the fields for Form Sponsors.
	 */
	public static function get_fields() {
		$packages = ss_get_packages();
		$package_options = array();
		if ( $packages ) {
			$package_options[0] = __( 'Select a Package', 'simple-sponsorships' );
			foreach( $packages as $package ) {
				$package_options[ $package['ID'] ] = $package['title'];
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
				'validate'          => array( __CLASS__, 'is_valid_package' ),
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
	public static function is_valid_package( $package ) {
		if ( absint( $package ) > 0 ) {
			return true;
		}

		return false;
	}

}