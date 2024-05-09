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
                        <input type="text" id="ftp_host" name="ftp_host" value="<?php echo esc_attr($params->ftp_host); ?>" />
                    </td>
                </tr>
                <tr>    
                    <th scope="row">
                        <label for="blogname">Usuario FTP:</label>
                    </th>
                    <td>
                        <input type="text" id="ftp_user" name="ftp_user" value="<?php echo esc_attr($params->ftp_user); ?>" />
                    </td>
                </tr>
                <tr>    
                    <th scope="row">
                        <label for="blogname">Contraseña FTP:</label>
                    </th>
                    <td>
                        <input type="password" id="ftp_pass" name="ftp_pass" value="<?php echo esc_attr($params->ftp_pass); ?>" />
                    </td>
                </tr>
                <tr>    
                    <th scope="row">
                        <label for="blogname">Directorio:</label>
                    </th>
                    <td>
                        <input type="text" id="ftp_path" name="ftp_path" value="<?php echo esc_attr($params->ftp_path); ?>" />
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