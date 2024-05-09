<?php

//Show
function mi_plugin_inmuebles_pagina_config() {
    $params = array(
        'ftp_host' => get_option('ftp_host'),
        'ftp_user' => get_option('ftp_user'),
        'ftp_pass' => get_option('ftp_pass'),
        'ftp_path' => get_option('ftp_path'),
        'local_path' => get_option('local_path')
    );
    
    load_view('credenciales_ftp', $params);
}


function guardar_credenciales_ftp() {
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
        echo 'Empezando importación <br>';
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

add_action('admin_init', 'guardar_credenciales_ftp');

