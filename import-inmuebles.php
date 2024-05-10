<?php
/*
Plugin Name: Import Inmuebles
Plugin URI: https://tusitio.com/plugin
Description: Consumir inmubles.
Version: 1.0
Author: Caos
Author URI: https://tusitio.com
License: GPLv2 or later
Text Domain: importM
*/

if ( ! defined( 'ABSPATH' ) ) {
    exit; 
}


define( 'IMPORTMLS_FILE', __FILE__ );
define( 'IMPORTMLS_DIR', plugin_dir_path( IMPORTMLS_FILE ) );
define( 'IMPORTMLS_BASENAME', plugin_basename( IMPORTMLS_FILE ) );
// Define la ruta del directorio de archivos temporales del plugin
define( 'DIR_NAME_TEMP', plugin_basename( 'data/temp' ) );

/**
 * Incluye el archivo que contiene las funciones principales o iniciales del pugin
 */
require IMPORTMLS_DIR . 'includes/plugin_init.php';
