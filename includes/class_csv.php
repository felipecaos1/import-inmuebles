<?php
class Csv
{
    
    /**
     * Importa datos desde un archivo CSV.
     * 
     * @param  object  $import Objeto de importación que contiene la lógica de importación específica.
     * @param  string  $file_name Nombre del archivo CSV a importar.
     */
    public static function import($import,$file_name) 
    {

        // Verificar si el usuario actual tiene permisos para realizar la acción
        if (current_user_can('manage_options')) {            
            $file_path = IMPORTMLS_DIR . $file_name;
                    
            if (file_exists($file_path)) {

                $batch_size = 4; // Número de registros por lote
                $start = isset($_POST['start']) ? intval($_POST['start']) : 0; // Obtener el punto de inicio del lote desde la solicitud AJAX
                $end = $start + $batch_size; // Calcular el final del lote
                $file_handle = fopen($file_path, 'r'); // Abrir el archivo en modo lectura
                if ($file_handle !== false) {
                    file_put_contents(IMPORTMLS_DIR.LOG_FILE, date('H:i:s') . 'Inicio la lectura del archivo: ' . $file_path . PHP_EOL, FILE_APPEND);

                    $csv_data = array();
                    $counter = 0;
                    while (($data = fgetcsv($file_handle)) !== false) {
                        file_put_contents(IMPORTMLS_DIR.LOG_FILE, date('H:i:s') . 'Fila: '. $counter . PHP_EOL, FILE_APPEND);
                        if ($counter >= $start && $counter < $end) {
                            // Llamar a la función para crear el post en WordPress                            
                            if($counter == 3){
                                // get_post()
                                $import->crear_inmueble($data);
                            }
                          
                        }
                        $counter++;
                        if ($counter >= $end) {
                            break; // Salir del bucle después de procesar el número deseado de filas
                        }
                    }
                    fclose($file_handle);
                    file_put_contents(IMPORTMLS_DIR.LOG_FILE, date('H:i:s') . 'Ha terminado la importación' . PHP_EOL, FILE_APPEND);
                    // wp_send_json_success(array(
                    //     'message' => 'Se han creado 10 posts desde el CSV.',
                    // )); // Enviar la respuesta JSON
                } else {
                    file_put_contents(IMPORTMLS_DIR.LOG_FILE, date('H:i:s') . 'Error al abrir el archivo CSV.' . PHP_EOL, FILE_APPEND);
                }
            } else {
                file_put_contents(IMPORTMLS_DIR.LOG_FILE, date('H:i:s') . 'Archivo CSV no encontrado.' . PHP_EOL, FILE_APPEND);
            }
        } else {
            file_put_contents(IMPORTMLS_DIR.LOG_FILE, date('H:i:s') . 'Acceso denegado.' . PHP_EOL, FILE_APPEND);
        }
    }
}
// Agregar acción para la solicitud AJAX
// add_action('wp_ajax_leer_csv_ajax', 'leer_csv_ajax_handler');
// add_action('wp_ajax_nopriv_leer_csv_ajax', 'leer_csv_ajax_handler'); 