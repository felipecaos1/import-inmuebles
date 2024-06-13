jQuery(document).ready(function($) {
    // Tu código JavaScript aquí
    if (typeof MyPluginData !== 'undefined') {
        var unique_id = MyPluginData.unique_id;
        var base_url = MyPluginData.base_url;
        var number_img = MyPluginData.number_img;
        // console.log(number_img);
        if(unique_id){

            // Grid Gallery web
            var galleryDiv = $('.gallery_wrapper');
            for (let index = 0; index < 5; index++) {

                if(number_img[index]){

                    var ext = (number_img[index] < 10 ) ? '.L0'+(number_img[index]) : '.L'+(number_img[index]);
                    var newDivHTML = `
                    <div class="col-md-4 image_gallery lightbox_trigger special_border_top" data-slider-no="${index+2}" style="background-image:url('${base_url+unique_id+ext}')">
                    <div class="img_listings_overlay"></div>
                    </div>
                    `;
                
                    if(index == 4){
                        var newDivHTML = `
                        <div class="col-md-4 image_gallery lightbox_trigger special_border_top" data-slider-no="${index+2}" style="background-image:url('${base_url+unique_id+ext}')">
                        <div class="img_listings_overlay img_listings_overlay_last"></div>
                        <span class="img_listings_mes">Ver todo ${number_img.length} fotos</span>
                        </div>
                        `;
                    }
                
                    $(galleryDiv).append(newDivHTML);
    
                }else{
                    var newDivHTML = `
                    <div class="col-md-4 image_gallery lightbox_trigger special_border_top" data-slider-no="${index+2}" style="background-image:url('http://propiedadesnakama.test/wp-content/uploads/2023/06/LOGO-NAKAMA-HORIZONTAL-GRIS.png'); background-size:contain; background-repeat:no-repeat">
                    <div class="img_listings_overlay"></div>
                    </div>
                    `;
                    $(galleryDiv).append(newDivHTML);

                }
            }

            // Carrusel móvil
            
            // console.log(carruWrape);
            var checkSlickInitialized = setInterval(function() {
                if ($('div.slick-slider.property_multi_image_slider').hasClass('slick-initialized')) {
                    clearInterval(checkSlickInitialized);
                    var carruWrape = $('.property_multi_image_slider .slick-track');
                    // console.log(carruWrape[0]);
                    for (let index = 0; index < number_img.length; index++){
                        var ext = (number_img[index] < 10 ) ? '.L0'+(number_img[index]) : '.L'+(number_img[index]);
                        var newDivSlick = `
                        <div class="item slick-slide" data-slick-index="${index+1}" aria-hidden="true" tabindex="-1" role="tabpanel" id="slick-slide0${index+1}" aria-describedby="slick-slide-control0${index+1}" style="width: 0px;">
                            <div class="multi_image_slider_image  lightbox_trigger custome-lightbox_trigger" data-slider-no="${index+2}" style="background-image:url(${base_url+unique_id+ext})">
                            </div>
                            <div class="carousel-caption"></div>
                        </div>`;
        
                        // $(carruWrape).eq(0).append(newDivSlick);
        
                        $('div.slick-slider.property_multi_image_slider').slick('slickAdd', newDivSlick);
                        
                    }
                }
            }, 100); // Verificar cada 100ms
            

            // LightBox General
            setTimeout(() => {
                var $carousel = $('#owl-demo');
                // var lightbox = $('#owl-demo .owl-stage ');
                // console.log(lightbox);
                for (let index = 0; index < number_img.length; index++) {
                    var ext = (number_img[index] < 10 ) ? '.L0'+(number_img[index]) : '.L'+(number_img[index]);
                    
                    var newDivLightbox = `
                    <div class="item" href="#${index+2}">
                    <img src="${base_url+unique_id+ext}" alt="image">
                    </div>
                    `;
                    $carousel.trigger('add.owl.carousel', [$(newDivLightbox)]).trigger('refresh.owl.carousel');

    
                }

                $('.custome-lightbox_trigger').click(function(){
                    // console.log($(this).data('slider-no'));
                    var slide = $(this).data('slider-no');
                    $carousel.trigger('to.owl.carousel', [slide-1, 0]);
                    $('.lightbox_property_wrapper').show();
                });
                
            }, 2000);

        }
            
        // Aquí puedes usar postId para cualquier operación que necesites
    } else {
        console.log('MyPluginData no está definido.');
    }
});


