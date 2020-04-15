<?php
/**
 * Integration asbtract class
 */

namespace Simple_Sponsorships\Integrations;

/**
 * Class Integration
 *
 * @package Simple_Sponsorships\Integrations
 */
abstract class Integration {

	/**
	 * Integration ID (slug)
	 *
	 * @var string
	 */
	public $id = '';

	/**
	 * Title
	 *
	 * @var string
	 */
	public $title = '';

	/**
	 * Image
	 *
	 * @var string
	 */
	public $image = '';

	/**
	 * Description
	 *
	 * @var string
	 */
	public $desc = '';

	/**
	 * Active
	 *
	 * @var bool
	 */
	public $active = false;

	/**
	 * @param bool $active
	 */
	public function set_active( $active = true ) {
		$this->active = $active;
	}

	/**
	 * Method that will be called when the integration is being deactivated.
	 */
	public function deactivate() {}

	/**
	 * Method that will be called when the integration is activated.
	 */
	public function activate() {}


	/**
	 * Buttons
	 */
	public function buttons() {
		if( ! $this->active ) {
			?>
			<button type="button" data-integration="<?php echo $this->id; ?>" class="button button-primary ss-button-integration-activate"><?php _e( 'Activate', 'givasap' ); ?></button>
			<?php
		} else {
			?>
			<button type="button" data-integration="<?php echo $this->id; ?>" class="button button-default ss-button-integration-deactivate"><?php _e( 'Deactivate', 'givasap' ); ?></button>
			<?php
		}
	}
}