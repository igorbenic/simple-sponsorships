<?php
/**
 * Created by PhpStorm.
 * User: igor
 * Date: 20/03/2019
 * Time: 00:29
 */

namespace Simple_Sponsorships\Integrations\GravityForms;


class SS_Packages_Field extends \GF_Field {

	public $type = 'ss_packages';

	function get_form_editor_field_settings() {
		return array(
			'conditional_logic_field_setting',
			'prepopulate_field_setting',
			'error_message_setting',
			'label_setting',
			'label_placement_setting',
			'admin_label_setting',
			'size_setting',
			'rules_setting',
			'visibility_setting',
			'duplicate_setting',
			'default_value_setting',
			'placeholder_setting',
			'description_setting',
			'css_class_setting',
		);
	}

	public function get_form_editor_field_title() {
		return esc_attr__( 'Packages', 'simple-sponsorships' );
	}

	/**
	 * Showing the button in the correct group
	 *
	 * @return array
	 */
	public function get_form_editor_button() {
		return array(
			'group' => 'simple_sponsorships',
			'text'  => $this->get_form_editor_field_title(),
		);
	}

	/**
	 * Adds the field button to the specified group.
	 *
	 * @param array $field_groups The field groups containing the individual field buttons.
	 *
	 * @return array
	 */
	public function add_button( $field_groups ) {
		$field_groups = $this->maybe_add_field_group( $field_groups );

		return parent::add_button( $field_groups );
	}

	/**
	 * Adds the custom field group if it doesn't already exist.
	 *
	 * @param array $field_groups The field groups containing the individual field buttons.
	 *
	 * @return array
	 */
	public function maybe_add_field_group( $field_groups ) {
		foreach ( $field_groups as $field_group ) {
			if ( $field_group['name'] == 'simple_sponsorships' ) {

				return $field_groups;
			}
		}

		$field_groups[] = array(
			'name'   => 'simple_sponsorships',
			'label'  => __( 'Simple Sponsorships', 'simple-sponsorships' ),
			'fields' => array()
		);

		return $field_groups;
	}

	/**
	 * The field input field.
	 *
	 * @param array  $form
	 * @param string $value
	 * @param null   $entry
	 *
	 * @return string
	 */
	public function get_field_input( $form, $value = '', $entry = null ) {
		$is_entry_detail = $this->is_entry_detail();
		$is_form_editor  = $this->is_form_editor();
		$is_admin = $is_entry_detail || $is_form_editor;

		return $is_admin ? 'On Admin' : 'On Front';
	}
}

\GF_Fields::register( new SS_Packages_Field() );