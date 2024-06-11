<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

$wp_directory = dirname(__FILE__, 5);
// $parent_directory = dirname(__FILE__, 2);

require_once $wp_directory . '/wp-load.php';


// Verificar si se proporciona el parámetro 'batch_zip'
if (isset($argv[1]) && strpos($argv[1], 'batch_zip=') === 0) {
    $batch = substr($argv[1], strlen('batch_zip=')); // Extraer el valor del parámetro
    
    if ($batch != '' && is_numeric($batch)) {
        $file_manager = new FileManager();
        $file_manager->load_all_zip($batch);
        exit;
    }
}

// Verificar si se proporciona el parámetro 'import'
if (isset($argv[1]) && strpos($argv[1], 'import=') === 0) {
    // Extraer el valor del parámetro 'import'
    $importParams = explode('&', $argv[1]);
    $import_type = substr($importParams[0], strlen('import='));
    $date = isset($importParams[1]) ? substr($importParams[1], strlen('date=')) : null;
    
    if ($import_type != '') {
        $file_manager = new FileManager();
        $file_manager->import($import_type, $date);
        exit;
    }
}
?>