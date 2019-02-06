<?php
/**
 * Displaying Packages.
 */

if ( ! defined( 'ABSPATH' ) ) {
	return;
}

$id = isset( $args['id'] ) ? absint( $args['id'] ) : 0;

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

?>
<div class="ss-packages">
	<?php
	foreach ( $packages as $package ) {
		?>
		<div class="ss-package">
			<?php
			echo $package->get_data('title');
			echo apply_filters( 'the_content', $package->get_data('description') );
			// price
			// button
			?>
		</div>
		<?php
	}
	?>
</div>
