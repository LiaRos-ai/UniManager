<?php
declare(strict_types=1);

/**
 * Script de instalación - Crea la base de datos y tabla
 */

echo "\n";
echo "═══════════════════════════════════════════════════════════════\n";
echo "  INSTALACIÓN Y CONFIGURACIÓN DE LA BASE DE DATOS\n";
echo "═══════════════════════════════════════════════════════════════\n\n";

try {
    // Conexión inicial sin BD
    echo "📌 Conectando a MySQL...\n";
    
    $pdo = new PDO(
        'mysql:host=localhost;port=3306;charset=utf8mb4',
        'root',
        '',
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        ]
    );
    
    echo "✅ Conexión exitosa\n\n";
    
    // Crear BD
    echo "📌 Creando base de datos 'unimanager'...\n";
    $pdo->exec("DROP DATABASE IF EXISTS unimanager");
    $pdo->exec("CREATE DATABASE unimanager CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
    echo "✅ Base de datos creada/recreada\n\n";
    
    // Crear nueva conexión a la BD
    $pdo = new PDO(
        'mysql:host=localhost;port=3306;dbname=unimanager;charset=utf8mb4',
        'root',
        '',
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        ]
    );
    
    // Crear tabla
    echo "📌 Creando tabla 'estudiantes'...\n";
    
    $sql = <<<SQL
CREATE TABLE estudiantes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    codigo VARCHAR(50) UNIQUE NOT NULL,
    nombre VARCHAR(100) NOT NULL,
    apellido_paterno VARCHAR(100) NOT NULL,
    apellido_materno VARCHAR(100) NOT NULL,
    email VARCHAR(150) UNIQUE NOT NULL,
    ci VARCHAR(20) UNIQUE NOT NULL,
    telefono VARCHAR(20),
    semestre INT DEFAULT 1,
    estado ENUM('active', 'inactive', 'graduated', 'suspended') DEFAULT 'active',
    promedio DECIMAL(5, 2) DEFAULT 0.00,
    notas JSON,
    fecha_creacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    fecha_actualizacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_email (email),
    INDEX idx_ci (ci),
    INDEX idx_estado (estado),
    INDEX idx_semestre (semestre),
    INDEX idx_promedio (promedio)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
SQL;
    
    $pdo->exec($sql);
    echo "✅ Tabla creada\n\n";
    echo "📌 Insertando datos de prueba...\n";
    
    $datos = [
        ['2024-00001', 'Juan', 'Pérez', 'García', 'juan.perez@est.edu.bo', '12345678-LP', '+59178451234', 3, 'active', 85.50],
        ['2024-00002', 'María', 'López', 'Quispe', 'maria.lopez@est.edu.bo', '23456789-SC', '+59176542345', 4, 'active', 92.75],
        ['2024-00003', 'Carlos', 'Mamani', 'Condori', 'carlos.mamani@est.edu.bo', '34567890-CB', '+59172345678', 2, 'active', 78.25],
        ['2024-00004', 'Ana', 'García', 'Torres', 'ana.garcia@est.edu.bo', '45678901-LP', NULL, 5, 'inactive', 55.00],
        ['2024-00005', 'Luis', 'Ruiz', 'Apaza', 'luis.ruiz@est.edu.bo', '56789012-SC', '+59178234567', 3, 'active', 88.00],
    ];
    
    $stmt = $pdo->prepare(
        "INSERT INTO estudiantes (codigo, nombre, apellido_paterno, apellido_materno, email, ci, telefono, semestre, estado, promedio) 
         VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)"
    );
    
    foreach ($datos as $fila) {
        $stmt->execute($fila);
        echo "   ✓ Insertado: {$fila[1]} {$fila[2]}\n";
    }
    
    echo "\n";
    
    // Verificar datos
    echo "📌 Verificando datos en la BD...\n";
    $result = $pdo->query("SELECT COUNT(*) as total FROM estudiantes");
    $row = $result->fetch();
    echo "✅ Total de estudiantes: {$row['total']}\n\n";
    
    echo "═══════════════════════════════════════════════════════════════\n";
    echo "  ✅ INSTALACIÓN COMPLETADA EXITOSAMENTE\n";
    echo "═══════════════════════════════════════════════════════════════\n";
    echo "\nAhora puedes ejecutar el test CRUD:\n";
    echo "   php test-crud.php\n\n";
    
} catch (PDOException $e) {
    echo "\n❌ ERROR: " . $e->getMessage() . "\n";
    echo "\nMINO la secuencia:\n";
    echo "1. Asegúrate que MySQL está ejecutándose en XAMPP\n";
    echo "2. Que PHP puede acceder a MySQL\n\n";
    die();
}
?>