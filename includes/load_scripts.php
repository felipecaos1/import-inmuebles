<?php

function wptuts_scripts_basic() {
    // Use plugins_url() to get the correct URL for enqueuing scripts and styles
    wp_register_script('custom-script', plugins_url('js/custom-script.js', IMPORTMLS_FILE), array('jquery'), '1.0.0', true);
    wp_enqueue_script('custom-script');
    wp_register_style('custom-style', plugins_url('css/custom-style.css', IMPORTMLS_FILE), array(), '20120208', 'all');
    wp_enqueue_style('custom-style');
    wp_register_style('bootstrap', plugins_url('css/bootstrap.min.css', IMPORTMLS_FILE), array(), '5.3.3', 'all');
    wp_enqueue_style('bootstrap');
}

add_action('admin_enqueue_scripts', 'wptuts_scripts_basic');

// Función para cargar el script solo en los posts de tipo estate_property
function enqueue_custom_script_for_properties() {
    if (is_singular('propiedades')) {
        $post_id = get_the_ID();//obtenemos el id del post

        if($post_id){
            $inmueble = get_by_unique_id($post_id);
            if(true){

                wp_enqueue_script(
                    'set_img',
                    plugins_url('js/set_img.js', IMPORTMLS_FILE),
                    array('jquery'),
                    null,
                    true
                );

                wp_enqueue_style(
                    'gallery_css',
                    plugins_url('css/gallery_css.css', IMPORTMLS_FILE),
                    array(),
                    '20120208',
                    'all'
                );

                $post_galery_insert = explode(',', $inmueble->post_galery_insert);
                wp_localize_script('set_img', 'MyPluginData', array(
                    'unique_id' => $inmueble->unique_id,//'0003C877'
                    'base_url' =>  plugins_url('/', IMPORTMLS_FILE).DIR_NAME_TEMP.'/',
                    'number_img'=>$post_galery_insert
                ));
            }
            
        }
    }
}
add_action('wp_enqueue_scripts', 'enqueue_custom_script_for_properties');

function get_by_unique_id($post_id) 
{
    global $wpdb;

    $tabla_nombre = $wpdb->prefix . TABLE_NAME;

    $resultado = $wpdb->get_row(
        $wpdb->prepare(
            "SELECT * FROM $tabla_nombre WHERE post_created = %d", $post_id
        )
    );

    return $resultado;
}