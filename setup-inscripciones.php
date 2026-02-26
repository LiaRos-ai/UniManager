<?php
/**
 * Script para crear tablas de docentes, cursos e inscripciones
 * Ejecutar: php setup-inscripciones.php
 */

require_once __DIR__ . '/vendor/autoload.php';

use App\Database\Database;

try {
    $db = Database::getInstance();
    
    echo "🔧 Creando tabla DOCENTES...\n";
    $db->execute(
        "CREATE TABLE IF NOT EXISTS docentes (
            id            INT AUTO_INCREMENT PRIMARY KEY,
            nombre        VARCHAR(100) NOT NULL,
            apellido      VARCHAR(100) NOT NULL,
            email         VARCHAR(150) UNIQUE NOT NULL,
            especialidad  VARCHAR(100),
            estado        ENUM('activo', 'inactivo') DEFAULT 'activo',
            fecha_creacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            
            INDEX idx_email (email),
            INDEX idx_estado (estado)
        ) ENGINE=InnoDB"
    );
    echo "✅ Tabla DOCENTES creada\n\n";
    
    echo "🔧 Creando tabla CURSOS...\n";
    $db->execute(
        "CREATE TABLE IF NOT EXISTS cursos (
            id                INT AUTO_INCREMENT PRIMARY KEY,
            codigo            VARCHAR(50) UNIQUE NOT NULL,
            nombre            VARCHAR(150) NOT NULL,
            descripcion       TEXT,
            docente_id        INT,
            creditos          INT NOT NULL DEFAULT 3,
            capacidad_maxima  INT NOT NULL DEFAULT 30,
            matriculados      INT NOT NULL DEFAULT 0,
            semestre          INT NOT NULL DEFAULT 1,
            estado            ENUM('activo', 'inactivo', 'cancelado') DEFAULT 'activo',
            fecha_creacion    TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            
            FOREIGN KEY (docente_id) REFERENCES docentes(id) ON DELETE SET NULL,
            INDEX idx_codigo (codigo),
            INDEX idx_docente (docente_id),
            INDEX idx_estado (estado),
            INDEX idx_semester (semestre)
        ) ENGINE=InnoDB"
    );
    echo "✅ Tabla CURSOS creada\n\n";
    
    echo "🔧 Creando tabla INSCRIPCIONES...\n";
    $db->execute(
        "CREATE TABLE IF NOT EXISTS inscripciones (
            id               INT AUTO_INCREMENT PRIMARY KEY,
            estudiante_id    INT NOT NULL,
            curso_id         INT NOT NULL,
            nota_final       DECIMAL(4,2) NULL,
            estado           ENUM('activa','completada','retirada') DEFAULT 'activa',
            fecha_inscripcion DATE NOT NULL DEFAULT (CURDATE()),
            
            FOREIGN KEY (estudiante_id) REFERENCES estudiantes(id) ON DELETE CASCADE,
            FOREIGN KEY (curso_id) REFERENCES cursos(id) ON DELETE RESTRICT,
            
            UNIQUE KEY uk_estudiante_curso (estudiante_id, curso_id),
            INDEX idx_estudiante (estudiante_id),
            INDEX idx_curso (curso_id),
            INDEX idx_estado (estado)
        ) ENGINE=InnoDB"
    );
    echo "✅ Tabla INSCRIPCIONES creada\n\n";
    
    echo "🔧 Creando vista VISTA_RENDIMIENTO_ESTUDIANTE...\n";
    $db->execute(
        "CREATE OR REPLACE VIEW vista_rendimiento_estudiante AS
         SELECT
             e.id,
             CONCAT(e.nombre, ' ', e.apellido_paterno) AS estudiante,
             COUNT(i.id)                              AS cursos_inscritos,
             SUM(i.estado = 'completada')             AS cursos_completados,
             ROUND(COALESCE(AVG(i.nota_final), 0), 2) AS promedio_notas
         FROM estudiantes e
         LEFT JOIN inscripciones i ON e.id = i.estudiante_id
         GROUP BY e.id, e.nombre, e.apellido_paterno"
    );
    echo "✅ Vista VISTA_RENDIMIENTO_ESTUDIANTE creada\n\n";
    
    // Insertar datos de prueba para docentes
    echo "💾 Insertando docentes de prueba...\n";
    $docentes_data = [
        ['ing.rivera@unimanager.edu', 'Ing. Carlos', 'Rivera', 'Ingeniería de Sistemas'],
        ['dra.garcia@unimanager.edu', 'Dra. María', 'García', 'Matemáticas'],
        ['ing.torres@unimanager.edu', 'Ing. Juan', 'Torres', 'Redes y Telecomunicaciones'],
        ['prof.lopez@unimanager.edu', 'Prof. Ana', 'López', 'Programación'],
        ['dr.morales@unimanager.edu', 'Dr. Pedro', 'Morales', 'Bases de Datos'],
    ];
    
    $docentes_count = 0;
    foreach ($docentes_data as [$email, $nombre, $apellido, $especialidad]) {
        try {
            $db->execute(
                "INSERT IGNORE INTO docentes (email, nombre, apellido, especialidad) VALUES (?, ?, ?, ?)",
                [$email, $nombre, $apellido, $especialidad]
            );
            $docentes_count++;
        } catch (Exception $e) {
            // Ignorar si ya existe
        }
    }
    echo "✅ $docentes_count docentes insertados\n\n";
    
    // Insertar datos de prueba para cursos
    echo "💾 Insertando cursos de prueba...\n";
    $cursos_data = [
        ['SIS-101', 'Programación I', 'Introducción a la programación con PHP', 1, 3, 30, 0, 1],
        ['SIS-102', 'Bases de Datos', 'Diseño y gestión de BD con MySQL', 5, 4, 25, 0, 1],
        ['MAT-201', 'Cálculo Diferencial', 'Análisis matemático avanzado', 2, 4, 35, 0, 2],
        ['RED-301', 'Redes TCP/IP', 'Protocolos y arquitectura de redes', 3, 3, 20, 0, 3],
        ['PRO-102', 'Programación II', 'OOP y patrones de diseño', 4, 4, 28, 0, 2],
        ['SIS-201', 'Ingeniería de Software', 'Desarrollo ágil y metodologías', 1, 3, 25, 0, 3],
    ];
    
    $cursos_count = 0;
    foreach ($cursos_data as [$codigo, $nombre, $desc, $docente, $creditos, $cap, $mat, $sem]) {
        try {
            $db->execute(
                "INSERT IGNORE INTO cursos (codigo, nombre, descripcion, docente_id, creditos, capacidad_maxima, matriculados, semestre) 
                 VALUES (?, ?, ?, ?, ?, ?, ?, ?)",
                [$codigo, $nombre, $desc, $docente, $creditos, $cap, $mat, $sem]
            );
            $cursos_count++;
        } catch (Exception $e) {
            // Ignorar si ya existe
        }
    }
    echo "✅ $cursos_count cursos insertados\n\n";
    
    // Inscribir estudiantes en cursos (relación N:M)
    echo "💾 Creando inscripciones de prueba...\n";
    $inscripciones_data = [
        [1, 1, 85.5, 'completada'],
        [1, 2, 92.0, 'completada'],
        [2, 1, 78.0, 'activa'],
        [2, 3, 88.5, 'completada'],
        [3, 2, 95.5, 'completada'],
        [3, 4, 81.0, 'activa'],
        [4, 1, 72.0, 'activa'],
        [4, 5, 89.0, 'completada'],
        [5, 3, 91.5, 'completada'],
        [5, 6, 79.0, 'activa'],
    ];
    
    $insc_count = 0;
    foreach ($inscripciones_data as [$est, $cur, $nota, $estado]) {
        try {
            $db->execute(
                "INSERT IGNORE INTO inscripciones (estudiante_id, curso_id, nota_final, estado) 
                 VALUES (?, ?, ?, ?)",
                [$est, $cur, $nota, $estado]
            );
            $insc_count++;
        } catch (Exception $e) {
            // Ignorar si ya existe
        }
    }
    echo "✅ $insc_count inscripciones creadas\n\n";
    
    echo "════════════════════════════════════════════════\n";
    echo "✅ SETUP COMPLETADO EXITOSAMENTE\n";
    echo "════════════════════════════════════════════════\n";
    echo "📊 Resumen:\n";
    echo "   • Docentes: $docentes_count\n";
    echo "   • Cursos: $cursos_count\n";
    echo "   • Inscripciones: $insc_count\n";
    echo "\n📋 Tablas creadas:\n";
    echo "   ✓ docentes\n";
    echo "   ✓ cursos\n";
    echo "   ✓ inscripciones\n";
    echo "   ✓ vista_rendimiento_estudiante\n";
    echo "\n🚀 Sistema listo para inscripciones\n";

} catch (Exception $e) {
    echo "❌ ERROR: " . $e->getMessage() . "\n";
    exit(1);
}
