<?php
/**
 * Functions for Recurring Payments
 */

/**
 * Get all the Recurring Statuses
 *
 * @return mixed|void
 */
function ss_get_recurring_statuses() {
	return apply_filters( 'ss_recurring_statuses', array(
		'pending'   => __( 'Pending', 'simple-sponsorships-premium' ),
		'active'    => __( 'Active', 'simple-sponsorships-premium' ),
		'cancelled' => __( 'Cancelled', 'simple-sponsorships-premium' ),
		'expired'   => __( 'Expired', 'simple-sponsorship-premium' ),
	));
}

/**
 * Return if the sponsorship can have recurring once. Default checks the status of the main sponsorhsip.
 *
 * @param \Simple_Sponsorships\Sponsorship $parent_sponsorship
 *
 * @return boolean
 */
function ss_sponsorship_can_have_recurring( $parent_sponsorship ) {
	$status                   = $parent_sponsorship->get_data( 'status' );
	$allowed_recurring_status = apply_filters( 'ss_recurring_sponsorship_allowed_status', array( 'paid' ), $parent_sponsorship );
	$can_have                 = false;

	if ( $allowed_recurring_status && in_array( $status, $allowed_recurring_status ) ) {
		$can_have = true;
	}

	return apply_filters( 'ss_sponsorship_can_have_recurring', $can_have, $parent_sponsorship );
}

/**
 * Create a Recurring Sponsorship
 *
 * @param \Simple_Sponsorships\Sponsorship $parent_sponsorship
 *
 * @return \Simple_Sponsorships\Sponsorship|boolean False if sponsorship not created.
 */
function ss_create_recurring_sponsorship( $parent_sponsorship, $args = array() ) {
	$packages          = $parent_sponsorship->get_items( 'package' );
	$args['status']    = 'approved'; // Setting Approved status since it's a recurring.
	$args['packages']  = array();
	$args['parent_id'] = $parent_sponsorship->get_id();
	$args['sponsor']   = $parent_sponsorship->get_data( 'sponsor', 0 );

	foreach ( $packages as $package ) {
		$args['packages'][ $package['item_id'] ] = $package['item_qty'];
	}

	$id = ss_create_sponsorship( $args );
	$sponsorship = $id ? ss_get_sponsorship( $id ) : false;

	if ( $sponsorship ) {
		if ( isset( $args['amount'] ) ) {
			$sponsorship->update_data( 'amount', $args['amount'] );
		}

		$user_id = $parent_sponsorship->get_data('_user_id', 0 );

		if ( $user_id ) {
			$sponsorship->update_data( '_user_id', $user_id );
		}

		ss_copy_recurring_sponsorship_data( $parent_sponsorship, $sponsorship );

		$parent_sponsorship->update_data( 'type', 'recurring' );
		$parent_sponsorship = ss_get_recurring_sponsorship( $parent_sponsorship );
		$parent_sponsorship->update_recurring_status( 'pending' ); // When we create a new recurring approved sponsorship, the parent is pending payment.

		do_action( 'ss_recurring_sponsorship_created', $sponsorship, $args, $parent_sponsorship );
	}
	return $sponsorship;
}

/**
 * Copy Recurring Sponsorship Data
 *
 * @param \Simple_Sponsorships\Sponsorship|integer $from_sponsorship Sponsorship object or ID.
 * @param \Simple_Sponsorships\Sponsorship|integer $to_sponsorship   Sponsorship object or ID.
 */
function ss_copy_recurring_sponsorship_data( $from_sponsorship, $to_sponsorship ) {
	if ( is_numeric( $from_sponsorship ) ) {
		$from_sponsorship = ss_get_sponsorship( $from_sponsorship );
	}

	if ( ! $from_sponsorship ) {
		return false;
	}

	if ( is_numeric( $to_sponsorship ) ) {
		$to_sponsorship = ss_get_sponsorship( $to_sponsorship );
	}

	if ( ! $to_sponsorship ) {
		return false;
	}

	$db    = $from_sponsorship->get_db_object();
	$metas = $db->get_all_meta( $from_sponsorship->get_id() );

	$exclude_meta_keys = apply_filters( 'ss_exclude_meta_recurring_sponsorship', array(
		'ss_paypal_subscriber',
		'_paypal_transaction_fee',
		'_transaction_id',
		'_paypal_status',
	));

	if ( $metas ) {
		foreach ( $metas as $meta_data ) {
			if ( in_array( $meta_data['meta_key'], $exclude_meta_keys, true ) ) {
				continue;
			}

			$to_sponsorship->update_data( $meta_data['meta_key'], $meta_data['meta_value'] );
		}
	}
}

/**
 * Check if the Sponsorship is a recurring one.
 *
 * @param integer|\Simple_Sponsorships\Sponsorship $sponsorship
 *
 * @return boolean
 */
function ss_is_recurring_sponsorship( $sponsorship ) {
	if ( is_numeric( $sponsorship ) ) {
		$sponsorship = ss_get_sponsorship( $sponsorship );
	}

	return 'recurring' === $sponsorship->get_data( 'type', 'onetime' );
}

/**
 * Get Recurring Sponsorship
 *
 * @param integer|\Simple_Sponsorships\Sponsorship $sponsorship
 *
 * @return \Simple_Sponsorships\Recurring_Payments\Recurring_Sponsorship
 */
function ss_get_recurring_sponsorship( $sponsorship ) {
	if ( is_numeric( $sponsorship ) ) {
		$sponsorship = ss_get_sponsorship( $sponsorship, false );
	}

	$recurring_sponsorship = new \Simple_Sponsorships\Recurring_Payments\Recurring_Sponsorship( $sponsorship->get_id(), false );
	$recurring_sponsorship->set_sponsorship( $sponsorship );
	return $recurring_sponsorship;
}