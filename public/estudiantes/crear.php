<?php
declare(strict_types=1);

session_start();

require_once __DIR__ . '/../../config/constants.php';
require_once __DIR__ . '/../../vendor/autoload.php';

use App\Utils\Validator;
use App\Enums\StudentStatus;

// Generar token CSRF
if (!isset($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

$datos = [];
$errores = [];
$exito = false;

// Procesar formulario
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    
    // Verificar CSRF
    $tokenRecibido = $_POST['csrf_token'] ?? '';
    if (!hash_equals($_SESSION['csrf_token'], $tokenRecibido)) {
        die('Token CSRF inválido');
    }
    
    // Obtener y sanitizar datos
    $datos = [
        'nombre' => htmlspecialchars(trim($_POST['nombre'] ?? ''), ENT_QUOTES, 'UTF-8'),
        'apellido_paterno' => htmlspecialchars(trim($_POST['apellido_paterno'] ?? ''), ENT_QUOTES, 'UTF-8'),
        'apellido_materno' => htmlspecialchars(trim($_POST['apellido_materno'] ?? ''), ENT_QUOTES, 'UTF-8'),
        'email' => filter_var(trim($_POST['email'] ?? ''), FILTER_SANITIZE_EMAIL),
        'ci' => trim($_POST['ci'] ?? ''),
        'telefono' => trim($_POST['telefono'] ?? ''),
        'semestre' => (int)($_POST['semestre'] ?? 1),
        'estado' => $_POST['estado'] ?? StudentStatus::ACTIVE->value
    ];
    
    // Validar nombre
    if (!Validator::requerido($datos['nombre'])) {
        $errores['nombre'][] = "El nombre es requerido";
    } elseif (!Validator::longitud($datos['nombre'], 2, 50)) {
        $errores['nombre'][] = "El nombre debe tener entre 2 y 50 caracteres";
    }
    
    // Validar apellido paterno
    if (!Validator::requerido($datos['apellido_paterno'])) {
        $errores['apellido_paterno'][] = "El apellido paterno es requerido";
    } elseif (!Validator::longitud($datos['apellido_paterno'], 2, 50)) {
        $errores['apellido_paterno'][] = "El apellido paterno debe tener entre 2 y 50 caracteres";
    }
    
    // Validar apellido materno
    if (!Validator::requerido($datos['apellido_materno'])) {
        $errores['apellido_materno'][] = "El apellido materno es requerido";
    } elseif (!Validator::longitud($datos['apellido_materno'], 2, 50)) {
        $errores['apellido_materno'][] = "El apellido materno debe tener entre 2 y 50 caracteres";
    }
    
    // Validar email
    if (!Validator::requerido($datos['email'])) {
        $errores['email'][] = "El email es requerido";
    } elseif (!Validator::email($datos['email'])) {
        $errores['email'][] = "El email no es válido";
    }
    
    // Validar CI
    if (!Validator::requerido($datos['ci'])) {
        $errores['ci'][] = "El CI es requerido";
    } elseif (!Validator::ci($datos['ci'])) {
        $errores['ci'][] = "El CI no es válido (formato: 12345678-LP)";
    }
    
    // Validar teléfono (opcional)
    if (Validator::requerido($datos['telefono']) && !Validator::telefono($datos['telefono'])) {
        $errores['telefono'][] = "El teléfono no es válido (formato: +591 7XXXXXXX)";
    }
    
    // Validar semestre
    if ($datos['semestre'] < 1 || $datos['semestre'] > 10) {
        $errores['semestre'][] = "El semestre debe estar entre 1 y 10";
    }
    
    // Si no hay errores, procesar
    if (empty($errores)) {
        // Aquí normalmente guardarías en la base de datos
        // Por ahora simulamos el éxito
        $exito = true;
        
        // Regenerar token
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registrar Estudiante - <?= APP_NAME ?></title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            background: #f5f5f5;
            padding: 20px;
        }

        .container {
            max-width: 800px;
            margin: 0 auto;
            background: white;
            border-radius: 15px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            overflow: hidden;
        }

        .header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 30px;
        }

        .header h1 {
            font-size: 1.8em;
            margin-bottom: 5px;
        }

        .content {
            padding: 30px;
        }

        .form-group {
            margin-bottom: 20px;
        }

        label {
            display: block;
            margin-bottom: 5px;
            font-weight: 500;
            color: #333;
        }

        .required {
            color: #dc3545;
        }

        input,
        select {
            width: 100%;
            padding: 10px;
            border: 2px solid #e0e0e0;
            border-radius: 8px;
            font-size: 14px;
            transition: border-color 0.3s;
        }

        input:focus,
        select:focus {
            outline: none;
            border-color: #667eea;
        }

        .has-error input,
        .has-error select {
            border-color: #dc3545;
        }

        .error {
            color: #dc3545;
            font-size: 0.85em;
            margin-top: 5px;
        }

        .form-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 20px;
        }

        @media (max-width: 600px) {
            .form-grid {
                grid-template-columns: 1fr;
            }
        }

        .btn {
            background: #667eea;
            color: white;
            padding: 12px 30px;
            border: none;
            border-radius: 8px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: background 0.3s;
        }

        .btn:hover {
            background: #5568d3;
        }

        .btn-secondary {
            background: #6c757d;
            margin-right: 10px;
        }

        .btn-secondary:hover {
            background: #5a6268;
        }

        .success-message {
            background: #d4edda;
            color: #155724;
            padding: 20px;
            border-radius: 8px;
            margin-bottom: 20px;
            border: 1px solid #c3e6cb;
        }

        .buttons {
            margin-top: 30px;
            display: flex;
            gap: 10px;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>Registrar Nuevo Estudiante</h1>
            <p><?= APP_NAME ?></p>
        </div>

        <div class="content">
            <?php if ($exito): ?>
                <div class="success-message">
                    <strong>✅ Estudiante registrado exitosamente</strong>
                    <p>El estudiante <?= htmlspecialchars($datos['nombre'] . ' ' . $datos['apellido_paterno']) ?> ha sido registrado correctamente.</p>
                </div>
                <a href="../index.php" class="btn">Volver al Dashboard</a>
            <?php else: ?>
                <form method="POST" action="">
                    <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">

                    <div class="form-grid">
                        <div class="form-group <?= isset($errores['nombre']) ? 'has-error' : '' ?>">
                            <label>
                                Nombre <span class="required">*</span>
                            </label>
                            <input type="text" 
                                   name="nombre" 
                                   value="<?= htmlspecialchars($datos['nombre'] ?? '') ?>"
                                   placeholder="Juan">
                            <?php if (isset($errores['nombre'])): ?>
                                <?php foreach ($errores['nombre'] as $error): ?>
                                    <div class="error"><?= $error ?></div>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </div>

                        <div class="form-group <?= isset($errores['apellido_paterno']) ? 'has-error' : '' ?>">
                            <label>
                                Apellido Paterno <span class="required">*</span>
                            </label>
                            <input type="text" 
                                   name="apellido_paterno" 
                                   value="<?= htmlspecialchars($datos['apellido_paterno'] ?? '') ?>"
                                   placeholder="Pérez">
                            <?php if (isset($errores['apellido_paterno'])): ?>
                                <?php foreach ($errores['apellido_paterno'] as $error): ?>
                                    <div class="error"><?= $error ?></div>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </div>
                    </div>

                    <div class="form-group <?= isset($errores['apellido_materno']) ? 'has-error' : '' ?>">
                        <label>
                            Apellido Materno <span class="required">*</span>
                        </label>
                        <input type="text" 
                               name="apellido_materno" 
                               value="<?= htmlspecialchars($datos['apellido_materno'] ?? '') ?>"
                               placeholder="García">
                        <?php if (isset($errores['apellido_materno'])): ?>
                            <?php foreach ($errores['apellido_materno'] as $error): ?>
                                <div class="error"><?= $error ?></div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>

                    <div class="form-grid">
                        <div class="form-group <?= isset($errores['email']) ? 'has-error' : '' ?>">
                            <label>
                                Email <span class="required">*</span>
                            </label>
                            <input type="email" 
                                   name="email" 
                                   value="<?= htmlspecialchars($datos['email'] ?? '') ?>"
                                   placeholder="juan.perez@est.edu.bo">
                            <?php if (isset($errores['email'])): ?>
                                <?php foreach ($errores['email'] as $error): ?>
                                    <div class="error"><?= $error ?></div>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </div>

                        <div class="form-group <?= isset($errores['ci']) ? 'has-error' : '' ?>">
                            <label>
                                CI <span class="required">*</span>
                            </label>
                            <input type="text" 
                                   name="ci" 
                                   value="<?= htmlspecialchars($datos['ci'] ?? '') ?>"
                                   placeholder="12345678-LP">
                            <?php if (isset($errores['ci'])): ?>
                                <?php foreach ($errores['ci'] as $error): ?>
                                    <div class="error"><?= $error ?></div>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </div>
                    </div>

                    <div class="form-grid">
                        <div class="form-group <?= isset($errores['telefono']) ? 'has-error' : '' ?>">
                            <label>Teléfono</label>
                            <input type="tel" 
                                   name="telefono" 
                                   value="<?= htmlspecialchars($datos['telefono'] ?? '') ?>"
                                   placeholder="+591 78451234">
                            <?php if (isset($errores['telefono'])): ?>
                                <?php foreach ($errores['telefono'] as $error): ?>
                                    <div class="error"><?= $error ?></div>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </div>

                        <div class="form-group <?= isset($errores['semestre']) ? 'has-error' : '' ?>">
                            <label>
                                Semestre <span class="required">*</span>
                            </label>
                            <select name="semestre">
                                <?php for ($i = 1; $i <= 10; $i++): ?>
                                    <option value="<?= $i ?>" 
                                            <?= ($datos['semestre'] ?? 1) === $i ? 'selected' : '' ?>>
                                        <?= $i ?>° Semestre
                                    </option>
                                <?php endfor; ?>
                            </select>
                            <?php if (isset($errores['semestre'])): ?>
                                <?php foreach ($errores['semestre'] as $error): ?>
                                    <div class="error"><?= $error ?></div>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </div>
                    </div>

                    <div class="buttons">
                        <a href="../index.php" class="btn btn-secondary">Cancelar</a>
                        <button type="submit" class="btn">Registrar Estudiante</button>
                    </div>
                </form>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>