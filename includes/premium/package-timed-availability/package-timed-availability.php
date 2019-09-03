<?php

/**
 * Plugin Name: Package Timed Availability
 * Description: This is an add-on for Simple Sponsorships to add package availability.
 * Version: 1.0.0
 */

namespace Simple_Sponsorships\Package_Timed_Availability;

use Simple_Sponsorships\DB\DB_Packages;
use Simple_Sponsorships\Integrations\Integration;

if ( ! defined( 'ABSPATH' ) ) {
	return;
}

class Plugin extends Integration {

	/**
	 * Plugin constructor.
	 */
	public function __construct() {
		$this->title = __( 'Package Timed Availability', 'simple-sponsorships-premium' );
		$this->id    = 'package-timed-availability';
		$this->desc  = __( 'Define the availability of each package through date and time.', 'simple-sponsorships-premium' );
		$this->image = trailingslashit( SS_PLUGIN_URL ) . 'assets/images/svg/integrations/clock.svg';

		add_action( 'ss_get_package_fields', array( $this, 'add_field' ) );
		add_action( 'ss_package_updated', array( $this, 'save_package' ), 20, 2 );
		add_action( 'ss_package_added', array( $this, 'save_package' ), 20, 2 );
		add_filter( 'ss_package_is_available', array( $this, 'is_package_available' ), 20, 2 );
		add_action( 'ss_settings_field_package_datetime_availability', array( $this, 'datetime_availability_field' ) );

	}

	/**
	 * @param $args
	 */
	public function datetime_availability_field( $args ) {
		$name = isset( $args['name'] ) && $args['name'] ? $args['name'] : $args['id'];
		$id   = $args['id'];

		$required = '';

		if ( $args['required'] ) {
			$required = 'required="required"';
		}

		$package_id = isset( $_GET['id'] ) ? absint( $_GET['id'] ) : false;
		$from_date  = '';
		$from_time  = '';
		$to_date    = '';
		$to_time    = '';

		if ( $package_id ) {
			$db = new DB_Packages();
			$from_date = $db->get_meta( $package_id, '_from_date' );
			$from_time = $db->get_meta( $package_id, '_from_time' );
			$to_date   = $db->get_meta( $package_id, '_to_date' );
			$to_time   = $db->get_meta( $package_id, '_to_time' );
		}

		?>
		<div class="ss-package-datetime-availability">
			<p><strong><?php esc_html_e( 'From', 'simple-sponsorships-premium' ); ?></strong></p>
			<p class="description"><?php esc_html_e( 'Leave empty to make it available from any date.', 'simple-sponsorships-premium' ); ?></p>

			<input autocomplete="off" type="text" name="ss_packages[from_date]" class="ss-datepicker" value="<?php echo esc_attr( $from_date ); ?>" />
			<input autocomplete="off" type="time" name="ss_packages[from_time]" value="<?php echo esc_attr( $from_time ); ?>" />

			<p><strong><?php esc_html_e( 'To', 'simple-sponsorships-premium' ); ?></strong></p>
			<p class="description"><?php esc_html_e( 'Leave empty to make it available until any date.', 'simple-sponsorships-premium' ); ?></p>

			<input autocomplete="off" type="text" name="ss_packages[to_date]" class="ss-datepicker" value="<?php echo esc_attr( $to_date ); ?>" />
			<input autocomplete="off" type="time" name="ss_packages[to_time]" value="<?php echo esc_attr( $to_time ); ?>" />
		</div>
		<?php
	}

	/**
	 * @param $fields
	 */
	public function add_field( $fields ) {

		$fields['from_date'] = array(
			'id' => 'from_date',
			'type' => 'package_datetime_availability',
			'title' => __( 'Availability', 'simple-sponsorships-premium' ),
			'field_class' => array( 'widefat', 'ss-datepicker' ),
			'default' => '',
			'desc' => __( 'From when is this package available', 'simple-sponsorships-premium' ),
		);

		return $fields;
	}

	/**
	 * @param $id
	 * @param $data
	 */
	public function save_package( $id, $data ) {
		$db = new DB_Packages();
		if ( isset( $data['from_date'] ) ) {
			$db->update_meta( $id, '_from_date', sanitize_text_field( $data['from_date'] ) );
		}

		if ( isset( $data['from_time'] ) ) {
			$db->update_meta( $id, '_from_time', sanitize_text_field( $data['from_time'] ) );
		}

		if ( isset( $data['to_date'] ) ) {
			$db->update_meta( $id, '_to_date', sanitize_text_field( $data['to_date'] ) );
		}

		if ( isset( $data['to_time'] ) ) {
			$db->update_meta( $id, '_to_time', sanitize_text_field( $data['to_time'] ) );
		}
	}

	/**
	 * Return if the package is available.
	 *
	 * @param $bool
	 * @param \Simple_Sponsorships\Package $package
	 */
	public function is_package_available( $bool, $package ) {
		if ( ! $bool ) {
			return $bool;
		}

		$from_date = $package->get_data('_from_date' );
		$from_time = $package->get_data('_from_time' );
		$to_date   = $package->get_data('_to_date' );
		$to_time   = $package->get_data('_to_time' );

		if ( ! $from_date ) { return $bool; }

		$from_date_format = $from_date;
		if ( $from_time ) {
			$from_date_format .= ' ' . $from_time;
		}

		$now       = time();
		$offset    = get_option( 'gmt_offset' ) * HOUR_IN_SECONDS;
		$from_time = strtotime( $from_date_format ) - $offset;

		if ( $now < $from_time ) {
			return false;
		}

		if ( ! $to_date ) { return $bool; }

		$to_date_format = $to_date;
		if ( $to_time ) {
			$to_date_format .= ' ' . $to_time;
		}
		$to_time = strtotime( $to_date_format ) - $offset;

		if ( $now > $to_time ) {
			return false;
		}

		return $bool;
	}

}
