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
$col      = isset( $args['col'] ) && absint( $args['col'] ) ? absint( $args['col'] ) : 0;
$packages = array();

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

$col_class = '';

if ( $col ) {
    $col_class = 'ss-col ss-col-' . $col;
}

?>
<div class="simple-sponsorships ss-packages-pricing-table <?php echo esc_attr( $col_class ); ?>">
	<?php
	foreach ( $packages as $package ) {
		$features = $package->get_data('features', array() );
		if ( isset( $features['package'] ) && absint( $features['package'] ) ) {
			$feature_package = ss_get_package( $features['package'], false );
			$features['package'] = sprintf( __( 'All in %s', 'simple-sponsorships' ), $feature_package->get_data('title' ) );
		} else {
			unset( $features['package'] );
		}
		?>
		<div class="ss-package ss-package-table ss-col-item">
			<?php
			echo '<' . $heading . '>' . $package->get_title() . '</' . $heading . '>';
            echo '<div class="package-price">' . $package->get_price_formatted() . '</div>';
			if ( $features ) {
				echo '<ul class="package-features">';
				if ( isset( $features['package'] ) ) {
					echo '<li>' . $features['package'] . '</li>';
					unset( $features['package'] );
				}
				if ( $features ) {
					foreach ( $features as $feature ) {
						echo '<li>' . $feature . '</li>';
					}
				}
				echo '</ul>';
			}

			if ( $button && $sponsor_page ) {
				$disabled = $package->is_available() ? '' : 'disabled=disabled';
				$link = add_query_arg( 'package', $package->get_data('id'), $sponsor_page );
				echo '<div class="package-action"><a href="' . esc_attr( $link ) . '" class="button ss-button" ' . $disabled . '>';
				echo __( 'Sponsor', 'simple-sponsorships' );
				echo '</a></div>';
			}
			?>
		</div>
		<?php
	}
	?>
</div>
