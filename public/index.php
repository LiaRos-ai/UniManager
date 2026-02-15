<?php
declare(strict_types=1);

// Cargar configuración
require_once __DIR__ . '/../config/constants.php';
require_once __DIR__ . '/../config/database.php';

// Cargar autoloader de Composer
require_once __DIR__ . '/../vendor/autoload.php';

// Importar clases necesarias
use App\Repositories\StudentRepository;
use App\Enums\StudentStatus;

// Crear repositorio
$studentRepo = new StudentRepository();

// Obtener datos
$todosEstudiantes = $studentRepo->findAll();
$estadisticas = $studentRepo->getEstadisticas();
$top3 = array_slice($studentRepo->orderByPromedio(), 0, 3);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= APP_NAME ?> - Dashboard</title>
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
            padding: 20px;
        }

        .container {
            background: white;
            padding: 30px;
            border-radius: 20px;
            box-shadow: 0 20px 60px rgba(0,0,0,0.3);
            max-width: 1200px;
            margin: 0 auto;
        }

        h1 {
            color: #667eea;
            font-size: 2em;
            margin-bottom: 5px;
        }

        .badge {
            display: inline-block;
            padding: 4px 10px;
            border-radius: 12px;
            font-size: 0.8em;
            font-weight: bold;
            margin: 5px;
        }

        .badge.success { background: #d4edda; color: #155724; }
        .badge.warning { background: #fff3cd; color: #856404; }
        .badge.info { background: #d1ecf1; color: #0c5460; }

        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 15px;
            margin: 25px 0;
        }

        .stat-card {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 20px;
            border-radius: 15px;
            text-align: center;
        }

        .stat-card .number {
            font-size: 2.5em;
            font-weight: bold;
            margin: 10px 0;
        }

        .stat-card .label {
            font-size: 0.9em;
            opacity: 0.9;
        }

        .table-container {
            margin: 25px 0;
            overflow-x: auto;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            background: white;
        }

        th {
            background: #f8f9fa;
            color: #667eea;
            padding: 12px;
            text-align: left;
            font-weight: 600;
            border-bottom: 2px solid #667eea;
        }

        td {
            padding: 10px 12px;
            border-bottom: 1px solid #e9ecef;
        }

        tr:hover {
            background: #f8f9fa;
        }

        .section-title {
            color: #667eea;
            font-size: 1.3em;
            margin: 30px 0 15px 0;
            padding-bottom: 10px;
            border-bottom: 2px solid #667eea;
        }

        .status-active { color: #28a745; font-weight: bold; }
        .status-inactive { color: #dc3545; font-weight: bold; }
    </style>
</head>
<body>
    <div class="container">
        <h1><?= APP_NAME ?></h1>
        <div>
            <span class="badge info">Versión <?= APP_VERSION ?></span>
            <span class="badge success">Día 3 - Completado</span>
        </div>

        <div class="stats-grid">
            <div class="stat-card">
                <div class="label">Total Estudiantes</div>
                <div class="number"><?= $estadisticas['total'] ?></div>
            </div>
            <div class="stat-card">
                <div class="label">Aprobados</div>
                <div class="number"><?= $estadisticas['aprobados'] ?></div>
            </div>
            <div class="stat-card">
                <div class="label">Reprobados</div>
                <div class="number"><?= $estadisticas['reprobados'] ?></div>
            </div>
            <div class="stat-card">
                <div class="label">Promedio General</div>
                <div class="number"><?= number_format($estadisticas['promedio_general'], 1) ?></div>
            </div>
        </div>

        <h2 class="section-title">🏆 Top 3 Estudiantes</h2>
        <div class="table-container">
            <table>
                <thead>
                    <tr>
                        <th>Posición</th>
                        <th>Código</th>
                        <th>Nombre Completo</th>
                        <th>Semestre</th>
                        <th>Promedio</th>
                        <th>Estado</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($top3 as $i => $estudiante): ?>
                        <tr>
                            <td><?= $i + 1 ?></td>
                            <td><?= $estudiante->getCodigo() ?></td>
                            <td><?= $estudiante->getNombreCompleto() ?></td>
                            <td><?= $estudiante->getSemestre() ?>°</td>
                            <td><strong><?= number_format($estudiante->getPromedio(), 2) ?></strong></td>
                            <td>
                                <span class="status-<?= $estudiante->estaActivo() ? 'active' : 'inactive' ?>">
                                    <?= $estudiante->getEstado()->label() ?>
                                </span>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <h2 class="section-title">👥 Todos los Estudiantes</h2>
        <div class="table-container">
            <table>
                <thead>
                    <tr>
                        <th>Código</th>
                        <th>Nombre Completo</th>
                        <th>Email</th>
                        <th>Sem.</th>
                        <th>Promedio</th>
                        <th>Estado</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($todosEstudiantes as $estudiante): ?>
                        <tr>
                            <td><?= $estudiante->getCodigo() ?></td>
                            <td><?= $estudiante->getNombreCompleto() ?></td>
                            <td><?= $estudiante->getEmail() ?></td>
                            <td><?= $estudiante->getSemestre() ?>°</td>
                            <td><?= number_format($estudiante->getPromedio(), 2) ?></td>
                            <td>
                                <span class="status-<?= $estudiante->estaActivo() ? 'active' : 'inactive' ?>">
                                    <?= $estudiante->getEstado()->label() ?>
                                </span>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <div style="margin-top: 30px; padding-top: 20px; border-top: 1px solid #eee; text-align: center; color: #888; font-size: 0.9em;">
            Tecnología Web II - Día 3 Completado • <?= date('Y') ?>
        </div>
    </div>
</body>
</html>