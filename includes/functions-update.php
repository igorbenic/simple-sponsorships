<?php

/**
 * Updating integrations so the free one are active from 1.3.0 as they were in previous.
 */
function ss_update_130_integrations() {

	$active_integrations = ss_get_active_integrations();
	if ( ! in_array( 'gravityforms', $active_integrations ) ) {
		$active_integrations[] = 'gravityforms';
		ss_update_active_integrations( $active_integrations );
	}
}