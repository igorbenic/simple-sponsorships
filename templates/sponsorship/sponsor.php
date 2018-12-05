<?php

$sponsorship = $args['sponsorship'];

?>
<h3><?php esc_html_e( 'Sponsor', 'simple-sponsorships' ); ?></h3>
<table class="ss-sponsorship-details">
	<tr>
		<th>
			<?php esc_html_e( 'Amount', 'simple-sponsorships' ); ?>
		</th>
		<td>
			<?php echo ss_currency_symbol() . $sponsorship->get_data( 'amount' ); ?>
		</td>
	</tr>
	<tr>
		<th>
			<?php esc_html_e( 'Sponsorship', 'simple-sponsorships' ); ?>
		</th>
		<td>
			<?php
			$package = $sponsorship->get_data( 'package' );
			if ( $package ) {
				$package = ss_get_package( $package );
				echo $package->get_data( 'title' );
			} else {
				echo __( 'No Package selected.', 'simple-sponsorships' );
			}
			?>
		</td>
	</tr>
	<tr>
		<th>
			<?php esc_html_e( 'Date', 'simple-sponsorships' ); ?>
		</th>
		<td>
			<?php echo date_i18n( get_option( 'date_format' ) . ' ' . get_option( 'time_format' ), strtotime( $sponsorship->get_data( 'date' ) ) ); ?>
		</td>
	</tr>
</table>