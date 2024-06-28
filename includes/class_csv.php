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
                $count_for_batch_size = 0; //conteo para igualar el el ote para procesar
                $counter = 0;
                
                $BATCH_SIZE_INIT = Runtime::get_runtime('BATCH_SIZE_INIT');
                $BATCH_SIZE_END = Runtime::get_runtime('BATCH_SIZE_END');

                while (($data = fgetcsv($file_handle)) !== false) {
                    if ($counter == 0) {
                        self::$header = $data;
                    } else if($counter > $BATCH_SIZE_INIT){
                        $csv_data[] = $data;

                        // Procesar en lotes
                        if (count($csv_data) >= $batch_size) {
                            Log::info('Entro a process_batch: ' . $counter);
                            self::process_batch($import, $csv_data);
                            $csv_data = []; // Reiniciar el array para el siguiente lote
                        }
                    }
                    // if($counter == $batch_size){
                    if($counter == $BATCH_SIZE_END){
                        break;
                    }
                    $counter++;
                }

                // Procesar cualquier fila restante
                if (!empty($csv_data)) {
                    Log::info('Entro a process_batch en fila restante: ' . $counter);
                    self::process_batch($import, $csv_data);
                }

                //Seteamos las variables de ejecución con los nuevos valores
                Runtime::set_runtime('BATCH_SIZE_INIT', ($BATCH_SIZE_INIT + 3000));
                Runtime::set_runtime('BATCH_SIZE_END',  ($BATCH_SIZE_END + 3000));
                
                fclose($file_handle);
                Log::info('Ha terminado la importación',['Post creados' => $counter - 1]);
                $result = true;
            } else {
                Log::error('Error al abrir el archivo CSV.');
            }
        } else {
            Log::info('Archivo CSV no encontrado.');
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
