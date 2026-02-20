<?php
declare(strict_types=1);

namespace App\Repositories;

use App\Models\Student;
use App\Enums\StudentStatus;
use App\Database\Database;

/**
 * Repositorio de estudiantes con conexión a BD
 */
class StudentRepository {
    private Database $db;
    
    public function __construct() {
        $this->db = Database::getInstance();
    }
    
    /**
     * Crea un nuevo estudiante
     */
    public function create(Student $student): int {
        try {
            $this->db->beginTransaction();
            
            $id = $this->db->insert('estudiantes', [
                'codigo' => $student->getCodigo(),
                'nombre' => $student->getNombre(),
                'apellido_paterno' => $student->getApellidoPaterno(),
                'apellido_materno' => $student->getApellidoMaterno(),
                'email' => $student->getEmail(),
                'ci' => $student->getCi(),
                'telefono' => $student->getTelefono(),
                'semestre' => $student->getSemestre(),
                'estado' => $student->getEstado()->value,
                'promedio' => $student->getPromedio(),
            ]);
            
            $this->db->commit();
            return $id;
            
        } catch (\Exception $e) {
            $this->db->rollBack();
            throw $e;
        }
    }
    
    /**
     * Obtiene todos los estudiantes
     */
    public function findAll(): array {
        $stmt = $this->db->query("SELECT * FROM estudiantes ORDER BY apellido_paterno, nombre");
        $datos = $stmt->fetchAll();
        
        return array_map(fn($row) => $this->rowToModel($row), $datos);
    }
    
    /**
     * Busca estudiante por ID
     */
    public function findById(int $id): ?Student {
        $stmt = $this->db->query("SELECT * FROM estudiantes WHERE id = ?", [$id]);
        $data = $stmt->fetch();
        
        return $data ? $this->rowToModel($data) : null;
    }
    
    /**
     * Busca estudiante por código
     */
    public function findByCodigo(string $codigo): ?Student {
        $stmt = $this->db->query("SELECT * FROM estudiantes WHERE codigo = ?", [$codigo]);
        $data = $stmt->fetch();
        
        return $data ? $this->rowToModel($data) : null;
    }
    
    /**
     * Busca estudiantes por nombre
     */
    public function search(string $query): array {
        $searchTerm = '%' . $query . '%';
        $sql = "SELECT * FROM estudiantes 
                WHERE CONCAT(nombre, ' ', apellido_paterno, ' ', apellido_materno) LIKE ?
                ORDER BY apellido_paterno, nombre";
        
        $stmt = $this->db->query($sql, [$searchTerm]);
        $datos = $stmt->fetchAll();
        
        return array_map(fn($row) => $this->rowToModel($row), $datos);
    }
    
    /**
     * Busca estudiantes por semestre
     */
    public function findBySemestre(int $semestre): array {
        $stmt = $this->db->query(
            "SELECT * FROM estudiantes WHERE semestre = ? ORDER BY apellido_paterno, nombre",
            [$semestre]
        );
        $datos = $stmt->fetchAll();
        
        return array_map(fn($row) => $this->rowToModel($row), $datos);
    }
    
    /**
     * Busca estudiantes por estado
     */
    public function findByEstado(StudentStatus $estado): array {
        $stmt = $this->db->query(
            "SELECT * FROM estudiantes WHERE estado = ? ORDER BY apellido_paterno, nombre",
            [$estado->value]
        );
        $datos = $stmt->fetchAll();
        
        return array_map(fn($row) => $this->rowToModel($row), $datos);
    }
    
    /**
     * Obtiene estudiantes aprobados
     */
    public function findAprobados(): array {
        $stmt = $this->db->query("SELECT * FROM estudiantes WHERE promedio >= 60 ORDER BY apellido_paterno, nombre");
        $datos = $stmt->fetchAll();
        
        return array_map(fn($row) => $this->rowToModel($row), $datos);
    }
    
    /**
     * Obtiene estudiantes reprobados
     */
    public function findReprobados(): array {
        $stmt = $this->db->query("SELECT * FROM estudiantes WHERE promedio < 60 ORDER BY apellido_paterno, nombre");
        $datos = $stmt->fetchAll();
        
        return array_map(fn($row) => $this->rowToModel($row), $datos);
    }
    
    /**
     * Actualiza un estudiante
     */
    public function update(int $id, Student $student): bool {
        $rowsAffected = $this->db->update(
            'estudiantes',
            [
                'codigo' => $student->getCodigo(),
                'nombre' => $student->getNombre(),
                'apellido_paterno' => $student->getApellidoPaterno(),
                'apellido_materno' => $student->getApellidoMaterno(),
                'email' => $student->getEmail(),
                'ci' => $student->getCi(),
                'telefono' => $student->getTelefono(),
                'semestre' => $student->getSemestre(),
                'estado' => $student->getEstado()->value,
                'promedio' => $student->getPromedio(),
            ],
            'id = ?',
            [$id]
        );
        
        return $rowsAffected > 0;
    }
    
    /**
     * Elimina un estudiante
     */
    public function delete(int $id): bool {
        $rowsAffected = $this->db->delete('estudiantes', 'id = ?', [$id]);
        return $rowsAffected > 0;
    }
    
    /**
     * Obtiene estadísticas generales
     */
    public function getEstadisticas(): array {
        $total = $this->db->query("SELECT COUNT(*) as count FROM estudiantes")->fetch()['count'];
        $aprobados = $this->db->query("SELECT COUNT(*) as count FROM estudiantes WHERE promedio >= 60")->fetch()['count'];
        
        $resultado = $this->db->query("SELECT AVG(promedio) as promedio FROM estudiantes")->fetch();
        $promedioGeneral = (float)($resultado['promedio'] ?? 0);
        
        return [
            'total' => (int)$total,
            'aprobados' => (int)$aprobados,
            'reprobados' => (int)$total - (int)$aprobados,
            'promedio_general' => round($promedioGeneral, 2),
            'tasa_aprobacion' => $total > 0 ? round(((int)$aprobados / (int)$total) * 100, 2) : 0
        ];
    }
    
    /**
     * Convierte una fila de BD a objeto Student
     */
    private function rowToModel(array $row): Student {
        return new Student(
            id: (int)$row['id'],
            codigo: $row['codigo'],
            nombre: $row['nombre'],
            apellidoPaterno: $row['apellido_paterno'],
            apellidoMaterno: $row['apellido_materno'],
            email: $row['email'],
            ci: $row['ci'],
            telefono: $row['telefono'],
            semestre: (int)$row['semestre'],
            estado: StudentStatus::from($row['estado']),
            notas: !empty($row['notas']) ? json_decode($row['notas'], true) : []
        );
    }
}