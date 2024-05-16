<div class="wrap noti-none">
    <div class="row mx-0 py-3 rounded px-2 align-items-center" style="background-color:#013753">
        <div class="col-md-3">
            <img src="<?php echo PLUGIN_BASE_URL .'img/ogo-mls-white-1.png'; ?>" alt="MLS" class="img-fluid">
        </div>
        <div class="col-md-9 text-end">
            <h1 class="text-white fw-bold py-1">Importador de inmuebles de la MLS</h1>
        </div>
    </div>
</div>
<div class="wrap">
    <div class="card p-0">
        <div class="card-header">
            Estado de la importación
        </div>
        <div class="card-body">
            <h5 class="card-title mb-4"><?php echo current_date(); ?> 
                <?php 
                    $class='text-bg-warning';
                    $text = 'Algo no va bien';
                    $importar= true;
                    if(get_option('import_zip')&&get_option('import_zip')&&get_option('import_zip')){
                        $class='text-bg-success';
                        $text = 'Finalizada';
                        $importar = false;
                    }
                
                ?>
                <span class="badge rounded-pill text-white <?=$class?> fs-6 py-1 "><?=$text?></span>
            </h5>
            <p class="card-text"><?php echo get_option('import_zip') ? 'La importación del contenido multimedia se realizó de manera correcta.':'<strong>Error</strong> - La importación del contenido multimedia no finalizó de manera esperada.' ?></p>
            <p class="card-text"><?php echo get_option('import_res') ? 'La importación de los inmuebles residenciales se realizó de manera correcta.':'<strong>Error</strong> - La importación de inmubles residencial no finalizó de manera esperada.' ?></p>
            <p class="card-text"><?php echo get_option('import_com') ? 'La importación de los inmuebles comerciales se realizó de manera correcta.':'<strong>Error</strong> - La importación de inmubles comerciales no finalizó de manera esperada.' ?></p>
            <?php if($importar): ?>
                <form method="POST">
                    <input type="submit" name="import-file" class="btn btn-primary" value="Importar Manualmente">
                </form>
            <?php endif; ?>
        </div>
    </div>
</div>