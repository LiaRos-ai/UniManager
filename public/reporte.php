<?php
declare(strict_types=1);

require_once __DIR__ . '/../config/constants.php';
require_once __DIR__ . '/../vendor/autoload.php';

use App\Repositories\StudentRepository;
use App\Utils\Helpers;

$studentRepo = new StudentRepository();

// Obtener ID del estudiante
$id = (int)($_GET['id'] ?? 0);
$estudiante = $studentRepo->findById($id);

if (!$estudiante) {
    die('Estudiante no encontrado');
}

$reporte = $estudiante->generarReporte();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reporte - <?= $estudiante->getNombreCompleto() ?></title>
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
            text-align: center;
        }

        .header h1 {
            font-size: 2em;
            margin-bottom: 10px;
        }

        .content {
            padding: 30px;
        }

        .section {
            margin-bottom: 30px;
        }

        .section h2 {
            color: #667eea;
            font-size: 1.3em;
            margin-bottom: 15px;
            padding-bottom: 10px;
            border-bottom: 2px solid #667eea;
        }

        .info-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 15px;
        }

        .info-item {
            background: #f8f9fa;
            padding: 15px;
            border-radius: 8px;
        }

        .info-label {
            font-size: 0.85em;
            color: #888;
            margin-bottom: 5px;
        }

        .info-value {
            font-size: 1.1em;
            font-weight: bold;
            color: #333;
        }

        .grade {
            display: inline-block;
            width: 60px;
            height: 60px;
            line-height: 60px;
            text-align: center;
            border-radius: 50%;
            font-size: 1.8em;
            font-weight: bold;
            color: white;
            margin-right: 10px;
        }

        .grade-A { background: #28a745; }
        .grade-B { background: #17a2b8; }
        .grade-C { background: #ffc107; }
        .grade-D { background: #fd7e14; }
        .grade-F { background: #dc3545; }

        .back-button {
            display: inline-block;
            padding: 10px 20px;
            background: #667eea;
            color: white;
            text-decoration: none;
            border-radius: 8px;
            margin-top: 20px;
        }

        .back-button:hover {
            background: #5568d3;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1><?= APP_NAME ?></h1>
            <p>Reporte Académico Individual</p>
        </div>

        <div class="content">
            <div class="section">
                <h2>Información del Estudiante</h2>
                <div class="info-grid">
                    <div class="info-item">
                        <div class="info-label">Código</div>
                        <div class="info-value"><?= $reporte['estudiante']['codigo'] ?></div>
                    </div>
                    <div class="info-item">
                        <div class="info-label">Nombre Completo</div>
                        <div class="info-value"><?= $reporte['estudiante']['nombre_completo'] ?></div>
                    </div>
                    <div class="info-item">
                        <div class="info-label">Email</div>
                        <div class="info-value"><?= $reporte['estudiante']['email'] ?></div>
                    </div>
                    <div class="info-item">
                        <div class="info-label">Semestre</div>
                        <div class="info-value"><?= $reporte['estudiante']['semestre'] ?>°</div>
                    </div>
                </div>
            </div>

            <div class="section">
                <h2>Rendimiento Académico</h2>
                <div style="display: flex; align-items: center; margin-bottom: 20px;">
                    <div class="grade grade-<?= $reporte['academico']['letra'] ?>">
                        <?= $reporte['academico']['letra'] ?>
                    </div>
                    <div>
                        <div style="font-size: 1.5em; font-weight: bold;">
                            Promedio: <?= $reporte['academico']['promedio'] ?>
                        </div>
                        <div style="color: <?= $reporte['academico']['aprobado'] ? '#28a745' : '#dc3545' ?>">
                            <?= $reporte['academico']['aprobado'] ? '✓ Aprobado' : '✗ Reprobado' ?>
                        </div>
                    </div>
                </div>

                <div class="info-grid">
                    <div class="info-item">
                        <div class="info-label">Mejor Nota</div>
                        <div class="info-value"><?= $reporte['academico']['mejor_nota'] ?></div>
                    </div>
                    <div class="info-item">
                        <div class="info-label">Peor Nota</div>
                        <div class="info-value"><?= $reporte['academico']['peor_nota'] ?></div>
                    </div>
                    <div class="info-item">
                        <div class="info-label">Mediana</div>
                        <div class="info-value"><?= $reporte['academico']['mediana'] ?></div>
                    </div>
                    <div class="info-item">
                        <div class="info-label">Total Notas</div>
                        <div class="info-value"><?= count($reporte['academico']['notas']) ?></div>
                    </div>
                </div>
            </div>

            <a href="index.php" class="back-button">← Volver al Dashboard</a>
        </div>
    </div>
</body>
</html>