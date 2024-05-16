<?php

function mi_plugin_inmuebles_menu() {
    add_menu_page(
        'Inmuebles MSL',
        'Inmuebles MLS',
        'manage_options',
        'inuebles-mls',
        'mi_plugin_importar_inmuebles',
        'dashicons-building',
        30
    );
     // Submenú
    add_submenu_page(
        'inuebles-mls', // ID del menú padre
        'Configuraciones',
        'Configuraciones',
        'manage_options',
        'configuraciones',
        'mi_plugin_inmuebles_pagina_config'
    );
}
add_action('admin_menu', 'mi_plugin_inmuebles_menu');

