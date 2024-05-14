<?php

require_once IMPORTMLS_DIR . 'imports/class_import.php';

class CommercialImport extends Import
{
    /**
     * Crea una nueva entrada de propiedad en WordPress utilizando los datos proporcionados.
     *
     * @param array $data Datos de la propiedad obtenidos del archivo CSV.
     */
    public function crear_inmueble($data)
    {
        Log::info('Validando el inmueble comercial: '. $data['id']);

        $existing_post_id = $this->buscar_inmueble_por_id($data['id']);

        // - street_name_es no llega
        $data['street_name_es'] = '';
        // - bedrooms no llega
        $data['bedrooms'] = 0;
        // - bathrooms no llega
        $data['bathrooms'] = 0;
        
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
            // 'galeria-de-imagenes' => $gallery_ids,
            'urbanizacion' =>($data['subdivision'] !='No aplica')? $data['subdivision']:'',
            'is_mls' => true
        );

        if ($existing_post_id) {
            // Actualiza el post existente
            $post_id = $existing_post_id;

            $post_data = array(
                'ID'            => $post_id,
                'post_title'    => $data['id'].' - '.$data['commercial_type'].' en '.$data['map_area'].' - '.$data['district'],
                // 'post_status'   => 'publish', 
                'post_type'     => 'propiedades',
                'meta_input'    => $meta_datos 
            );

            $updated = wp_update_post($post_data);

            if (!is_wp_error($updated)) {
                Log::info('Inmueble Actualizado');
            } else {
                Log::info('Error al actualizar el inmueble');
            }

        }else{
            // funcion para crear un array con los id de las imagenes
            $gallery_ids = $this->get_post_galery_ids($data['unique_id'],$data['listing_photo_count']);

            $meta_datos['galeria-de-imagenes'] =  $gallery_ids;

            $post_data = array(
                'post_title'    => $data['id'].' - '.$data['commercial_type'].' en '.$data['map_area'].' - '.$data['district'],
                'post_status'   => 'publish', 
                'post_type'     => 'propiedades',
                'meta_input'    => $meta_datos 
            );
    
        
            // Insertar el post usando wp_insert_post()
            $post_id = wp_insert_post($post_data);

            if (!is_wp_error($post_id)) {
                Log::info('Inmueble Creado');

                $ruta_feature_img = IMPORTMLS_DIR . DIR_NAME_TEMP.'/'.$data['unique_id'].'.L01';

                $result_feature_img = $this->set_feature_img($post_id, $ruta_feature_img);

                if (!$result_feature_img) {
                    $post_data = array(
                        'ID' => $post_id, // ID del post que quieres actualizar
                        'post_status' => 'pending', // Estado deseado: borrador
                    );
                    // Actualizar el post usando wp_update_post()
                    $updated = wp_update_post($post_data);
                    Log::info('El post cambio a estado pendiente');
                }
            }else{
                Log::info('Error al crear el inmueble');
            }
        }

        // Verificar si hay un post 
        if ($post_id) {

            $this->set_taxonomia($post_id, $data['district'], 'ciudad');
            $this->set_taxonomia($post_id, $this->get_property_type($data['commercial_type']), 'tipo-de-propiedad');
          
        } else {
            Log::error('Error, no hay un id para establecer las taxonomias');
        }        
    }

}