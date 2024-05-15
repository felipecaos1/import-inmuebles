<?php
class Import
{
    /**
     * Establece la imagen destacada del post 
     *
     * @param string $ruta_feature_img | int $post_id recibe la ruta de la imagen y el id del post
     * @return true | false 
     */
    public function set_feature_img($post_id , $ruta_feature_img)
    {
        if ( file_exists( $ruta_feature_img ) ){
            $imagen_id = $this->load_image_and_get_id($ruta_feature_img);

            if ($imagen_id) {
                $result_thumb = set_post_thumbnail($post_id, $imagen_id);

                if (!is_wp_error($result_thumb)) {
                    // Log::info('Se establecio la imagen destacada del post');
                    return true;
                } else {
                    // Log::info('Error estableciendo la imagen destacada del post');
                    return false;
                }

            } else {
                // Log::error('Hubo un error al cargar la imagen: '. $ruta_feature_img);
                return false;
            }
        }else{
            // Log::info('La imagen: '.$ruta_feature_img.' no existe');
            return false;
        } 
    }
    
    /**
     * Busca un post py devuelve el id, si lo encuentra
     *
     * @param int $meta_id ID del post que se buscará
     * @return id | false Retorna id si encuentra el post o retorna false, si no lo encuentra.
     */
    public function buscar_inmueble_por_id($id)
    {
        $args = array(
            'post_type' => 'propiedades',
            'meta_query' => array(
                array(
                    'key' => 'id',
                    'value' => $id,
                    'compare' => '='
                )
            )
        );

        $query = new WP_Query($args);

        if ($query->have_posts()) {
            $query->the_post();
            return get_the_ID();
        } else {
            wp_reset_postdata();
            return false;
        }
    }
    
    /**
     * Asigna una term a un post en WordPress.
     *
     * @param int $post_id ID del post al que se asignará la term.
     * @param string $term Nombre de la term a asignar.
     * @return true|WP_Error Retorna true si la asignación fue exitosa, o un objeto WP_Error si hubo un error.
     */
    protected function set_taxonomia($post_id, $target, $tax ) 
    {
        if (empty($post_id) || !is_int($post_id) || empty($target)) {
            return new WP_Error('invalid_input', 'Input values are invalid.');
        }
        $term = term_exists($target, $tax);
        
        // Si el término no existe, créalo
        if (!$term) {
            $term = wp_insert_term($target, $tax);
            // Verificar si hubo un error al crear el término
            if (is_wp_error($term)) {
                return $term;
            }
        }

        // Obtener el ID del término
        $term_id = is_array($term) ? $term['term_id'] : $term;
        $term_id = (int) $term_id;
        
        // Asigna el término al post utilizando el ID
        $result = wp_set_object_terms($post_id, $term_id , $tax, false);
        if (is_wp_error($result)) {
            return $result; // Retornar el error para manejo externo
        }
    
        Log::info('Se establecio la taxonomia: '. $tax);
        return true;
    }
    
    /**
     * Obtiene los IDs de las imágenes de una galería de medios.
     *
     * @param string $id_unique ID único de la galería.
     * @param int $multi_count Número de imágenes en la galería.
     * @return string Retorna un string con los IDs de las imágenes cargadas, separados por comas.
     */
    protected function get_post_galery_ids($id_unique ='', $multi_count = 1 )
    {
        $list_ids = [];
        for ($i=2; $i <= $multi_count ; $i++) {
            $ext = ($i < 10 ) ? '.L0'.$i : '.L'.$i;
            $ruta_img = IMPORTMLS_DIR . DIR_NAME_TEMP .'/'.$id_unique.$ext;
            if ( file_exists( $ruta_img ) ){
                $imagen_id = $this->load_image_and_get_id($ruta_img);
                if ($imagen_id) {
                    $list_ids[]= $imagen_id;
                } else {
                    // Log::error('Hubo un error al cargar la imagen en la galería.');
                }
            }else{
                // Log::info('La imagen '.$ruta_img.' no exixte para ser insertada en la galería.');
            }
        }
        $str_ids = implode(',', $list_ids );

        // Log::info('Se creo la galería.');
        return $str_ids;
    }

    /**
     * Carga una imagen desde una URL y obtiene su ID en la biblioteca de medios de WordPress.
     *
     * @param string $imagen_url URL de la imagen a cargar.
     * @return int|bool Retorna el ID de la imagen cargada o false si no se pudo cargar.
     */
    protected function load_image_and_get_id($imagen_url) 
    {
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

    /**
     * Calcula el tiempo transcurrido desde un año de construcción dado hasta el año actual.
     *
     * @param int|string $year_construction Año de construcción.
     * @return int|string Retorna el número de años transcurridos o una cadena vacía si el año de construcción es inválido
     */
    protected function calculate_built_time($year_construction)
    {
        if($year_construction != '' && $year_construction != null && strlen($year_construction) == 4 ){
            $year_current = date('Y');
            return $year_current - $year_construction;
        }
        return '';
    }

    /**
     * Convierte una cadena de string en un arreglo de como
     *
     * @param string $amenities listado de comodidades.
     * @return Array Retorna un array de comodidades para almacenar en la base de datos
     */
    protected function get_amenities($amenities)
    {        
        // Separamos el string 
        $elementosBrutos = explode(',', $amenities);

        // Limpiar los elementos: eliminar espacios en blanco y filtrar elementos vacíos
        $elementos = array_filter(array_map('trim', $elementosBrutos), function($item) {
            return $item !== '';
        });

        // Crear un array asociativo donde cada elemento tiene el valor 'true'
        $array_resultado = array_fill_keys($elementos, 'true');
     
        return $array_resultado;
    }

     /**
     * Toma el tipo de propiedad mld y devuelve el tipo de propiedad equivalente en el sistema
     *
     * @param string $property_type tipo de propiedad.
     * @return string retorne una cadena con el tipo de propiedad equivalente 
     */

     protected function get_property_type($property_type)
     {
         $property_type = strtolower($property_type);
         switch ($property_type) {
             case 'apartamento':
                 return 'Apartamentos';
                 break;
             case 'lote residencial':
                 return 'Lote';
                 break;
             case 'casa':
                 return 'Casas';
                 break;
             case 'finca recreativa':
                 return 'Fincas';
                 break;
             case 'rural':
                 return 'Fincas';
                 break;
             case 'oficina':
                 return 'Oficinas';
                 break;
            //  case 'local comercial':
            //      return '';
            //      break;
            //  case 'consultorio':
            //      return '';
            //      break;
            //  case 'hotel/apart hotel':
            //      return '';
            //      break;
             case 'finca productiva':
                 return 'Fincas';
                 break;
             case 'bodega':
                 return 'Bodegas';
                 break;
             case 'lote comercial':
                 return 'Lotes';
                 break;
            //  case 'parqueadero':
            //      return '';
            //      break;
             
             default:
                 return $property_type;
                 break;
         }
     }

}