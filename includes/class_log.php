<?php

/**
 * Clase para gestionar logs.
 */
class Log
{
    // Niveles de registro
    const LEVEL_ERROR = 'error';
    const LEVEL_INFO = 'info';
    const LEVEL_DEBUG = 'debug';

    // Ruta al archivo de logs
    const LOG_FILE = IMPORTMLS_DIR . LOG_FILE;
    
     /**
     * Registra un mensaje de log con un nivel específico.
     *
     * @param string $level El nivel del log (error, info, debug, etc.).
     * @param string $message El mensaje a registrar.
     * @param array $context El contexto asociado con el mensaje (opcional).
     * @return void
     */
    public static function log($level, $message, $context = [])
    {
        self::ensure_log_file_exists();
        $timestamp = date('Y-m-d H:i:s');
        $logString = "[$timestamp] [$level]: $message";
        if (!empty($context)) {
            $logString .= ' ' . json_encode($context);
        }
        
        // Guardamos el registro en el archivo de logs
        file_put_contents(self::LOG_FILE, $logString . PHP_EOL, FILE_APPEND);
    }

    /**
     * Registra un mensaje de error.
     *
     * @param string $message El mensaje de error.
     * @param array $context El contexto asociado con el mensaje (opcional).
     * @return void
     */
    public static function error($message, $context = [])
    {
        self::log(self::LEVEL_ERROR, $message, $context);
    }

    /**
     * Registra un mensaje de información.
     *
     * @param string $message El mensaje informativo.
     * @param array $context El contexto asociado con el mensaje (opcional).
     * @return void
     */
    public static function info($message, $context = [])
    {
        self::log(self::LEVEL_INFO, $message, $context);
    }

    /**
     * Registra un mensaje de debug.
     *
     * @param string $message El mensaje de debug.
     * @param array $context El contexto asociado con el mensaje (opcional).
     * @return void
     */
    public static function debug($message, $context = [])
    {
        self::log(self::LEVEL_DEBUG, $message, $context);
    }

    /**
     * Asegura que el archivo de logs exista.
     *
     * Verifica si el archivo de logs existe en la ruta especificada.
     * Si no existe, crea el archivo para que esté listo para recibir registros.
     *
     * @return void
     */
    private static function ensure_log_file_exists()
    {
        // Verificar si el archivo de logs existe, sino crearlo
        if (!file_exists(self::LOG_FILE)) {
            $log = fopen(LOG_FILE, 'w');
            fclose($log);
        }
    }
}