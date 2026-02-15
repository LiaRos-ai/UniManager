<?php
declare(strict_types=1);

namespace App\Utils;

/**
 * Validador de datos del sistema
 */
class Validator {

    /**
     * Valida email
     */
    public static function email(?string $email): bool {
        return filter_var($email ?? '', FILTER_VALIDATE_EMAIL) !== false;
    }

    /**
     * Valida carnet de identidad boliviano
     */
    public static function ci(?string $ci): bool {
        $ci = trim($ci ?? '');
        return preg_match('/^\d{6,8}(-[A-Z]{2})?$/', $ci) === 1;
    }

    /**
     * Valida código de estudiante
     */
    public static function codigoEstudiante(?string $codigo): bool {
        // Formato: 2024-XXXXX
        $codigo = trim($codigo ?? '');
        return preg_match('/^\d{4}-\d{5}$/', $codigo) === 1;
    }

    /**
     * Valida rango de edad
     */
    public static function edad(?int $edad): bool {
        return $edad !== null && $edad >= 16 && $edad <= 100;
    }

    /**
     * Valida longitud de texto
     */
    public static function longitud(?string $texto, int $min, int $max): bool {
        $longitud = strlen($texto ?? '');
        return $longitud >= $min && $longitud <= $max;
    }

    /**
     * Valida teléfono boliviano
     */
    public static function telefono(?string $tel): bool {
        $tel = preg_replace('/\s+/', '', $tel ?? '');
        return preg_match('/^\+?591[67]\d{7}$/', $tel) === 1;
    }

    /**
     * Valida que no esté vacío
     */
    public static function requerido(mixed $valor): bool {
        if (is_string($valor)) {
            return trim($valor) !== '';
        }
        return !empty($valor);
    }
}
