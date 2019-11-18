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
		add_action( 'enqueue_block_editor_assets', array( $this, 'enqueue' ), 20 );
		add_filter( 'block_categories', array( $this, 'add_block_category' ), 10 );
		add_action( 'init', array( $this, 'register_blocks' ) );
	}
	
	/**
	 * Returning Sponsors for the Block.
	 *
	 * @param array $args Array of arguments.
	 * @return string
	 */
	public function get_sponsors( $args ) {
		$block = \Simple_Sponsorships\Shortcodes::sponsors( $args );

		if ( ! $block ) {
			$block = __( 'There were no sponsors found.', 'simple-sponsorships' );
		}

		return $block;
	}

	/**
	 * Return the Package HTML.
	 *
	 * @param array $args Array of arguments.
	 * @return string
	 */
	public function get_packages( $args ) {
		$block = \Simple_Sponsorships\Shortcodes::packages( $args );

		if ( ! $block ) {
			$block = __( 'Please select a package.', 'simple-sponsorships' );
		}

		return $block;
	}

	/**
	 * Return the Package HTML.
	 *
	 * @param array $args Array of arguments.
	 * @return string
	 */
	public function get_form_sponsor( $args ) {
		$block = \Simple_Sponsorships\Shortcodes::sponsor_form( $args );

		return $block;
	}

	/**
	 * Registering the dynamic blocks.
	 */
	public function register_blocks() {

		if ( ! function_exists( 'register_block_type' ) ) {
			return;
		}

		register_block_type( 'simple-sponsorships/sponsors', [
			'render_callback' => array( $this, 'get_sponsors' ),
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
				],
				'type' => [
					'type' => 'string',
					'default' => ''
				],
				'col' => [
					'type' => 'number',
					'default' => 2
				],
				'link_sponsor' => [
					'type' => 'string',
					'default' => '1',
				],
				'hide_title' => [
					'type' => 'string',
					'default' => '0',
				],
			]
		] );
		
		register_block_type( 'simple-sponsorships/packages', [
			'render_callback' => array( $this, 'get_packages' ),
			'attributes'      => [
				'id' => [
					'default' => '0',
					'type'    => 'string',
				],
				'button'  => [
					'type'    => 'string',
					'default' => '0',
				],
				'heading' => [
					'default' => 'h2',
					'type'    => 'string',
				],
				'col' => [
					'type' => 'number',
					'default' => 1
				],
			]
		] );

		register_block_type( 'simple-sponsorships/form-sponsor', [
			'render_callback' => array( $this, 'get_form_sponsor' ),
			'attributes'      => [
				'packages' => [
					'default' => '',
					'type'    => 'string'
				],
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

		if ( ! wp_script_is( 'ss-block-js', 'registered' ) ) {
			wp_register_script(
				'ss-block-js',
				SS_PLUGIN_URL . '/assets/dist/js/gutenberg.js',
				array( 'wp-blocks', 'wp-i18n', 'wp-element', 'wp-components', 'wp-editor', 'wp-compose' )
			);
		}

		wp_enqueue_script( 'ss-block-js' );
		wp_localize_script( 'ss-block-js', 'ss_blocks', array(
			'content_types' => \ss_get_content_types(),
			'nonce'         => wp_create_nonce( 'ss-admin-nonce' ),
			'ajax'          => admin_url( 'admin-ajax.php' ),
		));

		// Styles.
		wp_enqueue_style(
			'ss-block-css',
			SS_PLUGIN_URL . '/assets/dist/css/gutenberg.css',
			array( 'wp-edit-blocks' )
		);
	}
}