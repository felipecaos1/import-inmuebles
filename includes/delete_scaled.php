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

// Ejemplo de uso: Eliminar versiones escaladas de una imagen específica por nombre de archivo
function init_delete_img_scaled($import_type) {
    set_time_limit(0);
    $res = new ResidentialImport();
    $data = $res->get_data_by_import_type($import_type);

    // dump_json(count($data),$data);
    // $com = new CommercialImport();
    // $data = $com->get_data_by_import_type($import_type);
    $count = 0;
    foreach($data as $inmueble){

        if($count > 5000 && $count <= 6000){
          delete_scaled_images_by_filename([$inmueble->unique_id,$inmueble->unique_id.'-L02']);
        }
          
     $count++;
    }
}

?>