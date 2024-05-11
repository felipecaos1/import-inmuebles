<?php

require IMPORTMLS_DIR . 'includes/class_csv.php';
require IMPORTMLS_DIR . 'includes/class_inmueble_import.php';

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
     * Importa archivos de datos de inmuebles (residenciales y comerciales) y fotos de inmuebles.
     * Los archivos se descargan, se importan a la base de datos y luego se eliminan.
     *
     * @param string|null $date Fecha en formato 'Ymd' de la que se importarán los archivos. Si es nulo, se usa la fecha actual.
     */
    public function import($date = null)
    {        
        file_put_contents(IMPORTMLS_DIR.LOG_FILE, date('H:i:s') . 'Inicia la importación' . PHP_EOL, FILE_APPEND);
        date_default_timezone_set('America/Bogota');
        set_time_limit(600);

        if($date == null){
            $date = date('Ymd');
        }

        $residentialFile = "/res{$date}.csv";
        $commercialFile = "/com{$date}.csv";
        $zip = "/photo{$date}.zip";
        
        //Descargar los archivos
        $this->download_file($residentialFile,DIR_NAME_TEMP);
        $this->download_file($commercialFile,DIR_NAME_TEMP);
        $this->download_file($zip,DIR_NAME_TEMP);

        //Importar
        $this->import_file($zip,'zip');
        $this->import_file($residentialFile,'csv');
        $this->import_file($commercialFile,'csv');

        //Eliminar
        // $this->delete_file($residentialFile);
        // $this->delete_file($commercialFile);
        // $this->delete_file($zip);

        // echo json_encode([$residentialFile,$commercialFile,$zip]);
        // exit;
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
            file_put_contents(IMPORTMLS_DIR.LOG_FILE, date('H:i:s') . 'Conecatdo al servidor FTP' . PHP_EOL, FILE_APPEND);
            // Verifica si el archivo existe en el servidor FTP
            $files = ftp_nlist($ftp, '/');
            if (in_array($name_file, $files)) {
                $destination_file = IMPORTMLS_DIR . $path . $name_file;  // Asegúrate de que el directorio 'csv' existe o créalo.
                if (!file_exists(IMPORTMLS_DIR . $path)) {
                    mkdir(IMPORTMLS_DIR . $path , 0777, true); // Crea el directorio si no existe.
                }
                if (ftp_get($ftp, $destination_file, $name_file, FTP_BINARY)) {
                    file_put_contents(IMPORTMLS_DIR.LOG_FILE, date('H:i:s') . 'Archivo descargado con éxito: ' . $destination_file . PHP_EOL, FILE_APPEND);          
                    $response = true;
                } else {
                    file_put_contents(IMPORTMLS_DIR.LOG_FILE, date('H:i:s') . 'Error al descargar el archivo: ' . $name_file . PHP_EOL, FILE_APPEND);
                }
            } else {
                file_put_contents(IMPORTMLS_DIR.LOG_FILE, date('H:i:s') . 'Archivo no encontrado en el servidor FTP: ' . $name_file . PHP_EOL, FILE_APPEND);
            }
            ftp_close($ftp);
        } else {
            file_put_contents(IMPORTMLS_DIR.LOG_FILE, date('H:i:s') . 'Error al conectar al FTP. ' . PHP_EOL, FILE_APPEND);
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

        if($import_type == 'csv'){
            // Importar archivo CSV
            Csv::import(new ImmovableImport(),DIR_NAME_TEMP.'/'.$name_file);
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
            file_put_contents(IMPORTMLS_DIR.LOG_FILE, date('H:i:s') . 'Archivo descomprimido con éxito: ' . $filePath . PHP_EOL, FILE_APPEND);
        } else {
            file_put_contents(IMPORTMLS_DIR.LOG_FILE, date('H:i:s') . 'Error al descomprimir el archivo: ' . $filePath . PHP_EOL, FILE_APPEND);
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
            file_put_contents(IMPORTMLS_DIR.LOG_FILE, date('H:i:s') . 'Archivo eliminado con éxito: ' . $name_file . PHP_EOL, FILE_APPEND);
        } else {
            file_put_contents(IMPORTMLS_DIR.LOG_FILE, date('H:i:s') . 'El archivo '.$name_file.'no existe. '. PHP_EOL, FILE_APPEND);
        }
    }

    // Función para procesar el archivo CSV desde FTP
    private function mi_plugin_inmuebles_procesar_csv_desde_ftp() 
    {
        $ftp_server = $_POST['ftp_host'];
        $ftp_user = $_POST['ftp_user'];
        $ftp_pass = $_POST['ftp_pass'];
        $ftp_file = $_POST['ftp_path'];

        $ftp = ftp_connect( $ftp_server );

        if ( $ftp ) {
            if ( ftp_login( $ftp, $ftp_user, $ftp_pass ) ) {
                $files = ftp_nlist( $ftp, $ftp_file );
                ftp_close( $ftp );
                wp_send_json( [ 'archivos' => $files ] );
                exit;
            } else {
                return false; // Error al iniciar sesión en el servidor FTP
            }
        } else {
            return false; // Error al conectar al servidor FTP
        }

        // $conn_id = ftp_connect($ftp_server);
        // if ($conn_id === false) {
        //     // Manejar el error de conexión
        //     return;
        // }

        // $login = ftp_login($conn_id, $ftp_user, $ftp_pass);
        // if (!$login) {
        //     // Manejar el error de inicio de sesión
        //     ftp_close($conn_id);
        //     return;
        // }
        // echo $login;
        

        // $temp_file = tempnam(sys_get_temp_dir(), 'csv_');
        // if (ftp_get($conn_id, $temp_file, $ftp_file, FTP_BINARY)) {
        //     $csv_content = file_get_contents($temp_file);
        //     $csv_lines = explode("\n", $csv_content);
        //     foreach ($csv_lines as $line) {
        //         $datos = str_getcsv($line);
        //         if (!empty($datos)) {
        //             // Procesar cada fila y crear publicaciones de inmuebles
        //             // Aquí debes ajustar el procesamiento según la estructura de tu CSV
        //         }
        //     }
        //     unlink($temp_file);
        // }

        // ftp_close($conn_id);
    }

}