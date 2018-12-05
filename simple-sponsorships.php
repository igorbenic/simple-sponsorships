<?php
/**
 * Plugin Name:     Simple Sponsorships
 * Plugin URI:      #
 * Description:     Accept sponsors and sponsorships on your site.
 * Author:          Igor Benic
 * Author URI:      https://www.ibenic.com
 * Text Domain:     simple-sponsorships
 * Domain Path:     /languages
 * Version:         0.1.0
 *
 * @package         Simple_Sponsorships
 */

namespace Simple_Sponsorships;

if ( ! defined( 'ABSPATH' ) ) {
	return;
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
	public $version = '0.1.0';

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
		include_once 'includes/abstract/class-db.php';
		include_once 'includes/abstract/class-custom-data.php';
		include_once 'includes/abstract/class-email.php';

		include_once 'includes/functions-core.php';
		include_once 'includes/functions-sponsorship.php';
		include_once 'includes/functions-forms.php';
		include_once 'includes/functions-session.php';

		include_once 'includes/class-session.php';
		include_once 'includes/class-content-types.php';
		include_once 'includes/class-installer.php';
		include_once 'includes/class-package.php';
		include_once 'includes/class-sponsorship.php';
		include_once 'includes/class-sponsor.php';
		include_once 'includes/class-templates.php';
		include_once 'includes/class-shortcodes.php';
		include_once 'includes/class-form-sponsors.php';

		include_once 'includes/emails/class-email-new-sponsorship.php';

		// DB
		include_once 'includes/class-dbs.php';
		include_once 'includes/db/class-db-packages.php';
		include_once 'includes/db/class-db-sponsorships.php';
		include_once 'includes/db/class-db-sponsors.php';

		if ( is_admin() ) {
			include_once 'includes/admin/class-admin.php';
		}

		$this->session = new Session();
	}

	/**
	 * Hooking
	 */
	public function hooks() {
		add_action( 'plugins_loaded', array( $this, 'run' ) );
		add_action( 'init', array( $this, 'process_actions' ) );

		add_action( 'ss_sponsorship_details', 'ss_sponsorship_details' );
		add_action( 'ss_sponsorship_sponsor', 'ss_sponsorship_sponsor' );
	}

	/**
	 * Processing Actions on POST or GET requests in Public
	 */
	public function process_actions() {
		// We have an admin one.
		if ( is_admin() ) { return; }

		if ( isset( $_POST['ss-action'] ) ) {
			do_action( 'ss_' . $_POST['ss-action'], $_POST );
		}

		if ( isset( $_GET['ss-action'] ) ) {
			do_action( 'ss_' . $_GET['ss-action'], $_GET );
		}
	}

	public function run() {
		new Content_Types();
		new Shortcodes();
		new Form_Sponsors();

		// Registering the Databases to wpdb.
		$dbs = new Databases();
		$dbs->register();
	}
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