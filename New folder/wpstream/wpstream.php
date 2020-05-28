<?php
/**
 * Plugin Name:       WpStream
 * Plugin URI:        http://wpstream.net
 * Description:       WpStream is a WordPress plugin to help you stream & monetize media content.
 * Version:           3.2.0
 * Author:            wpstream
 * Author URI:        http://wpstream.net
 * Text Domain:       wpstream
 * Domain Path:       /languages
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}
define('WPSTREAM_CLUBLINK', 'wpstream.net');
define('WPSTREAM_CLUBLINKSSL', 'https');
define('WPSTREAM_PLUGIN_URL',  plugins_url() );
define('WPSTREAM_PLUGIN_DIR_URL',  plugin_dir_url(__FILE__) );
define('WPSTREAM_PLUGIN_PATH',  plugin_dir_path(__FILE__) );
define('WPSTREAM_PLUGIN_BASE',  plugin_basename(__FILE__) );
/**
 * Currently plugin version.
 * Start at version 3.0.1 and use SemVer - https://semver.org
 * Rename this for your plugin and update it as you release new versions.
 */
define( 'WPSTREAM_VERSION', '3.0.1' );

/**
 * The code that runs during plugin activation.
 * This action is documented in includes/class-wpstream-activator.php
 */
function activate_wpstream() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-wpstream-activator.php';
	Wpstream_Activator::activate();
}

/**
 * The code that runs during plugin deactivation.
 * This action is documented in includes/class-wpstream-deactivator.php
 */
function deactivate_wpstream() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-wpstream-deactivator.php';
	Wpstream_Deactivator::deactivate();
}

register_activation_hook( __FILE__, 'activate_wpstream' );
register_deactivation_hook( __FILE__, 'deactivate_wpstream' );

/**
 * The core plugin class that is used to define internationalization,
 * admin-specific hooks, and public-facing site hooks.
 */
require plugin_dir_path( __FILE__ ) . 'includes/class-wpstream.php';

/**
 * Begins execution of the plugin.
 *
 * Since everything within the plugin is registered via hooks,
 * then kicking off the plugin from this point in the file does
 * not affect the page life cycle.
 *
 * @since    3.0.1
 */
//function run_wpstream() {
//
//	$plugin = new Wpstream();
//	$plugin->run();
//
//}
//run_wpstream();
global $wpstream_plugin;
$wpstream_plugin = new Wpstream();
$wpstream_plugin->run();