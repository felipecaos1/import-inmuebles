jQuery(document).ready(function($) {
    // Tu código JavaScript aquí
    // var galleryDivThumb = $('.ug-gallery-wrapper .ug-theme-panel .ug-strip-panel .ug-thumbs-strip .ug-thumbs-strip-inner .ug-thumb-wrapper img');
    var galleryDivThumb = $('.ug-gallery-wrapper ');
    console.log(galleryDivThumb);
    galleryDivThumb.each(function(){
        console.log('hola');
    });
    if (typeof MyPluginData !== 'undefined') {
        var unique_id = MyPluginData.unique_id;
        var base_url = MyPluginData.base_url;
        var number_img = MyPluginData.number_img;

        if(unique_id){

            
            // galleryDiv.each(function(index, element) {
            //     var ext = ( number_img[index] < 10 ) ? '.L0'+(number_img[index]) : '.L'+(number_img[index]);
            //     var path = base_url +unique_id +ext;
            //     var img = $(element).find('img');
                
            //     if(index!=0){
            //         $(element).attr('href', path);
            //         img.attr('src', path);
            //         // console.log( path );
            //     }else{
            //         var url = $(element).attr('href');
            //         var path_2 = 'https://alternainmobiliaria.com/wp-content/uploads/2024/06/'+unique_id+'-L02.jpeg';
            //         if (url.endsWith('preview.jpg')) {
            //             $(element).attr('href', path_2);
            //             img.attr('src', path_2);
            //         } 
            //     }
            //     // $(element).css('background-image', 'url(' + path + ')');
            // });
        }

       
    } else {
        console.log('MyPluginData no está definido....');
    }
});