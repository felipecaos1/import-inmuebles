jQuery(document).ready(function($) {
    // Tu código JavaScript aquí

    // Vista movil
    var swiper = $('.swiper .swiper-slide img');
    swiper.each(function(index, element){
        $(element).attr('src', 'https://alternainmobiliaria.com/wp-content/uploads/2024/05/preview-1536x864.jpg');
    });


    setTimeout(() => {

        const galleryContainer  = $('.elementor-element-37b6014');
        const swiperWrapperMain = document.createElement('div');
        swiperWrapperMain.className = 'swiper-wrapper';


        const images = [
            'https://alternainmobiliaria.com/wp-content/plugins/import-inmuebles/data/temp/0003CAE3.L03',
            'https://alternainmobiliaria.com/wp-content/uploads/2024/05/preview.jpg',
            'https://alternainmobiliaria.com/wp-content/plugins/import-inmuebles/data/temp/0003CAE3.L03',
            'https://alternainmobiliaria.com/wp-content/uploads/2024/05/preview.jpg',
            'https://alternainmobiliaria.com/wp-content/plugins/import-inmuebles/data/temp/0003CAE3.L03',
            'https://alternainmobiliaria.com/wp-content/uploads/2024/05/preview.jpg',
        ];
        
        images.forEach(src => {
            const swiperSlide = document.createElement('div');
            swiperSlide.className = 'swiper-slide';
            const img = document.createElement('img');
            img.src = src;
            swiperSlide.appendChild(img);
            swiperWrapperMain.appendChild(swiperSlide);
        });
        const swiperContainerMain = document.createElement('div');
        swiperContainerMain.className = 'swiper-container main-slider galery-principal-swiper';
        swiperContainerMain.style.position = 'relative';
        swiperContainerMain.style.overflow = 'hidden';
        swiperContainerMain.appendChild(swiperWrapperMain);

        const swiperPaginationMain = document.createElement('div');
        swiperPaginationMain.className = 'swiper-pagination';

        const swiperButtonPrevMain = document.createElement('div');
        swiperButtonPrevMain.className = 'swiper-button-prev';

        const swiperButtonNextMain = document.createElement('div');
        swiperButtonNextMain.className = 'swiper-button-next';

        swiperContainerMain.appendChild(swiperPaginationMain);
        

        // Crear la estructura HTML para Swiper de miniaturas
        const swiperWrapperThumbs = document.createElement('div');
        swiperWrapperThumbs.className = 'swiper-wrapper';
        

        images.forEach(src => {
            const swiperSlide = document.createElement('div');
            swiperSlide.className = 'swiper-slide';
            const img = document.createElement('img');
            img.src = src;
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

        
    }, 2000);
    
    if (typeof MyPluginData !== 'undefined') {
        var unique_id = MyPluginData.unique_id;
        var base_url = MyPluginData.base_url;
        var number_img = MyPluginData.number_img;

        if(unique_id){

            
           
        }

       
    } else {
        console.log('MyPluginData no está definido....');
    }
});