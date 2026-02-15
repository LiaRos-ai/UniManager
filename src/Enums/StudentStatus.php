<?php
declare(strict_types=1);

namespace App\Enums;

/**
 * Estados de estudiante
 */
enum StudentStatus: string {
    case ACTIVE = 'active';
    case INACTIVE = 'inactive';
    case GRADUATED = 'graduated';
    case SUSPENDED = 'suspended';

    public function label(): string {
        return match($this) {
            self::ACTIVE => 'Activo',
            self::INACTIVE => 'Inactivo',
            self::GRADUATED => 'Graduado',
            self::SUSPENDED => 'Suspendido',
        };
    }

    public function color(): string {
        return match($this) {
            self::ACTIVE => 'green',
            self::INACTIVE => 'gray',
            self::GRADUATED => 'blue',
            self::SUSPENDED => 'red',
        };
    }
}
