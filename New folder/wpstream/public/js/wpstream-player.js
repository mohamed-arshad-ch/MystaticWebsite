/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 * player,'.$live_event_uri_final.','.$live_conect_views.'
 */

window.WebSocket = window.WebSocket || window.MozWebSocket;
if (!window.WebSocket) {
  console.log("Sorry, but your browser doesnt support WebSockets");
}

                  

var timeQueue;

function wpstream_player_initialize(now,live_event_uri_final,live_conect_views){
 
    var player = videojs('wpstream-video'+now,{
            html5: {
            hls: {
                bandwidth: 500000,
                useBandwidthFromLocalStorage: true,
                overrideNative: !videojs.browser.IS_SAFARI,
                smoothQualityChange: true
                }
            },
            errorDisplay: false,
            autoplay:true,
            preload:"auto"
    });
    
    
    wpstream_player_load(player,live_event_uri_final,live_conect_views);
    
    jQuery("#wpestream_live_counting").appendTo(jQuery('#wpstream-video'+now));
    
    
    
    timeQueue = [];
    setInterval(function(){
        timeQueue.push(player.currentTime());
        if (timeQueue.length > 30){
            timeQueue.shift();
            if (timeQueue[0] == timeQueue[timeQueue.length -1]){

                if (!player.paused() || player.currentTime() == 0){
                    timeQueue = [];
                    try{
                        player.currentTime(0);
                    }catch(err){

                    }
                    wpstream_player_load(player,live_event_uri_final,live_conect_views);
                }

            }
        }
    }, 1000);
    

}





function wpstream_player_load(player,live_event_uri_final,live_conect_views){
    player.src({
        src:  live_event_uri_final,
        type: "application/x-mpegURL"
    });
    player.play();
  
}





function wpstream_count_connect_plugin(player,live_conect_views){

    console.log("connecting counter...");
    var connection = new WebSocket("wss://"+live_conect_views+":10111");
    connection.onopen = function () {
      console.log("connected.")
    };

    connection.onclose = function(){
      console.log("closed. reconnecting...");
      setTimeout(function(){ wpstream_count_connect_plugin(live_conect_views) }, 5 * 1000);
    }

    connection.onerror = function (error) {
      console.log("onerror: ", error);
    };

    connection.onmessage = function (message) {
        try {
            var json = JSON.parse(message.data);
        } catch (e) {
            console.log("Invalid JSON: ", message.data);
            return;
        }
        if (json.type === "viewerCount") { 
            count = json.data;
            console.log("viewers: " + count)
            var view_box=jQuery("#"+player+" .wpestream_live_counting");
            view_box.css("background-color","#c91d1d");
            view_box.html( count + " Viewers");

        } else {
            console.log("Unknown type:", json);
        }
    };
}
