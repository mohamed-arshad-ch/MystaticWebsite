<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */


/**
 * Description of class-wpstream-player
 *
 * @author cretu
 */
class Wpstream_Player{
    public function __construct($plugin_main) {
        $this->main = $plugin_main;
          
        add_filter( 'the_content',array($this, 'wpstream_filter_the_title') );
        add_action( 'woocommerce_before_single_product', array($this,'wpstream_user_logged_in_product_already_bought') );
    }
    
    
    
    
    /**
    * Insert player in page
    *
    * @author cretu
    */
    public function wpstream_filter_the_title( $content   ) {
            if( is_singular('wpstream_product')){
                global $post;
                $args=array('id'=>$post->ID);
                $custom_content = $this->wpstream_insert_player_inpage($args);
                $content = '<div class="wpestream_inserted_player">'.$custom_content.'</div>'.$content;
                return $content;
            }else{
                return $content;
            }
    }
    
    /**
    * Insert player in page
    *
    * @author cretu
    */

    public function wpstream_insert_player_inpage($attributes, $content = null){
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
            $this->wpstream_video_player_shortcode($product_id);
        $return_string= ob_get_contents();
        ob_end_clean(); 

        return $return_string;
    }

    
    
    
    /**
    * Video Player shortcode
    *
    * @author cretu
    */

    public function wpstream_video_player_shortcode($from_sh_id='') {

        if ( is_user_logged_in() ) {
            global $product;
            $current_user   =   wp_get_current_user();
            $product_id     =   intval($from_sh_id);


            if ( ( in_array( 'woocommerce/woocommerce.php', apply_filters( 'active_plugins', get_option( 'active_plugins' ) ) ) && wc_customer_bought_product( $current_user->user_email, $current_user->ID, $product_id) ) || get_post_type($product_id)=='wpstream_product' ){
                global $product;
                echo '<div class="wpstream_player_wrapper wpstream_player_shortcode"><div class="wpstream_player_container">';


                if( get_post_type($product_id) == 'wpstream_product' ){

                    $wpstream_product_type =    esc_html(get_post_meta($product_id, 'wpstream_product_type', true));
                    if($wpstream_product_type==1){
                        $this->wpstream_live_event_player($product_id);
                    } else if($wpstream_product_type==2 || $wpstream_product_type==3){
                        $this->wpstream_video_on_demand_player($product_id);
                    }

                }else{
                    $term_list                  =   wp_get_post_terms($product_id, 'product_type');
                    $is_subscription_live_event =   esc_html(get_post_meta($product_id,'_subscript_live_event',true));

                    if( $term_list[0]->name=='live_stream' || ( $term_list[0]->name=='subscription' && $is_subscription_live_event=='yes' ) ){
                        $this->wpstream_live_event_player($product_id);
                    }else if( $term_list[0]->name=='video_on_demand'  || ($term_list[0]->name=='subscription' && $is_subscription_live_event=='no' ) ){
                        $this->wpstream_video_on_demand_player($product_id);
                    }
                }



            }else{
                $thumb_id           =   get_post_thumbnail_id($product_id);
                $thumb              =   wp_get_attachment_image_src($thumb_id,'medium_large');

                echo '<div class="wpstream_player_wrapper wpstream_player_shortcode no_buy"><div class="wpstream_player_container">';
                echo '<div class="wpstream_notice">'.__('You did not buy this product!','wpstream').
                      '</div><img src="'.$thumb[0].'" alt="product_thumb">';
            }

            echo '</div></div>';
        }else{
            $product_id     =   intval($from_sh_id);
            if( get_post_type($product_id) == 'wpstream_product' ){

                    $wpstream_product_type =    esc_html(get_post_meta($product_id, 'wpstream_product_type', true));
                    if($wpstream_product_type==1){
                        $this->wpstream_live_event_player($product_id);
                    } else if($wpstream_product_type==2 || $wpstream_product_type==3){
                        $this->wpstream_video_on_demand_player($product_id);
                    }

            }
        }
    }

    
    
    
    
    
    /**
    * Live Event Player
    *
    * @author cretu
    */
    function remove_http($url) {
        $disallowed = array('http://', 'https://');
        foreach($disallowed as $d) {
           if(strpos($url, $d) === 0) {
              return str_replace($d, '', $url);
           }
        }
        return $url;
    }
    
    
    /**
    * Live Event Player
    *
    * @author cretu
    */
    
    function wpstream_live_event_player($product_id){
            $live_event_uri         =   get_post_meta($product_id,'live_event_uri',true); 
            $thumb_id               =   get_post_thumbnail_id($product_id);
            $thumb                  =   wp_get_attachment_image_src($thumb_id,'small');
            $live_event_uri_final   =   $this->wpstream_request_hls_player($product_id);
            $now                    =   time().rand(0,10);
            $live_conect_array      =   explode('live.streamer.wpstream.net',$live_event_uri_final);
            $live_conect_views      =   $live_conect_array[0].'live.streamer.wpstream.net';
            $live_conect_views      =   $this->remove_http($live_conect_views);
           
            
            echo '
                
                <div class="wpstream_live_player_wrapper" id="wpstream_live_player_wrapper'.$now.'" > '
                    . '<div id="wpestream_live_counting" class="wpestream_live_counting"></div>';
                  
                    if(trim($live_event_uri_final)==''){
                        print '<div class="wpstream_not_live_mess"><div class="wpstream_not_live_mess_back"></div><div class="wpstream_not_live_mess_mess">'.esc_html__('We are not live at this moment. Please check back later.','wpstream').'</div></div>';
                    }else{
                           print '<script type="text/javascript">
                                //<![CDATA[
                                    jQuery(document).ready(function(){
                                        wpstream_count_connect_plugin("wpstream_live_player_wrapper'.$now.'" ,"'.$live_conect_views.'");
                                    });
                                //]]>
                            </script>';
                    }
            
                    echo'
                    <video id="wpstream-video'.$now.'"     poster="'.$thumb[0].'" class="video-js vjs-default-skin  vjs-16-9" controls>
                    <source
                        src="'.$live_event_uri_final.'"
                        type="application/x-mpegURL">
                    </video>';

                    print '<script type="text/javascript">
                                //<![CDATA[
                                    jQuery(document).ready(function(){
                                        wpstream_player_initialize("'.$now.'","'.$live_event_uri_final.'","'.$live_conect_views.'");
                                    });
                                //]]>
                            </script>';

               print '</div>';
               usleep (10000);

        }



        /**
        * HLS PLAYER
        *
        * @author cretu
        */
    

        public function wpstream_request_hls_player($product_id){
            
            $transient_name = 'hls_to_return_'.$product_id;
            $hls_to_return = get_transient( $transient_name );
            
            if ( false ===  $hls_to_return  ) {
            
                $show_id        =   intval($product_id);
                $token          =   $this->main->wpstream_live_connection->wpstream_get_token();
                $values_array   =   array();
                $values_array['show_id'] =   $show_id;

                $url            =   WPSTREAM_CLUBLINKSSL."://www.".WPSTREAM_CLUBLINK."/wp-json/rcapi/v1/videos/get_player_hls/?access_token=".$token;


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
                    $hls_to_return =  trim($received_data);
                    set_transient( $transient_name, $hls_to_return, 120 );
                    return $hls_to_return =  trim($received_data);
                }else{     
                    return 'failed connection';
                }
                exit();
            }else{
               return $hls_to_return;
            }

        }



        /**
        * VODPlayer
        *
        * @author cretu
        */

        public function wpstream_video_on_demand_player($product_id){
                $thumb_id               =   get_post_thumbnail_id($product_id);
                $thumb                  =   wp_get_attachment_image_src($thumb_id,'small');
                $wpstream_data_setup    =   '  data-setup="{}" ';
                
                /* free_video_type
                 * 1 - free live channel
                 * 2 - free video encrypted
                 * 3 - free video -not encrypted
                 */
                $free_video_type        =   intval( get_post_meta($product_id, 'wpstream_product_type', true));
                 

                if($free_video_type==2 || get_post_type($product_id)=='product' ){
                    
                    /* IF vide is encrypted-  readed from vod,streaner
                     */
                    
                    $video_type         =   'application/x-mpegURL';
                    $video_path         =   get_post_meta($product_id,'_movie_url',true); 
                    if(get_post_type($product_id)=='wpstream_product'){
                        $video_path =    esc_html(get_post_meta($product_id, 'wpstream_free_video', true));
                    }

                    $username           =   esc_html ( get_option('wpstream_api_username','') );
                    if (filter_var($username, FILTER_VALIDATE_EMAIL)) {
                        $username = $this->wpstream_retrive_username();
                    }

                    if(strpos($username,'@')!=false){
                        $username_array= explode('@', $username);
                        $username=$username_array[0];
                    }

                    $video_path_final   =   'https://vod.streamer.wpstream.net/'.$username.'/'.$video_path.'/index.m3u8?'.get_site_url();
                    if(!is_ssl()){
                        $video_path_final   =   'http://vod.streamer.wpstream.net/'.$username.'/'.$video_path.'/index.m3u8?'.get_site_url(); 
                    }
                    
                }else if($free_video_type==3){
                    
                    /* Video is unecrypted - read from local or youtube / vimeo
                    */
                    
                    $video_type         =   'video/mp4';
                    $video_path_final=esc_html(get_post_meta($product_id, 'wpstream_free_video_external', true));

                    if (strpos($video_path_final, 'www.youtube') !== false) {
                        $wpstream_data_setup= '    data-setup=\'{ "techOrder": ["youtube"], "sources": [{ "type": "video/youtube", "src": "'.$video_path_final.'"}] }\'   '; 
                        $video_path_final='';
                    }
                    if (strpos($video_path_final, 'vimeo.com') !== false) {
                        $wpstream_data_setup= '   data-setup=\'{"techOrder": ["vimeo"], "sources": [{ "type": "video/vimeo",  "src": "'.$video_path_final.'"}], "vimeo": { "color": "#fbc51b"} }\'   '; 
                        $video_path_final='';
                    }

                }

                    echo '<video id="wpstream-video'.time().'" class="video-js vjs-default-skin  vjs-16-9 kuk wpstream_video_on_demand" controls preload="auto"
                            poster="'.$thumb[0].'" '.$wpstream_data_setup.'>

                            <source src="'.trim($video_path_final).'"  type="'.$video_type.'">
                            <p class="vjs-no-js">
                              To view this video please enable JavaScript, and consider upgrading to a web browser that
                              <a href="http://videojs.com/html5-video-support/" target="_blank">supports HTML5 video</a>
                            </p>
                        </video>';

        }


        
        /**
        * Retreive username for vod path
        *
        * @author cretu
        */
        private function wpstream_retrive_username(){
                $token          =   $this->main->wpstream_live_connection->wpstream_get_token();

                $url=WPSTREAM_CLUBLINKSSL."://www.".WPSTREAM_CLUBLINK."/wp-json/rcapi/v1/return_login/?access_token=".$token;
                $arguments = array(
                    'method' => 'GET',
                    'timeout' => 45,
                    'redirection' => 5,
                    'httpversion' => '1.0',
                    'blocking' => true,
                    'headers' => array(),
                    'cookies' => array()
                );
                $response       =   wp_remote_post($url,$arguments);
                $received_data  =   json_decode( wp_remote_retrieve_body($response) ,true);
                if( isset($response['response']['code']) && $response['response']['code']=='200'){
                    return ($received_data); die(); 
                }else{     
                    print 'Failed to conbect media server';
                    die(); 
                }
        }
        
        
        
        
        
           /**
	 * check if the user bought the product and display the player - TO REDo
	 *
	 * @since     3.0.1
         * returns html of the player
	*/
        public function wpstream_user_logged_in_product_already_bought($from_sh_id='') {

            if ( is_user_logged_in() ) {
                global $product;
                $current_user   =       wp_get_current_user();
                $product_id     =       $product->get_id();


                if ( wc_customer_bought_product( $current_user->user_email, $current_user->ID, $product_id) || ( function_exists('wcs_user_has_subscription') && wcs_user_has_subscription( $current_user->ID, $product_id ,'active') ) ){


                    echo '<div class="wpstream_player_wrapper"><div class="wpstream_player_container">';

                    $is_subscription_live_event =   esc_html(get_post_meta($product_id,'_subscript_live_event',true));
                    $term_list                  =   wp_get_post_terms($product_id, 'product_type');
                   

                    if( $term_list[0]->name=='live_stream' || ($term_list[0]->name=='subscription' && $is_subscription_live_event=='yes' )  ){
                        $this->wpstream_live_event_player($product_id);
                    }else if( $term_list[0]->name=='video_on_demand'  || ($term_list[0]->name=='subscription' && $is_subscription_live_event=='no' ) ){
                        $this->wpstream_video_on_demand_player($product_id);
                    }

                }else{
                    echo '<div class="wpstream_player_wrapper no_buy"><div class="wpstream_player_container">';
                    echo '<div class="wpstream_notice">'.__('You did not buy this product!','wpstream').'</div>';
                }

                echo '</div></div>';
            }
        }

}
