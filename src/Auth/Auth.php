<?php
// src/Auth/Auth.php
declare(strict_types=1);

namespace UniManager\Auth;

use App\Database\Database;
use PDOException;
use Exception;

class Auth {
    private Database $db;
    private const MAX_INTENTOS = 5;
    private const BLOQUEO_MINUTOS = 15;
    
    public function __construct() {
        $this->db = Database::getInstance();
    }
    
    // ===================================
    // REGISTRO DE NUEVO USUARIO
    // ===================================
    public function registrar(string $nombre, string $apellido,
                              string $email, string $password): bool {
        // 1. Validar datos
        $this->validarDatosRegistro($nombre, $apellido, $email, $password);
        
        // 2. Verificar que el email no existe
        if ($this->emailExiste($email)) {
            throw new Exception('Este email ya está registrado');
        }
        
        // 3. Hashear contraseña (bcrypt, cost 12)
        $passwordHash = password_hash($password, PASSWORD_BCRYPT, ['cost' => 12]);
        
        // 4. Guardar en BD
        $id = $this->db->insert('usuarios', [
            'nombre'   => trim($nombre),
            'apellido' => trim($apellido),
            'email'    => strtolower(trim($email)),
            'password' => $passwordHash,
            'rol_id'   => 2  // estudiante por defecto
        ]);
        
        return $id > 0;
    }
    
    // ===================================
    // INICIO DE SESIÓN
    // ===================================
    public function login(string $email, string $password): array {
        $email = strtolower(trim($email));
        
        // 1. Buscar usuario
        $usuario = $this->db->query(
            'SELECT u.*, r.nombre as rol_nombre FROM usuarios u
             JOIN roles r ON u.rol_id = r.id
             WHERE u.email = :email',
            ['email' => $email]
        )->fetch();
        
        // 2. Verificar si existe (siempre procesamos el hash para evitar timing attacks)
        $hashFalso = '$2y$12$abcdefghijklmnop.xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx';
        $hashAVerificar = $usuario ? $usuario['password'] : $hashFalso;
        $passwordCorrecta = password_verify($password, $hashAVerificar);
        
        if (!$usuario || !$passwordCorrecta) {
            if ($usuario) {
                $this->registrarIntentoFallido($usuario['id']);
            }
            throw new Exception('Email o contraseña incorrectos');
        }
        
        // 3. Verificar si está activo
        if (!$usuario['activo']) {
            throw new Exception('Tu cuenta está desactivada. Contacta al administrador.');
        }
        
        // 4. Verificar si necesita actualizar hash
        if (password_needs_rehash($usuario['password'], PASSWORD_BCRYPT, ['cost' => 12])) {
            $nuevoHash = password_hash($password, PASSWORD_BCRYPT, ['cost' => 12]);
            $this->db->update('usuarios', ['password' => $nuevoHash], 'id = ?', [$usuario['id']]);
        }
        
        // 5. Crear sesión segura
        session_regenerate_id(true);  // Previene Session Fixation
        $_SESSION['user_id']     = $usuario['id'];
        $_SESSION['user_nombre'] = $usuario['nombre'] . ' ' . $usuario['apellido'];
        $_SESSION['user_email']  = $usuario['email'];
        $_SESSION['user_rol']    = $usuario['rol_nombre'];
        $_SESSION['login_time']  = time();
        
        // 6. Actualizar último login y resetear intentos
        $this->db->update('usuarios',
            ['ultimo_login' => date('Y-m-d H:i:s'), 'intentos_login' => 0],
            'id = ?', [$usuario['id']]
        );
        
        return $usuario;
    }
    
    // ===================================
    // CERRAR SESIÓN
    // ===================================
    public function logout(): void {
        $_SESSION = [];  // Limpiar todos los datos
        
        // Eliminar cookie de sesión
        if (ini_get('session.use_cookies')) {
            $params = session_get_cookie_params();
            setcookie(session_name(), '', time() - 42000,
                $params['path'], $params['domain'],
                $params['secure'], $params['httponly']
            );
        }
        
        session_destroy();
    }
    
    // ===================================
    // VERIFICAR AUTENTICACIÓN
    // ===================================
    public function estaAutenticado(): bool {
        return !empty($_SESSION['user_id']);
    }
    
    public function requiereAuth(): void {
        if (!$this->estaAutenticado()) {
            header('Location: /login.php?redirect=' . urlencode($_SERVER['REQUEST_URI']));
            exit;
        }
    }
    
    public function requiereRol(string ...$roles): void {
        $this->requiereAuth();
        if (!in_array($_SESSION['user_rol'], $roles)) {
            http_response_code(403);
            include 'views/errors/403.php';
            exit;
        }
    }
    
    // ===================================
    // MÉTODOS PRIVADOS
    // ===================================
    private function validarDatosRegistro(string $nombre, string $apellido,
                                           string $email, string $password): void {
        if (strlen(trim($nombre)) < 2) throw new Exception('Nombre muy corto');
        if (strlen(trim($apellido)) < 2) throw new Exception('Apellido muy corto');
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) throw new Exception('Email inválido');
        if (strlen($password) < 8) throw new Exception('La contraseña debe tener mínimo 8 caracteres');
        if (!preg_match('/[A-Z]/', $password)) throw new Exception('La contraseña debe tener al menos una mayúscula');
        if (!preg_match('/[0-9]/', $password)) throw new Exception('La contraseña debe tener al menos un número');
    }
    
    private function emailExiste(string $email): bool {
        $resultado = $this->db->query(
            'SELECT id FROM usuarios WHERE email = :email',
            ['email' => $email]
        )->fetch();
        return $resultado !== false;
    }
    
    private function registrarIntentoFallido(int $userId): void {
        $this->db->execute(
            'UPDATE usuarios SET intentos_login = intentos_login + 1 WHERE id = ?',
            [$userId]
        );
    }
}