<?php
/*
Plugin Name: Import Inmuebles
'Plugin URI: https://tusitio.com/plugin
Description: Realice la importación de inmuebles de la MLS de una forma sencilla.
Version: 1.0
Author: Caos-Darsof't
'Author URI: https://tusitio.com
License: GPLv2 or later
Text Domain: importM
*/

if ( ! defined( 'ABSPATH' ) ) {
    exit; 
}


define( 'IMPORTMLS_FILE', __FILE__ );
define( 'IMPORTMLS_DIR', plugin_dir_path( IMPORTMLS_FILE ) );
define('PLUGIN_BASE_URL', plugins_url('/', IMPORTMLS_FILE));
define( 'IMPORTMLS_BASENAME', plugin_basename( IMPORTMLS_FILE ) );
define( 'TABLE_NAME', 'import_inmuebles_logs' );
define( 'LOG_FILE', 'import-inmubles.log');
// Define la ruta del directorio de archivos temporales del plugin
define( 'DIR_NAME_TEMP', plugin_basename( 'data/temp' ) );

/**
 * Incluye el archivo que contiene las funciones principales o iniciales del pugin
 */
require IMPORTMLS_DIR . 'includes/plugin_init.php';



