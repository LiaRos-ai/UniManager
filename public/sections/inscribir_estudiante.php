<?php
declare(strict_types=1);

use App\Repositories\InscripcionRepository;
use App\Repositories\StudentRepository;

$inscripcionRepo = new InscripcionRepository();
$estudianteRepo = new StudentRepository();

$estudiante_seleccionado = null;
$inscripciones_estudiante = [];
$cursos_disponibles = [];
$mensaje = '';
$tipo_mensaje = '';

// Obtener lista de estudiantes para el dropdown (como arrays)
$estudiantes = $estudianteRepo->findAllAsArray();

// Si se envía formulario de inscripción
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['accion'])) {
    $accion = htmlspecialchars($_POST['accion']);
    
    if ($accion === 'inscribir') {
        $estudiante_id = (int)($_POST['estudiante_id'] ?? 0);
        $curso_id = (int)($_POST['curso_id'] ?? 0);
        
        if ($estudiante_id > 0 && $curso_id > 0) {
            try {
                $inscripcionRepo->inscribir($estudiante_id, $curso_id);
                $_SESSION['exito'] = "✅ Inscripción realizada exitosamente";
                header('Location: ?action=inscribir_estudiante&id=' . $estudiante_id);
                exit;
            } catch (Exception $e) {
                $_SESSION['error'] = "❌ " . $e->getMessage();
                header('Location: ?action=inscribir_estudiante&id=' . $estudiante_id);
                exit;
            }
        }
    } elseif ($accion === 'retirar') {
        $estudiante_id = (int)($_POST['estudiante_id'] ?? 0);
        $curso_id = (int)($_POST['curso_id'] ?? 0);
        
        if ($estudiante_id > 0 && $curso_id > 0) {
            try {
                $inscripcionRepo->retirar($estudiante_id, $curso_id);
                $_SESSION['exito'] = "✅ Retiro realizado exitosamente";
                header('Location: ?action=inscribir_estudiante&id=' . $estudiante_id);
                exit;
            } catch (Exception $e) {
                $_SESSION['error'] = "❌ " . $e->getMessage();
                header('Location: ?action=inscribir_estudiante&id=' . $estudiante_id);
                exit;
            }
        }
    }
}

// Si se seleccionó un estudiante
if (isset($_GET['id'])) {
    $estudiante_id = (int)$_GET['id'];
    $estudiante_seleccionado = $estudianteRepo->findByIdAsArray($estudiante_id);
    
    if ($estudiante_seleccionado) {
        $inscripciones_estudiante = $inscripcionRepo->cursosDeEstudiante($estudiante_id);
        $cursos_disponibles = $inscripcionRepo->cursosDisponibles();
        
        // Obtener rendimiento
        $rendimiento = $inscripcionRepo->rendimiento($estudiante_id);
    }
}

// Obtener historial completo
$historial_completo = $inscripcionRepo->historiaiCompleto();
$estadisticas = $inscripcionRepo->estadisticas();
?>

<div class="inscripciones-container">
    <div class="header-section">
        <h1>📚 Sistema de Inscripciones</h1>
        <p class="subtitle">Gestiona las inscripciones de estudiantes en cursos</p>
    </div>

    <!-- Tarjetas de estadísticas -->
    <div class="stats-grid">
        <div class="stat-card" style="border-left: 4px solid #3498db;">
            <h3><?= $estadisticas['total_inscripciones'] ?></h3>
            <p>Inscripciones Totales</p>
        </div>
        <div class="stat-card" style="border-left: 4px solid #2ecc71;">
            <h3><?= $estadisticas['activas'] ?></h3>
            <p>Activas</p>
        </div>
        <div class="stat-card" style="border-left: 4px solid #f39c12;">
            <h3><?= $estadisticas['completadas'] ?></h3>
            <p>Completadas</p>
        </div>
        <div class="stat-card" style="border-left: 4px solid #e74c3c;">
            <h3><?= $estadisticas['retiradas'] ?></h3>
            <p>Retiradas</p>
        </div>
        <div class="stat-card" style="border-left: 4px solid #9b59b6;">
            <h3><?= number_format((float)$estadisticas['promedio_general'], 2) ?></h3>
            <p>Promedio General</p>
        </div>
    </div>

    <div class="inscripciones-content">
        <!-- Panel Izquierdo: Seleccionar Estudiante -->
        <div class="student-selection">
            <h2>👥 Seleccionar Estudiante</h2>
            <form method="get">
                <input type="hidden" name="action" value="inscribir_estudiante">
                <select name="id" class="form-control" onchange="this.form.submit()">
                    <option value="">-- Selecciona un estudiante --</option>
                    <?php foreach ($estudiantes as $est): ?>
                        <option value="<?= $est['id'] ?>" <?= ($estudiante_seleccionado && $estudiante_seleccionado['id'] === $est['id']) ? 'selected' : '' ?>>
                            <?= htmlspecialchars($est['nombre']) ?> (<?= htmlspecialchars($est['codigo']) ?>)
                        </option>
                    <?php endforeach; ?>
                </select>
            </form>
        </div>

        <!-- Panel Derecho: Detalles y Cursos -->
        <?php if ($estudiante_seleccionado): ?>
        <div class="student-details">
            <!-- Información del Estudiante -->
            <div class="info-box">
                <h2>📋 Información del Estudiante</h2>
                <div class="info-grid">
                    <div class="info-item">
                        <span class="label">Nombre:</span>
                        <span class="value"><?= htmlspecialchars($estudiante_seleccionado['nombre']) ?></span>
                    </div>
                    <div class="info-item">
                        <span class="label">Código:</span>
                        <span class="value"><?= htmlspecialchars($estudiante_seleccionado['codigo']) ?></span>
                    </div>
                    <div class="info-item">
                        <span class="label">Email:</span>
                        <span class="value"><?= htmlspecialchars($estudiante_seleccionado['email']) ?></span>
                    </div>
                    <div class="info-item">
                        <span class="label">Semestre:</span>
                        <span class="value"><?= $estudiante_seleccionado['semestre'] ?></span>
                    </div>
                </div>
            </div>

            <!-- Rendimiento Académico -->
            <?php if ($rendimiento): ?>
            <div class="rendimiento-box">
                <h2>📊 Rendimiento Académico</h2>
                <div class="rendimiento-stats">
                    <div class="rend-item">
                        <span class="rend-label">Cursos Inscritos:</span>
                        <span class="rend-value"><?= $rendimiento['cursos_inscritos'] ?></span>
                    </div>
                    <div class="rend-item">
                        <span class="rend-label">Completados:</span>
                        <span class="rend-value"><?= $rendimiento['cursos_completados'] ?></span>
                    </div>
                    <div class="rend-item">
                        <span class="rend-label">Promedio:</span>
                        <span class="rend-value"><?= number_format((float)$rendimiento['promedio_notas'], 2) ?></span>
                    </div>
                </div>
            </div>
            <?php endif; ?>

            <!-- Inscripciones Actuales -->
            <div class="inscripciones-actuales">
                <h2>📖 Cursos Inscritos (<?= count($inscripciones_estudiante) ?>)</h2>
                
                <?php if (empty($inscripciones_estudiante)): ?>
                    <div class="empty-state">
                        <p>📭 El estudiante no está inscrito en ningún curso</p>
                    </div>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>Curso</th>
                                    <th>Créditos</th>
                                    <th>Docente</th>
                                    <th>Nota</th>
                                    <th>Estado</th>
                                    <th>Inscripción</th>
                                    <th>Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($inscripciones_estudiante as $insc): ?>
                                <tr class="estado-<?= htmlspecialchars($insc['estado']) ?>">
                                    <td><strong><?= htmlspecialchars($insc['curso']) ?></strong></td>
                                    <td><?= $insc['creditos'] ?></td>
                                    <td><?= htmlspecialchars($insc['docente'] ?? 'N/A') ?></td>
                                    <td>
                                        <?php if ($insc['nota_final']): ?>
                                            <span class="badge badge-<?= strtolower($insc['calificacion']) ?>">
                                                <?= $insc['nota_final'] ?> (<?= $insc['calificacion'] ?>)
                                            </span>
                                        <?php else: ?>
                                            <span class="badge badge-default">-</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <span class="badge badge-estado-<?= htmlspecialchars($insc['estado']) ?>">
                                            <?= ucfirst(htmlspecialchars($insc['estado'])) ?>
                                        </span>
                                    </td>
                                    <td><?= htmlspecialchars($insc['fecha_inscripcion']) ?></td>
                                    <td>
                                        <?php if ($insc['estado'] === 'activa'): ?>
                                        <form method="post" style="display:inline;">
                                            <input type="hidden" name="accion" value="retirar">
                                            <input type="hidden" name="estudiante_id" value="<?= $estudiante_seleccionado['id'] ?>">
                                            <input type="hidden" name="curso_id" value="<?= $insc['curso_id'] ?>">
                                            <button type="submit" class="btn btn-small btn-danger" 
                                                    onclick="return confirm('¿Retirar al estudiante de este curso?')">
                                                Retirar
                                            </button>
                                        </form>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </div>

            <!-- Cursos Disponibles para Inscribir -->
            <div class="cursos-disponibles">
                <h2>➕ Cursos Disponibles para Inscribir</h2>
                
                <?php 
                // Filtrar cursos en los que el estudiante no está inscrito
                $cursos_no_inscritos = array_filter($cursos_disponibles, function($curso) use ($inscripciones_estudiante) {
                    foreach ($inscripciones_estudiante as $insc) {
                        if ($insc['curso_id'] === $curso['id'] && $insc['estado'] === 'activa') {
                            return false;
                        }
                    }
                    return true;
                });
                ?>
                
                <?php if (empty($cursos_no_inscritos)): ?>
                    <div class="empty-state">
                        <p>✅ El estudiante está inscrito en todos los cursos disponibles</p>
                    </div>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>Código</th>
                                    <th>Nombre del Curso</th>
                                    <th>Docente</th>
                                    <th>Créditos</th>
                                    <th>Semestre</th>
                                    <th>Cupos</th>
                                    <th>Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($cursos_no_inscritos as $curso): ?>
                                <tr class="<?= ($curso['cupos_disponibles'] <= 0) ? 'sin-cupos' : '' ?>">
                                    <td><strong><?= htmlspecialchars($curso['codigo']) ?></strong></td>
                                    <td>
                                        <div>
                                            <p><?= htmlspecialchars($curso['nombre']) ?></p>
                                            <small><?= htmlspecialchars($curso['descripcion'] ?? '') ?></small>
                                        </div>
                                    </td>
                                    <td><?= htmlspecialchars($curso['docente'] ?? 'N/A') ?></td>
                                    <td><?= $curso['creditos'] ?></td>
                                    <td><?= $curso['semestre'] ?></td>
                                    <td>
                                        <?php if ($curso['cupos_disponibles'] > 0): ?>
                                            <span class="badge badge-success">
                                                <?= $curso['cupos_disponibles'] ?> / <?= $curso['capacidad_maxima'] ?>
                                            </span>
                                        <?php else: ?>
                                            <span class="badge badge-danger">
                                                ❌ LLENO (<?= $curso['matriculados'] ?> / <?= $curso['capacidad_maxima'] ?>)
                                            </span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <?php if ($curso['cupos_disponibles'] > 0): ?>
                                        <form method="post" style="display:inline;">
                                            <input type="hidden" name="accion" value="inscribir">
                                            <input type="hidden" name="estudiante_id" value="<?= $estudiante_seleccionado['id'] ?>">
                                            <input type="hidden" name="curso_id" value="<?= $curso['id'] ?>">
                                            <button type="submit" class="btn btn-small btn-success">
                                                Inscribir
                                            </button>
                                        </form>
                                        <?php else: ?>
                                            <button disabled class="btn btn-small btn-danger">
                                                Sin cupos
                                            </button>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </div>
        </div>
        <?php else: ?>
            <div class="empty-selection">
                <p>👆 Selecciona un estudiante para ver sus inscripciones</p>
            </div>
        <?php endif; ?>
    </div>

    <!-- Historial Completo -->
    <div class="historial-section">
        <h2>📜 Historial Completo de Inscripciones</h2>
        
        <?php if (empty($historial_completo)): ?>
            <div class="empty-state">
                <p>📭 No hay inscripciones registradas</p>
            </div>
        <?php else: ?>
            <div class="table-responsive">
                <table class="table table-sm">
                    <thead>
                        <tr>
                            <th>Estudiante</th>
                            <th>Código Est.</th>
                            <th>Curso</th>
                            <th>Código Curso</th>
                            <th>Docente</th>
                            <th>Nota</th>
                            <th>Calif.</th>
                            <th>Estado</th>
                            <th>Fecha Inscripción</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($historial_completo as $registro): ?>
                        <tr>
                            <td><?= htmlspecialchars($registro['estudiante_nombre']) ?></td>
                            <td><small><?= htmlspecialchars($registro['codigo_estudiante']) ?></small></td>
                            <td><?= htmlspecialchars($registro['nombre_curso']) ?></td>
                            <td><small><?= htmlspecialchars($registro['codigo_curso']) ?></small></td>
                            <td><?= htmlspecialchars($registro['docente_nombre'] ?? 'N/A') ?></td>
                            <td>
                                <?php if ($registro['nota_final']): ?>
                                    <?= number_format((float)$registro['nota_final'], 2) ?>
                                <?php else: ?>
                                    <span class="text-muted">-</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <span class="badge badge-<?= strtolower($registro['calificacion']) ?>">
                                    <?= $registro['calificacion'] ?>
                                </span>
                            </td>
                            <td>
                                <span class="badge badge-estado-<?= htmlspecialchars($registro['estado']) ?>">
                                    <?= ucfirst(htmlspecialchars($registro['estado'])) ?>
                                </span>
                            </td>
                            <td><?= $registro['fecha_inscripcion'] ?></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>
</div>

<style>
.inscripciones-container {
    padding: 20px;
    max-width: 1400px;
    margin: 0 auto;
}

.header-section {
    margin-bottom: 30px;
}

.header-section h1 {
    color: #2c3e50;
    margin: 0 0 5px 0;
}

.subtitle {
    color: #7f8c8d;
    margin: 0;
}

.stats-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
    gap: 15px;
    margin-bottom: 30px;
}

.stat-card {
    background: white;
    padding: 20px;
    border-radius: 8px;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    text-align: center;
}

.stat-card h3 {
    font-size: 28px;
    color: #2c3e50;
    margin: 0 0 10px 0;
}

.stat-card p {
    color: #7f8c8d;
    margin: 0;
    font-size: 12px;
}

.inscripciones-content {
    display: grid;
    grid-template-columns: 250px 1fr;
    gap: 20px;
    margin-bottom: 30px;
}

.student-selection {
    background: white;
    padding: 20px;
    border-radius: 8px;
    height: fit-content;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
}

.student-selection h2 {
    font-size: 16px;
    margin-top: 0;
    color: #2c3e50;
}

.student-selection select {
    width: 100%;
    padding: 10px;
    border: 1px solid #ddd;
    border-radius: 4px;
    font-size: 14px;
}

.student-details {
    display: grid;
    gap: 20px;
}

.info-box, .rendimiento-box, .inscripciones-actuales, .cursos-disponibles {
    background: white;
    padding: 20px;
    border-radius: 8px;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
}

.info-box h2, .rendimiento-box h2, .inscripciones-actuales h2, .cursos-disponibles h2 {
    margin-top: 0;
    color: #2c3e50;
    font-size: 18px;
}

.info-grid {
    display: grid;
    grid-template-columns: 1fr;
    gap: 12px;
}

.info-item {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 8px;
    background: #f8f9fa;
    border-radius: 4px;
}

.info-item .label {
    font-weight: 600;
    color: #2c3e50;
}

.info-item .value {
    color: #34495e;
}

.rendimiento-stats {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 15px;
}

.rend-item {
    background: #f8f9fa;
    padding: 15px;
    border-radius: 4px;
    text-align: center;
}

.rend-label {
    display: block;
    color: #7f8c8d;
    font-size: 12px;
    margin-bottom: 5px;
}

.rend-value {
    display: block;
    font-size: 24px;
    font-weight: bold;
    color: #2c3e50;
}

.empty-state {
    padding: 40px 20px;
    text-align: center;
    color: #7f8c8d;
    background: #f8f9fa;
    border-radius: 4px;
}

.empty-selection {
    grid-column: 1 / -1;
    padding: 80px 20px;
    text-align: center;
    background: white;
    border-radius: 8px;
    color: #7f8c8d;
    font-size: 18px;
}

.table-responsive {
    overflow-x: auto;
}

.table {
    width: 100%;
    border-collapse: collapse;
    margin-top: 10px;
}

.table thead {
    background: #ecf0f1;
}

.table th {
    padding: 12px;
    text-align: left;
    color: #2c3e50;
    font-weight: 600;
    border-bottom: 2px solid #bdc3c7;
}

.table td {
    padding: 10px 12px;
    border-bottom: 1px solid #ecf0f1;
}

.table tbody tr:hover {
    background: #f8f9fa;
}

.table tbody tr.sin-cupos {
    opacity: 0.6;
}

.estado-activa {
    background: #d5f4e6;
}

.estado-completada {
    background: #d4edda;
}

.estado-retirada {
    background: #f8d7da;
}

.badge {
    display: inline-block;
    padding: 4px 8px;
    border-radius: 4px;
    font-size: 11px;
    font-weight: 600;
}

.badge-success {
    background: #d4edda;
    color: #155724;
}

.badge-danger {
    background: #f8d7da;
    color: #721c24;
}

.badge-default {
    background: #e2e3e5;
    color: #383d41;
}

.badge-a {
    background: #d4edda;
    color: #155724;
}

.badge-b {
    background: #cfe2ff;
    color: #084298;
}

.badge-c {
    background: #fff3cd;
    color: #664d03;
}

.badge-d {
    background: #ffe5cc;
    color: #664d03;
}

.badge-f {
    background: #f8d7da;
    color: #721c24;
}

.badge-estado-activa {
    background: #cfe2ff;
    color: #084298;
}

.badge-estado-completada {
    background: #d4edda;
    color: #155724;
}

.badge-estado-retirada {
    background: #e2e3e5;
    color: #383d41;
}

.btn {
    padding: 10px 16px;
    border: none;
    border-radius: 4px;
    cursor: pointer;
    font-weight: 600;
    font-size: 14px;
    transition: all 0.3s;
}

.btn-small {
    padding: 6px 12px;
    font-size: 12px;
}

.btn-success {
    background: #28a745;
    color: white;
}

.btn-success:hover {
    background: #218838;
}

.btn-danger {
    background: #dc3545;
    color: white;
}

.btn-danger:hover {
    background: #c82333;
}

.btn:disabled {
    opacity: 0.5;
    cursor: not-allowed;
}

.historial-section {
    background: white;
    padding: 20px;
    border-radius: 8px;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    margin-top: 30px;
}

.historial-section h2 {
    margin-top: 0;
    color: #2c3e50;
}

.table-sm th,
.table-sm td {
    padding: 8px;
    font-size: 13px;
}

.text-muted {
    color: #6c757d;
}

@media (max-width: 768px) {
    .inscripciones-content {
        grid-template-columns: 1fr;
    }
    
    .stats-grid {
        grid-template-columns: repeat(auto-fit, minmax(120px, 1fr));
    }
    
    .rendimiento-stats {
        grid-template-columns: 1fr;
    }
}
</style>
