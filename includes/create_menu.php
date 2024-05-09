<?php

function mi_plugin_inmuebles_menu() {
    add_menu_page(
        'Configuración de Inmuebles',
        'Importar Inmuebles',
        'manage_options',
        'importar-inmuebles',
        'mi_plugin_importar_inmuebles',
        'dashicons-building',
        30
    );
     // Submenú
    add_submenu_page(
        'importar-inmuebles', // ID del menú padre
        'Credenciales FTP',
        'Credenciales FTP',
        'manage_options',
        'credenciales-ftp',
        'mi_plugin_inmuebles_pagina_config'
    );
}
add_action('admin_menu', 'mi_plugin_inmuebles_menu');