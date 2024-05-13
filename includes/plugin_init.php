<?php

require IMPORTMLS_DIR . 'includes/load_scripts.php';
require IMPORTMLS_DIR . 'includes/create_menu.php';
require IMPORTMLS_DIR . 'includes/set_mimes.php';

require IMPORTMLS_DIR . 'includes/class_file_manager.php';

function mi_plugin_importar_inmuebles()
{
    load_view('importar_inmuebles');
}

/**
 * Genera la página de configuración del plugin "Import Inmuebles", cargando la vista 'credenciales_ftp'.
 * La función obtiene las opciones de FTP almacenadas en la base de datos para pasarlas como parámetros a la vista.
 */
function mi_plugin_inmuebles_pagina_config() 
{
    $params = array(
        'ftp_host' => get_option('ftp_host'),
        'ftp_user' => get_option('ftp_user'),
        'ftp_pass' => get_option('ftp_pass'),
        'ftp_path' => get_option('ftp_path'),
        'local_path' => get_option('local_path')
    );
    
    load_view('credenciales_ftp', $params);
}

/**
 * Guarda las credenciales FTP ingresadas por el usuario en la página de configuración del plugin.
 * También inicia el proceso de importación de archivos si se activa la opción 'import-file'.
 */
function guardar_credenciales_ftp() 
{
    if (isset($_POST['guardar_credenciales'])) {
        $ftp_host = sanitize_text_field($_POST['ftp_host']);
        $ftp_user = sanitize_text_field($_POST['ftp_user']);
        $ftp_pass = sanitize_text_field($_POST['ftp_pass']);
        $ftp_path = sanitize_text_field($_POST['ftp_path']);

        update_option('ftp_host', $ftp_host);
        update_option('ftp_user', $ftp_user);
        update_option('ftp_pass', $ftp_pass);
        update_option('ftp_path', $ftp_path);

        echo '<div class="notice notice-success"><p>Credenciales FTP guardadas correctamente.</p></div>';
    }

    if(isset($_POST['import-file'])){
        $import_files = new FileManager();
        $import_files->import();
        exit;
    }
}

/**
 * Carga una vista desde un archivo PHP en un directorio específico
 * y le pasa un objeto como parámetro.
 *
 * @param string $view   Nombre del archivo de la vista (sin la extensión .php).
 * @param array  $params Parámetros que se convierten en un objeto para pasar a la vista (No es obligatorio).
 */
function load_view($view,$params = null)
{
    $params = $params != null ? (object)$params : $params;
    require IMPORTMLS_DIR . 'views/'.$view.'.php';
}

/**
 * Muestra los datos proporcionados en formato JSON de forma legible y termina la ejecución del script.
 *
 * @param mixed $var El dato o la variable a mostrar en formato JSON.
 * @return array Vacío ya que la ejecución se detiene después de mostrar el JSON.
 */
function dump_json(...$vars): void 
{
    $data = [];
    foreach ($vars as $var) {
        $data[] = $var;
    }

    echo "<pre>" . json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) . "</pre>";
    exit;
}

add_action('admin_init', 'guardar_credenciales_ftp');

