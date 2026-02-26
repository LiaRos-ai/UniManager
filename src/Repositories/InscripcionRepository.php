<?php
declare(strict_types=1);

namespace App\Repositories;

use App\Database\Database;
use RuntimeException;

/**
 * InscripcionRepository - Gestiona inscripciones de estudiantes en cursos
 */
class InscripcionRepository {
    private Database $db;

    public function __construct() {
        $this->db = Database::getInstance();
    }

    /**
     * Obtiene todos los cursos de un estudiante con detalles completos
     * 
     * @param int $estudianteId ID del estudiante
     * @return array Array de cursos con información del docente
     */
    public function cursosDeEstudiante(int $estudianteId): array {
        $stmt = $this->db->query(
            'SELECT
                c.id as curso_id,
                c.codigo, 
                c.nombre AS curso, 
                c.creditos,
                c.semestre,
                i.nota_final, 
                i.estado, 
                i.fecha_inscripcion,
                CASE 
                    WHEN i.nota_final >= 90 THEN "A"
                    WHEN i.nota_final >= 80 THEN "B"
                    WHEN i.nota_final >= 70 THEN "C"
                    WHEN i.nota_final >= 60 THEN "D"
                    ELSE "F"
                END AS calificacion,
                CONCAT(d.nombre, " ", d.apellido) AS docente,
                d.especialidad
             FROM inscripciones i
             INNER JOIN cursos c ON i.curso_id = c.id
             LEFT JOIN docentes d ON c.docente_id = d.id
             WHERE i.estudiante_id = :id
             ORDER BY i.fecha_inscripcion DESC',
            [':id' => $estudianteId]
        );
        
        return $stmt->fetchAll() ?: [];
    }

    /**
     * Inscribe un estudiante en un curso (con validación de cupos)
     * 
     * @param int $estudianteId ID del estudiante
     * @param int $cursoId ID del curso
     * @return bool true si la inscripción fue exitosa
     * @throws RuntimeException Si no hay cupos disponibles o hay error
     */
    public function inscribir(int $estudianteId, int $cursoId): bool {
        // Verificar que el estudiante existe
        $estudiante = $this->db->queryOne(
            'SELECT id FROM estudiantes WHERE id = :id',
            [':id' => $estudianteId]
        );
        
        if (!$estudiante) {
            throw new RuntimeException('Estudiante no encontrado.');
        }

        // Verificar cupos disponibles
        $curso = $this->db->queryOne(
            'SELECT id, capacidad_maxima, matriculados, nombre FROM cursos WHERE id = :id',
            [':id' => $cursoId]
        );

        if (!$curso) {
            throw new RuntimeException('Curso no encontrado.');
        }

        if ($curso['matriculados'] >= $curso['capacidad_maxima']) {
            throw new RuntimeException(
                'No hay cupos disponibles en el curso "' . $curso['nombre'] . '". ' .
                'Capacidad: ' . $curso['capacidad_maxima'] . ' estudiantes.'
            );
        }

        // Verificar que no esté ya inscrito
        $existente = $this->db->queryOne(
            'SELECT id FROM inscripciones WHERE estudiante_id = :e AND curso_id = :c',
            [':e' => $estudianteId, ':c' => $cursoId]
        );
        
        if ($existente) {
            throw new RuntimeException('El estudiante ya está inscrito en este curso.');
        }

        try {
            // Iniciar transacción
            $this->db->beginTransaction();

            // Insertar inscripción
            $this->db->execute(
                'INSERT INTO inscripciones (estudiante_id, curso_id, fecha_inscripcion) 
                 VALUES (:e, :c, CURDATE())',
                [':e' => $estudianteId, ':c' => $cursoId]
            );

            // Actualizar contador de matriculados
            $this->db->execute(
                'UPDATE cursos SET matriculados = matriculados + 1 WHERE id = :id',
                [':id' => $cursoId]
            );

            // Confirmar transacción
            $this->db->commit();

            return true;
        } catch (Exception $e) {
            $this->db->rollBack();
            throw new RuntimeException("Error al inscribir: " . $e->getMessage());
        }
    }

    /**
     * Obtiene el rendimiento del estudiante (usa la vista)
     * 
     * @param int $estudianteId ID del estudiante
     * @return array|null Datos de rendimiento o null si no existe
     */
    public function rendimiento(int $estudianteId): ?array {
        return $this->db->queryOne(
            'SELECT * FROM vista_rendimiento_estudiante WHERE id = :id',
            [':id' => $estudianteId]
        );
    }

    /**
     * Retira a un estudiante de un curso
     * 
     * @param int $estudianteId ID del estudiante
     * @param int $cursoId ID del curso
     * @return bool true si se retiró exitosamente
     */
    public function retirar(int $estudianteId, int $cursoId): bool {
        try {
            $this->db->beginTransaction();

            // Marcar inscripción como retirada
            $this->db->execute(
                'UPDATE inscripciones SET estado = "retirada" 
                 WHERE estudiante_id = :e AND curso_id = :c',
                [':e' => $estudianteId, ':c' => $cursoId]
            );

            // Reducir contador de matriculados
            $this->db->execute(
                'UPDATE cursos SET matriculados = matriculados - 1 WHERE id = :id AND matriculados > 0',
                [':id' => $cursoId]
            );

            $this->db->commit();
            return true;
        } catch (Exception $e) {
            $this->db->rollBack();
            throw new RuntimeException("Error al retirar: " . $e->getMessage());
        }
    }

    /**
     * Obtiene la lista de cursos disponibles con información de cupos
     * 
     * @param int|null $semestre Filtrar por semestre (opcional)
     * @return array Array de cursos disponibles
     */
    public function cursosDisponibles(?int $semestre = null): array {
        $sql = 'SELECT 
                    c.id,
                    c.codigo,
                    c.nombre,
                    c.descripcion,
                    c.creditos,
                    c.capacidad_maxima,
                    c.matriculados,
                    (c.capacidad_maxima - c.matriculados) AS cupos_disponibles,
                    c.semestre,
                    CONCAT(d.nombre, " ", d.apellido) AS docente,
                    d.especialidad
                FROM cursos c
                LEFT JOIN docentes d ON c.docente_id = d.id
                WHERE c.estado = "activo"';
        
        $params = [];
        if ($semestre !== null) {
            $sql .= ' AND c.semestre = :semestre';
            $params[':semestre'] = $semestre;
        }
        
        $sql .= ' ORDER BY c.semestre, c.nombre';
        
        $stmt = $this->db->query($sql, $params);
        return $stmt->fetchAll() ?: [];
    }

    /**
     * Obtiene un historial completo de inscripciones con JOIN de estudiante + cursos + docente
     * 
     * @return array Historial completo de todas las inscripciones
     */
    public function historiaiCompleto(): array {
        $stmt = $this->db->query(
            'SELECT
                i.id as inscripcion_id,
                e.id as estudiante_id,
                e.codigo as codigo_estudiante,
                CONCAT(e.nombre, " ", e.apellido_paterno, " ", e.apellido_materno) AS estudiante_nombre,
                e.email as estudiante_email,
                e.semestre as estudiante_semestre,
                
                c.id as curso_id,
                c.codigo as codigo_curso,
                c.nombre as nombre_curso,
                c.creditos,
                c.semestre as curso_semestre,
                
                d.id as docente_id,
                CONCAT(d.nombre, " ", d.apellido) as docente_nombre,
                d.especialidad,
                d.email as docente_email,
                
                i.nota_final,
                i.estado,
                i.fecha_inscripcion,
                CASE 
                    WHEN i.nota_final IS NULL THEN "-"
                    WHEN i.nota_final >= 90 THEN "A"
                    WHEN i.nota_final >= 80 THEN "B"
                    WHEN i.nota_final >= 70 THEN "C"
                    WHEN i.nota_final >= 60 THEN "D"
                    ELSE "F"
                END AS calificacion
             FROM inscripciones i
             INNER JOIN estudiantes e ON i.estudiante_id = e.id
             INNER JOIN cursos c ON i.curso_id = c.id
             LEFT JOIN docentes d ON c.docente_id = d.id
             ORDER BY i.fecha_inscripcion DESC'
        );

        return $stmt->fetchAll() ?: [];
    }

    /**
     * Obtiene estadísticas de inscripciones
     * 
     * @return array Estadísticas globales
     */
    public function estadisticas(): array {
        $total_inscripciones = $this->db->queryOne(
            'SELECT COUNT(*) as total FROM inscripciones',
            []
        )['total'] ?? 0;

        $inscritos_activos = $this->db->queryOne(
            'SELECT COUNT(*) as total FROM inscripciones WHERE estado = "activa"',
            []
        )['total'] ?? 0;

        $completados = $this->db->queryOne(
            'SELECT COUNT(*) as total FROM inscripciones WHERE estado = "completada"',
            []
        )['total'] ?? 0;

        $retirados = $this->db->queryOne(
            'SELECT COUNT(*) as total FROM inscripciones WHERE estado = "retirada"',
            []
        )['total'] ?? 0;

        $promedio_general = $this->db->queryOne(
            'SELECT ROUND(AVG(nota_final), 2) as promedio FROM inscripciones WHERE nota_final IS NOT NULL',
            []
        )['promedio'] ?? 0;

        return [
            'total_inscripciones' => $total_inscripciones,
            'activas' => $inscritos_activos,
            'completadas' => $completados,
            'retiradas' => $retirados,
            'promedio_general' => $promedio_general,
        ];
    }
}
