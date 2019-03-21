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

		if ( $this->is_gravityforms_supported() && class_exists( 'GF_Field' ) ) {
			require_once( 'includes/class-packages-field.php' );
		}
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