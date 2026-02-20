<?php
declare(strict_types=1);

session_start();
require_once __DIR__ . '/../config/constants.php';
require_once __DIR__ . '/../vendor/autoload.php';

use App\Repositories\StudentRepository;
use App\Models\Student;
use App\Enums\StudentStatus;

// Inicializar variables
$action = $_GET['action'] ?? 'dashboard';
$repo = new StudentRepository();
$mensaje = $_SESSION['mensaje'] ?? null;
unset($_SESSION['mensaje']);

// ESTILOS
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>UniManager - Sistema de Gestión Académica</title>
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
            max-width: 1200px;
            margin: 0 auto;
        }

        .header {
            background: white;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            margin-bottom: 30px;
        }

        .header h1 {
            color: #667eea;
            font-size: 2.5em;
            margin-bottom: 10px;
        }

        .header p {
            color: #666;
            font-size: 1.1em;
        }

        .nav {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
            gap: 10px;
            margin: 20px 0 0 0;
        }

        .nav a, .nav button {
            padding: 12px 20px;
            background: #667eea;
            color: white;
            text-decoration: none;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            font-weight: 600;
            transition: background 0.3s;
        }

        .nav a:hover, .nav button:hover {
            background: #5568d3;
        }

        .nav a.active {
            background: #764ba2;
        }

        .content {
            background: white;
            border-radius: 10px;
            padding: 30px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }

        .mensaje {
            padding: 15px;
            margin-bottom: 20px;
            border-radius: 8px;
            font-weight: 600;
        }

        .exito {
            background: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }

        .error {
            background: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }

        .info {
            background: #d1ecf1;
            color: #0c5460;
            border: 1px solid #bee5eb;
        }

        .grid-stats {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
            margin: 30px 0;
        }

        .stat-card {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 20px;
            border-radius: 10px;
            text-align: center;
        }

        .stat-card h3 {
            font-size: 2.5em;
            margin-bottom: 10px;
        }

        .stat-card p {
            font-size: 0.9em;
            opacity: 0.9;
        }

        .table-container {
            overflow-x: auto;
            margin: 20px 0;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin: 20px 0;
        }

        table thead {
            background: #f0f0f0;
            font-weight: 600;
        }

        table th, table td {
            padding: 12px;
            text-align: left;
            border-bottom: 1px solid #ddd;
        }

        table tr:hover {
            background: #f9f9f9;
        }

        .badge {
            display: inline-block;
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 0.85em;
            font-weight: 600;
        }

        .badge-active {
            background: #d4edda;
            color: #155724;
        }

        .badge-inactive {
            background: #e2e3e5;
            color: #383d41;
        }

        .badge-suspended {
            background: #f8d7da;
            color: #721c24;
        }

        .badge-graduated {
            background: #d1ecf1;
            color: #0c5460;
        }

        .btn {
            padding: 8px 16px;
            margin: 0 5px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-weight: 600;
            transition: all 0.3s;
            text-decoration: none;
            display: inline-block;
        }

        .btn-primary {
            background: #667eea;
            color: white;
        }

        .btn-primary:hover {
            background: #5568d3;
        }

        .btn-success {
            background: #28a745;
            color: white;
        }

        .btn-success:hover {
            background: #218838;
        }

        .btn-warning {
            background: #ffc107;
            color: #333;
        }

        .btn-warning:hover {
            background: #e0a800;
        }

        .btn-danger {
            background: #dc3545;
            color: white;
        }

        .btn-danger:hover {
            background: #c82333;
        }

        .btn-small {
            padding: 5px 10px;
            font-size: 0.85em;
        }

        .form-group {
            margin-bottom: 20px;
        }

        label {
            display: block;
            margin-bottom: 8px;
            font-weight: 600;
            color: #333;
        }

        input, select, textarea {
            width: 100%;
            padding: 10px;
            border: 2px solid #ddd;
            border-radius: 5px;
            font-size: 1em;
            font-family: inherit;
        }

        input:focus, select:focus, textarea:focus {
            outline: none;
            border-color: #667eea;
            box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
        }

        .form-row {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
        }

        .modal {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.5);
            z-index: 1000;
            align-items: center;
            justify-content: center;
        }

        .modal.active {
            display: flex;
        }

        .modal-content {
            background: white;
            padding: 30px;
            border-radius: 10px;
            max-width: 500px;
            width: 90%;
            max-height: 90vh;
            overflow-y: auto;
        }

        .modal-close {
            float: right;
            font-size: 2em;
            cursor: pointer;
            color: #666;
        }

        .modal-close:hover {
            color: #000;
        }

        .no-data {
            text-align: center;
            padding: 40px;
            color: #999;
        }

        .no-data p {
            font-size: 1.2em;
            margin-bottom: 20px;
        }
    </style>
</head>
<body>
    <div class="container">
        <!-- HEADER -->
        <div class="header">
            <h1>🎓 UniManager</h1>
            <p>Sistema de Gestión Académica - CRUD Student</p>
            
            <nav class="nav">
                <a href="?action=dashboard" class="<?= $action === 'dashboard' ? 'active' : '' ?>">📊 Dashboard</a>
                <a href="?action=estudiantes" class="<?= $action === 'estudiantes' ? 'active' : '' ?>">👥 Estudiantes</a>
                <a href="?action=crear" class="<?= $action === 'crear' ? 'active' : '' ?>">➕ Crear</a>
                <a href="?action=buscar" class="<?= $action === 'buscar' ? 'active' : '' ?>">🔍 Buscar</a>
                <a href="?action=estadisticas" class="<?= $action === 'estadisticas' ? 'active' : '' ?>">📈 Estadísticas</a>
            </nav>
        </div>

        <!-- CONTENIDO -->
        <div class="content">
            <?php if ($mensaje): ?>
                <div class="mensaje <?= $mensaje['tipo'] ?>">
                    <?= htmlspecialchars($mensaje['texto']) ?>
                </div>
            <?php endif; ?>

            <?php 
            // Mostrar contenido según acción
            switch ($action) {
                case 'dashboard':
                    include 'sections/dashboard.php';
                    break;
                case 'estudiantes':
                    include 'sections/estudiantes.php';
                    break;
                case 'crear':
                    include 'sections/crear.php';
                    break;
                case 'editar':
                    include 'sections/editar.php';
                    break;
                case 'buscar':
                    include 'sections/buscar.php';
                    break;
                case 'estadisticas':
                    include 'sections/estadisticas.php';
                    break;
                default:
                    include 'sections/dashboard.php';
            }
            ?>
        </div>
    </div>
</body>
</html>