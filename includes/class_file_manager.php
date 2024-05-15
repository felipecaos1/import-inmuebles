<?php

require IMPORTMLS_DIR . 'includes/class_log.php';
require IMPORTMLS_DIR . 'includes/class_csv.php';
require IMPORTMLS_DIR . 'imports/class_residential_import.php';
require IMPORTMLS_DIR . 'imports/class_commercial_import.php';

class FileManager
{
    /**
     * Establece una conexión FTP utilizando las credenciales almacenadas en las opciones del plugin.
     *
     * '@return resource|false Retorna un recurso de conexión FTP si la conexión se establece correctamente, o false si faltan credenciales.
     */
    private function my_ftp_connect()
    {
        // Obtener las credenciales FTP almacenadas en las opciones del plugin
        $ftp_server = get_option('ftp_host');
        $ftp_user = get_option('ftp_user');
        $ftp_pass = get_option('ftp_pass');
        $ftp_file = get_option('ftp_path');

        // Verificar que todas las credenciales estén presentes
        if($ftp_server && $ftp_user && $ftp_pass && $ftp_file){
            $ftp = ftp_connect( $ftp_server );
            ftp_login( $ftp, $ftp_user, $ftp_pass );
            return $ftp; // Retornar el recurso de conexión FTP
        }else{
            return false; // Retornar false si faltan credenciales
        }
    }

    /**
     * Inicia la importación masiva de archivos ZIP desde el servidor FTP y procesa cada archivo.
     *
     * @param int $cant Cantidad de archivos a procesar en cada lote.
     * @return string Retorna un mensaje JSON con información sobre la importación.
     */
    public function load_all_zip($cant = 1)
    {
        set_time_limit(600);
    
        try {
            Log::info("Iniciando la importación de los archivos ZIP");
            
            $ftp = $this->my_ftp_connect();
            $archivos = ftp_nlist($ftp, '/');

            $archivosZip = array_filter($archivos, function ($archivo) {
                return pathinfo($archivo, PATHINFO_EXTENSION) == 'zip' && strpos($archivo, 'photo_ofc') === false;
            });
    
            $inicio = ($cant - 1) * 30; // Calcular el índice de inicio basado en la cantidad y el tamaño del lote
            $fin = $cant * 30; // Calcular el índice de fin
    
            $archivosParaProcesar = array_slice($archivosZip, $inicio, 30); // Obtener el lote de archivos a procesar
    
            foreach ($archivosParaProcesar as $key => $archivoZip) {
                $this->download_file($archivoZip,DIR_NAME_TEMP);
                $this->import_file($archivoZip, 'zip');
                $this->delete_file($archivoZip);
            }
    
            Log::info("La importación de los archivos ZIP fue exitosa");
            Log::info("Total archivos = " . count($archivosZip) . " : numero peticion url = " . $cant);
            return json_encode(['message' => 'La importación de los archivos ZIP fue exitosa', 'Total zips' =>$archivosZip]);
    
        } catch (\Exception $e) {
            Log::error("Error durante la importación de los archivos ZIP: " . $e->getMessage());
        }
    }

    /**
     * Importa archivos de datos de inmuebles (residenciales y comerciales) y fotos de inmuebles.
     * Los archivos se descargan, se importan a la base de datos y luego se eliminan.
     *
     * @param string|null $date Fecha en formato 'Ymd' de la que se importarán los archivos. Si es nulo, se usa la fecha actual.
     */
    public function import($date = null)
    {        
        date_default_timezone_set('America/Bogota');
        set_time_limit(0);
        
        Log::info('Inicia la importación');
        if($date == null){
            $date = date('Ymd');
        }

        $residentialFile = "/res{$date}.csv";
        $commercialFile = "/com{$date}.csv";
        $zip = "/photo{$date}.zip";
        
        //Procesar zip
        $this->download_file($zip,DIR_NAME_TEMP);
        $this->import_file($zip,'zip');
        $this->delete_file($zip);

        //Procesar Residencial
        $this->download_file($residentialFile,DIR_NAME_TEMP);
        $this->import_file($residentialFile,'residential');
        $this->delete_file($residentialFile);
        
        //Procesar Comercial
        $this->download_file($commercialFile,DIR_NAME_TEMP);
        $this->import_file($commercialFile,'commercial');
        $this->delete_file($commercialFile);
    }

    /**
     * Descarga un archivo desde un servidor FTP.
     *
     * @param string $name_file Nombre del archivo a descargar.
     * @param string $path Ruta local donde se almacenará el archivo descargado.
     * @return bool True si la descarga fue exitosa, false si hubo un error.
     */
    private function download_file($name_file, $path) 
    {
        $response = false;
        $ftp = $this->my_ftp_connect();
        if ($ftp) {
            Log::info('Conectado al servidor FTP');
            // Verifica si el archivo existe en el servidor FTP
            $files = ftp_nlist($ftp, '/');
            if (in_array($name_file, $files)) {
                $destination_file = IMPORTMLS_DIR . $path . $name_file;  // Asegúrate de que el directorio 'csv' existe o créalo.
                if (!file_exists(IMPORTMLS_DIR . $path)) {
                    mkdir(IMPORTMLS_DIR . $path , 0777, true); // Crea el directorio si no existe.
                }
                if (ftp_get($ftp, $destination_file, $name_file, FTP_BINARY)) {          
                    Log::info('Archivo descargado con éxito: ' . $destination_file);
                    $response = true;
                } else {
                    Log::info('Error al descargar el archivo: ' . $name_file);
                }
            } else {
                Log::info('Archivo no encontrado en el servidor FTP: ' . $name_file);
            }
            ftp_close($ftp);
        } else {
            Log::error('Error al conectar al FTP.');
        }
        return $response;
    }

    /**
     * Importa un archivo CSV o descomprimir un ZIP.
     *
     * @param string $name_file Nombre del archivo a importar.
     * @param string $import_type Tipo de archivo a importar ('csv' o 'zip').
     */
    private function import_file($name_file,$import_type)
    {
        if($import_type == 'residential'){
            // Importar archivo CSV
            Csv::import(new ResidentialImport(),DIR_NAME_TEMP.'/'.$name_file);
        }elseif($import_type == 'commercial'){ 
            Csv::import(new CommercialImport(),DIR_NAME_TEMP.'/'.$name_file);
        }elseif($import_type == 'zip'){ 
            // Descomprimir archivo ZIP           
            $destination_file = IMPORTMLS_DIR .DIR_NAME_TEMP . $name_file;
            if (pathinfo($destination_file, PATHINFO_EXTENSION) === 'zip') {
                $this->unzipFile($destination_file, IMPORTMLS_DIR . DIR_NAME_TEMP);
            } else {
                echo "El archivo descargado no es un archivo comprimido.";
            }
        }
    }

    /**
     * Descomprime un archivo ZIP.
     *
     * @param string $filePath Ruta al archivo ZIP que se va a descomprimir.
     * @param string $extractTo Ruta donde se extraerán los archivos del ZIP.
     */
    private function unzipFile($filePath, $extractTo) 
    {
        $zip = new ZipArchive;
        $res = $zip->open($filePath);
        if ($res === TRUE) {
            $zip->extractTo($extractTo);
            $zip->close();
            Log::info('Archivo descomprimido con éxito: ' . $filePath);
        } else {
            Log::error('Error al descomprimir el archivo: ' . $filePath);
        }
    }

    /**
     * Elimina un archivo.
     *
     * @param string $name_file Nombre del archivo a eliminar.
     */
    private function delete_file($name_file)
    {
        $file_path = IMPORTMLS_DIR .DIR_NAME_TEMP . $name_file;

        if (file_exists($file_path)) {
            unlink($file_path);
            Log::info('Archivo eliminado con éxito: ' . $name_file);
        } else {
            Log::error('El archivo '.$name_file.'no existe.');
        }
    }

}