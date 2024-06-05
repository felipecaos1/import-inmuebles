jQuery(document).ready(function($) {
    // Tu código JavaScript aquí
    if (typeof MyPluginData !== 'undefined') {
        var unique_id = MyPluginData.unique_id;
        var base_url = MyPluginData.base_url;

        if(unique_id){

            
            console.log(MyPluginData.number_img);
            var galleryDiv = $('.gallery_wrapper .image_gallery');
            galleryDiv.each(function(index, element) {
                var ext = (index+1 < 10 ) ? '.L0'+(index+1) : '.L'+(index+1);
                var path = base_url +unique_id +ext;
                console.log(path);
                $(element).css('background-image', 'url(' + path + ')');
            });
            
            var elements = $(' #owl-demo .owl-stage .owl-item .item img');
            
            // Recorrer los elementos y imprimir cada uno
            elements.each(function(index, element) {
                var ext = (index+1 < 10 ) ? '.L0'+(index+1) : '.L'+(index+1);
                var path = base_url +unique_id +ext;
                console.log(path);
                var href = $(element).attr('src', path);
            });
        }
            
        // Aquí puedes usar postId para cualquier operación que necesites
    } else {
        console.log('MyPluginData no está definido.');
    }
});