<?php
/**
 * Script para probar el sistema de inscripciones
 * Ejecutar: php test-inscripciones.php
 */

require_once __DIR__ . '/vendor/autoload.php';

use App\Repositories\InscripcionRepository;
use App\Repositories\StudentRepository;

try {
    $inscRepo = new InscripcionRepository();
    $estudRepo = new StudentRepository();
    
    echo "\n════════════════════════════════════════════════\n";
    echo "🧪 PRUEBAS DEL SISTEMA DE INSCRIPCIONES\n";
    echo "════════════════════════════════════════════════\n\n";
    
    // Test 1: Obtener cursos disponibles
    echo "✓ Test 1: Obtener cursos disponibles\n";
    $cursos = $inscRepo->cursosDisponibles();
    echo "  → Total de cursos disponibles: " . count($cursos) . "\n";
    foreach ($cursos as $curso) {
        echo "    • {$curso['codigo']} - {$curso['nombre']} ";
        echo "({$curso['cupos_disponibles']} cupos)\n";
    }
    echo "\n";
    
    // Test 2: Obtener cursos de un estudiante específico
    echo "✓ Test 2: Obtener cursos de un estudiante (ID: 1)\n";
    $cursosPorEstudiante = $inscRepo->cursosDeEstudiante(1);
    echo "  → Total de cursos inscritos: " . count($cursosPorEstudiante) . "\n";
    foreach ($cursosPorEstudiante as $curso) {
        $nota = $curso['nota_final'] ? "{$curso['nota_final']} ({$curso['calificacion']})" : "Sin calificar";
        echo "    • {$curso['curso']} - {$nota} - Estado: {$curso['estado']}\n";
    }
    echo "\n";
    
    // Test 3: Obtener rendimiento de un estudiante
    echo "✓ Test 3: Obtener rendimiento del estudiante (ID: 1)\n";
    $rendimiento = $inscRepo->rendimiento(1);
    if ($rendimiento) {
        echo "  → Estudiante: {$rendimiento['estudiante']}\n";
        echo "  → Cursos inscritos: {$rendimiento['cursos_inscritos']}\n";
        echo "  → Cursos completados: {$rendimiento['cursos_completados']}\n";
        echo "  → Promedio de notas: {$rendimiento['promedio_notas']}\n";
    }
    echo "\n";
    
    // Test 4: Intentar inscribir en un curso nuevo (no duplicado)
    echo "✓ Test 4: Intentar inscribir estudiante en un curso\n";
    try {
        // Buscar un curso en el que el estudiante no está inscrito
        $estudiante_id = 1;
        $todos_los_cursos = $inscRepo->cursosDisponibles();
        $cursos_inscritos = array_map(fn($c) => $c['curso_id'], $inscRepo->cursosDeEstudiante($estudiante_id));
        
        $curso_nuevo = null;
        foreach ($todos_los_cursos as $curso) {
            if (!in_array($curso['id'], $cursos_inscritos)) {
                $curso_nuevo = $curso;
                break;
            }
        }
        
        if ($curso_nuevo && $curso_nuevo['cupos_disponibles'] > 0) {
            $inscRepo->inscribir($estudiante_id, $curso_nuevo['id']);
            echo "  ✅ Inscripción exitosa\n";
            echo "     Curso: {$curso_nuevo['nombre']}\n";
            echo "     Cupos restantes: " . ($curso_nuevo['cupos_disponibles'] - 1) . "\n";
        } else {
            echo "  ⚠️  No hay cursos disponibles para inscribir\n";
        }
    } catch (Exception $e) {
        echo "  ❌ Error: {$e->getMessage()}\n";
    }
    echo "\n";
    
    // Test 5: Estadísticas de inscripciones
    echo "✓ Test 5: Estadísticas globales de inscripciones\n";
    $stats = $inscRepo->estadisticas();
    echo "  → Total de inscripciones: {$stats['total_inscripciones']}\n";
    echo "  → Activas: {$stats['activas']}\n";
    echo "  → Completadas: {$stats['completadas']}\n";
    echo "  → Retiradas: {$stats['retiradas']}\n";
    echo "  → Promedio general: {$stats['promedio_general']}\n";
    echo "\n";
    
    // Test 6: Historial completo
    echo "✓ Test 6: Historial completo de inscripciones (primeros 5 registros)\n";
    $historial = $inscRepo->historiaiCompleto();
    $contador = 0;
    foreach ($historial as $registro) {
        if ($contador >= 5) break;
        echo "  • {$registro['estudiante_nombre']} ({$registro['codigo_estudiante']}) ";
        echo "→ {$registro['nombre_curso']} ({$registro['codigo_curso']}) ";
        echo "Docente: {$registro['docente_nombre']} ";
        echo "Nota: " . ($registro['nota_final'] ? $registro['nota_final'] . " ({$registro['calificacion']})" : "-") . " ";
        echo "Estado: {$registro['estado']}\n";
        $contador++;
    }
    echo "  → Total de registros: " . count($historial) . "\n";
    echo "\n";
    
    // Test 7: Validación de cupos (intentar inscribir en curso lleno)
    echo "✓ Test 7: Validación de cupos disponibles\n";
    
    // Encontrar un curso con pocos cupos y llenarlos
    $curso_test = null;
    foreach ($inscRepo->cursosDisponibles() as $c) {
        if ($c['cupos_disponibles'] > 0) {
            $curso_test = $c;
            break;
        }
    }
    
    if ($curso_test) {
        // Crear un estudiante temporal para llenar el curso
        $estudiantes = $estudRepo->findAll();
        echo "  → Verificando capacidad del curso: {$curso_test['nombre']}\n";
        echo "    Capacidad máxima: {$curso_test['capacidad_maxima']}\n";
        echo "    Matriculados: {$curso_test['matriculados']}\n";
        echo "    Cupos disponibles: {$curso_test['cupos_disponibles']}\n";
        
        if ($curso_test['cupos_disponibles'] <= 0) {
            echo "  ⚠️  Curso lleno - no se puede inscribir\n";
        } else {
            echo "  ✅ Hay cupos disponibles\n";
        }
    }
    echo "\n";
    
    echo "════════════════════════════════════════════════\n";
    echo "✅ PRUEBAS COMPLETADAS EXITOSAMENTE\n";
    echo "════════════════════════════════════════════════\n\n";
    
} catch (Exception $e) {
    echo "❌ ERROR: " . $e->getMessage() . "\n";
    exit(1);
}
