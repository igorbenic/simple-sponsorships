<?php
/**
 * Template to show the shortcode.
 */


$all     = isset( $args['all'] ) && '1' === $args['all'] ? true : false;
$content = isset( $args['content'] ) ? $args['content'] : 'current';
$logo    = isset( $args['logo'] ) ? absint( $args['logo'] ) : 1;
$text    = isset( $args['text'] ) ? absint( $args['text'] ) : 1;
$package = isset( $args['package'] ) ? absint( $args['package'] ) : 0;

$ss_args = array(
    'ss_package' => $args['package']
);

if ( $all ) {
	$sponsors = ss_get_active_sponsors( $ss_args );
} else {
    $content_id = is_numeric( $content ) ? absint( $content ) : get_the_ID();
    $ss_args['ss_content'] = $content_id;
	$sponsors = ss_get_sponsors( $ss_args );
}

if ( ! $sponsors ) {
	return;
}

?>
<div class="ss-sponsors">
	<?php
		foreach ( $sponsors as $sponsor_object ) {
			$sponsor = new \Simple_Sponsorships\Sponsor( 0, false );
			$sponsor->populate_from_post( $sponsor_object );
			$has_logo  = $logo && has_post_thumbnail( $sponsor->get_id() );
			$link      = $sponsor->get_link();
			?>
			<div class="ss-sponsor">
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

				echo $sponsor->get_data( 'post_title' );

				if ( $link ) {
					echo '</a>';
				}
				if ( $text ) {
					$content = $sponsor->get_data( 'post_content' );
					if ( $content ) {
						echo '<div class="ss-sponsor-content">';
						echo do_shortcode( wpautop( $sponsor->get_data( 'post_content' ) ) );
						echo '</div>';
					}
				}
				?>
			</div>
			<?php
		}
	?>
</div>
