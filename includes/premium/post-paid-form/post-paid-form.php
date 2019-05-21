<?php

/**
 * Plugin Name: Post Paid Form
 * Description: This is an add-on for Simple Sponsorships to add a post paid form.
 * Version: 1.0.0
 */

namespace Simple_Sponsorships\Post_Paid_Form;

if ( ! defined( 'ABSPATH' ) ) {
	return;
}

class Plugin {

	/**
	 * Plugin constructor.
	 */
	public function __construct() {
		include 'includes/class-form-post-paid.php';

		add_action( 'ss_sponsorship_form', array( $this, 'show_form' ) );
		add_action( 'ss_form_field_sponsor_logo', array( $this, 'logo_field' ), 20, 2 );
		add_action( 'ss_post_paid_form', array( $this, 'process_form' ) );
	}

	/**
	 * Processing the Form.
	 */
	function process_form() {
		$form = new Form_Post_Paid();
		$form->process();
	}

	/**
	 * Logo Field
	 *
	 * @param array $args
	 */
	public function logo_field( $args, $wrap_field ) {
		$name = isset( $args['name'] ) && $args['name'] ? $args['name'] : $args['id'];
		$id   = $args['id'];

		$html_wrap  = '<div class="ss-form-field ss-form-field-' . sanitize_html_class( $args['type' ] ) . '">';
		$html_wrap .= '<label for="' . esc_attr( $id ) . '">';
		$html_wrap .= esc_html( $args['title'] );
		$required = '';

		if ( $args['required'] ) {
			$html_wrap .= '<span class="ss-required">*</span>';
			$required = 'required="required"';
		}
		$html_wrap .= '</label>';
		$html_wrap .= '%s';
		$html_wrap .= '</div>';

		$html = '<input type="hidden" name="'. esc_attr( $name ) .'" value="' . esc_attr( $args['value'] ) . '" />';


		$html .= '<div class="ss-logo-container">';

		if ( $args['value'] ) {
			$html .= wp_get_attachment_image( $args['value'], 'full' );
		}

		$html .= '<input type="file" ' . $required . ' class="ss-file-input" name="' . esc_attr( $name ) . '_file" />';
		$html .= '</div>';
		$html .= '<p class="description">' . $args['desc'] . '</p>';
		if ( $wrap_field ) {
			$html = sprintf( $html_wrap, $html );
		}

		echo $html;
	}

	/**
	 * Show payment form if needed.
	 *
	 * @param \Simple_Sponsorships\Sponsorship $sponsorship Sponsorship Object.
	 */
	public function show_form( $sponsorship ) {
		if ( ! $sponsorship->is_paid() ) {
			return;
		}

		\Simple_Sponsorships\Templates::get_template_part( 'post-paid-form', null, array( 'sponsorship' => $sponsorship ) );
	}
}

new Plugin();