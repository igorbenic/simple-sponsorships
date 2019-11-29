<?php
/**
 * Plugin Name:     Simple Sponsorships
 * Plugin URI:      https://wordpress.org/plugins/simple-sponsorships/
 * Description:     Accept sponsors and sponsorships on your site.
 * Author:          Igor Benic
 * Author URI:      https://www.ibenic.com
 * Text Domain:     simple-sponsorships
 * Domain Path:     /languages
 * Version:         1.4.1
 *
 * @fs_premium_only /includes/premium/, /assets/css/premium/, /assets/js/premium/, /assets/dist/css/premium/, /assets/dist/js/premium/
 * @package         Simple_Sponsorships
 */

namespace Simple_Sponsorships;

use Simple_Sponsorships\Admin\Admin;
use Simple_Sponsorships\Widgets\Widgets;

if ( ! defined( 'ABSPATH' ) ) {
	return;
}

if ( function_exists( '\Simple_Sponsorships\ss_fs' ) ) {
	ss_fs()->set_basename( true, __FILE__ );
} else {
	if ( ! function_exists( '\Simple_Sponsorships\ss_fs' ) ) {
		// Create a helper function for easy SDK access.
		function ss_fs() {
			global $ss_fs;

			if ( ! isset( $ss_fs ) ) {
				// Include Freemius SDK.
				require_once dirname( __FILE__ ) . '/freemius/start.php';

				$ss_fs = \fs_dynamic_init( array(
					'id'                  => '3701',
					'slug'                => 'simple-sponsorships',
					'type'                => 'plugin',
					'public_key'          => 'pk_79a74779312b5f726d168770a13c2',
					'is_premium'          => true,
					// If your plugin is a serviceware, set this option to false.
					'has_premium_version' => true,
					'has_addons'          => false,
					'has_paid_plans'      => true,
					'menu'                => array(
						'slug'    => 'edit.php?post_type=sponsors',
						'support' => false,
					),
				) );
			}

			return $ss_fs;
		}

		// Init Freemius.
		ss_fs();
		// Signal that SDK was initiated.
		do_action( 'ss_fs_loaded' );
	}
}

if ( ! class_exists( '\Simple_Sponsorships\Plugin' ) ) {
	/**
	 * The main class.
	 *
	 * @package Simple_Sponsorships
	 */
	class Plugin {

		/**
		 * @var
		 */
		private static $instance;

		/**
		 * @var string
		 */
		public $version = '1.4.1';

		/**
		 * Settings
		 *
		 * @var array
		 */
		private $settings = null;

		/**
		 * Session
		 *
		 * @var null
		 */
		public $session = null;

		/**
		 * Integrations
		 */
		protected $integrations = array();

		/**
		 * Plugin constructor.
		 */
		public function __construct() {
			$this->define();
			$this->includes();
			$this->hooks();
		}

		/**
		 * Get settings for Simple Sponsorships.
		 *
		 * @return array
		 */
		public function get_settings() {
			if ( null === $this->settings ) {
				$this->settings = get_option( 'ss_settings', array() );
			}

			return $this->settings;
		}

		/**
		 * Returns the main plugin instance.
		 *
		 * @return Plugin
		 */
		public static function instance() {
			if ( ! isset( self::$instance ) && ! ( self::$instance instanceof Plugin ) ) {
				self::$instance = new Plugin();
				self::$instance->define();
				self::$instance->includes();
				self::$instance->hooks();

				register_activation_hook( __FILE__, array( '\Simple_Sponsorships\Installer', 'activate' ) );
			}

			return self::$instance;
		}

		/**
		 * Define constants.
		 */
		public function define() {
			if ( ! defined( 'SS_VERSION' ) ) {
				define( 'SS_VERSION', $this->version );
			}

			if ( ! defined( 'SS_PLUGIN_URL' ) ) {
				define( 'SS_PLUGIN_URL', untrailingslashit( plugin_dir_url( __FILE__ ) ) );
			}

			if ( ! defined( 'SS_PLUGIN_PATH' ) ) {
				define( 'SS_PLUGIN_PATH', untrailingslashit( plugin_dir_path( __FILE__ ) ) );
			}
		}

		/**
		 * Including files.
		 */
		public function includes() {
			// Abstracts.
			include_once 'includes/abstract/class-db.php';
			include_once 'includes/abstract/class-custom-data.php';
			include_once 'includes/abstract/class-email.php';
			include_once 'includes/abstract/class-payment-gateway.php';
			include_once 'includes/abstract/class-form.php';
			include_once 'includes/abstract/class-integration.php';
			include_once 'includes/abstract/class-premium-integration-dummy.php';

			// Functions.
			include_once 'includes/functions-core.php';
			include_once 'includes/functions-sponsorship.php';
			include_once 'includes/functions-forms.php';
			include_once 'includes/functions-session.php';
			include_once 'includes/functions-emails.php';
			include_once 'includes/functions-gateways.php';
			include_once 'includes/functions-sponsors.php';
			include_once 'includes/functions-packages.php';

			// Classes.
			include_once 'includes/class-formatting.php';
			include_once 'includes/class-session.php';
			include_once 'includes/class-content-types.php';
			include_once 'includes/class-installer.php';
			include_once 'includes/class-package.php';
			include_once 'includes/class-sponsorship.php';
			include_once 'includes/class-sponsor.php';
			include_once 'includes/class-templates.php';
			include_once 'includes/class-shortcodes.php';
			include_once 'includes/class-emails.php';
			include_once 'includes/class-payment-gateways.php';
			include_once 'includes/class-countries.php';
			include_once 'includes/class-widgets.php';
			include_once 'includes/class-ajax.php';
			include_once 'includes/class-blocks.php';

			// Integrations
			include_once 'includes/integrations/class-gravityforms.php';
			include_once 'includes/integrations/dummy/class-stripe.php';
			include_once 'includes/integrations/dummy/class-package-slots.php';
			include_once 'includes/integrations/dummy/class-post-paid-form.php';
			include_once 'includes/integrations/dummy/class-package-features.php';
			include_once 'includes/integrations/dummy/class-package-timed-availability.php';

			// Gateways.
			include_once 'includes/gateways/class-paypal.php';
			include_once 'includes/gateways/class-bank-transfer.php';

			// Forms.
			include_once 'includes/forms/class-form-sponsors.php';
			include_once 'includes/forms/class-form-payment.php';

			// Emails.
			include_once 'includes/emails/class-email-new-sponsorship.php';
			include_once 'includes/emails/class-email-pending-sponsorship.php';
			include_once 'includes/emails/class-email-activated-sponsorship.php';
			include_once 'includes/emails/class-email-customer-invoice.php';
			include_once 'includes/emails/class-email-rejected-sponsorship.php';

			// DB.
			include_once 'includes/class-dbs.php';
			include_once 'includes/db/class-db-packages.php';
			include_once 'includes/db/class-db-sponsorships.php';
			include_once 'includes/db/class-db-sponsors.php';
			include_once 'includes/db/class-db-sponsorship-items.php';

			if ( is_admin() ) {
				include_once 'includes/admin/class-admin.php';
			}

			$this->session = new Session();

			do_action( 'ss_plugin_loaded' );
		}

		/**
		 * Hooking
		 */
		public function hooks() {
			add_action( 'plugins_loaded', array( $this, 'run' ) );
			add_action( 'init', array( $this, 'process_actions' ) );
			add_action( 'init', array( '\Simple_Sponsorships\Installer', 'check_version' ), 5 );
			add_action( 'wp_enqueue_scripts', array( $this, 'enqueue' ), 20 );
			add_action( 'plugins_loaded', array( $this, 'load_integrations' ), 99 );

			add_action( 'ss_sponsorship_details', 'ss_sponsorship_details' );
			add_action( 'ss_sponsorship_sponsor', 'ss_sponsorship_sponsor' );
			add_action( 'ss_sponsorship_customer_details', 'ss_sponsorship_customer_details' );
			add_action( 'ss_sponsor_form_sponsorship_created', 'ss_email_on_new_sponsorship' );
			add_action( 'ss_sponsorship_activated', 'ss_email_on_activated_sponsorship' );
			add_action( 'ss_sponsorship_activated', 'ss_email_invoice_on_activated_sponsorship' );
			add_action( 'ss_sponsorship_status_updated', 'ss_email_on_approved_sponsorship', 20, 3 );
			add_action( 'ss_sponsorship_status_updated', 'ss_activate_sponsorship_on_status_change', 20, 3 );
			add_action( 'ss_sponsorship_status_rejected', 'ss_email_on_rejected_sponsorship', 20, 1 );
			add_action( 'ss_sponsor_form', 'ss_process_sponsor_form' );
			add_action( 'ss_payment_form', 'ss_process_payment_form' );
			add_action( 'ss_sponsorship_form', 'ss_show_payment_form_for_sponsorship' );
			add_filter( 'the_content', 'ss_show_sponsors_under_content' );
		}

		/**
		 * Loading Integrations
		 * @return array
		 */
		public function load_integrations() {
			$active_integrations = ss_get_active_integrations();
			$integrations = ss_get_registered_integrations();
			foreach ( $active_integrations as $slug ) {
				if( isset( $integrations[ $slug ] ) ) {
					$integration = $integrations[ $slug ];
					$this->integrations[ $slug ] = new $integration();
				}
			}
		}


		/**
		 * Enqueueing Scripts and Styles.
		 */
		public function enqueue() {

			if ( ! wp_style_is( 'ss-style', 'registered' ) ) {
				wp_register_style( 'ss-style', SS_PLUGIN_URL . '/assets/dist/css/public.css', array(), $this->version );
			}

			if ( ! wp_script_is( 'ss-script', 'registered' ) ) {
				wp_register_script( 'ss-script', SS_PLUGIN_URL . '/assets/dist/js/public.js', array( 'jquery' ), $this->version, true );
			}

			wp_enqueue_style( 'ss-style' );
			wp_enqueue_script( 'ss-script' );
			wp_localize_script( 'ss-script', 'ss_wp', array(
				'ajax'  => admin_url( 'admin-ajax.php' ),
				'nonce' => wp_create_nonce( 'ss-ajax' )
			) );
		}

		/**
		 * Processing Actions on POST or GET requests in Public
		 */
		public function process_actions() {
			// We have an admin one.
			if ( is_admin() ) {
				return;
			}

			if ( isset( $_POST['ss-action'] ) ) {
				do_action( 'ss_' . sanitize_text_field( strtolower( $_POST['ss-action'] ) ), $_POST );
			}

			if ( isset( $_GET['ss-action'] ) ) {
				do_action( 'ss_' . sanitize_text_field( strtolower( $_GET['ss-action'] ) ), $_GET );
			}
		}

		/**
		 * Get gateways class.
		 *
		 * @return Payment_Gateways
		 */
		public function payment_gateways() {
			return Payment_Gateways::instance();
		}

		/**
		 * Run the plugin
		 */
		public function run() {
			new Content_Types();
			new Shortcodes();
			new Emails();
			new Widgets();
			new Blocks();

			// Registering the Databases to wpdb.
			$dbs = new Databases();
			$dbs->register();

			if ( is_admin() ) {
				new Admin();
			}

			\load_plugin_textdomain( 'simple-sponsorships', false, basename( dirname( __FILE__ ) ) . '/languages' );

		}
	}
}

if ( ss_fs()->is__premium_only() ) {
	class Premium {
		/**
		 * @var
		 */
		private static $instance;

		/**
		 * Premium constructor.
		 */
		public function __construct() {
			add_action( 'plugins_loaded', array( $this, 'run' ) );
			add_action( 'ss_plugin_loaded', array( $this, 'includes' ) );
			add_filter( 'ss_registered_integrations', array( $this, 'register_integrations' ) );
			add_filter( 'ss_template_paths', array( $this, 'premium_templates' ) );
			add_action( 'enqueue_block_editor_assets', array( $this, 'enqueue_editor_scripts' ), 10 );
			add_action( 'wp_enqueue_scripts', array( $this, 'enqueue' ), 10 );
		}

		/**
		 * Run the Premium
		 */
		public function run() {
			\load_plugin_textdomain( 'simple-sponsorships-premium', false, basename( dirname( __FILE__ ) ) . '/includes/premium/languages' );
		}

		/**
		 * Including premium files.
		 */
		public function includes() {
			include_once 'includes/premium/functions-update.php';
			include_once 'includes/premium/package-slots/package-slots.php';
			include_once 'includes/premium/post-paid-form/post-paid-form.php';
			include_once 'includes/premium/stripe-gateway/stripe-gateway.php';

			if ( ss_fs()->is_plan( 'platinum' ) ) {
				include_once 'includes/premium/package-features/package-features.php';
				include_once 'includes/premium/package-timed-availability/package-timed-availability.php';
			}
		}

		/**
		 * Adding the premium path for templates.
		 *
		 * @param $paths
		 *
		 * @return mixed
		 */
		public function premium_templates( $paths ) {
			$paths[90] = SS_PLUGIN_PATH . '/includes/premium/templates';
			return $paths;
		}

		/**
		 * @param $integrations
		 */
		public function register_integrations( $integrations ) {
			$integrations['stripe']         = '\Simple_Sponsorships\Stripe\Plugin';
			$integrations['package-slots']  = '\Simple_Sponsorships\Package_Slots\Plugin';
			$integrations['post-paid-form'] = '\Simple_Sponsorships\Post_Paid_Form\Plugin';

			if ( ss_fs()->is_plan( 'platinum' ) ) {
				$integrations['package-features'] = '\Simple_Sponsorships\Package_Features\Plugin';
				$integrations['package-timed-availability'] = '\Simple_Sponsorships\Package_Timed_Availability\Plugin';
			}
			return $integrations;
		}

		/**
		 * Enqueue premium scripts for block editor.
		 */
		public function enqueue_editor_scripts() {
			wp_register_script(
				'ss-block-js',
				SS_PLUGIN_URL . '/assets/dist/js/premium/gutenberg.js',
				array( 'wp-blocks', 'wp-i18n', 'wp-element', 'wp-components', 'wp-editor', 'wp-compose' )
			);

			wp_register_style(
				'ss-block-css',
				SS_PLUGIN_URL . '/assets/dist/css/premium/gutenberg.css'
			);
		}

		/**
		 * Register premium styles.
		 */
		public function enqueue() {
			wp_register_style( 'ss-style', SS_PLUGIN_URL . '/assets/dist/css/premium/public.css', array(), get_main()->version );
		}
	}

	new Premium();
}

if ( ! function_exists('\Simple_Sponsorships\get_main' ) ) {
	/**
	 * Getting the instance.
	 *
	 * @return Plugin
	 */
	function get_main() {
		return Plugin::instance();
	}

	get_main();
}
