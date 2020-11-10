<?php

/**
 * Restricted Content shortcode
 */

if ( ! defined( 'ABSPATH' ) ) {
	return;
}

?>
<div class="ss-restrict-content">
	<p>
		<?php
		echo esc_html( sprintf(
			__( 'This content is restricted for sponsors who sponsor "%s"', 'simple-sponsorships-premium' ),
			$args['title']
		) );
		?>
	</p>
	<a href="<?php echo esc_url( $args['link'] ); ?>" class="button"><?php esc_html_e( 'Sponsor', 'simple-sponsorships-premium' ); ?></a>
</div>
