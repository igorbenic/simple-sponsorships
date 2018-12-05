<?php

$sponsorship = $args['sponsorship'];

if ( $sponsorship->is_request() ) {
	?>
	<div class="ss-notice ss-notice-info">
		<p><?php esc_html_e( 'Your Sponsorship Request is waiting to on approval from the site owner.', 'simple-sponsorships' ); ?></p>
		<p><?php esc_html_e( 'You will receive an email upon the decision.', 'simple-sponsorships' ); ?></p>
	</div>
	<?php
} elseif ( $sponsorship->is_pending() ) {
	?>
	<div class="ss-notice ss-notice-info">
		<p><?php esc_html_e( 'This Sponsorship is awaiting your payment. Once paid it will be completed.', 'simple-sponsorships' ); ?></p>
	</div>
	<?php
}
?>
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