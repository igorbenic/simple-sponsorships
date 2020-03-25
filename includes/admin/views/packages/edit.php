<?php
/**
 * Edit Package
 */

use Simple_Sponsorships\Admin\Settings;
?>
<h1><?php esc_html_e( 'Edit Package', 'simple-sponsorships' ); ?>
	<a href="<?php echo admin_url( 'edit.php?post_type=sponsors&page=ss-packages' ); ?>" class="add-new-h2">
		<?php esc_html_e( 'Back to Packages', 'simple-sponsorships' ); ?>
	</a>
</h1>
<?php
if ( $errors ) {
	foreach ( $errors as $error ) {
		?>
		<div id="message" class="error notice notice-error is-dismissible">
			<p><?php echo esc_html( $error ); ?></p>
			<button type="button" class="notice-dismiss">
				<span class="screen-reader-text"><?php esc_html_e( 'Dismiss this error.', 'simple-sponsorships' ); ?></span>
			</button>
		</div>
		<?php
	}
}
?>
<form id="ss-edit-package" action="" method="POST">
	<?php do_action( 'ss_edit_package_form_top' ); ?>
	<table class="form-table">
		<tbody>
		<?php
		if ( $fields ) {
			foreach ( $fields as $field_slug => $field_args ) {
			    $default = isset( $field_args['default'] ) ? $field_args['default'] : '';
				$field_args['name'] = 'ss_packages[' . Settings::sanitize_key( $field_args['id'] ) . ']';
				$field_args['value'] = $package->get_data( $field_slug, $default );
				$row_classes         = isset( $field_args['row_class'] ) ? Settings::sanitize_html_class( $field_args['row_class'] ) : '';

				do_action( 'ss_edit_package_before_field_' . $field_slug, $fields );
				if ( 'hidden' === $field_args['type'] ) {
                    $row_classes .= ' hidden';
                }
				?>
                <tr id="<?php echo $field_args['id'] . '_row' ?>" class="ss-field-row <?php echo esc_attr( $row_classes ); ?>">
                <?php
					if ( $field_args['title'] ) {
						?>
						<th scope="row" valign="top">
							<label for="<?php echo esc_attr( $field_args['id'] ); ?>">
								<?php echo esc_html( $field_args['title'] ); ?>
							</label>
						</th>
					<?php } ?>
					<td <?php if ( ! $field_args['title'] ) { echo 'colspan="2"'; } ?>>
						<?php ss_render_settings_field( $field_args ); ?>
					</td>
				</tr>
				<?php
				do_action( 'ss_edit_package_after_field_' . $field_slug, $fields );
			}
		}
		?>
		</tbody>
	</table>
	<?php do_action( 'ss_edit_package_form_bottom' ); ?>
	<p class="submit">
		<input type="hidden" name="ss-action" value="edit_package"/>
		<input type="hidden" name="ss_packages[id]" value="<?php echo esc_attr( $package->get_id() ); ?>"/>
		<input type="submit" value="<?php esc_attr_e( 'Edit Package', 'simple-sponsorships' ); ?>" class="button-primary"/>
	</p>
</form>
