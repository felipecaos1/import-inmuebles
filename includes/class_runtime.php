<?php 

class Runtime
{
    // Ruta al archivo runtime.dat
    const RUNTIME_FILE = IMPORTMLS_DIR . 'runtime.dat';

    /**
     * Obtiene el valor correspondiente a una clave del archivo runtime.dat.
     *
     * @param string $runtime_key La clave cuyo valor se desea obtener.
     * @return mixed El valor correspondiente a la clave especificada, o null si la clave no existe.
     */
    public static function get_runtime($runtime_key)
    {
        self::ensure_runtime_file_exists();

        $runtime_content = file_get_contents(self::RUNTIME_FILE);
        $runtime_vars = [];
        foreach (explode("\n", $runtime_content) as $line) {
            // Ignorar líneas vacías o comentarios
            if (!empty($line) && strpos($line, '=') !== false) {
                list($key, $value) = explode('=', $line);
                $runtime_vars[trim($key)] = trim($value);
            }
        }
        return isset($runtime_vars[$runtime_key]) ? $runtime_vars[$runtime_key] : null;
    }

    /**
     * Establece el valor de una clave en el archivo runtime.dat.
     *
     * @param string $runtime_key La clave cuyo valor se desea establecer.
     * @param mixed $new_value El nuevo valor para la clave especificada.
     * @return void
     */
    public static function set_runtime($runtime_key, $new_value)
    {
        $runtime_content = file_get_contents(self::RUNTIME_FILE);
        $runtime_vars = [];
        foreach (explode("\n", $runtime_content) as $line) {
            // Ignorar líneas vacías o comentarios
            if (!empty($line) && strpos($line, '=') !== false) {
                list($key, $value) = explode('=', $line);
                $runtime_vars[trim($key)] = trim($value);
            }
        }
        $runtime_vars[$runtime_key] = $new_value;
        // Reconstruir el contenido del archivo
        $new_runtime_content = '';
        foreach ($runtime_vars as $key => $value) {
            $new_runtime_content .= "$key=$value\n";
        }
        // Escribir el nuevo contenido en el archivo
        file_put_contents(self::RUNTIME_FILE, $new_runtime_content);
    }

    /**
     * Asegura que el archivo runtime.dat exista.
     *
     * Verifica si el archivo runtime.dat existe en la ruta especificada.
     * Si no existe, crea el archivo para que esté listo para recibir registros.
     *
     * @return void
     */
    private static function ensure_runtime_file_exists()
    {
        // Verificar si el archivo de logs existe, sino crearlo
        if (!file_exists(self::RUNTIME_FILE)) {
            $log = fopen(self::RUNTIME_FILE, 'w');
            fclose($log);
        }
    }
}

?>