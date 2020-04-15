<?php
/**
 * Recurring Sponsorship Object
 *
 * This is the parent Sponsorship used for Subscriptions.
 */

namespace Simple_Sponsorships\Recurring_Payments;

use Simple_Sponsorships\Sponsorship;

class Recurring_Sponsorship extends Sponsorship {

	/**
	 * Sponsorship Object
	 * @var null|Sponsorship
	 */
	protected $sponsorship = null;

	/**
	 * @param $sponsorship
	 */
	public function set_sponsorship( $sponsorship ) {
		$this->sponsorship = $sponsorship;
	}

	/**
	 * Get the data for the level
	 *
	 * @param string|array $key Key or array of keys for data.
	 */
	public function get_data( $key, $default = '' ) {

		if ( ! isset( $this->data[ $key ] ) && null !== $this->sponsorship ) {
			$data = $this->sponsorship->get_data( $key, $default );
			$this->set_data( $key, $data );
		}

		return parent::get_data( $key, $default );
	}

	/**
	 * Get Subscription Status
	 */
	public function get_recurring_status() {
		return $this->get_data( '_recurring_status', 'pending' );
	}

	/**
	 * Update the Subscription Status
	 */
	public function update_recurring_status( $status ) {
		$allowed_statuses = array_keys( ss_get_recurring_statuses() );
		if ( ! in_array( $status, $allowed_statuses, true ) ) {
			return false;
		}

		$this->update_data( '_recurring_status', $status );
		return true;
	}

	/**
	 * Get the expiry Date
	 *
	 * @return mixed|string
	 */
	public function get_expiry_date() {
		$expiry_date = $this->get_data( '_expiry_date', false );
		if ( ! $expiry_date ) {
			$this->calculate_expiry_date();
			$expiry_date = $this->get_data( '_expiry_date', false );
		}

		return $expiry_date;
	}

	/**
	 * Get the expiry Date
	 *
	 * @return mixed|string
	 */
	public function get_expiry_timestamp() {

		$expiration = $this->get_expiry_date();
		$timestamp  = ( $expiration && 'none' != $expiration ) ? strtotime( $expiration, current_time( 'timestamp' ) ) : false;

		return apply_filters( 'ss_sponsorship_get_expiry_timestamp', $timestamp, $this->get_id(), $this );
	}

	/**
	 * Expiry Date is the date for the next payment to be done
	 *
	 * @param string $date Date from which we calculate the date
	 */
	public function calculate_expiry_date( $date = null ) {
		// If empty, we use the start date.
		if ( ! $date ) {
			$date = $this->get_data( 'date' );
		}

		$offset    = get_option( 'gmt_offset', 0 ) * HOUR_IN_SECONDS;
		$timestamp = strtotime( $date ) - $offset;
		$packages  = $this->get_packages();
		$duration  = 0;
		$duration_unit = '';
		$duration_unit_val = 0;

		foreach ( $packages as $package_id => $object ) {
			if ( 'recurring' === $object->get_type() ) {
				$package_duration      = absint( $object->get_data( 'recurring_duration', 1 ) );
				$package_duration_unit = $object->get_data( 'recurring_duration_unit', 'day' );
				$package_duration_unit_val = 0;

				switch ( $package_duration_unit ) {
					case 'year':
						$package_duration_unit_val = 3;
						break;
					case 'month':
						$package_duration_unit_val = 2;
						break;
					default:
						$package_duration_unit_val = 1;
				}

				if ( 0 === $duration_unit_val || $duration_unit_val < $package_duration_unit_val ) {
					$duration          = $package_duration;
					$duration_unit_val = $package_duration_unit_val;
					$duration_unit     = $package_duration_unit;
				} elseif (  $duration_unit_val === $package_duration_unit_val && $duration < $package_duration ) {
					$duration          = $package_duration;
				}
			}
		}

		$extended_timestamp = 0;
		switch ( $duration_unit ) {
			case 'year':
				$extended_timestamp = $duration * YEAR_IN_SECONDS;
				break;
			case 'month':
				$extended_timestamp = $duration * MONTH_IN_SECONDS;
				break;
			case 'day':
				$extended_timestamp = $duration * DAY_IN_SECONDS;
				break;
		}

		if ( $extended_timestamp ) {
			$timestamp += $extended_timestamp;
		}

		$this->update_data( '_expiry_date', date( 'Y-m-d h:i:s', $timestamp ) );
	}

	/**
	 * Expire the Subscriptions
	 */
	public function expire() {
		$this->update_recurring_status( 'expired' );
		do_action( 'ss_recurring_sponsorship_expired' );
	}

	/**
	 * Expire the Subscriptions
	 */
	public function cancel() {
		$this->update_recurring_status( 'cancelled' );
		do_action( 'ss_recurring_sponsorship_cancelled' );
	}
}