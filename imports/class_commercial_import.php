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
        
        $this->inmueble = $this->get_by_unique_id($data['unique_id']);
        if(!$this->inmueble) {
            $this->inmueble = $this->insert_data_into_table($data['unique_id'],'commercial');
        }
        
        $ruta_feature_img = IMPORTMLS_DIR . DIR_NAME_TEMP.'/'.$data['unique_id'].'.L01';
        

        // - street_name_es no llega
        // $data['street_name_es'] = '';
        // - bedrooms no llega
        $data['bedrooms'] = 0;
        // - bathrooms no llega
        $data['bathrooms'] = 0;
        
        // Metacampos
        $meta_datos = array(


            'precio-de-venta--precio-de-alquiler' => $data['price_current'],
            'area-totalterreno' => $data['lot_sqft'],
            'area-construida' => $data['sqft_total'],
            'area-privada' => $data['sqft_total'],
            'estado-fisico-de-la-propiedad' => $data['remodelled'],
            'estrato' => 'N/A',
            'garage' => ($data['parking_spaces'] != 0)? $data['parking_spaces'].' estacionamientos':'Sin estacionamientos',
            'banos' => $data['bathrooms'],
            'alcobas' => $data['bedrooms'],
            'ano-de-construccion' => $data['year_built'],//Calcular sobre fecha
            'precio-de-administracion' => $data['monthly_assessment'],//Calcular sobre fecha
            
            'tiempo' => 'Mensual',//Calcular sobre fecha
            'tipo-de-negocio' => 'Venta',
            
            'tipo-de-inmueble' => $data['commercial_type'],
            'caracteristicas-internas' => $this->get_amenities($data['interior_features']),
            'caracteristicas-externas' => $this->get_amenities($data['exterior_features']),



            // '_direccion' => $data['street_name_es'].', '.$data['map_area'].', '.$data['district'],
            // 'descripcion' => $data['remarks_es'],
            // 'id'=> $data['id'],
            // 'floor' => '',
            // 'bodega' =>'',
            // 'comodidades' => $this->get_amenities($data['interior_features'].','.$data['exterior_features']),
            // 'sector' => array(
            //     $data['map_area'] => 'true'
            // ),
            // 'imagenes-del-inmueble' => $gallery_ids,
            // 'urbanizacion' =>($data['subdivision'] !='No aplica')? $data['subdivision']:'',
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

            if( count($gallery_ids) > 0 ){
                $new_gallery=[];
                $old_gallery = get_post_meta($post_id, 'imagenes-del-inmueble', true);
                if(count($old_gallery) > 0){
                    $new_gallery = array_merge($old_gallery,$gallery_ids);
                }else{
                    $new_gallery = $gallery_ids;
                }

                $meta_datos['imagenes-del-inmueble'] =  $new_gallery;
            }

            $post_data = array(
                'ID'            => $post_id,
                'post_title'    => $data['commercial_type'].' en '.$data['map_area'].' - '.$data['district'].' - '.$data['id'],
                'post_content'  => 'CÓDIGO '.$data['id'].'<br>'.$data['remarks_es'],
                'post_status'   => 'publish', 
                'post_type'     => 'propiedades',
                'meta_input'    => $meta_datos 
            );

            $updated = wp_update_post($post_data);

            if (!is_wp_error($updated)) {
                // Log::info('Inmueble Actualizado');
            } else {
                $error_message = $updated->get_error_message();
                Log::info('Error al actualizar el inmueble: ' . $error_message);
            }

        }else{
            
            $meta_datos['imagenes-del-inmueble'] =  $gallery_ids;

            $post_data = array(
                'post_title'    =>$data['commercial_type'].' en '.$data['map_area'].' - '.$data['district'].' - '. $data['id'],
                'post_status'   => 'publish', 
                //'post_status'   => 'pending', 
                'post_type'     => 'propiedades',
                'meta_input'    => $meta_datos 
            );        
            // Crea un nuevo post
            $post_id = wp_insert_post($post_data);

            if (!is_wp_error($post_id)) {
                //Log::info('Inmueble Creado');
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
                
                $this->set_taxonomia($post_id, 23 , 'pais'); //Colombia
                $this->set_taxonomia($post_id, 19 , 'estado-del-inmueble');//Activo
                $this->set_taxonomia($post_id, 15 , 'tipo-de-negocio'); //Venta

            }else{
                $error_message = $post_id->get_error_message();
                Log::info('Error al crear el inmueble: ' . $error_message);
            }
        }

        // Verificar si hay un post 
        if ($post_id) {
            
            $this->set_taxonomia($post_id, $data['map_area'] , 'zonabarrio');
            $this->set_taxonomia($post_id, $data['district'] , 'ciudad');
            // $this->set_taxonomia($post_id,  , 'departamento');
            // $this->set_taxonomia($post_id, $this->get_property_type($data['commercial_type']), 'tipo-de-propiedad');          
        } else {
            Log::error('Error, no hay un id para establecer las taxonomias');
        }        
    }

}