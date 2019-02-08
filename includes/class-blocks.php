<?php
/**
 * The blocks class. Handles registering blocks.
 */

namespace Simple_Sponsorships;

/**
 * Class Blocks
 *
 * @package Simple_Sponsorships
 */
class Blocks {

	/**
	 * Blocks constructor.
	 */
	public function __construct() {
		add_action( 'enqueue_block_editor_assets', array( $this, 'enqueue' ) );
		add_filter( 'block_categories', array( $this, 'add_block_category' ), 10 );
		add_action( 'init', array( $this, 'register_blocks' ) );
	}

	/**
	 * Registering the dynamic blocks.
	 */
	public function register_blocks() {

		register_block_type( 'simple-sponsorships/sponsors', [
			'render_callback' => array( '\Simple_Sponsorships\Shortcodes', 'sponsors' ),
			'attributes'      => [
				'all' => [
					'default' => '0',
					'type'    => 'string'
				],
				'content'  => [
					'type'    => 'string',
					'default' => 'current',
				],
				'logo' => [
					'type' => 'string',
					'default' => '1',
				],
				'text' => [
					'type' => 'string',
					'default' => '1',
				],
				'package' => [
					'type' => 'string',
					'default' => '0'
				]
			]
		] );

	}

	/**
	 * Adding Simple Sponsorships category.
	 *
	 * @param array $categories Array of categories.
	 *
	 * @return array
	 */
	public function add_block_category( $categories ) {
		return array_merge(
			$categories,
			array(
				array(
					'slug' => 'simple-sponsorships',
					'title' => __( 'Simple Sponsorships', 'simple-sponsorships' ),
				),
			)
		);
	}

	/**
	 * Enqueue Editor Assets for Blocks.
	 */
	public function enqueue() {
		wp_enqueue_script(
			'ss-block-js',
			SS_PLUGIN_URL . '/assets/dist/js/gutenberg.js',
			array( 'wp-blocks', 'wp-i18n', 'wp-element', 'wp-components', 'wp-editor' )
		);

		// Styles.
		wp_enqueue_style(
			'ss-block-css',
			SS_PLUGIN_URL . '/assets/dist/css/gutenberg.css',
			array( 'wp-edit-blocks' )
		);
	}
}