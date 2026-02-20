<?php
declare(strict_types=1);

namespace App\Models;

use App\Enums\StudentStatus;

/**
 * Modelo de Estudiante
 */
class Student {
    public function __construct(
        private ?int $id = null,
        private string $codigo = '',
        private string $nombre = '',
        private string $apellidoPaterno = '',
        private string $apellidoMaterno = '',
        private string $email = '',
        private string $ci = '',
        private ?string $telefono = null,
        private int $semestre = 1,
        private StudentStatus $estado = StudentStatus::ACTIVE,
        private array $notas = []
    ) {}

    // GETTERS
    public function getId(): ?int {
        return $this->id;
    }

    public function getCodigo(): string {
        return $this->codigo;
    }

    public function getNombre(): string {
        return $this->nombre;
    }

    public function getApellidoPaterno(): string {
        return $this->apellidoPaterno;
    }

    public function getApellidoMaterno(): string {
        return $this->apellidoMaterno;
    }

    public function getNombreCompleto(): string {
        return trim("{$this->nombre} {$this->apellidoPaterno} {$this->apellidoMaterno}");
    }

    public function getEmail(): string {
        return $this->email;
    }

    public function getCi(): string {
        return $this->ci;
    }

    public function getTelefono(): ?string {
        return $this->telefono;
    }

    public function getSemestre(): int {
        return $this->semestre;
    }

    public function getEstado(): StudentStatus {
        return $this->estado;
    }

    public function getNotas(): array {
        return $this->notas;
    }

    public function getPromedio(): float {
        if (empty($this->notas)) {
            return 0.0;
        }
        return (float)array_sum($this->notas) / count($this->notas);
    }

    // SETTERS
    public function setId(?int $id): self {
        $this->id = $id;
        return $this;
    }

    public function setCodigo(string $codigo): self {
        $this->codigo = $codigo;
        return $this;
    }

    public function setNombre(string $nombre): self {
        $this->nombre = $nombre;
        return $this;
    }

    public function setApellidoPaterno(string $apellidoPaterno): self {
        $this->apellidoPaterno = $apellidoPaterno;
        return $this;
    }

    public function setApellidoMaterno(string $apellidoMaterno): self {
        $this->apellidoMaterno = $apellidoMaterno;
        return $this;
    }

    public function setEmail(string $email): self {
        $this->email = $email;
        return $this;
    }

    public function setCi(string $ci): self {
        $this->ci = $ci;
        return $this;
    }

    public function setTelefono(?string $telefono): self {
        $this->telefono = $telefono;
        return $this;
    }

    public function setSemestre(int $semestre): self {
        $this->semestre = $semestre;
        return $this;
    }

    public function setEstado(StudentStatus $estado): self {
        $this->estado = $estado;
        return $this;
    }

    public function setNotas(array $notas): self {
        $this->notas = $notas;
        return $this;
    }

    public function agregarNota(float $nota): self {
        $this->notas[] = $nota;
        return $this;
    }

    // MÉTODOS DE LÓGICA
    public function estaActivo(): bool {
        return $this->estado === StudentStatus::ACTIVE;
    }

    public function estaAprobado(): bool {
        return $this->getPromedio() >= 60;
    }

    public function generarReporte(): array {
        $stats = [];
        if (!empty($this->notas)) {
            $stats = [
                'promedio' => round($this->getPromedio(), 2),
                'mejor_nota' => max($this->notas),
                'peor_nota' => min($this->notas),
                'mediana' => $this->calcularMediana($this->notas),
                'notas' => $this->notas,
                'aprobado' => $this->estaAprobado(),
                'letra' => $this->obtenerLetra(),
            ];
        }

        return [
            'estudiante' => [
                'id' => $this->id,
                'codigo' => $this->codigo,
                'nombre_completo' => $this->getNombreCompleto(),
                'email' => $this->email,
                'ci' => $this->ci,
                'semestre' => $this->semestre,
                'estado' => $this->estado->label(),
            ],
            'academico' => $stats
        ];
    }

    private function calcularMediana(array $notas): float {
        if (empty($notas)) return 0;
        
        sort($notas);
        $n = count($notas);
        $medio = (int)($n / 2);
        
        return $n % 2 === 0
            ? ($notas[$medio - 1] + $notas[$medio]) / 2
            : $notas[$medio];
    }

    private function obtenerLetra(): string {
        $promedio = $this->getPromedio();
        return match(true) {
            $promedio >= 90 => 'A',
            $promedio >= 80 => 'B',
            $promedio >= 70 => 'C',
            $promedio >= 60 => 'D',
            default => 'F'
        };
    }

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
            'promedio' => $this->getPromedio(),
        ];
    }
}