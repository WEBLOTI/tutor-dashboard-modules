<?php
/**
 * Plugin Name: Tutor Dashboard Modules
 * Description: Modular student dashboard endpoints for Tutor LMS managed from WordPress admin.
 * Version: 0.1.0
 * Author: Booming
 * Author URI: https://www.instagram.com/madebybooming/
 * Text Domain: tutor-dashboard-modules
 * Domain Path: /languages
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'TDM_VERSION', '0.1.0' );
define( 'TDM_FILE', __FILE__ );
define( 'TDM_PATH', plugin_dir_path( __FILE__ ) );
define( 'TDM_URL', plugin_dir_url( __FILE__ ) );

spl_autoload_register(
	static function ( $class ) {
		$prefix = 'TDM\\';
		if ( strpos( $class, $prefix ) !== 0 ) {
			return;
		}

		$relative = substr( $class, strlen( $prefix ) );
		$relative = str_replace( '\\', DIRECTORY_SEPARATOR, $relative );
		$file     = TDM_PATH . 'src/' . $relative . '.php';

		if ( file_exists( $file ) ) {
			require_once $file;
		}
	}
);

if ( ! function_exists( 'tdm' ) ) {
	/**
	 * Retrieve the plugin singleton.
	 *
	 * @return \TDM\Plugin
	 */
	function tdm() {
		return \TDM\Plugin::instance();
	}
}

register_activation_hook( TDM_FILE, array( 'TDM\\Plugin', 'activate' ) );
register_deactivation_hook( TDM_FILE, array( 'TDM\\Plugin', 'deactivate' ) );

add_action(
	'plugins_loaded',
	static function () {
		tdm()->boot();
	},
	20
);
