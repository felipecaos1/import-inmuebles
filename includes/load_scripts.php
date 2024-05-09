<?php

function wptuts_scripts_basic() {
    // Use plugins_url() to get the correct URL for enqueuing scripts and styles
    wp_register_script('custom-script', plugins_url('js/custom-script.js', IMPORTMLS_FILE), array('jquery'), '1.0.0', true);
    wp_enqueue_script('custom-script');
    wp_register_style('custom-style', plugins_url('css/custom-style.css', IMPORTMLS_FILE), array(), '20120208', 'all');
    wp_enqueue_style('custom-style');
}
add_action('admin_enqueue_scripts', 'wptuts_scripts_basic');