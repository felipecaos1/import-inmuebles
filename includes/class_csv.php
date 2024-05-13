<?php
class Csv
{
    private static $header;
    
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
                    Log::info('Inicio la lectura del archivo: ' . $file_name);
                    $csv_data = array();    
                    $counter = 0;

                    while (($data = fgetcsv($file_handle)) !== false) {
                        if($counter == 0){
                            self::$header = $data;
                        }
                        if ($counter >= $start && $counter < $end) {
                            // Log::info('Fila: '. $counter); 
                            // Llamar a la función para crear el post en WordPress                            
                            if($counter == 3){
                                // get_post()
                                $import->crear_inmueble(
                                    self::column_mapping_heading_row($data)
                                );
                            }                          
                        }
                        $counter++;
                        if ($counter >= $end) {
                            break; // Salir del bucle después de procesar el número deseado de filas
                        }
                    }
                    fclose($file_handle);
                    Log::info('Ha terminado la importación',['Post creados' => 10]);
                    // wp_send_json_success(array(
                    //     'message' => 'Se han creado 10 posts desde el CSV.',
                    // )); // Enviar la respuesta JSON
                } else {
                    Log::error('Error al abrir el archivo CSV.');
                }
            } else {
                Log::info('Archivo CSV no encontrado.');
            }
        } else {
            Log::error('Acceso denegado.');
        }
    }

    /**
     * Mapea y limpia los nombres de las columnas del CSV para asignar claves en un array.
     *
     * @param array $row La fila del CSV con los datos.
     * @return array El array mapeado con los nombres de las columnas limpios como claves.
     */
    private static function column_mapping_heading_row($row) : array 
    {
        $return_row = [];
        for ($i=1; $i <= count(self::$header); $i++) { 
            $clean_name = self::clean_column_name(self::$header[$i-1]);
            $return_row[$clean_name] = $row[$i-1];
        }
        return $return_row;
    }

    /**
     * Limpia el nombre de una columna del CSV eliminando el BOM y las comillas.
     *
     * @param string $name El nombre de la columna a limpiar.
     * @return string El nombre de la columna limpio.
     */
    private static function clean_column_name($name) : string 
    {
        // Elimina el BOM y las comillas del nombre de la columna
        return trim(str_replace(["\xef\xbb\xbf", '"'], '', $name));
    }
}
// Agregar acción para la solicitud AJAX
// add_action('wp_ajax_leer_csv_ajax', 'leer_csv_ajax_handler');
// add_action('wp_ajax_nopriv_leer_csv_ajax', 'leer_csv_ajax_handler'); 