<?php
declare(strict_types=1);

namespace App\Utils;

/**
 * Funciones de utilidad del sistema
 */
class Helpers {
    
    /**
     * Genera código de estudiante
     */
    public static function generarCodigoEstudiante(int $numero): string {
        $anio = (int)date('Y');
        return sprintf('%04d-%05d', $anio, $numero);
    }
    
    /**
     * Formatea nombre completo
     */
    public static function formatearNombre(
        string $nombre,
        string $apellidoPaterno,
        string $apellidoMaterno
    ): string {
        return mb_convert_case(
            "$nombre $apellidoPaterno $apellidoMaterno",
            MB_CASE_TITLE,
            'UTF-8'
        );
    }
    
    /**
     * Genera iniciales
     */
    public static function generarIniciales(string $nombreCompleto): string {
        $palabras = explode(' ', $nombreCompleto);
        $iniciales = array_map(fn($p) => mb_substr($p, 0, 1), $palabras);
        return mb_strtoupper(implode('', $iniciales), 'UTF-8');
    }
    
    /**
     * Formatea teléfono
     */
    public static function formatearTelefono(?string $telefono): string {
        if ($telefono === null) {
            return 'N/A';
        }
        
        $limpio = preg_replace('/\D/', '', $telefono);
        
        if (strlen($limpio) === 11 && str_starts_with($limpio, '591')) {
            return sprintf('+591 %s %s %s', 
                substr($limpio, 3, 2),
                substr($limpio, 5, 3),
                substr($limpio, 8, 3)
            );
        }
        
        return $telefono;
    }
    
    /**
     * Trunca texto
     */
    public static function truncar(
        string $texto,
        int $longitud = 100,
        string $sufijo = '...'
    ): string {
        if (mb_strlen($texto) <= $longitud) {
            return $texto;
        }
        
        return mb_substr($texto, 0, $longitud - mb_strlen($sufijo)) . $sufijo;
    }
    
    /**
     * Sanitiza texto para HTML
     */
    public static function sanitizar(string $texto): string {
        return htmlspecialchars(trim($texto), ENT_QUOTES, 'UTF-8');
    }
}