<?php
/**
 * Displaying Packages.
 */

if ( ! defined( 'ABSPATH' ) ) {
	return;
}

$id       = isset( $args['id'] ) ? absint( $args['id'] ) : 0;
$button   = isset( $args['button'] ) ? absint( $args['button'] ) : 0;
$ids      = isset( $args['packages'] ) ? $args['packages'] : '';
$heading  = isset( $args['heading'] ) ? $args['heading'] : 'h2';
$col      = isset( $args['col'] ) && absint( $args['col'] ) ? absint( $args['col'] ) : 0;
$packages = array();

if ( $ids && ! is_array( $ids ) ) {
    $ids = array_map( 'trim', explode( ',', $ids ) );

    if ( 1 == count( $ids ) ) {
        $id = $ids[0];
    }
}

if ( ! $id ) {
	$db_packages = ss_get_packages();
	if ( $db_packages ) {
		foreach ( $db_packages as $package ) {
		    if ( $ids && ! in_array( $package['ID'], $ids ) ) {
		        continue;
            }
			$packages[] = ss_get_package( $package['ID'] );
		}
		if ( $packages && $ids ) {
			$sorted = array();
			foreach ( $ids as $sort_key => $p_id ) {
				foreach ( $packages as $package ) {
					if ( absint( $package->get_id() ) === absint( $p_id ) ) {
						$sorted[ $sort_key ] = $package;
						break;
					}
				}
			}
			$packages = $sorted;
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
			$features['package'] = sprintf( __( 'All in %s', 'simple-sponsorships-premium' ), $feature_package->get_data('title' ) );
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
				echo '<div class="package-action"><a href="' . esc_attr( $link ) . '" class="ss-button" ' . $disabled . '>';
				echo __( 'Sponsor', 'simple-sponsorships-premium' );
				echo '</a></div>';
			}
			?>
		</div>
		<?php
	}
	?>
</div>
