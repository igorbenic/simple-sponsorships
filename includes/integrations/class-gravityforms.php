<?php
/**
 * Integrations for the Gravity Forms.
 */

namespace Simple_Sponsorships\Integrations;
use Simple_Sponsorships\Form_Sponsors;

/**
 * Class GravityForms
 *
 * @package Simple_Sponsorships\Integrations
 */
class GravityForms {

	/**
	 * GravityForms constructor.
	 */
	public function __construct() {
		add_action( 'gform_loaded', array( $this, 'load' ) );
	}

	/**
	 * Load this integration once the GravityForm is loaded.
	 */
	public function load() {
		if ( ! method_exists( 'GFForms', 'include_addon_framework' ) ) {
			return;
		}

		require_once( 'gravityforms/class-gf-addon.php' );

		\GFAddOn::register( '\Simple_Sponsorships\Integrations\GravityForms\GF_Addon' );


    }
}

new GravityForms();