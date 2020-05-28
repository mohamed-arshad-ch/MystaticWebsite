<?php

class Wpstream_Live_Api_Connection  {

	

    
    public function __construct() {
        add_action( 'wp_ajax_wpstream_give_me_live_uri', array($this,'wpstream_give_me_live_uri') );  
        add_action( 'wp_ajax_wpstream_check_dns_sync', array($this,'wpstream_check_dns_sync') );
        add_action( 'wp_ajax_wpstream_check_server_against_db', array($this,'wpstream_check_server_against_db') );  
        add_action( 'wp_ajax_wpstream_close_event', array($this,'wpstream_close_event') );
        add_action( 'wp_ajax_wpstream_get_download_link', array($this,'wpstream_get_download_link') );  
        add_action( 'wp_ajax_wpstream_get_delete_file', array($this,'wpstream_get_delete_file') ); 
    }
    
    /**
     * Check live stream in db
     *
     * @since    3.0.1
     * returns live url
    */
    public function wpstream_check_server_against_db(){
        $show_id    =   intval($_POST['show_id']);
        $is_live    =   false;

        $live_event_for_user    =   $this->wpstream_get_live_event_for_user();
        if(isset($live_event_for_user[$show_id])) {
           $is_live=true;   
        }

        echo json_encode( array('islive' =>$is_live) );
        exit();
    }


    /**
     * Check server dns status
     *
     * @since    3.0.1
     * returns live url
    */
    public function wpstream_check_dns_sync(){
            $token  = $this->wpstream_get_token();
            $values_array=array(
                "dns_change_id"           =>  esc_html($_POST['change_id']),
            );

            $url=WPSTREAM_CLUBLINKSSL."://www.".WPSTREAM_CLUBLINK."/wp-json/rcapi/v1/checkdns/get/?access_token=".$token;
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
                print ($received_data);
            }else{     
               // print 'failed connection';
            }
            die();
    }

    
    
    
    
    
    
    /**
     * Request live url
     *
     * @since    3.0.1
     * returns live url
     */
    public function wpstream_give_me_live_uri(){
        
            check_ajax_referer( 'wpstream_start_event_nonce', 'security' );
            $current_user       =   wp_get_current_user();
            $allowded_html      =   array();
            $userID             =   $current_user->ID;
            $return_uri         =   '';


            if(function_exists('wpstream_get_option') && intval(wpstream_get_option('allow_streaming_regular_users',''))==1 ){
                if( !is_user_logged_in() ){
                   exit('okko xab1');
                }
            }else{
                if( !current_user_can('administrator') ){
                   exit('okko xa1');
                }
            }
         
            

            $show_id            =   intval  ( $_POST['show_id'] );
            $is_record          =   floatval( $_POST['is_record'] );
          

   
            //prepare 3rd options
            $is_fb          =   floatval( $_POST['is_fb'] );
            $is_youtube     =   floatval( $_POST['is_youtube'] );
            $is_twich       =   floatval( $_POST['is_twich'] );
            
            if($is_youtube==1){
                update_post_meta($show_id,'wpstream_youtube_rtmp',esc_html($_POST['youtube_rtmp']) );
            }else{
                update_post_meta($show_id,'wpstream_youtube_rtmp','' );
            }
            
            if($is_twich==1){
                update_post_meta($show_id,'wpstream_twich_rtmp',esc_html($_POST['twich_rtmp']) );
            }else{
                update_post_meta($show_id,'wpstream_twich_rtmp','' );
            }
            
      
            
            ////////////////////////////////////
            
            $event_data         =   $this->wpstream_request_live_stream_uri($show_id,$is_record,$userID);
            
            if(is_array($event_data)){
                $explode                =   explode('wpstream.net/',  $event_data['live_uri']);
                $uri                    =   $explode[0].'wpstream.net/wpstream/';
                $server_status_check    = 
                $stream_key             =   trim(str_replace('wpstream/',' ',$explode[1]));


                update_post_meta($show_id,'live_event_uri', $event_data['live_uri']);
                update_post_meta($show_id,'live_event_stream_name', $event_data['carnat1']);
                update_post_meta($show_id,'ip', $event_data['ip']);
                update_post_meta($show_id,'live_event_carnat2', $event_data['carnat2']);


                if( isset($event_data['dns_change_id']) && $event_data['dns_change_id']!='' ){

                        echo json_encode(   array(
                            'sometihng'             =>  'mr',
                            'conected'              =>  true,
                            'caster_url'            =>  $event_data['webcaster_url'],
                            'live_uri'              =>  $event_data['live_uri'] ,
                            'all_data'              =>  $event_data,
                            'obs_uri'               =>  $event_data['live_uri2'],
                            'obs_stream'            =>  $stream_key,
                            'server_status_check'   =>  'http://'.$event_data['ip'].':8444' ,
                            'ip'                    =>  $event_data['ip'],
                            'subdomain_key'         =>  $event_data['subdomain_key'],
                            'dns_change_id'         =>  $event_data['dns_change_id']
                            )
                        );

                }else{
                    echo json_encode(   array(
                        'conected'      =>  false,
                        'error'         =>  'Error 4176'// no aws server dns propagation
                    ));
                }

            }else{
                echo json_encode(   array(
                    'conected'      =>  false,
                    'error'         =>  $event_data
                ));
            }
            die();
    }





    private function wpstream_request_live_stream_uri($show_id,$is_record,$request_by_userid){    
            $token  = $this->wpstream_get_token();
            $domain = parse_url ( get_site_url() );

            $home_url       =   get_home_url();
            $domain_scheme  =   'http';
            $home_url       =   str_replace('https://','http://',$home_url);
            
            if(is_ssl()){
                $domain_scheme='https';
                $home_url = str_replace('http://','https://',$home_url);
            }
            
          
            
            
            $values_array=array(
                "show_id"           =>  $show_id,
                "scheme"            =>  $domain_scheme,
                "domain"            =>  $domain['host'],
                "domain_ip"         =>  $_SERVER['SERVER_ADDR'],
                "is_record"         =>  $is_record,
                "location"          =>  $home_url ,
                "request_by_userid" =>  $request_by_userid,
            );

            $url=WPSTREAM_CLUBLINKSSL."://www.".WPSTREAM_CLUBLINK."/wp-json/rcapi/v1/livestrem/new/?access_token=".$token;

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
               return ($received_data);
            }else{     
                if($received_data['data']['status']=='401'){
                    return('You are not connected to wpstream.net! Please check your WpStream credentials! ');
      
                }else{
                     return 'Failed to connect to WpStream. Please try again later.';
                }
               
            }

    }























    /**
     * Retrive auth token from tranzient
     *
     * @since    3.0.1
     * returns token
     */
    public function wpstream_get_token(){
        $token =  get_transient('wpstream_token_request');
        if ( false === $token || $token==='' ) {
            $token = $this->wpstream_club_get_token();
            set_transient( 'wpstream_token_request', $token ,600);
        }

        return $token;

    }

	
    
     /**
     * Request auth token from wpstream.net
     *
     * @since    3.0.1
     * returns token fron wpstream
     */
    protected function wpstream_club_get_token(){

        $client_id      = esc_html ( get_option('wpstream_api_key','') );
        $client_secret  = esc_html ( get_option('wpstream_api_secret_key','') );
        $username       = esc_html ( get_option('wpstream_api_username','') );
        $password       = esc_html ( get_option('wpstream_api_password','') );

        if ( $username=='' || $password==''){
            return;
        }
        $curl = curl_init();
        
        $json = array(
                'grant_type'=>'password',
                'username'  =>$username,
                'password'  =>$password,
                'client_id'=>'qxZ6fCoOMj4cNK8SXRHa5nug6vnswlFWSF37hsW3',
                'client_secret'=>'L1fzLosJf9TlwnCCTZ5pkKmdqqkHShKEi0d4oFNE'
            );

        curl_setopt_array($curl, array(
        CURLOPT_URL => WPSTREAM_CLUBLINKSSL."://www.".WPSTREAM_CLUBLINK."/?oauth=token",
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_ENCODING => "",
        CURLOPT_MAXREDIRS => 10,
        CURLOPT_TIMEOUT => 30,
        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
        CURLOPT_CUSTOMREQUEST => "POST",
    //    CURLOPT_POSTFIELDS => "grant_type=password&username=".$username."&password=".$password."&client_id=qxZ6fCoOMj4cNK8SXRHa5nug6vnswlFWSF37hsW3&client_secret=L1fzLosJf9TlwnCCTZ5pkKmdqqkHShKEi0d4oFNE",
      CURLOPT_POSTFIELDS=> json_encode($json),
        CURLOPT_HTTPHEADER => array(
         
            "cache-control: no-cache",
            "content-type: application/json",

            ),
        ));

        $response = curl_exec($curl);
        $err = curl_error($curl);
        
  

        
        
        curl_close($curl);
        $response= json_decode($response);

        if(isset($response->access_token)){
            return $response->access_token;
        }else{
            return;
        }
    }
    
 
    
    
    /**
    * Return admin package data
    *
    * @since    3.0.1
    * returns pack data
    */
    
    public function wpstream_request_pack_data_per_user(){

        if( !current_user_can('administrator') ){
            exit('okko 1');
        }
        $token          =   $this->wpstream_get_token();
        $values_array   =   array();
        $url            =   WPSTREAM_CLUBLINKSSL."://www.".WPSTREAM_CLUBLINK."/wp-json/rcapi/v1/status/packdetails/?access_token=".$token;


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
           return ($received_data);
        }else{     
            return 'failed connection';
        }

    }

    
    /**
    * Check Api Status
    *
    * @since    3.0.1
    * returns true or false
    */
    
    function wpstream_client_check_api_status(){

            $token= $this->wpstream_get_token();

            $url=WPSTREAM_CLUBLINKSSL."://www.".WPSTREAM_CLUBLINK."/wp-json/rcapi/v1/status/?access_token=".$token;
            $arguments = array(
                'method' => 'GET',
                'timeout' => 45,
                'redirection' => 5,
                'httpversion' => '1.0',
                'blocking' => true,
                'headers' => array(),
                'cookies' => array()
            );
            $response   =   wp_remote_post($url,$arguments);
            $body       =   wp_remote_retrieve_body($response);

            if ( $body === true || $body ==='true'){
                return true;
            }else{
                return false;
            } 
    }
    
     /**
    * Start Get live events for users
    *
    * @since    3.0.1
    * returns true or false
    */
    public function wpstream_get_live_event_for_user(){
        $current_user       =   wp_get_current_user();
        $allowded_html      =   array();
        $userID             =   $current_user->ID;
        $return_uri         =   '';

        if(function_exists('wpstream_get_option') && intval(wpstream_get_option('allow_streaming_regular_users',''))==1 ){
          if( !is_user_logged_in() ){
               exit('okko mnb1');
            }
        }else{
            if( !current_user_can('administrator') ){
               exit('okko mn1');
            }
        }


        $event_data         =   $this->wpstream_request_live_stream_for_user($userID);
        return $event_data;
    }
    
    
    
    
    
    
    
    
    
    /**
    * Start Get live events for users
    *
    * @since    3.0.1
    * returns true or false
    */
    public function wpstream_request_live_stream_for_user($user_id){

        if(function_exists('wpstream_get_option') && intval(wpstream_get_option('allow_streaming_regular_users',''))==1 ){
          if( !is_user_logged_in() ){
               exit('okko grt1z');
            }
        }else{
            if( !current_user_can('administrator') ){
               exit('okko grt1');
            }
        }


        $domain = parse_url ( get_site_url() );
        $token= $this->wpstream_get_token();

        $values_array=array(
            "show_id"           =>  $user_id,
            "scheme"            =>  $domain['scheme'],
            "domain"            =>  $domain['host'],
            "domain_ip"         =>  $_SERVER['SERVER_ADDR']
        );



        $url=WPSTREAM_CLUBLINKSSL."://www.".WPSTREAM_CLUBLINK."/wp-json/rcapi/v1/livestrem/peruser/?access_token=".$token;


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

        if(is_wp_error($response)){
            return 'failed connection';
        }
        if( isset($response['response']['code']) && $response['response']['code']=='200'){
           return ($received_data);
        }else{     
            return 'failed connection';
        }

    }


    
    
    /**
    * Delete event
    *
    * @since    3.0.1
    * returns noda
    */
    
    public function wpstream_close_event(){
            check_ajax_referer( 'wpstream_start_event_nonce', 'security' );
            $current_user       =   wp_get_current_user();
            $allowded_html      =   array();
            $userID             =   $current_user->ID;
            $return_uri         =   '';
            if( !current_user_can('administrator') ){
               exit('okko');
            }

            $show_id            =   intval($_POST['show_id']);
            update_post_meta ($show_id,'event_passed',1);
            die();
    }


    /**
    * Get signed upload form data
    *
    * @since    3.0.1
    * returns aws form
    */
    public function wpstream_get_signed_form_upload_data(){
            if( !current_user_can('administrator') ){
                exit('okko');
            }

            $token          =   $this->wpstream_get_token();
            $values_array   =   array();
            $url            =   WPSTREAM_CLUBLINKSSL."://www.".WPSTREAM_CLUBLINK."/wp-json/rcapi/v1/videos/get_upload_form/?access_token=".$token;


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
                return $received_data;
            }else{     
                return 'failed connection';
            }
    }
    
    /**
    * Get video from storage- clear data for front end use
    *
    * @since    3.0.1
    * returns aws data
    */
    public function wpstream_get_videos(){
            if( !current_user_can('administrator') ){
                exit('okko');
            }
            $token          =   $this->wpstream_get_token();
            $values_array   =   array();
            $url            =   WPSTREAM_CLUBLINKSSL."://www.".WPSTREAM_CLUBLINK."/wp-json/rcapi/v1/videos/get_list_row/?access_token=".$token;


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
                $video_options=array();
                foreach ($received_data as $key=>$videos){
                   $video_options[$videos['video_name_storage']]=$videos['video_name_storage'].'';
                }

                return $video_options;
            }else{     
                return 'failed connection';
            }


       
        
    }
    
    
    
    /**
    * Get video from storage- raw data
    *
    * @since    3.0.1
    * returns aws data
    */
    public function wpstream_get_videos_from_storage_raw_data( ){

            if( !current_user_can('administrator') ){
                exit('okko');
            }
            $token          =   $this->wpstream_get_token();
            $values_array   =   array();
            $url            =   WPSTREAM_CLUBLINKSSL."://www.".WPSTREAM_CLUBLINK."/wp-json/rcapi/v1/videos/get_list_row/?access_token=".$token;
          

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
                return $received_data;            
            }else{     
                return 'failed connection';
            }

    }

    
    
    /**
    * Get download link from aws
    *
    * @since    3.0.1
    * returns aws data
    */
    
    function wpstream_get_download_link(){
            if( !current_user_can('administrator') ){
                exit('okko get_download_link');
            }

            $video_name                 =   sanitize_text_field($_POST['video_name']);
            $token                      =   $this->wpstream_get_token();
            $values_array               =   array();
            $values_array['video_name'] =   $video_name;
            $url                        =   WPSTREAM_CLUBLINKSSL."://www.".WPSTREAM_CLUBLINK."/wp-json/rcapi/v1/videos/get_download_link/?access_token=".$token;


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
            $response       =   wp_remote_post($url,$arguments);
            $received_data  =   json_decode( wp_remote_retrieve_body($response) ,true);

            if( isset($response['response']['code']) && $response['response']['code']=='200'){
                print trim($received_data);
            }else{     
                return 'failed connection';
            }
            exit();
    }

    
     /**
    * Delete file from storage
    *
    * @since    3.0.1
    * 
    */
    public function wpstream_get_delete_file(){
        if( !current_user_can('administrator') ){
            exit('okko get_delete_file');
        }

        $video_name                 =   esc_html($_POST['video_name']);
        $token                      =   $this->wpstream_get_token();
        $values_array               =   array();
        $values_array['video_name'] =   $video_name;
        $url                        =   WPSTREAM_CLUBLINKSSL."://www.".WPSTREAM_CLUBLINK."/wp-json/rcapi/v1/videos/get_delete_file/?access_token=".$token;


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
        $response           =   wp_remote_post($url,$arguments);
        $received_data      =   json_decode( wp_remote_retrieve_body($response) ,true);


        if( isset($response['response']['code']) && $response['response']['code']=='200'){
            print $received_data;
        }else{     
            return 'failed connection';
        }
        exit();

    
    }

     /**
    * check if stream is live
    *
    * @since    3.0.1
    * 
    */
    public function wpstream_is_is_live($product_id){
    
            $token          =       $this->wpstream_get_token();

            $values_array=array(
                "show_id"           =>  $product_id,
            );
            $url=WPSTREAM_CLUBLINKSSL."://www.".WPSTREAM_CLUBLINK."/wp-json/rcapi/v1/livestrem/checklive/?access_token=".$token;


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

            if(is_wp_error($response)){
                return 'failed connection';
            }
            if( isset($response['response']['code']) && $response['response']['code']=='200'){
               return ($received_data);
            }else{     
                return 'failed connection';
            }
   
    }

    /**
    * get server ip for live streaming
    *
    * @since    3.0.1
    * 
    */
    public function  wpstream_get_live_stream_server($current_user,$streamname){

            $token          =       $this->wpstream_get_token();
            $values_array   =       array();
            $values_array['new_stream']     =   $streamname;

            $url            =   WPSTREAM_CLUBLINKSSL."://www.".WPSTREAM_CLUBLINK."/wp-json/rcapi/v1/livestrem/get_server_ip/?access_token=".$token;


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
                return trim($received_data);
            }else{     
                return 'failed connection';
            }
            exit();

        }
    
}// end class