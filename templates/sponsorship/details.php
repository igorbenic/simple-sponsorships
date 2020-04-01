<?php

$sponsorship = $args['sponsorship'];

if ( $sponsorship->is_request() ) {
	?>
	<div class="ss-notice ss-notice-info">
		<p><?php esc_html_e( 'Your Sponsorship Request is waiting to on approval from the site owner.', 'simple-sponsorships' ); ?></p>
		<p><?php esc_html_e( 'You will receive an email upon the decision.', 'simple-sponsorships' ); ?></p>
	</div>
	<?php
} elseif ( $sponsorship->is_approved() || $sponsorship->is_on_hold() ) {
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
			<?php esc_html_e( 'Status', 'simple-sponsorships' ); ?>
        </th>
        <td>
			<?php
			ss_the_sponsorship_status( $sponsorship->get_data( 'status' ) );
            ?>
        </td>
    </tr>
	<tr>
		<th>
			<?php esc_html_e( 'Amount', 'simple-sponsorships' ); ?>
		</th>
		<td>
			<?php echo $sponsorship->get_formatted_amount(); ?>
		</td>
	</tr>
	<tr>
		<th>
			<?php esc_html_e( 'Sponsorship', 'simple-sponsorships' ); ?>
		</th>
		<td>
			<?php
                $packages = $sponsorship->get_items( 'package' );
				if ( $packages ) {
				    $titles = array();
				    foreach ( $packages as $package ) {
					    $titles[] = $package['item_name'] . ( floatval( $package['item_qty'] ) > 1 ? ' (' . $package['item_qty'] . ')' : '' );
                    }
                    echo implode( '<br/>', $titles );
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
    <?php
        $parent_id = $sponsorship->get_data('parent_id');
        if ( $parent_id && absint( $parent_id ) > 0 ) {
            $parent_sponsorship = ss_get_sponsorship( $parent_id );
            ?>
            <tr>
                <th>
			        <?php esc_html_e( 'Parent Sponsorship', 'simple-sponsorships' ); ?>
                </th>
                <td>
			        <a href="<?php echo esc_url( $parent_sponsorship->get_view_link() ) ?>"><?php echo sprintf( __( 'Sponsorship #%d', 'simple-sponsorship' ), $parent_sponsorship->get_id() ); ?></a>
                </td>
            </tr>
            <?php
        }
    ?>
</table>