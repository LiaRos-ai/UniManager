<?php
declare(strict_types=1);

class Curso {
    public function __construct(
        private int $id,
        private string $codigo,
        private string $nombre,
        private int $creditos,
        private int $capacidadMaxima,
        private int $matriculados = 0
    ) {}

    public function getId(): int {
        return $this->id;
    }

    public function getCodigo(): string {
        return $this->codigo;
    }

    public function getNombre(): string {
        return $this->nombre;
    }

    public function getCreditos(): int {
        return $this->creditos;
    }

    public function hayEspacio(): bool {
        return $this->matriculados < $this->capacidadMaxima;
    }

    public function matricular(): bool {
        if (!$this->hayEspacio()) {
            return false;
        }
        $this->matriculados++;
        return true;
    }

    public function getDisponibilidad(): string {
        $disponibles = $this->capacidadMaxima - $this->matriculados;
        return "{$disponibles}/{$this->capacidadMaxima} cupos";
    }
}
?>