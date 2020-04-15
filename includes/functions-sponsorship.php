<?php
/**
 * Globally available functions for Sponsorships and similar.
 */

if ( ! defined( 'ABSPATH' ) ) {
	return;
}

/**
 * Get Currencies
 *
 * @since 0.1.0
 *
 * @return array $currencies A list of the available currencies
 */
function ss_get_currencies() {
	$currencies = array(
		'USD'  => __( 'US Dollars (&#36;)', 'simple-sponsorships' ),
		'EUR'  => __( 'Euros (&euro;)', 'simple-sponsorships' ),
		'GBP'  => __( 'Pound Sterling (&pound;)', 'simple-sponsorships' ),
		'AUD'  => __( 'Australian Dollars (&#36;)', 'simple-sponsorships' ),
		'BRL'  => __( 'Brazilian Real (R&#36;)', 'simple-sponsorships' ),
		'CAD'  => __( 'Canadian Dollars (&#36;)', 'simple-sponsorships' ),
		'CZK'  => __( 'Czech Koruna', 'simple-sponsorships' ),
		'DKK'  => __( 'Danish Krone', 'simple-sponsorships' ),
		'HKD'  => __( 'Hong Kong Dollar (&#36;)', 'simple-sponsorships' ),
		'HUF'  => __( 'Hungarian Forint', 'simple-sponsorships' ),
		'ILS'  => __( 'Israeli Shekel (&#8362;)', 'simple-sponsorships' ),
		'JPY'  => __( 'Japanese Yen (&yen;)', 'simple-sponsorships' ),
		'MYR'  => __( 'Malaysian Ringgits', 'simple-sponsorships' ),
		'MXN'  => __( 'Mexican Peso (&#36;)', 'simple-sponsorships' ),
		'NZD'  => __( 'New Zealand Dollar (&#36;)', 'simple-sponsorships' ),
		'NOK'  => __( 'Norwegian Krone', 'simple-sponsorships' ),
		'PHP'  => __( 'Philippine Pesos', 'simple-sponsorships' ),
		'PLN'  => __( 'Polish Zloty', 'simple-sponsorships' ),
		'SGD'  => __( 'Singapore Dollar (&#36;)', 'simple-sponsorships' ),
		'SEK'  => __( 'Swedish Krona', 'simple-sponsorships' ),
		'CHF'  => __( 'Swiss Franc', 'simple-sponsorships' ),
		'TWD'  => __( 'Taiwan New Dollars', 'simple-sponsorships' ),
		'THB'  => __( 'Thai Baht (&#3647;)', 'simple-sponsorships' ),
		'INR'  => __( 'Indian Rupee (&#8377;)', 'simple-sponsorships' ),
		'TRY'  => __( 'Turkish Lira (&#8378;)', 'simple-sponsorships' ),
		'RIAL' => __( 'Iranian Rial (&#65020;)', 'simple-sponsorships' ),
		'RUB'  => __( 'Russian Rubles', 'simple-sponsorships' ),
		'AOA'  => __( 'Angolan Kwanza', 'simple-sponsorships' ),
	);

	return apply_filters( 'ss_currencies', $currencies );
}

/**
 * Get the current currency
 *
 * @return string
 */
function ss_get_currency() {
	return ss_get_option( 'currency', 'USD' );
}

/**
 * Currency Symbol
 *
 * @param  string $currency The currency string
 * @return string           The symbol to use for the currency
 */
function ss_currency_symbol( $currency = '' ) {
	if ( empty( $currency ) ) {
		$currency = ss_get_currency();
	}

	switch ( $currency ) :
		case "GBP" :
			$symbol = '&pound;';
			break;
		case "BRL" :
			$symbol = 'R&#36;';
			break;
		case "EUR" :
			$symbol = '&euro;';
			break;
		case "USD" :
		case "AUD" :
		case "NZD" :
		case "CAD" :
		case "HKD" :
		case "MXN" :
		case "SGD" :
			$symbol = '&#36;';
			break;
		case "JPY" :
			$symbol = '&yen;';
			break;
		case "AOA" :
			$symbol = 'Kz';
			break;
		default :
			$symbol = $currency;
			break;
	endswitch;

	return apply_filters( 'ss_currency_symbol', $symbol, $currency );
}

/**
 * Get the Sponsorship.
 *
 * @param integer $id
 * @param boolean $load_data If true, the data will be loaded immediately.
 */
function ss_get_sponsorship( $id, $load_data = true ) {
	return new \Simple_Sponsorships\Sponsorship( $id, $load_data );
}

/**
 * Return sponsorship statuses.
 *
 * @return array
 */
function ss_get_sponsorship_statuses() {
	return apply_filters( 'ss_sponsorship_statuses', array(
		'request'  => __( 'Request', 'simple-sponsorships' ),
		'approved' => __( 'Approved', 'simple-sponsorships' ),
		'on-hold'  => __( 'On Hold', 'simple-sponsorships' ),
		'paid'     => __( 'Paid', 'simple-sponsorships' ),
		'rejected' => __( 'Rejected', 'simple-sponsorships' ),
		'failed'   => __( 'Failed', 'simples-sponsorships' ),
	));
}

/**
 * It will echo the status text.
 *
 * @return array
 */
function ss_the_sponsorship_status( $status ) {
	$statuses = ss_get_sponsorship_statuses();

	echo isset( $statuses[ $status ] ) ? $statuses[ $status ] : __( 'Unknown Status', 'simple-sponsorships' );
}

/**
 * Create a Sponsorship
 *
 * @param array $args
 *
 * @return bool|int
 */
function ss_create_sponsorship( $args = array() ) {

	$default = array(
		'status'         => 'request',
		'amount'         => 0,
		'package'        => 0,
		'packages'       => array(),
		'type'           => 'onetime',
		'gateway'        => 'manual',
		'sponsor'        => 0,
		'currency'       => ss_get_currency(),
		'transaction_id' => '',
		'parent_id'      => 0,
		'date'           => date( 'Y-m-d H:i:s', current_time( 'timestamp' ) ),
		'ss_key'         => uniqid( '_ss', true ),
	);

	$args     = wp_parse_args( $args, $default );
	$packages = $args['packages'];
	unset( $args['packages'] );
	$db  = new \Simple_Sponsorships\DB\DB_Sponsorships();
	$ret = $db->insert( $args );

	if ( $ret ) {
		$sponsorship = ss_get_sponsorship( $ret, false );
		if ( $packages ) {
			foreach ( $packages as $package_id => $qty ) {
				if ( ! $qty ) {
					continue;
				}
				$sponsorship->add_package( $package_id, $qty );
			}
		}

		do_action( 'ss_sponsorship_before_calculate_totals', $ret, $args, $sponsorship );

		$sponsorship->calculate_totals();

		do_action( 'ss_sponsorship_created', $ret, $args, $sponsorship );
	}

	return $ret;
}

/**
 * Displaying Sponsorship Details.
 *
 * @param integer|\Simple_Sponsorships\Sponsorship $sponsorship
 */
function ss_sponsorship_details( $sponsorship ) {
	if ( is_integer( $sponsorship ) ) {
		$sponsorship = new \Simple_Sponsorships\Sponsorship( $sponsorship );
	}

	\Simple_Sponsorships\Templates::get_template_part( 'sponsorship/details', '', array( 'sponsorship' => $sponsorship ) );
}

/**
 * Displaying Sponsorship Sponsor Information.
 *
 * @param integer|\Simple_Sponsorships\Sponsorship $sponsorship
 */
function ss_sponsorship_sponsor( $sponsorship ) {
	if ( is_integer( $sponsorship ) ) {
		$sponsorship = new \Simple_Sponsorships\Sponsorship( $sponsorship );
	}

	\Simple_Sponsorships\Templates::get_template_part( 'sponsorship/sponsor', '', array( 'sponsorship' => $sponsorship ) );
}

/**
 * Displaying Sponsorship Details.
 *
 * @param integer|\Simple_Sponsorships\Sponsorship $sponsorship
 */
function ss_sponsorship_customer_details( $sponsorship ) {
	if ( is_integer( $sponsorship ) ) {
		$sponsorship = new \Simple_Sponsorships\Sponsorship( $sponsorship );
	}

	\Simple_Sponsorships\Templates::get_template_part( 'sponsorship/customer-details', '', array( 'sponsorship' => $sponsorship ) );
}

/**
 * Activate the sponsorships when it's status has changed.
 *
 * @param $status
 * @param $old_status
 * @param $sponsorship_id
 */
function ss_activate_sponsorship_on_status_change( $status, $old_status, $sponsorship_id ) {
	if ( 'paid' === $status && 'paid' !== $old_status ) {
		$sponsorship = new \Simple_Sponsorships\Sponsorship( $sponsorship_id );
		$sponsorship->activate();
	}
}

/**
 * Return Table Columns for Sponsorships
 *
 * @since 1.5.0
 *
 * @return array
 */
function ss_get_sponsorships_table_columns() {
	return apply_filters( 'ss_sponsorships_table_columns', array(
		'id'     => __( '#', 'simple-sponsorships' ),
		'status' => __( 'Status', 'simple-sponsorships' ),
		'amount' => __( 'Amount', 'simple-sponsorships'),
		'date'   => __( 'Date', 'simple-sponsorships' ),
	));
}

/**
 * Get the column value for each sponsorship
 * @param \Simple_Sponsorships\Sponsorship $sponsorship
 * @param string                           $column Column slug
 *
 * @return string
 */
function ss_get_sponsorships_table_column_value( $sponsorship, $column ) {
	$ret = '';

	switch ( $column ) {
		case 'id':
			$ret = '<a href="' . esc_url( $sponsorship->get_view_account_url() ) . '">' . $sponsorship->get_id() . '</a>';
			break;
		case 'amount':
			$ret = $sponsorship->get_formatted_amount();
			break;
		case 'date':
			$date_format = get_option( 'date_format' );
			$time_format = get_option( 'time_format' );

			$ret = '<small class="ss-sponsorship-date" style="display:block;">' . date_i18n( $date_format, strtotime( $sponsorship->get_data('date') ) ) . '</small>';

			if ( $time_format ) {
				$ret .= '<small class="ss-sponsorship-time" style="display:block;">'. date_i18n( $time_format, strtotime( $sponsorship->get_data('date') ) ) . '</small>';
			}
			break;
		case 'actions':
			$ret = array(
				'view' => '<a class="ss-button button" href="' . esc_url( $sponsorship->get_view_account_url() ) . '">' . __( 'View', 'simple-sponsorships' ) . '</a>',
			);
			break;
		case 'status':
			$status   = $sponsorship->get_data( 'status' );
			$statuses = ss_get_sponsorship_statuses();
			$ret = isset( $statuses[ $status ] ) ? $statuses[ $status ] : $status;
			break;
		default:
			$ret = $sponsorship->get_data( $column );
			break;
	}

	$ret = apply_filters( 'ss_get_sponsorships_table_column_value_' . $column, $ret, $sponsorship );
	if ( is_array( $ret ) ) {
		$ret = implode( ' ', $ret );
	}
	return $ret;
}