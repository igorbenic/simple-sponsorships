<?php
/**
 * Integrations for the Gravity Forms.
 */

namespace Simple_Sponsorships\Integrations;
use Simple_Sponsorships\Integrations\GravityForms\GF_Addon;

/**
 * Class GravityForms
 *
 * @package Simple_Sponsorships\Integrations
 */
class GravityForms extends Integration {

	/**
	 * GravityForms constructor.
	 */
	public function __construct() {
		$this->title = __( 'Gravity Forms', 'simple-sponsorships' );
		$this->id    = 'gravityforms';
		$this->desc  = __( 'Add fields to map with Sponsor Form field so you can use Gravity Forms to create sponsorships', 'simple-sponsorships' );
		$this->image = trailingslashit( SS_PLUGIN_URL ) . 'includes/integrations/gravityforms/logo.svg';
		// Integration loaded once everything has been loaded so we can call it like this.
		$this->load();
	}

	/**
	 * Load this integration once the GravityForm is loaded.
	 */
	public function load() {
		if ( ! method_exists( 'GFForms', 'include_addon_framework' ) ) {
			return;
		}

		require_once( 'gravityforms/class-gf-addon.php' );
		GF_Addon::get_instance();

    }
}