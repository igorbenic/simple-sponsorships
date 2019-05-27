<?php

/**
 * Plugin Name: Package Features
 * Description: This is an add-on for Simple Sponsorships to add package features.
 * Version: 1.0.0
 */

namespace Simple_Sponsorships\Package_Features;

use Simple_Sponsorships\DB\DB_Packages;
use Simple_Sponsorships\DB\DB_Sponsorship_Items;
use Simple_Sponsorships\Sponsorship;
use Simple_Sponsorships\Templates;

if ( ! defined( 'ABSPATH' ) ) {
	return;
}

class Plugin {

	/**
	 * Plugin constructor.
	 */
	public function __construct() {
		add_action( 'ss_get_package_fields', array( $this, 'add_features_field' ), 11 );
		add_action( 'ss_package_updated', array( $this, 'save_package_features' ), 20, 2 );
		add_action( 'ss_package_added', array( $this, 'save_package_features' ), 20, 2 );
		add_action( 'ss_settings_field_package_features', array( $this, 'features_field' ) );
		add_action( 'init', array( $this, 'register_shortcode' ) );
	}

	/**
	 * Register the Shortcode
	 */
	public function register_shortcode() {
		add_shortcode( 'ss_package_pricing_tables', array( $this, 'pricing_tables_shortcode' ) );
	}

	/**
	 * Pricing Tables
	 *
	 * @param $atts
	 */
	public function pricing_tables_shortcode( $atts ) {
		$atts = shortcode_atts( array(
			'packages' => '',
			'button'   => 1,
			'col'      => 0,
		), $atts, 'ss_package_pricing_tables' );

		ob_start();
		Templates::get_template_part( 'shortcode/package-pricing-tables', null, $atts );
		return ob_get_clean();
	}

	/**
	 * Features Field
	 *
	 * @param array $args
	 */
	public function features_field( $args ) {
		$name = isset( $args['name'] ) && $args['name'] ? $args['name'] : $args['id'];
		$id   = $args['id'];

		$required = '';

		if ( $args['required'] ) {
			$required = 'required="required"';
		}
		$packages        = ss_get_packages();
		$package_options = array();

		foreach ( $packages as $package ) {
			if ( isset( $_GET['id'] ) && absint( $_GET['id'] ) === absint( $package['ID'] ) ) {
				continue;
			}

			$package_options[ absint( $package['ID'] ) ] = $package['title'];
		}

		$html = '<div class="ss-package-features">';
		if ( $package_options ) {
			$package_selected = isset( $args['value'] ) && is_array( $args['value'] ) && isset( $args['value']['package'] ) ? absint( $args['value']['package'] ) : 0;
			$html .= '<div class="ss-package-feature">';
			$html .= '<label for="includes_package_feature">' . __( 'Includes Features from: ', 'simple-sponsorships' ) . '</label>';
			$html .= '<select class="widefat" id="includes_package_feature" name="' . esc_attr( $name ) . '[package]">';
			$html .= '<option value="0">' . esc_html__( 'No Package', 'simple-sponsorships' ) . '</option>';
			foreach ( $package_options as $package_id => $package_title ) {
				$html .= '<option ' . selected( $package_id, $package_selected, false ) . ' value="' . esc_attr( $package_id ) . '">' . esc_html( $package_title ) . '</option>';
			}
			$html .= '</select>';
			$html .= '</div>';
		}
		if ( $args['value'] && is_array( $args['value'] ) ) {
			$features_count = $args['value'];
			foreach ( $args['value'] as $index => $value ) {
				if ( 'package' === $index ) { continue; }
				$html .= '<div class="ss-package-feature">';
				$html .= '<input type="text" class="widefat" name="' . esc_attr( $name ) . '[]" value="' . esc_attr( $value ) . '" />';
				$html .= '<button type="button" class="button button-secondary button-small ss-button-action" data-success="removePackageFeature">x</button>';

				if ( $features_count > 1 ) {
					$html .= '<span type="button" class="button button-secondary button-small ss-sortable-handle" aria-label="' . esc_attr__( 'Move Feature Up or Down', 'simple-sponsorships' ) . '"><span class=" dashicons dashicons-sort"></span></span>';
				}
				$html .= '</div>';
			}
		}
		$html .= '</div>';
		$html .= '<div class="ss-package-feature" id="ss_package_feature_template">';
		$html .= '<input type="text" class="widefat" name="' . esc_attr( $name ) . '[]" />';
		$html .= '</div>';
		$html .= '<button type="button" class="button button-secondary ss-button-action" data-success="addPackageFeature">' . esc_attr__( 'Add Feature', 'simple-sponsorships' ) . '</button>';
		$html .= '<p class="description">' . $args['desc'] . '</p>';


		echo $html;
	}

	/**
	 * Features Field
	 * @param array $fields
	 */
	public function add_features_field( $fields ) {

		$fields['features'] = array(
			'id'          => 'features',
			'type'        => 'package_features',
			'title'       => __( 'Features', 'simple-sponsorships' ),
			'field_class' => 'widefat',
			'default'     => array(),
			'desc'        => __( 'List package features.', 'simple-sponsorships' ),
		);

		return $fields;
	}

	/**
	 * @param $id
	 * @param $data
	 */
	public function save_package_features( $id, $data ) {
		$db = new DB_Packages();
		if ( isset( $data['features'] ) ) {
			$db->update_meta( $id, 'features', array_filter( $data['features'] ) );
		} else {
			$db->delete_meta( $id, 'features' );
		}
	}
}

new Plugin();