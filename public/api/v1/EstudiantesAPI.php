<?php
declare(strict_types=1);

// Rutas compatibles con Windows/Linux
$dir = dirname(__DIR__, 2);
if (!file_exists($dir . DIRECTORY_SEPARATOR . 'config' . DIRECTORY_SEPARATOR . 'Database.php')) {
    $dir = dirname($dir);
}
$projectRoot = $dir;

require_once $projectRoot . DIRECTORY_SEPARATOR . 'config' . DIRECTORY_SEPARATOR . 'Database.php';
require_once __DIR__ . '/ApiResponse.php';

/**
 * API RESTful de Estudiantes
 *
 * Endpoints:
 *   GET    /api/v1/estudiantes        → Listar todos (con paginación)
 *   POST   /api/v1/estudiantes        → Crear nuevo estudiante
 *   GET    /api/v1/estudiantes/{id}   → Obtener uno por ID
 *   PUT    /api/v1/estudiantes/{id}   → Actualizar completo
 *   DELETE /api/v1/estudiantes/{id}   → Eliminar (soft delete)
 */
class EstudiantesAPI {

    private \PDO $db;

    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }

    // ═══════════════════════════════════════════════════════
    //  ROUTER: despacha al método correcto según HTTP method
    // ═══════════════════════════════════════════════════════
    public function handleRequest(string $method, ?int $id): void {
        match(true) {
            $method === 'GET'    && $id === null => $this->listar(),
            $method === 'GET'    && $id !== null => $this->obtener($id),
            $method === 'POST'   && $id === null => $this->crear(),
            $method === 'PUT'    && $id !== null => $this->actualizar($id),
            $method === 'DELETE' && $id !== null => $this->eliminar($id),
            default => ApiResponse::error('Método o ruta no permitida', 405)
        };
    }

    // ── GET /api/v1/estudiantes ──────────────────────────────
    private function listar(): void {
        $page   = max(1, (int)($_GET['page']  ?? 1));
        $limit  = min(50, max(1, (int)($_GET['limit'] ?? 10)));
        $offset = ($page - 1) * $limit;
        $search = trim($_GET['search'] ?? '');

        // Query con búsqueda opcional
        $where = $search ? "WHERE (nombre LIKE ? OR email LIKE ? OR codigo LIKE ?)" : "";
        $params = $search ? ["%$search%", "%$search%", "%$search%"] : [];

        $stmt = $this->db->prepare(
            "SELECT id, codigo, nombre, apellido_paterno, apellido_materno, email, semestre, estado, promedio, fecha_creacion
             FROM estudiantes $where ORDER BY nombre LIMIT $limit OFFSET $offset"
        );
        $stmt->execute($params);
        $estudiantes = $stmt->fetchAll();

        // Total para paginación
        $countStmt = $this->db->prepare("SELECT COUNT(*) FROM estudiantes $where");
        $countStmt->execute($params);
        $total = (int)$countStmt->fetchColumn();

        ApiResponse::success([
            'estudiantes' => $estudiantes,
            'pagination'  => [
                'page'       => $page,
                'limit'      => $limit,
                'total'      => $total,
                'totalPages' => (int)ceil($total / $limit),
                'hasNext'    => ($page * $limit) < $total,
                'hasPrev'    => $page > 1,
            ]
        ]);
    }

    // ── GET /api/v1/estudiantes/{id} ─────────────────────────
    private function obtener(int $id): void {
        $stmt = $this->db->prepare(
            'SELECT id, codigo, nombre, apellido_paterno, apellido_materno, email, ci, telefono, semestre, estado, promedio, fecha_creacion
             FROM estudiantes WHERE id=?'
        );
        $stmt->execute([$id]);
        $estudiante = $stmt->fetch();

        if (!$estudiante) {
            ApiResponse::error("Estudiante con ID $id no encontrado", 404);
        }
        ApiResponse::success($estudiante);
    }

    // ── POST /api/v1/estudiantes ─────────────────────────────
    private function crear(): void {
        $data = $this->getJsonBody();

        // Validación
        $errores = $this->validar($data);
        if (!empty($errores)) {
            ApiResponse::error('Datos de entrada inválidos', 422, $errores);
        }

        // Verificar email y CI únicos
        $check = $this->db->prepare('SELECT id FROM estudiantes WHERE email=?');
        $check->execute([$data['email']]);
        if ($check->fetch()) {
            ApiResponse::error('El email ya está registrado', 409);
        }

        // Insertar
        $stmt = $this->db->prepare(
            'INSERT INTO estudiantes (codigo, nombre, apellido_paterno, apellido_materno, email, ci, telefono, semestre, estado)
             VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)'
        );
        $stmt->execute([
            $data['codigo'] ?? '',
            trim($data['nombre']),
            trim($data['apellido_paterno'] ?? ''),
            trim($data['apellido_materno'] ?? ''),
            strtolower(trim($data['email'])),
            $data['ci'] ?? '',
            $data['telefono'] ?? '',
            (int)($data['semestre'] ?? 1),
            $data['estado'] ?? 'active',
        ]);

        $newId = (int)$this->db->lastInsertId();
        ApiResponse::success(
            ['id' => $newId, 'message' => 'Estudiante creado exitosamente'],
            201,
            'Recurso creado'
        );
    }

    // ── PUT /api/v1/estudiantes/{id} ─────────────────────────
    private function actualizar(int $id): void {
        // Verificar que existe
        $check = $this->db->prepare('SELECT id FROM estudiantes WHERE id=?');
        $check->execute([$id]);
        if (!$check->fetch()) {
            ApiResponse::error("Estudiante con ID $id no encontrado", 404);
        }

        $data    = $this->getJsonBody();
        $errores = $this->validar($data);
        if (!empty($errores)) {
            ApiResponse::error('Datos de entrada inválidos', 422, $errores);
        }

        $stmt = $this->db->prepare(
            'UPDATE estudiantes
             SET nombre=?, apellido_paterno=?, apellido_materno=?, email=?, ci=?, telefono=?, semestre=?, estado=?
             WHERE id=?'
        );
        $stmt->execute([
            trim($data['nombre']),
            trim($data['apellido_paterno'] ?? ''),
            trim($data['apellido_materno'] ?? ''),
            strtolower(trim($data['email'])),
            $data['ci'] ?? '',
            $data['telefono'] ?? '',
            (int)($data['semestre'] ?? 1),
            $data['estado'] ?? 'active',
            $id,
        ]);

        ApiResponse::success(['updated' => true], 200, 'Estudiante actualizado');
    }

    // ── DELETE /api/v1/estudiantes/{id} ──────────────────────
    private function eliminar(int $id): void {
        $check = $this->db->prepare('SELECT id FROM estudiantes WHERE id=?');
        $check->execute([$id]);
        if (!$check->fetch()) {
            ApiResponse::error("Estudiante con ID $id no encontrado", 404);
        }

        // Soft delete: marcar como 'inactive' en lugar de borrar
        $stmt = $this->db->prepare('UPDATE estudiantes SET estado=? WHERE id=?');
        $stmt->execute(['inactive', $id]);

        // 204 No Content: éxito sin cuerpo de respuesta
        http_response_code(204);
        exit;
    }

    // ── HELPERS PRIVADOS ─────────────────────────────────────

    /** Lee y decodifica el cuerpo JSON del request */
    private function getJsonBody(): array {
        $raw  = file_get_contents('php://input');
        $data = json_decode($raw, true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            ApiResponse::error('Cuerpo JSON inválido', 400);
        }
        return $data ?? [];
    }

    /** Valida campos obligatorios */
    private function validar(array $data): array {
        $errores = [];
        if (empty(trim($data['nombre'] ?? ''))) {
            $errores['nombre'] = 'El nombre es obligatorio';
        } elseif (strlen($data['nombre']) < 2) {
            $errores['nombre'] = 'Mínimo 2 caracteres';
        }
        if (empty(trim($data['email'] ?? ''))) {
            $errores['email'] = 'El email es obligatorio';
        } elseif (!filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
            $errores['email'] = 'El email no tiene formato válido';
        }
        if (empty(trim($data['apellido_paterno'] ?? ''))) {
            $errores['apellido_paterno'] = 'El apellido paterno es obligatorio';
        }
        $sem = (int)($data['semestre'] ?? 0);
        if ($sem < 1 || $sem > 10) {
            $errores['semestre'] = 'El semestre debe estar entre 1 y 10';
        }
        return $errores;
    }
}