<?php
class ImmovableImport
{
    /**
     * Crea una nueva entrada de propiedad en WordPress utilizando los datos proporcionados.
     *
     * @param array $data Datos de la propiedad obtenidos del archivo CSV.
     */
    public function crear_inmueble($data)
    {

        file_put_contents(IMPORTMLS_DIR.LOG_FILE, date('H:i:s') . 'Creando el inmueble: '. $data[8] . PHP_EOL, FILE_APPEND);

        // funcion para crear un array con los id de las imagenes
        $gallery_ids = $this->get_post_galery_ids($data[31],$data[32]);

        
        // Metacampos
        $meta_datos = array(
            'valor' => $data[2],
            '_direccion' => $data[63].', '.$data[6].', '.$data[5],
            'area-de-la-propiedad' => $data[14],
            'area-construida' => $data[15],
            'descripcion' => $data[25],
            'estrato' => '',
            'id'=> $data[8],
            'tiempo-construccion' => $this->calculate_built_time($data[9]),//Calcular sobre fecha
            'floor' => '',
            'estacionamiento' => ($data[54] != 0)? $data[54].' estacionamientos':'Sin estacionamientos',
            'habitaciones' => $data[10],
            'banos' => $data[11],
            'bodega' =>'',
            'comodidades' => $this->get_amenities($data[40].','.$data[41]),
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
            file_put_contents(IMPORTMLS_DIR.LOG_FILE, date('H:i:s') . 'Se creo el post: '. $post_id . PHP_EOL, FILE_APPEND);

            $this->set_taxonomia($post_id, $data[5], 'ciudad');
            $this->set_taxonomia($post_id, $this->get_property_type($data[7]), 'tipo-de-propiedad');

            $ruta_feature_img = IMPORTMLS_DIR . DIR_NAME_TEMP.'/'.$data[31].'.L01';
            
            if ( file_exists( $ruta_feature_img ) ){
                $imagen_id = $this->load_image_and_get_id($ruta_feature_img);
                if ($imagen_id) {
                    set_post_thumbnail($post_id, $imagen_id);
                    file_put_contents(IMPORTMLS_DIR.LOG_FILE, date('H:i:s') . 'Se cargo la imagen destacada del post: ' . PHP_EOL, FILE_APPEND);
                } else {
                    file_put_contents(IMPORTMLS_DIR.LOG_FILE, date('H:i:s') . 'Hubo un error al cargar la imagen: '. $ruta_feature_img . PHP_EOL, FILE_APPEND);
                    // echo 'Hubo un error al cargar la imagen.';
                }
            }else{
                file_put_contents(IMPORTMLS_DIR.LOG_FILE, date('H:i:s') . 'La imagen: '.$ruta_feature_img.' no existe' . PHP_EOL, FILE_APPEND);
            }
            
        } else {
    
            file_put_contents(IMPORTMLS_DIR.LOG_FILE, date('H:i:s') . 'Error al intentar crear el post ' . PHP_EOL, FILE_APPEND);
            echo 'Error al crear el post: ' . $post_id->get_error_message();
        }
        
    }

    /**
     * Asigna una term a un post en WordPress.
     *
     * @param int $post_id ID del post al que se asignará la term.
     * @param string $term Nombre de la term a asignar.
     * @return true|WP_Error Retorna true si la asignación fue exitosa, o un objeto WP_Error si hubo un error.
     */
    private function set_taxonomia($post_id, $target, $tax ) 
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
    
        file_put_contents(IMPORTMLS_DIR.LOG_FILE, date('H:i:s') . 'Se establecio la taxonomia: '. $tax . PHP_EOL, FILE_APPEND);
        return true;
    }
    
    /**
     * Obtiene los IDs de las imágenes de una galería de medios.
     *
     * @param string $id_unique ID único de la galería.
     * @param int $multi_count Número de imágenes en la galería.
     * @return string Retorna un string con los IDs de las imágenes cargadas, separados por comas.
     */
    private function get_post_galery_ids($id_unique ='', $multi_count = 1 )
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
                    file_put_contents(IMPORTMLS_DIR.LOG_FILE, date('H:i:s') . 'Hubo un error al cargar la imagen en la galería.' . PHP_EOL, FILE_APPEND);
                }
            }else{
                file_put_contents(IMPORTMLS_DIR.LOG_FILE, date('H:i:s') . 'La imagen '.$ruta_img.' no exixte para ser insertada en la galería.' . PHP_EOL, FILE_APPEND);
            }
        }
        $str_ids = implode(',', $list_ids );

        file_put_contents(IMPORTMLS_DIR.LOG_FILE, date('H:i:s') . 'Se creo la galería.' . PHP_EOL, FILE_APPEND);
        return $str_ids;
    }

    /**
     * Carga una imagen desde una URL y obtiene su ID en la biblioteca de medios de WordPress.
     *
     * @param string $imagen_url URL de la imagen a cargar.
     * @return int|bool Retorna el ID de la imagen cargada o false si no se pudo cargar.
     */
    private function load_image_and_get_id($imagen_url) 
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
    private function calculate_built_time($year_construction)
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
    private function get_amenities($amenities)
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

     private function get_property_type($property_type)
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
             
             default:
                 return $property_type;
                 break;
         }
     }
}