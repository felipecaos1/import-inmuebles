<?php

function crear_inmueble($data){
    // funcion para crear un array con los id de las imagenes
    $gallery_ids = get_post_galery_ids($data[31],$data[32]);

    // Metacampos
    $meta_datos = array(
        'valor' => $data[2],
        '_direccion' => $data[63].', '.$data[6].', '.$data[5],
        'area-de-la-propiedad' => $data[14],
        'area-construida' => $data[15],
        'descripcion' => $data[25],
        'estrato' => '',
        'id'=> $data[8],
        'tiempo-construccion' => $data[9],//Calcular sobre fecha
        'floor' => '',
        'estacionamiento' => ($data[54] != 0)? $data[54].' estacionamientos':'Sin estacionamientos',
        'habitaciones' => $data[10],
        'banos' => $data[11],
        'bodega' =>'',
        'comodidades' => array(''),
        'sector' => array(
            $data[6] => 'true'
        ),
        'galeria-de-imagenes' => $gallery_ids,
        'urbanizacion' =>($data[3] !='No aplica')? $data[3]:'',
        'is_mls' => true
    );
    
    $post_data = array(
        'post_title'    => $data[8].' - '.$data[7].' en '.$data[6].' - '.$data[5],
        'post_status'   => 'publish', 
        'post_type'     => 'propiedades',
        'meta_input'    => $meta_datos 
    );

    // Insertar el post usando wp_insert_post()
    $post_id = wp_insert_post($post_data);
    // Verificar si la inserción fue exitosa
    if (!is_wp_error($post_id)) {

        $result = set_ciudad($post_id, $data[5]);

        //id_multimedia $data[31]
        //miltimedia count $data[32]
        // $data[31] = '0003A9CA';
        $ruta_feature_img = IMPORTMLS_DIR . DIR_NAME_TEMP.'/'.$data[31].'.L01';
       
        
        if ( file_exists( $ruta_feature_img ) ){
            $imagen_id = cargar_imagen_y_obtener_id($ruta_feature_img);
            if ($imagen_id) {
                set_post_thumbnail($post_id, $imagen_id);
            } else {
                echo 'Hubo un error al cargar la imagen.';
            }
        }else{
            echo 'errorImagendestacada ';
        }
         
    } else {
  
        echo 'Error al crear el post: ' . $post_id->get_error_message();
    }
    
}

function set_ciudad($post_id, $ciudad) {
    if (empty($post_id) || !is_int($post_id) || empty($ciudad)) {
        return new WP_Error('invalid_input', 'Input values are invalid.');
    }
    $term = term_exists($ciudad, 'ciudad');
    
    // Si el término no existe, créalo
    if (!$term) {
        $term = wp_insert_term($ciudad, 'ciudad');
        // Verificar si hubo un error al crear el término
        if (is_wp_error($term)) {
            return $term;
        }
    }

    // Obtener el ID del término
    $term_id = is_array($term) ? $term['term_id'] : $term;

    $term_id = (int) $term_id;
    
    // Asigna el término al post utilizando el ID
    $result = wp_set_object_terms($post_id, $term_id , 'ciudad', false);
    if (is_wp_error($result)) {
        return $result; // Retornar el error para manejo externo
    }
 
    return true;
}

// Recibe el id unico de multimedia
// Recibe el numero de imagenes
// Devuelve un string con los ids de las imagenes cargadas 
function get_post_galery_ids($id_unique ='', $multi_count = 1 ){

    $list_ids = [];

    for ($i=2; $i <= $multi_count ; $i++) {
        $ext = ($i < 10 ) ? '.L0'.$i : '.L'.$i;
        $ruta_img = IMPORTMLS_DIR . DIR_NAME_TEMP .'/'.$id_unique.$ext;
        echo $ruta_img;
        if ( file_exists( $ruta_img ) ){
            $imagen_id = cargar_imagen_y_obtener_id($ruta_img);
            if ($imagen_id) {
                $list_ids[]= $imagen_id;
            } else {
                echo 'Hubo un error al cargar la imagen.';
            }
        }else{
            echo 'error, la imagen no existe';
        }
    }
    $str_ids = implode(',', $list_ids );

    return $str_ids;
}

function cargar_imagen_y_obtener_id($imagen_url) {
    $file_array = array(
    'name' => wp_basename($imagen_url),
    'tmp_name' => $imagen_url,
    );
    
    try {
        $imagen_id = media_handle_sideload($file_array);
    } catch (\Throwable $th) {
        echo 'error side load';
    }

    if (is_wp_error($imagen_id)) {
        return false;
    }else{
        echo is_wp_error($imagen_id);
    }
    return $imagen_id;
}