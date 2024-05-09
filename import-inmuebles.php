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
// Includes 
define( 'DIR_NAME_TEMP', plugin_basename( 'data/temp' ) );//Pasar a la clase correspondiente

require IMPORTMLS_DIR . 'includes/load_scripts.php';
require IMPORTMLS_DIR . 'includes/create_menu.php';
require IMPORTMLS_DIR . 'includes/set_mimes.php';

require IMPORTMLS_DIR . 'includes/ftp_config.php';


require IMPORTMLS_DIR . 'includes/file_manager.php';


function mi_plugin_importar_inmuebles(){
    ?>
    <div class="wrap">
        <h1>Importar Inmuebles</h1>
        <div class="mt-2">
            <button id="btnLeerCSV" class="btn btn-primary">Leer CSV</button>
            <div id="loteActual">Lote actual: </div>
            <div id="csvRows"></div>
        </div>
    </div>
    <?php
}




