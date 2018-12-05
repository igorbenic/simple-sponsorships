<?php

$notices = isset( $args['notices'] ) ? $args['notices'] : array();

if ( ! $notices ) { return; }

foreach ( $notices as $notice ) {
	$html = apply_filters( 'ss_notice_html', '<div class="ss-notice ss-notice-%s">%s</div>' );
	printf( $html, $notice['type'], $notice['notice'] );
}
?>
