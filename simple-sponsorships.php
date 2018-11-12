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
	 * @var string
	 */
	public $version = '0.1.0';

	/**
	 * Plugin constructor.
	 */
	public function __construct() {
		$this->define();
		$this->includes();
		$this->hooks();

		register_activation_hook( __FILE__, array( '\Simple_Sponsorships\Installer', 'activate' ) );
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

		include_once 'includes/class-content-types.php';
		include_once 'includes/class-installer.php';

		// DB
		include_once 'includes/class-dbs.php';
		include_once 'includes/db/class-db-levels.php';
		include_once 'includes/db/class-db-sponsorships.php';

		if ( is_admin() ) {
			include_once 'includes/admin/class-admin.php';
		}
	}

	/**
	 * Hooking
	 */
	public function hooks() {
		add_action( 'plugins_loaded', array( $this, 'run' ) );
	}

	public function run() {
		new Content_Types();

		// Registering the Databases to wpdb.
		$dbs = new Databases();
		$dbs->register();
	}
}

new Plugin();