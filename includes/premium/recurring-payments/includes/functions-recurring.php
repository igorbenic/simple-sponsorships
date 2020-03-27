<?php
/**
 * Functions for Recurring Payments
 */

/**
 * Create a Recurring Sponsorship
 *
 * @param \Simple_Sponsorships\Sponsorship $parent_sponsorship
 *
 * @return \Simple_Sponsorships\Sponsorship|boolean False if sponsorship not created.
 */
function ss_create_recurring_sponsorship( $parent_sponsorship, $args = array() ) {
	$packages          = $parent_sponsorship->get_items( 'package' );
	$args              = array();
	$args['status']    = $parent_sponsorship->get_data('status' );
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

		do_action( 'ss_recurring_sponsorship_created', $sponsorship, $args );
	}
	return $sponsorship;
}