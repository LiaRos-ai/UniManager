<?php
declare(strict_types=1);

namespace App\Services;

/**
 * Servicio para cálculos académicos
 */
class AcademicCalculator {
    
    /**
     * Calcula promedio simple
     */
    public function calcularPromedio(array $notas): float {
        if (empty($notas)) {
            return 0.0;
        }
        return array_sum($notas) / count($notas);
    }
    
    /**
     * Obtiene letra de calificación
     */
    public function obtenerLetra(float $promedio): string {
        return match(true) {
            $promedio >= 90 => 'A',
            $promedio >= 80 => 'B',
            $promedio >= 70 => 'C',
            $promedio >= 60 => 'D',
            default => 'F'
        };
    }
    
    /**
     * Determina si aprobó
     */
    public function aprobo(float $promedio, float $notaMinima = 60): bool {
        return $promedio >= $notaMinima;
    }
    
    /**
     * Calcula estadísticas de notas
     */
    public function calcularEstadisticas(array $notas): array {
        if (empty($notas)) {
            return [
                'promedio' => 0,
                'minimo' => 0,
                'maximo' => 0,
                'mediana' => 0,
                'total_notas' => 0
            ];
        }
        
        $promedio = $this->calcularPromedio($notas);
        $minimo = min($notas);
        $maximo = max($notas);
        
        // Calcular mediana
        sort($notas);
        $n = count($notas);
        $medio = (int)($n / 2);
        $mediana = $n % 2 === 0
            ? ($notas[$medio - 1] + $notas[$medio]) / 2
            : $notas[$medio];
        
        return [
            'promedio' => round($promedio, 2),
            'minimo' => $minimo,
            'maximo' => $maximo,
            'mediana' => round($mediana, 2),
            'total_notas' => $n
        ];
    }
    
    /**
     * Calcula nota necesaria para aprobar
     */
    public function notaNecesariaParaAprobar(
        array $notasActuales,
        int $totalNotas,
        float $promedioMinimo = 60
    ): float {
        $notasObtenidas = count($notasActuales);
        $notasFaltantes = $totalNotas - $notasObtenidas;
        
        if ($notasFaltantes <= 0) {
            return 0;
        }
        
        $sumaActual = array_sum($notasActuales);
        $sumaRequerida = $promedioMinimo * $totalNotas;
        $puntosFaltantes = $sumaRequerida - $sumaActual;
        
        return $puntosFaltantes / $notasFaltantes;
    }
}