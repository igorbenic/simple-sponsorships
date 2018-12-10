<?php

$sponsorship = $args['sponsorship'];
$sponsor     = $sponsorship->get_sponsor_data();
$email       = $sponsor->get_data( '_email' );
$website     = $sponsor->get_data( '_website' );

?>
<h3><?php esc_html_e( 'Sponsor', 'simple-sponsorships' ); ?></h3>
<table class="ss-sponsorship-details">
	<tr>
		<th>
			<?php esc_html_e( 'Name', 'simple-sponsorships' ); ?>
		</th>
		<td>
			<?php echo $sponsor->get_data( 'name' ); ?>
		</td>
	</tr>
    <?php
    if ( $website ) {

	    ?>
        <tr>
            <th>
			    <?php esc_html_e( 'Website', 'simple-sponsorships' ); ?>
            </th>
            <td>
			    <?php echo sprintf( '<a href="%s" target="blank">%s</a>', $website, $website ); ?>
            </td>
        </tr>
	    <?php
    }

    if ( $email ) {
    ?>
	<tr>
		<th>
			<?php esc_html_e( 'Email', 'simple-sponsorships' ); ?>
		</th>
		<td>
			<?php echo $email; ?>
		</td>
	</tr>
    <?php } ?>
</table>