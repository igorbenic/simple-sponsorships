<?php
/**
 * A class for managing all widgets.
 */

namespace Simple_Sponsorships\Widgets;

if ( ! defined( 'ABSPATH' ) ) {
	return;
}

/**
 * Class Widgets
 *
 * @package Simple_Sponsorships
 */
class Widgets {

	/**
	 * Widgets constructor.
	 */
	public function __construct() {

		$this->include_widgets();

		add_action( 'widgets_init', array( $this, 'register_widgets' ) );
	}

	/**
	 * Include the widgets.
	 */
	public function include_widgets() {
		include_once 'widgets/class-widget-sponsors.php';
		include_once 'widgets/class-widget-placeholder.php';
	}

	/**
	 * Register all widgets.
	 */
	public function register_widgets() {

		register_widget( '\Simple_Sponsorships\Widgets\Widget_Sponsors' );
		register_widget( '\Simple_Sponsorships\Widgets\Widget_Placeholder' );
	}
}