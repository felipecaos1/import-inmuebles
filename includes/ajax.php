<?php

// Modificar la función para leer el archivo CSV y crear un número limitado de posts
function leer_csv_ajax_handler($file_name) 
{
    // Verificar si el usuario actual tiene permisos para realizar la acción
    if (current_user_can('manage_options')) {
        
        $file_path = IMPORTMLS_DIR . $file_name;
                 
        if (file_exists($file_path)) {
            $batch_size = 2; // Número de registros por lote
            $start = isset($_POST['start']) ? intval($_POST['start']) : 0; // Obtener el punto de inicio del lote desde la solicitud AJAX
            $end = $start + $batch_size; // Calcular el final del lote
            $file_handle = fopen($file_path, 'r'); // Abrir el archivo en modo lectura
            if ($file_handle !== false) {
                $csv_data = array();
                $counter = 0;
                while (($data = fgetcsv($file_handle)) !== false) {
                    if ($counter >= $start && $counter < $end) {
                        // Verificar que la fila tiene la cantidad esperada de elementos
                        if (count($data) == 65) {
                            // Llamar a la función para crear el post en WordPress
                           
                            if($counter != 0){
                                crear_inmueble($data);
                            }
                        } else {
                            // echo 'no';
                            // Opcionalmente, puedes manejar filas con la cantidad incorrecta de elementos aquí
                        }
                    }
                    $counter++;
                    if ($counter >= $end) {
                        break; // Salir del bucle después de procesar el número deseado de filas
                    }
                }
                fclose($file_handle);
                wp_send_json_success(array(
                    'message' => 'Se han creado 10 posts desde el CSV.',
                )); // Enviar la respuesta JSON
            } else {
                wp_send_json_error('Error al abrir el archivo CSV.');
            }
        } else {
            wp_send_json_error('Archivo CSV no encontrado.');
        }
    } else {
        wp_send_json_error('Acceso denegado.');
    }
}
// Agregar acción para la solicitud AJAX
add_action('wp_ajax_leer_csv_ajax', 'leer_csv_ajax_handler');
add_action('wp_ajax_nopriv_leer_csv_ajax', 'leer_csv_ajax_handler'); 