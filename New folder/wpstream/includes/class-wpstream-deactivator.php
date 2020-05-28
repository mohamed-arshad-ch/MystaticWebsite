<?php

/**
 * Fired during plugin deactivation
 *
 * @link       http://wpstream.net
 * @since      3.0.1
 *
 * @package    Wpstream
 * @subpackage Wpstream/includes
 */

/**
 * Fired during plugin deactivation.
 *
 * This class defines all code necessary to run during the plugin's deactivation.
 *
 * @since      3.0.1
 * @package    Wpstream
 * @subpackage Wpstream/includes
 * @author     wpstream <office@wpstream.net>
 */
class Wpstream_Deactivator {

	/**
	 * Short Description. (use period)
	 *
	 * Long Description.
	 *
	 * @since    3.0.1
	 */
	public static function deactivate() {
            delete_option('wp_estate_token_expire');
            delete_option('wp_estate_curent_token');
            delete_option('wpstream_api_key');
            delete_option('wpstream_api_secret_key');
            delete_option('wpstream_api_username');
            delete_option('wpstream_api_password');

            flush_rewrite_rules();
	}

}
