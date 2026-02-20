<?php
declare(strict_types=1);

use App\Repositories\StudentRepository;

$repo = new StudentRepository();
$query = trim($_GET['q'] ?? '');
$resultados = [];

if ($query) {
    $resultados = $repo->search($query);
}
?>

<h2>🔍 Buscar Estudiante</h2>

<form method="GET" action="?action=buscar" style="margin-bottom: 30px; max-width: 500px;">
    <div class="form-group">
        <label for="q">Buscar por nombre, email o código</label>
        <div style="display: flex; gap: 10px;">
            <input type="text" id="q" name="q" value="<?= htmlspecialchars($query) ?>" placeholder="Ej: Juan Pérez" autofocus>
            <button type="submit" class="btn btn-primary" style="flex-shrink: 0;">Buscar</button>
            <?php if ($query): ?>
                <a href="?action=buscar" class="btn btn-primary" style="flex-shrink: 0;">Limpiar</a>
            <?php endif; ?>
        </div>
    </div>
</form>

<?php if ($query): ?>
    <?php if (empty($resultados)): ?>
        <div class="no-data">
            <p>No se encontraron resultados para "<strong><?= htmlspecialchars($query) ?></strong>"</p>
            <p style="margin-top: 10px; font-size: 0.9em;">Intenta con otro término de búsqueda</p>
        </div>
    <?php else: ?>
        <div class="info" style="margin-bottom: 20px;">
            <strong>Se encontraron <?= count($resultados) ?> resultado(s)</strong>
        </div>

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
                    <?php foreach ($resultados as $est): ?>
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
                            <a href="?action=estudiantes&eliminar=<?= $est->getId() ?>" class="btn btn-danger btn-small" onclick="return confirm('¿Eliminar?')">Eliminar</a>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>
<?php else: ?>
    <div class="info" style="text-align: center; padding: 40px;">
        <p>Introduce un término para buscar estudiantes</p>
        <p style="margin-top: 10px; font-size: 0.9em; color: #666;">
            Puedes buscar por: nombre completo, email, código
        </p>
    </div>
<?php endif; ?>
