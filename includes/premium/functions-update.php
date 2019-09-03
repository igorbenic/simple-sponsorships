<?php

/**
 * Updating integrations so the premium/platinum ones are active from 1.3.0 as they were in previous.
 */
function ss_update_130_integrations_premium() {

	$active_integrations = ss_get_active_integrations();

	if ( ! in_array( 'stripe', $active_integrations ) ) {
		$active_integrations[] = 'stripe';
	}

	if ( ! in_array( 'package-slots', $active_integrations ) ) {
		$active_integrations[] = 'package-slots';
	}

	if ( ! in_array( 'post-paid-form', $active_integrations ) ) {
		$active_integrations[] = 'post-paid-form';
	}

	if ( \Simple_Sponsorships\ss_fs()->is_plan( 'platinum' ) ) {
		if ( ! in_array( 'package-features', $active_integrations ) ) {
			$active_integrations[] = 'package-features';
		}

		if ( ! in_array( 'package-timed-availability', $active_integrations ) ) {
			$active_integrations[] = 'package-timed-availability';
		}
	}

	ss_update_active_integrations( $active_integrations );
}