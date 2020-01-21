<?php
/**
 * Admin part for Levels
 */

namespace Simple_Sponsorships\Admin;
use Simple_Sponsorships\DB\DB_Sponsors;
use Simple_Sponsorships\DB\DB_Sponsorships;
use Simple_Sponsorships\Formatting;
use Simple_Sponsorships\Sponsorship;

/**
 * Class Levels
 *
 * @package Simple_Sponsorships\Admin
 */
class Sponsorships {

	/**
	 * Errors when processing packages.
	 *
	 * @var null|\WP_Error
	 */
	public $errors = null;

	/**
	 * Sponsorship Item
	 *
	 * @var null|Sponsorship
	 */
	public $sponsorship = null;

	/**
	 * Levels constructor.
	 */
	public function __construct() {
		add_action( 'ss_admin_page_ss_sponsorships', array( $this, 'page' ) );
		add_action( 'ss_new-sponsorship', array( $this, 'new_sponsorship' ) );
		add_action( 'ss_edit-sponsorship', array( $this, 'edit_sponsorship' ) );
		add_action( 'ss_settings_field_sponsorship_package_select', array( $this, 'package_field' ) );

		add_action( 'ss_sponsorship_sponsor_created', array( $this, 'save_meta_to_sponsor' ), 20, 2 );
		$this->errors = new \WP_Error();
	}

	/**
	 * Saving Meta to Sponsor
	 *
	 * @param integer $sponsor_id
	 * @param array $posted_data
	 */
	public function save_meta_to_sponsor( $sponsor_id, $posted_data ) {

		$db = new DB_Sponsors();

		if ( isset( $posted_data['_email'] ) ) {
			$db->add_meta( $sponsor_id, '_email', $posted_data['_email'] );
		}

		if ( isset( $posted_data['_company'] ) ) {
			$db->add_meta( $sponsor_id, '_company', $posted_data['_company'] );
		}

		if ( isset( $posted_data['_website'] ) ) {
			$db->add_meta( $sponsor_id, '_website', $posted_data['_website'] );
		}

		if ( isset( $posted_data['id'] ) && $posted_data['id'] ) {
		    $db_sponsorships = new DB_Sponsorships();
		    $user_id = $db_sponsorships->get_meta( $posted_data['id'], '_user_id', true );
		    if ( $user_id ) {
		        $db->add_meta( $sponsor_id, '_user_id', $user_id );
            }
        }
	}

	/**
	 * Creating a New Sponsorship.
	 */
	public function new_sponsorship() {

		if ( ! isset( $_POST['ss-action'] ) || 'add_sponsorship' !== $_POST['ss-action'] ) {
			return;
		}

		$posted_data    = isset( $_POST['ss_sponsorships'] ) ? $_POST['ss_sponsorships'] : array();
		$status         = isset( $posted_data['status'] ) ? sanitize_text_field( $posted_data['status'] ) : '';
		$amount         = isset( $posted_data['amount'] ) ? floatval( $posted_data['amount'] ) : 0;
		$package        = isset( $posted_data['package'] ) ? absint( $posted_data['package'] ) : 0;
		$packages       = isset( $posted_data['packages'] ) ? $posted_data['packages'] : array();
		$transaction_id = isset( $posted_data['transaction_id'] ) ? sanitize_text_field( $posted_data['transaction_id'] ) : '';

		if ( ! $status ) {
			$this->errors->add( 'no-status', __( 'No Status is required.', 'simple-sponsorships' ) );
		}

		if ( ! is_array( $packages ) && is_numeric( $packages ) ) {
			$packages = array( absint( $packages ) );
		}

		$sponsor = $this->create_sponsor_for_sponsorship( $posted_data );

		if ( $this->errors->get_error_messages() ) {
			return;
		}

		$sponsorship_id = ss_create_sponsorship(array(
			'status'         => $status,
			'amount'         => $amount,
			'gateway'        => 'manual',
			'transaction_id' => $transaction_id,
			'package'        => $package,
			'sponsor'        => $sponsor,
			'packages'       => $packages,
		));

		if ( ! $sponsorship_id ) {
			$this->errors->add( 'cant-create', __( 'Sponsorship could not be created.', 'simple-sponsorships' ) );
			return;
		}

		$this->add_meta( $sponsorship_id, $posted_data );

		do_action( 'ss_sponsorship_added', $sponsorship_id );

		if ( isset( $_POST['ss-redirect'] ) ) {
			wp_safe_redirect( $_POST['ss-redirect'] );
			exit;
		}
	}

	/**
	 * Creating a New Sponsorship.
	 */
	public function edit_sponsorship() {

		if ( ! isset( $_POST['ss-action'] ) || 'edit_sponsorship' !== $_POST['ss-action'] ) {
			return;
		}

		$posted_data    = isset( $_POST['ss_sponsorships'] ) ? $_POST['ss_sponsorships'] : array();
		$status         = isset( $posted_data['status'] ) ? sanitize_text_field( $posted_data['status'] ) : '';
		$amount         = isset( $posted_data['amount'] ) ? floatval( $posted_data['amount'] ) : 0;
		$package        = isset( $posted_data['package'] ) ? absint( $posted_data['package'] ) : 0;
		$gateway        = isset( $posted_data['gateway'] ) ? $posted_data['gateway'] : 'manual';
		$items          = isset( $posted_data['items'] ) ? $posted_data['items'] : array();
		$packages       = isset( $posted_data['packages'] ) ? $posted_data['packages'] : array();
		$transaction_id = isset( $posted_data['transaction_id'] ) ? sanitize_text_field( $posted_data['transaction_id'] ) : '';
		$id             = isset( $posted_data['id'] ) ? absint( $posted_data['id'] ) : 0;

		if ( ! $status ) {
			$this->errors->add( 'no-status', __( 'Status is required.', 'simple-sponsorships' ) );
		}

		if ( ! $id ) {
			$this->errors->add( 'no-id', __( 'This Sponsorship does not exist.', 'simple-sponsorships' ) );
		}

		if ( $this->errors->get_error_messages() ) {
			return;
		}

		$sponsor = $this->create_sponsor_for_sponsorship( $posted_data );

		$this->update_meta( $posted_data );

		if ( $this->errors->get_error_messages() ) {
			return;
		}

		$db = new DB_Sponsorships();
		$db_data = array(
			'status'         => $status,
			'amount'         => $amount,
			'gateway'        => $gateway,
			'transaction_id' => $transaction_id,
			'package'        => $package, // @todo Move to meta instead for multiple packages.
			'sponsor'        => $sponsor,
		);

		$ret = $db->update( $id, $db_data, array( '%s', '%s', '%s', '%s', '%d', '%d' ) );

		if ( $ret ) {
			$sponsorship = ss_get_sponsorship( $id, false );
			$sponsorship->update_items( $items );

			if ( $packages ) {
			    foreach ( $packages as $package_id ) {
			        $sponsorship->add_package( $package_id );
                }
            }

			do_action( 'ss_sponsorship_updated', $id, $posted_data );
		}
	}

	/**
	 * Update Meta
	 *
	 * @param array $posted_data
	 */
	protected function update_meta( $posted_data = array() ) {
		$db = new DB_Sponsorships();
		$id = absint( $posted_data['id'] );

		$sponsorship = new Sponsorship( $id, false );

		foreach ( $posted_data as $key => $value ) {
			if ( $sponsorship->is_table_column( $key ) ) {
				continue;
			}

			if ( 'items' === $key ) {
			    continue;
            }

			$db->update_meta( $id, $key, $value );
		}

		do_action( 'ss_sponsorship_update_meta', $posted_data, $sponsorship );
	}

	/**
	 * Update Meta
	 *
	 * @param integer $sponsorship_id
	 * @param array   $posted_data
	 */
	protected function add_meta( $sponsorship_id, $posted_data = array() ) {
		$db = new DB_Sponsorships();

		$sponsorship = new Sponsorship( $sponsorship_id, false );

		foreach ( $posted_data as $key => $value ) {
			if ( $sponsorship->is_table_column( $key ) ) {
				continue;
			}

			if ( 'items' === $key ) {
				continue;
			}

			$db->add_meta( $sponsorship_id, $key, $value );
		}

		do_action( 'ss_sponsorship_add_meta', $posted_data, $sponsorship );
	}

	/**
	 * @param $posted_data
	 *
	 * @return bool|int|\WP_Error
	 */
	public function create_sponsor_for_sponsorship( $posted_data ) {
		$sponsor = isset( $posted_data['sponsor'] ) ? $posted_data['sponsor'] : false;
		$status  = isset( $posted_data['status'] ) ? sanitize_text_field( $posted_data['status'] ) : '';

		if ( ! $sponsor ) {
			$this->errors->add( 'missing-sponsor', __( 'Sponsor data not posted.', 'simple-sponsorships' ) );
			return false;
		}

		if ( 'new' === $sponsor ) {
			$sponsor_name = isset( $posted_data['_sponsor_name'] ) ? sanitize_text_field( $posted_data['_sponsor_name'] ) : '';

			if ( ! $sponsor_name ) {
				$this->errors->add( 'no-sponsor', __( 'Select a Sponsor or insert the name to add a new one.', 'simple-sponsorships' ) );
				return false;
			}

			$sponsor_status = $status !== 'paid' ? 'ss-inactive' : 'publish';

			$sponsor_id = wp_insert_post( array(
				'post_status' => $sponsor_status,
				'post_type'   => 'sponsors',
				'post_title'  => $sponsor_name
			) );

			if ( is_wp_error( $sponsor_id ) ) {
				$this->errors->add( $sponsor_id->get_error_code(), $sponsor_id->get_error_message() );
			}

			$sponsor = $sponsor_id;
			do_action( 'ss_sponsorship_sponsor_created', $sponsor_id, $posted_data );
		}

		return $sponsor;
	}

	/**
	 * Admin Page
	 */
	public function page() {
		global $sponsorship;
		$action = isset( $_GET['ss-action'] ) ? sanitize_text_field( $_GET['ss-action'] ) : 'list';

		switch( $action ) {
			case 'edit-sponsorship':
				$errors      = $this->errors->get_error_messages();
				$fields      = $this->get_fields();
				$sponsorship = isset( $_GET['id'] ) ? ss_get_sponsorship( absint( $_GET['id'] ) ) : ss_get_sponsorship(0 );
				include_once 'views/sponsorships/edit.php';
				break;
			case 'new-sponsorship':
				$errors = $this->errors->get_error_messages();
				$fields = $this->get_fields();
				$sponsorship = ss_get_sponsorship(0 );
				include_once 'views/sponsorships/new.php';
				break;
			default:
				include_once 'sponsorships/class-sponsorships-table-list.php';

				$list = new Sponsorships_Table_List();

				include_once 'views/sponsorships/list.php';
				break;
		}

	}

	/**
	 * Return the level fields.
	 *
	 * @return array
	 */
	public function get_fields() {
		$all_packages = ss_get_packages();
		$packages     = array();

		if ( $all_packages ) {
			foreach ( $all_packages as $_package ) {
				$packages[ $_package['ID'] ] = $_package['title'];
			}
		}

		$all_sponsors = ss_get_sponsors();
		$sponsors     = array(
			'new' => __( 'Create a new sponsor', 'simple-sponsorships' ),
		);

		if ( $all_sponsors ) {
			foreach ( $all_sponsors as $_sponsor ) {
				$sponsors[ $_sponsor->ID ] = $_sponsor->post_title;
			}
		}

		$available_gateways = SS()->payment_gateways()->get_available_payment_gateways();
		$gateways           = array();
		foreach ( $available_gateways as $gateway_id => $gateway ) {
			$gateways[ $gateway_id ] = $gateway->get_method_title();
		}
		$fields = array(
			'status' => array(
				'id'      => 'status',
				'type'    => 'select',
				'title'   => __( 'Status', 'simple-sponsorships' ),
				'options' => ss_get_sponsorship_statuses()
			),
			'reject_reason' => array(
				'id'      => 'reject_reason',
				'type'    => 'textarea',
				'title'   => __( 'Reject Reason', 'simple-sponsorships' ),
				'row_class'   => array( 'ss-hidden',  'show_if_status_rejected' ),
			),
			'amount' => array(
				'id'          => 'amount',
				'type'        => 'number',
				'placeholder' => __( 'The Sponsorship Amount', 'simple-sponsorships' ),
				'title'       => sprintf( __( 'Amount (%s)', 'simple-sponsorships' ), ss_get_currency() ),
				'step'        => '0.01',
			),
			'packages' => array(
				'id'      => 'packages',
				'type'    => 'sponsorship_package_select',
				'title'   => '', //__( 'Package', 'simple-sponsorships' ),
				'options' => $packages,
			),
			'sponsor_heading' => array(
				'id'      => 'sponsor_heading',
				'type'    => 'section_start',
				'title'   => __( 'Sponsor Information', 'simple-sponsorships' ),
			),
			'sponsor' => array(
				'id'      => 'sponsor',
				'type'    => 'select',
				'title'   => __( 'Sponsor', 'simple-sponsorships' ),
				'options' => $sponsors
			),
			'_sponsor_name' => array(
				'id'      => '_sponsor_name',
				'type'    => 'text',
				'title'   => __( 'Sponsor Name', 'simple-sponsorships' ),
				'field_class'   => 'hide-if-sponsor',
			),
			'_email' => array(
				'id'      => '_email',
				'type'    => 'email',
				'title'   => __( 'Email', 'simple-sponsorships' ),
				'field_class'   => 'hide-if-sponsor',
			),
			'_website' => array(
				'id'      => '_website',
				'type'    => 'url',
				'title'   => __( 'Website', 'simple-sponsorships' ),
				'field_class'   => 'hide-if-sponsor',
			),
			'_company' => array(
				'id'      => '_company',
				'type'    => 'text',
				'title'   => __( 'Company', 'simple-sponsorships' ),
				'field_class'   => 'hide-if-sponsor',
			),
			'sponsor_footer' => array(
				'id'      => 'sponsor_heading',
				'type'    => 'section_end',
			),
			'payment_heading' => array(
				'id'      => 'payment_heading',
				'type'    => 'seaction_start',
				'title'   => __( 'Payment', 'simple-sponsorships' ),
			),
			'payment' => array(
				'id'      => 'payment',
				'type'    => 'select',
				'title'   => __( 'Gateway', 'simple-sponsorships' ),
				'options' => $gateways
			),
			'transaction_id' => array(
				'id'          => 'transaction_id',
				'type'        => 'text',
				'placeholder' => __( 'Transaction ID', 'simple-sponsorships' ),
				'title'       => __( 'Transaction ID', 'simple-sponsorships' ),
				'desc'        => __( 'The transaction ID, if any', 'simple-sponsorships' ),
			),
			'payment_footer' => array(
				'id'      => 'sponsor_heading',
				'type'    => 'section_end',
			),
		);

		return apply_filters( 'ss_get_sponsorship_fields', $fields );
	}

	/**
	 * Package Field
	 *
	 * @param $field
	 */
	public function package_field( $field ) {
		global $sponsorship;
		$items = $sponsorship->get_items( 'package' );

		?>
		<table class="ss-packages ss-items">
			<thead>
				<tr>
					<th class="item-title">
						<?php esc_html_e( 'Package', 'simple-sponsorships' ); ?>
					</th>
					<th class="item-qty">
						<?php esc_html_e( 'Qty.', 'simple-sponsorships' ); ?>
					</th>
					<th class="item-amount">
						<?php esc_html_e( 'Amount', 'simple-sponsorships' ); ?>
					</th>
                    <th class="item-actions"></th>
				</tr>
			</thead>
            <tfoot>
                <tr>
                    <td></td>
                    <td class="ss-items-total">
                        <?php esc_html_e( 'Total', 'simple-sponsorships' ); ?>
                    </td>
                    <td class="item-amount"><?php echo $sponsorship->get_formatted_amount(); ?></td>
                    <td class="item-actions">
                        <button type="button" aria-label="<?php esc_html_e( 'Calculate Totals', 'simple-sponsorships' ); ?>" class="button button-secondary ss-button-action" data-id="<?php echo esc_attr( $sponsorship->get_id() ); ?>" data-action="ss_sponsorship_calculate_totals" data-success="sponsorshipCalculateTotals">
		                    <?php esc_html_e( 'Calculate Totals', 'simple-sponsorships' ); ?>
                        </button>
                    </td>
                </tr>
            </tfoot>
			<tbody>
				<?php
				if ( $items ) {
					foreach ( $items as $index => $item ) {
						?>
						<tr>
							<td class="item-title">
                                <input type="hidden" name="ss_sponsorships[items][<?php echo $index ?>][ID]" value="<?php echo esc_attr( $item['ID'] ); ?>" />
                                <input type="hidden" name="ss_sponsorships[items][<?php echo $index ?>][item_id]" value="<?php echo esc_attr( $item['item_id'] ); ?>" />
                                <input type="hidden" name="ss_sponsorships[items][<?php echo $index ?>][item_type]" value="<?php echo esc_attr( $item['item_type'] ); ?>" />
                                <input type="hidden" name="ss_sponsorships[items][<?php echo $index ?>][item_name]" class="ss-field-editable" value="<?php echo esc_attr( $item['item_name'] ); ?>" />
								<div class="ss-item-field-text">
                                    <?php
									    echo $item['item_name'];
								    ?>
                                </div>
							</td>
							<td class="item-qty">
                                <input type="hidden" name="ss_sponsorships[items][<?php echo $index ?>][item_qty]" class="ss-field-editable" value="<?php echo esc_attr( $item['item_qty'] ); ?>" />
                                <div class="ss-item-field-text">
                                    <?php
                                    echo $item['item_qty'];
                                    ?>
                                </div>
							</td>
							<td class="item-amount">
                                <input type="hidden" name="ss_sponsorships[items][<?php echo $index ?>][item_amount]" class="ss-field-editable" value="<?php echo esc_attr( $item['item_amount'] ); ?>" />
                                <div class="ss-item-field-text">
                                    <?php
                                    echo Formatting::price( $item['item_amount'] );
                                    ?>
                                </div>
							</td>
                            <td class="item-actions">
                                <button type="button" aria-label="<?php esc_html_e( 'Delete the item', 'simple-sponsorships' ); ?>" class="button button-secondary ss-button-action dashicons dashicons-trash" data-success="sponsorshipRemovePackage" data-id="<?php echo esc_attr( $item['ID'] ); ?>">
                                    <?php esc_html_e( 'Delete', 'simple-sponsorships' ); ?>
                                </button>
                                <button type="button" aria-label="<?php esc_html_e( 'Edit item', 'simple-sponsorships' ); ?>" class="button button-secondary ss-button-action dashicons dashicons-edit" data-success="sponsorshipEditPackage">
		                            <?php esc_html_e( 'Edit', 'simple-sponsorships' ); ?>
                                </button>
                            </td>
						</tr>
						<?php
					}
				}
				?>
                <tr id="addPackage" <?php if ( $items ) { ?>class="ss-hidden"<?php } ?>>
                    <td colspan="4">
                        <?php
                            if ( $field['options'] ) {
                                esc_html_e( 'Add a package:', 'simple-sponsorships' );
                                ?>
                                <select name="ss_sponsorships[packages][]">
                                    <option value="0"><?php esc_html_e( 'Select a Package', 'simple-sponsorships' ); ?></option>
                                    <?php
                                    foreach ( $field['options'] as $package_id => $package_title ) {
                                        echo '<option value="' . esc_attr( $package_id ) . '">' . $package_title . '</option>';
                                    }
                                    ?>
                                </select>
                                <?php
                            } else {
                                echo '<p>' . __( 'Please create some packages first.', 'simple-sponsorships' ) . '</p>';
                            }
                            ?>
                    </td>
                </tr>
                <?php
				do_action( 'ss_sponsorships_admin_package_field_after_packages', $field, $sponsorship );
				?>
			</tbody>
		</table>
		<?php
	}

	/**
	 * Return Level Types.
	 *
	 * @return array
	 */
	public static function get_types() {
		return apply_filters( 'ss_package_types', array(
			'normal' => __( 'Normal', 'simple-sponsorships' ),
		));
	}
}

new Sponsorships();