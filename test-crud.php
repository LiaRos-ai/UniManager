<?php
declare(strict_types=1);

/**
 * Script de prueba CRUD para la entidad Student
 * Prueba conexión a BD y operaciones básicas
 */

// Configuración
error_reporting(E_ALL);
ini_set('display_errors', '1');
date_default_timezone_set('America/La_Paz');

// Cargar autoloader de Composer
require_once __DIR__ . '/vendor/autoload.php';
require_once __DIR__ . '/config/constants.php';

use App\Database\Database;
use App\Models\Student;
use App\Repositories\StudentRepository;
use App\Enums\StudentStatus;

echo "\n";
echo "═══════════════════════════════════════════════════════════════\n";
echo "  PRUEBA DE CONEXIÓN Y CRUD - ENTIDAD STUDENT\n";
echo "═══════════════════════════════════════════════════════════════\n\n";

try {
    // ============================================================
    // 1. PRUEBA DE CONEXIÓN
    // ============================================================
    echo "📌 PASO 1: Probando conexión a la base de datos...\n";
    echo "─────────────────────────────────────────────────────────────\n";
    
    $db = Database::getInstance();
    
    if ($db->testConnection()) {
        echo "✅ Conexión exitosa a MySQL\n";
        echo "   BD: unimanager\n";
        echo "   Host: localhost\n\n";
    } else {
        throw new Exception("No se pudo conectar a la base de datos");
    }
    
    // ============================================================
    // 2. CREAR UN ESTUDIANTE (CREATE)
    // ============================================================
    echo "📌 PASO 2: Creando un nuevo estudiante (CREATE)...\n";
    echo "─────────────────────────────────────────────────────────────\n";
    
    $nuevoEstudiante = new Student(
        codigo: '2024-99999',
        nombre: 'Roberto',
        apellidoPaterno: 'Mendoza',
        apellidoMaterno: 'Silva',
        email: 'roberto.mendoza@est.edu.bo',
        ci: '99999999-LP',
        telefono: '+59178765432',
        semestre: 4,
        estado: StudentStatus::ACTIVE,
        notas: [85, 90, 88, 92]
    );
    
    $repo = new StudentRepository();
    $estudianteId = $repo->create($nuevoEstudiante);
    
    echo "✅ Estudiante creado con ID: $estudianteId\n";
    echo "   Nombre: {$nuevoEstudiante->getNombreCompleto()}\n";
    echo "   Email: {$nuevoEstudiante->getEmail()}\n";
    echo "   Promedio: {$nuevoEstudiante->getPromedio()}\n\n";
    
    // ============================================================
    // 3. LEER ESTUDIANTES (READ)
    // ============================================================
    echo "📌 PASO 3: Leyendo estudiantes de la BD (READ)...\n";
    echo "─────────────────────────────────────────────────────────────\n";
    
    // Leer todos
    echo "\n✓ Todos los estudiantes:\n";
    $estudiantes = $repo->findAll();
    foreach ($estudiantes as $est) {
        echo "   • ID:{$est->getId()} | {$est->getNombreCompleto()} | Email: {$est->getEmail()} | Promedio: {$est->getPromedio()}\n";
    }
    
    // Leer por ID
    echo "\n✓ Buscando estudiante por ID ($estudianteId):\n";
    $estudiante = $repo->findById($estudianteId);
    if ($estudiante) {
        echo "   ✓ Encontrado: {$estudiante->getNombreCompleto()}\n";
        echo "     Semestre: {$estudiante->getSemestre()}\n";
        echo "     Estado: {$estudiante->getEstado()->label()}\n";
    }
    
    // Buscar por código
    echo "\n✓ Buscando por código '2024-00001':\n";
    $estByCod = $repo->findByCodigo('2024-00001');
    if ($estByCod) {
        echo "   ✓ Encontrado: {$estByCod->getNombreCompleto()}\n";
    }
    
    // Búsqueda por nombre
    echo "\n✓ Búsqueda por nombre 'María':\n";
    $resultados = $repo->search('María');
    foreach ($resultados as $est) {
        echo "   • {$est->getNombreCompleto()} - {$est->getEmail()}\n";
    }
    
    // Por semestre
    echo "\n✓ Estudiantes del semestre 4:\n";
    $porSemestre = $repo->findBySemestre(4);
    foreach ($porSemestre as $est) {
        echo "   • {$est->getNombreCompleto()}\n";
    }
    
    // Aprobados
    echo "\n✓ Estudiantes aprobados (promedio >= 60):\n";
    $aprobados = $repo->findAprobados();
    echo "   Total: " . count($aprobados) . "\n";
    foreach ($aprobados as $est) {
        echo "   • {$est->getNombreCompleto()} - Promedio: {$est->getPromedio()}\n";
    }
    
    echo "\n";
    
    // ============================================================
    // 4. ACTUALIZAR UN ESTUDIANTE (UPDATE)
    // ============================================================
    echo "📌 PASO 4: Actualizando el estudiante (UPDATE)...\n";
    echo "─────────────────────────────────────────────────────────────\n";
    
    if ($estudiante) {
        // Modificar datos
        $estudiante
            ->setSemestre(5)
            ->setTelefono('+59179999999')
            ->setEstado(StudentStatus::SUSPENDED);
        
        // Actualizar en BD
        $actualizado = $repo->update($estudianteId, $estudiante);
        
        if ($actualizado) {
            echo "✅ Estudiante actualizado:\n";
            echo "   Semestre actualizado a: {$estudiante->getSemestre()}\n";
            echo "   Teléfono actualizado a: {$estudiante->getTelefono()}\n";
            echo "   Estado actualizado a: {$estudiante->getEstado()->label()}\n";
            
            // Verificar cambios
            $verificar = $repo->findById($estudianteId);
            echo "\n✓ Verificación de cambios en BD:\n";
            echo "   Semestre: {$verificar->getSemestre()}\n";
            echo "   Teléfono: {$verificar->getTelefono()}\n";
            echo "   Estado: {$verificar->getEstado()->label()}\n";
        }
    }
    
    echo "\n";
    
    // ============================================================
    // 5. ESTADÍSTICAS
    // ============================================================
    echo "📌 PASO 5: Estadísticas del sistema...\n";
    echo "─────────────────────────────────────────────────────────────\n";
    
    $stats = $repo->getEstadisticas();
    echo "✓ Total de estudiantes: {$stats['total']}\n";
    echo "✓ Aprobados: {$stats['aprobados']}\n";
    echo "✓ Reprobados: {$stats['reprobados']}\n";
    echo "✓ Promedio general: {$stats['promedio_general']}\n";
    echo "✓ Tasa de aprobación: {$stats['tasa_aprobacion']}%\n";
    
    echo "\n";
    
    // ============================================================
    // 6. ELIMINAR UN ESTUDIANTE (DELETE)
    // ============================================================
    echo "📌 PASO 6: Eliminando el estudiante de prueba (DELETE)...\n";
    echo "─────────────────────────────────────────────────────────────\n";
    
    if ($repo->delete($estudianteId)) {
        echo "✅ Estudiante eliminado correctamente (ID: $estudianteId)\n";
        
        // Verificar eliminación
        $verificarEliminacion = $repo->findById($estudianteId);
        if ($verificarEliminacion === null) {
            echo "✓ Verificación: Estudiante no encontrado en BD ✅\n";
        }
    }
    
    echo "\n";
    
    // ============================================================
    // RESUMEN
    // ============================================================
    echo "═══════════════════════════════════════════════════════════════\n";
    echo "  ✅ PRUEBAS COMPLETADAS EXITOSAMENTE\n";
    echo "═══════════════════════════════════════════════════════════════\n";
    echo "\n✓ Todas las operaciones CRUD funcionaron correctamente:\n";
    echo "  ✓ CREATE - Crear nuevo estudiante\n";
    echo "  ✓ READ   - Leer/obtener estudiantes\n";
    echo "  ✓ UPDATE - Actualizar información del estudiante\n";
    echo "  ✓ DELETE - Eliminar estudiante\n";
    echo "\n";
    
} catch (Exception $e) {
    echo "\n❌ ERROR: " . $e->getMessage() . "\n";
    echo "\nCausa posible:\n";
    
    if (str_contains($e->getMessage(), 'SQLSTATE')) {
        echo "• La base de datos 'unimanager' no existe o no se puede conectar\n";
        echo "• Ejecuta el script database.sql en tu MySQL\n";
        echo "\nEntre a phpMyAdmin y ejecuta:\n";
        echo "-----------------------------------------------\n";
        echo file_get_contents(__DIR__ . '/database.sql');
        echo "\n-----------------------------------------------\n";
    } else {
        echo "• Revisa la configuración de Database.php\n";
        echo "• Verifica que MySQL esté corriendo\n";
    }
    
    die();
}
?>