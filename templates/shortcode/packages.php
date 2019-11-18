<?php
/**
 * Displaying Packages.
 */

if ( ! defined( 'ABSPATH' ) ) {
	return;
}

$id       = isset( $args['id'] ) ? absint( $args['id'] ) : 0;
$button   = isset( $args['button'] ) ? absint( $args['button'] ) : 0;
$heading  = isset( $args['heading'] ) ? $args['heading'] : 'h2';
$cols     = isset( $args['col'] ) ? absint( $args['col'] ) : 1;
$classes  = array();
$packages = array();

if ( $cols > 1 ) {
    $classes[] = 'ss-col';
	$classes[] = 'ss-col-' . $cols;
}

if ( ! $id ) {
	$db_packages = ss_get_packages();
	if ( $db_packages ) {
		foreach ( $db_packages as $package ) {
			$packages[] = ss_get_package( $package['ID'] );
		}
	}
} else {
	$packages[] = ss_get_package( $id );
}

if ( ! $packages ) {
	return;
}

$sponsor_page = '';

if ( $button ) {
    $sponsor_page = ss_get_option( 'sponsor_page', 0 );
    if ( $sponsor_page ) {
        $sponsor_page = get_permalink( $sponsor_page );
    }
}

?>
<div class="simple-sponsorships ss-packages <?php echo implode( ' ', $classes ); ?>">
	<?php
	foreach ( $packages as $package ) {
		?>
		<div class="ss-package">
			<?php
			echo '<' . $heading . '>' . $package->get_title() . '</' . $heading . '>';
			$package_content = $package->get_description();
			if ( $package_content ) {
			    echo '<div class="ss-package-content">';
			    echo $package_content;
			    echo '</div>';
            }

            if ( $button && $sponsor_page && $package->is_available() ) {
                $link = add_query_arg( 'package', $package->get_data('id'), $sponsor_page );
                echo '<a href="' . esc_attr( $link ) . '" class="button ss-button">';
                echo sprintf( __( 'Sponsor %1$s (%2$s)', 'simple-sponsorships' ), $package->get_data( 'title' ), $package->get_price_html() );
                echo '</a>';
            }
			?>
		</div>
		<?php
	}
	?>
</div>
