<div class="wrap noti-none">
    <div class="row mx-0 py-2 rounded align-items-center" style="background-color:#013753">
        <div class="col">
            <h1 class="text-white fw-bold py-0">Configuraciones</h1>
        </div>
    </div>
    <div class="row mx-0 pt-4">
        <div class="col-md-6 border rounded p-3 shadow ">
            <form class="row g-3" method="post" action="">
                <div class="col-12">
                    <label for="ftp_host" class="form-label">Host FTP:</label>
                    <input class="form-control" type="text" id="ftp_host" name="ftp_host" value="<?php echo esc_attr($params->ftp_host); ?>" />
                </div>
                <div class="col-12">
                    <label for="ftp_user" class="form-label">Usuario FTP:</label>
                    <input class="form-control" type="text" id="ftp_user" name="ftp_user" value="<?php echo esc_attr($params->ftp_user); ?>" />
                </div>
                <div class="col-12">
                    <label for="ftp_pass" class="form-label">Contraseña FTP:</label>
                    <input class="form-control" type="password" id="ftp_pass" name="ftp_pass" value="<?php echo esc_attr($params->ftp_pass); ?>" />
                </div>
                <div class="col-12">
                    <label for="ftp_path" class="form-label">Directorio:</label>
                    <input class="form-control" type="text" id="ftp_path" name="ftp_path" value="<?php echo esc_attr($params->ftp_path); ?>" />
                </div>
                <div class="col-12">
                    <input type="submit" name="guardar_credenciales" id="submit" class="btn btn-primary" value="Guardar Credenciales">
                </div>
            </form>
        </div>
    </div>
</div>