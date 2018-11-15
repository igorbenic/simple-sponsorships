<?php
/**
 * Admin part for Levels
 */

namespace Simple_Sponsorships\Admin;
use Simple_Sponsorships\DB\DB_Levels;

/**
 * Class Levels
 *
 * @package Simple_Sponsorships\Admin
 */
class Levels {

	/**
	 * Errors when processing levels.
	 *
	 * @var null|\WP_Error
	 */
	public $errors = null;

	/**
	 * Levels constructor.
	 */
	public function __construct() {
		add_action( 'ss_admin_page_ss_levels', array( $this, 'page' ) );
		add_action( 'ss_new-level', array( $this, 'new_level' ) );

		$this->errors = new \WP_Error();
	}

	/**
	 * Creating a New Level.
	 */
	public function new_level() {

		if ( ! isset( $_POST['ss-action'] ) || 'add_level' !== $_POST['ss-action'] ) {
			return;
		}

		$posted_data = isset( $_POST['ss_levels'] ) ? $_POST['ss_levels'] : array();
		$title       = isset( $posted_data['title'] ) ? sanitize_text_field( $posted_data['title'] ) : '';
		$description = isset( $posted_data['description'] ) ? sanitize_text_field( $posted_data['title'] ) : '';
		$type        = isset( $posted_data['type'] ) ? sanitize_text_field( $posted_data['type'] ) : 'normal';
		$price       = isset( $posted_data['price'] ) ? floatval( $posted_data['price'] ) : 0;

		if ( ! $title ) {
			$this->errors->add( 'no-name', __( 'Level Name is required.', 'simple-sponsorships' ) );
		}

		if ( $this->errors->get_error_messages() ) {
			return;
		}

		$db = new DB_Levels();
		$db_data = array(
			'title' => $title,
			'description' => $description,
			'type' => $type,
			'price' => $price
		);

		$level_id = $db->insert( $db_data, array( '%s', '%s', '%s', '%d' ) );

		do_action( 'ss_level_added', $level_id );

		if ( isset( $_POST['ss-redirect'] ) ) {
			wp_safe_redirect( $_POST['ss-redirect'] );
			exit;
		}
	}

	/**
	 * Levels Admin Page
	 */
	public function page() {
		$action = isset( $_GET['ss-action'] ) ? $_GET['ss-action'] : 'list';

		switch( $action ) {
			case 'new-level':
				$errors = $this->errors->get_error_messages();
				$fields = $this->get_level_fields();
				include_once 'views/levels/new.php';
				break;
			default:
				include_once 'levels/class-levels-table-list.php';

				$list = new Levels_Table_List();

				include_once 'views/levels/list.php';
				break;
		}

	}

	/**
	 * Return the level fields.
	 *
	 * @return array
	 */
	public function get_level_fields() {

		$fields = array(
			'title' => array(
				'id' => 'title',
				'type' => 'text',
				'placeholder' => __( 'Enter the Level Name', 'simple-sponsorships' ),
				'title' => __( 'Title', 'simple-sponsorships' ),
			),
			'description' => array(
				'id' => 'description',
				'type' => 'text',
				'placeholder' => __( 'Enter the Level Description', 'simple-sponsorships' ),
				'title' => __( 'Description', 'simple-sponsorships' ),
				'field_class' => 'widefat',
			),
			'price' => array(
				'id' => 'price',
				'type' => 'number',
				'title' => __( 'Price', 'simple-sponsorships' ),
				'field_class' => 'widefat',
			),
			'type' => array(
				'id' => 'type',
				'type' => 'select',
				'title' => __( 'Type', 'simple-sponsorships' ),
				'field_class' => 'widefat',
				'options' => self::get_types()
			)
		);

		return apply_filters( 'ss_get_level_fields', $fields );
	}

	/**
	 * Return Level Types.
	 *
	 * @return array
	 */
	public static function get_types() {
		return apply_filters( 'ss_level_types', array(
			'normal' => __( 'Normal', 'simple-sponsorships' ),
		));
	}
}

new Levels();