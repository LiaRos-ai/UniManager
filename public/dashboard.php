<?php
/**
 * public/dashboard.php
 * Página protegida - Dashboard principal (SOLO USUARIOS AUTENTICADOS)
 */

declare(strict_types=1);

// Iniciar sesión segura
require_once __DIR__ . '/../config/session.php';
require_once __DIR__ . '/../vendor/autoload.php';

iniciarSesionSegura();
requiereAutenticacion();  // ← PROTEGE ESTA PÁGINA - Redirige a login si no está autenticado

use App\Repositories\StudentRepository;
use App\Repositories\InscripcionRepository;
use UniManager\Auth\Auth;

$usuario = obtenerUsuarioActual();
$auth = new Auth();

// Obtener datos de estadísticas
$stats = null;
$inscripciones_proximas = [];

if (tieneRol('estudiante', 'docente', 'administrador')) {
    $repo = new StudentRepository();
    $stats = $repo->getEstadisticas();
    
    if (tieneRol('estudiante')) {
        $inscripcionRepo = new InscripcionRepository();
        $cursosActivos = $inscripcionRepo->cursosDisponibles();
        $inscripciones_proximas = array_slice($cursosActivos, 0, 5);
    }
}

// Procesar logout
if (isset($_GET['logout'])) {
    $auth->logout();
    header('Location: /unimanager/public/login.php?mensaje=success');
    exit;
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - UniManager</title>
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

        .navbar {
            background: white;
            padding: 20px 30px;
            border-radius: 10px;
            margin-bottom: 30px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .navbar h1 {
            color: #667eea;
            font-size: 1.8em;
        }

        .navbar-user {
            display: flex;
            align-items: center;
            gap: 25px;
        }

        .user-info {
            text-align: right;
        }

        .user-info p {
            color: #999;
            font-size: 0.85em;
        }

        .user-info strong {
            color: #333;
            display: block;
            font-size: 0.95em;
        }

        .btn-logout {
            padding: 10px 20px;
            background: #dc3545;
            color: white;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            text-decoration: none;
            font-weight: 600;
            transition: background 0.3s;
            font-size: 0.9em;
        }

        .btn-logout:hover {
            background: #c82333;
        }

        .header-section {
            background: white;
            padding: 30px;
            border-radius: 10px;
            margin-bottom: 30px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }

        .header-section h2 {
            color: #667eea;
            margin-bottom: 10px;
            font-size: 1.6em;
        }

        .header-section p {
            color: #999;
            font-size: 0.9em;
        }

        .alert {
            padding: 15px 20px;
            border-radius: 8px;
            margin-bottom: 20px;
            border-left: 4px solid;
        }

        .alert-info {
            background: #d1ecf1;
            color: #0c5460;
            border-left-color: #17a2b8;
        }

        .alert-success {
            background: #d4edda;
            color: #155724;
            border-left-color: #28a745;
        }

        .alert-warning {
            background: #fff3cd;
            color: #856404;
            border-left-color: #ffc107;
        }

        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }

        .stat-card {
            background: white;
            padding: 25px;
            border-radius: 10px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            border-left: 4px solid #667eea;
        }

        .stat-card h3 {
            font-size: 2.2em;
            color: #667eea;
            margin-bottom: 10px;
        }

        .stat-card p {
            color: #999;
            font-size: 0.85em;
            font-weight: 500;
        }

        .section {
            background: white;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            margin-bottom: 30px;
        }

        .section h3 {
            color: #667eea;
            margin-bottom: 20px;
            font-size: 1.3em;
        }

        .btn-group {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
            gap: 15px;
        }

        .btn {
            padding: 12px 20px;
            background: #667eea;
            color: white;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            text-decoration: none;
            font-weight: 600;
            transition: background 0.3s;
            display: inline-block;
            text-align: center;
            font-size: 0.9em;
        }

        .btn:hover {
            background: #5568d3;
        }

        .btn-secondary {
            background: #6c757d;
        }

        .btn-secondary:hover {
            background: #5a6268;
        }

        .table {
            width: 100%;
            border-collapse: collapse;
        }

        .table th,
        .table td {
            padding: 12px;
            text-align: left;
            border-bottom: 1px solid #eee;
        }

        .table th {
            background: #f8f9fa;
            color: #333;
            font-weight: 600;
            font-size: 0.85em;
        }

        .table tbody tr:hover {
            background: #f8f9fa;
        }

        .badge {
            display: inline-block;
            padding: 6px 12px;
            border-radius: 4px;
            font-size: 0.75em;
            font-weight: 600;
        }

        .badge-success {
            background: #d4edda;
            color: #155724;
        }

        .badge-info {
            background: #d1ecf1;
            color: #0c5460;
        }

        .badge-warning {
            background: #fff3cd;
            color: #856404;
        }

        .empty-message {
            text-align: center;
            padding: 40px;
            color: #999;
        }

        .security-info {
            background: #f0f4f8;
            padding: 15px;
            border-radius: 6px;
            border-left: 4px solid #28a745;
        }

        .security-info ul {
            margin-left: 20px;
            margin-top: 10px;
            color: #555;
            font-size: 0.9em;
        }

        .security-info li {
            margin-bottom: 8px;
        }
    </style>
</head>
<body>
    <div class="container">
        <!-- Navbar -->
        <div class="navbar">
            <h1>🎓 UniManager</h1>
            <div class="navbar-user">
                <div class="user-info">
                    <strong><?= htmlspecialchars($usuario['nombre']) ?></strong>
                    <p><?= htmlspecialchars($usuario['email']) ?></p>
                    <p style="font-size: 0.75em; margin-top: 3px;">
                        <span style="background: #667eea; color: white; padding: 2px 8px; border-radius: 3px;">
                            <?= htmlspecialchars(strtoupper($usuario['rol'])) ?>
                        </span>
                    </p>
                </div>
                <a href="?logout=1" class="btn-logout">🚪 Cerrar Sesión</a>
            </div>
        </div>

        <!-- Header -->
        <div class="header-section">
            <h2>👋 ¡Bienvenido, <?= htmlspecialchars(explode(' ', $usuario['nombre'])[0]) ?>!</h2>
            <p>Dashboard protegido - Sesión segura iniciada</p>
        </div>

        <!-- Alert de zona protegida -->
        <div class="alert alert-info">
            <strong>🔒 ZONA PROTEGIDA:</strong> Esta página solo es accesible para usuarios autenticados. 
            Tu sesión está protegida con CSRF token e ID regenerado.
        </div>

        <?php if ($stats): ?>
        <!-- Estadísticas -->
        <div class="stats-grid">
            <div class="stat-card">
                <h3><?= $stats['total'] ?></h3>
                <p>📊 Total Estudiantes</p>
            </div>
            <div class="stat-card">
                <h3><?= $stats['aprobados'] ?></h3>
                <p>✅ Aprobados</p>
            </div>
            <div class="stat-card">
                <h3><?= $stats['reprobados'] ?></h3>
                <p>❌ Reprobados</p>
            </div>
            <div class="stat-card">
                <h3><?= number_format((float)$stats['promedio_general'], 2) ?></h3>
                <p>📈 Promedio General</p>
            </div>
        </div>
        <?php endif; ?>

        <!-- Accesos rápidos -->
        <div class="section">
            <h3>🔗 Accesos Rápidos</h3>
            <div class="btn-group">
                <a href="/unimanager/public/index.php?action=dashboard" class="btn">📊 Dashboard</a>
                <a href="/unimanager/public/index.php?action=estudiantes" class="btn">👥 Estudiantes</a>
                <a href="/unimanager/public/index.php?action=inscribir_estudiante" class="btn">📚 Inscripciones</a>
                <a href="/unimanager/public/index.php?action=estadisticas" class="btn">📈 Estadísticas</a>
            </div>
        </div>

        <?php if (!empty($inscripciones_proximas)): ?>
        <!-- Cursos próximos -->
        <div class="section">
            <h3>📖 Cursos Disponibles</h3>
            <table class="table">
                <thead>
                    <tr>
                        <th>Código</th>
                        <th>Nombre</th>
                        <th>Docente</th>
                        <th>Cupos</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($inscripciones_proximas as $curso): ?>
                    <tr>
                        <td><strong><?= htmlspecialchars($curso['codigo']) ?></strong></td>
                        <td><?= htmlspecialchars($curso['nombre']) ?></td>
                        <td><?= htmlspecialchars($curso['docente'] ?? 'N/A') ?></td>
                        <td>
                            <?php if ($curso['cupos_disponibles'] > 0): ?>
                                <span class="badge badge-success">
                                    <?= $curso['cupos_disponibles'] ?>/<?= $curso['capacidad_maxima'] ?>
                                </span>
                            <?php else: ?>
                                <span class="badge badge-warning">
                                    LLENO
                                </span>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <?php endif; ?>

        <!-- Información de seguridad -->
        <div class="section">
            <h3>🔐 Información de Seguridad</h3>
            <div class="security-info">
                <strong>Protecciones Implementadas:</strong>
                <ul>
                    <li>✅ Sesión iniciada con ID regenerado (previene Session Fixation)</li>
                    <li>✅ CSRF Token validado en formularios</li>
                    <li>✅ Cookies: HttpOnly + Secure + SameSite=Strict</li>
                    <li>✅ Contraseña: bcrypt con cost 12</li>
                    <li>✅ Expiración de sesión: 30 minutos de inactividad</li>
                    <li>✅ Logging de intentos fallidos</li>
                    <li>✅ Password Needs Rehash: Actualización automática</li>
                </ul>
            </div>
        </div>

        <!-- Footer -->
        <div style="text-align: center; padding: 20px; color: rgba(255,255,255,0.8); font-size: 0.85em;">
            <p>UniManager © 2026 - Sistema Seguro de Gestión Académica</p>
        </div>
    </div>
</body>
</html>