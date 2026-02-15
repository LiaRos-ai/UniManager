<?php
declare(strict_types=1);

// Cargar configuración
require_once __DIR__ . '/../config/constants.php';
require_once __DIR__ . '/../config/database.php';

// Cargar autoloader de Composer
require_once __DIR__ . '/../vendor/autoload.php';

// Importar clases necesarias
use App\Enums\UserRole;
use App\Enums\StudentStatus;
use App\Utils\Validator;

// Crear instancia de configuración
$dbConfig = new DatabaseConfig();

// Datos de ejemplo para mostrar
$ejemploEstudiante = [
    'codigo' => '2024-00001',
    'nombre' => 'Juan Pérez',
    'email' => 'juan@ejemplo.com',
    'ci' => '12345678-LP',
    'telefono' => '+59178451234',
    'estado' => StudentStatus::ACTIVE
];

// Validar datos
$validaciones = [
    'Código Estudiante' => Validator::codigoEstudiante($ejemploEstudiante['codigo']),
    'Email' => Validator::email($ejemploEstudiante['email']),
    'CI' => Validator::ci($ejemploEstudiante['ci']),
    'Teléfono' => Validator::telefono($ejemploEstudiante['telefono']),
];
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= APP_NAME ?> - Sistema de Gestión Académica</title>
    <style>
        /* Estilos previos se mantienen */
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            padding: 20px;
        }

        .container {
            background: white;
            padding: 40px;
            border-radius: 20px;
            box-shadow: 0 20px 60px rgba(0,0,0,0.3);
            max-width: 1000px;
            margin: 0 auto;
        }

        h1 {
            color: #667eea;
            font-size: 2.5em;
            margin-bottom: 10px;
        }

        .version {
            color: #888;
            font-size: 0.9em;
            margin-bottom: 30px;
        }

        .badge {
            display: inline-block;
            padding: 6px 12px;
            border-radius: 12px;
            font-size: 0.85em;
            font-weight: bold;
            margin: 5px;
        }

        .badge.success { background: #d4edda; color: #155724; }
        .badge.error { background: #f8d7da; color: #721c24; }
        .badge.info { background: #d1ecf1; color: #0c5460; }

        .grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 15px;
            margin: 20px 0;
        }

        .card {
            background: #f8f9fa;
            padding: 20px;
            border-radius: 10px;
            border-left: 4px solid #667eea;
        }

        .card h3 {
            color: #667eea;
            margin-bottom: 10px;
            font-size: 1em;
        }

        .card p {
            color: #555;
            margin: 5px 0;
        }

        .validation-list {
            list-style: none;
            padding: 0;
        }

        .validation-list li {
            padding: 8px;
            margin: 5px 0;
            border-radius: 5px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .validation-list li.valid {
            background: #d4edda;
        }

        .validation-list li.invalid {
            background: #f8d7da;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1><?= APP_NAME ?></h1>
        <div class="version">
            Versión <?= APP_VERSION ?> 
            <span class="badge info">Día 2 - Completado</span>
        </div>

        <div class="grid">
            <div class="card">
                <h3>Información del Sistema</h3>
                <p><strong>PHP:</strong> <?= phpversion() ?></p>
                <p><strong>Entorno:</strong> <?= APP_ENV ?></p>
                <p><strong>Zona Horaria:</strong> <?= date_default_timezone_get() ?></p>
            </div>

            <div class="card">
                <h3>Base de Datos</h3>
                <p><strong>Host:</strong> <?= $dbConfig->getHost() ?></p>
                <p><strong>BD:</strong> <?= $dbConfig->getDatabase() ?></p>
                <p><strong>Puerto:</strong> <?= $dbConfig->getPort() ?></p>
            </div>
        </div>

        <div class="card" style="margin: 20px 0;">
            <h3>Ejemplo de Estudiante</h3>
            <p><strong>Código:</strong> <?= $ejemploEstudiante['codigo'] ?></p>
            <p><strong>Nombre:</strong> <?= $ejemploEstudiante['nombre'] ?></p>
            <p><strong>Email:</strong> <?= $ejemploEstudiante['email'] ?></p>
            <p><strong>CI:</strong> <?= $ejemploEstudiante['ci'] ?></p>
            <p>
                <strong>Estado:</strong> 
                <span class="badge" style="background-color: <?= $ejemploEstudiante['estado']->color() ?>; color: white;">
                    <?= $ejemploEstudiante['estado']->label() ?>
                </span>
            </p>
        </div>

        <div class="card">
            <h3>Validaciones Realizadas</h3>
            <ul class="validation-list">
                <?php foreach ($validaciones as $campo => $valido): ?>
                    <li class="<?= $valido ? 'valid' : 'invalid' ?>">
                        <span><?= $campo ?></span>
                        <span><?= $valido ? '✓ Válido' : '✗ Inválido' ?></span>
                    </li>
                <?php endforeach; ?>
            </ul>
        </div>

        <div class="card" style="margin-top: 20px;">
            <h3>Roles Disponibles</h3>
            <?php foreach (UserRole::cases() as $role): ?>
                <span class="badge info"><?= $role->label() ?></span>
            <?php endforeach; ?>
        </div>

        <div style="margin-top: 30px; padding-top: 20px; border-top: 1px solid #eee; text-align: center; color: #888; font-size: 0.9em;">
            Tecnología Web II - Día 2 Completado<br>
            <?= date('Y') ?>
        </div>
    </div>
</body>
</html>
