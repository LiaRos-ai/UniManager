<?php
declare(strict_types=1);

use App\Repositories\StudentRepository;
use App\Enums\StudentStatus;

$repo = new StudentRepository();
$id = (int)($_GET['id'] ?? 0);
$estudiante = $repo->findById($id);

if (!$estudiante) {
    echo '<div class="no-data"><p>Estudiante no encontrado</p></div>';
    exit;
}

$errores = [];
$datos = [];

// Procesamiento del formulario
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $datos = [
        'nombre' => trim($_POST['nombre'] ?? ''),
        'apellido_paterno' => trim($_POST['apellido_paterno'] ?? ''),
        'apellido_materno' => trim($_POST['apellido_materno'] ?? ''),
        'email' => trim($_POST['email'] ?? ''),
        'ci' => trim($_POST['ci'] ?? ''),
        'telefono' => trim($_POST['telefono'] ?? ''),
        'semestre' => (int)($_POST['semestre'] ?? 1),
        'estado' => $_POST['estado'] ?? 'active',
    ];

    // Validaciones
    if (empty($datos['nombre'])) $errores[] = 'El nombre es requerido';
    if (empty($datos['apellido_paterno'])) $errores[] = 'El apellido paterno es requerido';
    if (empty($datos['apellido_materno'])) $errores[] = 'El apellido materno es requerido';
    if (empty($datos['email']) || !filter_var($datos['email'], FILTER_VALIDATE_EMAIL)) {
        $errores[] = 'Email válido requerido';
    }
    if (empty($datos['ci'])) $errores[] = 'El CI es requerido';

    // Si no hay errores, actualizar
    if (empty($errores)) {
        try {
            $estudiante
                ->setNombre($datos['nombre'])
                ->setApellidoPaterno($datos['apellido_paterno'])
                ->setApellidoMaterno($datos['apellido_materno'])
                ->setEmail($datos['email'])
                ->setCi($datos['ci'])
                ->setTelefono($datos['telefono'] ?: null)
                ->setSemestre($datos['semestre'])
                ->setEstado(StudentStatus::from($datos['estado']));

            $repo->update($id, $estudiante);

            $_SESSION['mensaje'] = [
                'tipo' => 'exito',
                'texto' => '✅ Estudiante actualizado exitosamente'
            ];

            header('Location: ?action=estudiantes');
            exit;
        } catch (Exception $e) {
            $errores[] = 'Error al actualizar: ' . $e->getMessage();
        }
    }
} else {
    // Prellenar datos
    $datos = [
        'nombre' => $estudiante->getNombre(),
        'apellido_paterno' => $estudiante->getApellidoPaterno(),
        'apellido_materno' => $estudiante->getApellidoMaterno(),
        'email' => $estudiante->getEmail(),
        'ci' => $estudiante->getCi(),
        'telefono' => $estudiante->getTelefono() ?? '',
        'semestre' => $estudiante->getSemestre(),
        'estado' => $estudiante->getEstado()->value,
    ];
}
?>

<h2>✏️ Editar Estudiante</h2>

<div class="info" style="margin-bottom: 20px;">
    <strong>Código:</strong> <?= htmlspecialchars($estudiante->getCodigo()) ?> | 
    <strong>ID:</strong> <?= $estudiante->getId() ?>
</div>

<?php if (!empty($errores)): ?>
    <div class="mensaje error">
        <strong>❌ Errores encontrados:</strong>
        <ul style="margin-top: 10px;">
            <?php foreach ($errores as $error): ?>
                <li><?= htmlspecialchars($error) ?></li>
            <?php endforeach; ?>
        </ul>
    </div>
<?php endif; ?>

<form method="POST" action="?action=editar&id=<?= $id ?>" style="max-width: 600px;">
    <div class="form-row">
        <div class="form-group">
            <label for="semestre">Semestre</label>
            <select id="semestre" name="semestre">
                <?php for ($i = 1; $i <= 10; $i++): ?>
                    <option value="<?= $i ?>" <?= $datos['semestre'] == $i ? 'selected' : '' ?>>
                        <?= $i ?>° Semestre
                    </option>
                <?php endfor; ?>
            </select>
        </div>
        <div class="form-group">
            <label for="estado">Estado</label>
            <select id="estado" name="estado">
                <option value="active" <?= $datos['estado'] === 'active' ? 'selected' : '' ?>>Activo</option>
                <option value="inactive" <?= $datos['estado'] === 'inactive' ? 'selected' : '' ?>>Inactivo</option>
                <option value="suspended" <?= $datos['estado'] === 'suspended' ? 'selected' : '' ?>>Suspendido</option>
                <option value="graduated" <?= $datos['estado'] === 'graduated' ? 'selected' : '' ?>>Graduado</option>
            </select>
        </div>
    </div>

    <div class="form-group">
        <label for="nombre">Nombre</label>
        <input type="text" id="nombre" name="nombre" value="<?= htmlspecialchars($datos['nombre']) ?>" required>
    </div>

    <div class="form-row">
        <div class="form-group">
            <label for="apellido_paterno">Apellido Paterno</label>
            <input type="text" id="apellido_paterno" name="apellido_paterno" value="<?= htmlspecialchars($datos['apellido_paterno']) ?>" required>
        </div>
        <div class="form-group">
            <label for="apellido_materno">Apellido Materno</label>
            <input type="text" id="apellido_materno" name="apellido_materno" value="<?= htmlspecialchars($datos['apellido_materno']) ?>" required>
        </div>
    </div>

    <div class="form-row">
        <div class="form-group">
            <label for="email">Email</label>
            <input type="email" id="email" name="email" value="<?= htmlspecialchars($datos['email']) ?>" required>
        </div>
        <div class="form-group">
            <label for="ci">CI</label>
            <input type="text" id="ci" name="ci" value="<?= htmlspecialchars($datos['ci']) ?>" required>
        </div>
    </div>

    <div class="form-group">
        <label for="telefono">Teléfono</label>
        <input type="tel" id="telefono" name="telefono" value="<?= htmlspecialchars($datos['telefono']) ?>" placeholder="+591 7XXXXXXX">
    </div>

    <div style="margin-top: 30px;">
        <button type="submit" class="btn btn-success">✓ Guardar Cambios</button>
        <a href="?action=estudiantes" class="btn btn-primary">Cancelar</a>
    </div>
</form>
