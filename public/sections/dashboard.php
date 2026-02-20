<?php
declare(strict_types=1);

use App\Repositories\StudentRepository;

$repo = new StudentRepository();
$stats = $repo->getEstadisticas();
$estudiantes = $repo->findAll();
?>

<h2>📊 Dashboard - Información General</h2>

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
        <h3><?= number_format($stats['promedio_general'], 2) ?></h3>
        <p>Promedio General</p>
    </div>
    <div class="stat-card">
        <h3><?= number_format($stats['tasa_aprobacion'], 1) ?>%</h3>
        <p>Tasa de Aprobación</p>
    </div>
</div>

<h3>🎓 Últimos Estudiantes Registrados</h3>

<?php if (empty($estudiantes)): ?>
    <div class="no-data">
        <p>No hay estudiantes registrados</p>
        <a href="?action=crear" class="btn btn-primary">Crear Primer Estudiante</a>
    </div>
<?php else: ?>
    <div class="table-container">
        <table>
            <thead>
                <tr>
                    <th>Código</th>
                    <th>Nombre Completo</th>
                    <th>Email</th>
                    <th>Semestre</th>
                    <th>Estado</th>
                    <th>Promedio</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach (array_slice($estudiantes, 0, 5) as $est): ?>
                <tr>
                    <td><strong><?= htmlspecialchars($est->getCodigo()) ?></strong></td>
                    <td><?= htmlspecialchars($est->getNombreCompleto()) ?></td>
                    <td><?= htmlspecialchars($est->getEmail()) ?></td>
                    <td><?= $est->getSemestre() ?>°</td>
                    <td>
                        <span class="badge badge-<?= $est->getEstado()->value ?>">
                            <?= $est->getEstado()->label() ?>
                        </span>
                    </td>
                    <td><?= number_format($est->getPromedio(), 2) ?></td>
                    <td>
                        <a href="?action=editar&id=<?= $est->getId() ?>" class="btn btn-warning btn-small">Editar</a>
                        <a href="?action=eliminar&id=<?= $est->getId() ?>" class="btn btn-danger btn-small" onclick="return confirm('¿Eliminar estudiante?')">Eliminar</a>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <p style="margin-top: 20px; color: #666;">
        Mostrando los 5 últimos de <?= count($estudiantes) ?> estudiantes. 
        <a href="?action=estudiantes">Ver todos →</a>
    </p>
<?php endif; ?>
