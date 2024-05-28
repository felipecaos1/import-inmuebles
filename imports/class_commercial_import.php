<?php

require_once IMPORTMLS_DIR . 'imports/class_import.php';

class CommercialImport extends Import
{
    private $data_result;
    private $inmueble;
    

    public function __construct($data_result = []) {
        $this->data_result = $data_result;
    }

    /**
     * Crea una nueva entrada de propiedad en WordPress utilizando los datos proporcionados.
     *
     * @param array $data Datos de la propiedad obtenidos del archivo CSV.
     */
    public function crear_inmueble($data)
    {
        $key = array_search($data['unique_id'], array_column($this->data_result, 'unique_id'));
        if ($key !== false) {
            $this->inmueble = $this->data_result[$key];
        } else {
            $this->inmueble = $this->insert_data_into_table($data['unique_id'],'commercial');
        }
        
        $ruta_feature_img = IMPORTMLS_DIR . DIR_NAME_TEMP.'/'.$data['unique_id'].'.L01';
        // funcion para crear un array con los id de las imagenes
        $gallery_ids = $this->get_post_galery_ids($data['unique_id'],$data['listing_photo_count'],$this->inmueble->post_galery_insert);

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

        if ($this->inmueble->post_created) {
            // Actualiza el post existente
            $post_id = $this->inmueble->post_created;

            if(!$this->inmueble->feature_img){
                $feature_img = $this->set_feature_img($post_id, $ruta_feature_img);
                if($feature_img){
                    $this->update_by_unique_id($data['unique_id'],['feature_img' => true]);
                }
            }

            if($gallery_ids != '' ){
                $new_gallery='';
                $old_gallery = get_post_meta($post_id, 'galeria-de-imagenes', true);
                if($old_gallery != '' ){
                    $new_gallery .= $old_gallery.',';
                }
                $new_gallery .= $gallery_ids;

                $meta_datos['galeria-de-imagenes'] =  $new_gallery;
            }

            $post_data = array(
                'ID'            => $post_id,
                'post_title'    => $data['commercial_type'].' en '.$data['map_area'].' - '.$data['district'].' - '.$data['id'],
                // 'post_status'   => 'publish', 
                'post_type'     => 'propiedades',
                'meta_input'    => $meta_datos 
            );

            $updated = wp_update_post($post_data);

            if (!is_wp_error($updated)) {
                // Log::info('Inmueble Actualizado');
            } else {
                // Log::info('Error al actualizar el inmueble');
            }

        }else{
            
            $meta_datos['galeria-de-imagenes'] =  $gallery_ids;

            $post_data = array(
                'post_title'    =>$data['commercial_type'].' en '.$data['map_area'].' - '.$data['district'].' - '. $data['id'],
                // 'post_status'   => 'publish', 
                'post_status'   => 'pending', 
                'post_type'     => 'propiedades',
                'meta_input'    => $meta_datos 
            );        
            // Crea un nuevo post
            $post_id = wp_insert_post($post_data);

            if (!is_wp_error($post_id)) {
                // Imagen destacada
                $result_feature_img = $this->set_feature_img($post_id, $ruta_feature_img);
                
                // Validar si se establecio la imagen destacada
                if (!$result_feature_img) {
                    if(get_option('id_preview')){
                        $imagen_id = get_option('id_preview');
                        $result_thumb = set_post_thumbnail($post_id, $imagen_id);
                    }
                }
                $this->update_by_unique_id($data['unique_id'],['post_created' => $post_id,'feature_img' => $result_feature_img]);
            }else{
                // Log::info('Error al crear el inmueble');
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