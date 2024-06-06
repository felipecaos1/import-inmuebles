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
                    return true;
                    
                } else {
                    return false;
                }
            } else {
                return false;
            }
        }else{
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
    protected function set_taxonomia($post_id, $targets, $tax ) 
    {
        if (empty($post_id) || !is_int($post_id) || empty($targets) || !is_array($targets)) {
            return new WP_Error('invalid_input', 'Input values are invalid.');
        }

        $term_ids = [];

        foreach ($targets as $target) {

            $term = term_exists($target, $tax);
            
            // Si el término no existe, créalo
            if (!$term) {
                $term = wp_insert_term($target, $tax);
                // Verificar si hubo un error al crear el término
                if (is_wp_error($term)) {
                    return $term;
                }
            }
        }

        // Obtener el ID del término
        $term_id = is_array($term) ? $term['term_id'] : $term;
        $term_id = (int) $term_id;
        $term_ids[] = $term_id;
        
         // Asigna los términos al post utilizando los IDs
        $result = wp_set_object_terms($post_id, $term_ids, $tax, false);
        if (is_wp_error($result)) {
            return $result; // Retornar el error para manejo externo
        }
    
        // Log::info('Se establecio la taxonomia: '. $tax);
        return true;
    }
    
    /**
     * Obtiene los IDs de las imágenes de una galería de medios.
     *
     * @param string $id_unique ID único de la galería.
     * @param int $multi_count Número de imágenes en la galería.
     * @return string Retorna un string con los IDs de las imágenes cargadas, separados por comas.
     */
    protected function get_post_galery_ids($id_unique, $multi_count, $post_galery_insert)
    {
        $list_ids = [];
        $string_post_galery = $post_galery_insert; //"2,3,4,5" || ""


        if (!empty($post_galery_insert)) {
            $post_galery_insert = explode(',', $post_galery_insert); // Arreglo con los datos convertidos
        }else{
            $post_galery_insert = [];
        }

        $count_galery_insert = count($post_galery_insert);

        for ($i=2; $i <= $multi_count ; $i++) {            
            if($count_galery_insert >= ($multi_count - 1)){ // Verifico si cantidad de de imagenes insertadas es mayor o igual a las que se van a procesar, pare el proceso
                break;
            }

            if(!in_array($i,$post_galery_insert)){ //Se valida que solo ingrese los que no esten en el resultado de la base de datos 
                $ext = ($i < 10 ) ? '.L0'.$i : '.L'.$i;
                $ruta_img = IMPORTMLS_DIR . DIR_NAME_TEMP .'/'.$id_unique.$ext;
                if ( file_exists( $ruta_img ) ){
                    $imagen_id = get_option('id_preview');
                    if($i === 2){ //Si es la primer imagen, la tratamos de convertir a .jpeg
                        $destination_path = IMPORTMLS_DIR . DIR_NAME_TEMP .'/'.$id_unique.'-L02.jpeg';
                        $result = $this->convert_image_to_jpg($ruta_img, $destination_path);
                        if (is_wp_error($result)) {
                            Log::error('Hubo un error al convertir la imagen '.$id_unique.$ext);
                        }else{
                            $ruta_img = $destination_path;
                        }
                        $imagen_id = $this->load_image_and_get_id($ruta_img);
                            
                    }
                    if ($imagen_id) {
                        $list_ids[]= $imagen_id;
                        if(!empty($string_post_galery)){
                            $string_post_galery .=','.$i;
                        }else{
                            $string_post_galery .= $i;
                        }
                        
                    }else {
                        // Log::error('Hubo un error al cargar la imagen en la galería.'.$i.' '.$id_unique );
                    }
                }else{
                    // Log::info('La imagen '.$ruta_img.' no exixte para ser insertada en la galería.');
                }
            }

        }
        
        $this->update_by_unique_id($id_unique,['post_galery_insert' => $string_post_galery]);
        

        $str_ids = implode(',', $list_ids );

        return $str_ids;
    }

    /**
     * Devuelve el rango en el que se encuentra los metros cuadrados construidos.
     *
     * @param int $numero metros cuadrados construídos.
     * @return string Retorna un string que indica el rango en donde se encuentra
     */

    protected function rangoMcuadrados($numero) {
        if ($numero < 100) {
            return "Menos de 100 m2";
        } elseif ($numero >= 100 && $numero <= 150) {
            return "100 - 150 m2";
        } elseif ($numero > 150 && $numero <= 250) {
            return "150 - 250 m2";
        } elseif ($numero > 250 && $numero <= 350) {
            return "250 - 350 m2";
        } else {
            return "Más de 350 m2";
        }
    }

     /**
     * Devuelve el rango en el que se encuentra los metros totales del lote.
     *
     * @param int $numero metros cuadrados del lote.
     * @return string Retorna un string que indica el rango en donde se encuentra
     */

     protected function rangoMLote($numero) {
        if ($numero < 500) {
            return "Menos de 500m2";
        } elseif ($numero >= 500 && $numero <= 1000) {
            return "500 - 1.000 m2";
        } elseif ($numero > 1000 && $numero <= 1500) {
            return "1.000 - 1.500 m2";
        } elseif ($numero > 1500 && $numero <= 3000) {
            return "1.500 - 3.000 m2";
        } else {
            return "Más de 3.000 m2";
        }
    }


    // protected function get_post_galery_ids($id_unique, $multi_count, $post_galery_insert)
    // {
    //     $list_ids = [];
    //     $save_post_galery_insert = true;
    //     $cont_porcess = 1;

    //     if (!empty($post_galery_insert)) {
    //         $post_galery_insert = explode(',', $post_galery_insert); // Arreglo con los datos convertidos
    //     }else{
    //         $post_galery_insert = [];
    //     }

    //     $count_galery_insert = count($post_galery_insert);

    //     for ($i=2; $i <= $multi_count ; $i++) {            
    //         if($count_galery_insert >= ($multi_count - 1)){ // Verifico si cantidad de de imagenes insertadas es mayor o igual a las que se van a procesar, pare el proceso
    //             $save_post_galery_insert = false;
    //             break;
    //         }
    //         if($count_galery_insert >= 6){ //Validar que tengan N catidad o menos en la base de datos, si las tiene para el bucle
    //             break;
    //         }
    //         if(!in_array($i,$post_galery_insert)){ //Se valida que solo ingrese los que no esten en el resultado de la base de datos 
    //             if($cont_porcess > 2){ //Solo permitir N proceso para cargue de las imagenes
    //                 break;
    //             }
    //             $ext = ($i < 10 ) ? '.L0'.$i : '.L'.$i;
    //             $ruta_img = IMPORTMLS_DIR . DIR_NAME_TEMP .'/'.$id_unique.$ext;
    //             if ( file_exists( $ruta_img ) ){
    //                 $imagen_id = get_option('id_preview');
    //                 if($i === 2){ //Si es la primer imagen, la tratamos de convertir a .jpeg
    //                     $destination_path = IMPORTMLS_DIR . DIR_NAME_TEMP .'/'.$id_unique.'-L02.jpeg';
    //                     $result = $this->convert_image_to_jpg($ruta_img, $destination_path);
    //                     if (is_wp_error($result)) {
    //                         Log::error('Hubo un error al convertir la imagen '.$id_unique.$ext);
    //                     }else{
    //                         $ruta_img = $destination_path;
    //                     }
    //                     $imagen_id = $this->load_image_and_get_id($ruta_img);
    //                 }
    //                 if ($imagen_id) {
    //                     $list_ids[]= $imagen_id;
    //                     $post_galery_insert[]= $i; //Agregamos el valor de $i al arreglo
    //                 } else {
    //                     // Log::error('Hubo un error al cargar la imagen en la galería.'.$i.' '.$id_unique );
    //                 }
    //                 $cont_porcess ++;
    //             }else{
    //                 // Log::info('La imagen '.$ruta_img.' no exixte para ser insertada en la galería.');
    //             }
    //         }

    //     }
        
    //     if($save_post_galery_insert){
    //         $post_galery_insert = implode(',', $post_galery_insert );//Lo convertimos en cadena nuevamente para poderlo guardar en la base de datos
    //         $this->update_by_unique_id($id_unique,['post_galery_insert' => $post_galery_insert]);
    //     }

    //     $str_ids = implode(',', $list_ids );

    //     return $str_ids;
    // }
    /**
     * Carga una imagen desde una URL y obtiene su ID en la biblioteca de medios de WordPress.
     *
     * @param string $imagen_url URL de la imagen a cargar.
     * @return int|bool Retorna el ID de la imagen cargada o false si no se pudo cargar.
     */
    public function load_image_and_get_id($imagen_url) 
    {
        $file_array = array(
            'name' => wp_basename($imagen_url),
            'tmp_name' => $imagen_url,
        );

        // Incluir los archivos necesarios
        if (!function_exists('media_handle_sideload')) {
            require_once(ABSPATH . 'wp-admin/includes/file.php');
            require_once(ABSPATH . 'wp-admin/includes/media.php');
            require_once(ABSPATH . 'wp-admin/includes/image.php');
        }
        
        
        $imagen_id = media_handle_sideload($file_array);
        
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
        // $array_resultado = array_fill_keys($elementos, 'true');
     
        return $elementos;
    }

     /**
     * Toma el tipo de propiedad mld y devuelve el tipo de propiedad equivalente en el sistema
     *
     * @param string $property_type tipo de propiedad.
     * @return string retorne una cadena con el tipo de propiedad equivalente 
     */

    // protected function get_property_type($property_type)
    // {
    //     $property_type = strtolower($property_type);
    //     switch ($property_type) {
    //         // case 'apartamento':
    //         //     return 'Apartamentos';
    //         //     break;
    //         case 'lote residencial':
    //             return 'Lotes';
    //             break;
    //         // case 'casa':
    //         //     return 'Casas';
    //         //     break;
    //         case 'finca recreativa':
    //             return 'Fincas';
    //             break;
    //         case 'rural':
    //             return 'Fincas';
    //             break;
    //         case 'oficina':
    //             return 'Oficinas';
    //             break;
    //         case 'finca productiva':
    //             return 'Fincas';
    //             break;
    //         case 'bodega':
    //             return 'Bodegas';
    //             break;
    //         case 'lote comercial':
    //             return 'Lotes';
    //             break;
    //         default:
    //             return  ucwords($property_type);
    //             break;
    //     }
    // }

    /**
     * Convierte una imagen a formato JPEG y la guarda en el destino especificado.
     *
     * @param string $source_path Ruta de la imagen de origen.
     * @param string $destination_path Ruta donde se guardará la imagen convertida.
     * @param int $quality Calidad de la imagen JPEG (opcional, por defecto es 90).
     * @return bool Devuelve true si la conversión y el guardado son exitosos, false si hay errores.
     */
    private function convert_image_to_jpg($source_path, $destination_path, $quality = 90) 
    {
        // Obtener una instancia del editor de imágenes
        $image_editor = wp_get_image_editor($source_path);
    
        // Verificar si no se obtuvo la instancia correctamente
        if (is_wp_error($image_editor)) {
            // return $image_editor;
            return false;
        }

        // Realizar alguna manipulación de imagen si es necesario (opcional)
        // $image_editor->resize(800, 600, true);

        // Establecer la calidad de la imagen JPEG
        // $image_editor->set_quality($quality);

        // Guardar la imagen en el archivo de destino con la calidad especificada
        $result = $image_editor->save($destination_path, 'image/jpeg');
    
        if (is_wp_error($result)) {
            // return $result;
            return false;
        }
        
        unlink($source_path);  
        return true;
    }

    /**
     * Insert data into the database table.
     *
     * @param string $unique_id   The unique ID.
     * @param string $import_type The import type.
     *
     * @return int|false The inserted row ID or false on failure.
     */
    protected function insert_data_into_table($unique_id, $import_type)
    {
        global $wpdb;
    
        $tabla_nombre = $wpdb->prefix . TABLE_NAME;
    
        $wpdb->insert(
            $tabla_nombre,
            array(
                'unique_id' => $unique_id,
                'feature_img' => 0,
                'post_created' => 0,
                'post_galery_insert' => null,
                'import_type' => $import_type
            )
        );
    
        $insert_id = $wpdb->insert_id;
        return $wpdb->get_row("SELECT * FROM $tabla_nombre WHERE id = $insert_id");
    }

    protected function get_by_unique_id($unique_id) 
    {
        global $wpdb;
    
        $tabla_nombre = $wpdb->prefix . TABLE_NAME;
    
        $resultado = $wpdb->get_row(
            $wpdb->prepare(
                "SELECT * FROM $tabla_nombre WHERE unique_id = %d", $unique_id
            )
        );
    
        return $resultado;
    }

    public function get_data_by_import_type($import_type) 
    {
        global $wpdb;
    
        $tabla_nombre = $wpdb->prefix . TABLE_NAME;
    
        // Consulta SQL para recuperar los datos
        $sql = $wpdb->prepare("SELECT * FROM $tabla_nombre WHERE import_type = %s", $import_type);
    
        // Ejecutar la consulta
        $results = $wpdb->get_results($sql);
    
        // Verificar si se encontraron resultados
        if ($results) {
            // Devolver los resultados
            return $results;
        } else {
            // No se encontraron resultados
            return [];
        }
    }

    protected function update_by_unique_id($unique_id, $data) 
    {
        global $wpdb;
        $tabla_nombre = $wpdb->prefix . TABLE_NAME;
    
        $resultado = $wpdb->update(
            $tabla_nombre,
            $data,
            array('unique_id' => $unique_id),
            array('%s'), // Formato para 'unique_id' (string)
            array('%s')  // Formato para 'post_galery_insert' (string)
        );
    
        return $resultado !== false;
    }
    

}