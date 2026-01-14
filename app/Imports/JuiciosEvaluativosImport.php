<?php

namespace App\Imports;

use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

class JuiciosEvaluativosImport implements ToCollection, WithHeadingRow
{
    /**
     * Estados que deben ser filtrados (no se contarán)
     */
    private const ESTADOS_EXCLUIDOS = [
        'RETIRO VOLUNTARIO',
        'CANCELADO',
        'TRASLADADO',
    ];

    /**
     * Conteo de resultados por evaluar por aprendiz
     */
    private Collection $conteoPorAprendiz;

    /**
     * Números de documento de aprendices importados previamente
     */
    private array $numerosDocumentoAprendices;

    /**
     * Constructor - inicializar la colección
     * @param array $numerosDocumentoAprendices Números de documento de aprendices importados
     */
    public function __construct(array $numerosDocumentoAprendices = [])
    {
        $this->conteoPorAprendiz = collect();
        $this->numerosDocumentoAprendices = array_values(array_filter(array_map(function ($doc) {
            return $this->normalizeDocumento($doc);
        }, $numerosDocumentoAprendices)));
    }

    /**
     * Especificar en qué fila están los encabezados (fila 13)
     */
    public function headingRow(): int
    {
        return 13;
    }

    /**
     * Procesar las filas del Excel
     * @param Collection $rows
     */
    public function collection(Collection $rows)
    {
        // Debug: Log las claves de la primera fila
        if ($rows->isNotEmpty()) {
            $firstRow = $rows->first();
            \Log::info('Juicios - Claves del Excel:', $firstRow->keys()->toArray());
            \Log::info('Juicios - Primera fila:', $firstRow->toArray());
            \Log::info('Juicios - Números de documento a buscar:', $this->numerosDocumentoAprendices);
        }
        
        // Agrupar por número de documento (usar array normal, no Collection)
        $juiciosPorAprendiz = [];
        $totalFilas = 0;
        $filasProcesadas = 0;

        foreach ($rows as $row) {
            $totalFilas++;
            // Obtener el estado del aprendiz
            $estado = $this->getValue($row, 'Estado');
            
            // Filtrar aprendices con estados excluidos
            if ($this->debeExcluirse($estado)) {
                continue;
            }

            // Obtener número de documento (identificador único)
            $numeroDocumento = $this->normalizeDocumento($this->getValue($row, 'Número de Documento'));
            
            if (empty($numeroDocumento)) {
                continue;
            }

            // IMPORTANTE: Solo procesar aprendices que fueron importados previamente
            $numeroDocumentoTrim = $numeroDocumento;
            if (!empty($this->numerosDocumentoAprendices) && !in_array($numeroDocumentoTrim, $this->numerosDocumentoAprendices, true)) {
                continue; // Saltar si el aprendiz no fue importado previamente
            }
            
            $filasProcesadas++;

            // Obtener juicio de evaluación
            $juicioEvaluacion = $this->getValue($row, 'Juicio de Evaluación');
            
            // Determinar si está por evaluar (debe ser exactamente "POR EVALUAR")
            $porEvaluar = $this->estaPorEvaluar($juicioEvaluacion);

            // Obtener el estado del aprendiz (solo la primera vez que se encuentra)
            $estadoAprendiz = $this->getValue($row, 'Estado');
            
            // Agrupar por número de documento
            if (!isset($juiciosPorAprendiz[$numeroDocumentoTrim])) {
                $juiciosPorAprendiz[$numeroDocumentoTrim] = [
                    'numero_documento' => $numeroDocumentoTrim,
                    'tipo_documento' => $this->getValue($row, 'Tipo de Documento') ?? 'CC',
                    'nombre_completo' => $this->getValue($row, 'Nombre Completo') ?? '',
                    'estado' => $estadoAprendiz ?? null,
                    'total_resultados' => 0,
                    'por_evaluar' => 0,
                    'aprobados' => 0,
                    'no_aprobados' => 0
                ];
            }

            // Incrementar contadores
            $juiciosPorAprendiz[$numeroDocumentoTrim]['total_resultados']++;
            
            if ($porEvaluar) {
                $juiciosPorAprendiz[$numeroDocumentoTrim]['por_evaluar']++;
            } elseif (strtoupper(trim($juicioEvaluacion ?? '')) === 'APROBADO') {
                $juiciosPorAprendiz[$numeroDocumentoTrim]['aprobados']++;
            } elseif (strtoupper(trim($juicioEvaluacion ?? '')) === 'NO APROBADO') {
                $juiciosPorAprendiz[$numeroDocumentoTrim]['no_aprobados']++;
            }
        }

        // Convertir a colección indexada
        $this->conteoPorAprendiz = collect(array_values($juiciosPorAprendiz));
        
        // Debug: Log del resultado
        \Log::info('Juicios - Total filas procesadas:', [
            'total_filas' => $totalFilas,
            'filas_procesadas' => $filasProcesadas,
            'aprendices_encontrados' => $this->conteoPorAprendiz->count(),
            'conteo' => $this->conteoPorAprendiz->toArray()
        ]);
    }

    /**
     * Determinar si un resultado está por evaluar
     * Debe ser exactamente "POR EVALUAR"
     */
    private function estaPorEvaluar(?string $juicioEvaluacion): bool
    {
        if (empty($juicioEvaluacion)) {
            return false; // Vacío no cuenta como "POR EVALUAR"
        }

        $juicioNormalizado = strtoupper(trim($juicioEvaluacion));
        
        // Por evaluar solo si es exactamente "POR EVALUAR"
        return $juicioNormalizado === 'POR EVALUAR';
    }

    private function normalizeDocumento($value): string
    {
        if ($value === null) {
            return '';
        }

        if (is_int($value)) {
            return (string) $value;
        }

        if (is_float($value) || is_numeric($value)) {
            return (string) (int) round((float) $value);
        }

        $str = trim((string) $value);
        $digits = preg_replace('/\D+/', '', $str);
        return $digits ?? '';
    }

    /**
     * Obtener valor de una fila usando búsqueda flexible por palabras clave
     */
    private function getValue($row, $columnNames)
    {
        // Si se pasa un array, intentar cada variación
        if (is_array($columnNames)) {
            foreach ($columnNames as $columnName) {
                $value = $this->getValue($row, $columnName);
                if ($value !== null && $value !== '') {
                    return $value;
                }
            }
            return null;
        }

        // Si se pasa un string, buscar ese nombre
        $columnName = $columnNames;

        // Primero intentar con el nombre exacto
        if (isset($row[$columnName])) {
            $value = $row[$columnName];
            return $value !== null ? (is_string($value) ? trim($value) : $value) : null;
        }

        // Normalizar el nombre de columna buscado para extraer palabras clave
        $searchKeywords = $this->extractKeywords($columnName);
        
        // Buscar en TODAS las claves disponibles
        $bestMatch = null;
        $bestScore = 0;
        
        foreach ($row->keys() as $key) {
            // Normalizar la clave del row
            $normalizedKey = $this->normalizeKey($key);
            $normalizedColumn = $this->normalizeKey($columnName);
            
            // Comparación exacta normalizada
            if ($normalizedKey === $normalizedColumn) {
                $value = $row[$key];
                return $value !== null ? (is_string($value) ? trim($value) : $value) : null;
            }
            
            // Búsqueda por palabras clave (más flexible)
            $keyKeywords = $this->extractKeywords($key);
            $score = $this->calculateMatchScore($searchKeywords, $keyKeywords);
            
            if ($score > $bestScore && $score > 0.5) { // Al menos 50% de coincidencia
                $bestScore = $score;
                $bestMatch = $key;
            }
        }
        
        // Si encontramos una coincidencia razonable, retornar el valor
        if ($bestMatch !== null) {
            $value = $row[$bestMatch];
            return $value !== null ? (is_string($value) ? trim($value) : $value) : null;
        }

        return null;
    }

    /**
     * Extraer palabras clave de un nombre de columna
     */
    private function extractKeywords(string $text): array
    {
        // Normalizar y dividir en palabras
        $normalized = $this->normalizeKey($text);
        $words = explode('_', $normalized);
        
        // Filtrar palabras vacías y muy cortas (menos de 2 caracteres)
        $keywords = array_filter($words, function($word) {
            return strlen($word) >= 2;
        });
        
        return array_values($keywords);
    }

    /**
     * Calcular el score de coincidencia entre dos conjuntos de palabras clave
     */
    private function calculateMatchScore(array $searchKeywords, array $keyKeywords): float
    {
        if (empty($searchKeywords) || empty($keyKeywords)) {
            return 0;
        }
        
        $matches = 0;
        $total = count($searchKeywords);
        
        foreach ($searchKeywords as $searchWord) {
            foreach ($keyKeywords as $keyWord) {
                // Coincidencia exacta
                if ($searchWord === $keyWord) {
                    $matches++;
                    break;
                }
                // Coincidencia parcial (una palabra contiene a la otra)
                if (strpos($keyWord, $searchWord) !== false || strpos($searchWord, $keyWord) !== false) {
                    $matches += 0.5;
                    break;
                }
            }
        }
        
        return $total > 0 ? $matches / $total : 0;
    }

    /**
     * Normalizar una clave para comparación
     */
    private function normalizeKey(string $key): string
    {
        // Convertir a minúsculas
        $key = Str::lower($key);
        
        // Remover acentos
        $key = $this->removeAccents($key);
        
        // Reemplazar espacios, guiones y guiones bajos con guiones bajos
        $key = preg_replace('/[\s\-_]+/', '_', $key);
        
        // Remover caracteres especiales excepto guiones bajos
        $key = preg_replace('/[^a-z0-9_]/', '', $key);
        
        return trim($key, '_');
    }

    /**
     * Remover acentos de una cadena
     */
    private function removeAccents(string $string): string
    {
        $accents = [
            'á' => 'a', 'é' => 'e', 'í' => 'i', 'ó' => 'o', 'ú' => 'u',
            'Á' => 'A', 'É' => 'E', 'Í' => 'I', 'Ó' => 'O', 'Ú' => 'U',
            'ñ' => 'n', 'Ñ' => 'N'
        ];
        
        return strtr($string, $accents);
    }

    /**
     * Verificar si un estado debe ser excluido (case-insensitive)
     */
    private function debeExcluirse(?string $estado): bool
    {
        if (empty($estado)) {
            return false;
        }

        $estadoNormalizado = trim($estado);
        
        // Comparación case-insensitive
        foreach (self::ESTADOS_EXCLUIDOS as $estadoExcluido) {
            if (strcasecmp($estadoNormalizado, $estadoExcluido) === 0) {
                return true;
            }
        }
        
        return false;
    }

    /**
     * Obtener el conteo de resultados por evaluar por aprendiz
     * @return Collection
     */
    public function getConteoPorAprendiz(): Collection
    {
        return $this->conteoPorAprendiz;
    }
}
