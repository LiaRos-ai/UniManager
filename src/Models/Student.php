<?php
declare(strict_types=1);

namespace App\Models;

use App\Enums\StudentStatus;

/**
 * Modelo de Estudiante
 */
class Student {
    public function __construct(
        private ?int $id,
        private string $codigo,
        private string $nombre,
        private string $apellidoPaterno,
        private string $apellidoMaterno,
        private string $email,
        private string $ci,
        private ?string $telefono,
        private int $semestre,
        private StudentStatus $estado,
        private array $notas = []
    ) {}
    
    // Getters
    public function getId(): ?int { return $this->id; }
    public function getCodigo(): string { return $this->codigo; }
    public function getNombre(): string { return $this->nombre; }
    public function getApellidoPaterno(): string { return $this->apellidoPaterno; }
    public function getApellidoMaterno(): string { return $this->apellidoMaterno; }
    public function getEmail(): string { return $this->email; }
    public function getCi(): string { return $this->ci; }
    public function getTelefono(): ?string { return $this->telefono; }
    public function getSemestre(): int { return $this->semestre; }
    public function getEstado(): StudentStatus { return $this->estado; }
    public function getNotas(): array { return $this->notas; }
    
    /**
     * Obtiene el nombre completo
     */
    public function getNombreCompleto(): string {
        return "{$this->nombre} {$this->apellidoPaterno} {$this->apellidoMaterno}";
    }
    
    /**
     * Agrega una nota
     */
    public function agregarNota(float $nota): void {
        $this->notas[] = $nota;
    }
    
    /**
     * Calcula el promedio de notas
     */
    public function getPromedio(): float {
        if (empty($this->notas)) {
            return 0.0;
        }
        return array_sum($this->notas) / count($this->notas);
    }
    
    /**
     * Verifica si está aprobado
     */
    public function estaAprobado(): bool {
        return $this->getPromedio() >= 60;
    }
    
    /**
     * Verifica si está activo
     */
    public function estaActivo(): bool {
        return $this->estado === StudentStatus::ACTIVE;
    }
    
    /**
     * Convierte a array
     */
    public function toArray(): array {
        return [
            'id' => $this->id,
            'codigo' => $this->codigo,
            'nombre' => $this->nombre,
            'apellido_paterno' => $this->apellidoPaterno,
            'apellido_materno' => $this->apellidoMaterno,
            'nombre_completo' => $this->getNombreCompleto(),
            'email' => $this->email,
            'ci' => $this->ci,
            'telefono' => $this->telefono,
            'semestre' => $this->semestre,
            'estado' => $this->estado->value,
            'estado_label' => $this->estado->label(),
            'notas' => $this->notas,
            'promedio' => $this->getPromedio(),
            'aprobado' => $this->estaAprobado(),
            'activo' => $this->estaActivo()
        ];
    }

    /**
     * Genera reporte académico completo
     */
    public function generarReporte(): array {
        $calculator = new \App\Services\AcademicCalculator();
        $stats = $calculator->calcularEstadisticas($this->notas);
        
        return [
            'estudiante' => [
                'id' => $this->id,
                'codigo' => $this->codigo,
                'nombre_completo' => $this->getNombreCompleto(),
                'email' => $this->email,
                'semestre' => $this->semestre
            ],
            'academico' => [
                'notas' => $this->notas,
                'promedio' => $stats['promedio'],
                'letra' => $calculator->obtenerLetra($stats['promedio']),
                'aprobado' => $calculator->aprobo($stats['promedio']),
                'mejor_nota' => $stats['maximo'],
                'peor_nota' => $stats['minimo'],
                'mediana' => $stats['mediana']
            ],
            'estado' => [
                'activo' => $this->estaActivo(),
                'estado' => $this->estado->label()
            ]
        ];
    }
}