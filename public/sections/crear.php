<?php
declare(strict_types=1);

use App\Repositories\StudentRepository;
use App\Models\Student;
use App\Enums\StudentStatus;

$repo = new StudentRepository();
$errores = [];
$datos = [];

// Procesamiento del formulario
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $datos = [
        'codigo' => trim($_POST['codigo'] ?? ''),
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
    if (empty($datos['codigo'])) $errores[] = 'El código es requerido';
    if (empty($datos['nombre'])) $errores[] = 'El nombre es requerido';
    if (empty($datos['apellido_paterno'])) $errores[] = 'El apellido paterno es requerido';
    if (empty($datos['apellido_materno'])) $errores[] = 'El apellido materno es requerido';
    if (empty($datos['email']) || !filter_var($datos['email'], FILTER_VALIDATE_EMAIL)) {
        $errores[] = 'Email válido requerido';
    }
    if (empty($datos['ci'])) $errores[] = 'El CI es requerido';
    if ($datos['semestre'] < 1 || $datos['semestre'] > 10) $errores[] = 'Semestre inválido';

    // Si no hay errores, crear
    if (empty($errores)) {
        try {
            $student = new Student(
                codigo: $datos['codigo'],
                nombre: $datos['nombre'],
                apellidoPaterno: $datos['apellido_paterno'],
                apellidoMaterno: $datos['apellido_materno'],
                email: $datos['email'],
                ci: $datos['ci'],
                telefono: $datos['telefono'] ?: null,
                semestre: $datos['semestre'],
                estado: StudentStatus::from($datos['estado'])
            );

            $id = $repo->create($student);

            $_SESSION['mensaje'] = [
                'tipo' => 'exito',
                'texto' => "✅ Estudiante creado exitosamente (ID: $id)"
            ];

            header('Location: ?action=estudiantes');
            exit;
        } catch (Exception $e) {
            $errores[] = 'Error al crear: ' . $e->getMessage();
        }
    }
}
?>

<h2>➕ Crear Nuevo Estudiante</h2>

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

<form method="POST" action="?action=crear" style="max-width: 600px;">
    <div class="form-row">
        <div class="form-group">
            <label for="codigo">Código *</label>
            <input type="text" id="codigo" name="codigo" value="<?= htmlspecialchars($datos['codigo'] ?? '') ?>" placeholder="2024-00001" required>
        </div>
        <div class="form-group">
            <label for="semestre">Semestre *</label>
            <select id="semestre" name="semestre" required>
                <?php for ($i = 1; $i <= 10; $i++): ?>
                    <option value="<?= $i ?>" <?= ($datos['semestre'] ?? 1) == $i ? 'selected' : '' ?>>
                        <?= $i ?>° Semestre
                    </option>
                <?php endfor; ?>
            </select>
        </div>
    </div>

    <div class="form-group">
        <label for="nombre">Nombre *</label>
        <input type="text" id="nombre" name="nombre" value="<?= htmlspecialchars($datos['nombre'] ?? '') ?>" placeholder="Juan" required>
    </div>

    <div class="form-row">
        <div class="form-group">
            <label for="apellido_paterno">Apellido Paterno *</label>
            <input type="text" id="apellido_paterno" name="apellido_paterno" value="<?= htmlspecialchars($datos['apellido_paterno'] ?? '') ?>" placeholder="Pérez" required>
        </div>
        <div class="form-group">
            <label for="apellido_materno">Apellido Materno *</label>
            <input type="text" id="apellido_materno" name="apellido_materno" value="<?= htmlspecialchars($datos['apellido_materno'] ?? '') ?>" placeholder="García" required>
        </div>
    </div>

    <div class="form-row">
        <div class="form-group">
            <label for="email">Email *</label>
            <input type="email" id="email" name="email" value="<?= htmlspecialchars($datos['email'] ?? '') ?>" placeholder="usuario@est.edu.bo" required>
        </div>
        <div class="form-group">
            <label for="ci">CI *</label>
            <input type="text" id="ci" name="ci" value="<?= htmlspecialchars($datos['ci'] ?? '') ?>" placeholder="12345678-LP" required>
        </div>
    </div>

    <div class="form-row">
        <div class="form-group">
            <label for="telefono">Teléfono</label>
            <input type="tel" id="telefono" name="telefono" value="<?= htmlspecialchars($datos['telefono'] ?? '') ?>" placeholder="+591 7XXXXXXX">
        </div>
        <div class="form-group">
            <label for="estado">Estado</label>
            <select id="estado" name="estado">
                <option value="active" <?= ($datos['estado'] ?? 'active') === 'active' ? 'selected' : '' ?>>Activo</option>
                <option value="inactive" <?= ($datos['estado'] ?? '') === 'inactive' ? 'selected' : '' ?>>Inactivo</option>
                <option value="suspended" <?= ($datos['estado'] ?? '') === 'suspended' ? 'selected' : '' ?>>Suspendido</option>
                <option value="graduated" <?= ($datos['estado'] ?? '') === 'graduated' ? 'selected' : '' ?>>Graduado</option>
            </select>
        </div>
    </div>

    <div style="margin-top: 30px;">
        <button type="submit" class="btn btn-success">✓ Crear Estudiante</button>
        <a href="?action=estudiantes" class="btn btn-primary">Cancelar</a>
    </div>
</form>

<p style="margin-top: 20px; color: #999; font-size: 0.9em;">
    * Campos requeridos
</p>
