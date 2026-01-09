<?php

namespace App\Imports;

use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

class AprendicesImport implements ToCollection, WithHeadingRow
{
    /**
     * Estados que deben ser filtrados (no se importarán)
     */
    private const ESTADOS_EXCLUIDOS = [
        'RETIRO VOLUNTARIO',
        'CANCELADO',
        'TRASLADADO',
        'APLAZADO',
    ];

    /**
     * Colección de aprendices procesados
     */
    private Collection $aprendices;

    /**
     * Constructor - inicializar la colección de aprendices
     */
    public function __construct()
    {
        $this->aprendices = collect();
    }

    /**
     * Especificar en qué fila están los encabezados (fila 5)
     */
    public function headingRow(): int
    {
        return 5;
    }

    /**
     * Procesar las filas del Excel
     * @param Collection $rows
     */
    public function collection(Collection $rows)
    {
        foreach ($rows as $row) {
            // Obtener el estado primero para filtrar
            // Intentar diferentes variaciones del nombre de columna
            $estado = $this->getValue($row, ['estado', 'Estado', 'ESTADO']);

            $estadoExcluido = $this->debeExcluirse($estado);

            // Mapear los datos del Excel a la estructura esperada
            // Los encabezados exactos del Excel son:
            // Tipo de Documento, Número de Documento, Nombre, Apellidos, Celular, Correo Electrónico, Estado
            
            // Usar búsqueda flexible por palabras clave
            // El sistema buscará automáticamente en todas las claves disponibles
            
            // Tipo de Documento - busca cualquier clave que contenga "tipo" y "documento"
            $tipoDocumento = $this->getValue($row, 'Tipo de Documento');
            
            // Número de Documento - busca cualquier clave que contenga "numero" y "documento"
            $numeroDocumentoRaw = $this->getValue($row, 'Número de Documento');
            $numeroDocumento = $this->normalizeDocumento($numeroDocumentoRaw);
            
            // Nombre
            $nombre = $this->getValue($row, 'Nombre');
            
            // Apellidos - busca "apellidos" o "apellido"
            $apellido = $this->getValue($row, 'Apellidos');
            
            // Celular
            $celular = $this->getValue($row, 'Celular');
            
            // Correo Electrónico - busca cualquier clave que contenga "correo" y "electronico"
            $email = $this->getValue($row, 'Correo Electrónico');

            // Validar que los campos requeridos no estén vacíos
            if (empty($numeroDocumento) || empty($nombre) || empty($apellido)) {
                continue;
            }

            $this->aprendices->push([
                'tipo_documento' => !empty($tipoDocumento) ? strtoupper(trim((string)$tipoDocumento)) : 'CC',
                'numero_documento' => $numeroDocumento,
                'nombre' => trim($nombre),
                'apellido' => trim($apellido),
                'celular' => !empty($celular) ? trim($celular) : '',
                'email' => !empty($email) ? trim($email) : '',
                'estado' => !empty($estado) ? trim($estado) : 'EN FORMACION',
                'estado_excluido' => $estadoExcluido,
            ]);
        }
    }

    /**
     * Normalizar número de documento:
     * - Si viene numérico (Excel), convertir a entero
     * - Si viene con puntos/espacios, dejar solo dígitos
     */
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
     * Obtener los aprendices procesados
     * @return Collection
     */
    public function getAprendices(): Collection
    {
        return $this->aprendices;
    }

    /**
     * Obtener valor de una fila usando búsqueda flexible por palabras clave
     * Busca en TODAS las claves disponibles y compara usando palabras clave
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
     * Maatwebsite/Excel puede usar Str::slug() (guiones) o guiones bajos
     * Normalizamos ambos a guiones bajos para comparar
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
}
