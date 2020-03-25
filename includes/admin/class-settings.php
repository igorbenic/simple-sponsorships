<?php
/**
 * Settings for Simple Sponsorships.
 */

namespace Simple_Sponsorships\Admin;

/**
 * Class Settings
 *
 * @package Simple_Sponsorships\Admin
 */
class Settings {

	/**
	 * WP Pages. Used to cache the data.
	 *
	 * @var null
	 */
	protected $pages = null;

	/**
	 * Image Sizes.
	 *
	 * @var null
	 */
	protected $image_sizes = null;

	/**
	 * Settings constructor.
	 */
	public function __construct() {
		add_action( 'ss_admin_page_ss_settings', array( $this, 'page' ) );
		add_action( 'admin_init', array( $this, 'register_settings' ) );
	}

	/**
	 * Page View.
	 */
	public function page() {
		$settings_tabs = $this->get_settings_tabs();
		$settings_tabs = empty($settings_tabs) ? array() : $settings_tabs;
		$active_tab    = isset( $_GET['tab'] ) ? sanitize_text_field( $_GET['tab'] ) : 'general';
		$active_tab    = array_key_exists( $active_tab, $settings_tabs ) ? $active_tab : 'general';
		$sections      = $this->get_settings_tab_sections( $active_tab );
		$key           = 'main';

		if ( ! empty( $sections ) ) {
			$key = key( $sections );
		}

		$registered_sections = $this->get_settings_tab_sections( $active_tab );
		$section             = isset( $_GET['section'] ) && ! empty( $registered_sections ) && array_key_exists( $_GET['section'], $registered_sections ) ? sanitize_text_field( $_GET['section'] ) : $key;

		// Unset 'main' if it's empty and default to the first non-empty if it's the chosen section
		$all_settings = $this->get_settings();

		// Let's verify we have a 'main' section to show
		$has_main_settings = true;
		if ( empty( $all_settings[ $active_tab ]['main'] ) ) {
			$has_main_settings = false;
		}

		// Check for old non-sectioned settings (see #4211 and #5171)
		if ( ! $has_main_settings ) {
			foreach( $all_settings[ $active_tab ] as $sid => $stitle ) {
				if ( is_string( $sid ) && ! empty( $sections) && array_key_exists( $sid, $sections ) ) {
					continue;
				} else {
					$has_main_settings = true;
					break;
				}
			}
		}

		$override = false;
		if ( false === $has_main_settings ) {
			unset( $sections['main'] );

			if ( 'main' === $section ) {
				foreach ( $sections as $section_key => $section_title ) {
					if ( ! empty( $all_settings[ $active_tab ][ $section_key ] ) ) {
						$section  = $section_key;
						$override = true;
						break;
					}
				}
			}
		}
		include_once 'views/settings.php';
	}

	/**
	 * Get Settings
	 */
	public function get_settings() {
		$settings = array(
			'general' => array(
				'main' => array(
					/* For now, let's leave manual approval always on.
					'manual_approve' => array(
						'id'   => 'manual_approve',
						'name' => __( 'Approve', 'simple-sponsorships' ),
						'desc' => __( 'Approve Sponsorships Manually', 'simple-sponsorships' ),
						'type' => 'checkbox',
						'tooltip_title' => __( 'Page Settings', 'simple-sponsorships' ),
						'tooltip_desc'  => __( 'Configure Pages where Sponsors can see their settings.','simple-sponsorships' ),
					),*/
					'content_types' => array(
						'id'          => 'content_types',
						'label'        => __( 'Content Types', 'simple-sponsorships' ),
						'desc'        => __( 'Choose what content type can be sponsored. This will enable a box on each content to add sponsors.', 'simple-sponsorships' ),
						'type'        => 'multicheck',
						'options'     => $this->get_post_types(),
						'placeholder' => __( 'Select a Type', 'simple-sponsorships' ),
						'default'     => array( 'post', 'page' ),
					),
					'page_settings' => array(
						'id'   => 'page_settings',
						'label' => '<h3>' . __( 'Pages', 'simple-sponsorships' ) . '</h3>',
						'desc' => '',
						'type' => 'header',
						'tooltip_title' => __( 'Page Settings', 'simple-sponsorships' ),
						'tooltip_desc'  => __( 'Configure Pages where Sponsors can see their settings.','simple-sponsorships' ),
					),
					'sponsor_page' => array(
						'id'          => 'sponsor_page',
						'label'        => __( 'Sponsor Page', 'simple-sponsorships' ),
						'desc'        => __( 'This is the page that will show the sponsor form. The [sponsor_form] shortcode should be on this page.', 'simple-sponsorships' ),
						'type'        => 'select',
						'options'     => $this->get_pages(),
						'chosen'      => true,
						'placeholder' => __( 'Select a page', 'simple-sponsorships' ),
					),
					'sponsorship_page' => array(
						'id'          => 'sponsorship_page',
						'label'        => __( 'Sponsorship Page', 'simple-sponsorships' ),
						'desc'        => __( 'This is the page that will show the sponsorship details. The [ss_sponsorship_details] shortcode should be on this page.', 'simple-sponsorships' ),
						'type'        => 'select',
						'options'     => $this->get_pages(),
						'chosen'      => true,
						'placeholder' => __( 'Select a page', 'simple-sponsorships' ),
					),
					'account_page' => array(
						'id'          => 'account_page',
						'label'       => __( 'Account Page', 'simple-sponsorships' ),
						'desc'        => __( 'This is the page that will show the sponsorship details. The [ss_account] shortcode should be on this page.', 'simple-sponsorships' ),
						'type'        => 'select',
						'options'     => $this->get_pages(),
						'chosen'      => true,
						'placeholder' => __( 'Select a page', 'simple-sponsorships' ),
					),
					'terms_page' => array(
						'id'          => 'terms_page',
						'label'        => __( 'Terms & Conditions Page', 'simple-sponsorships' ),
						'desc'        => __( 'This is the page that will show your terms and conditons.', 'simple-sponsorships' ),
						'type'        => 'select',
						'options'     => $this->get_pages(),
						'chosen'      => true,
						'placeholder' => __( 'Select a page', 'simple-sponsorships' ),
					),
					'privacy_page' => array(
						'id'          => 'privacy_page',
						'label'        => __( 'Privacy Policy Page', 'simple-sponsorships' ),
						'desc'        => __( 'This is the page that will show your privacy policy.', 'simple-sponsorships' ),
						'type'        => 'select',
						'options'     => $this->get_pages(),
						'chosen'      => true,
						'placeholder' => __( 'Select a page', 'simple-sponsorships' ),
					),
				)
			),
			'sponsors' => array(
				'main' => array(
					'show_content_placeholder' => array(
						'id'      => 'show_content_placeholder',
						'label'    => __( 'Placeholder', 'simple-sponsorships' ),
						'type'    => 'checkbox',
						'desc'    => __( 'If checked, it will show sponsor placeholder under the content.', 'simple-sponsorships' ),
						'default' => '0'
					),
					'content_placeholder_text' => array(
						'id'      => 'content_placeholder_text',
						'label'   => __( 'Placeholder Text', 'simple-sponsorships' ),
						'type'    => 'textarea',
						'default' => __( 'Become a Sponsor', 'simple-sponsorships' ),
					),
					'content_placeholder_icon' => array(
						'id'      => 'content_placeholder_icon',
						'label'   => __( 'Placeholder Icon', 'simple-sponsorships' ),
						'type'    => 'textarea',
						'default' => \Simple_Sponsorships\Templates::get_file_contents( trailingslashit( SS_PLUGIN_PATH ) . 'assets/images/svg/id-user.svg', 'placeholder-image' ),
						'desc'    => __( 'You can use regular images with HTML img tag or SVGs', 'simple-sponsorships' ),
					),
				),
				'under_content' => array(
					'show_in_content_footer' => array(
						'id'      => 'show_in_content_footer',
						'label'    => __( 'Show Sponsors under Content', 'simple-sponsorships' ),
						'type'    => 'checkbox',
						'desc'    => __( 'If checked, it will show sponsors that sponsored the content.', 'simple-sponsorships' ),
						'default' => '0'
					),
					'show_in_content_footer_title' => array(
						'id'      => 'show_in_content_footer_title',
						'label'   => __( 'Show Sponsor Title', 'simple-sponsorships' ),
						'type'    => 'checkbox',
						'desc'    => __( 'If checked, it will show the sponsor title under the sponsored content.', 'simple-sponsorships' ),
						'default' => '0'
					),
					'show_in_content_footer_text' => array(
						'id'      => 'show_in_content_footer_text',
						'label'   => __( 'Show Sponsor Text', 'simple-sponsorships' ),
						'type'    => 'checkbox',
						'desc'    => __( 'If checked, it will show the sponsor text under the sponsored content.', 'simple-sponsorships' ),
						'default' => '0'
					),
					'show_in_content_footer_size' => array(
						'id'          => 'show_in_content_footer_size',
						'label'       => __( 'Logo size', 'simple-sponsorships' ),
						'desc'        => __( 'Sponsor logo size when displayed under the content', 'simple-sponsorships' ),
						'type'        => 'select',
						'options'     => $this->get_image_sizes(),
						'placeholder' => __( 'Select an Image Size', 'simple-sponsorships' ),
						'default'     => 'full'
					),
					'show_in_content_footer_layout' => array(
						'id'          => 'show_in_content_footer_layout',
						'label'       => __( 'Layout', 'simple-sponsorships' ),
						'desc'        => __( 'Layout of sponsors under the content', 'simple-sponsorships' ),
						'type'        => 'select',
						'options'     => array(
							'vertical'   => __( 'Vertical', 'simple-sponsorships' ),
							'horizontal' => __( 'Horizontal', 'simple-sponsorships' )
						),
						'default'     => 'vertical'
					),
				)
			),
			'packages' => array(
				'main' => array(
					'allow_multiple_packages' => array(
						'id'      => 'allow_multiple_packages',
						'label'   => __( 'Multiple Packages', 'simple-sponsorships' ),
						'type'    => 'checkbox',
						'desc'    => __( 'If checked, it will allow people to purchase more than 1 package.', 'simple-sponsorships' ),
						'default' => '0'
					)
				),
			),
			'forms' => array(
				'main' => array(
					'allow_account_creation' => array(
						'id'      => 'allow_account_creation',
						'label'   => __( 'Allow Account', 'simple-sponsorships' ),
						'type'    => 'checkbox',
						'desc'    => __( 'If checked, it will allow people to create an account.', 'simple-sponsorships' ),
						'default' => '0'
					)
				),
			),
			'gateways' => apply_filters( 'ss_settings_gateways', array(
				'main' => array(
					'enable_payments' => array(
						'id'      => 'enable_payments',
						'label'    => __( 'Enable Payments', 'simple-sponsorships' ),
						'type'    => 'checkbox',

					),
					'payment_instructions' => array(
						'id'      => 'payment_instructions',
						'label'    => __( 'Payment Instructions', 'simple-sponsorships' ),
						'type'    => 'textarea',
						'desc'    => __( 'Instructions that will show if the payments are not enabled so your sponsors know how to pay you.', 'simple-sponsorships' ),
						'default' => __( 'We will contact you with specific information through email.', 'simple-sponsorships' ),
					),
					'currency' => array(
						'id'      => 'currency',
						'label'    => __( 'Currency', 'simple-sponsorships' ),
						'type'    => 'select',
						'options' => ss_get_currencies()
					)
				)
			) ),
			'emails' => apply_filters( 'ss_settings_emails', array(
				'main' => array(
					'ss_email_background_color' => array(
						'id'      => 'ss_email_background_color',
						'label'   => __( 'Background Color', 'simple-sponsorships' ),
						'type'    => 'color',
						'default' => '#ffffff'

					),
					'ss_email_body_background_color' => array(
						'id'      => 'ss_email_body_background_color',
						'label'   => __( 'Body Background Color', 'simple-sponsorships' ),
						'type'    => 'color',
						'default' => '#ffffff'
					),
					'ss_email_base_color' => array(
						'id'      => 'ss_email_base_color',
						'label'    => __( 'Base Color', 'simple-sponsorships' ),
						'type'    => 'color',
						'default' => '#000000'
					),
					'ss_email_base_text_color' => array(
						'id'      => 'ss_email_base_text_color',
						'label'    => __( 'Base Text Color', 'simple-sponsorships' ),
						'type'    => 'color',
						'default' => '#ffffff'
					),
					'ss_email_text_color' => array(
						'id'      => 'ss_email_text_color',
						'label'   => __( 'Text Color', 'simple-sponsorships' ),
						'type'    => 'color',
						'default' => '#000000'
					),
					'ss_email_link_text_color' => array(
						'id'      => 'ss_email_link_text_color',
						'label'    => __( 'Link Text Color', 'simple-sponsorships' ),
						'type'    => 'color',
						'default' => '#000000'
					),
					'ss_admin_email' => array(
						'id'      => 'ss_admin_email',
						'label'    => __( 'Admin Email', 'simple-sponsorships' ),
						'type'    => 'email',
						'default' => get_option( 'admin_email' ),
						'desc'    => __( 'Where should Sponsorship requests be sent to.', 'simple-sponsorships' ),
					),
				)
			) ),
		);

		return apply_filters( 'ss_get_settings', $settings );
	}

	/**
	 * Return an array with pages
	 *
	 * @return array
	 */
	public function get_pages() {

		if ( ! isset( $_GET['page'] ) || 'ss-settings' !== $_GET['page'] ) {
			return array( '' => '' );
		}

		if ( null === $this->pages ) {
			$pages_options = array();
			$pages         = get_pages();
			if ( $pages ) {
				foreach ( $pages as $page ) {
					$pages_options[ $page->ID ] = $page->post_title;
				}
			}
			$this->pages = $pages_options;
		}



		return $this->pages;
	}

	/**
	 * Image Sizes.
	 */
	public function get_image_sizes() {
		if ( null == $this->image_sizes ) {
			$this->image_sizes = ss_get_image_sizes();
		}

		$sizes = array_keys( $this->image_sizes );
		if ( ! in_array( 'full', $sizes ) ) {
			$sizes[] = 'full';
		}

		$sizes_array = array();
		foreach ( $sizes as $size ) {
			$sizes_array[ $size ] = $size;
		}
		return $sizes_array;
	}

	/**
	 * Return an array with pages
	 *
	 * @return array
	 */
	public function get_post_types() {

		$post_types  = get_post_types( array( 'show_ui' => true ), 'objects' );
		$_post_types = array();

		foreach ( $post_types as $type ) {
			// We don't need our sponsors.
			if ( 'sponsors' === $type->name ) {
				continue;
			}
			$_post_types[ $type->name ] = $type->label;
		}
		return $_post_types;
	}

	/**
	 * Get Settings Sections
	 *
	 * Copied from Easy Digital Downloads (https://easydigitaldownloads.com)
	 */
	public function get_settings_sections() {
		$sections = array(
			'general' => array(
				'main' => __( 'General', 'simple-sponsorships' ),
			),
			'sponsors' => array(
				'main'          => __( 'General', 'simple-sponsorships' ),
				'under_content' => __( 'Under Content', 'simple-sponsorships' ),
			),
			'packages' => array(
				'main'          => __( 'General', 'simple-sponsorships' ),
			),
			'forms' => array(
				'main'          => __( 'General', 'simple-sponsorships' ),
			),
			'gateways' => array(
				'main' => __( 'General', 'simple-sponsorships' ),
			),
			'emails' => array(
				'main' => __( 'General', 'simple-sponsorships' ),
			),
		);

		return apply_filters( 'ss_get_settings_sections', $sections );
	}

	/**
	 * Get tabs.
	 *
	 * Copied from Easy Digital Downloads (https://easydigitaldownloads.com)
	 *
	 * @return array
	 */
	public function get_settings_tabs() {

		$tabs             = array();
		$tabs['general']  = __( 'General', 'simple-sponsorships' );
		$tabs['sponsors'] = __( 'Sponsors', 'simple-sponsorships' );
		$tabs['packages'] = __( 'Packages', 'simple-sponsorships' );
		$tabs['forms']    = __( 'Forms', 'simple-sponsorships' );
		$tabs['gateways'] = __( 'Payment Gateways', 'simple-sponsorships' );
		$tabs['emails']   = __( 'Emails', 'simple-sponsorships' );
		//$tabs['styles']   = __( 'Styles', 'easy-digital-downloads' );
		//$tabs['taxes']    = __( 'Taxes', 'easy-digital-downloads' );
		//$tabs['privacy']  = __( 'Privacy', 'easy-digital-downloads' );
		//$tabs['misc']     = __( 'Misc', 'easy-digital-downloads' );

		return apply_filters( 'ss_settings_tabs', $tabs );
	}

	/**
	 * Retrieve settings tabs
	 *
	 * Copied from Easy Digital Downloads (https://easydigitaldownloads.com)
	 *
	 * @return array $section
	 */
	function get_settings_tab_sections( $tab = false ) {

		$tabs     = array();
		$sections = $this->get_settings_sections();

		if( $tab && ! empty( $sections[ $tab ] ) ) {
			$tabs = $sections[ $tab ];
		} else if ( $tab ) {
			$tabs = array();
		}

		return $tabs;
	}

	/**
	 * Register Settings using WP Settings API.
	 *
	 * Copied from Easy Digital Downloads (https://easydigitaldownloads.com)
	 */
	public function register_settings() {
		if ( false == get_option( 'ss_settings' ) ) {
			add_option( 'ss_settings', array() );
		}

		SS()->payment_gateways();

		foreach ( $this->get_settings() as $tab => $sections ) {
			foreach ( $sections as $section => $settings) {

				// Check for backwards compatibility
				$section_tabs = $this->get_settings_tab_sections( $tab );
				if ( ! is_array( $section_tabs ) || ! array_key_exists( $section, $section_tabs ) ) {
					$section = 'main';
					$settings = $sections;
				}

				add_settings_section(
					'ss_settings_' . $tab . '_' . $section,
					__return_null(),
					'__return_false',
					'ss_settings_' . $tab . '_' . $section
				);

				foreach ( $settings as $option ) {
					// For backwards compatibility
					if ( empty( $option['id'] ) ) {
						continue;
					}

					$args = wp_parse_args( $option, array(
						'section'       => $section,
						'id'            => null,
						'label'         => '',
						'desc'          => '',
						'name'          => '',
						'size'          => null,
						'options'       => '',
						'std'           => '',
						'min'           => null,
						'max'           => null,
						'step'          => null,
						'chosen'        => null,
						'multiple'      => null,
						'placeholder'   => null,
						'allow_blank'   => true,
						'readonly'      => false,
						'faux'          => false,
						'tooltip_title' => false,
						'tooltip_desc'  => false,
						'field_class'   => '',
						'default'       => '',
					) );

					add_settings_field(
						'ss_settings[' . $args['id'] . ']',
						$args['label'],
						array( $this, 'settings_field' ),
						'ss_settings_' . $tab . '_' . $section,
						'ss_settings_' . $tab . '_' . $section,
						$args
					);
				}
			}

		}

		// Creates our settings in the options table
		register_setting( 'ss_settings', 'ss_settings', array( $this, 'sanitize_settings' ) );
	}

	/**
	 * Flattens the set of registered settings and their type so we can easily sanitize all the settings
	 * in a much cleaner set of logic in edd_settings_sanitize
	 *
	 * Copied from Easy Digital Downloads (https://easydigitaldownloads.com)
	 *
	 * @param $filtered_tab bool|string     A tab to filter setting types by.
	 * @param $filtered_section bool|string A section to filter setting types by.
	 * @return array Key is the setting ID, value is the type of setting it is registered as
	 */
	public function get_registered_settings_types( $filtered_tab = false, $filtered_section = false ) {
		$settings      = $this->get_settings();
		$setting_types = array();
		foreach ( $settings as $tab_id => $tab ) {

			if ( false !== $filtered_tab && $filtered_tab !== $tab_id ) {
				continue;
			}

			foreach ( $tab as $section_id => $section_or_setting ) {

				// See if we have a setting registered at the tab level for backwards compatibility
				if ( false !== $filtered_section && is_array( $section_or_setting ) && array_key_exists( 'type', $section_or_setting ) ) {
					$setting_types[ $section_or_setting['id'] ] = $section_or_setting['type'];
					continue;
				}

				if ( false !== $filtered_section && $filtered_section !== $section_id ) {
					continue;
				}

				foreach ( $section_or_setting as $section => $section_settings ) {

					if ( ! empty( $section_settings['type'] ) ) {
						$setting_types[ $section_settings['id'] ] = $section_settings['type'];
					}

				}

			}

		}

		return $setting_types;
	}


	/**
	 * Sanitizing settings.
	 *
	 * Copied from Easy Digital Downloads (https://easydigitaldownloads.com)
	 *
	 * @param array $input
	 *
	 * @return array
	 */
	public function sanitize_settings( $input = array() ) {
		$ss_options = get_option( 'ss_settings', array() );

		$doing_section = false;
		if ( ! empty( $_POST['_wp_http_referer'] ) ) {
			$doing_section = true;
		}

		$setting_types = $this->get_registered_settings_types();
		$input         = $input ? $input : array();

		if ( $doing_section ) {

			parse_str( $_POST['_wp_http_referer'], $referrer ); // Pull out the tab and section
			$tab      = isset( $referrer['tab'] ) ? $referrer['tab'] : 'general';
			$section  = isset( $referrer['section'] ) ? $referrer['section'] : 'main';

			$setting_types = $this->get_registered_settings_types( $tab, $section );

			// Run a general sanitization for the tab for special fields (like taxes)
			$input = apply_filters( 'ss_settings_' . $tab . '_sanitize', $input );

			// Run a general sanitization for the section so custom tabs with sub-sections can save special data
			$input = apply_filters( 'ss_settings_' . $tab . '-' . $section . '_sanitize', $input );

		}

		// Merge our new settings with the existing
		$output = array_merge( $ss_options, $input );

		foreach ( $setting_types as $key => $type ) {

			if ( empty( $type ) ) {
				continue;
			}

			// Some setting types are not actually settings, just keep moving along here
			$non_setting_types = apply_filters( 'ss_non_setting_types', array(
				'header', 'descriptive_text', 'hook',
			) );

			if ( in_array( $type, $non_setting_types ) ) {
				continue;
			}

			if ( array_key_exists( $key, $output ) ) {
				$output[ $key ] = apply_filters( 'ss_settings_sanitize_' . $type, $output[ $key ], $key );
				$output[ $key ] = apply_filters( 'ss_settings_sanitize', $output[ $key ], $key );
			}

			if ( $doing_section ) {
				switch( $type ) {
					case 'checkbox':
					case 'gateways':
					case 'multicheck':
					case 'payment_icons':
						if ( array_key_exists( $key, $input ) && $output[ $key ] === '-1' ) {
							unset( $output[ $key ] );
						}
						break;
					case 'text':
						if ( array_key_exists( $key, $input ) && empty( $input[ $key ] ) ) {
							unset( $output[ $key ] );
						}
						break;
					case 'textarea':
						break;
					default:
						if ( array_key_exists( $key, $input ) && empty( $input[ $key ] ) || ( array_key_exists( $key, $output ) && ! array_key_exists( $key, $input ) ) ) {
							unset( $output[ $key ] );
						}
						break;
				}
			} else {
				if ( empty( $input[ $key ] ) ) {
					unset( $output[ $key ] );
				}
			}

		}

		if ( $doing_section ) {
			add_settings_error( 'ss-notices', '', __( 'Settings updated.', 'simple-sponsorships' ), 'updated' );
		}

		return $output;
		}

	/**
	 * Settings Field.
	 *
	 * @param array $args
	 */
	public function settings_field( $args ) {
		$args['value'] = ss_get_option( $args['id'], $args['default'] );

		$class = $this->sanitize_html_class( $args['field_class'] );

		$args['id'] = 'ss_settings[' . self::sanitize_key( $args['id'] ) . ']';

		self::render_field( $args );
	}

	/**
	 * Render a settings field.
	 *
	 * @param array $args
	 */
	public static function render_field( $args ) {

		$args = wp_parse_args( $args, array(
			'section'       => '',
			'id'            => null,
			'desc'          => '',
			'name'          => '',
			'size'          => null,
			'options'       => '',
			'std'           => '',
			'min'           => null,
			'max'           => null,
			'step'          => null,
			'chosen'        => null,
			'multiple'      => null,
			'placeholder'   => null,
			'allow_blank'   => true,
			'readonly'      => false,
			'faux'          => false,
			'tooltip_title' => false,
			'tooltip_desc'  => false,
			'field_class'   => '',
			'title'         => '',
			'required'      => false,
			'value'         => '',
			'type'          => 'text',
		) );

		$class = self::sanitize_html_class( $args['field_class'] );

		$id = $args['id'];

		$name = $args['name'] ? $args['name'] : $args['id'];

		$name = 'name="' . $name . '"';

		$label = '<label for="' . $id . '"> '  . wp_kses_post( $args['desc'] ) . '</label>';

		switch( $args['type'] ) {
			case 'text':
			case 'email':
			case 'password':
			case 'url':
				$type = $args['type'];
				if ( $args['value'] ) {
					$value = $args['value'];
				} elseif( ! empty( $args['allow_blank'] ) && empty( $args['value'] ) ) {
					$value = '';
				} else {
					$value = isset( $args['std'] ) ? $args['std'] : '';
				}

				if ( isset( $args['faux'] ) && true === $args['faux'] ) {
					$args['readonly'] = true;
					$value = isset( $args['std'] ) ? $args['std'] : '';
					$name  = '';
				}

				$disabled = ! empty( $args['disabled'] ) ? ' disabled="disabled"' : '';
				$readonly = $args['readonly'] === true ? ' readonly="readonly"' : '';
				$size     = ( isset( $args['size'] ) && ! is_null( $args['size'] ) ) ? $args['size'] : 'regular';
				$html     = '<input type="' . $type . '" class="' . $class . ' ' . self::sanitize_html_class( $size ) . '-text" id="' . $id . '" ' . $name . ' value="' . esc_attr( stripslashes( $value ) ) . '"' . $readonly . $disabled . ' placeholder="' . esc_attr( $args['placeholder'] ) . '"/>';
				$html    .= $label;

				echo apply_filters( 'ss_after_setting_output', $html, $args );
				break;
			case 'number':

				if ( $args['value'] ) {
					$value = $args['value'];
				} else {
					$value = isset( $args['std'] ) ? $args['std'] : '';
				}

				if ( isset( $args['faux'] ) && true === $args['faux'] ) {
					$args['readonly'] = true;
					$value = isset( $args['std'] ) ? $args['std'] : '';
					$name  = '';
				}

				$max  = isset( $args['max'] ) ? $args['max'] : 999999;
				$min  = isset( $args['min'] ) ? $args['min'] : 0;
				$step = isset( $args['step'] ) ? $args['step'] : 1;

				$size = ( isset( $args['size'] ) && ! is_null( $args['size'] ) ) ? $args['size'] : 'regular';
				$html = '<input type="number" step="' . esc_attr( $step ) . '" max="' . esc_attr( $max ) . '" min="' . esc_attr( $min ) . '" class="' . $class . ' ' . sanitize_html_class( $size ) . '-text" id="' . $id . '" ' . $name . ' value="' . esc_attr( stripslashes( $value ) ) . '"/>';
				$html .= $label;

				echo apply_filters( 'ss_after_setting_output', $html, $args );
				break;
			case 'textarea':

				if ( $args['value'] ) {
					$value = $args['value'];
				} else {
					$value = isset( $args['std'] ) ? $args['std'] : '';
				}

				$html = '<textarea class="' . $class . ' large-text" cols="50" rows="5" id="' . $id . '" ' . $name . '>' . esc_textarea( stripslashes( $value ) ) . '</textarea>';
				$html .= $label;

				echo apply_filters( 'ss_after_setting_output', $html, $args );
				break;
			case 'editor':

				if ( $args['value'] ) {
					$value = $args['value'];
				} else {
					$value = isset( $args['std'] ) ? $args['std'] : '';
				}


				ob_start();
				\wp_editor( wp_unslash( $value ), $id, array( 'textarea_name' => $args['name'] ? $args['name'] : $args['id'] ) );

				$html = ob_get_clean();
				$html .= $label;

				echo apply_filters( 'ss_after_setting_output', $html, $args );
				break;
			case 'select':

				if ( $args['value'] ) {
					$value = $args['value'];
				} else {

					// Properly set default fallback if the Select Field allows Multiple values
					if ( empty( $args['multiple'] ) ) {
						$value = isset( $args['std'] ) ? $args['std'] : '';
					} else {
						$value = ! empty( $args['std'] ) ? $args['std'] : array();
					}

				}

				if ( isset( $args['placeholder'] ) ) {
					$placeholder = $args['placeholder'];
				} else {
					$placeholder = '';
				}

				if ( isset( $args['chosen'] ) ) {
					$class .= ' edd-select-chosen';
				}

				$nonce = isset( $args['data']['nonce'] )
					? ' data-nonce="' . sanitize_text_field( $args['data']['nonce'] ) . '" '
					: '';

				// If the Select Field allows Multiple values, save as an Array
				$name_attr = $args['name'] ? $args['name'] : $args['id'];
				$name_attr = ( $args['multiple'] ) ? $name_attr . '[]' : $name_attr;

				$html = '<select ' . $nonce . ' id="' . $id . '" name="' . $name_attr . '" class="' . $class . '" data-placeholder="' . esc_html( $placeholder ) . '" ' . ( ( $args['multiple'] ) ? 'multiple="true"' : '' ) . '>';

				if ( $placeholder ) {
					$html .= '<option value="">' . esc_html( $placeholder ) . '</option>';
				}

				foreach ( $args['options'] as $option => $name ) {

					if ( ! $args['multiple'] ) {
						$selected = selected( $option, $value, false );
						$html .= '<option value="' . esc_attr( $option ) . '" ' . $selected . '>' . esc_html( $name ) . '</option>';
					} else {
						// Do an in_array() check to output selected attribute for Multiple
						$html .= '<option value="' . esc_attr( $option ) . '" ' . ( ( in_array( $option, $value ) ) ? 'selected="true"' : '' ) . '>' . esc_html( $name ) . '</option>';
					}

				}

				$html .= '</select>';
				$html .= $label;

				echo apply_filters( 'ss_after_setting_output', $html, $args );
				break;
			case 'multicheck':
				$html = '';
				if ( ! empty( $args['options'] ) ) {
					$html .= '<input type="hidden" ' . $name . ' value="-1" />';
					foreach( $args['options'] as $key => $option ):
						if( isset( $args['value'][ $key ] ) ) { $enabled = $option; } else { $enabled = NULL; }
						$html .= '<input name="' . $id . '[' . self::sanitize_key( $key ) . ']" id="' . $id . '[' . self::sanitize_key( $key ) . ']" class="' . $class . '" type="checkbox" value="' . esc_attr( $option ) . '" ' . checked( $option, $enabled, false) . '/>&nbsp;';
						$html .= '<label for="' . $id . '[' . self::sanitize_key( $key ) . ']">' . wp_kses_post( $option ) . '</label><br/>';
					endforeach;
					$html .= '<p class="description">' . $args['desc'] . '</p>';
				}

				echo apply_filters( 'ss_after_setting_output', $html, $args );
				break;
			case 'checkbox':

				if ( isset( $args['faux'] ) && true === $args['faux'] ) {
					$name = '';
				}

				$checked  = ! empty( $args['value'] ) ? checked( 1, $args['value'], false ) : '';
				$html     = '<input type="hidden"' . $name . ' value="-1" />';
				$html    .= '<input type="checkbox" id="' . $id . '"' . $name . ' value="1" ' . $checked . ' class="' . $class . '"/>';
				$html    .= '<label for="' . $id . '"> '  . wp_kses_post( $args['desc'] ) . '</label>';

				echo apply_filters( 'ss_after_setting_output', $html, $args );
				break;
			// Color picker.
			case 'color':
				if ( $args['value'] ) {
					$value = $args['value'];
				} elseif( ! empty( $args['allow_blank'] ) && empty( $args['value'] ) ) {
					$value = '';
				} else {
					$value = isset( $args['std'] ) ? $args['std'] : '';
				}

				if ( isset( $args['faux'] ) && true === $args['faux'] ) {
					$args['readonly'] = true;
					$value = isset( $args['std'] ) ? $args['std'] : '';
					$name  = '';
				}

				$disabled = ! empty( $args['disabled'] ) ? ' disabled="disabled"' : '';
				$readonly = $args['readonly'] === true ? ' readonly="readonly"' : '';
				$size     = ( isset( $args['size'] ) && ! is_null( $args['size'] ) ) ? $args['size'] : 'regular';
				$html     = '<input type="text" class="ss-colorpicker ' . $class . ' ' . self::sanitize_html_class( $size ) . '-text" id="' . $id . '" ' . $name . ' value="' . esc_attr( stripslashes( $value ) ) . '"' . $readonly . $disabled . ' placeholder="' . esc_attr( $args['placeholder'] ) . '"/>';
				$html    .= $label;

				echo apply_filters( 'ss_after_setting_output', $html, $args );
				break;
			case 'heading':
				echo isset( $args['desc'] ) ? '<h2>' . $args['desc'] . '</h2>' : '';
				break;
			case 'hidden':
				if ( $args['value'] ) {
					$value = $args['value'];
				} elseif( ! empty( $args['allow_blank'] ) && empty( $args['value'] ) ) {
					$value = '';
				} else {
					$value = isset( $args['std'] ) ? $args['std'] : '';
				}
				echo $html = '<input type="hidden" id="' . $id . '" ' . $name . ' value="' . esc_attr( stripslashes( $value ) ) . '"/>';
				break;
			default:
				do_action( 'ss_settings_field_' . $args['type'], $args );
				break;
		}
	}

	/**
	 * Sanitize HTML Class Names
	 *
	 * Copied from Easy Digital Downloads
	 *
	 * @param  string|array $class HTML Class Name(s)
	 * @return string $class
	 */
	public static function sanitize_html_class( $class = '' ) {

		if ( is_string( $class ) ) {
			$class = sanitize_html_class( $class );
		} else if ( is_array( $class ) ) {
			$class = array_values( array_map( 'sanitize_html_class', $class ) );
			$class = implode( ' ', array_unique( $class ) );
		}

		return $class;

	}

	/**
	 * Sanitizes a string key for Settings
	 *
	 * Copied from Easy Digital Downloads
	 *
	 * Keys are used as internal identifiers. Alphanumeric characters, dashes, underscores, stops, colons and slashes are allowed
	 *
	 * @param  string $key String key
	 * @return string Sanitized key
	 */
	public static function sanitize_key( $key ) {
		$raw_key = $key;
		$key = preg_replace( '/[^a-zA-Z0-9_\-\.\:\/]/', '', $key );

		return apply_filters( 'ss_sanitize_key', $key, $raw_key );
	}
}

new Settings();