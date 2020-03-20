<?php
/**
 * Showing Sponsorships
 */

if ( ! $args['sponsorships'] ) {
	?>
	<p><?php esc_html_e( 'You don\'t have any sponsorships', 'simple-sponsorships' ); ?></p>
	<?php
	return;
}

$columns = ss_get_sponsorships_table_columns();

?>
<table class="ss-table ss-table-sponsorships">
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
			foreach ( $args['sponsorships'] as $sponsorship ) {
			    ?>
                <tr>
                <?php
				foreach ( $columns as $column_id => $column_title ) {
					?>
					<td class="ss-table-column ss-table-column-<?php echo esc_attr( $column_id ); ?>">
						<?php echo ss_get_sponsorships_table_column_value( $sponsorship, $column_id ); ?>
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