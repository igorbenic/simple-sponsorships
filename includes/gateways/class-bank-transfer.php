<?php
/**
 * Bank Information
 */

namespace Simple_Sponsorships\Gateways;

/**
 * Class Stripe
 *
 * @package Simple_Sponsorships\Gateways
 */
class Bank_Transfer extends Payment_Gateway {

	/**
	 * True if the gateway shows fields on the checkout.
	 *
	 * @var bool
	 */
	public $has_fields = true;

	/**
	 * PayPal constructor.
	 */
	public function __construct() {
		$this->id           = 'bank_transfer';
		$this->method_title = __( 'Direct bank transfer', 'simple-sponsorships-premium' );
		$this->title        = __( 'Direct bank transfer', 'simple-sponsorships-premium' );
		$this->has_fields   = true;

		$this->get_settings();

		$this->title = isset( $this->settings['bank_transfer_title'] ) ? $this->settings['bank_transfer_title'] : $this->title;

		add_action( 'ss_settings_field_bank_transfer_account', array( $this, 'account_information_field' ) );
		parent::__construct();
	}

	/**
	 * Fields for PayPal.
	 *
	 * @return array
	 */
	public function get_fields() {
		return array(
			'bank_transfer_title' => array(
				'id'          => 'bank_transfer_title',
				'label'       => __( 'Title', 'simple-sponsorships' ),
				'default'     => __( 'Direct bank transfer', 'simple-sponsorships-premium' ),
				'desc'        => __( 'Title that will be shown in the payment form.', 'simple-sponsorships' ),
				'type'        => 'text',
			),
			'bank_transfer_instructions' => array(
				'id'          => 'bank_transfer_instructions',
				'label'       => __( 'Instructions', 'simple-sponsorships' ),
				'default'     => __( 'Once you complete the billing form here, make the payment to the following bank account. Once we get the payment information, we will mark it as paid.', 'simple-sponsorships-premium' ),
				'type'        => 'textarea',
				'desc'        => __( 'Add instructions for your potential sponsors to know what to do.', 'simple-sponsorships' ),
			),
			'bank_transfer_account' => array(
				'id'          => 'bank_transfer_account',
				'label'       => __( 'Account Information', 'simple-sponsorships' ),
				'default'     => array(),
				'type'        => 'bank_transfer_account',
			),
		);
	}

	public function payment_fields() {
		parent::payment_fields();

		$instructions = ss_get_option( 'bank_transfer_instructions' );
		$account      = ss_get_option( 'bank_transfer_account', array() );

		if ( $instructions ) {
			echo '<p>' . esc_html( $instructions ) . '</p>';
		}

		if ( $account ) {
			$account = wp_parse_args( $account, array(
				'account_name' => '',
				'account_number' => '',
				'bank_name' => '',
				'sort_code' => '',
				'iban' => '',
				'bic' => '',
			));
			?>
			<table class="widefat wc_input_table sortable" cellspacing="0">
				<tbody class="accounts">
					<?php
					if ( $account['account_name'] ) {
						?>
						<tr>
							<th><?php esc_html_e( 'Account name', 'woocommerce' ); ?></th>
							<td><?php echo esc_html( wp_unslash( $account['account_name'] ) ); ?></td>
						</tr>
						<?php
					}

					if ( $account['account_number'] ) {
						?>
						<tr>
							<th><?php esc_html_e( 'Account number', 'woocommerce' ); ?></th>
							<td><?php echo esc_html( $account['account_number'] ); ?></td>
						</tr>
						<?php
					}

					if ( $account['bank_name'] ) {
						?>
						<tr>
							<th><?php esc_html_e( 'Bank name', 'woocommerce' ); ?></th>
							<td><?php echo esc_html( wp_unslash( $account['bank_name'] ) ); ?></td>
						</tr>
						<?php
					}

					if ( $account['sort_code'] ) {
						?>
						<tr>
							<th><?php esc_html_e( 'Sort code', 'woocommerce' ); ?></th>
							<td><?php echo esc_html( $account['sort_code'] ); ?></td>
						</tr>
						<?php
					}

					if ( $account['iban'] ) {
						?>
						<tr>
							<th><?php esc_html_e( 'IBAN', 'woocommerce' ); ?></th>
							<td><?php echo esc_html( $account['iban'] ); ?></td>
						</tr>
						<?php
					}

					if ( $account['bic'] ) {
						?>
						<tr>
							<th><?php esc_html_e( 'BIC / Swift', 'woocommerce' ); ?></th>
							<td><?php echo esc_html( $account['bic'] ); ?></td>
						</tr>
						<?php
					}
					?>
				</tbody>
			</table>
			<?php
		}
	}

	/**
	 * Process Payment.
	 *
	 * Process the payment. Override this in your gateway. When implemented, this should.
	 * return the success and redirect in an array. e.g:
	 *
	 *        return array(
	 *            'result'   => 'success',
	 *            'redirect' => $this->get_return_url( $sponsorship )
	 *        );
	 *
	 * @param \Simple_Sponsorships\Sponsorship $sponsorship Sponsorship Object.
	 * @return array
	 */
	public function process_payment( $sponsorship ) {

		if ( $sponsorship->get_data('amount') > 0 ) {
			// Mark as on-hold (we're awaiting the payment).
			$sponsorship->set_status( apply_filters( 'ss_bacs_process_payment_sponsorship_status', 'on-hold', $sponsorship ) );
		} else {
			$this->complete( $sponsorship );
		}

		return array(
			'result'   => 'success',
			'redirect' => '',
		);
	}

	/**
	 * Account Fields
	 *
	 * @param $field
	 */
	public function account_information_field( $field ) {

		$account = $field['value'];
		$account = wp_parse_args( $account, array(
			'account_name' => '',
			'account_number' => '',
			'bank_name' => '',
			'sort_code' => '',
			'iban' => '',
			'bic' => '',
		));
		?>
		<table class="widefat wc_input_table sortable" cellspacing="0">
			<thead>
			<tr>
				<th><?php esc_html_e( 'Account name', 'woocommerce' ); ?></th>
				<th><?php esc_html_e( 'Account number', 'woocommerce' ); ?></th>
				<th><?php esc_html_e( 'Bank name', 'woocommerce' ); ?></th>
				<th><?php esc_html_e( 'Sort code', 'woocommerce' ); ?></th>
				<th><?php esc_html_e( 'IBAN', 'woocommerce' ); ?></th>
				<th><?php esc_html_e( 'BIC / Swift', 'woocommerce' ); ?></th>
			</tr>
			</thead>
			<tbody class="accounts">
			<?php
			echo '<tr class="account">
					<td><input type="text" value="' . esc_attr( wp_unslash( $account['account_name'] ) ) . '" name="' . esc_attr( $field['id'] ) . '[account_name]" /></td>
					<td><input type="text" value="' . esc_attr( $account['account_number'] ) . '" name="' . esc_attr( $field['id'] ) . '[account_number]" /></td>
					<td><input type="text" value="' . esc_attr( wp_unslash( $account['bank_name'] ) ) . '" name="' . esc_attr( $field['id'] ) . '[bank_name]" /></td>
					<td><input type="text" value="' . esc_attr( $account['sort_code'] ) . '" name="' . esc_attr( $field['id'] ) . '[sort_code]" /></td>
					<td><input type="text" value="' . esc_attr( $account['iban'] ) . '" name="' . esc_attr( $field['id'] ) . '[iban]" /></td>
					<td><input type="text" value="' . esc_attr( $account['bic'] ) . '" name="' . esc_attr( $field['id'] ) . '[bic]" /></td>
				</tr>';

			?>
			</tbody>
		</table>
		<?php
	}
}