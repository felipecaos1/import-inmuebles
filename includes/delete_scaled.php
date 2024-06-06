<?php
// Función para eliminar versiones escaladas de una imagen específica usando el nombre de archivo
function delete_scaled_images_by_filename($filename) {
    // Buscar el adjunto por el nombre de archivo
    // Array de ids
    // dump_json($filename);
  
    $attachment_ids = attachment_ids_by_filename($filename);
    // dump_json($attachment_ids);
    // exit;
    if (empty($attachment_ids)) {
        // error_log("No se pudo encontrar un adjunto con el nombre de archivo: $filename");
        return false;
    }

    // for ids 
    $count = 0;
    foreach ( $attachment_ids as $id ){
        // Obtener los metadatos del adjunto
        $metadata = wp_get_attachment_metadata($id);
        
        // Si no hay metadatos, la imagen no existe o no es válida
        if (!$metadata) {
            error_log("No se encontraron metadatos para el adjunto con ID: $id");
            return false;
        }
        
        // Obtener el directorio de carga
        $upload_dir = wp_upload_dir();
        $base_dir = $upload_dir['basedir'];
        
        // Obtener la ruta completa del archivo original
        $original_file = $base_dir . '/' . $metadata['file'];
        
        // Recorrer y eliminar cada versión escalada
        foreach ($metadata['sizes'] as $size => $size_info) {
            $scaled_file = $base_dir . '/' . dirname($metadata['file']) . '/' . $size_info['file'];
            if (file_exists($scaled_file)) {

                @unlink($scaled_file);
                $count++;
            }
        }
        
        // Actualizar los metadatos del adjunto para eliminar las versiones escaladas
        $metadata['sizes'] = array();
        wp_update_attachment_metadata($id, $metadata);
    }
    
    echo $count.'<br>';
}

function attachment_ids_by_filename($filenames) {
    global $wpdb;
    
    // Asegurar que todos los elementos en el array sean cadenas
    $placeholders = implode(',', array_fill(0, count($filenames), '%s'));

    // Crear la consulta SQL utilizando IN para manejar múltiples nombres de archivo
    $query = $wpdb->prepare("SELECT ID FROM $wpdb->posts WHERE post_type = 'attachment' AND post_title IN ($placeholders)", $filenames);

    // Devolver los IDs de los adjuntos que coinciden
    return $wpdb->get_col($query);
}

    /**
     * Inicializa el proceso de eliminación de imágenes escaladas.
     * Este proceso elimina imágenes escaladas según un rango específico definido por un contador.
     * El contador se utiliza para controlar la ejecución de la función.
     *
     * @return void
     */
    function init_delete_img_scaled() 
    {
        // Establecer el límite de tiempo de ejecución a infinito para evitar que la función se detenga prematuramente.
        set_time_limit(0);
        
         // Crear una instancia de la clase ResidentialImport para obtener los datos según el tipo de importación.
        $res = new ResidentialImport();
        $data_res = $res->get_data_by_import_type('residential');
        $data_com = $res->get_data_by_import_type('commercial');
        
        // Inicializar un contador para realizar un seguimiento del número de iteraciones.
        $count_res = 0;        

        // Iterar sobre los datos obtenidos.
        foreach($data_res as $inmueble){
            delete_scaled_images_by_filename([$inmueble->unique_id,$inmueble->unique_id.'-L02']);
            // Incrementar el contador de iteración.         
            $count_res ++;
        }

        // Inicializar un contador para realizar un seguimiento del número de iteraciones.
        $count_com = 0;        

        // Iterar sobre los datos obtenidos.
        foreach($data_com as $inmueble){
            delete_scaled_images_by_filename([$inmueble->unique_id,$inmueble->unique_id.'-L02']);
            // Incrementar el contador de iteración.         
            $count_com ++;
        }

        Log::info("Inmuebles procesados",['residential' => $count_res, 'commercial' => $count_com]);
    }

?>