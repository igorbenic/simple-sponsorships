<?php
/**
 * A class as a base to be used for building forms.
 */

namespace Simple_Sponsorships;
use Simple_Sponsorships\DB\DB_Sponsorships;

/**
 * Class Form
 *
 * @package Simple_Sponsorships
 */
abstract class Form {

	/**
	 * @var string
	 */
	protected $slug = '';

	/**
	 * This will hold errors.
	 *
	 * @var null|\WP_Error
	 */
	protected $errors = null;

	/**
	 * Form_Sponsors constructor.
	 */
	public function __construct() {
		$this->errors = new \WP_Error();
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

		foreach ( $this->get_fields() as $key => $field ) {

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

			$data[ $key ] = apply_filters( 'ss_posted_' . $this->slug . '_' . $type . '_field', apply_filters( 'ss_posted_' . $this->slug . '_field_' . $key, $value ) );

		}

		return apply_filters( 'ss_' . $this->slug . '_posted_data', $data );
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
	 * Process the Form Data. Returns the posted data that was also validated.
	 *
	 * @return boolean|array
	 */
	public function process_data() {
		$this->reset_errors();

		$posted_data = $this->get_posted_data();

		$this->validate_fields( $posted_data );

		if ( 0 !== count( $this->errors->get_error_messages( ) ) ) {
			ss_add_notices( $this->errors->get_error_messages( ), 'error' );
			return false;
		}

		return $posted_data;
	}

	/**
	 * Validate the posted data.
	 *
	 * @param array $data
	 */
	public function validate_fields( &$data ) {
		foreach ( $this->get_fields() as $key => $field ) {
			if ( ! isset( $data[ $key ] ) ) {
				continue;
			}

			$required     = isset( $field['required'] ) ? $field['required'] : false;
			$required_fnc = isset( $field['required_function'] ) ? $field['required_function'] : false;
			$validate     = isset( $field['validate'] ) ? $field['validate'] : '';
			$sanitization = isset( $field['sanitize'] ) ? $field['sanitize'] : '';
			$field_label  = isset( $field['title'] ) ? $field['title'] : '';

			if ( $validate && is_callable( $validate ) ) {
				$is_valid = call_user_func( $validate, $data[ $key ] );
				if ( ! $is_valid ) {
					$validation_message = isset( $field['not_valid_message'] ) ? $field['not_valid_message'] : __( '%s is not valid.', 'simple-sponsorship' );
					$validation_message = apply_filters( $this->slug . '_validation_message_' . $key, $validation_message, $field );
					$this->errors->add( 'validation', sprintf( $validation_message, '<strong>' . esc_html( $field_label ) . '</strong>' ) );
					continue;
				}
			}

			if ( $sanitization && is_callable( $sanitization ) ) {
				$data[ $key ] = call_user_func( $sanitization, $data[ $key ] );
			}

			if ( $required ) {
				$add_error = '' === $data[ $key ] ? true : false;

				if ( $required_fnc && is_callable( $required_fnc ) ) {
					$add_error = false;
					if ( ! call_user_func( $required_fnc, $data[ $key ] ) ) {
						$add_error = true;
					}
				}

				if ( $add_error ) {
					/* translators: %s: field name */
					$this->errors->add( 'required-field', apply_filters( 'ss_required_field_notice', sprintf( __( '%s is a required field.', 'simple-sponsorships' ), '<strong>' . esc_html( $field_label ) . '</strong>' ), $field, $key, $this->slug ) );
				}
			}
		}
	}

	/**
	 * Return the fields for Form Sponsors.
	 */
	public function get_fields() {
		return array();
	}
}