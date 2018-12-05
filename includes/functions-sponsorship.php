<?php
/**
 * Globally available functions for Sponsorships and similar.
 */

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
 */
function ss_get_sponsorship( $id ) {
	return new \Simple_Sponsorships\Sponsorship( $id, true );
}

/**
 * Return sponsorship statuses.
 *
 * @return array
 */
function ss_get_sponsorship_statuses() {
	return apply_filters( 'ss_sponsorship_statuses', array(
		'request'   => __( 'Request', 'simple-sponsorships' ),
		'pending'   => __( 'Pending', 'simple-sponsorships' ),
		'completed' => __( 'Completed', 'simple-sponsorships' ),
	));
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
		'gateway'        => 'manual',
		'sponsor'        => 0,
		'currency'       => ss_get_currency(),
		'transaction_id' => '',
		'date'           => date( 'Y-m-d H:i:s', current_time( 'timestamp' ) ),
		'ss_key'         => uniqid( '_ss', true ),
	);

	$args = wp_parse_args( $args, $default );
	$db   = new \Simple_Sponsorships\DB\DB_Sponsorships();
	$ret  = $db->insert( $args );

	if ( $ret ) {
		do_action( 'ss_sponsorship_created', $ret );
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