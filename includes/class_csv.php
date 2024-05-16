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
    public static function import($import, $file_name) 
    {
        $result = false;        
        // Verificar si el usuario actual tiene permisos para realizar la acción
        if (current_user_can('manage_options')) {    
            $file_path = IMPORTMLS_DIR . $file_name;
                    
            if (file_exists($file_path)) {
                $file_handle = fopen($file_path, 'r'); // Abrir el archivo en modo lectura
                if ($file_handle !== false) {
                    Log::info('Inicio la lectura del archivo: ' . $file_name);
                    
                    // Leer el archivo en bloques
                    set_time_limit(0); // Asegura que el script no tenga límite de tiempo de ejecución
                    // ini_set('memory_limit', '512M'); // Ajusta el límite de memoria
                    
                    $csv_data = [];
                    $batch_size = 50; // Tamaño del lote para procesamiento por lotes
                    $counter = 0;

                    while (($data = fgetcsv($file_handle)) !== false) {
                        if ($counter == 0) {
                            self::$header = $data;
                        } else {
                            $csv_data[] = $data;

                            // Procesar en lotes
                            if (count($csv_data) >= $batch_size) {
                                self::process_batch($import, $csv_data);
                                $csv_data = []; // Reiniciar el array para el siguiente lote
                            }
                        }
                        if($counter == 100){
                            break;
                        }
                        $counter++;
                    }

                    // Procesar cualquier fila restante
                    if (!empty($csv_data)) {
                        self::process_batch($import, $csv_data);
                    }

                    fclose($file_handle);
                    Log::info('Ha terminado la importación',['Post creados' => $counter - 1]);
                    $result = true;
                } else {
                    Log::error('Error al abrir el archivo CSV.');
                }
            } else {
                Log::info('Archivo CSV no encontrado.');
            }
        } else {
            Log::error('Acceso denegado.');
        }

        return $result;
    }

    /**
     * Procesa un lote de datos CSV.
     *
     * @param object $import Objeto de importación que contiene la lógica de importación específica.
     * @param array $batch Datos CSV a procesar.
     */
    private static function process_batch($import, $batch) 
    {
        foreach ($batch as $row) {
            $import->crear_inmueble(self::column_mapping_heading_row($row));
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
        for ($i = 1; $i <= count(self::$header); $i++) { 
            $clean_name = self::clean_column_name(self::$header[$i - 1]);
            $return_row[$clean_name] = $row[$i - 1];
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
