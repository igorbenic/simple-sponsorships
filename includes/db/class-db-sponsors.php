<?php
/**
 * Created by PhpStorm.
 * User: igor
 * Date: 11/11/18
 * Time: 19:10
 */

namespace Simple_Sponsorships\DB;


class DB_Sponsors extends DB {

	/**
	 * DB Type.
	 *
	 * @var string
	 */
	protected $type = 'sponsor';

	/**
	 * Table Name.
	 *
	 * @var string
	 */
	protected $table = 'posts';

	/**
	 * @var string
	 */
	protected $meta_table = 'postmeta';

	/**
	 * Add filters to the WP_Query to join Sponsorship table and content.
	 */
	public function filter_sponsors_query() {
		add_filter( 'posts_where', array( $this, 'filter_sponsors_where' ), 20, 2 );
		add_filter( 'posts_join', array( $this, 'filter_sponsors_join' ), 20, 2 );
		add_filter( 'posts_distinct', array( $this, 'filter_sponsors_distinct' ), 20, 2 );
	}

	/**
	 * Remove filters to the WP_Query to join Sponsorship table and content.
	 */
	public function unfilter_sponsors_query() {
		remove_filter( 'posts_where', array( $this, 'filter_sponsors_where' ), 20 );
		remove_filter( 'posts_join', array( $this, 'filter_sponsors_join' ), 20 );
		remove_filter( 'posts_distinct', array( $this, 'filter_sponsors_distinct' ), 20 );
	}

	/**
	 * Filtering WP_Query to DISTINCT the sponsors.
	 *
	 * @param string $distinct
	 * @param \WP_Query $wp_query
	 */
	public function filter_sponsors_distinct( $distinct, $wp_query ) {
		return 'DISTINCT';
	}

	/**
	 * @param $where
	 * @param $wp_query
	 */
	public function filter_sponsors_where( $where, $wp_query ) {
		global $wpdb;
		if ( isset( $wp_query->query['ss_package'] ) && absint( $wp_query->query['ss_package'] ) ) {
			$where .= $wpdb->prepare( " AND ss_items.item_type='package' AND ss_items.item_id=%d", absint( $wp_query->query['ss_package'] ) );
		}

		if ( isset( $wp_query->query['ss_content'] ) && absint( $wp_query->query['ss_content'] ) ) {
			$where .= $wpdb->prepare( " AND ss_post_meta.post_id=%d AND ss_post_meta.meta_key='_ss_sponsor'", absint( $wp_query->query['ss_content'] ) );
		}
		return $where;
	}

	/**
	 * @param $where
	 * @param $wp_query
	 */
	public function filter_sponsors_join( $join, $wp_query ) {
		global $wpdb;
		if ( isset( $wp_query->query['ss_package'] ) && absint( $wp_query->query['ss_package'] ) ) {
			$join .= " INNER JOIN $wpdb->sssponsorships as ss_sponsorships on ss_sponsorships.sponsor = $wpdb->posts.ID";
			$join .= " INNER JOIN $wpdb->sssponsorship_items as ss_items on ss_items.sponsorship_id = ss_sponsorships.ID";
		}

		if ( isset( $wp_query->query['ss_content'] ) && absint( $wp_query->query['ss_content'] ) ) {
			$join .= " INNER JOIN $wpdb->postmeta as ss_post_meta on ss_post_meta.meta_value = $wpdb->posts.ID";
		}
		return $join;
	}

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
		return \get_post_meta( $id, $key, $single );
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
		return \add_post_meta( $id, $key, $value );
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
		return \update_post_meta( $id, $key, $value, $prev_value );
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
		return \delete_post_meta( $id, $key, $value );
	}

	/**
	 * Return all sponsors that are sponsoring a CPT.
	 *
	 * @param $post_id
	 * @return array|null
	 */
	public function get_from_post( $post_id ) {
		global $wpdb;

		$results = $wpdb->get_results( $wpdb->prepare( "SELECT * FROM $wpdb->posts as posts INNER JOIN $wpdb->postmeta as meta on meta.meta_value = posts.ID WHERE meta.post_id=%d AND meta.meta_key='_ss_sponsor'", $post_id ) );
		return $results;
	}

	/**
	 * Return all sponsors from a package.
	 *
	 * @param $package_id
	 */
	public function get_from_package( $package_id ) {
		global $wpdb;

		$results = $wpdb->get_results( $wpdb->prepare( "SELECT * FROM $wpdb->posts as posts INNER JOIN $wpdb->sssponsorships as sponsorships on sponsorships.sponsor = posts.ID WHERE sponsorships.package=%d", $package_id ) );
		return $results;
	}

	/**
	 * Get all content sponsored by a Sponsor
	 *
	 * @param $sponsor_id
	 */
	public function get_sponsored_content( $sponsor_id ) {
		return get_posts(array(
			'meta_query' => array(
				array(
					'key'   => '_ss_sponsor',
					'value' => $sponsor_id,
				),
			),
		));
	}
}