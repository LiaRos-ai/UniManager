<?php
declare(strict_types=1);

use App\Repositories\StudentRepository;
use App\Enums\StudentStatus;

$repo = new StudentRepository();
$stats = $repo->getEstadisticas();

$aprobados = $repo->findAprobados();
$reprobados = $repo->findReprobados();
$activos = $repo->findByEstado(StudentStatus::ACTIVE);
$inactivos = $repo->findByEstado(StudentStatus::INACTIVE);
?>

<h2>📈 Estadísticas del Sistema</h2>

<div class="grid-stats">
    <div class="stat-card">
        <h3><?= $stats['total'] ?></h3>
        <p>Total de Estudiantes</p>
    </div>
    <div class="stat-card">
        <h3><?= $stats['aprobados'] ?></h3>
        <p>Estudiantes Aprobados</p>
    </div>
    <div class="stat-card">
        <h3><?= $stats['reprobados'] ?></h3>
        <p>Estudiantes Reprobados</p>
    </div>
    <div class="stat-card">
        <h3><?= number_format((float)$stats['promedio_general'], 2) ?></h3>
        <p>Promedio General</p>
    </div>
    <div class="stat-card">
        <h3><?= number_format((float)$stats['tasa_aprobacion'], 1) ?>%</h3>
        <p>Tasa de Aprobación</p>
    </div>
    <div class="stat-card">
        <h3><?= count($activos) ?></h3>
        <p>Estudiantes Activos</p>
    </div>
</div>

<h3>📊 Desglose por Estado</h3>
<div class="table-container">
    <table>
        <thead>
            <tr>
                <th>Estado</th>
                <th>Cantidad</th>
                <th>Porcentaje</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td><span class="badge badge-active">Activo</span></td>
                <td><?= count($activos) ?></td>
                <td><?= $stats['total'] > 0 ? number_format((count($activos) / $stats['total']) * 100, 1) : 0 ?>%</td>
            </tr>
            <tr>
                <td><span class="badge badge-inactive">Inactivo</span></td>
                <td><?= count($inactivos) ?></td>
                <td><?= $stats['total'] > 0 ? number_format((count($inactivos) / $stats['total']) * 100, 1) : 0 ?>%</td>
            </tr>
        </tbody>
    </table>
</div>

<h3>🎓 Rendimiento Académico</h3>
<div class="table-container">
    <table>
        <thead>
            <tr>
                <th>Categoría</th>
                <th>Cantidad</th>
                <th>Porcentaje</th>
            </tr>
        </thead>
        <tbody>
            <tr style="background: #d4edda;">
                <td><strong>Aprobados</strong> (≥ 60)</td>
                <td><?= $stats['aprobados'] ?></td>
                <td><?= number_format($stats['tasa_aprobacion'], 1) ?>%</td>
            </tr>
            <tr style="background: #f8d7da;">
                <td><strong>Reprobados</strong> (< 60)</td>
                <td><?= $stats['reprobados'] ?></td>
                <td><?= number_format(100 - $stats['tasa_aprobacion'], 1) ?>%</td>
            </tr>
        </tbody>
    </table>
</div>

<h3>📈 Estudiantes por Semestre</h3>
<?php
$semestreStats = [];
for ($i = 1; $i <= 10; $i++) {
    $count = count($repo->findBySemestre($i));
    if ($count > 0) {
        $semestreStats[$i] = $count;
    }
}
?>

<?php if (empty($semestreStats)): ?>
    <p style="color: #999;">No hay estudiantes registrados</p>
<?php else: ?>
    <div class="table-container">
        <table>
            <thead>
                <tr>
                    <th>Semestre</th>
                    <th>Cantidad</th>
                    <th>Porcentaje</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($semestreStats as $sem => $count): ?>
                <tr>
                    <td><strong><?= $sem ?>°</strong></td>
                    <td><?= $count ?></td>
                    <td><?= number_format(($count / $stats['total']) * 100, 1) ?>%</td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
<?php endif; ?>

<h3>🏆 Top 5 Mejores Estudiantes</h3>
<?php
$todos = $repo->findAll();
usort($todos, function($a, $b) {
    return $b->getPromedio() <=> $a->getPromedio();
});
$top5 = array_slice($todos, 0, 5);
?>

<?php if (empty($top5)): ?>
    <p style="color: #999;">No hay estudiantes registrados</p>
<?php else: ?>
    <div class="table-container">
        <table>
            <thead>
                <tr>
                    <th>Posición</th>
                    <th>Nombre</th>
                    <th>Código</th>
                    <th>Promedio</th>
                    <th>Calificación</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($top5 as $index => $est): ?>
                <tr style="background: <?= $index === 0 ? '#fff3cd' : '' ?>">
                    <td><strong><?= $index + 1 ?></strong></td>
                    <td><?= htmlspecialchars($est->getNombreCompleto()) ?></td>
                    <td><?= htmlspecialchars($est->getCodigo()) ?></td>
                    <td><strong><?= number_format($est->getPromedio(), 2) ?></strong></td>
                    <td>
                        <?php 
                            $promedio = $est->getPromedio();
                            if ($promedio >= 90) echo '🏆 Excelente A';
                            elseif ($promedio >= 80) echo '⭐ Muy Bueno B';
                            elseif ($promedio >= 70) echo '👍 Bueno C';
                            elseif ($promedio >= 60) echo '✓ Aprobado D';
                            else echo '✗ Reprobado F';
                        ?>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
<?php endif; ?>

<div style="margin-top: 30px; padding: 20px; background: #f8f9fa; border-radius: 8px;">
    <h4>📋 Información Adicional</h4>
    <ul style="margin-left: 20px;">
        <li><strong>Nota mínima aprobatoria:</strong> 60.00</li>
        <li><strong>Escala de calificaciones:</strong>
            <ul>
                <li>A: 90 - 100 (Excelente)</li>
                <li>B: 80 - 89 (Muy Bueno)</li>
                <li>C: 70 - 79 (Bueno)</li>
                <li>D: 60 - 69 (Aprobado)</li>
                <li>F: 0 - 59 (Reprobado)</li>
            </ul>
        </li>
    </ul>
</div>
