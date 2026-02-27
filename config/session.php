<?php
/**
 * config/session.php
 * Configuración segura de sesiones
 */

/**
 * Inicia sesión de manera segura
 */
function iniciarSesionSegura(): void {
    // Si ya está iniciada, no hacer nada
    if (session_status() === PHP_SESSION_ACTIVE) {
        return;
    }
    
    // Configurar parámetros de seguridad de sesión
    session_set_cookie_params([
        'lifetime' => 3600,      // 1 hora
        'path'     => '/',
        'domain'   => '',
        'secure'   => true,      // Solo HTTPS (cambiar a false si no tienes HTTPS)
        'httponly' => true,      // No accesible desde JavaScript
        'samesite' => 'Strict'   // CSRF protection
    ]);
    
    // Iniciar sesión
    session_start();
    
    // Regenerar ID después de X segundos o mediante login/cambio de rol
    if (!isset($_SESSION['inicio_sesion'])) {
        $_SESSION['inicio_sesion'] = time();
        session_regenerate_id(true);
    } elseif (time() - $_SESSION['inicio_sesion'] > 1800) {  // 30 minutos
        session_regenerate_id(true);
        $_SESSION['inicio_sesion'] = time();
    }
}

/**
 * Verifica si user está autenticado
 */
function estaAutenticado(): bool {
    return !empty($_SESSION['user_id']);
}

/**
 * Requiere autenticación - redirige a login si no está autenticado
 */
function requiereAutenticacion(): void {
    if (!estaAutenticado()) {
        header('Location: /unimanager/public/login.php?redirect=' . urlencode($_SERVER['REQUEST_URI']));
        exit;
    }
}

/**
 * Obtiene datos del usuario actual
 */
function obtenerUsuarioActual(): ?array {
    if (!estaAutenticado()) {
        return null;
    }
    
    return [
        'id'     => $_SESSION['user_id'],
        'nombre' => $_SESSION['user_nombre'],
        'email'  => $_SESSION['user_email'],
        'rol'    => $_SESSION['user_rol']
    ];
}

/**
 * Verifica si el usuario tiene un rol específico
 */
function tieneRol(string ...$roles): bool {
    if (!estaAutenticado()) {
        return false;
    }
    return in_array($_SESSION['user_rol'], $roles);
}
