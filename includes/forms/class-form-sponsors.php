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
			if ( ss_multiple_packages_enabled() ) {
				$args['packages'] = $posted_data['package'];
			} else {
				$args['packages'] = array( $posted_data['package'] => 1 );
			}
		}

		/**
		 * This can be used to check availability of all packages even the initial availability is fine.
		 */
		$package_availability_check = apply_filters( 'ss_create_sponsorships_package_availability_check', null, $args['packages'] );

		if ( null !== $package_availability_check ) {
			if ( is_wp_error( $package_availability_check ) ) {
				ss_add_notice( $package_availability_check->get_error_message(), 'error' );
			} else {
				ss_add_notice( $package_availability_check, 'error' );
			}
			return false;
		}

		$user_id = get_current_user_id();

		$account_required = apply_filters( 'ss_create_sponsorships_account_required', false, $posted_data );

		if ( ! $user_id && ( ss_is_account_creation_enabled() || $account_required ) ) {
			$create_account   = isset( $posted_data['create_account'] ) ? true : false;
			$account_username = isset( $posted_data['create_account_username'] ) ? sanitize_text_field( $posted_data['create_account_username'] ) : '';
			$account_pass     = isset( $posted_data['create_account_password'] ) ? sanitize_text_field( $posted_data['create_account_password'] ) : '';

			if ( $create_account || $account_required ) {
				if ( ! $account_username ) {
					ss_add_notice( __( 'Account Username is required.', 'simple-sponsorships' ), 'error' );
					return false;
				}

				if ( ! $account_pass ) {
					ss_add_notice( __( 'Account Password is required.', 'simple-sponsorships' ), 'error' );
					return false;
				}

				$user_id = $this->create_account( $posted_data['email'], $account_username, $account_pass );
			}
		}

		if ( is_wp_error( $user_id ) ) {
			ss_add_notice( $user_id->get_error_message(), 'error' );
			return false;
		}

		$sponsorship_id = ss_create_sponsorship( $args );
		$db             = new DB_Sponsorships();

		if ( $sponsorship_id ) {
			$meta_data = $this->unset_sponsorship_columns( $posted_data );

			foreach ( $meta_data as $key => $value ) {
				$db->update_meta( $sponsorship_id, '_' . $key, $value );
			}

			if ( $user_id ) {
				$db->update_meta( $sponsorship_id, '_user_id', $user_id );
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
	 * Create the account for the user
	 *
	 * @param $email
	 * @param $username
	 * @param $password
	 */
	public function create_account( $email, $username, $password ) {

		if ( email_exists( $email ) ) {
			return new \WP_Error( 'registration-error-email-exists', apply_filters( 'ss_registration_error_email_exists', __( 'An account is already registered with your email address. Please log in.', 'simple-sponsorships' ), $email ) );
		}

		$username = sanitize_user( $username );

		if ( empty( $username ) || ! validate_username( $username ) ) {
			return new \WP_Error( 'registration-error-invalid-username', __( 'Please enter a valid account username.', 'woocommerce' ) );
		}

		if ( username_exists( $username ) ) {
			return new \WP_Error( 'registration-error-username-exists', apply_filters( 'ss_registration_error_username_exists', sprintf( __( 'The username %s has already been taken. Please try another.', 'simple-sponsorships' ), $username ) ) );
		}

		if ( empty( $password ) ) {
			return new \WP_Error( 'registration-error-missing-password', __( 'Please enter an account password.', 'simple-sponsorships' ) );
		}

		// Use WP_Error to handle registration errors.
		$errors = new \WP_Error();

		do_action( 'ss_create_account', $username, $email, $errors );

		$errors = apply_filters( 'ss_account_creation_errors', $errors, $username, $email );

		if ( $errors->get_error_code() ) {
			return $errors;
		}

		$account_data = apply_filters(
			'ss_new_account_data',
			array(
				'user_login' => $username,
				'user_pass'  => $password,
				'user_email' => $email,
				'role'       => 'subscriber',
			)
		);

		$user_id = wp_insert_user( $account_data );

		if ( is_wp_error( $user_id ) ) {
			return $user_id;
		}

		do_action( 'ss_account_created', $user_id, $account_data );

		wp_set_current_user( $user_id );
		wp_set_auth_cookie( $user_id, true );

		return $user_id;
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
		$package_options = array( 0 => __( 'There are no packages available', 'simple-sponsorships' ) );
		if ( $packages ) {
			$package_options[0] = __( 'Select a Package', 'simple-sponsorships' );
			foreach( $packages as $package ) {
				$package_options[ $package->get_data( 'ID' ) ] = $package->get_data( 'title' ) . ' (' . $package->get_price_formatted() . ')';
			}
		}
		$package_field_type = ss_multiple_packages_enabled() ? 'package_select' : 'select';
		$package_required_function = ss_multiple_packages_enabled() ? array( $this, 'package_check_required' ) : false;

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
				'type'              => $package_field_type,
				'options'           => $package_options,
				'validate'          => array( $this, 'is_valid_package' ),
				'not_valid_message' => __( 'Please select a %s', 'simple-sponsorship' ),
				'packages'          => $packages,
				'required_function' => $package_required_function,
			),
		);

		if ( ss_is_account_creation_enabled() && ! is_user_logged_in() ) {
			$fields['create_account'] = array(
				'title'    => __( 'Create Account', 'simple-sponsorships' ),
				'required' => false,
				'type'     => 'checkbox',
			);

			$fields['create_account_username'] = array(
				'title'    => __( 'Account Username', 'simple-sponsorships' ),
				'required' => false,
				'type'     => 'text',
				'class'    => array( 'ss-hidden' )
			);

			$fields['create_account_password'] = array(
				'title'    => __( 'Account Password', 'simple-sponsorships' ),
				'required' => false,
				'type'     => 'password',
				'class'    => array( 'ss-hidden' )
			);
		}

		$fields['sponsor_terms'] = array(
			'title'    => __( 'Terms and Conditions', 'simple-sponsorships' ),
			'required' => true,
			'type'     => 'checkbox',
			'desc'     => $this->get_terms_description(),
		);

		return apply_filters( 'ss_form_sponsors_fields', $fields );
	}

	/**
	 * Get the Terms description.
	 *
	 * @return string
	 */
	protected function get_terms_description() {
		$terms         = sprintf( __( 'I have read and agree to %s.', 'simple-sponsorships' ), '[terms]' );
		$terms_page_id = ss_get_option( 'terms_page', 0 );
		$term_replace  = __( 'Terms and Conditions', 'simple-sponsorships' );

		if ( $terms_page_id ) {
			$term_replace = '<a target="_blank" href="' . esc_url( get_permalink( $terms_page_id ) ) . '">' . $term_replace . '</a>';
		}

		return str_replace( '[terms]', $term_replace, $terms );
	}

	/**
	 * Check if the package is a valid one.
	 *
	 * @param $package
	 */
	public function is_valid_package( $package ) {
		if ( ss_multiple_packages_enabled() ) {
			if ( $package ) {
				foreach ( $package as $package_id => $qty ) {
					$_package = ss_get_package( $package_id );

					if ( ! $_package->is_available() ) {
						return false;
					}
				}
			}
		} else {

			if ( ! absint( $package ) ) {
				return false;
			}

			$package = ss_get_package( $package );

			if ( ! $package->is_available() ) {
				return false;
			}
		}

		return true;
	}

	/**
	 * @param $packages
	 */
	public function package_check_required( $packages ) {
		$qty = 0;
		$return = false;
		if ( is_array( $packages ) && $packages ) {
			foreach ( $packages as $package_id => $package_qty ) {
				$qty += $package_qty;
			}
		}

		if ( $qty ) {
			$return = true;
		}

		return apply_filters( 'ss_package_check_required', $return, $packages );
	}

}