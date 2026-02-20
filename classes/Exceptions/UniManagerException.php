<?php
declare(strict_types=1);

namespace UniManager\Exceptions;

// Excepción base
class UniManagerException extends \Exception {
    public function __construct(
        string $message,
        private array $context = []
    ) {
        parent::__construct($message);
    }
    
    public function getContext(): array {
        return $this->context;
    }
}

// Excepciones específicas
class UsuarioNoEncontradoException extends UniManagerException {
    public function __construct(int $userId) {
        parent::__construct(
            "Usuario con ID $userId no encontrado",
            ['user_id' => $userId]
        );
    }
}

class CursoNoEncontradoException extends UniManagerException {
    public function __construct(int $cursoId) {
        parent::__construct(
            "Curso con ID $cursoId no encontrado",
            ['curso_id' => $cursoId]
        );
    }
}

class MatriculaDuplicadaException extends UniManagerException {
    public function __construct(int $estudianteId, int $cursoId) {
        parent::__construct(
            "El estudiante ya está matriculado en este curso",
            [
                'estudiante_id' => $estudianteId,
                'curso_id' => $cursoId
            ]
        );
    }
}

class CupoLlenoException extends UniManagerException {
    public function __construct(string $curso) {
        parent::__construct(
            "El curso '$curso' no tiene cupos disponibles",
            ['curso' => $curso]
        );
    }
}

class PermisosDenegadosException extends UniManagerException {
    public function __construct(string $accion, string $rol) {
        parent::__construct(
            "El rol '$rol' no tiene permisos para: $accion",
            ['accion' => $accion, 'rol' => $rol]
        );
    }
}

class ValidationException extends UniManagerException {
    public function __construct(array $errores) {
        $mensaje = "Errores de validación: " . implode(', ', array_keys($errores));
        parent::__construct($mensaje, ['errores' => $errores]);
    }
    
    public function getErrores(): array {
        return $this->getContext()['errores'];
    }
}
?>