<?php

function modificar_tipo_mime_lxx($mimes) {
    for ($i = 1; $i <= 99; $i++) {
        $extension = sprintf('l%02d', $i); 
        $mimes[$extension] = 'image/jpeg'; 
    }
    return $mimes;
}
add_filter('upload_mimes', 'modificar_tipo_mime_lxx');