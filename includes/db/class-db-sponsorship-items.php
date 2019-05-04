<?php
/**
 * For managing items in database.
 */

namespace Simple_Sponsorships\DB;


class DB_Sponsorship_Items extends DB {

	/**
	 * DB Type.
	 *
	 * @var string
	 */
	protected $type = 'sponsorship-item';

	/**
	 * Table Name.
	 *
	 * @var string
	 */
	protected $table = 'ss_sponsorship_items';

	/**
	 * @var string
	 */
	protected $meta_table = 'ss_sponsorship_itemmeta';

	/**
	 * Get the level meta data.
	 *
	 * @param      $id
	 * @param      $key
	 * @param bool $single
	 *
	 * @return mixed
	 */
	public function get_meta( $id, $key, $single = true ) {
		return \get_metadata( 'sssponsorship_item', $id, $key, $single );
	}

	/**
	 * Add Meta
	 *
	 * @param $id
	 * @param $key
	 * @param $value
	 *
	 * @return false|int
	 */
	public function add_meta( $id, $key, $value ) {
		return \add_metadata( 'sssponsorship_item', $id, $key, $value );
	}

	/**
	 * Update Meta.
	 *
	 * @param        $id
	 * @param        $key
	 * @param string $value
	 * @param string $prev_value
	 *
	 * @return bool|int
	 */
	public function update_meta( $id, $key, $value = '', $prev_value = '' ) {
		return \update_metadata( 'sssponsorship_item', $id, $key, $value, $prev_value );
	}

	/**
	 * Delete Meta.
	 *
	 * @param        $id
	 * @param        $key
	 * @param string $value
	 * @param bool   $delete_all
	 *
	 * @return bool
	 */
	public function delete_meta( $id, $key, $value = '', $delete_all = false ) {
		return \delete_metadata( 'sssponsorship_item', $id, $key, $value, $delete_all );
	}

	/**
	 * Create the Item.
	 */
	public function create_item( $item ) {
		$table_columns = array(
			'ID',
			'item_type',
			'item_name',
			'sponsorship_id',
			'item_id',
			'item_qty',
			'item_amount',
		);

		if ( isset( $item['ID'] ) ) {
			return new \WP_Error( 'item-id', __( 'When creating an item, ID should not be passed.', 'simple-sponsorships' ) );
		}

		if ( ! isset( $item['sponsorship_id'] ) || ! $item['sponsorship_id'] ) {
			return new \WP_Error( 'sponsorship-id', __( 'There has to be a sponsorship ID.', 'simple-sponsorships' ) );
		}

		$meta = array();

		foreach ( $item as $item_key => $item_value ) {
			if ( in_array( $item_key, $table_columns, true ) ) {
				continue;
			}

			$meta[ $item_key ] = $item_value;
			unset( $item[ $item_key ] );
		}

		$id = $this->insert( $item );

		if ( false !== $id ) {
			foreach ( $meta as $meta_key => $meta_value ) {
				$this->add_meta( $id, $meta_key, $meta_value );
			}
		}
		do_action( 'ss_sponsorship_create_item', $id, $this, $item, $meta );
		return $id;
	}


	/**
	 * Create the Item.
	 */
	public function update_item( $item ) {
		$table_columns = array(
			'ID',
			'item_type',
			'item_name',
			'sponsorship_id',
			'item_id',
			'item_qty',
			'item_amount',
		);

		if ( ! isset( $item['ID'] ) || ! $item['ID'] ) {
			return new \WP_Error( 'item-id', __( 'When updating an item, ID should be present.', 'simple-sponsorships' ) );
		}

		if ( ! isset( $item['sponsorship_id'] ) || ! $item['sponsorship_id'] ) {
			return new \WP_Error( 'sponsorship-id', __( 'There has to be a sponsorship ID.', 'simple-sponsorships' ) );
		}

		$meta = array();

		foreach ( $item as $item_key => $item_value ) {
			if ( in_array( $item_key, $table_columns, true ) ) {
				continue;
			}

			$meta[ $item_key ] = $item_value;
			unset( $item[ $item_key ] );
		}

		$id = $item['ID'];
		unset( $item['ID'] );
		$update = $this->update( $id, $item );

		if ( false !== $update ) {
			foreach ( $meta as $meta_key => $meta_value ) {
				$this->update_meta( $id, $meta_key, $meta_value );
			}
		}
		do_action( 'ss_sponsorship_update_item', $id, $this, $item, $meta );
		return $id;
	}

	/**
	 * Delete Item.
	 * @param $item_id
	 */
	public function delete_item( $item_id ) {
		do_action( 'ss_sponsorship_item_delete', $item_id );
		$delete = $this->delete( array( 'ID' => $item_id ) );
		if ( $delete ) {
			$this->delete_all_meta( array( 'sssponsorship_item_id' => $item_id ) );
		}
	}

	/**
	 * Installing the DB.
	 *
	 * @return string
	 */
	public function get_schema() {
		$table_name = $this->get_table_name();
		$schema = "CREATE TABLE {$table_name} (
		ID bigint(20) NOT NULL AUTO_INCREMENT,
		item_type varchar(200) NOT NULL,
		item_name TEXT NOT NULL,
		item_amount tinytext NOT NULL,
		item_qty bigint(20) NOT NULL DEFAULT '1',
		item_id bigint(20) NOT NULL DEFAULT '0',
		sponsorship_id bigint(20) NOT NULL,
		PRIMARY KEY ID (ID)
		) CHARACTER SET utf8 COLLATE utf8_general_ci;";

		$meta_table_name = $this->get_meta_table_name();
		$schema .= "CREATE TABLE {$meta_table_name} (
		meta_id bigint(20) NOT NULL AUTO_INCREMENT,
		sssponsorship_item_id bigint(20) NOT NULL DEFAULT '0',
		meta_key varchar(255) DEFAULT NULL,
		meta_value longtext,
		PRIMARY KEY meta_id (meta_id),
		KEY sssponsorship_item_id (sssponsorship_item_id),
		KEY meta_key (meta_key)
		) CHARACTER SET utf8 COLLATE utf8_general_ci;";

		return $schema;
	}
}