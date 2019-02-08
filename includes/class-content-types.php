<?php
/**
 * This class is used to register post types, taxonomies and other content types.
 */

namespace Simple_Sponsorships;

/**
 * Class Content_Types
 *
 * @package Simple_Sponsorships
 */
class Content_Types {

	/**
	 * Content_Types constructor.
	 */
	public function __construct() {
		add_action( 'init', array( $this, 'register' ), 0 );
		add_action( 'init', array( $this, 'register_post_status' ), 10 );
		add_filter( 'gutenberg_can_edit_post_type', array( $this, 'disable_gutenberg_for_sponsors' ), 10, 2 );
		add_filter( 'use_block_editor_for_post_type', array( $this, 'disable_gutenberg_for_sponsors' ), 10, 2 );
	}

	/**
	 * @param $is_enabled
	 * @param $post_type
	 *
	 * @return bool
	 */
	public function disable_gutenberg_for_sponsors( $is_enabled, $post_type ) {
		if ( 'sponsors' === $post_type ) {
		 return false;
		}

		return $is_enabled;
	}
	/**
	 * Register Types.
	 */
	public function register() {
		$this->register_sponsors();
	}

	/**
	 * Register Sponsors CPT
	 */
	private function register_sponsors() {

		$labels = array(
			'name'                  => _x( 'Sponsors', 'Post Type General Name', 'simple-sponsorships' ),
			'singular_name'         => _x( 'Sponsor', 'Post Type Singular Name', 'simple-sponsorships' ),
			'menu_name'             => __( 'Sponsorships', 'simple-sponsorships' ),
			'name_admin_bar'        => __( 'Sponsor', 'simple-sponsorships' ),
			'archives'              => __( 'Item Archives', 'simple-sponsorships' ),
			'attributes'            => __( 'Item Attributes', 'simple-sponsorships' ),
			'parent_item_colon'     => __( 'Parent Item:', 'simple-sponsorships' ),
			'all_items'             => __( 'Sponsors', 'simple-sponsorships' ),
			'add_new_item'          => __( 'Add New Sponsor', 'simple-sponsorships' ),
			'add_new'               => __( 'Add Sponsor', 'simple-sponsorships' ),
			'new_item'              => __( 'New Sponsor', 'simple-sponsorships' ),
			'edit_item'             => __( 'Edit Sponsor', 'simple-sponsorships' ),
			'update_item'           => __( 'Update Sponsor', 'simple-sponsorships' ),
			'view_item'             => __( 'View Sponsor', 'simple-sponsorships' ),
			'view_items'            => __( 'View Sponsors', 'simple-sponsorships' ),
			'search_items'          => __( 'Search Sponsor', 'simple-sponsorships' ),
			'not_found'             => __( 'Not found', 'simple-sponsorships' ),
			'not_found_in_trash'    => __( 'Not found in Trash', 'simple-sponsorships' ),
			'featured_image'        => __( 'Logo', 'simple-sponsorships' ),
			'set_featured_image'    => __( 'Set logo', 'simple-sponsorships' ),
			'remove_featured_image' => __( 'Remove logo', 'simple-sponsorships' ),
			'use_featured_image'    => __( 'Use as logo', 'simple-sponsorships' ),
			'insert_into_item'      => __( 'Insert into item', 'simple-sponsorships' ),
			'uploaded_to_this_item' => __( 'Uploaded to this Sponsor', 'simple-sponsorships' ),
			'items_list'            => __( 'Sponsors list', 'simple-sponsorships' ),
			'items_list_navigation' => __( 'Sponsors list navigation', 'simple-sponsorships' ),
			'filter_items_list'     => __( 'Filter Sponsors list', 'simple-sponsorships' ),
		);
		$args = array(
			'label'                 => __( 'Sponsor', 'simple-sponsorships' ),
			'description'           => __( 'Sponsors', 'simple-sponsorships' ),
			'labels'                => $labels,
			'supports'              => array( 'title', 'editor', 'thumbnail' ),
			'hierarchical'          => false,
			'public'                => true,
			'show_ui'               => true,
			'show_in_menu'          => true,
			'menu_position'         => 30,
			'menu_icon'             => 'dashicons-awards',
			'show_in_admin_bar'     => true,
			'show_in_nav_menus'     => true,
			'can_export'            => true,
			'has_archive'           => true,
			'exclude_from_search'   => false,
			'publicly_queryable'    => true,
			'capability_type'       => 'page',
			'show_in_rest'              => true,
		);
		register_post_type( 'sponsors', $args );

	}

	/**
	 * Register our custom post statuses, used for sponsor status.
	 */
	public function register_post_status() {
		$sponsor_statuses = apply_filters(
			'ss_register_sponsor_post_statuses',
			array(
				'ss-inactive'    => array(
					'label'                     => _x( 'Inactive', 'Sponsor status', 'simple-sponsorships' ),
					'public'                    => false,
					'exclude_from_search'       => false,
					'show_in_admin_all_list'    => true,
					'show_in_admin_status_list' => true,
					/* translators: %s: number of orders */
					'label_count'               => _n_noop( 'Inactive <span class="count">(%s)</span>', 'Inactive <span class="count">(%s)</span>', 'simple-sponsorships' ),
				),
			)
		);
		foreach ( $sponsor_statuses as $status => $values ) {
			register_post_status( $status, $values );
		}
	}
}