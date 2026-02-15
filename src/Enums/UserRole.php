<?php
declare(strict_types=1);

namespace App\Enums;

/**
 * Roles de usuario en el sistema
 */
enum UserRole: string {
    case ADMIN = 'admin';
    case TEACHER = 'teacher';
    case STUDENT = 'student';

    /**
     * Obtiene el nombre legible del rol
     */
    public function label(): string {
        return match($this) {
            self::ADMIN => 'Administrador',
            self::TEACHER => 'Docente',
            self::STUDENT => 'Estudiante',
        };
    }

    /**
     * Verifica si tiene permisos administrativos
     */
    public function isAdmin(): bool {
        return $this === self::ADMIN;
    }

    /**
     * Verifica si es docente
     */
    public function isTeacher(): bool {
        return $this === self::TEACHER;
    }
}