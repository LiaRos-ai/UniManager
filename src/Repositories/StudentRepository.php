<?php
declare(strict_types=1);

namespace App\Repositories;

use App\Models\Student;
use App\Enums\StudentStatus;

/**
 * Repositorio de estudiantes (simulado en memoria)
 * En el Día 9 conectaremos a base de datos real
 */
class StudentRepository {
    private array $students = [];
    private int $nextId = 1;
    
    public function __construct() {
        $this->seedData();
    }
    
    /**
     * Datos de prueba
     */
    private function seedData(): void {
        $estudiantes = [
            ['Ana', 'García', 'Pérez', 'ana.garcia@est.edu.bo', '12345678-LP', '+59178451234', 7, [85, 90, 88, 92]],
            ['Luis', 'Pérez', 'Mamani', 'luis.perez@est.edu.bo', '23456789-SC', '+59176542345', 6, [78, 82, 85, 80]],
            ['María', 'López', 'Quispe', 'maria.lopez@est.edu.bo', '34567890-CB', '+59172345678', 5, [95, 92, 98, 94]],
            ['Carlos', 'Ruiz', 'Condori', 'carlos.ruiz@est.edu.bo', '45678901-LP', '+59178234567', 7, [65, 70, 68, 72]],
            ['Sofia', 'Torres', 'Apaza', 'sofia.torres@est.edu.bo', '56789012-SC', null, 4, [58, 62, 65, 60]],
        ];
        
        foreach ($estudiantes as $i => $data) {
            [$nombre, $paterno, $materno, $email, $ci, $tel, $sem, $notas] = $data;
            
            $student = new Student(
                id: $this->nextId++,
                codigo: sprintf('2024-%05d', $i + 1),
                nombre: $nombre,
                apellidoPaterno: $paterno,
                apellidoMaterno: $materno,
                email: $email,
                ci: $ci,
                telefono: $tel,
                semestre: $sem,
                estado: $i === 4 ? StudentStatus::INACTIVE : StudentStatus::ACTIVE,
                notas: $notas
            );
            
            $this->students[$student->getId()] = $student;
        }
    }
    
    /**
     * Obtiene todos los estudiantes
     */
    public function findAll(): array {
        return array_values($this->students);
    }
    
    /**
     * Busca por ID
     */
    public function findById(int $id): ?Student {
        return $this->students[$id] ?? null;
    }
    
    /**
     * Busca por código
     */
    public function findByCodigo(string $codigo): ?Student {
        $resultado = array_filter(
            $this->students,
            fn($s) => $s->getCodigo() === $codigo
        );
        return !empty($resultado) ? array_values($resultado)[0] : null;
    }
    
    /**
     * Busca por nombre
     */
    public function search(string $query): array {
        return array_filter($this->students, function($student) use ($query) {
            $nombreCompleto = strtolower($student->getNombreCompleto());
            $busqueda = strtolower($query);
            return str_contains($nombreCompleto, $busqueda);
        });
    }
    
    /**
     * Filtra por semestre
     */
    public function findBySemestre(int $semestre): array {
        return array_filter(
            $this->students,
            fn($s) => $s->getSemestre() === $semestre
        );
    }
    
    /**
     * Filtra por estado
     */
    public function findByEstado(StudentStatus $estado): array {
        return array_filter(
            $this->students,
            fn($s) => $s->getEstado() === $estado
        );
    }
    
    /**
     * Obtiene estudiantes aprobados
     */
    public function findAprobados(): array {
        return array_filter(
            $this->students,
            fn($s) => $s->estaAprobado()
        );
    }
    
    /**
     * Obtiene estudiantes reprobados
     */
    public function findReprobados(): array {
        return array_filter(
            $this->students,
            fn($s) => !$s->estaAprobado()
        );
    }
    
    /**
     * Ordena por promedio
     */
    public function orderByPromedio(bool $descendente = true): array {
        $students = $this->students;
        usort($students, function($a, $b) use ($descendente) {
            $comp = $a->getPromedio() <=> $b->getPromedio();
            return $descendente ? -$comp : $comp;
        });
        return $students;
    }
    
    /**
     * Obtiene estadísticas generales
     */
    public function getEstadisticas(): array {
        $total = count($this->students);
        $aprobados = count($this->findAprobados());
        
        $promedios = array_map(fn($s) => $s->getPromedio(), $this->students);
        $promedioGeneral = !empty($promedios) 
            ? array_sum($promedios) / count($promedios) 
            : 0;
        
        return [
            'total' => $total,
            'aprobados' => $aprobados,
            'reprobados' => $total - $aprobados,
            'promedio_general' => $promedioGeneral,
            'tasa_aprobacion' => $total > 0 ? ($aprobados / $total) * 100 : 0
        ];
    }
}