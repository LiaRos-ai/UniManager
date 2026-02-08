<?php
declare(strict_types=1);

// Cargar configuración
require_once __DIR__ . '/../config/constants.php';

// Cargar autoloader de Composer
require_once __DIR__ . '/../vendor/autoload.php';
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= APP_NAME ?> - Sistema de Gestión Académica</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
        }

        .container {
            background: white;
            padding: 60px;
            border-radius: 20px;
            box-shadow: 0 20px 60px rgba(0,0,0,0.3);
            text-align: center;
            max-width: 600px;
        }

        h1 {
            color: #667eea;
            font-size: 3em;
            margin-bottom: 10px;
        }

        .version {
            color: #888;
            font-size: 0.9em;
            margin-bottom: 30px;
        }

        p {
            color: #555;
            font-size: 1.2em;
            line-height: 1.6;
            margin: 20px 0;
        }

        .info-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 15px;
            margin: 30px 0;
        }

        .info-card {
            background: #f8f9fa;
            padding: 20px;
            border-radius: 10px;
            border-left: 4px solid #667eea;
        }

        .info-card strong {
            display: block;
            color: #667eea;
            margin-bottom: 5px;
        }

        .status {
            display: inline-block;
            background: #d4edda;
            color: #155724;
            padding: 8px 16px;
            border-radius: 20px;
            font-weight: bold;
            margin: 20px 0;
        }

        .features {
            text-align: left;
            margin: 30px 0;
            padding: 20px;
            background: #f8f9fa;
            border-radius: 10px;
        }

        .features h3 {
            color: #667eea;
            margin-bottom: 15px;
        }

        .features ul {
            list-style: none;
        }

        .features li {
            padding: 8px 0;
            padding-left: 25px;
            position: relative;
        }

        .features li:before {
            content: "✓";
            position: absolute;
            left: 0;
            color: #667eea;
            font-weight: bold;
        }

        .footer {
            margin-top: 30px;
            padding-top: 20px;
            border-top: 1px solid #eee;
            color: #888;
            font-size: 0.9em;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1><?= APP_NAME ?></h1>
        <div class="version">Versión <?= APP_VERSION ?></div>

        <p>Sistema de Gestión Académica desarrollado con PHP moderno</p>

        <div class="status">✓ Sistema Inicializado Correctamente</div>

        <div class="info-grid">
            <div class="info-card">
                <strong>Versión PHP</strong>
                <?= phpversion() ?>
            </div>
            <div class="info-card">
                <strong>Entorno</strong>
                <?= strtoupper(APP_ENV) ?>
            </div>
            <div class="info-card">
                <strong>Zona Horaria</strong>
                <?= date_default_timezone_get() ?>
            </div>
            <div class="info-card">
                <strong>Fecha/Hora</strong>
                <?= date('d/m/Y H:i:s') ?>
            </div>
        </div>

        <div class="features">
            <h3>Módulos Planificados</h3>
            <ul>
                <li>Gestión de Estudiantes</li>
                <li>Gestión de Docentes</li>
                <li>Registro de Cursos</li>
                <li>Sistema de Calificaciones</li>
                <li>Control de Asistencia</li>
                <li>Reportes y Estadísticas</li>
                <li>API RESTful</li>
                <li>Panel Administrativo</li>
            </ul>
        </div>

        <div class="footer">
            Tecnología Web II - Ingeniería de Sistemas<br>
            <?= date('Y') ?>
        </div>
    </div>
</body>
</html>