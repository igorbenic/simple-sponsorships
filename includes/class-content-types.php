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
			'menu_position'         => 5,
			'menu_icon'             => 'dashicons-awards',
			'show_in_admin_bar'     => true,
			'show_in_nav_menus'     => true,
			'can_export'            => true,
			'has_archive'           => true,
			'exclude_from_search'   => false,
			'publicly_queryable'    => true,
			'capability_type'       => 'page',
		);
		register_post_type( 'sponsors', $args );

	}
}