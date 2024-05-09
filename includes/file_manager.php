<?php

require IMPORTMLS_DIR . 'includes/csv.php';
require IMPORTMLS_DIR . 'includes/inmueble_import.php';

class FileManager
{
    // conexion ftp
    private function my_ftp_connect()
    {
        $ftp_server = get_option('ftp_host');
        $ftp_user = get_option('ftp_user');
        $ftp_pass = get_option('ftp_pass');
        $ftp_file = get_option('ftp_path');

        if($ftp_server && $ftp_user && $ftp_pass && $ftp_file){
            $ftp = ftp_connect( $ftp_server );
            ftp_login( $ftp, $ftp_user, $ftp_pass );
            return $ftp;
        }else{
            return false;
        }
    }

    /**
     * Funcion para manejar la importacion de los csv res y com, y el zip photo
     */
    public function import($date = null)
    {        
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
        $this->import_file($residentialFile,'csv');
        $this->import_file($commercialFile,'csv');
        $this->import_file($zip,'zip');

        //Eliminar
        $this->delete_file($residentialFile);
        $this->delete_file($commercialFile);
        $this->delete_file($zip);

        echo json_encode([$residentialFile,$commercialFile,$zip]);
        exit;

    }

    private function download_file($name_file, $path) 
    {
        $response = false;
        $ftp = $this->my_ftp_connect();
        if ($ftp) {
            // Verifica si el archivo existe en el servidor FTP
            $files = ftp_nlist($ftp, '/');
            if (in_array($name_file, $files)) {
                $destination_file = IMPORTMLS_DIR . $path . $name_file;  // Asegúrate de que el directorio 'csv' existe o créalo.
                if (!file_exists(IMPORTMLS_DIR . $path)) {
                    mkdir(IMPORTMLS_DIR . $path , 0777, true); // Crea el directorio si no existe.
                }
                if (ftp_get($ftp, $destination_file, $name_file, FTP_BINARY)) {
                    echo "Archivo descargado con éxito: " . $destination_file;                
                    $response = true;

                    // Verifica si el archivo es un zip antes de intentar descomprimir
                    // if (pathinfo($destination_file, PATHINFO_EXTENSION) === 'zip') {
                    //     $this->unzipFile($destination_file, IMPORTMLS_DIR . $path);
                    // } else {
                    //     echo "El archivo descargado no es un archivo comprimido.";
                    // }

                } else {
                    echo "Error al descargar el archivo.";
                }
            } else {
                echo "Archivo no encontrado en el servidor FTP.";
            }
            ftp_close($ftp);
        } else {
            echo "Error al conectar al FTP.";
        }
        return $response;
    }

    private function import_file($name_file,$import_type)
    {
        if($import_type == 'csv'){
            Csv::import(new ImmovableImport(),DIR_NAME_TEMP.'/'.$name_file);
        }elseif($import_type == 'zip'){            
            $destination_file = IMPORTMLS_DIR .DIR_NAME_TEMP . $name_file;
            if (pathinfo($destination_file, PATHINFO_EXTENSION) === 'zip') {
                $this->unzipFile($destination_file, IMPORTMLS_DIR . DIR_NAME_TEMP);
            } else {
                echo "El archivo descargado no es un archivo comprimido.";
            }
        }
    }

    // Descomprimir archivo 
    private function unzipFile($filePath, $extractTo) 
    {
        $zip = new ZipArchive;
        $res = $zip->open($filePath);
        if ($res === TRUE) {
            $zip->extractTo($extractTo);
            $zip->close();
            echo "Archivo descomprimido con éxito.";
        } else {
            echo "Error al descomprimir el archivo.";
        }
    }

    private function delete_file($name_file)
    {
        $file_path = IMPORTMLS_DIR .DIR_NAME_TEMP . $name_file;
        if (file_exists($file_path)) {
            unlink($file_path);
            echo "Archivo eliminado con éxito.";
        } else {
            echo "El archivo no existe.";
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