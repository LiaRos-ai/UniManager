<?php
// public/login.php
declare(strict_types=1);
require_once '../config/session.php';  // Configuración segura de sesión
require_once '../vendor/autoload.php';

use UniManager\Auth\Auth;

iniciarSesionSegura();

$auth = new Auth();
$error = '';
$redirect = $_GET['redirect'] ?? '/unimanager/public/dashboard.php';

// Si ya está logueado, redirigir
if ($auth->estaAutenticado()) {
    header('Location: ' . $redirect);
    exit;
}

// Generar token CSRF si no existe
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

// Procesar formulario
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Verificar token CSRF
    if (!hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'] ?? '')) {
        $error = 'Token de seguridad inválido. Recarga la página.';
    } else {
        try {
            $email    = filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL);
            $password = $_POST['password'] ?? '';  // NO sanitizar passwords
            
            if (empty($email) || empty($password)) {
                throw new Exception('Por favor completa todos los campos');
            }
            
            $auth->login($email, $password);
            header('Location: ' . $redirect);
            exit;
        } catch (Exception $e) {
            $error = $e->getMessage();
            sleep(1); // Ralentizar para dificultar brute force
        }
    }
}
?>
<!DOCTYPE html>
<html lang='es'>
<head>
    <meta charset='UTF-8'>
    <title>Login - UniManager</title>
    <style>
    body { font-family: Arial, sans-serif; background: #f0f4f8;
           display: flex; justify-content: center; align-items: center; min-height: 100vh; }
    .card { background: white; padding: 40px; border-radius: 12px;
            box-shadow: 0 4px 20px rgba(0,0,0,0.15); width: 380px; }
    h2 { text-align: center; color: #1F4E79; margin-bottom: 24px; }
    label { display: block; margin-bottom: 6px; font-weight: bold; color: #333; }
    input { width: 100%; padding: 10px; border: 2px solid #ddd;
            border-radius: 6px; font-size: 14px; box-sizing: border-box; }
    input:focus { border-color: #2E75B6; outline: none; }
    .btn { width: 100%; padding: 12px; background: #1F4E79; color: white;
           border: none; border-radius: 6px; font-size: 16px; cursor: pointer; }
    .btn:hover { background: #2E75B6; }
    .error { background: #fdecea; color: #c00000; padding: 10px;
             border-radius: 6px; margin-bottom: 16px; }
    .campo { margin-bottom: 16px; }
    </style>
</head>
<body>
    <div class='card'>
        <h2>🎓 UniManager</h2>
        
        <?php if ($error): ?>
        <div class='error'>⚠️ <?= htmlspecialchars($error) ?></div>
        <?php endif; ?>
        
        <form method='POST' action='login.php'>
            <!-- Token CSRF oculto -->
            <input type='hidden' name='csrf_token'
                   value='<?= htmlspecialchars($_SESSION['csrf_token']) ?>'>
            
            <div class='campo'>
                <label for='email'>📧 Email institucional:</label>
                <input type='email' id='email' name='email' required
                       placeholder='usuario@unimanager.edu'
                       value='<?= htmlspecialchars($_POST['email'] ?? '') ?>'>
            </div>
            
            <div class='campo'>
                <label for='password'>🔒 Contraseña:</label>
                <input type='password' id='password' name='password' required>
            </div>
            
            <button type='submit' class='btn'>Iniciar Sesión</button>
        </form>
        <p style='text-align:center; margin-top:16px;'>
            ¿No tienes cuenta? <a href='registro.php'>Regístrate aquí</a>
        </p>
    </div>
</body>
</html>