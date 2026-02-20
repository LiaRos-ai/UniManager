<?php
declare(strict_types=1);

class Estudiante {
    public function __construct(
        private int $id,
        private string $nombre,
        private string $apellido,
        private string $email,
        private string $carrera
    ) {}

    public function getId(): int {
        return $this->id;
    }

    public function getNombreCompleto(): string {
        return "{$this->nombre} {$this->apellido}";
    }

    public function getEmail(): string {
        return $this->email;
    }

    public function getCarrera(): string {
        return $this->carrera;
    }
}
?>