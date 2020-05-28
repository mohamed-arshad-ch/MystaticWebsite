<?php

/**
 * Define the internationalization functionality
 *
 * Loads and defines the internationalization files for this plugin
 * so that it is ready for translation.
 *
 * @link       http://wpstream.net
 * @since      3.0.1
 *
 * @package    Wpstream
 * @subpackage Wpstream/includes
 */

/**
 * Define the internationalization functionality.
 *
 * Loads and defines the internationalization files for this plugin
 * so that it is ready for translation.
 *
 * @since      3.0.1
 * @package    Wpstream
 * @subpackage Wpstream/includes
 * @author     wpstream <office@wpstream.net>
 */
class Wpstream_i18n {


	/**
	 * Load the plugin text domain for translation.
	 *
	 * @since    3.0.1
	 */
	public function load_plugin_textdomain() {

		load_plugin_textdomain(
			'wpstream',
			false,
			dirname( dirname( plugin_basename( __FILE__ ) ) ) . '/languages/'
		);

	}



}
