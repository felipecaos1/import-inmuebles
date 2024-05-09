<?php
// conexion ftp
function my_ftp_connect(){
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
function import_data($date = null)
{
    date_default_timezone_set('America/Bogota');
    set_time_limit(600);

    if($date == null){
        $date = date('Ymd');
    }

    $residentialFile = "/res{$date}.csv";
    $commercialFile = "/com{$date}.csv";
    $zip = "/photo{$date}.zip";

    // downloadFile($residentialFile,DIR_NAME_TEMP);
    // downloadFile($commercialFile,DIR_NAME_TEMP);
    // downloadFile($zip,DIR_NAME_TEMP);

    leer_csv_ajax_handler(DIR_NAME_TEMP.'/'.$residentialFile); // data/csv/res20231016.csv 'data/temp'

    // if ($this->downloadFile($residentialFile) && $this->importFile($residentialFile, 'residential')) {
    //     $this->deleteFile($residentialFile);
    // }

    // if ($this->downloadFile($commercialFile) && $this->importFile($commercialFile, 'commercial')) {
    //     $this->deleteFile($commercialFile);
    // }

    // if ($this->downloadFile($zip) && $this->importFile($zip, 'zip')) {
    //     $this->deleteFile($zip);
    // }

    echo json_encode([$residentialFile,$commercialFile,$zip]);
    exit;

}

function downloadFile($nameFile, $path) {
    $response = false;
    $ftp = my_ftp_connect();
    if ($ftp) {
        // Verifica si el archivo existe en el servidor FTP
        $files = ftp_nlist($ftp, '/');
        if (in_array($nameFile, $files)) {
            $destination_file = IMPORTMLS_DIR . $path . $nameFile;  // Asegúrate de que el directorio 'csv' existe o créalo.
            if (!file_exists(IMPORTMLS_DIR . $path)) {
                mkdir(IMPORTMLS_DIR . $path , 0777, true); // Crea el directorio si no existe.
            }
            if (ftp_get($ftp, $destination_file, $nameFile, FTP_BINARY)) {
                echo "Archivo descargado con éxito: " . $destination_file;                
                $response = true;

                // Verifica si el archivo es un zip antes de intentar descomprimir
                if (pathinfo($destination_file, PATHINFO_EXTENSION) === 'zip') {
                    unzipFile($destination_file, IMPORTMLS_DIR . $path);
                } else {
                    echo "El archivo descargado no es un archivo comprimido.";
                }

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

// Descomprimir archivo 
function unzipFile($filePath, $extractTo) {
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

// Función para procesar el archivo CSV desde FTP
function mi_plugin_inmuebles_procesar_csv_desde_ftp() {
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
