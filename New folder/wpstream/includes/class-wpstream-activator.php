<?php

/**
 * Fired during plugin activation
 *
 * @link       http://wpstream.net
 * @since      3.0.1
 *
 * @package    Wpstream
 * @subpackage Wpstream/includes
 */

/**
 * Fired during plugin activation.
 *
 * This class defines all code necessary to run during the plugin's activation.
 *
 * @since      3.0.1
 * @package    Wpstream
 * @subpackage Wpstream/includes
 * @author     wpstream <office@wpstream.net>
 */
class Wpstream_Activator {

	/**
	 * Short Description. (use period)
	 *
	 * Long Description.
	 *
	 * @since    3.0.1
	 */
	public static function activate() {
        
            require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-wpstream_product.php';
            $plugin_post_types = new Wpstream_Product();
            $plugin_post_types->create_custom_post_type();
    
            flush_rewrite_rules();
          
//            
            
	}

}
