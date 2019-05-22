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
		add_filter( 'ss_get_settings', array( $this, 'add_settings' ) );
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

		$can_replace_logo = (bool) ss_get_option( 'allow_sponsors_logo_replace', 0 );
		$can_delete_logo  = (bool) ss_get_option( 'allow_sponsors_logo_delete', 0 );

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
			if ( $can_delete_logo ) {
				$html .= '<button type="submit" name="ss_sponsor_delete_logo">' . __( 'Delete Logo', 'simple-sponsorships' ) . '</button>';
			} elseif ( $can_replace_logo ) {
				$html .= '<input type="file" ' . $required . ' class="ss-file-input" name="' . esc_attr( $name ) . '_file" />';
			}
		} else {
			$html .= '<input type="file" ' . $required . ' class="ss-file-input" name="' . esc_attr( $name ) . '_file" />';
		}

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

	/**
	 * Add Settings
	 *
	 * @param array $settings
	 *
	 * @return mixed
	 */
	public function add_settings( $settings ) {

		$general_main = $settings['general']['main'];
		$new_main     = array();
		foreach ( $general_main as $settings_id => $setting ) {
			$new_main[ $settings_id ] = $setting;
			if ( 'show_content_placeholder' === $settings_id ) {
				$new_main['allow_sponsors_logo_replace'] = array(
					'id'      => 'allow_sponsors_logo_replace',
					'label'   => __( 'Sponsors Logo Replace', 'simple-sponsorships' ),
					'type'    => 'checkbox',
					'desc'    => __( 'If checked, it will allow sponsors to replace their logo.', 'simple-sponsorships' ),
					'default' => '0'
				);

				$new_main['allow_sponsors_logo_delete'] = array(
					'id'      => 'allow_sponsors_logo_delete',
					'label'   => __( 'Sponsors Logo Delete', 'simple-sponsorships' ),
					'type'    => 'checkbox',
					'desc'    => __( 'If checked, it will allow sponsors to delete their logo and it will delete the logo from your site.', 'simple-sponsorships' ),
					'default' => '0'
				);
			}
		}
		$settings['general']['main'] = $new_main;
		return $settings;
	}
}

new Plugin();