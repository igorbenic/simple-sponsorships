<?php
/**
 * Showing Sponsorships
 */

if ( ! $args['sponsor'] ) {
	?>
	<p><?php esc_html_e( 'You don\'t have any sponsor data', 'simple-sponsorships' ); ?></p>
	<?php
	return;
}

$sponsor = $args['sponsor'];
$sponsor_info = apply_filters( 'ss_account_sponsor_info_data', array(
    'name' => array(
        'title' => __( 'Name', 'simple-sponsorships' ),
        'value' => esc_html( $sponsor->get_data( 'post_title' ) ),
    ),
	'description' => array(
		'title' => __( 'Description', 'simple-sponsorships' ),
		'value' => wp_kses_post( $sponsor->get_data( 'post_content' ) ),
	),
	'website' => array(
		'title' => __( 'Website', 'simple-sponsorships' ),
		'value' => esc_html( $sponsor->get_data( '_website' ) ),
	),
), $sponsor );
?>

<div class="ss-sponsor-info" itemprop="sponsor" itemtype="http://schema.org/Organization">

    <?php
        $has_logo = false;
        if ( $sponsor->has_logo() ) {
            $has_logo = true;
            echo '<div class="ss-sponsor-logo">' . $sponsor->get_logo() . '</div>';
        }
    ?>
    <div class="ss-sponsor-content <?php echo $has_logo ? 'has-logo' : '' ?>">
        <?php
            foreach ( $sponsor_info as $sponsor_info_slug => $sponsor_info_data ) {
                $sponsor_info_data = wp_parse_args( $sponsor_info_data, array(
                    'title' => '',
                    'value' => '',
                ));
                if ( '' === $sponsor_info_data['value'] ) {
                    continue;
                }
                ?>
                <div class="ss-sponsor-info-data ss-sponsor-info-data-<?php echo esc_attr( $sponsor_info_slug ); ?>">
                    <?php
                        if ( $sponsor_info_data['title'] ) {
                    ?>
                    <div class="ss-sponsor-info-title">
			            <?php echo $sponsor_info_data['title']; ?>
                    </div>
                    <?php } ?>
                    <div class="ss-sponsor-info-value">
			            <?php
			            echo $sponsor_info_data['value'];
			            ?>
                    </div>
                </div>
                <?php
            }
        ?>
    </div>
</div>