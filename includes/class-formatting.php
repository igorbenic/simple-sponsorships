<?php
/**
 * Class with formatting methods.
 */

namespace Simple_Sponsorships;

/**
 * Class Formatting
 *
 * @package Simple_Sponsorships
 */
class Formatting {

	/**
	 * Format the price with a currency symbol.
	 *
	 * Copied from WooCommerce
	 *
	 * @param  float $price Raw price.
	 * @param  array $args  Arguments to format a price {
	 *     Array of arguments.
	 *     Defaults to empty array.
	 *
	 *     @type bool   $ex_tax_label       Adds exclude tax label.
	 *                                      Defaults to false.
	 *     @type string $currency           Currency code.
	 *                                      Defaults to empty string (Use the result from get_woocommerce_currency()).
	 *     @type string $decimal_separator  Decimal separator.
	 *                                      Defaults the result of wc_get_price_decimal_separator().
	 *     @type string $thousand_separator Thousand separator.
	 *                                      Defaults the result of wc_get_price_thousand_separator().
	 *     @type string $decimals           Number of decimals.
	 *                                      Defaults the result of wc_get_price_decimals().
	 *     @type string $price_format       Price format depending on the currency position.
	 *                                      Defaults the result of get_woocommerce_price_format().
	 * }
	 * @return string
	 */
	public static function price( $price, $args = array() ) {
		$args = apply_filters(
			'ss_price_args', wp_parse_args(
				$args, array(
					'ex_tax_label'       => false,
					'currency'           => '',
					'decimal_separator'  => self::get_price_decimal_separator(),
					'thousand_separator' => self::get_price_thousand_separator(),
					'decimals'           => self::get_price_decimals(),
					'price_format'       => self::get_price_format(),
					'exclude_html'       => false,
				)
			)
		);

		$unformatted_price = $price;
		$negative          = $price < 0;
		$price             = apply_filters( 'raw_ss_price', floatval( $negative ? $price * -1 : $price ) );
		$price             = apply_filters( 'formatted_ss_price', number_format( $price, $args['decimals'], $args['decimal_separator'], $args['thousand_separator'] ), $price, $args['decimals'], $args['decimal_separator'], $args['thousand_separator'] );

		if ( apply_filters( 'ss_price_trim_zeros', false ) && $args['decimals'] > 0 ) {
			$price = self::trim_zeros( $price );
		}

		$symbol          = $args['exclude_html'] ? ss_currency_symbol( $args['currency'] ) : '<span class="ss-price-currencysymbol">' . ss_currency_symbol( $args['currency'] ) . '</span>';
		$formatted_price = ( $negative ? '-' : '' ) . sprintf( $args['price_format'], $symbol, $price );
		$return          = $args['exclude_html'] ? $formatted_price : '<span class="ss-price-amount amount">' . $formatted_price . '</span>';

		/**
		 * Filters the string of price markup.
		 *
		 * @param string $return            Price HTML markup.
		 * @param string $price             Formatted price.
		 * @param array  $args              Pass on the args.
		 * @param float  $unformatted_price Price as float to allow plugins custom formatting.
		 */
		return apply_filters( 'ss_price', $return, $price, $args, $unformatted_price );
	}

	/**
	 * Return the decimal separator for prices.
	 *
	 * @return string
	 */
	public static function get_price_decimal_separator() {
		$separator = apply_filters( 'ss_get_price_decimal_separator', ss_get_option( 'price_decimal_sep' ) );
		return $separator ? stripslashes( $separator ) : '.';
	}

	/**
	 * Return the thousand separator for prices.
	 *
	 * @return string
	 */
	public static function get_price_thousand_separator() {
		return stripslashes( apply_filters( 'ss_get_price_thousand_separator', ss_get_option( 'price_thousand_sep' ) ) );
	}

	/**
	 * Return the number of decimals after the decimal point.
	 *
	 * @return int
	 */
	public static function get_price_decimals() {
		return absint( apply_filters( 'ss_get_price_decimals', ss_get_option( 'price_num_decimals', 2 ) ) );
	}

	/**
	 * Get the price format depending on the currency position.
	 *
	 * @return string
	 */
	public static function get_price_format() {
		$currency_pos = ss_get_option( 'currency_pos', 'left' );
		$format       = '%1$s%2$s';

		switch ( $currency_pos ) {
			case 'left':
				$format = '%1$s%2$s';
				break;
			case 'right':
				$format = '%2$s%1$s';
				break;
			case 'left_space':
				$format = '%1$s&nbsp;%2$s';
				break;
			case 'right_space':
				$format = '%2$s&nbsp;%1$s';
				break;
		}

		return apply_filters( 'ss_price_format', $format, $currency_pos );
	}

	/**
	 * Trim trailing zeros off prices.
	 *
	 * @param string|float|int $price Price.
	 * @return string
	 */
	public static function trim_zeros( $price ) {
		return preg_replace( '/' . preg_quote( self::get_price_decimal_separator(), '/' ) . '0++$/', '', $price );
	}
}