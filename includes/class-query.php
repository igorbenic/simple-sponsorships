<?php
/**
 * Created by PhpStorm.
 * User: igor
 * Date: 20/01/2020
 * Time: 16:46
 */

namespace Simple_Sponsorships;

defined( 'ABSPATH' ) || exit;

/**
 * WC_Query Class.
 */
class Query {

	/**
	 * Query vars to add to wp.
	 *
	 * @var array
	 */
	public $query_vars = array();

	/**
	 * Reference to the main product query on the page.
	 *
	 * @var array
	 */
	private static $product_query;

	/**
	 * Stores chosen attributes.
	 *
	 * @var array
	 */
	private static $_chosen_attributes;

	/**
	 * Constructor for the query class. Hooks in methods.
	 */
	public function __construct() {
		add_action( 'init', array( $this, 'add_endpoints' ) );
		if ( ! is_admin() ) {
			add_action( 'wp_loaded', array( $this, 'get_errors' ), 20 );
			add_filter( 'query_vars', array( $this, 'add_query_vars' ), 0 );
			add_action( 'parse_request', array( $this, 'parse_request' ), 0 );
			add_filter( 'the_title', array( $this, 'page_endpoint_title' ) );
		}
		$this->init_query_vars();
	}

	/**
	 * Get any errors from querystring.
	 */
	public function get_errors() {
		$error = ! empty( $_GET['ss_error'] ) ? sanitize_text_field( wp_unslash( $_GET['ss_error'] ) ) : ''; // WPCS: input var ok, CSRF ok.

		if ( $error && ! wc_has_notice( $error, 'error' ) ) {
			ss_add_notice( $error, 'error' );
		}
	}

	/**
	 * Init query vars by loading options.
	 */
	public function init_query_vars() {
		// Query vars to add to WP.
		$this->query_vars = array(
			// My account actions.
			'sponsorships'               => get_option( 'ss_myaccount_sponsorships_endpoint', 'sponsorships' ),
			'view-sponsorship'           => get_option( 'ss_myaccount_view_sponsorship_endpoint', 'view-sponsorship' ),
			'sponsored-content'          => get_option( 'ss_myaccount_sponsored_content_endpoint', 'content' ),
			//'downloads'                  => get_option( 'woocommerce_myaccount_downloads_endpoint', 'downloads' ),
			'sponsor-info'               => get_option( 'ss_myaccount_edit_sponsor_endpoint', 'sponsor-info' ),
			'reports'                    => get_option( 'ss_myaccount_reports_endpoint', 'reports' ),
			//'edit-address'               => get_option( 'woocommerce_myaccount_edit_address_endpoint', 'edit-address' ),
			//'payment-methods'            => get_option( 'woocommerce_myaccount_payment_methods_endpoint', 'payment-methods' ),
			//'lost-password'              => get_option( 'woocommerce_myaccount_lost_password_endpoint', 'lost-password' ),
			//'customer-logout'            => get_option( 'woocommerce_logout_endpoint', 'customer-logout' ),
			//'add-payment-method'         => get_option( 'woocommerce_myaccount_add_payment_method_endpoint', 'add-payment-method' ),
			//'delete-payment-method'      => get_option( 'woocommerce_myaccount_delete_payment_method_endpoint', 'delete-payment-method' ),
			//'set-default-payment-method' => get_option( 'woocommerce_myaccount_set_default_payment_method_endpoint', 'set-default-payment-method' ),
		);
	}

	/**
	 * Replace a page title with the endpoint title.
	 *
	 * @param  string $title Post title.
	 * @return string
	 */
	function page_endpoint_title( $title ) {
		global $wp_query;

		if ( ! is_null( $wp_query ) && ! is_admin() && is_main_query() && in_the_loop() && is_page() && is_ss_endpoint_url() ) {
			$endpoint       = $this->get_current_endpoint();
			$endpoint_title = $this->get_endpoint_title( $endpoint );
			$title          = $endpoint_title ? $endpoint_title : $title;

			remove_filter( 'the_title', array( $this, 'page_endpoint_title' ) );
		}

		return $title;
	}

	/**
	 * Get page title for an endpoint.
	 *
	 * @param  string $endpoint Endpoint key.
	 * @return string
	 */
	public function get_endpoint_title( $endpoint ) {
		global $wp;

		switch ( $endpoint ) {
			case 'sponsorships':
				if ( ! empty( $wp->query_vars['sponsorships'] ) ) {
					/* translators: %s: page */
					$title = sprintf( __( 'Sponosrships (page %d)', 'simple-sponsorships' ), intval( $wp->query_vars['sponsorships'] ) );
				} else {
					$title = __( 'Sponsorships', 'simple-sponsorships' );
				}
				break;
			case 'view-sponsorship':
				$sponsorship = ss_get_sponsorship( $wp->query_vars['view-sponsorship'] );
				/* translators: %s: order number */
				$title = ( $sponsorship ) ? sprintf( __( 'Sponsorship #%s', 'simple-sponsorships' ), $sponsorship->get_id() ) : '';
				break;
			/*case 'downloads':
				$title = __( 'Downloads', 'woocommerce' );
				break;*/
			case 'sponsor-info':
				$title = __( 'Sponsor', 'simple-sponsorships' );
				break;
			case 'sponsor-reports':
				$title = __( 'Reports', 'simple-sponsorships' );
				break;
			/*case 'edit-address':
				$title = __( 'Addresses', 'woocommerce' );
				break;
			case 'payment-methods':
				$title = __( 'Payment methods', 'woocommerce' );
				break;
			case 'add-payment-method':
				$title = __( 'Add payment method', 'woocommerce' );
				break;
			case 'lost-password':
				$title = __( 'Lost password', 'woocommerce' );
				break;*/
			default:
				$title = '';
				break;
		}

		return apply_filters( 'ss_endpoint_' . $endpoint . '_title', $title, $endpoint );
	}

	/**
	 * Endpoint mask describing the places the endpoint should be added.
	 *
	 * @since 2.6.2
	 * @return int
	 */
	public function get_endpoints_mask() {
		if ( 'page' === get_option( 'show_on_front' ) ) {
			$page_on_front     = get_option( 'page_on_front' );
			$myaccount_page_id = ss_get_option( 'account_page', 0 );

			if ( in_array( $page_on_front, array( $myaccount_page_id ), true ) ) {
				return EP_ROOT | EP_PAGES;
			}
		}

		return EP_PAGES;
	}

	/**
	 * Add endpoints for query vars.
	 */
	public function add_endpoints() {
		$mask = $this->get_endpoints_mask();

		foreach ( $this->get_query_vars() as $key => $var ) {
			if ( ! empty( $var ) ) {
				add_rewrite_endpoint( $var, $mask );
			}
		}
	}

	/**
	 * Add query vars.
	 *
	 * @param array $vars Query vars.
	 * @return array
	 */
	public function add_query_vars( $vars ) {
		foreach ( $this->get_query_vars() as $key => $var ) {
			$vars[] = $key;
		}
		return $vars;
	}

	/**
	 * Get query vars.
	 *
	 * @return array
	 */
	public function get_query_vars() {
		return apply_filters( 'ss_get_query_vars', $this->query_vars );
	}

	/**
	 * Get query current active query var.
	 *
	 * @return string
	 */
	public function get_current_endpoint() {
		global $wp;

		foreach ( $this->get_query_vars() as $key => $value ) {
			if ( isset( $wp->query_vars[ $key ] ) ) {
				return $key;
			}
		}
		return '';
	}

	/**
	 * Parse the request and look for query vars - endpoints may not be supported.
	 */
	public function parse_request() {
		global $wp;

		// Map query vars to their keys, or get them if endpoints are not supported.
		foreach ( $this->get_query_vars() as $key => $var ) {
			if ( isset( $_GET[ $var ] ) ) { // WPCS: input var ok, CSRF ok.
				$wp->query_vars[ $key ] = sanitize_text_field( wp_unslash( $_GET[ $var ] ) ); // WPCS: input var ok, CSRF ok.
			} elseif ( isset( $wp->query_vars[ $var ] ) ) {
				$wp->query_vars[ $key ] = $wp->query_vars[ $var ];
			}
		}
	}
}
