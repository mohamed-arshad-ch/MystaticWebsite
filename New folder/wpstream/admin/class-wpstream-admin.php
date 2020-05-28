<?php

/**
 * The admin-specific functionality of the plugin.
 *
 * @link       http://wpstream.net
 * @since      3.0.1
 *
 * @package    Wpstream
 * @subpackage Wpstream/admin
 */

/**
 * The admin-specific functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the admin-specific stylesheet and JavaScript.
 *
 * @package    Wpstream
 * @subpackage Wpstream/admin
 * @author     wpstream <office@wpstream.net>
 */
class Wpstream_Admin {
        
    
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
	 * @param      string    $plugin_name       The name of this plugin.
	 * @param      string    $version    The version of this plugin.
	 */
	public function __construct( $plugin_name, $version,$plugin_main ) {

		$this->plugin_name = $plugin_name;
		$this->version = $version;
                $this->main = $plugin_main;

	}

	/**
	 * Register the stylesheets for the admin area.
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

		wp_enqueue_style( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'css/wpstream-admin.css', array(), $this->version, 'all' );
    
	}

	/**
	 * Register the JavaScript for the admin area.
	 *
	 * @since    3.0.1
	 */
	public function enqueue_scripts() {

                wp_enqueue_script("jquery-ui-slider");
                wp_enqueue_script("jquery-ui-datepicker");  
                wp_enqueue_script('jquery.fileupload',   plugin_dir_url( __FILE__ ) .'js/jquery.fileupload.js',array(), '1.0', true);  
                wp_enqueue_script('wpstream-admin-control',   plugin_dir_url( __FILE__ ) .'js/admin_control.js',array(), '1.0', true);  
                wp_localize_script('wpstream-admin-control', 'wpstream_admin_control_vars', 
                    array( 
                        'admin_url'             =>  get_admin_url(),
                        'loading_url'           =>  WPSTREAM_PLUGIN_DIR_URL.'/img/loading.gif',
                        'download_mess'         =>  esc_html__('Click to download!','wpstream'),
                        'uploading'             =>  esc_html('We are uploading your file.Do not close this window!','wpstream'),
                        'upload_complete2'      =>  esc_html('Upload Complete! You can upload another file!','wpstream'),
                        'not_accepted'          =>  esc_html('The file is not an accepted video format','wpstream'),
                        'upload_complete'       =>  esc_html('Upload Complete!','wpstream'),
                        'no_band'               =>  esc_html('Not enough bandwidth.','wpsteam'),
                        'no_band_no_store'      =>  esc_html('Not enough bandwidth or storage.','wpsteam')

                    ));
        }
         
        
        /**
	 * Add Plugin Administation menu
	 *
	 * @since    3.0.1 
	 */
        
        public function wpstream_manage_admin_menu() {
    
            add_menu_page( __('WpStream','wpestream'), __('WpStream ','wpstream'), 'administrator', 'wpstream_plugin_options', array($this,'wpstream_set_wpstream_credentials') );
            add_submenu_page( 'wpstream_plugin_options', __('WpStream Options','wpestream'),          __('WpStream Options','wpestream'),          'administrator', 'wpstream_plugin_options',      array($this,'wpstream_set_wpstream_credentials') );
            add_submenu_page( 'wpstream_plugin_options', __('WpStream Channels','wpestream'),         __('WpStream Channels','wpestream'),          'administrator', 'wpstream_new_general_set',    array( $this,'wpstream_new_general_set'));
            add_submenu_page( 'wpstream_plugin_options', __('WpStream Media Management','wpestream'), __('WpStream Media Management','wpestream'),  'administrator', 'wpstream_media_management',   array($this,'wpstream_media_management'));
        }

        
        
        /**
        * Shows events wpstream
        *
        * @since    3.0.1 
        */
        
        public function wpstream_new_general_set() {  

            $no_channel=1;

            if(class_exists ('WC_Subscription')){

            }

            //event_passed
            $args = array(
                'posts_per_page'    => -1,
                'post_type'         => 'product',
                'post_status'       => 'publish',
                'meta_query'        =>      array(
                                                array(
                                                        'key'     => 'event_passed',
                                                        'value'   => 1,
                                                        'compare' => '!=',
                                                )
                                            ),

                'tax_query'         => array(
                                        'relation'  => 'AND',
                                        array(
                                            'taxonomy'  =>  'product_type',
                                            'field'     =>  'slug',
                                            'terms'     => array('live_stream','subscription')
                                        )
                                    ),
            );



            $event_list = new WP_Query($args);
            global $live_event_for_user;
            $live_event_for_user    =    $this->main->wpstream_live_connection->wpstream_get_live_event_for_user();
            $pack_details           =    $this->main->wpstream_live_connection->wpstream_request_pack_data_per_user();

            $this->main->show_user_data($pack_details);




            if( $event_list->have_posts()){
               
                
                print '<div class="pack_details_wrapper_transparent">
                <h3>'.__('Your Pay-Per-View Channel List','wpstream').'</h3>';

             
                $link_new   =   admin_url('post-new.php?post_type=product').'&new_stream='. rawurlencode('new');
                  
                print '<a href="'.esc_url($link_new).'"  class="wpstream_create_new_product_link">'.esc_html__('Create new pay-per-view channel.','wpstream').'</a>';
                print '</div>';

                print '<div style="clear: both;"></div><div class="event_list_wrapper">';

                    while ($event_list->have_posts()): $event_list->the_post();

                        $the_id                     =   get_the_ID();
                        $is_subscription_live_event =   esc_html(get_post_meta($the_id,'_subscript_live_event',true));
                        $term_list                  =   wp_get_post_terms($the_id, 'product_type');

                        if( $term_list[0]->name=='subscription' && $is_subscription_live_event=='no'){
                            continue;
                        }

                        $this->wpstream_live_stream_unit($the_id);

                    endwhile;

                print'</div>'; 
                $no_channel=1;
            }else{
                $no_channel=0;
            }


            $ajax_nonce = wp_create_nonce( "wpstream_start_event_nonce" );
            print '<input type="hidden" id="wpstream_start_event_nonce" value="'.$ajax_nonce.'">';
            $current_user       =   wp_get_current_user();
            $allowded_html      =   array();
            $userID             =   $current_user->ID;
            $user_live_streams  =   get_user_meta($userID,'live_shows');


            wp_reset_postdata();




            // free 

            $args_free = array(
                'posts_per_page'    => -1,
                'post_type'         => 'wpstream_product',
                'post_status'       => 'publish',
                'meta_query'        =>      array(
                                                'relation'  => 'AND',
                                                array(
                                                        'key'     => 'wpstream_product_type',
                                                        'value'   => 1,
                                                        'compare' => '==',
                                                ),

                                            ),


            );
            $event_list_free = new WP_Query($args_free);


            if( $event_list_free->have_posts()){
                print '<div class="pack_details_wrapper_transparent">
                <h3>'.__('Your Free Channel List','wpestate').'</h3>';

                $link_new = admin_url('post-new.php?post_type=wpstream_product');
                print '<a href="'.esc_url($link_new).'" class="wpstream_create_new_product_link">'.esc_html__('Create new free channel.','wpstream').'</a>';
                print '</div>';
                print '<div style="clear: both;"></div><div class="event_list_wrapper">';

                    while ($event_list_free->have_posts()): $event_list_free->the_post();


                        $the_id =   get_the_ID();

                        if( get_post_meta ($the_id,'event_passed',true)!=1){
                            $this->wpstream_live_stream_unit($the_id);
                        }

                    endwhile;

                print'</div>';    
                $no_channel=1;
            }else{
                $no_channel=0;
            }



            if($no_channel==0){
                $link_new_paid = admin_url('post-new.php?post_type=product').'&new_stream='. rawurlencode('new');
                $link_new_free = admin_url('post-new.php?post_type=wpstream_product');
                print '<div class="no_events_warning"> '.__('* You don\'t have any live channels!','wpstream');

                print '<a href="'.esc_url($link_new_free).'" class="wpstream_no_chanel_add_channel">'.esc_html__('Add new free channel ','wpstream').'</a>';
                print '<a href="'.esc_url($link_new_paid).'" class="wpstream_no_chanel_add_channel">'.esc_html__('Add new pay-per-view channel ','wpstream').'</a>';        

                print '</div>';
            }




        }
        // end   wpstream_new_general_set  


          
        
        
        
        
        /**
        * Shows event unit card in admin
        *
        * @since    3.0.1 
        */
        public function wpstream_live_stream_unit($the_id){
            global $live_event_for_user;

            $live_class='';
            if(isset($live_event_for_user[$the_id])) {
                $live_class=" wpstream_show_started";
            }


            print '<div class="event_list_unit '.$live_class.' ">';
                $ip=   get_post_meta($the_id,'ip',true);


                if(has_post_thumbnail($the_id)){
                    $thumb  =   get_the_post_thumbnail_url($the_id,'thumbnail');
                }else{
                    $thumb= plugin_dir_url( dirname( __FILE__ ) ). 'img/default_150.jpg';
                }


                print '<div class="event_thumb_wrapper"><img class="event_thumb" src="'.$thumb.'" alt="show_imagge"></div>';
                print '<h3 data-prodid="'.$the_id.'">'.get_the_title($the_id).'  - '.esc_html__('Id','wpstream').': '.$the_id.'<a class="view_channel" href="'.get_permalink($the_id).'" target="_blank">'.esc_html__('View Channel','wprentals').'</a></h3> ';

                $live_event_stream_name =   get_post_meta($the_id,'live_event_stream_name',true);
                $live_event_array       =   get_post_meta($the_id,'live_event_uri',true);
                $live_event_uri         =   '';


                    $pending_streaming_class            =   '';
                    $wpstream_ready_to_stream_class     =   '';
                    $external_software_streaming_class  =   '';
                    $wpstream_no_stream_class           =   '';
                    $carnat_key1                        =   '';
                    $carnat_key2                        =   '';
                    $uri                                =   '';
                    $webcaster_url                      =   '';
                    $rtmp_ip_uri                        =   '';
                    $uri_ip                             =   '';

                    if(is_array($live_event_for_user) && isset($live_event_for_user[$the_id])) {

                        $pending_streaming_class            =   'show_stream_data pending_trigger';
                        $wpstream_ready_to_stream_class     =   'hide_stream_data';
                        $external_software_streaming_class  =   'show_stream_data';
                        $wpstream_no_stream_class           =   'hide_stream_data';
                        if(isset($live_event_for_user[$the_id]['live_uri'])){
                        $explode        =   explode('wpstream.net/',  $live_event_for_user[$the_id]['live_uri']);
                        $uri            =   $explode[0].'wpstream.net/wpstream/';
                        $stream_name    =   get_post_meta($the_id,'live_event_stream_name',true);
                        $carnat_key1    =   $live_event_for_user[$the_id]['carnat1'];
                        $carnat_key2    =   $live_event_for_user[$the_id]['carnat2'];
                        $webcaster_url  =   'https://'.$live_event_for_user[$the_id]['subdomain_key'].'.live.streamer.wpstream.net:8443/'.$live_event_for_user[$the_id]['carnat2'];

                        $rtmp_ip_uri = 'http://'.$live_event_for_user[$the_id]['ip'].':8444';
                        $uri_ip= 'rtmp://'.$live_event_for_user[$the_id]['ip'].'/wpstream/';
                        }

                    }else{

                        $pending_streaming_class            =   'hide_stream_data';
                        $wpstream_ready_to_stream_class     =   'hide_stream_data';
                        $external_software_streaming_class  =   'hide_stream_data';
                        $wpstream_no_stream_class           =   'show_stream_data';
                    }




                        print' <div class="pending_streaming '.$pending_streaming_class.' " data-server-id="'.$the_id.'" data-server-url="'.$rtmp_ip_uri.'">';
                            print'<div class="wpstream_channel_status not_ready_to_stream"><span class="dashicons dashicons-dismiss"></span>'.esc_html__('Getting ready to stream. Please wait...','wprentals').' ';
                            print '<img class="server_loading_new"  src="'.plugin_dir_url( dirname( __FILE__ ) ).'img/loading.gif" alt="loading" /></div>';
                            print '<div class="multiple_warning_events"> '.esc_html__('* You can run multiple live events on a single channel. **Once deleted, you can no longer broadcast to it.','wpstream').'</div>';
                        print '</div>';


                        print '<div class="wpstream_ready_to_stream '.$wpstream_ready_to_stream_class.'">';
                            print   '<div class="wpstream_channel_status"><span class="dashicons dashicons-yes"></span>'.esc_html__('Ready To Stream !','wpstream').'</div>';
                            print   '<div class="start_webcaster wpstream_button" data-webcaster-url="'.$webcaster_url.'" >'.esc_html__('Start Browser Broadcast','wpstream').'</div>';
                            print   '<div class="start_external wpstream_button"  >'.esc_html__('Broascast With 3rd Party Software','wpstream').'</div>';   
                        print '</div>';



                        print '<div class="external_software_streaming '.  $external_software_streaming_class.' ">';
                            print '<div class="event_list_unit_notificationx"><strong>'.__('URL:').' </strong> <div class="wpstream_live_uri_text">' . $uri_ip.'</div><div class="copy_live_uri">'.__('copy to clipboard','wpstream').'</div>';
                            print '<div class="event_list_stream_key_wrap"><strong>'.__('Stream key:').' </strong><div class="wpstream_live_key_text">'. $carnat_key1.'?auth='.$carnat_key2.'</div><div class="copy_live_key">'.__('copy to clipboard','wpstream').'</div></div>';
                            print '<div class="warning_stream">'.sprintf(esc_html__('For professional results use %s with above credentials. You may also use the "RTMP Camera" app on Android phones and tablets or the "Broadcast Me" app on iPhone and iPad.','wpstream'),'<a href="https://obsproject.com/download" target="_blank">OBS Studio software</a>').'</div>';           
                            print ' <img class="how_to" src="'.plugin_dir_url( dirname( __FILE__ ) ).'img/how_to_obs.jpg" alt="show_imagge">';
                            print'</div>';
                        print'</div>';




                        print '<div class="wpstream_no_stream '.$wpstream_no_stream_class.' ">';
                            print '<div class="event_list_unit_notificationx"><span class="server_notification">'.__('Channel is inactive.','wpestream').' <img class="server_loading" src="'.plugins_url().'/wpstream/img/loading.gif" alt="loading" /></span></div>';
                            print '<div class="record_wrapper">Record ? <input type="checkbox" name="record_event" class="record_event"/>Yes</div>';
                            print '<input class="start_event wpstream_button"  type="button" data-show-id="'.$the_id.'" value="'.esc_html__('Broadcast To Channel','wpstream').'">';
                            print '<input class="close_event wpstream_button"  type="button" data-show-id="'.$the_id.'" value="'.esc_html__('Delete Channel','wpstream').'">';
                            print '<div class="multiple_warning_events"> '.esc_html__('* You can run multiple live events on a single channel. **Once deleted, you can no longer broadcast to it.','wpstream').'</div>';
                        
                      
                            print '<div class="wpstream_social_media_broadcast">';
                                
                                print '<div class="wpstream_social_media_unit"><input type="checkbox" class="wpstream_on_facebook" id="wpstream_on_facebook'.$the_id.'"><label for="wpstream_on_facebook'.$the_id.'">'.esc_html__('Stream on Facebook','wpstream').'</label>';
                                print '</div>';

                                print '<div class="wpstream_social_media_unit"><input type="checkbox" class="wpstream_on_youtube" id="wpstream_on_youtube'.$the_id.'"><label for="wpstream_on_youtube'.$the_id.'">'.esc_html__('Stream on Youtube','wpstream').'</label>';
                                print '</div>';


                                print '<div class="wpstream_social_media_unit"><input type="checkbox" class="wpstream_on_twich" id="wpstream_on_twich'.$the_id.'"><label for="wpstream_on_twich'.$the_id.'">'.esc_html__('Stream on Twich','wpstream').'</label>';
                                print '</div>';
                                
                                print '<div class="wpstream_on_facebook_container wpstream_social_stream_container">';
                                    print 'Some Facebook Settings for event '.$the_id;
                                print '</div>';
                                
                                print '<div class="wpstream_on_youtube_container wpstream_social_stream_container">';
                                    print '<label for="wpstream_youtube_rtmp'.$the_id.'">'.esc_html__('Youtube RTMP','wpstream').'</label>';
                                    print '<input type="text" class="wpstream_youtube_rtmp"  id="wpstream_youtube_rtmp'.$the_id.'" value="'.esc_html(get_post_meta($the_id,'wpstream_youtube_rtmp',true )).'">';
                                print '</div>';
                                
                                print '<div class="wpstream_on_twich_container wpstream_social_stream_container">';
                                    print '<label for="wpstream_twich_rtmp'.$the_id.'">'.esc_html__('Twich RTMP','wpstream').'</label>';
                                    print '<input type="text" class="wpstream_twich_rtmp"  id="wpstream_twich_rtmp'.$the_id.'"  value="'.esc_html(get_post_meta($the_id,'wpstream_twich_rtmp',true )).'">';
                                print '</div>';

                            
                            print '</div>';
                        print '</div>';


            print '</div>';
        }


        
        
        
        
        

        
        
        
        /**
	 * Set credential admin function
	 *
	 * @since    3.0.1
	 */       
        public function wpstream_set_wpstream_credentials(){
    
            if($_SERVER['REQUEST_METHOD'] === 'POST'){	
                $allowed_html   =   array();
                $exclude_array  =   array();
                $allowed_html   =   array();

                foreach($_POST as $variable=>$value){	
                    if ($variable!='submit'){
                        if (!in_array($variable, $exclude_array) ){
                            update_option( sanitize_key('wpstream_'.$variable), wp_kses ($value,$allowed_html) );
                        }	
                    }	
                }
                
        


                update_option('wp_estate_token_expire',0);
                update_option('wp_estate_curent_token',' ');
                delete_transient( 'wpstream_token_request');

            }
       
    
            $allowed_html   =   array();
            $api_key        =   esc_html( get_option('wpstream_api_key','') );


            $wpstream_options_array =array(
                2   =>  array(
                            'label' =>  'WpStream.net Username or Email',
                            'name'  =>  'api_username',
                            'type'  =>  'text',
                        ),
                3   =>  array(
                            'label' =>  'WpStream.net Password',
                            'name'  =>  'api_password',
                            'type'  =>  'password',
                        ),
        //        0   =>  array(
        //                    'label' =>  'API Key',
        //                    'name'  =>  'api_key',
        //                    'type'  =>  'text',
        //                ),
        //        1   =>  array(
        //                    'label' =>  'API Secret Key',
        //                    'name'  =>  'api_secret_key',
        //                    'type'  =>  'text',
        //                ),

            );


            $token          =   $this->main->wpstream_live_connection->wpstream_get_token();
            $pack_details   =   $this->main->wpstream_live_connection->wpstream_request_pack_data_per_user();
            
            $this->main->show_user_data($pack_details);

            print   '<form method="post" action="" >';
                        print '<div  class="theme_options_tab_wpstream" style="display:block;" >
                                <h1>'.__('WpStream Credentials','wpstream').'</h1>';

                        
                                if( get_option('wpstream_api_username')=='' ||  get_option('wpstream_api_password')== ' '){
                                    echo '<div class="api_not_conected wpstream_orange">'.__('To connect your plugin, enter your WpStream credentials below or go <a href="https://wpstream.net/my-account/" target="_blank">here</a> to create an account.','wpstream').'</div>';
                                }else if($token==''){
                                    echo '<div class="api_not_conected">'.__(' Incorrect username or password. Please check your credentials or go <a href="https://wpstream.net/my-account/edit-account/" target="_blank">here</a> to reset your password.','wpstream').'</div>';
                                }else if( $this->main->wpstream_live_connection->wpstream_client_check_api_status() ){
                                    echo '<div class="api_conected">'.__('Conected to WpStream.net!','wpstream').'</div>';
                                }else{
                                    echo '<div class="api_not_conected wpstream_brown">'.__('Failed to connect to WpStream.net. Please address CURL connectivity with your hosting provider.','wpstream').'</div>';
                                }
                                print '<div class="wpstream_option_wrapper">';
                                    foreach ($wpstream_options_array as $key=>$option){
                                        print '<div class="wpstream_option">';

                                            $options_value =  esc_html( get_option('wpstream_'.$option['name'],'') );
                                            print '<label for="'.$option['name'].'">'.$option['label'].'</label>';
                                            print '<input id="'.$option['name'].'" type="'.$option['type'].'" size="36"  name="'.$option['name'].'" value="'.esc_html($options_value).'" />';

                                        print '</div>';
                                    }
                                print '</div>';


                            print '<input type="submit" name="submit"  class="wpstream_button" value="'.__('Save Changes','wpstream').'" />';



                        print '</div>';
            print   '</form>';

            print '<div  class="theme_options_tab_wpstream" style="display:block;" >';
                $link_new = admin_url('admin.php?page=wpstream_new_general_set');
                $link_new_paid = admin_url('post-new.php?post_type=product').'&new_stream='. rawurlencode('new');
                $link_new_free = admin_url('post-new.php?post_type=wpstream_product');
 

                print '<a href="'.esc_url($link_new_free).'" class="wpstream_no_chanel_add_channel">'.esc_html__('Create new free channel','wpstream').'</a>';
                print '<a href="'.esc_url($link_new_paid).'" class="wpstream_no_chanel_add_channel">'.esc_html__('Create pay-per-view channel','wpstream').'</a>';
                print '<a href="'.esc_url($link_new).'"      class="wpstream_no_chanel_add_channel">'.esc_html('My Channels','wpstream').'</a>';        
            print '</div>';
   

    }


  
        /**
	 * Media Management
	 *
	 * @since    3.0.1
	 */  
        public function wpstream_media_management(){
            $pack_details           =    $this->main->wpstream_live_connection->wpstream_request_pack_data_per_user();

            $this->main->show_user_data($pack_details);


            print '<div id="wpstream_media_upload"><h3>'.__('Media Upload','wpstream').'</h3>'.$this->wpstream_present_media_upload().'</div>';

            print '<div id="wpstream_file_management"><h3 id="video_management_title">'.__('Video Management','wpstream').'</h3>'.$this->wpstream_present_file_management().'</div>';

        }


  
        /**
	 * Media upload
	 *
	 * @since    3.0.1
	 */  
        public function wpstream_present_media_upload(){
            $to_return='';
            $formInputs=$this->main->wpstream_live_connection->wpstream_get_signed_form_upload_data();

            if($formInputs === 'notenough'){
                $to_return.='<div class="wpstream_upload_alert">'.esc_html__('You do not have enough bandwidth or storage to upload a video!','wpstream').'</div>';
                return $to_return;
            }


            $to_return.='<div class="wpstream_upload_container">';
            $to_return.='<div id="wpstream_uploaded_mes">'.esc_html__('Please select or drop a video file. Do not close this window during the upload!','wpstream').'</div>';
            $to_return.='<form action="https://wpstream-videos.s3.amazonaws.com/"
                          method="POST"
                          enctype="multipart/form-data"
                          class="direct-upload">';

            $to_return.='<input id="wpstream_upload" type="file" class="inputfile inputfile-1" value="Pick a video file" name="file" multiple>';
            $to_return.='<label for="wpstream_upload"><svg xmlns="http://www.w3.org/2000/svg" width="20" height="17" viewBox="0 0 20 17"><path d="M10 0l-5.2 4.9h3.3v5.1h3.8v-5.1h3.3l-5.2-4.9zm9.3 11.5l-3.2-2.1h-2l3.4 2.6h-3.5c-.1 0-.2.1-.2.1l-.8 2.3h-6l-.8-2.2c-.1-.1-.1-.2-.2-.2h-3.6l3.4-2.6h-2l-3.2 2.1c-.4.3-.7 1-.6 1.5l.6 3.1c.1.5.7.9 1.2.9h16.3c.6 0 1.1-.4 1.3-.9l.6-3.1c.1-.5-.2-1.2-.7-1.5z"/></svg> <span id="wpstream_label_action">Choose a file&hellip;</span></label>';


            $to_return.='<div class="wpstream_file_drop_color">';
            $to_return.='<div class="wpstream_form_ex">'.esc_html__('Drop a video file here!','wpstream').'</div>';      
            $to_return.='<div class="wpstream_form_ex_details">'.esc_html__('The Video File must be encoded with the following settings:

        Container: MP4,
        Video codec: H264,
        Audio codec: AAC. Media will fail to play back if it does not follow the above settings. 
        You may use a tool like MediaInfo to verify your file. Also you may convert it with specialized software like HandBrake.','wpstream').'<strong>'.esc_html__('Accepted file extensions: .mp4, .mov .','wpstream').'</strong></div>';    
            if(is_array($formInputs)){      
                foreach ($formInputs as $name => $value) { 
                        $to_return.='<input type="hidden" name="'. $name.'" value="'.$value.'">';
                }                     
            }                  

            $to_return.='
            <div class="progress-bar-area"></div></div>
            </form>';

            $to_return.='</div>';
            return $to_return;

        }



        /**
	 * Display movie list
	 *
	 * @since    3.0.1
	 */  
        public function wpstream_present_file_management(){
                $video_list_raw = $this->main->wpstream_live_connection->wpstream_get_videos_from_storage_raw_data();

                $to_return='';
                if(is_array($video_list_raw)){
                    foreach ($video_list_raw as $key =>$video){
                         $to_return.='<div class="wpstream_video_wrapper">';

                            $to_return.='<div class="wpstream_video_title">';
                            $to_return.='<div class="wpstream_video_notice"></div></div>';
                            $to_return.='<div class="wpstream_video_title"><strong class="storage_file_name">'.esc_html__('File Name :','wpstream').'</strong>'.'<span class="storage_file_name_real">'.$video['video_name_storage'].'</span></div>';
                            $to_return.=' <div class="wpstream_delete_media" ';
                            $to_return.=' onclick="return confirm(\' Are you sure you wish to delete '.$video['video_name_storage'].'?\')" data-filename="'.$video['video_name_storage'].'">'.esc_html__('delete file','wpstream').'</div>';
                            $to_return.='<div class="wpstream_get_download_link" data-filename="'.$video['video_name_storage'].'">'.esc_html__('get download link','wpstream').'</div>';
                            $to_return.='<a href="" class="wpstream_download_link">'.esc_html__('Click to download! The url will work for the next 20 minutes!','wpstream').'</a>';

                            $add_free_video_url=admin_url('post-new.php?post_type=wpstream_product').'&new_video_name='. rawurlencode($video['video_name_storage']);
                            $add_paid_video_url=admin_url('post-new.php?post_type=product').'&new_video_name='. rawurlencode($video['video_name_storage']);



                            $to_return .='<a class="create_new_free_video" href="'.esc_url($add_free_video_url).'">'.esc_html__('Create new free VOD from this video').'</a>'; 
                            $to_return .='<a class="create_new_ppv_video" href="'.esc_url($add_paid_video_url).'">'.esc_html__('Create pay-per-view VOD from this video').'</a>'; 

                         $to_return.='</div>';

                    }
               } else {
                   $to_return.= '<div class="wpstream_video_wrapper">'.esc_html__('You don\'t have any videos.','wpstream').'</div>';
               }
               return $to_return;
        }


        
        
        
        /**
	 * save meta options
	 *
	 * @since    3.0.1
	 */  
        public function  wpstream_free_product_update_post($post_id,$post){

            if(!is_object($post) || !isset($post->post_type)) {
                return;
            }

            if($post->post_type!='wpstream_product'){
                return;    
            }


            $allowed_keys=array(
                'wpstream_product_type',
                'wpstream_free_video',
                'wpstream_free_video_external'
             );

            $allowed_html=array();

            foreach ($_POST as $key => $value) {
                if( !is_array ($value) ){
                    if (in_array ($key, $allowed_keys)) {
                        $postmeta = wp_kses ( $value,$allowed_html ); 
                        update_post_meta($post_id, sanitize_key($key), $postmeta );
                    }
                }       
            }
        }
        
        
         /**
	 * save meta options
	 *
	 * @since    3.0.1
	 */ 
        public function add_wpstream_product_metaboxes() {	
            add_meta_box(  'add_wpstream_product_metaboxes-sectionid', __( 'Live Channel/Video Settings', 'wpestate' ),array($this,'display_meta_options'),'wpstream_product' ,'normal','default');
        }
        
        
        
          /**
	 * render meta options
	 *
	 * @since    3.0.1
	 */ 
        public function display_meta_options( $post ) {
                wp_nonce_field( plugin_basename( __FILE__ ), 'estate_agent_noncename' );
                global $post;

                $is_live               =    '';
                $is_video              =    '';
                $is_video_external     =    '';
                if( isset( $_GET['new_video_name']) && $_GET['new_video_name']!=''  ){
                    $is_video               =   ' selected ';
                    $wpstream_free_video    =   esc_html( $_GET['new_video_name']);
                }else{
                    $wpstream_product_type  =    esc_html(get_post_meta($post->ID, 'wpstream_product_type', true));
                    $wpstream_free_video    =    esc_html(get_post_meta($post->ID, 'wpstream_free_video', true));
                    
                    if($wpstream_product_type==1){
                        $is_live = ' selected ';
                    }
                   
                    if($wpstream_product_type==2){
                        $is_video = ' selected ';
                    }

                    if($wpstream_product_type==3){
                        $is_video_external = ' selected ';
                    }
                }

                print'
                <p class="meta-options">
                    <label for="wpstream_product_type">'.__('Media Type:','wpstream').' </label><br />
                    <select id="wpstream_product_type" name="wpstream_product_type">
                        <option value="1" '.$is_live.'>'.__('Free Live Channel - encrypted streaming & copy protection','wpstream').'</option>
                        <option value="2" '.$is_video.'>'.__('Free Video - encrypted streaming & copy protection','wpstream').'</option>
                        <option value="3" '.$is_video_external.'>'.__('Free Video - unprotected','wpstream').'</option>
                    </select>
                </p>        
                ';           



                print '
                <p class="meta-options video_free">
                    <label for="wpstream_free_video">'.__('Choose video:','wpstream').' </label><br />
                    <select id="wpstream_free_video" name="wpstream_free_video">';
                        $video_list =  $this->main->wpstream_live_connection->wpstream_get_videos();

                        if(is_array($video_list)){
                            foreach ($video_list as $key=>$value){
                                print '<option value="'.$key.'"'; 
                                if($wpstream_free_video === $key){
                                   print ' selected ';
                                }
                                print '>'.$value.'</option>';
                            }
                        }
                 print'
                    </select>
                </p>        
                ';  

                $wpstream_free_video_external=    esc_html(get_post_meta($post->ID, 'wpstream_free_video_external', true));
                print '<p class="meta-options1 video_free_external">
                        <label for="wpstream_free_video_external">'.__('Choose video:','wpstream').' </label><br />

                        <input id="wpstream_free_video_external" type="text" size="36" name="wpstream_free_video_external" value="'.$wpstream_free_video_external.'" />
                        <input id="wpstream_free_video_external_button" type="button"   size="40" class="upload_button button" value="'.esc_html__('Select Video','wpstream').'" />

                        <p>'.esc_html__('You can choose a video from your computer or use the url from external source or use the url of a YouTube Video or use the url from a Vimeo video.','wpstream').'</p>

                </p> ';
        }
        
        
        
        
        
       
        
         /**
        * Add new product types to Woocommerce select product type
        *
        * @since    3.0.1
        */ 
        public function wpstream_add_products( $types ){
            $types[ 'live_stream' ]             = __( 'Live Channel','wpestream' );
            $types[ 'video_on_demand' ]         = __( 'Video On Demand','wpestream' );
            
            return $types;
        }
        
        
        
         /**
        * Js action to do when user pick live stream or video on demand
        *
        * @since    3.0.1
        */ 
        
        public function wpstream_products_custom_js() {
            if ( 'product' != get_post_type() ) :
                return;
            endif;

            ?>
            <script type='text/javascript'>
                jQuery( document ).ready( function() {
                    jQuery('.options_group.pricing' ).addClass ( 'show_if_live_stream' ).show();
                    jQuery('.options_group.pricing' ).addClass ( 'show_if_video_on_demand' ).show();
                    jQuery('._sold_individually_field').parent().addClass('show_if_live_stream').show();
                    jQuery('._sold_individually_field').parent().addClass('show_if_video_on_demand').show();
                    jQuery('._sold_individually_field').show();
             
                    var selected = jQuery('#product-type').val();
                });
            </script>
            <?php

        }
         
        
        /**
        * Add custom classes to the product types
        *
        * @since    3.0.1
        */ 
        
        public function wpstream_hide_attributes_data_panel( $tabs) {
    
            $tabs['shipping']['class'][] = 'hide_if_live_stream  hide_if_video_on_demand';
            $tabs['inventory']['class'][] = 'show_if_live_stream  show_if_video_on_demand';
           // $tabs['general']['class'][] = 'show_if_live_stream show_if_video_on_demand';

            return $tabs;
        }
        
        
         /**
        * Add custom fields to custom product types
        *
        * @since    3.0.1
        */ 
        
        public function wpstream_add_custom_general_fields() {

            global $woocommerce, $post;
            if(function_exists('wcs_user_has_subscription')){
                echo '<div class="options_group   show_if_subscription">';  
                    woocommerce_wp_select( 
                        array( 
                            'id'      =>    '_subscript_live_event', 
                            'label'   =>    __( 'Is a subscription based live channel ?', 'woocommerce' ), 
                            'options' =>    array("yes"=>"yes","no"=>"no")
                            )
                        );
                echo '</div>';
            }

            echo '<div class="options_group show_if_live_stream" style="border:none;"></div>';  
            echo '<div class="options_group show_if_video_on_demand">';  
                $selected='';
                if( isset( $_GET['new_video_name']) && $_GET['new_video_name']!=''  ){
                    $selected=esc_html($_GET['new_video_name']);
                }
                if($selected==''){
                   $selected= get_post_meta($post->ID,'_movie_url',true);
                }

                woocommerce_wp_select( 
                    array( 
                        'id'      =>    '_movie_url', 
                        'label'   =>    __( 'Select the video file.', 'woocommerce' ), 
                        'options' =>     $this->main->wpstream_live_connection->wpstream_get_videos(),
                        'selected'=>    true,
                        'value'    =>   $selected
                        )
                );

            echo '</div>';
        }

        /**
        * Save custom fields
        *
        * @since    3.0.1
        */ 
        
        public function wpstream_add_custom_general_fields_save( $post_id ){

            $permited_values = array(
                '_movie_url',
                '_subscript_live_event'
            );
            $allowed_html=array();
            foreach($_POST as $key=>$value){
                update_post_meta( $post_id, 'event_passed', 0 );
                if( in_array($key, $permited_values) ){
                    if( !empty( $_POST[$key] ) ){
                        update_post_meta( $post_id, wp_kses ($key,$allowed_html), wp_kses ( $_POST[$key],$allowed_html) );
                    }
                }
            }

        }
        
         /**
        * Add to cart redirect
        *
        * @since    3.0.1
        */ 
        
        public function wpstream_add_to_cart() {
            wc_get_template( 'single-product/add-to-cart/simple.php' );
        }

        
        /**
        * Replace add to cart button
        *
        * @since    3.0.1
        */ 
        public function replacing_add_to_cart_button( $button, $product  ) {
            global $product;
            $product_type = $product->get_type();

            if($product_type==='live_stream' || $product_type=='video_on_demand'){
                return $button = '<a class="button" href="'.get_site_url().'/shop/?add-to-cart=' .$product->get_id(). '&quantity=1">' . __( 'Add to Cart', 'woocommerce' ) . '</a>';
            }else{
                return $button;
            }
        }
       

         /**
        * Admin notices
        *
        * @since    3.0.1
        */ 
        public function wpstream_admin_notice() {
            global $pagenow;
            global $typenow;

            if ( !in_array( 'woocommerce/woocommerce.php', apply_filters( 'active_plugins', get_option( 'active_plugins' ) ) ) ) {
                print '<div class="notice  notice-error is-dismissible">
                    <p>'.__( 'WpStream Pay Per View / Subscription  mode works only with WooCommerce - If you want to charge money for your live events or videos you need to activate WooCommerce plugin', 'wpestate' ).'</p>
                </div>';
            }
            if( !in_array  ('curl', get_loaded_extensions())) {
                print '<div class="notice  notice-error is-dismissible">
                    <p>'.__( 'The php CURL library is not enabled on your server. WpStream plugin needs this library in order to work. Please address this issue with your hosting provider.', 'wpestate' ).'</p>
                </div>';
            }


            $wpestate_notices =  get_option('wp_stream_notices');

            if( !is_array($wpestate_notices) || 
                !isset($wpestate_notices['wpstream_update_1021']) ||
                ( isset($wpestate_notices['wpstream_update_1021']) && $wpestate_notices['wpstream_update_1021']!='yes')  ){

                print '<div  id ="setting-error-wprentals-cache"  data-notice-type="wpstream_update_1021"  data-dismissible="disable-done-notice-forever" class="wpestate_notices updated settings-error notice is-dismissible">
                    <p>'.esc_html__( 'New! We just released WpStream WordPress Theme - a turn key solution for video delivery & live streaming. This theme is built around WpStream plugin and is the perfect solution for video streaming or rentals platform.  ','wpstream').'<a href="https://wpstream.net/wpstream-theme-a-live-streaming-wordpress-theme" target="_blank">'.esc_html__('Download Theme','wpstream').'</a></p>
                </div>';
            }





            $ajax_nonce = wp_create_nonce( "wpstream_notice_nonce" );
            print '<input type="hidden" id="wpstream_notice_nonce" value="'.esc_html($ajax_nonce).'"/>';

        }
        
          /**
        * Admin notices
        *
        * @since    3.0.1
        */ 
        public function wpstream_update_cache_notice(){

            //check_ajax_referer( 'wpstream_notice_nonce', 'security'  );

            $notice_type    =   esc_html($_POST['notice_type']);
            $notices        =   get_option('wp_stream_notices');

            if(! is_array($notices) ){
                $notices=array();
            }

            $notices[$notice_type]='yes';

            update_option('wp_stream_notices',$notices);
            die();
        }
        
        
        
        /**
        * Activate metaboxes for Streaming controls on sidebar
        *
        * @since    3.0.1
        */ 
         public function wpstream_startstreaming_sidebar_meta() {
                global $post;
                $term_list                          =   wp_get_post_terms($post->ID, 'product_type');
                if(  get_post_meta($post->ID, 'wpstream_product_type', true)==1  ){
                    add_meta_box('wpstream-sidebar-meta',       esc_html__('Live Streaming',  'wpstream'), array($this,'wpstream_start_stream_meta'), 'wpstream_product', 'side', 'high');
                }
                
                $is_subscription_live_event =   esc_html(get_post_meta($post->ID,'_subscript_live_event',true));
              

                if(!is_wp_error( $term_list )){     
                    if( isset($term_list[0]->name) ){        
                        if( $term_list[0]->name=='live_stream' ||  ($term_list[0]->name=='subscription' && $is_subscription_live_event=='yes' )  ){    
                            add_meta_box('wpstream-sidebar-meta',       esc_html__('Live Streaming',  'wpstream'), array($this,'wpstream_start_stream_meta'), 'product', 'side', 'high');
                        }
                    }
                }

        }
        
         /**
        * Show Streaming controls on sidebar
        *
        * @since    3.0.1
        */ 
        public function wpstream_start_stream_meta(){
            global $live_event_for_user;
            global $post;

            $ajax_nonce = wp_create_nonce( "wpstream_start_event_nonce" );
            print '<input type="hidden" id="wpstream_start_event_nonce" value="'.$ajax_nonce.'">';
            $live_event_for_user    =    $this->main->wpstream_live_connection->wpstream_get_live_event_for_user();
            $this->wpstream_live_stream_unit($post->ID);

        }
}