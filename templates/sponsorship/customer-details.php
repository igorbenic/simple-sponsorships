<?php
/**
 * Customer Details of a Sponsorship.
 *
 * Used for invoicing.
 */

$form        = new \Simple_Sponsorships\Form_Payment();
$fields      = $form->get_fields();
$sponsorship = $args['sponsorship'];
?>

<table class="ss-sponsorship-details ss-sponsorship-customer-details">
	<?php
		foreach ( $fields as $field_id => $field ) {
			$value = $sponsorship->get_data( $field_id );
			if ( $value ) {
				?>
				<tr>
					<th>
						<?php echo esc_html( $field['title'] );?>
					</th>
					<td>
						<?php echo esc_html( $value ); ?>
					</td>
				</tr>
				<?php
			}
		}
	?>
</table>