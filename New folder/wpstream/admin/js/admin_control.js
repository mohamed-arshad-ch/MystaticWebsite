/*global $, jQuery, */
var counters={};
    
jQuery(document).ready(function ($) {
    "use strict";
    
    wpstream_check_live_connections();
    generate_download_link();
    generate_delete_link();
    social_media_toggle('wpstream_on_facebook');
    social_media_toggle('wpstream_on_youtube');
    social_media_toggle('wpstream_on_twich');
    
    
    function social_media_toggle(social_class){
        
        jQuery('.'+social_class).on('change',function(){

            if( $(this).prop('checked') ){
                jQuery($(this).parent().parent().find( '.'+social_class+'_container' )).slideDown('100');
            }else{
                jQuery($(this).parent().parent().find( '.'+social_class+'_container' )).slideUp('100');
            }  

        });
    }
    
    
    
    jQuery('.wpestate_notices .notice-dismiss').on('click',function(){
       
        var ajaxurl     = wpstream_admin_control_vars.admin_url + 'admin-ajax.php';
        var notice_type = $(this).parent().attr('data-notice-type');
        var nonce       = $('#wpstream_notice_nonce').val();
        

        jQuery.ajax({
            type: 'POST',
            url: ajaxurl,
            data: {
                'action'                    :   'wpstream_update_cache_notice',
                'notice_type'               :   notice_type,
                'security'                  :   nonce
            },
            success: function (data) {     

            
            },
            error: function (errorThrown) { 
              
            }
        });
    });
    
    
    
    
    function generate_delete_link(){
        $('.wpstream_delete_media').on('click',function(){
            
           
            var ajaxurl             =   wpstream_admin_control_vars.admin_url + 'admin-ajax.php';
            var video_name          =   $(this).attr('data-filename').trim();
            var acesta              =   $(this);
            var parent              =   $(this).parent();

            console.log("video_name "+video_name);
            jQuery.ajax({
                type: 'POST',
                url: ajaxurl,

                data: {
                    'action'            :   'wpstream_get_delete_file',
                    'video_name'        :   video_name

                },
                success: function (data) {
                    parent.remove();
                    console.log(data);

                },
                error: function (errorThrown) {
                }
            });
        });

    
    }
    
    function generate_download_link(){
            
        $('.wpstream_get_download_link').on('click',function(){
            var ajaxurl             =   wpstream_admin_control_vars.admin_url + 'admin-ajax.php';
            var video_name          =   $(this).attr('data-filename');
            var acesta              =   $(this);
            var parent              =   $(this).parent();

            jQuery(this).remove();
            parent.find('.wpstream_download_link').show().text('please wait...');



            jQuery.ajax({
                type: 'POST',
                url: ajaxurl,

                data: {
                    'action'            :   'wpstream_get_download_link',
                    'video_name'        :   video_name,

                },
                success: function (data) {

                    if(String(data)==='toolarge'){
                         parent.find('.wpstream_download_link').show().text(wpstream_admin_control_vars.no_band); 
                    }else{
                        parent.find('.wpstream_download_link').show().text(wpstream_admin_control_vars.download_mess);
                        parent.find('.wpstream_download_link').show().attr('href',data);

                    }

                },
                error: function (errorThrown) {
                }
            });
        });
    
    }

    
  
    
    
    $( '.inputfile' ).each( function(){
		var $input	 = $( this ),
			$label	 = $input.next( 'label' ),
			labelVal = $label.html();

		$input.on( 'change', function( e )
		{
			var fileName = '';

			if( this.files && this.files.length > 1 )
				fileName = ( this.getAttribute( 'data-multiple-caption' ) || '' ).replace( '{count}', this.files.length );
			else if( e.target.value )
				fileName = e.target.value.split( '\\' ).pop();

			if( fileName )
				$label.find( 'span' ).html( fileName );
			else
				$label.html( labelVal );
		});

		// Firefox bug fix
		$input
		.on( 'focus', function(){ $input.addClass( 'has-focus' ); })
		.on( 'blur', function(){ $input.removeClass( 'has-focus' ); });
    });
    
    var form = $('.direct-upload');
    var filesUploaded = [];
    var folders = [];

   // var new_file_name='';  
    
    form.fileupload({
        url: form.attr('action'),
        type: form.attr('method'),
        datatype: 'xml',
            add: function (event, data) {
               
               console.log(data);
               if( data.files[0].type!=='video/mp4' && data.files[0].type!=='video/quicktime'){
                    jQuery('#wpstream_uploaded_mes').empty().html(wpstream_admin_control_vars.not_accepted);
                   return;
               }
                    
          
             
               var file_size=(parseInt(data.files[0].size,10))/1000000;
               var user_storage = jQuery('#wpstream_storage').val();
               var user_band    = jQuery('#wpstream_band').val();
               
         
               
               if(file_size > user_storage || file_size>user_band){
                    jQuery('#wpstream_uploaded_mes').empty().html(wpstream_admin_control_vars.no_band_no_store);
                    return;
               }
               
               
                $('#wpstream_label_action').text(wpstream_admin_control_vars.uploading)
                
                jQuery('#wpstream_uploaded_mes').empty().html();
                // Show warning message if your leaving the page during an upload.
                window.onbeforeunload = function () {
                    return 'You have unsaved changes.';
                };

                var file = data.files[0];
                
                console.log('file'+JSON.stringify(file));
                //var filename = Date.now() + '.' + file.name.split('.').pop();
               
//                new_file_name=file.name;
               
                form.find('input[name="Content-Type"]').val(file.type);
                form.find('input[name="Content-Length"]').val(file.size);

                // Actually submit to form to S3.
                data.submit();

                // Show the progress bar
                // Uses the file size as a unique identifier
                var bar = $('<div class="progress" data-mod="'+file.size+'"><div class="bar"></div></div>');
                $('.progress-bar-area').append(bar);
                bar.slideDown('fast');
            },
            progress: function (e, data) {
                // This is what makes everything really cool, thanks to that callback
                // you can now update the progress bar based on the upload progress.
                var percent = Math.round((data.loaded / data.total) * 100);
                $('.progress[data-mod="'+data.files[0].size+'"] .bar').css('width', percent + '%').html(percent+'%');
            },
            
            fail: function (e, data) {
                // Remove the 'unsaved changes' message.
                window.onbeforeunload = null;
                $('.progress[data-mod="'+data.files[0].size+'"] .bar').css('width', '100%').addClass('red').html('');
            },
            done: function (event, data) {
                console.log(data);
                window.onbeforeunload = null;
                $('.bar').remove();
                $('#wpstream_uploaded_mes').empty().html(wpstream_admin_control_vars.upload_complete);
                $('#wpstream_label_action').text(wpstream_admin_control_vars.upload_complete2);

                var new_file_name=data.files[0].name;

                
                var new_file_name_array=  data.files[0].name.split(".");
                var temp_file_name= new_file_name_array[0].split(' ').join('_');
                temp_file_name= temp_file_name.replace(/\W/g, '');           
                new_file_name=temp_file_name+'.'+new_file_name_array[new_file_name_array.length-1];
                
                
                
                
                var onclick_string=' Are you sure you wish to delete '+new_file_name+' ? ';

                var to_insert='<div class="wpstream_video_wrapper"><div class="wpstream_video_title"><div class="wpstream_video_notice"></div></div><div class="wpstream_video_title">';
                to_insert=to_insert+'<strong class="storage_file_name">File Name :</strong><span class="storage_file_name_real">'+new_file_name+' </span></div>';
                to_insert=to_insert+'<div class="wpstream_delete_media"  '; 
                to_insert=to_insert+' onclick=" return confirm('+onclick_string+') "';
                to_insert=to_insert+' data-filename="'+new_file_name+'"  >delete file</div>';
                to_insert=to_insert+'<div class="wpstream_get_download_link" data-filename="'+new_file_name+'">get download link</div> ';
                to_insert=to_insert+'<a href="" class="wpstream_download_link">Click to download! The url will work for the next 20 minutes!</a></div>';
                
                $('#video_management_title').after(to_insert);

                $('.wpstream_get_download_link').unbind('click');
                $('.wpstream_delete_media').unbind('click');

                generate_download_link();
                generate_delete_link();

               
            }
    });
            
    
  

    
    jQuery('.copy_live_uri').click(function(){
        var value_uri = jQuery(this).parent().find('.wpstream_live_uri_text').text();
        var temp = jQuery("<input>");
        jQuery("body").append(temp);
        jQuery(temp).val(value_uri).select();
        document.execCommand("copy");
        jQuery(temp).remove();
        
    });
    
    jQuery('.copy_live_key').click(function(){
        var value_uri = jQuery(this).parent().find('.wpstream_live_key_text').text();
        var temp = jQuery("<input>");
        jQuery("body").append(temp);
        jQuery(temp).val(value_uri).select();
        document.execCommand("copy");
        jQuery(temp).remove();
    });
    
    
    jQuery('#product-type').on('change',function(){
        
        var product_type= jQuery('#product-type').val();
        if(product_type==='live_stream' || product_type==='video_on_demand'){
            jQuery('._sold_individually_field').show();
        }
        
    });
    
    if(wpstream_findGetParameter('new_video_name')!=='' && wpstream_findGetParameter('new_video_name')!=null ){
        jQuery('#product-type').val('video_on_demand').trigger('change');
    }
    
    if(wpstream_findGetParameter('new_stream')!=='' && wpstream_findGetParameter('new_stream')!=null ){
        jQuery('#product-type').val('live_stream').trigger('change');
    }
    
    var product_type=  jQuery('#product-type').val();
  
    if ( product_type === 'video_on_demand' ) {
        jQuery('.show_if_video_on_demand' ).show();
       
    } 
    else  if ( product_type === 'live_stream' ) {
        $( '.show_if_live_stream' ).show();
        
    }
            
  

  
    
    function wpstream_findGetParameter(parameterName) {
        var result = null,
            tmp = [];
        location.search
            .substr(1)
            .split("&")
            .forEach(function (item) {
              tmp = item.split("=");
              if (tmp[0] === parameterName) result = decodeURIComponent(tmp[1]);
            });
        return result;
    }


    jQuery('#_subscript_live_event').change(function(){
        //alert('move it'+product_type);
        if ( product_type === 'video_on_demand' || product_type === 'live_stream' ) {
        
        }else{
            var value= jQuery(this).val();     
            if(value==="no"){
                jQuery("._movie_url_field").parent().removeClass("hide_if_subscription").show();
            }else{
                jQuery("._movie_url_field").parent().addClass("hide_if_subscription").hide();
            }
        }
    });

    jQuery('#_subscript_live_event').trigger('change');
   

    $('#wpstream_product_type').change(function(){
        $('.video_free').hide();
        $('.video_free_external').hide();
  
        if( $('#wpstream_product_type').val()=== "2"){
            $('.video_free').show();
        }
        if( $('#wpstream_product_type').val()=== "3"){
            $('.video_free_external').show();
        }
    });
    $('#wpstream_product_type').trigger('change');
    
 

    $('#wpstream_free_video_external_button').on( 'click', function(event) {
        var formfield = $('#wpstream_free_video_external').attr('name');
        tb_show('', 'media-upload.php?type=image&amp;TB_iframe=true');
        window.send_to_editor = function (html) {
            var pathArray = html.match(/<media>(.*)<\/media>/);
            var mediaUrl = pathArray != null && typeof pathArray[1] != 'undefined' ? pathArray[1] : '';
          
            if(mediaUrl===''){
               mediaUrl = jQuery(html).attr("href");
            }
            jQuery('#wpstream_free_video_external').val(mediaUrl);
            tb_remove();
        };
        return false;
    });
    
    $('.close_event').click(function(event){
        event.preventDefault();
        var ajaxurl             =   wpstream_admin_control_vars.admin_url + 'admin-ajax.php';
        var acesta              =   $(this);
        var parent              =   $(this).parent().parent();
        var notification_area   =   $(this).parent().find('.event_list_unit_notificationx');
        var show_id             =   parseFloat( $(this).attr('data-show-id') );
        var nonce               =   $('#wpstream_start_event_nonce').val();
        $(this).unbind();
        notification_area.text('Closing Event');
    
        
        jQuery.ajax({
            type: 'POST',
            url: ajaxurl,
            dataType: 'json',
            data: {
                'action'            :   'wpstream_close_event',
                'security'          :   nonce,
                'show_id'           :   show_id
            },
            success: function (data) {       
                parent.remove();            
            },
            error: function (errorThrown) {
              
            }
        });
        
    });
    
    $('.start_webcaster').on('click',function(){
        var caster_url = $(this).attr('data-webcaster-url');
        $(this).parent().find('.external_software_streaming').slideUp()
        window.open(caster_url, '_blank', 'location=yes,height=390,width=660,scrollbars=yes,status=yes');
    })
      
    $('.start_external').on('click',function(){
        $(this).parent().parent().find('.external_software_streaming').slideToggle();
    });
    
    
    function wpstream_check_live_connections(){
        
        if(  $('.pending_streaming.pending_trigger').length>0 ){
            $('.pending_streaming.pending_trigger').each(function(){
                var server_url  =   $(this).attr('data-server-url');
                var counter     =   '';
                var acesta      =   $(this);


                var show_id  =   $(this).attr('data-server-id'); 
                wpstream_check_live_connections_from_database(acesta,show_id);
                var counter_long     =   '';
                counter_long =  setInterval( function (){ wpstream_check_live_connections_from_database(acesta,show_id)},60000);
                counters[show_id]=counter_long;

            });
        }
     
    }

    
    
    function wpstream_check_live_connections_step( acesta,server_url){

        var server_status = wpstream_check_server_status(server_url,
            function( server_status ){

                if(server_status ){
                    acesta.removeClass('show_stream_data').addClass('hide_stream_data');
                    acesta.parent().find('.wpstream_ready_to_stream').removeClass('hide_stream_data').addClass('show_stream_data');
                    clearInterval( counters[server_url]);
                }
            });

    }
    
    function wpstream_check_live_connections_on_start( parent,server_url,data){
        
        // server_url is dns change
        wpstream_check_dns_avb_callback(data.dns_change_id,
            function(server_status){
              
                if(server_status ){
                    clearInterval( counters[server_url]);
                    
                    
                    console.log(parent);
                    parent.find('.pending_streaming').removeClass('show_stream_data').addClass('hide_stream_data');
                    parent.find('.wpstream_ready_to_stream ').removeClass('hide_stream_data').addClass('show_stream_data');
                    parent.find('.external_software_streaming  ').removeClass('hide_stream_data').addClass('show_stream_data');
                    parent.find('.view_channel').addClass('show_stream_data');
                    
                    parent.find('.wpstream_ready_to_stream .start_webcaster').attr('data-webcaster-url',data.caster_url);
                    parent.find('.wpstream_live_uri_text').text(data.obs_uri);
                    parent.find('.wpstream_live_key_text').text(data.obs_stream);
                 
                }
        });

    }
    
    function wpstream_check_live_connections_from_database( acesta,show_id){

        var server_status = wpstream_check_server_status_from_db(show_id,
            function(server_status){
     
                if(server_status ){
             
                    console.log(acesta);
                    acesta.removeClass('show_stream_data').addClass('hide_stream_data');
                    acesta.parent().find('.wpstream_ready_to_stream').removeClass('hide_stream_data').addClass('show_stream_data');
                    acesta.parent().find('.view_channel').addClass('show_stream_data');
                    clearInterval( counters[show_id]);
                }
            });

    }
    
    function wpstream_check_server_status_from_db(show_id,callback){
 
        var ajaxurl             =   wpstream_admin_control_vars.admin_url + 'admin-ajax.php';
        jQuery.ajax({
            type: 'POST',
            url: ajaxurl,
            dataType: 'json',
            data: {
                'action'            :   'wpstream_check_server_against_db',
                'show_id'           :   show_id,
            },
            success: function (data) {
                console.log(data);
                if(data.islive){
                    callback (true);
                }else{
                    callback (false);
                }
                
            },
            error: function (errorThrown) {
            }
        });
    }
    
    function wpstream_check_server_status(url_param,callback) {
    
        var url = url_param ;
        var status='';
        $.ajax({
            url: url,
            type: "get",
            cache: false,
            dataType: 'jsonp', // it is for supporting crossdomain
            crossDomain : true,
            asynchronous : false,
            timeout : 1500, // set a timeout in milliseconds
            callback:'',
            complete : function(xhr) {
       
                if(xhr.status == "200" || xhr.status == "400") {
                    callback(true);
                }
                else {
                    callback(false);
                }
            }
       });
    }
            
    $('.start_event').click(function(event){
        event.preventDefault();
        var ajaxurl             =   wpstream_admin_control_vars.admin_url + 'admin-ajax.php';
        var acesta              =   $(this);
        var notification_area   =   $(this).parent().find('.event_list_unit_notification');
        var curent_content      =   $(this).parent().find('.server_notification');
        var is_record           =   0;
        var show_id             =   parseFloat( $(this).attr('data-show-id') );
        var nonce               =   $('#wpstream_start_event_nonce').val();
        var parent              =   $(this).parent().parent();
        
        if( $(this).parent().find('.record_event').is(":checked") ){
            is_record   =   1;
        }
        
        var is_fb               =   0;
        var is_youtube          =   0;
        var is_twich            =   0;
        var youtube_rtmp        =   $(this).parent().find('.wpstream_youtube_rtmp').val();
        var twich_rtmp          =   $(this).parent().find('.wpstream_twich_rtmp').val();
        
        if( $(this).parent().find('.wpstream_on_facebook').is(":checked") ){
            is_fb   =   1;
        }
        
        if( $(this).parent().find('.wpstream_on_youtube').is(":checked") ){
            is_youtube   =   1;
        }
        
        if( $(this).parent().find('.wpstream_on_twich').is(":checked") ){
            is_twich   =   1;
        }
        
        
        $(this).unbind();
        $(this).hide();
        parent.find('.record_wrapper').hide();
        parent.find('.close_event ').hide();
        curent_content.html('<div class="wpstream_channel_status not_ready_to_stream"><span class="dashicons dashicons-dismiss"></span>Getting ready to stream. Please wait...<img class="" src="'+wpstream_admin_control_vars.loading_url+'" alt="loading"></div>');
        parent.find('.multiple_warning_events').html('* Starting a channel can take 1-2 minutes or more. Please be patient.');
    

        jQuery.ajax({
            type: 'POST',
            url: ajaxurl,
            dataType: 'json',
            timeout: 300000,

            data: {
                'action'            :   'wpstream_give_me_live_uri',
                'security'          :   nonce,
                'show_id'           :   show_id,
                'is_record'         :   is_record,
                'is_fb'             :   is_fb,
                'is_youtube'        :   is_youtube,
                'is_twich'          :   is_twich,
                'youtube_rtmp'      :   youtube_rtmp,
                'twich_rtmp'        :   twich_rtmp
                
            },
            success: function (data) {
               
                if(data.conected===true){
                        console.log(data);
                        curent_content.empty();
                        parent.find('.wpstream_no_stream').removeClass('show_stream_data').addClass('hide_stream_data');
                        parent.find('.pending_streaming').removeClass('hide_stream_data').addClass('show_stream_data');         
                        var counter =  setInterval( function (){ wpstream_check_live_connections_on_start(parent,data.server_status_check,data)},10000);
                        counters[data.server_status_check]=counter;
    
            
                }else{
                    parent.find('.wpstream_no_stream').addClass('show_stream_data').empty().html(data.error);
                }
                
            },
            error: function (jqXHR,textStatus,errorThrown) {
             
            }
        });
        
    });
    
    $('.category_featured_image_button').on( 'click', function() {
        var parent = $(this).parent();
        var formfield  = parent.find('#category_featured_image').attr('name');
        tb_show('', 'media-upload.php?type=image&amp;TB_iframe=true');
        window.send_to_editor = function (html) {
            var imgurl = $('img', html).attr('src');
            parent.find('#category_featured_image').val(imgurl);
            var theid = $('img', html).attr('class');
            var thenum = theid.match(/\d+$/)[0];
            parent.find('#category_attach_id').val(thenum);
            tb_remove();
        };
        return false;
    });
  

});

  

function wpstream_check_dns_avb_callback(url_param,callback){
   
       var ajaxurl             =   wpstream_admin_control_vars.admin_url + 'admin-ajax.php';
        jQuery.ajax({
            type: 'POST',
            url: ajaxurl,
            timeout: 3000000,

            data: {
                'action'            :   'wpstream_check_dns_sync',
                'change_id'          :   url_param,
             
            },
            success: function (data) {
               
                if(data=='INSYNC'){
                    callback(true);
                }else{
                    callback(false); 
                }
                
            }, error: function (jqXHR,textStatus,errorThrown) {
              
            }
      });
}


function wpstream_enable_cliboard(parent){
    
    jQuery(parent).find('.copy_live_uri').click(function(){
        alert('click');
        var value_uri = jQuery(parent).find('.wpstream_live_uri_text').text();
        var temp = jQuery("<input>");
        jQuery("body").append(temp);
        jQuery(temp).val(value_uri).select();
        document.execCommand("copy");
        jQuery(temp).remove();
        
    });
    
    jQuery(parent).find('.copy_live_key').click(function(){
        var value_uri = jQuery(parent).find('.wpstream_live_key_text').text();
        var temp = jQuery("<input>");
        jQuery("body").append(temp);
        jQuery(temp).val(value_uri).select();
        document.execCommand("copy");
        jQuery(temp).remove();
        
    });
}