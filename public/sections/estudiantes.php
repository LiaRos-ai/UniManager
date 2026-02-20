<?php
declare(strict_types=1);

use App\Repositories\StudentRepository;

$repo = new StudentRepository();
$estudiantes = $repo->findAll();

// Procesar eliminación
if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['eliminar'])) {
    $id = (int)$_GET['eliminar'];
    if ($repo->delete($id)) {
        $_SESSION['mensaje'] = [
            'tipo' => 'exito',
            'texto' => '✅ Estudiante eliminado correctamente'
        ];
        header('Location: ?action=estudiantes');
        exit;
    }
}
?>

<h2>👥 Lista de Estudiantes</h2>

<?php if (empty($estudiantes)): ?>
    <div class="no-data">
        <p>No hay estudiantes registrados</p>
        <a href="?action=crear" class="btn btn-primary">Crear Primer Estudiante</a>
    </div>
<?php else: ?>
    <p style="margin-bottom: 20px; color: #666;">
        Total de estudiantes: <strong><?= count($estudiantes) ?></strong>
    </p>

    <div class="table-container">
        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Código</th>
                    <th>Nombre Completo</th>
                    <th>Email</th>
                    <th>CI</th>
                    <th>Semestre</th>
                    <th>Estado</th>
                    <th>Promedio</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($estudiantes as $est): ?>
                <tr>
                    <td><?= $est->getId() ?></td>
                    <td><strong><?= htmlspecialchars($est->getCodigo()) ?></strong></td>
                    <td><?= htmlspecialchars($est->getNombreCompleto()) ?></td>
                    <td><?= htmlspecialchars($est->getEmail()) ?></td>
                    <td><?= htmlspecialchars($est->getCi()) ?></td>
                    <td><?= $est->getSemestre() ?>°</td>
                    <td>
                        <span class="badge badge-<?= $est->getEstado()->value ?>">
                            <?= $est->getEstado()->label() ?>
                        </span>
                    </td>
                    <td>
                        <strong><?= number_format($est->getPromedio(), 2) ?></strong>
                        <?php if ($est->getPromedio() >= 60): ?>
                            <span style="color: green;">✓</span>
                        <?php else: ?>
                            <span style="color: red;">✗</span>
                        <?php endif; ?>
                    </td>
                    <td>
                        <a href="?action=editar&id=<?= $est->getId() ?>" class="btn btn-warning btn-small">Editar</a>
                        <a href="?action=estudiantes&eliminar=<?= $est->getId() ?>" class="btn btn-danger btn-small" onclick="return confirm('¿Estás seguro de eliminar a <?= htmlspecialchars($est->getNombreCompleto()) ?>?')">Eliminar</a>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
<?php endif; ?>

<div style="margin-top: 20px;">
    <a href="?action=crear" class="btn btn-success">➕ Crear Nuevo Estudiante</a>
</div>
