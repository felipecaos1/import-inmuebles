<?php

//Show
function mi_plugin_inmuebles_pagina_config() {
    $ftp_host = get_option('ftp_host');
    $ftp_user = get_option('ftp_user');
    $ftp_pass = get_option('ftp_pass');
    $ftp_path = get_option('ftp_path');
    $local_path = get_option('local_path');

    ?>
    <div class="wrap">
        <h1>Credenciales FTP</h1>
        <form method="post" action="">
            <table class="form-table">
                <tbody>
                    <tr>    
                        <th scope="row">
                            <label for="blogname">Host FTP:</label>
                        </th>
                        <td>
                            <input type="text" id="ftp_host" name="ftp_host" value="<?php echo esc_attr($ftp_host); ?>" />
                        </td>
                    </tr>
                    <tr>    
                        <th scope="row">
                            <label for="blogname">Usuario FTP:</label>
                        </th>
                        <td>
                            <input type="text" id="ftp_user" name="ftp_user" value="<?php echo esc_attr($ftp_user); ?>" />
                        </td>
                    </tr>
                    <tr>    
                        <th scope="row">
                            <label for="blogname">Contraseña FTP:</label>
                        </th>
                        <td>
                            <input type="password" id="ftp_pass" name="ftp_pass" value="<?php echo esc_attr($ftp_pass); ?>" />
                        </td>
                    </tr>
                    <tr>    
                        <th scope="row">
                            <label for="blogname">Directorio:</label>
                        </th>
                        <td>
                            <input type="text" id="ftp_path" name="ftp_path" value="<?php echo esc_attr($ftp_path); ?>" />
                        </td>
                    </tr>
                </tbody>
            </table>
            <p class="submit">
                <input type="submit" name="guardar_credenciales" id="submit" class="button button-primary" value="Guardar Credenciales">
            </p>
        </form>
        <form method="POST">
            <input type="submit" name="import-file" class="button button-primary" value="Importar Archivos">
        </form>
    </div>
    <?php
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
        downloadFile('/res20231016.csv','data/csv');
    }
}
add_action('admin_init', 'guardar_credenciales_ftp');

