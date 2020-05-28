(function () {
    "use strict";
    tinymce.create('tinymce.plugins.wpstream_player', {
        init: function (ed, url) {
            ed.addButton('wpstream_player', {
                title: 'WpStream Player',
                image: url + '/button.png',
                onclick: function () {
                    ed.selection.setContent('[wpstream_player id="Add here the live stream id or the video id" ][/wpstream_player]');
                }
            });
        },
        createControl: function (n, cm) {
            return null;
        }
    });
    tinymce.PluginManager.add('wpstream_player', tinymce.plugins.wpstream_player);
})();


(function () {
    tinymce.create('tinymce.plugins.wpstream_list_products', {
        
        init: function (ed, url) {
            ed.addButton('wpstream_list_products', {
                title: 'WpStream List Products',
                image:  url + '/list_media.png',
                onclick: function () {
                    ed.selection.setContent('[wpstream_list_products media_number="No of media" product_type="Free Live Channel or Free Video"][/wpstream_list_products]');
                }
            });
        },
        createControl: function (n, cm) {
            return null;
        }
    });
    tinymce.PluginManager.add('wpstream_list_products', tinymce.plugins.wpstream_list_products);
})();

