<?php
/**
 * Template for a single sponsor in the widget.
 */

if ( ! defined( 'ABSPATH' ) ) {
	return;
}

$sponsor = isset( $args['sponsor'] ) ? $args['sponsor'] : false;

if ( ! $sponsor ) {
	return;
}

$columns   = absint( $args['columns'] ) > 1 ? true : false;
$show_logo = $args['show_logo'] === '1' ? true : false;
$only_logo = $args['only_logo'] === '1' ? true : false;
$text      = isset( $args['text'] ) && $args['text'] === '1' ? true : false;
$has_logo  = $show_logo && has_post_thumbnail( $sponsor->get_id() );
$link      = $sponsor->get_link();
?>
<div class="ss-widget-sponsor <?php echo $columns ? 'ss-col-item' : ''; ?>">
	<?php

		if ( $has_logo ) {
			if ( $link ) {
				echo '<a href="' . $link . '">';
			}
			echo get_the_post_thumbnail( $sponsor->get_id() );
			if ( $link ) {
				echo '</a>';
			}
		}
		if ( $link ) {
			echo '<a target="_blank" href="' . $link . '">';
		}
		if ( ! $only_logo || ! $has_logo ) {
			echo $sponsor->get_data( 'post_title' );
		}
		if ( $text ) {
		    echo $sponsor->get_data( 'post_content' );
        }
		if ( $link ) {
			echo '</a>';
		}
	?>
</div>
