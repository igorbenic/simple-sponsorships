<?php
/**
 * Showing Sponsored Content
 */

if ( ! defined( 'ABSPATH' ) ) {
	return;
}

echo '<p><em>' . esc_html__( 'Showing any content that you have sponsored', 'simple-sponsorships' ) . '</em></p>';

if ( ! isset( $args['sponsor'] ) || ! $args['sponsor'] ) {
	echo '<div class="ss-notice">' . esc_html__( 'No Sponsored content found', 'simple-sponsorships' ) . '</div>';
	return;
}

$sponsored_content = $args['sponsor']->get_sponsored_content();

if ( ! $sponsored_content ) {
	echo '<div class="ss-notice">' . esc_html__( 'No Sponsored content found', 'simple-sponsorships' ) . '</div>';
	return;
}

$columns = apply_filters( 'ss_sponsored_content_table_columns', array(
	'post_title'   => __( 'Title', 'simple-sponsorships' ),
	'post_excerpt' => __( 'Excerpt', 'simple-sponsorships' ),
	'link'         => __( 'Link', 'simple-sponsorships' )
))

?>
<table class="ss-table ss-table-sponsored-content">
	<thead>
	<tr>
		<?php
		foreach ( $columns as $column_id => $column_title ) {
			?>
			<th class="ss-table-column ss-table-column-<?php echo esc_attr( $column_id ); ?>"><?php echo $column_title; ?></th>
			<?php
		}
		?>
	</tr>
	</thead>
	<tbody>
	<?php
	foreach ( $sponsored_content as $content ) {
		?>
		<tr>
			<?php
			foreach ( $columns as $column_id => $column_title ) {
				?>
				<td class="ss-table-column ss-table-column-<?php echo esc_attr( $column_id ); ?>">
					<?php echo ss_get_sponsored_content_table_column_value( $content, $column_id ); ?>
				</td>
				<?php
			}
			?>
		</tr>
		<?php
	}
	?>
	</tbody>
</table>
