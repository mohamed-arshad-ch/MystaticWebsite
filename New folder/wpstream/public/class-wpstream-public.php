<?php

/**
 * The public-facing functionality of the plugin.
 *
 * @link       http://wpstream.net
 * @since      3.0.1
 *
 * @package    Wpstream
 * @subpackage Wpstream/public
 */

/**
 * The public-facing functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the public-facing stylesheet and JavaScript.
 *
 * @package    Wpstream
 * @subpackage Wpstream/public
 * @author     wpstream <office@wpstream.net>
 */
class Wpstream_Public {

    
        
        /**
         * Store plugin main class to allow public access.
         *
         * @since    20180622
         * @var object      The main class.
         */
        public $main;
	/**
	 * The ID of this plugin.
	 *
	 * @since    3.0.1
	 * @access   private
	 * @var      string    $plugin_name    The ID of this plugin.
	 */
	private $plugin_name;

	/**
	 * The version of this plugin.
	 *
	 * @since    3.0.1
	 * @access   private
	 * @var      string    $version    The current version of this plugin.
	 */
	private $version;

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since    3.0.1
	 * @param      string    $plugin_name       The name of the plugin.
	 * @param      string    $version    The version of this plugin.
	 */
	public function __construct( $plugin_name, $version ,$plugin_main) {
                $this->main = $plugin_main;
		$this->plugin_name = $plugin_name;
		$this->version = $version;

	}

	/**
	 * Register the stylesheets for the public-facing side of the site.
	 *
	 * @since    3.0.1
	 */
	public function enqueue_styles() {

		/**
		 * This function is provided for demonstration purposes only.
		 *
		 * An instance of this class should be passed to the run() function
		 * defined in Wpstream_Loader as all of the hooks are defined
		 * in that particular class.
		 *
		 * The Wpstream_Loader will then create the relationship
		 * between the defined hooks and the functions defined in this
		 * class.
		 */

                wp_enqueue_style('wpstream-style',          plugin_dir_url( __FILE__ ) .'/css/wpstream_style.css' );
                wp_enqueue_style('video-js.min',            plugin_dir_url( __FILE__ ).'/css/video-js.min.css', array(), '1.0', 'all');

	}

	/**
	 * Register the JavaScript for the public-facing side of the site.
	 *
	 * @since    3.0.1
	 */
	public function enqueue_scripts() {

		/**
		 * This function is provided for demonstration purposes only.
		 *
		 * An instance of this class should be passed to the run() function
		 * defined in Wpstream_Loader as all of the hooks are defined
		 * in that particular class.
		 *
		 * The Wpstream_Loader will then create the relationship
		 * between the defined hooks and the functions defined in this
		 * class.
		 */

	
            
                wp_enqueue_script('video.min',              plugin_dir_url( __FILE__ ).'js/video.min.js',array('jquery'), '1.0', false);
                wp_enqueue_script('youtube.min',            plugin_dir_url( __FILE__ ).'js/youtube.min.js',array('video.min'), '1.0', false);
                wp_enqueue_script('videojs-vimeo.min',      plugin_dir_url( __FILE__ ).'js/videojs-vimeo.min.js',array('video.min'), '1.0', false);    
                wp_enqueue_script('wpstream-player',        plugin_dir_url( __FILE__ ).'js/wpstream-player.js',array('video.min'), '1.0', false);
    

	}
        
        
      
        /**
	 * add custom end points for woocomerce
	 *
	 * @since     3.0.1
	 * @return    nothing
        */
        public function wpstream_my_custom_endpoints() {
            add_rewrite_endpoint( 'video-list', EP_ROOT | EP_PAGES );
            add_rewrite_endpoint( 'event-list', EP_ROOT | EP_PAGES );
        }

        /**
	 * add custom query vars
	 *
	 * @since     3.0.1
	 * @return    nothing
        */
        public function wpstream_my_custom_query_vars( $vars ) {
            $vars[] = 'video-list';
            $vars[] = 'event-list';
            return $vars;
        }


        /**
	 * Hust flush rewrite rules
	 *
	 * @since     3.0.1
	 * 
	 */
        public function wpstream_custom_flush_rewrite_rules() {
            flush_rewrite_rules();
        }


        /**
	 * Add new sections in woocomerce account
	 *
	 * @since     3.0.1
	*/
        public function wpstream_custom_my_account_menu_items( $items ) {
            if(function_exists('wpstream_is_global_subscription') && wpstream_is_global_subscription()){
                $items = array(
                    'dashboard'         => __( 'Dashboard', 'woocommerce' ),
                    'orders'            => __( 'Orders', 'woocommerce' ),
                    'edit-address'      => __( 'Addresses', 'woocommerce' ),
                    'edit-account'      => __( 'Edit Account', 'woocommerce' ),
                    'customer-logout'   => __( 'Logout', 'woocommerce' ),
                );
            }else{
                $items = array(
                    'dashboard'         => __( 'Dashboard', 'woocommerce' ),
                    'orders'            => __( 'Orders', 'woocommerce' ),
                    'edit-address'      => __( 'Addresses', 'woocommerce' ),
                    'edit-account'      => __( 'Edit Account', 'woocommerce' ), 
                    'event-list'        => __( 'Events', 'wpstream' ),
                    'video-list'        => __( 'Videos', 'wpstream' ),
                    'customer-logout'   => __( 'Logout', 'woocommerce' ),
                );
            }
            return $items;
    }
    
    
    
        /**
	 * Add new endpoint
	 *
	 * @since     3.0.1
	*/
        public function wpstream_custom_endpoint_content_event_list() {
            include plugin_dir_path( __DIR__ ).'woocommerce/myaccount/event_list.php';
        }


        /**
	 * Add new endpoint
	 *
	 * @since     3.0.1
	*/
        public function wpstream_custom_endpoint_video_list() {
            include plugin_dir_path( __DIR__ ).'woocommerce/myaccount/video_list.php';
        }

        
        
        
     
        
        /**
	 * register shortcodes
	 *
	 * @since     3.0.1
         * 
	*/
        public function wpstream_shortcodes(){
            add_shortcode('wpstream_player',        array($this,'wpstream_insert_player_inpage_local') );
            add_shortcode('wpstream_list_products', array($this,'wpstream_list_products_function') );

            
            // register shortcodes for visual composer  
            if( function_exists('vc_map') ):
                vc_map(
                    array(
                       "name" => esc_html__( "WpStream Player","wpestate"),
                       "base" => "wpstream_player",
                       "class" => "",
                       "category" => esc_html__( 'WpStream','wpestate'),
                       'admin_enqueue_js' => array(get_template_directory_uri().'/vc_extend/bartag.js'),
                       'admin_enqueue_css' => array(get_template_directory_uri().'/vc_extend/bartag.css'),
                       'weight'=>100,
                       'icon'   =>'',
                       'description'=>esc_html__( 'Insert WpStream Player','wpestate'),
                       "params" => array(
                            array(
                                "type" => "textfield",
                                "holder" => "div",
                                "class" => "",
                                "heading" => esc_html__( "Product/Free Product Id","wpestate"),
                                "param_name" => "id",
                                "value" => "0",
                                "description" => esc_html__( "Add here the live stream id or the video id","wpestate")
                            ),

                       )
                    )
                );



                $product_type=array(
                        '1' =>  __('Free Live Channel','wpstream'),
                        '2' =>  __('Free Video','wpstream')
                );

                vc_map(
                array(
                   "name" => esc_html__( "WpStream Products List","wpestate"),
                   "base" => "wpstream_list_products",
                   "class" => "",
                   "category" => esc_html__( 'WpStream','wpestate'),
                   'admin_enqueue_js' => array(get_template_directory_uri().'/vc_extend/bartag.js'),
                   'admin_enqueue_css' => array(get_template_directory_uri().'/vc_extend/bartag.css'),
                   'weight'=>100,
                   'icon'   =>'',
                   'description'=>esc_html__( ' List wpstream products','wpestate'),
                   "params" => array(
                        array(
                             "type" => "textfield",
                             "holder" => "div",
                             "class" => "",
                             "heading" => esc_html__( "Media number","wpestate"),
                             "param_name" => "media_number",
                             "value" => "",
                             "description" => esc_html__( "No of media ","wpestate")
                         ),

                        array(
                            "type" => "dropdown",
                            "holder" => "div",
                            "class" => "",
                            "heading" => esc_html__( "Product type","wpestate"),
                            "param_name" => "product_type",
                            "value" => $product_type,
                            "description" => esc_html__( "What type of products ","wpestate")
                        ),

                   )
                )
                );
            endif;
            
            
            // add shorcotes to editor interface
            if (!current_user_can('edit_posts') && !current_user_can('edit_pages')) {
                return;
            }

            if (get_user_option('rich_editing') == 'true') {
                add_filter('mce_external_plugins', array( $this,'wpstream_add_plugin') );
                add_filter('mce_buttons_2', array($this,'wpstream_register_button') );    
            }
        }
        
         
           
        /**
	 * register shortcodes - add buttons in js
	 *
	 * @since     3.0.1
         * 
	*/
        
        
        public function wpstream_insert_player_inpage_local($attributes, $content = null){
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

        
        
        
        /**
	 * list products - shortcode function
	 *
	 * @since     3.0.1
         * 
	*/
        
        public function wpstream_list_products_function($atts, $content=null){

                $media_number     = "";  
                $product_type     = ""; 
                $attributes = shortcode_atts(
                        array(
                                'media_number' =>   '4',
                                'product_type' =>   __('Free Live Channel','wpstream'),

                        ), $atts);

                if ( isset($attributes['media_number']) ){
                    $media_number=$attributes['media_number'];
                }

                if ( isset($attributes['product_type']) ){
                    $product_type=$attributes['product_type'];
                }

                if($product_type== __('Free Live Channel','wpstream') ){
                    $product_type=1;
                }else{
                    $product_type=2;
                }

                $return_string=""; 



                $args = array(
                    'post_type'      => 'wpstream_product',
                    'post_status'    => 'publish',
                    'meta_query'     =>array(
                                        array(
                                        'key'      => 'wpstream_product_type',
                                        'value'    => $product_type,
                                        'compare'  => '=',
                                        ),
                        ),
                    'posts_per_page' =>$media_number,
                    'page'          => 1
                );

              
                $media_list= new WP_Query($args);

                if($product_type==1){
                    $see_product= __('See Free Live Chanel','wpstream');
                }else{
                    $see_product =_('See Free Video','wpstream');
                }



                while($media_list->have_posts()):$media_list->the_post();
                    $return_string.='<div class="wpstream_product_unit">'
                    .'<div class="product_image" style="background-image:url('.wp_get_attachment_thumb_url(get_post_thumbnail_id()).')"></div>'
                    .'<a href="'.get_permalink().'" class="product_title" >'.get_the_title().'</a>'
                    .'<a href="'.get_permalink().'"class="see_product">'.$see_product.'</a>'
                    .'</div>';
                endwhile;

                wp_reset_postdata();
                wp_reset_query();


                return   '<div class="shortcode_list_wrapper">'.$return_string.'</div>';

        }

        
        
        /**
	 * register shortcodes - add buttons in js
	 *
	 * @since     3.0.1
         * 
	*/
        
        public function wpstream_add_plugin($plugin_array) {   
            $plugin_array['wpstream_player']                = plugin_dir_url( __FILE__ ). '/js/shortcodes.js';
            $plugin_array['wpstream_list_products']         = plugin_dir_url( __FILE__ ). '/js/shortcodes.js';
            return $plugin_array;
        }
         
        /**
	 * register shortcodes - add buttons
	 *
	 * @since     3.0.1
         * 
	*/
        public function wpstream_register_button($buttons) {
            array_push($buttons, "|", "wpstream_player");
            array_push($buttons, "|", "wpstream_list_products");    
            return $buttons;
        }


        
        /**
	 * wpstream cors
	 *
	 * @since     3.0.1
         * 
	*/
        
        public function wpstream_cors_check_and_response(){
            if ($_SERVER['REQUEST_METHOD'] == "OPTIONS") {
                header('Access-Control-Allow-Methods: POST, GET');
                header('Access-Control-Allow-Headers: Authorization');
                header('Access-Control-Max-Age: 1');  //1728000
                header("Content-Length: 0");
                header("Content-Type: text/plain charset=UTF-8");
                exit(0);
            }
        }
        
        /**
	 * set user cookie
	 *
	 * @since     3.0.1
         * 
	*/

        public function wpstream_set_cookies(){
            if( !isset( $_REQUEST[ 'keys' ]) && !isset( $_REQUEST[ 'keys2' ]) ) {
                $cookie=uniqid ();
                setcookie("WpstreamNonLogCookie3", $cookie);
            }
        }
        
        /**
	 * get key for live stream
	 *
	 * @since     3.0.1
         * 
	*/
        
        
        public function wpstream_live_streaming_key(){
   
            if( isset( $_REQUEST[ 'keys' ]) && $_REQUEST[ 'keys' ]!='' ) {

                $streamname         =   esc_html($_REQUEST[ 'keys' ]);
                $current_user       =   wp_get_current_user();
                $stream_key         =   esc_html($streamname);
                $stream_key_array   =   explode('-', $stream_key);
                $real_stream_key    =   $stream_key_array[0];
                $server_ip          =   '';

                if ($server_ip=='' || false === ( $server_ip = get_transient( $server_ip.'_serverip' ) ) ) {   
                    $server_ip = $this->main->wpstream_live_connection->wpstream_get_live_stream_server($current_user,$streamname);
                    set_transient(  $server_ip.'_serverip', $server_ip, 600 );
                }

                $args_free = array(
                    'posts_per_page'    => -1,
                    'post_type'         => 'wpstream_product',
                    'post_status'       => 'publish',
                    'meta_query'        =>      array(
                                                    array(
                                                    'key'     => 'live_event_stream_name',
                                                    'value'   => $real_stream_key,
                                                    'compare' => '=',
                                                    )
                                                ),


                 );
                 $event_list_free = new WP_Query($args_free);


                if ($event_list_free->have_posts() ){
                        $event_list_free->the_post();    
                        $the_id     =   get_the_ID();
                        $event_data =   get_post_meta($the_id,'live_event_uri',true);
                        $ip         =   get_post_meta($the_id,'ip',true);

                        $show_id    =   $the_id;  


                        //if( isset( $_COOKIE['WpstreamNonLogCookie3'] ) ){ 

                            if ( false === ( $get_key = get_transient( $show_id.'_carnatus1x' ) ) ) {           
                                $get_key = $this->wpstream_get_encryption_key_remonting($stream_key,$server_ip);
                                set_transient(  $show_id.'_carnatus1x', $get_key, 30 );
                            }  
                            print $get_key;
                            die();
                           
//                        }
//                
//                        die('cookie not present');
                       
                }else{
                    //  this is for paid products
                    if ( is_user_logged_in() && intval($current_user->ID)!=0 ) {  

                            $args = array(
                                'posts_per_page'    => -1,
                                'post_type'         => 'product',
                                'post_status'       => 'publish',
                                'meta_query' => array(
                                    array(
                                            'key'     => 'live_event_stream_name',
                                            'value'   => $real_stream_key,
                                            'compare' => '=',
                                    ),
                                ),
                                'tax_query'         => array(
                                            'relation'  => 'AND',
                                            array(
                                                'taxonomy'  =>  'product_type',
                                                'field'     =>  'slug',
                                                'terms'     =>  array('live_stream','subscription')
                                            )
                                        ),
                            );


                            $event_list = new WP_Query($args);

                            if ($event_list->have_posts() ){
                                while ( $event_list->have_posts() ): 
                                    $event_list->the_post(); 
                                    $the_id     =   get_the_ID();
                                    $event_data =   get_post_meta($the_id,'live_event_uri',true);
                                    $ip         =   get_post_meta($the_id,'ip',true);
                                    $show_id    =   $the_id;

                                endwhile;

                                $is_valid_subscription=0;
                                if(class_exists ('WC_Subscription')){
                                    $is_valid_subscription = wcs_user_has_subscription( $current_user->ID, $show_id ,'active');
                                }


                                if(function_exists('wpstream_check_global_subscription_model')){
                                    if( wpstream_check_global_subscription_model() ){
                                        $is_valid_subscription=1;// this is global subscription
                                    }
                                }


                                if( wc_customer_bought_product( $current_user->email, $current_user->ID, $show_id) || $is_valid_subscription==1 ){     

                                   
                                    $get_key = $this->wpstream_get_encryption_key_remonting($stream_key,$server_ip);
                                       

                                    print $get_key;
                                    die();

                                }else{
                                    exit('live - no ticket ');
                                }

                            } else{
                                exit('live -no event');
                            }

                        }else{
                            exit('live- user not log or awserr');
                        }
                        
                }
                exit('no free or paid event');
                


            }else{
                return;
            }

        }
         
         
         
             
         /**
	 * get remote key for live
	 *
	 * @since     3.0.1
         * 
	*/
         
        public function wpstream_get_encryption_key_remonting ($stream_key,$server_ip){

              if ( false === ( $get_key = get_transient( $server_ip.'_carnatus1x' ) ) ) {           
                    $url= 'http://'.$server_ip.':80/keys/'.$stream_key;
                    $get= wp_remote_get( $url );

                    if(is_array($get)){
                        $get_key = $get['body'];
                    }else{
                       $get_key='';
                    }
                    
                    set_transient(  $server_ip.'_carnatus1x', $get_key, 30 );
                }
                return $get_key;
        }

         
         
         /**
	 * get key for 3rdparty
	 *
	 * @since     3.0.1
         * 
	*/
        
        
        public function wpstream_live_streaming_key_for_3rdparty(){
   
            if( isset( $_REQUEST[ 'thirdkeys' ]) && $_REQUEST[ 'thirdkeys' ]!='' ) {
            
                $thirdkeys         =   esc_html($_REQUEST[ 'thirdkeys' ]);
              
                //live_event_carnat2
                
                $args = array(
                    'post_type'      => array('product','wpstream_product'),
                    'post_status'    => 'publish',
                    'meta_query'     =>array(
                                        array(
                                        'key'      => 'live_event_carnat2',
                                        'value'    => $thirdkeys,
                                        'compare'  => '=',
                                        ),
                        ),
                    
                  
                );

         
                $media_list= new WP_Query($args);
                if($media_list->have_posts()){
                    while($media_list->have_posts()):$media_list->the_post();
                
                        $media_id       =   get_the_ID();
                        $replay_array   =   array(
                           // '', // fb will be here
                            stripslashes( get_post_meta($media_id,'wpstream_youtube_rtmp',true )),
                            stripslashes( get_post_meta($media_id,'wpstream_twich_rtmp',true) ),
                        );
                        
                        $reply_final=array('rtmp_urls'=>$replay_array);
                        header('Content-Type: application/json;charset=utf-8');
                        print json_encode($reply_final,JSON_UNESCAPED_SLASHES);
                        die();
                        
                        
                    endwhile;
                }else{
                    print'{}';
                    die('');
                }
                
            }

         }
        
          
         
         /**
	 * get key for vod
	 *
	 * @since     3.0.1
         * 
	*/
        
        
        public function wpstream_live_streaming_key_vod(){
   
            if( isset( $_REQUEST[ 'keys2' ]) && $_REQUEST[ 'keys2' ]!='' ) {
                global $wp_query; 
                $current_user   =   wp_get_current_user();
                

                $keys2        =   esc_html($_REQUEST[ 'keys2' ]);
               
         
                $keys2= ltrim($keys2,"/");
                $keys2= rtrim($keys2,"/");
                $keys2_array=explode('/',$keys2);
                
                
                $folder=$keys2_array[0];
                $movie=$keys2_array[1];
                
                //  $folder         =   sanitize_text_field( $wp_query->query_vars['streamname'] );
               // $movie          =   sanitize_text_field ( $wp_query->query_vars['streamname2'] );   
                
                $free_args = array(
                    'posts_per_page'    => -1,
                    'post_type'         => 'wpstream_product',
                    'meta_query' => array(
                        array(
                            'key'     => 'wpstream_free_video',
                            'value'   => $movie,
                            'compare' => '=',
                        ),
                    )
                );


                $free_video_list = new WP_Query($free_args);

                if ($free_video_list->have_posts() ){
                    //if( isset( $_COOKIE['WpstreamNonLogCookie3'] ) ){ 
                        $get_key = $this->wpstream_get_vod_key($folder.'/'.$movie);
                        echo ($get_key);
                      
                    //}
                     die();
                }else{
                    if ( is_user_logged_in() && intval($current_user->ID)!=0 ) {  

                        $args = array(
                            'posts_per_page'    => -1,
                            'post_type'         => 'product',
                            'meta_query' => array(
                                array(
                                        'key'     => '_movie_url',
                                        'value'   => $movie,
                                        'compare' => '=',
                                ),
                            ),
                            'tax_query'         => array(
                                        'relation'  => 'AND',
                                        array(
                                            'taxonomy'  =>  'product_type',
                                            'field'     =>  'slug',
                                            'terms'     =>  array('video_on_demand','subscription')
                                        )
                                ),

                        );


                        $video_list = new WP_Query($args);

                        $video_id   =0;
                        $ticket_flag=0;
                        if ($video_list->have_posts() ){
                            while ( $video_list->have_posts() ): 
                                $video_list->the_post(); 
                                $video_id     =   get_the_ID();

                                $show_id='';
                                $is_valid_subscription=0;
                                if(class_exists ('WC_Subscription')){
                             
                                    $is_valid_subscription = wcs_user_has_subscription( $current_user->ID, $show_id ,'active');
                                }

                                if(function_exists('wpstream_check_global_subscription_model')){
                                    if( wpstream_check_global_subscription_model() ){
                                        $is_valid_subscription=1;// this is global subscription
                                    }
                                }


                                if( wc_customer_bought_product( $current_user->email, $current_user->ID, $video_id) || 
                                        $is_valid_subscription==1 ){  
                                    
                                            $get_key = $this->wpstream_get_vod_key($folder.'/'.$movie);
                                            echo ($get_key);
                                            exit();
                                }else{
                                    $ticket_flag=1;
                                    exit('no ticket loop'.$current_user->ID.'/'.$video_id.'/'.$is_valid_subscription );
                                }
                            endwhile;

                            if($ticket_flag==0){
                                exit($current_user->email.'no ticket ukpt'.$current_user->ID.'.'.$video_id);
                            }

                        }else{
                            exit('no video found :'.$movie);
                        }

                    }else{
                        exit('user not log');
                    }

                }

            }

         }
         
         
          
         /**
	 * request key for vod from wpstream
	 *
	 * @since     3.0.1
         * 
	*/
        
         
        public function wpstream_get_vod_key($filename){   
           global $wpstream_plugin;
            $vod_key = get_transient("vod_key".$filename);
            if(false===$vod_key){
                $token  = $wpstream_plugin->wpstream_live_connection->wpstream_get_token();
                $domain = parse_url ( get_site_url() );

                $values_array=array(
                    "filename"           =>  $filename,
                );
                $url            =   WPSTREAM_CLUBLINKSSL."://www.".WPSTREAM_CLUBLINK."/wp-json/rcapi/v1/uservodkey/get/?access_token=".$token;


                $arguments = array(
                    'method'        => 'GET',
                    'timeout'       => 45,
                    'redirection'   => 5,
                    'httpversion'   => '1.0',
                    'blocking'      => true,
                    'headers'       => array(),
                    'body'          => $values_array,
                    'cookies'       => array()
                );
                $response       = wp_remote_post($url,$arguments);
                $received_data  = json_decode( wp_remote_retrieve_body($response) ,true);


                if( isset($response['response']['code']) && $response['response']['code']=='200'){
                    set_transient("vod_key".$filename,$received_data,120);
                    return ($received_data);
                }else{     
                    return 'failed connection';
                }
            }else{
                return $vod_key;
            }

        }
        
        
        
        
        
        
        
        
         
        /**
	 * wrapper start around woo
	 *
	 * @since     3.0.1
         * 
	*/
        
        
        public function wpstream_non_image_content_wrapper_start() {
            if ( is_user_logged_in() ) {
                global $product;
                $current_user   =   wp_get_current_user();
                $product_id     =   wc_get_product()->get_id();

                if ( wc_customer_bought_product( $current_user->user_email, $current_user->ID, $product_id) ){
                    echo '<div id="wpstream_product_wrap">';
                }else{
                    echo '<div id="wpstream_product_wrap_no_buy">';
                }
            }

        }

        /**
	 * wrapper end around woo
	 *
	 * @since     3.0.1
         * 
	*/
        

        function wpstream_non_image_content_wrapper_end() { 
           // echo '</div>';
        }
}
