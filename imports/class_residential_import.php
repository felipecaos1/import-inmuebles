<?php

require_once IMPORTMLS_DIR . 'imports/class_import.php';

class ResidentialImport extends Import
{
    /**
     * Crea una nueva entrada de propiedad en WordPress utilizando los datos proporcionados.
     *
     * @param array $data Datos de la propiedad obtenidos del archivo CSV.
     */
    public function crear_inmueble($data)
    {
        Log::info('Creando el inmueble residencial: '. $data['id']);

        // funcion para crear un array con los id de las imagenes
        $gallery_ids = $this->get_post_galery_ids($data['unique_id'],$data['listing_photo_count']);

        // Metacampos
        $meta_datos = array(
            'valor' => $data['price_current'],
            '_direccion' => $data['street_name_es'].', '.$data['map_area'].', '.$data['district'],
            'area-de-la-propiedad' => $data['lot_sqft'],
            'area-construida' => $data['sqft_total'],
            'descripcion' => $data['remarks_es'],
            'estrato' => '',
            'id'=> $data['id'],
            'tiempo-construccion' => $this->calculate_built_time($data['year_built']),//Calcular sobre fecha
            'floor' => '',
            'estacionamiento' => ($data['parking_spaces'] != 0)? $data['parking_spaces'].' estacionamientos':'Sin estacionamientos',
            'habitaciones' => $data['bedrooms'],
            'banos' => $data['bathrooms'],
            'bodega' =>'',
            'comodidades' => $this->get_amenities($data['interior_features'].','.$data['exterior_features']),
            'sector' => array(
                $data['map_area'] => 'true'
            ),
            'galeria-de-imagenes' => $gallery_ids,
            'urbanizacion' =>($data['subdivision'] !='No aplica')? $data['subdivision']:'',
            'is_mls' => true
        );

        $post_data = array(
            'post_title'    => $data['id'].' - '.$data['property_type'].' en '.$data['map_area'].' - '.$data['district'],
            'post_status'   => 'publish', 
            'post_type'     => 'propiedades',
            'meta_input'    => $meta_datos 
        );

        // Insertar el post usando wp_insert_post()
        $post_id = wp_insert_post($post_data);
        // Verificar si la inserción fue exitosa
        if (!is_wp_error($post_id)) {
            Log::info('Se creo el post: '. $post_id);

            $this->set_taxonomia($post_id, $data['district'], 'ciudad');
            $this->set_taxonomia($post_id, $this->get_property_type($data['property_type']), 'tipo-de-propiedad');

            $ruta_feature_img = IMPORTMLS_DIR . DIR_NAME_TEMP.'/'.$data['unique_id'].'.L01';
            
            if ( file_exists( $ruta_feature_img ) ){
                $imagen_id = $this->load_image_and_get_id($ruta_feature_img);
                if ($imagen_id) {
                    set_post_thumbnail($post_id, $imagen_id);
                    Log::info('Se cargo la imagen destacada del post');
                } else {
                    Log::error('Hubo un error al cargar la imagen: '. $ruta_feature_img);
                }
            }else{
                Log::info('La imagen: '.$ruta_feature_img.' no existe');
            }            
        } else {
            Log::error('Error al intentar crear el post.');
        }        
    }

}