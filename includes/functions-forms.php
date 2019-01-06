<?php
/**
 * Globally available functions for forms.
 */

if ( ! defined( 'ABSPATH' ) ) {
	return;
}

/**
 * Processing the Sponsor Form.
 */
function ss_process_sponsor_form() {
	$form = new \Simple_Sponsorships\Form_Sponsors();
	$form->process();
}

/**
 * Processing the Sponsor Form.
 */
function ss_process_payment_form() {
	$form = new \Simple_Sponsorships\Form_Payment();
	$form->process();
}

/**
 * Show payment form if needed.
 *
 * @param \Simple_Sponsorships\Sponsorship $sponsorship Sponsorship Object.
 */
function ss_show_payment_form_for_sponsorship( $sponsorship ) {
	if ( ! $sponsorship->is_approved() ) {
		return;
	}

	\Simple_Sponsorships\Templates::get_template_part( 'payment-form', null, array( 'sponsorship' => $sponsorship ) );
}

/**
 * Clean variables using sanitize_text_field. Arrays are cleaned recursively.
 * Non-scalar values are ignored.
 *
 * Copied from WooCommerce
 *
 * @param string|array $var Data to sanitize.
 * @return string|array
 */
function ss_clean( $var ) {
	if ( is_array( $var ) ) {
		return array_map( 'ss_clean', $var );
	} else {
		return is_scalar( $var ) ? sanitize_text_field( $var ) : $var;
	}
}

/**
 * Run wc_clean over posted textarea but maintain line breaks.
 *
 * Copied from WooCommerce
 *
 * @param  string $var Data to sanitize.
 * @return string
 */
function ss_sanitize_textarea( $var ) {
	return implode( "\n", array_map( 'ss_clean', explode( "\n", $var ) ) );
}

/**
 * @param $args
 */
function ss_form_render_field( $args, $wrap_field = true ) {
	$args = wp_parse_args( $args, array(
		'section'       => '',
		'id'            => null,
		'desc'          => '',
		'name'          => '',
		'size'          => null,
		'options'       => '',
		'std'           => '',
		'value'         => '',
		'min'           => null,
		'max'           => null,
		'step'          => null,
		'chosen'        => null,
		'multiple'      => null,
		'placeholder'   => null,
		'allow_blank'   => true,
		'readonly'      => false,
		'faux'          => false,
		'tooltip_title' => false,
		'tooltip_desc'  => false,
		'field_class'   => '',
		'title'         => '',
		'required'      => false,
	) );

	$class = '';

	if ( $args['field_class'] ) {
		if ( is_array( $args['field_class'] ) ) {
			$class = array_values( array_map( 'sanitize_html_class', $args['field_class'] ) );
			$class = implode( ' ', array_unique( $class ) );
		} else {
			$class = sanitize_html_class( $args['field_class'] );
		}
	}

	$id = $args['id'];

	$name = 'name="' . $id . '"';

	$label = '<label for="' . $id . '"> '  . wp_kses_post( $args['desc'] ) . '</label>';

	$required = '';

	$html_wrap  = '<div class="ss-form-field ss-form-field-' . sanitize_html_class( $args['type' ] ) . '">';
	$html_wrap .= '<label for="' . esc_attr( $id ) . '">';
	$html_wrap .= esc_html( $args['title'] );
	if ( $args['required'] ) {
		$html_wrap .= '<span class="ss-required">*</span>';
		$required = 'required="required"';
	}
	$html_wrap .= '</label>';
	$html_wrap .= '%s';
	$html_wrap .= '</div>';

	switch( $args['type'] ) {
		case 'text':
		case 'email':
		case 'password':
		case 'url':
			$type = $args['type'];
			if ( $args['value'] ) {
				$value = $args['value'];
			} elseif( ! empty( $args['allow_blank'] ) && empty( $args['value'] ) ) {
				$value = '';
			} else {
				$value = isset( $args['std'] ) ? $args['std'] : '';
			}

			if ( isset( $args['faux'] ) && true === $args['faux'] ) {
				$args['readonly'] = true;
				$value = isset( $args['std'] ) ? $args['std'] : '';
				$name  = '';
			}

			$disabled = ! empty( $args['disabled'] ) ? ' disabled="disabled"' : '';
			$readonly = $args['readonly'] === true ? ' readonly="readonly"' : '';
			$size     = ( isset( $args['size'] ) && ! is_null( $args['size'] ) ) ? $args['size'] : 'regular';
			$html     = '<input type="' . $type . '" class="' . $class . ' ' . sanitize_html_class( $size ) . '-text" id="' . $id . '" ' . $name . ' value="' . esc_attr( stripslashes( $value ) ) . '"' . $readonly . $disabled . $required . ' placeholder="' . esc_attr( $args['placeholder'] ) . '"/>';
			$html    .= $label;

			if ( $wrap_field ) {
				$html = sprintf( $html_wrap, $html );
			}

			echo apply_filters( 'ss_after_field_output', $html, $args );
			break;
		case 'number':

			if ( $args['value'] ) {
				$value = $args['value'];
			} else {
				$value = isset( $args['std'] ) ? $args['std'] : '';
			}

			if ( isset( $args['faux'] ) && true === $args['faux'] ) {
				$args['readonly'] = true;
				$value = isset( $args['std'] ) ? $args['std'] : '';
				$name  = '';
			}

			$max  = isset( $args['max'] ) ? $args['max'] : 999999;
			$min  = isset( $args['min'] ) ? $args['min'] : 0;
			$step = isset( $args['step'] ) ? $args['step'] : 1;

			$size = ( isset( $args['size'] ) && ! is_null( $args['size'] ) ) ? $args['size'] : 'regular';
			$html = '<input type="number" step="' . esc_attr( $step ) . '" max="' . esc_attr( $max ) . '" min="' . esc_attr( $min ) . '" class="' . $class . ' ' . sanitize_html_class( $size ) . '-text" id="' . $id . '" ' . $name . $required . ' value="' . esc_attr( stripslashes( $value ) ) . '"/>';
			$html .= $label;

			if ( $wrap_field ) {
				$html = sprintf( $html_wrap, $html );
			}

			echo apply_filters( 'ss_after_field_output', $html, $args );
			break;
		case 'textarea':

			if ( $args['value'] ) {
				$value = $args['value'];
			} else {
				$value = isset( $args['std'] ) ? $args['std'] : '';
			}

			$html = '<textarea class="' . $class . ' large-text" cols="50" rows="5" id="' . $id . '" ' . $name . $required . '>' . esc_textarea( stripslashes( $value ) ) . '</textarea>';
			$html .= $label;

			if ( $wrap_field ) {
				$html = sprintf( $html_wrap, $html );
			}

			echo apply_filters( 'ss_after_field_output', $html, $args );
			break;
		case 'select':

			if ( $args['value'] ) {
				$value = $args['value'];
			} else {

				// Properly set default fallback if the Select Field allows Multiple values
				if ( empty( $args['multiple'] ) ) {
					$value = isset( $args['std'] ) ? $args['std'] : '';
				} else {
					$value = ! empty( $args['std'] ) ? $args['std'] : array();
				}

			}

			if ( isset( $args['placeholder'] ) ) {
				$placeholder = $args['placeholder'];
			} else {
				$placeholder = '';
			}

			if ( isset( $args['chosen'] ) ) {
				$class .= ' edd-select-chosen';
			}

			$nonce = isset( $args['data']['nonce'] )
				? ' data-nonce="' . sanitize_text_field( $args['data']['nonce'] ) . '" '
				: '';

			// If the Select Field allows Multiple values, save as an Array
			$name_attr = $id;
			$name_attr = ( $args['multiple'] ) ? $name_attr . '[]' : $name_attr;

			$html = '<select ' . $nonce . $required . ' id="' . $id . '" name="' . $name_attr . '" class="' . $class . '" data-placeholder="' . esc_html( $placeholder ) . '" ' . ( ( $args['multiple'] ) ? 'multiple="true"' : '' ) . '>';

			foreach ( $args['options'] as $option => $name ) {

				if ( ! $args['multiple'] ) {
					$selected = selected( $option, $value, false );
					$html .= '<option value="' . esc_attr( $option ) . '" ' . $selected . '>' . esc_html( $name ) . '</option>';
				} else {
					// Do an in_array() check to output selected attribute for Multiple
					$html .= '<option value="' . esc_attr( $option ) . '" ' . ( ( in_array( $option, $value ) ) ? 'selected="true"' : '' ) . '>' . esc_html( $name ) . '</option>';
				}

			}

			$html .= '</select>';
			$html .= $label;

			if ( $wrap_field ) {
				$html = sprintf( $html_wrap, $html );
			}

			echo apply_filters( 'ss_after_field_output', $html, $args );
			break;
		case 'multicheck':

			$html = '';
			if ( ! empty( $args['options'] ) ) {
				$html .= '<input type="hidden" ' . $name . ' value="-1" />';
				foreach( $args['options'] as $key => $option ):
					if( isset( $args['value'][ $key ] ) ) { $enabled = $option; } else { $enabled = NULL; }
					$html .= '<input ' . $required . ' name="' . $id . '[' . $key . ']" id="' . $id . '[' . $key . ']" class="' . $class . '" type="checkbox" value="' . esc_attr( $option ) . '" ' . checked($option, $enabled, false) . '/>&nbsp;';
					$html .= '<label for="' . $id . '[' . $key . ']">' . wp_kses_post( $option ) . '</label><br/>';
				endforeach;
				$html .= '<p class="description">' . $args['desc'] . '</p>';
			}

			echo apply_filters( 'ss_after_field_output', $html, $args );
			break;
		case 'checkbox':

			if ( isset( $args['faux'] ) && true === $args['faux'] ) {
				$name = '';
			}

			$checked  = ! empty( $args['value'] ) ? checked( 1, $args['value'], false ) : '';
			$html     = '<label for="' . $id . '"><input type="hidden"' . $name . ' value="-1" />';
			$html    .= '<input type="checkbox" id="' . $id . '"' . $name . ' value="1" ' . $checked . $required . ' class="' . $class . '"/>';
			$html    .= wp_kses_post( $args['desc'] ) . '</label>';

			if ( $wrap_field ) {
				$html = sprintf( $html_wrap, $html );
			}

			echo apply_filters( 'ss_after_field_output', $html, $args );
			break;
		case 'heading':
			echo isset( $args['desc'] ) ? '<h2>' . $args['desc'] . '</h2>' : '';
			break;
		default:
			do_action( 'ss_form_field_' . $args['type'], $args );
			break;
	}
}

/**
 * Format the postcode according to the country and length of the postcode.
 *
 * Copied from WooCommerce
 *
 * @param string $postcode Unformatted postcode.
 * @param string $country  Base country.
 * @return string
 */
function ss_format_postcode( $postcode, $country ) {
	$postcode = ss_normalize_postcode( $postcode );

	switch ( $country ) {
		case 'CA':
		case 'GB':
			$postcode = trim( substr_replace( $postcode, ' ', -3, 0 ) );
			break;
		case 'IE':
			$postcode = trim( substr_replace( $postcode, ' ', 3, 0 ) );
			break;
		case 'BR':
		case 'PL':
			$postcode = substr_replace( $postcode, '-', -3, 0 );
			break;
		case 'JP':
			$postcode = substr_replace( $postcode, '-', 3, 0 );
			break;
		case 'PT':
			$postcode = substr_replace( $postcode, '-', 4, 0 );
			break;
		case 'US':
			$postcode = rtrim( substr_replace( $postcode, '-', 5, 0 ), '-' );
			break;
	}

	return apply_filters( 'ss_format_postcode', $postcode, $country );
}

/**
 * Normalize postcodes.
 *
 * Remove spaces and convert characters to uppercase.
 *
 * Copied from WooCommerce
 *
 * @param string $postcode Postcode.
 * @return string
 */
function ss_normalize_postcode( $postcode ) {
	return preg_replace( '/[\s\-]/', '', trim( strtoupper( $postcode ) ) );
}