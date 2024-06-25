jQuery(document).ready(function($) {
    // Tu código JavaScript aquí

    
    if (typeof MyPluginData !== 'undefined') {
        var unique_id = MyPluginData.unique_id;
        var base_url = MyPluginData.base_url;
        // var base_url = 'https://alternainmobiliaria.com/wp-content/plugins/import-inmuebles/data/temp/';
        var number_img = MyPluginData.number_img;
        // console.log(number_img);
        
        if(unique_id){

            loadScript('https://friendsinmobiliaria.test/wp-content/plugins/elementor/assets/lib/swiper/v8/swiper.min.js?ver=8.4.5')
                .then(() => {
                    const galleryContainer  = $('.elementor-element-37b6014');
                    const swiperWrapperMain = document.createElement('div');
                    swiperWrapperMain.className = 'swiper-wrapper';

                    number_img.forEach(index => {
                        var ext = parseInt(index) < 10 ? '.L0'+index : '.L'+index ;

                        const swiperSlide = document.createElement('div');
                        swiperSlide.className = 'swiper-slide';
                        const img = document.createElement('img');
                        img.src = base_url + unique_id +ext;
                        swiperSlide.appendChild(img);
                        swiperWrapperMain.appendChild(swiperSlide);
                    });
                    
                    const swiperContainerMain = document.createElement('div');
                    swiperContainerMain.className = 'swiper-container main-slider galery-principal-swiper';
                    swiperContainerMain.style.position = 'relative';
                    swiperContainerMain.style.overflow = 'hidden';
                    swiperContainerMain.appendChild(swiperWrapperMain);
            
                    // const swiperPaginationMain = document.createElement('div');
                    // swiperPaginationMain.className = 'swiper-pagination';
            
                    const swiperButtonPrevMain = document.createElement('div');
                    swiperButtonPrevMain.className = 'swiper-button-prev';
            
                    const swiperButtonNextMain = document.createElement('div');
                    swiperButtonNextMain.className = 'swiper-button-next';
            
                    // swiperContainerMain.appendChild(swiperPaginationMain);
                    
            
                    // Crear la estructura HTML para Swiper de miniaturas
                    const swiperWrapperThumbs = document.createElement('div');
                    swiperWrapperThumbs.className = 'swiper-wrapper';
                    
            
                    number_img.forEach(index => {
                        var ext2 = parseInt(index) < 10 ? '.L0'+index : '.L'+index ;

                        const swiperSlide = document.createElement('div');
                        swiperSlide.className = 'swiper-slide';
                        const img = document.createElement('img');
                        img.src = base_url + unique_id +ext2;
                        swiperSlide.appendChild(img);
                        swiperWrapperThumbs.appendChild(swiperSlide);
                    });
                    
            
                    const swiperContainerThumbs = document.createElement('div');
                    swiperContainerThumbs.className = 'swiper-container swiper-container-thumbs';
                    swiperContainerThumbs.style.position = 'relative';
                    swiperContainerThumbs.style.overflow = 'hidden';
                    swiperContainerThumbs.appendChild(swiperWrapperThumbs);
                    swiperContainerThumbs.appendChild(swiperButtonPrevMain);
                    swiperContainerThumbs.appendChild(swiperButtonNextMain);
            
                    // Insertar la galería en el contenedor
                    $(galleryContainer).append(swiperContainerMain);
                    $(galleryContainer).append(swiperContainerThumbs);
                    // galleryContainer.style.display = 'block';
            
                    // Inicializar Swiper
                    const galleryThumbs = new Swiper('.swiper-container-thumbs', {
                        slidesPerView: 4,
                        freeMode: true,
                        watchSlidesVisibility: true,
                        watchSlidesProgress: true,
                        navigation: {
                            nextEl: '.swiper-button-next',
                            prevEl: '.swiper-button-prev',
                        },
                    });
            
                    const galleryMain = new Swiper('.main-slider', {
                        loop: true,
                        // pagination: {
                        //     el: '.swiper-pagination',
                        //     clickable: true,
                        // },
                        
                        thumbs: {
                            swiper: galleryThumbs,
                        },
                    });


                    // ------------------------------Movil---------------------------------------------------------
                    const galleryContainerMovil = $('.elementor-element-37b6014');

                    const movilSwiperWrapperMain = document.createElement('div');
                    movilSwiperWrapperMain.className = 'swiper-wrapper';

                    number_img.forEach(index => {
                        var ext3 = parseInt(index) < 10 ? '.L0'+index : '.L'+index ;

                        const swiperSlide = document.createElement('div');
                        swiperSlide.className = 'swiper-slide';
                        const img = document.createElement('img');
                        img.src = base_url + unique_id +ext3;
                        swiperSlide.appendChild(img);
                        movilSwiperWrapperMain.appendChild(swiperSlide);
                    });

                    const movilSiperContainerMain = document.createElement('div');
                    movilSiperContainerMain.className = 'swiper-container main-slider-movil galery-movil-swiper';
                    movilSiperContainerMain.style.position = 'relative';
                    movilSiperContainerMain.style.overflow = 'hidden';
                    movilSiperContainerMain.appendChild(movilSwiperWrapperMain);


                    const movilSwiperButtonPrevMain = document.createElement('div');
                    movilSwiperButtonPrevMain.className = 'swiper-button-prev';
            
                    const movilSwiperButtonNextMain = document.createElement('div');
                    movilSwiperButtonNextMain.className = 'swiper-button-next';


                    movilSiperContainerMain.appendChild(movilSwiperButtonPrevMain);
                    movilSiperContainerMain.appendChild(movilSwiperButtonNextMain);


                    $(galleryContainerMovil).after(movilSiperContainerMain);



                    const galleryMainMovil = new Swiper('.main-slider-movil', {
                        loop: true,
                        navigation: {
                            nextEl: '.swiper-button-next',
                            prevEl: '.swiper-button-prev',
                        },
                        
                    });



                })
                .catch((error) => {
                    console.error(error);
                    // Manejar el error de carga del script aquí
                });
            

            // setTimeout(() => {

                
        
                
            // }, 2000);
            
        }

       
    } else {
        console.log('MyPluginData no está definido....');
    }

});

function loadScript(url) {
    return new Promise((resolve, reject) => {
        const script = document.createElement('script');
        script.src = url;
        script.onload = () => resolve(script);
        script.onerror = () => reject(new Error(`Script load error for ${url}`));
        document.body.appendChild(script);
    });
}