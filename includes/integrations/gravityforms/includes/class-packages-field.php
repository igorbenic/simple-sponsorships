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
			'label_setting',
			'duplicate_setting',
			'error_message_setting',
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
		$is_admin        = $is_entry_detail || $is_form_editor;
		$id              = (int) $this->id;
		$form_id         = $form['id'];
		$packages        = ss_get_available_packages();

		if ( ! $packages && $is_admin ) {
			return __( 'There are no available packages. Please create some.', 'simple-sponsorships' );
		}

		if ( ! $packages ) {
			return '';
		}

		$input = "<div class='ginput_container ginput_ss_packages' id='gf_ss_packages_container_{$form_id}'>" .
		         "<select name='input_{$id}' required='required'>";
		foreach ( $packages as $package ) {
			$input .= '<option value="' . esc_attr( $package->get_data('ID') ) . '">' . $package->get_data( 'title' ) . ' (' . $package->get_price_html() . ')' . '</option>';
		}
		$input .= "</select>" .
		         "</div>";

		return $input;
	}

	/**
	 * The entry details, for example, used in the email.
	 * @param array|string $value
	 * @param string       $currency
	 * @param bool         $use_text
	 * @param string       $format
	 * @param string       $media
	 *
	 * @return string
	 */
	public function get_value_entry_detail( $value, $currency = '', $use_text = false, $format = 'html', $media = 'screen' ) {
		if ( ! empty( $value ) ) {
			$package = ss_get_package( $value );
			return $package->get_data( 'title' ) . ' (' . $package->get_price_html() . ')';
		} else {
			return '';
		}
	}

	/**
	 * The entry that shows when listing.
	 * @param array|string $value
	 * @param array        $entry
	 * @param string       $field_id
	 * @param array        $columns
	 * @param array        $form
	 *
	 * @return string
	 */
	public function get_value_entry_list( $value, $entry, $field_id, $columns, $form ) {
		if ( ! empty( $value ) ) {
			$package = ss_get_package( $value );
			return $package->get_data( 'title' ) . ' (' . $package->get_price_html() . ')';
		} else {
			return '';
		}
	}

	/**
	 * @param array|string $value
	 * @param array        $form
	 */
	public function validate( $value, $form ) {
		if ( $value !== '' && $value !== 0 ) {
			$package = ss_get_package( $value );
			if ( ! $package->is_available() ) {
				$this->failed_validation = true;
				if ( ! empty( $this->errorMessage ) ) {
					$this->validation_message = $this->errorMessage;
				} else {
					$this->validation_message = __( 'Please select an available package.', 'simple-sponsorships' );
				}
			}
		}
	}
}

\GF_Fields::register( new SS_Packages_Field() );