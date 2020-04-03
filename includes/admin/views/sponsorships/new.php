<?php
/**
 * New level screen
 */

use Simple_Sponsorships\Admin\Settings;
?>
<h1><?php esc_html_e( 'New Sponsorship', 'simple-sponsorships' ); ?>
	<a href="<?php echo admin_url( 'edit.php?post_type=sponsors&page=ss-sponsorships' ); ?>" class="add-new-h2">
		<?php esc_html_e( 'Back to Sponsorships', 'simple-sponsorships' ); ?>
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
<form id="ss-add-sponsorship" class="ss-view-sponsorship" action="" method="POST">
	<?php do_action( 'ss_add_sponsorship_form_top', false ); ?>
	<table class="form-table">
		<tbody>
		<?php
		if ( $fields ) {
			foreach ( $fields as $field_slug => $field_args ) {
			    $field_args['name']  = 'ss_sponsorships[' . Settings::sanitize_key( $field_args['id'] ) . ']';
				$row_classes         = isset( $field_args['row_class'] ) ? Settings::sanitize_html_class( $field_args['row_class'] ) : '';
				do_action( 'ss_add_sponsorship_before_field_' . $field_slug, $fields );
				?>
                <tr id="<?php echo $field_args['id'] . '_row' ?>" class="ss-field-row <?php echo esc_attr( $row_classes ); ?>">
					<?php
					if ( 'section_start' === $field_args['type'] ) {
					?>
                    <td class="ss-section" colspan="2">
						<?php
						if ( $field_args['title'] ) {
							echo '<h2>' . $field_args['title'] . '</h2>';
						}
						?>
                        <table>
							<?php }

							if ( 'section_end' !== $field_args['type'] && 'section_start' !== $field_args['type'] ) {

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
							<?php }

							if ( 'section_end' === $field_args['type'] ) {
							?>
                        </table>
                    </td>
				<?php } ?>
                </tr>
				<?php
				do_action( 'ss_add_sponsorship_after_field_' . $field_slug, $fields );
			}
		}
		?>
		</tbody>
	</table>
	<?php do_action( 'ss_add_sponsorship_form_bottom', false ); ?>
	<p class="submit">
		<input type="hidden" name="ss-action" value="add_sponsorship"/>
		<input type="hidden" name="ss-redirect" value="<?php echo esc_url( admin_url( 'edit.php?post_type=sponsors&page=ss-sponsorships' ) ); ?>"/>
		<input type="submit" value="<?php esc_attr_e( 'Create Sponsorship', 'simple-sponsorships' ); ?>" class="button-primary"/>
	</p>
</form>
