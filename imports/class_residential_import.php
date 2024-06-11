<?php

require_once IMPORTMLS_DIR . 'imports/class_import.php';

class ResidentialImport extends Import
{
    private $inmueble;
    
    public function __construct() {
        
    }

    /**
     * Crea una nueva entrada de propiedad en WordPress utilizando los datos proporcionados.
     *
     * @param array $data Datos de la propiedad obtenidos del archivo CSV.
     */
    public function crear_inmueble($data)
    {
        $this->inmueble = $this->get_by_unique_id($data['unique_id']);
        if(!$this->inmueble) {
            $this->inmueble = $this->insert_data_into_table($data['unique_id'],'residential');
        }
        
        // $existing_post_id = $this->buscar_inmueble_por_id($data['id']);        
        $ruta_feature_img = IMPORTMLS_DIR . DIR_NAME_TEMP.'/'.$data['unique_id'].'.L01';

        // $urba = ($data['subdivision'] !='No aplica')? $data['subdivision']:'';//urbanización
        // Metacampos
        $meta_datos = array(
            'property_price' => $data['price_current']/1000000, //precio en millones
            'property_size' => $data['lot_sqft'],//área total
            'property_lot_size' => $data['sqft_total'], //área construída
            'property_rooms' => $data['bedrooms'],//habitaciones
            'property_bedrooms' => $data['bedrooms'],//dormitorios
            'property_bathrooms' => $data['bathrooms'],//Baños
            'codigo-de-la-propiedad' => $data['id'],//Código de la propiedad
            'parqueadero' => $data['parking_spaces'],//parqueaderos
            'ubicacion' => $data['street_name_es'].', '.$data['map_area'].', '.$data['district'],//Ubicación texto
            'hidden_address' => $data['street_name_es'].', '.$data['map_area'].', '.$data['district'],//Ubicación completa
            'property_agent' =>'24509',//id agente encargado
            '_direccion' => $data['street_name_es'].', '.$data['map_area'].', '.$data['district'],
            '_yoast_wpseo_metadesc'=>'🏙️ Area m2: '.$data['sqft_total'].' m2 - ▶️ Valor: $'.$data['price_current'].' - 🛏️ Habitaciones: '.$data['bedrooms'].' - 🚘 Parq: '.$data['parking_spaces'],
            'm2-construidos' =>$this->rangoMcuadrados($data['sqft_total']),
            'tamano-de-lote' =>$this->rangoMLote($data['lot_sqft']),
            'prop_featured' => '0',
            'property_latitude' =>$data['latitude'],//laitud de la propiedad 
            'property_longitude' =>$data['longitude'],//longitud de la propiedad
            'property_country' =>'Colombia',
            'administracion'=>'$'.number_format($data['monthly_assessment'], 0, '.'),
            // 'page_show_adv_search'=>'global',
            'page_use_float_search'=>'global',
            

            // 'topbar_transparent'=>'global',
            // 'topbar_border_transparent'=>'global',
            // 'sidebar_agent_option'=>'global',
            // 'local_pgpr_slider_type'=>'global',
            // 'local_pgpr_content_type'=>'global',
            // 'header_transparent'=>'global',
            // 'page_header_image_full_screen'=> 'yes',
            // 'page_header_image_back_type'=> 'cover',
            // 'post_show_title'=>'yes',
            // 'sidebar_option' =>'right',
            // 'sidebar_select' => 'primary-widget-area',
            // -------------------------
            
            'is_mls' => true
        );

        // funcion para crear un array con los id de las imagenes
        $gallery_ids = $this->get_post_galery_ids($data['unique_id'],$data['listing_photo_count'],$this->inmueble->post_galery_insert);
        
        if ($this->inmueble->post_created) {
            // Actualiza el post existente

            $post_id = $this->inmueble->post_created;

            if(!$this->inmueble->feature_img){
                $feature_img = $this->set_feature_img($post_id, $ruta_feature_img);
                if($feature_img){
                    $this->update_by_unique_id($data['unique_id'],['feature_img' => true]);
                }
            }

            // if($gallery_ids != '' ){
            //     $new_gallery='';
            //     $old_gallery = get_post_meta($post_id, 'galeria-de-imagenes', true);
            //     if($old_gallery != '' ){
            //         $new_gallery .= $old_gallery.',';
            //     }
            //     $new_gallery .= $gallery_ids;

            //     $meta_datos['image_to_attach'] =  $new_gallery;
            // }
            
            $post_data = array(
                'ID'            => $post_id,
                'post_title'    => $data['property_type'].' en '.$data['map_area'].' - '.$data['district'].' - '.$data['id'],
                // 'post_status'   => 'publish', 
                'post_type'     => 'estate_property',
                'post_content'  => $data['remarks_es'],
                'meta_input'    => $meta_datos 
            );

            $updated = wp_update_post($post_data);

            if (!is_wp_error($updated)) {
                // Log::info('Inmueble Actualizado');
            } else {
                $error_message = $updated->get_error_message();
                Log::info('Error al actualizar el inmueble: ' . $error_message);
            }

        } else {
            
            // $meta_datos['image_to_attach'] =  $gallery_ids;
         
            $post_data = array(
                'post_title'    => $data['property_type'].' en '.$data['map_area'].' - '.$data['district'].' - '.$data['id'],
                // 'post_status'   => 'publish', 
                'post_status'   => 'pending',
                'post_type'     => 'estate_property',
                'post_content'  => $data['remarks_es'],
                'meta_input'    => $meta_datos 
            );
            // Crea un nuevo post
            $post_id = wp_insert_post($post_data);

            if (!is_wp_error($post_id)) {                
                // Imagen destacada
                $result_feature_img = $this->set_feature_img($post_id, $ruta_feature_img);

                // Validar si se establecio la imagen destacada
                // if (!$result_feature_img) {
                //     if(get_option('id_preview')){
                //         $imagen_id = get_option('id_preview');
                //         $result_thumb = set_post_thumbnail($post_id, $imagen_id);
                //     }
                // }                
                $this->update_by_unique_id($data['unique_id'],['post_created' => $post_id,'feature_img' => $result_feature_img]);
            } else {
                $error_message = $post_id->get_error_message();
                Log::info('Error al crear el inmueble: ' . $error_message);
            }
        }

        // Verificar si hay un post 
        if ($post_id) {
            
            // Taxonomias
            // property_category: single: casa-apto,etc
            $this->set_taxonomia($post_id, [$data['property_type']], 'property_category');
            // property_action_category: single: compra-venta-nodisponible, se asigna por defecto Venta(id=51)
            wp_set_object_terms($post_id, 51 , 'property_action_category', false);
            // property_city: ciudades agrupadas
            $this->set_taxonomia($post_id, [$data['district']], 'property_city');
            // property_area: Barrio
            $this->set_taxonomia($post_id, [$data['map_area']], 'property_area');
            // property_county_state: "Medellín – Colombia"
            $this->set_taxonomia($post_id, ['Colombia'], 'property_county_state');//optimizar
            // property_features: amenities
            // var_dump($this->get_amenities($data['interior_features'].','.$data['exterior_features']));
            $this->set_taxonomia($post_id, $this->get_amenities($data['interior_features'].','.$data['exterior_features']), 'property_features');
            
            // property_status: vacio
            $this->set_taxonomia($post_id, [$data['remodelled']], 'property_status');

        } else {
            Log::error('Error, no hay un id para establecer las taxonomias');
        }        
    }

}