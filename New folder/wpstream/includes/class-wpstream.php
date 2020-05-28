<?php

/**
 * The file that defines the core plugin class
 *
 * A class definition that includes attributes and functions used across both the
 * public-facing side of the site and the admin area.
 *
 * @link       http://wpstream.net
 * @since      3.0.1
 *
 * @package    Wpstream
 * @subpackage Wpstream/includes
 */

/**
 * The core plugin class.
 *
 * This is used to define internationalization, admin-specific hooks, and
 * public-facing site hooks.
 *
 * Also maintains the unique identifier of this plugin as well as the current
 * version of the plugin.
 *
 * @since      3.0.1
 * @package    Wpstream
 * @subpackage Wpstream/includes
 * @author     wpstream <office@wpstream.net>
 */
class Wpstream {

        /**
        * Store plugin main class to allow public access.
        *
        * @since             3.0.1
        * @var object      The main class.
        */
        public $main;

	/**
	 * The loader that's responsible for maintaining and registering all hooks that power
	 * the plugin.
	 *
	 * @since    3.0.1
	 * @access   protected
	 * @var      Wpstream_Loader    $loader    Maintains and registers all hooks for the plugin.
	 */
	protected $loader;

	/**
	 * The unique identifier of this plugin.
	 *
	 * @since    3.0.1
	 * @access   protected
	 * @var      string    $plugin_name    The string used to uniquely identify this plugin.
	 */
	protected $plugin_name;

	/**
	 * The current version of the plugin.
	 *
	 * @since    3.0.1
	 * @access   protected
	 * @var      string    $version    The current version of the plugin.
	 */
	protected $version;

	/**
	 * Define the core functionality of the plugin.
	 *
	 * Set the plugin name and the plugin version that can be used throughout the plugin.
	 * Load the dependencies, define the locale, and set the hooks for the admin area and
	 * the public-facing side of the site.
	 *
	 * @since    3.0.1
	 */
        
        public $wpstream_live_connection;
        public $wpstream_player;
        public $xtest;
        public $plugin_admin;
        
	public function __construct() {
                $this->main = $this;
		if ( defined( 'WPSTREAM_VERSION' ) ) {
                    $this->version = WPSTREAM_VERSION;
		} else {
                    $this->version = '3.0.1';
		}
		$this->plugin_name = 'wpstream';

		$this->load_dependencies();
		$this->set_locale();
		$this->define_admin_hooks();
		$this->define_public_hooks();
                
                $this->xtest = "this is test2";
                $this->wpstream_conection();
                $this->wpstream_player();

	}

        
        
        
        
        public function wpstream_convert_band($megabits){
            $gigabit    =   $megabits   *   0.001;
            $gigabit    =   number_format($gigabit,2);
            return $gigabit;
        }

        
        
        
        
        private function wpstream_conection(){
            require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-wpstream-live-api-connection.php';
            $this->wpstream_live_connection = new Wpstream_Live_Api_Connection();
        }
        
        
        private function wpstream_player(){
            require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-wpstream-player.php';
            $this->wpstream_player = new Wpstream_Player($this->main);
        }
        
        
        
        
	/**
	 * Load the required dependencies for this plugin.
	 *
	 * Include the following files that make up the plugin:
	 *
	 * - Wpstream_Loader. Orchestrates the hooks of the plugin.
	 * - Wpstream_i18n. Defines internationalization functionality.
	 * - Wpstream_Admin. Defines all hooks for the admin area.
	 * - Wpstream_Public. Defines all hooks for the public side of the site.
	 *
	 * Create an instance of the loader which will be used to register the hooks
	 * with WordPress.
	 *
	 * @since    3.0.1
	 * @access   private
	 */
	private function load_dependencies() {

		/**
		 * The class responsible for orchestrating the actions and filters of the
		 * core plugin.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-wpstream-loader.php';

		/**
		 * The class responsible for defining internationalization functionality
		 * of the plugin.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-wpstream-i18n.php';

		/**
		 * The class responsible for defining all actions that occur in the admin area.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'admin/class-wpstream-admin.php';

		/**
		 * The class responsible for defining all actions that occur in the public-facing
		 * side of the site.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'public/class-wpstream-public.php';
                
                /**
		 * The class responsible for custom post type
		
                 */
                require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-wpstream_product.php';
                if(  class_exists( 'WooCommerce' ) ){
                    require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-wc_product_live_stream.php';
                    require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-wc_product_video_on_demand.php';
                }

		$this->loader = new Wpstream_Loader();

	}

	/**
	 * Define the locale for this plugin for internationalization.
	 *
	 * Uses the Wpstream_i18n class in order to set the domain and to register the hook
	 * with WordPress.
	 *
	 * @since    3.0.1
	 * @access   private
	 */
	private function set_locale() {

		$plugin_i18n = new Wpstream_i18n();

		$this->loader->add_action( 'plugins_loaded', $plugin_i18n, 'load_plugin_textdomain' );

	}

	/**
	 * Register all of the hooks related to the admin area functionality
	 * of the plugin.
	 *
	 * @since    3.0.1
	 * @access   private
	 */
	private function define_admin_hooks() {

  $this->admin=                $plugin_admin = new Wpstream_Admin( $this->get_plugin_name(), $this->get_version(), $this->main );

		$this->loader->add_action( 'admin_enqueue_scripts', $plugin_admin,  'enqueue_styles' );
		$this->loader->add_action( 'admin_enqueue_scripts', $plugin_admin,  'enqueue_scripts' );
                $this->loader->add_action( 'admin_menu',            $plugin_admin,  'wpstream_manage_admin_menu',999);
                                  
                $plugin_post_types = new Wpstream_Product();
                $this->loader->add_action( 'init', $plugin_post_types, 'create_custom_post_type', 999 );
                
                // save and render metaboxed
                $this->loader->add_action( 'admin_init',    $plugin_admin, 'add_wpstream_product_metaboxes' );
                $this->loader->add_action( 'save_post',     $plugin_admin, 'wpstream_free_product_update_post',1,2 );              
           
                // show streaming controls on sidebar
                $this->loader->add_action('add_meta_boxes', $plugin_admin, 'wpstream_startstreaming_sidebar_meta');
                
                
                $this->loader->add_action( 'admin_notices',                         $plugin_admin,'wpstream_admin_notice' );
                $this->loader->add_action( 'wp_ajax_wpstream_update_cache_notice',  $plugin_admin,'wpstream_update_cache_notice' );
                
                // add and save category extra fields
                $this->loader->add_action( 'category_edit_form_fields',  $plugin_post_types,   'wpstream_category_callback_function', 10, 2);
                $this->loader->add_action( 'category_add_form_fields',   $plugin_post_types,   'wpstream_category_callback_add_function', 10, 2 );  
                $this->loader->add_action( 'created_category',           $plugin_post_types,   'wpstream_category_save_extra_fields_callback', 10, 2);
                $this->loader->add_action( 'edited_category',            $plugin_post_types,   'wpstream_category_save_extra_fields_callback', 10, 2);

                $this->loader->add_action( 'product_cat_edit_form_fields',  $plugin_post_types,  'wpstream_category_callback_function', 10, 2);
                $this->loader->add_action( 'product_cat_add_form_fields',   $plugin_post_types,  'wpstream_category_callback_add_function', 10, 2 );  
                $this->loader->add_action( 'created_product_cat',           $plugin_post_types,  'wpstream_category_save_extra_fields_callback', 10, 2);
                $this->loader->add_action( 'edited_product_cat',            $plugin_post_types,  'wpstream_category_save_extra_fields_callback', 10, 2);


                $this->loader->add_action( 'wpstream_category_edit_form_fields', $plugin_post_types,   'wpstream_category_callback_function', 10, 2);
                $this->loader->add_action( 'wpstream_category_add_form_fields',  $plugin_post_types,   'wpstream_category_callback_add_function', 10, 2 );  
                $this->loader->add_action( 'created_wpstream_category',          $plugin_post_types,   'wpstream_category_save_extra_fields_callback', 10, 2);
                $this->loader->add_action( 'edited_wpstream_category',           $plugin_post_types,   'wpstream_category_save_extra_fields_callback', 10, 2);


                $this->loader->add_action( 'wpstream_actors_edit_form_fields',  $plugin_post_types,   'wpstream_category_callback_function', 10, 2);
                $this->loader->add_action( 'wpstream_actors_add_form_fields',   $plugin_post_types,   'wpstream_category_callback_add_function', 10, 2 );  
                $this->loader->add_action( 'created_wpstream_actors',           $plugin_post_types,   'wpstream_category_save_extra_fields_callback', 10, 2);
                $this->loader->add_action( 'edited_wpstream_actors',            $plugin_post_types,   'wpstream_category_save_extra_fields_callback', 10, 2);

                $this->loader->add_action( 'wpstream_movie_rating_edit_form_fields',  $plugin_post_types,   'wpstream_category_callback_function', 10, 2);
                $this->loader->add_action( 'wpstream_movie_rating_add_form_fields',   $plugin_post_types,   'wpstream_category_callback_add_function', 10, 2 );  
                $this->loader->add_action( 'created_wpstream_movie_rating',           $plugin_post_types,   'wpstream_category_save_extra_fields_callback', 10, 2);
                $this->loader->add_action( 'edited_wpstream_movie_rating',            $plugin_post_types,   'wpstream_category_save_extra_fields_callback', 10, 2);
                
         
          
                       
                if ( in_array( 'woocommerce/woocommerce.php', apply_filters( 'active_plugins', get_option( 'active_plugins' ) ) ) ) {
              
                    $this->loader->add_filter( 'product_type_selector',         $plugin_admin, 'wpstream_add_products' );
                    $this->loader->add_action( 'admin_footer',                  $plugin_admin, 'wpstream_products_custom_js' );
                    $this->loader->add_filter( 'woocommerce_product_data_tabs', $plugin_admin, 'wpstream_hide_attributes_data_panel',10,1 );
                    
                    $this->loader->add_filter( 'woocommerce_product_options_general_product_data',$plugin_admin, 'wpstream_add_custom_general_fields', 10,1);
                    $this->loader->add_filter( 'woocommerce_process_product_meta',$plugin_admin, 'wpstream_add_custom_general_fields_save',10,1 );
                    $this->loader->add_action( 'woocommerce_live_stream_add_to_cart', $plugin_admin, 'wpstream_add_to_cart',10,1);
                    $this->loader->add_action( 'woocommerce_video_on_demand_add_to_cart', $plugin_admin, 'wpstream_add_to_cart',10,1);
                    $this->loader->add_filter( 'woocommerce_loop_add_to_cart_link', $plugin_admin,'replacing_add_to_cart_button', 10, 2 );
                }
                
                
                
                


	}

	/**
	 * Register all of the hooks related to the public-facing functionality
	 * of the plugin.
	 *
	 * @since    3.0.1
	 * @access   private
	 */
	private function define_public_hooks() {

		$plugin_public = new Wpstream_Public( $this->get_plugin_name(), $this->get_version(), $this->main );

		$this->loader->add_action( 'wp_enqueue_scripts', $plugin_public, 'enqueue_styles' );
		$this->loader->add_action( 'wp_enqueue_scripts', $plugin_public, 'enqueue_scripts' );
                
                $this->loader->add_action( 'init', $plugin_public,'wpstream_my_custom_endpoints' );
                $this->loader->add_filter( 'query_vars',$plugin_public, 'wpstream_my_custom_query_vars', 0 );
              
                //live stream action                
                $this->loader->add_action('init',$plugin_public,'wpstream_set_cookies',0);
                $this->loader->add_action('init',$plugin_public,'wpstream_live_streaming_key');
                $this->loader->add_action('init',$plugin_public,'wpstream_live_streaming_key_for_3rdparty');
                $this->loader->add_action('init',$plugin_public,'wpstream_live_streaming_key_vod');
                // woo action
                
                $this->loader->add_action( 'woocommerce_before_single_product', $plugin_public,'wpstream_non_image_content_wrapper_start', 20 );
                $this->loader->add_action( 'woocommerce_after_single_product', $plugin_public,'wpstream_non_image_content_wrapper_end', 20 );
                
                $this->loader->add_filter( 'woocommerce_account_menu_items', $plugin_public,'wpstream_custom_my_account_menu_items' );
                $this->loader->add_action( 'woocommerce_account_event-list_endpoint', $plugin_public,'wpstream_custom_endpoint_content_event_list' );
                $this->loader->add_action( 'woocommerce_account_video-list_endpoint', $plugin_public,'wpstream_custom_endpoint_video_list' );
                

                $this->loader->add_action( 'after_switch_theme', $plugin_public,'wpstream_custom_flush_rewrite_rules' );
                $this->loader->add_action('init', $plugin_public,'wpstream_shortcodes');

//                // rewrite urls
//                $this->loader->add_action('init', $plugin_public,'wpstream_custom_rewrite_tag', 10, 0);
//                $this->loader->add_action('init', $plugin_public,'wpstream_custom_rewrite_rule', 10, 0);
//                 
//                $this->loader->add_filter( 'page_template',$plugin_public, 'wpstream_userkey_page_template' );
//                $this->loader->add_filter( 'page_template',$plugin_public, 'wpstream_vodkey_page_template' );
                
                //api
                
                
                $this->loader->add_action('wo_before_api', 'wpstream_cors_check_and_response',10,1);
                
              
                
	}

	/**
	 * Run the loader to execute all of the hooks with WordPress.
	 *
	 * @since    3.0.1
	 */
	public function run() {
		$this->loader->run();
	}

	/**
	 * The name of the plugin used to uniquely identify it within the context of
	 * WordPress and to define internationalization functionality.
	 *
	 * @since     3.0.1
	 * @return    string    The name of the plugin.
	 */
	public function get_plugin_name() {
		return $this->plugin_name;
	}

	/**
	 * The reference to the class that orchestrates the hooks with the plugin.
	 *
	 * @since     3.0.1
	 * @return    Wpstream_Loader    Orchestrates the hooks of the plugin.
	 */
	public function get_loader() {
		return $this->loader;
	}

	/**
	 * Retrieve the version number of the plugin.
	 *
	 * @since     3.0.1
	 * @return    string    The version number of the plugin.
	 */
	public function get_version() {
            return $this->version;
	}
        
        
      
        public function show_user_data($pack_details){
            if( isset($pack_details['band']) && isset( $pack_details['storage']) ){
                $wpstream_convert_band      =   $this->wpstream_convert_band($pack_details['band']);
                if($wpstream_convert_band<0)$wpstream_convert_band=0;
                
                $wpstream_convert_storage =   $this->wpstream_convert_band($pack_details['storage']);
                if($wpstream_convert_storage<0)$wpstream_convert_storage=0;
                
                print '<div class="pack_details_wrapper"><strong>'.__('Your account information: ','wpstream').'</strong> '.__('You have','wpstream').'<strong> '.$wpstream_convert_band.' Gb</strong> '.__('available streaming bandwidth and','wpstream').' <strong>'.$wpstream_convert_storage.' Gb</strong> '.__('available media storage','wpstream').'.</div>';
                print'<input type="hidden" id="wpstream_band" value="'.$pack_details['band'].'">';
                print'<input type="hidden" id="wpstream_storage" value="'.$pack_details['storage'].'">';

            }
        }

        public function wpstream_insert_player_elementor($attributes, $content = null){
                $product_id     =   '';
                $return_string  =   '';
                $attributes =   shortcode_atts( 
                    array(
                        'id'                       => 0,
                    ), $attributes) ;


                if ( isset($attributes['id']) ){
                    $product_id=$attributes['id'];
                }

                ob_start();
                $this->main->wpstream_player->wpstream_video_player_shortcode($product_id);
                $return_string= ob_get_contents();
                ob_end_clean(); 

                return $return_string;
        }
        
        
        

}
