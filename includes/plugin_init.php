<?php

require IMPORTMLS_DIR . 'includes/load_scripts.php';
require IMPORTMLS_DIR . 'includes/create_menu.php';
require IMPORTMLS_DIR . 'includes/set_mimes.php';
require IMPORTMLS_DIR . 'includes/class_log.php';

require IMPORTMLS_DIR . 'includes/class_file_manager.php';

date_default_timezone_set('America/Bogota');

function mi_plugin_importar_inmuebles()
{
    load_view('importar_inmuebles');
}

/**
 * Obtiene la fecha actual formateada en español.
 * Utiliza el formato '%A, %d de %B del %Y' por defecto.
 * @return string La fecha actual formateada.
 */
function current_date() 
{
    // Define el formato de fecha
    $formato = '%A, %d de %B del %Y';

    // Obtiene la marca de tiempo actual
    $timestamp = time();

    // Nombres de los meses en español
    $meses_espanol = array(
        'enero', 'febrero', 'marzo', 'abril', 'mayo', 'junio',
        'julio', 'agosto', 'septiembre', 'octubre', 'noviembre', 'diciembre'
    );

    // Nombres de los días de la semana en español
    $dias_espanol = array(
        'Domingo', 'Lunes', 'Martes', 'Miércoles', 'Jueves', 'Viernes', 'Sábado'
    );

    // Reemplaza los marcadores de posición con los nombres de los meses y días en español
    $formato = str_replace('%B', $meses_espanol[date('n', $timestamp) - 1], $formato);
    $formato = str_replace('%A', $dias_espanol[date('w', $timestamp)], $formato);

    // Formatea la fecha utilizando strftime
    $result = strftime($formato, $timestamp);
    // Establece el locale a español
    setlocale(LC_TIME, 'es_ES.UTF-8');

    return $result;
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

    setlocale(LC_TIME, 'es_ES.UTF-8');
    setlocale(LC_TIME, 'es_ES.UTF-8');
    setlocale(LC_TIME, 'es_ES.UTF-8');
    setlocale(LC_TIME, 'es_ES.UTF-8');

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

    wp_die("<pre>" . json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) . "</pre>");
}

/**
 * Procesa la URL para ejecutar una función específica basada en el parámetro 'batch_zip'.
 *
 * Esta función verifica si el parámetro 'batch_zip' está presente en la URL. Si el parámetro existe y 
 * su valor es un número válido, se crea una instancia de la clase FileManager y se llama al método 
 * load_all_zip con el valor del lote. Si el lote no es válido, se muestra un mensaje de error.
 * Url : http://tusitio.com/?batch_zip=1
 * Url : http://tusitio.com/?import=true
 * @return void
 */
function custom_plugin_process_url() 
{
    if (isset($_GET['batch_zip'])) {
        $batch = $_GET['batch_zip'];
        if ($batch != '' && is_numeric($batch)) {
            $file_manager = new FileManager();
            $file_manager->load_all_zip($batch);
            exit;
        }
        echo '<h1 style="color:red;">Ingresa un lote valido a ejecutar</h1>';
        exit;
    }
    
    if (isset($_GET['import'])) {
        if ($_GET['import']) {
            $file_manager = new FileManager();
            $file_manager->import();
            exit;
        }
    }
}

/**
 * Función se ejecuta al activar el plugin
 */
function import_inmuebles_activate() 
{
    Log::info("Plugin activando");

    $file_manager = new FileManager();
    $file_manager->assign_preview_image();
}

// Registrar la función de activación
register_activation_hook(IMPORTMLS_FILE, 'import_inmuebles_activate');

add_action('admin_init', 'guardar_credenciales_ftp');
add_action('init', 'custom_plugin_process_url');