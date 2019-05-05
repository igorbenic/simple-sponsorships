<?php
/**
 * Plugin Name:     Simple Sponsorships
 * Plugin URI:      https://wordpress.org/plugins/simple-sponsorships/
 * Description:     Accept sponsors and sponsorships on your site.
 * Author:          Igor Benic
 * Author URI:      https://www.ibenic.com
 * Text Domain:     simple-sponsorships
 * Domain Path:     /languages
 * Version:         1.0.0
 *
 * @fs_premium_only /includes/premium/
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
	public $version = '1.0.0';

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

		// Gateways.
		include_once 'includes/gateways/class-paypal.php';

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
		add_action( 'wp_enqueue_scripts', array( $this, 'enqueue' ) );

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
	 * Enqueueing Scripts and Styles.
	 */
	public function enqueue() {

		wp_enqueue_style( 'ss-style', SS_PLUGIN_URL . '/assets/dist/css/public.css', array(), $this->version );
		wp_enqueue_script( 'ss-script', SS_PLUGIN_URL . '/assets/dist/js/public.js', array( 'jquery' ), $this->version, true );
		wp_localize_script( 'ss-script', 'ss_wp', array(
			'ajax' => admin_url( 'admin-ajax.php' ),
			'nonce' => wp_create_nonce( 'ss-ajax' )
		));
	}

	/**
	 * Processing Actions on POST or GET requests in Public
	 */
	public function process_actions() {
		// We have an admin one.
		if ( is_admin() ) { return; }

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
			add_action( 'ss_plugin_loaded', array( $this, 'includes' ) );
		}

		public function includes() {
			include_once 'includes/premium/package-slots/package-slots.php';
		}
	}

	new Premium();
}

/**
 * Getting the instance.
 *
 * @return Plugin
 */
function get_main() {
	return Plugin::instance();
}

get_main();
