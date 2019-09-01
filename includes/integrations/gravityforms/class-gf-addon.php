<?php
/**
 * Created by PhpStorm.
 * User: igor
 * Date: 20/03/2019
 * Time: 00:17
 */

namespace Simple_Sponsorships\Integrations\GravityForms;
use Simple_Sponsorships\Form_Sponsors;

\GFForms::include_addon_framework();

class GF_Addon extends \GFAddOn {

	/**
	 * This addon Version
	 *
	 * @var string
	 */
	protected $_version = '1.0.0';

	/**
	 * Minimum GF Version
	 *
	 * @var string
	 */
	protected $_min_gravityforms_version = '1.9';

	/**
	 * Addon Slug
	 *
	 * @var string
	 */
	protected $_slug = 'simple_sponsorship_addon';

	/**
	 * Path from Plugins folder
	 * @var string
	 */
	protected $_path = 'simple-sponsorships/includes/integrations/class-gravityforms.php';

	/**
	 * Full path
	 * @var string
	 */
	protected $_full_path = __FILE__;

	/**
	 * Title
	 *
	 * @var string
	 */
	protected $_title = 'Gravity Forms Simple Field Add-On';

	/**
	 * Short Title
	 *
	 * @var string
	 */
	protected $_short_title = 'Simple Field Add-On';

	private static $_instance = null;

	public static function get_instance() {
		if ( self::$_instance == null ) {
			self::$_instance = new self();
		}

		return self::$_instance;
	}

	public function pre_init() {
		parent::pre_init();

		if ( ! $this->is_gravityforms_supported() ) {
			return;
		}

		add_action( 'gform_field_advanced_settings', array( $this, 'advanced_settings_field' ), 10, 2 );
		add_action( 'gform_editor_js', array($this, 'editor_script_field'), 11 );

		add_filter( 'gform_form_settings', array( $this, 'add_sponsorship_form_settings' ), 10, 2 );
		add_filter( 'gform_pre_form_settings_save', array( $this, 'save_form_setting' ) );

		add_action( 'gform_after_submission', array( $this, 'submission' ), 20, 2 );

		if ( $this->is_gravityforms_supported() && class_exists( 'GF_Field' ) ) {
			require_once( 'includes/class-packages-field.php' );
		}
	}

	/**
     * Submitting the Form
     *
	 * @param $entry
	 * @param $form
	 */
	public function submission( $entry, $form ) {
        if ( isset( $form['ss_sponsorship_form'] ) && absint( $form['ss_sponsorship_form'] ) ) {
            $ss_form     = new Form_Sponsors();
            $ss_fields   = $ss_form->get_fields();
            $form_fields = $form['fields'];
            $ss_data     = array();

            foreach ( $form_fields as $form_field ) {
                if (
                    'ss_packages' === $form_field['type'] ||
                    ( isset( $form_field['sponsor_field'] ) && $form_field['sponsor_field'] )
                ) {
	                $field_id = $form_field['id'];
	                $ss_field = 'ss_packages' === $form_field['type'] ? 'package' : $form_field['sponsor_field'];
	                if ( is_array( $form_field['inputs'] ) ) {
		                $ss_data[ $ss_field ] = array();
                        foreach( $form_field['inputs'] as $input ) {
	                        $ss_data[ $ss_field ][] = rgar( $entry, $input['id'] );
                        }
		                $ss_data[ $ss_field ] = implode( ' ', array_filter( $ss_data[ $ss_field ] ) );
                    } else {
		                $ss_data[ $ss_field ] = rgar( $entry, $field_id );
                    }
                }
            }

            $ss_data['package'] = array( $ss_data['package'] => 1 );
            $redirect = isset( $form['ss_sponsorship_redirect'] ) ? absint( $form['ss_sponsorship_redirect'] ) : 0;
            $ss_form->create_sponsorship( $ss_data, $redirect );
        }
    }

	/**
     * Adding a Section and Fields for Sponsorship
     *
	 * @param array $fields
	 */
	public function add_sponsorship_form_settings( $fields, $form ) {

		$ss_form_checked = '';
		if ( rgar( $form, 'ss_sponsorship_form' ) ) {
			$ss_form_checked = 'checked="checked"';
		}

		$ss_form_redirect = '';
		if ( rgar( $form, 'ss_sponsorship_redirect' ) ) {
			$ss_form_redirect = 'checked="checked"';
		}

	    $ss_form =  '
        <tr>
            <th>
                <label for="ss_sponsorship_form">' .
	            __( 'Sponsorship Form?', 'gravityforms' ) . ' ' .
            '</label>
		    </th>
		    <td>
			    <input type="checkbox" id="ss_sponsorship_form" name="ss_sponsorship_form" value="1" ' . $ss_form_checked . '/>
			    <label for="ss_sponsorship_form">' . __( 'Check if this form should be used to create new sponsorships', 'simple-sponsorships' ) . '</label>
            </td>
        </tr>';

		$ss_redirect =  '
        <tr>
            <th>
                <label for="ss_sponsorship_redirect">' .
		            __( 'Redirect to Sponsorship Page', 'gravityforms' ) . ' ' .
		            '</label>
		    </th>
		    <td>
			    <input type="checkbox" id="ss_sponsorship_redirect" name="ss_sponsorship_redirect" value="1" ' . $ss_form_redirect . '/>
			    <label for="ss_sponsorship_redirect">' . __( 'Redirect to the Sponsorship Page when the sponsorship is created.', 'simple-sponsorships' ) . '</label>
            </td>
        </tr>';
        $ss_fields = apply_filters( 'ss_gravity_form_settings_fields', array(
            'ss_sponsorship_form' => $ss_form,
            'ss_sponsorship_redirect' => $ss_redirect,
        ), $form, $fields );

        $fields[ __( 'Sponsorships', 'simple-sponsorships' ) ] = $ss_fields;
        return $fields;
    }

	/**
     * Save Form Settings.
     *
	 * @param array $form form.
	 *
	 * @return mixed
	 */
    public function save_form_setting( $form ) {
	    $form['ss_sponsorship_form']     = rgpost( 'ss_sponsorship_form' );
	    $form['ss_sponsorship_redirect'] = rgpost( 'ss_sponsorship_redirect' );
	    return $form;
    }

	/**
	 * JavaScript on the edit form
	 */
	public function editor_script_field() {
		?>

		<script type='text/javascript'>

            // To display custom field under each type of Gravity Forms field
            jQuery.each( fieldSettings, function(index, value) {
                if ( 'ss_packages' !== index ) {
                    fieldSettings[index] += ", .field_sponsor_field";
                }
            });

            jQuery( document.body ).on( 'change', '.field_sponsor_field select', function(){
                var $this = jQuery(this),
                    value = $this.val();
                SetFieldProperty( 'sponsor_field', value );
            });

            // store the custom field with associated Gravity Forms field
            jQuery(document).bind( "gform_load_field_settings", function( event, field, form ){

                // save field value: Start Section B
                jQuery("#field_sponsor_field_" + form.id ).val( field["sponsor_field"] );
                // End Section B
            });

		</script>

		<?php
	}

	/**
	 * Adding the Sponsor Field Settings.
	 *
	 * @param integer $position
	 * @param integer $form_id
	 */
	public function advanced_settings_field( $position, $form_id ) {
		if ( $position == 50 ) {
			$form   = new Form_Sponsors();
			$fields = $form->get_fields();
			if ( ! $fields ) { return; }
			?>
			<li class="field_sponsor_field field_setting">
				<label for="field_sponsor_field_<?php echo $form_id; ?>">
					<?php _e( "Sponsor Field", 'simple-sponsorships' ); ?>
				</label>
				<select id="field_sponsor_field_<?php echo $form_id; ?>">
					<option value=""><?php esc_html_e( 'Choose a Sponsor Field', 'simple-sponsorships' ); ?></option>
					<?php
					foreach ( $fields as $field_id => $field ) {
						echo '<option value="' . $field_id . '">' . $field['title'] . '</option>';
					}
					?>
				</select>
			</li>
			<?php
		}
	}
}