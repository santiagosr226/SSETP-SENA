<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Ficha;
use App\Models\Aprendiz;
use App\Models\Funcionario;
use App\Models\Programa;
use App\Imports\AprendicesImport;
use App\Imports\JuiciosEvaluativosImport;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Maatwebsite\Excel\Facades\Excel;

class FichaController extends Controller
{
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

    public function eliminarAprendiz(Request $request, Ficha $ficha, Aprendiz $aprendiz)
    {
        try {
            if ((int)$aprendiz->ficha_id !== (int)$ficha->id) {
                return response()->json([
                    'success' => false,
                    'message' => 'El aprendiz no pertenece a esta ficha.'
                ], 403);
            }

            DB::beginTransaction();

            $aprendiz->delete();

            $ficha->update([
                'fecha_actualizacion' => now()->toDateString(),
            ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Aprendiz eliminado correctamente',
            ]);
        } catch (\Exception $e) {
            if (DB::transactionLevel() > 0) {
                DB::rollBack();
            }
            return response()->json([
                'success' => false,
                'message' => 'Error al eliminar el aprendiz: ' . $e->getMessage(),
            ], 422);
        }
    }

    /**
     * Display a listing of the resource.
     */
        public function index(Request $request)
    {
        $search = $request->input('search');
        
        $fichas = Ficha::query()
            ->with(['programa', 'instructor'])
            ->when($search, function ($query, $search) {
                return $query->where(function ($q) use ($search) {
                    $q->where('numero', 'like', "%{$search}%")
                      ->orWhere('estado', 'like', "%{$search}%")
                      ->orWhere('modalidad', 'like', "%{$search}%")
                      ->orWhere('jornada', 'like', "%{$search}%")
                      ->orWhereHas('programa', function ($q2) use ($search) {
                          $q2->where('nombre', 'like', "%{$search}%");
                      })
                      ->orWhereHas('instructor', function ($q3) use ($search) {
                          $q3->where('nombre', 'like', "%{$search}%");
                      });
                });
            })
            ->orderBy('created_at', 'desc')
            ->paginate(10);
        
        return view('admin.fichas.index', compact('fichas', 'search'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $programas = Programa::all();
        $instructores = Funcionario::where('rol', 'instructor')->get();
        return view('admin.fichas.create', compact('programas', 'instructores'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        // Validar datos de la ficha
        $validated = $request->validate([
            'numero' => 'required|string|unique:fichas,numero',
            'programa_id' => 'required|exists:programas,id',
            'instructor_id' => 'required|exists:funcionarios,id',
            'estado' => 'required|string',
            'fecha_inicial' => 'required|date',
            'fecha_final_lectiva' => 'nullable|date',
            'fecha_final_formacion' => 'nullable|date',
            'fecha_limite_productiva' => 'nullable|date',
            'fecha_actualizacion' => 'nullable|date',
            'modalidad' => 'required|string',
            'jornada' => 'required|string',
            'resultados_aprendizaje_totales' => 'nullable|integer',
            'imported_aprendices' => 'nullable|string', // JSON string
        ]);

        try {
            \Log::info('Iniciando creación de ficha', ['request_data' => $request->all()]);
            
            DB::beginTransaction();

            // Crear la ficha
            // Nota: La migración requiere todas las fechas, así que usamos valores por defecto si no vienen
            \Log::info('Creando ficha...');
            $ficha = Ficha::create([
                'numero' => $validated['numero'],
                'programa_id' => $validated['programa_id'],
                'funcionario_id' => $validated['instructor_id'],
                'estado' => $validated['estado'],
                'modalidad' => $validated['modalidad'],
                'jornada' => $validated['jornada'],
                'fecha_inicial' => $validated['fecha_inicial'],
                'fecha_final_lectiva' => $validated['fecha_final_lectiva'] ?? $validated['fecha_inicial'],
                'fecha_final_formacion' => $validated['fecha_final_formacion'] ?? $validated['fecha_inicial'],
                'fecha_limite_productiva' => $validated['fecha_limite_productiva'] ?? $validated['fecha_inicial'],
                'fecha_actualizacion' => $validated['fecha_actualizacion'] ?? now()->toDateString(),
                'resultados_aprendizaje_totales' => (string)($validated['resultados_aprendizaje_totales'] ?? 0),
            ]);

            \Log::info('Ficha creada exitosamente', ['ficha_id' => $ficha->id]);
            
            // Procesar aprendices importados
            $aprendicesCount = 0;
            $aprendicesError = [];
            if ($request->has('imported_aprendices') && !empty($request->imported_aprendices)) {
                \Log::info('Procesando aprendices importados...');
                $aprendicesData = json_decode($request->imported_aprendices, true);
                
                if (is_array($aprendicesData) && !empty($aprendicesData)) {
                    \Log::info('Total aprendices a crear: ' . count($aprendicesData));
                    foreach ($aprendicesData as $index => $aprendizData) {
                        try {
                            // Formatear resultados de aprendizaje como fracción
                            $totalResultados = $aprendizData['total_resultados'] ?? 0;
                            $porEvaluar = $aprendizData['por_evaluar'] ?? 0;
                            $resultadosAprendizaje = $totalResultados > 0 
                                ? $porEvaluar . '/' . $totalResultados 
                                : '0/0';

                            // Validar que tenga documento
                            if (empty($aprendizData['numero_documento'])) {
                                \Log::warning('Aprendiz sin número de documento, saltando', ['index' => $index]);
                                $aprendicesError[] = [
                                    'numero_documento' => 'N/A',
                                    'nombre' => ($aprendizData['nombre'] ?? '') . ' ' . ($aprendizData['apellido'] ?? ''),
                                    'error' => 'Sin número de documento'
                                ];
                                continue;
                            }

                            // Crear el aprendiz
                            Aprendiz::create([
                                'nombre' => $aprendizData['nombre'] ?? '',
                                'apellido' => $aprendizData['apellido'] ?? '',
                                'tipo_documento' => $aprendizData['tipo_documento'] ?? 'CC',
                                'documento' => trim($aprendizData['numero_documento']),
                                'correo' => !empty($aprendizData['email']) ? $aprendizData['email'] : null,
                                'telefono' => !empty($aprendizData['celular']) ? $aprendizData['celular'] : null,
                                'estado' => $aprendizData['estado'] ?? 'EN FORMACION',
                                'alternativa' => null, // Campo nullable
                                'resultados_aprendizaje' => $resultadosAprendizaje,
                                'password' => Hash::make(trim($aprendizData['numero_documento'])),
                                'primer_acceso' => true,
                                'fecha_actualizacion' => now()->format('Y-m-d'),
                                'ficha_id' => $ficha->id,
                            ]);
                            $aprendicesCount++;
                        } catch (\Exception $e) {
                            $numeroDocumento = trim($aprendizData['numero_documento'] ?? 'N/A');
                            $nombreCompleto = trim(($aprendizData['nombre'] ?? '') . ' ' . ($aprendizData['apellido'] ?? ''));
                            
                            // Determinar el tipo de error
                            $errorMessage = 'Error desconocido';
                            if (str_contains($e->getMessage(), 'Duplicate entry') || str_contains($e->getMessage(), '1062')) {
                                $errorMessage = 'Número de documento duplicado';
                            } elseif (str_contains($e->getMessage(), 'Integrity constraint violation')) {
                                $errorMessage = 'Violación de restricción de integridad';
                            } else {
                                $errorMessage = $e->getMessage();
                            }
                            
                            \Log::error('Error al crear aprendiz ' . $index . ': ' . $e->getMessage(), [
                                'aprendiz_data' => $aprendizData,
                                'trace' => $e->getTraceAsString()
                            ]);
                            
                            $aprendicesError[] = [
                                'numero_documento' => $numeroDocumento,
                                'nombre' => $nombreCompleto,
                                'error' => $errorMessage
                            ];
                            // Continuar con el siguiente aprendiz
                        }
                    }
                    \Log::info('Aprendices creados: ' . $aprendicesCount . ', Errores: ' . count($aprendicesError));
                }
            }

            DB::commit();
            \Log::info('Transacción completada exitosamente');

            \Log::info('Enviando respuesta exitosa');
            
            $message = 'Ficha creada exitosamente';
            if ($aprendicesCount > 0) {
                $message .= ' con ' . $aprendicesCount . ' aprendices';
            }
            if (count($aprendicesError) > 0) {
                $message .= '. ' . count($aprendicesError) . ' aprendices no pudieron ser guardados';
            }
            
            return response()->json([
                'success' => true,
                'message' => $message,
                'ficha_id' => $ficha->id,
                'aprendices_creados' => $aprendicesCount,
                'aprendices_error' => $aprendicesError
            ]);

        } catch (\Illuminate\Validation\ValidationException $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Error de validación',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('Error al crear ficha: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString()
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Error al crear la ficha: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        try {
            $ficha = Ficha::with(['programa', 'instructor', 'aprendices'])->findOrFail($id);
            $programas = Programa::all();
            $instructores = Funcionario::where('rol', 'instructor')->get();
            
            return view('admin.fichas.edit', compact('ficha', 'programas', 'instructores'));
        } catch (\Exception $e) {
            return redirect()
                ->route('fichas.index')
                ->with('error', 'Ficha no encontrada: ' . $e->getMessage());
        }       
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        try {
            $ficha = Ficha::findOrFail($id);

            $estadosExcluidos = ['RETIRO VOLUNTARIO', 'CANCELADO', 'TRASLADADO', 'APLAZADO'];

            $validated = $request->validate([
                'numero' => [
                    'required',
                    'string',
                    Rule::unique('fichas', 'numero')->ignore($ficha->id),
                ],
                'programa_id' => 'required|exists:programas,id',
                'instructor_id' => 'required|exists:funcionarios,id',
                'estado' => 'required|string',
                'fecha_inicial' => 'required|date',
                'fecha_final_lectiva' => 'nullable|date',
                'fecha_final_formacion' => 'nullable|date',
                'fecha_limite_productiva' => 'nullable|date',
                'modalidad' => 'required|string',
                'jornada' => 'required|string',
                'resultados_aprendizaje_totales' => 'nullable|integer|min:0',
                'imported_aprendices' => 'nullable|string',
            ]);

            DB::beginTransaction();

            $fechaInicial = $validated['fecha_inicial'];

            $ficha->update([
                'numero' => $validated['numero'],
                'programa_id' => $validated['programa_id'],
                'funcionario_id' => $validated['instructor_id'],
                'estado' => $validated['estado'],
                'modalidad' => $validated['modalidad'],
                'jornada' => $validated['jornada'],
                'fecha_inicial' => $fechaInicial,
                'fecha_final_lectiva' => $validated['fecha_final_lectiva'] ?? $fechaInicial,
                'fecha_final_formacion' => $validated['fecha_final_formacion'] ?? $fechaInicial,
                'fecha_limite_productiva' => $validated['fecha_limite_productiva'] ?? $fechaInicial,
                'fecha_actualizacion' => now()->toDateString(),
                'resultados_aprendizaje_totales' => (string)($validated['resultados_aprendizaje_totales'] ?? 0),
            ]);

            // Aplicar aprendices importados (solo en edición) al guardar cambios
            $importedAprendicesRaw = $validated['imported_aprendices'] ?? null;
            $created = 0;
            $updated = 0;
            $skippedExcluded = 0;
            $conflicts = [];

            if (!empty($importedAprendicesRaw)) {
                $decoded = json_decode($importedAprendicesRaw, true);
                if (json_last_error() !== JSON_ERROR_NONE || !is_array($decoded)) {
                    throw new \InvalidArgumentException('El JSON de aprendices importados no es válido.');
                }

                foreach ($decoded as $aprendizData) {
                    $documento = $this->normalizeDocumento($aprendizData['numero_documento'] ?? '');
                    if ($documento === '') {
                        continue;
                    }

                    $correo = trim($aprendizData['email'] ?? '');
                    $telefono = trim($aprendizData['celular'] ?? '');
                    $estado = trim($aprendizData['estado'] ?? '');
                    $tipoDocumento = strtoupper(trim((string)($aprendizData['tipo_documento'] ?? '')));
                    $estadoUpper = strtoupper(trim($estado));
                    $estadoExcluido = $estadoUpper !== '' && in_array($estadoUpper, $estadosExcluidos, true);

                    $existing = Aprendiz::where('documento', $documento)->first();

                    if ($existing) {
                        if ((int)$existing->ficha_id !== (int)$ficha->id) {
                            $conflicts[] = [
                                'documento' => $documento,
                                'nombre' => trim(($aprendizData['nombre'] ?? '') . ' ' . ($aprendizData['apellido'] ?? '')),
                                'ficha_id_actual' => $existing->ficha_id,
                                'mensaje' => 'El aprendiz ya existe y está asociado a otra ficha'
                            ];
                            continue;
                        }

                        $updateData = [
                            'fecha_actualizacion' => now()->format('Y-m-d'),
                        ];
                        if ($correo !== '') {
                            $updateData['correo'] = $correo;
                        }
                        if ($telefono !== '') {
                            $updateData['telefono'] = $telefono;
                        }
                        if ($estado !== '') {
                            // Se permite actualizar el estado aunque sea excluido
                            $updateData['estado'] = $estado;
                        }

                        // No sobreescribir tipo_documento; solo completarlo si está vacío o N/A
                        if ($tipoDocumento !== '' && $tipoDocumento !== 'N/A') {
                            $currentTipo = strtoupper(trim((string)($existing->tipo_documento ?? '')));
                            if ($currentTipo === '' || $currentTipo === 'N/A') {
                                $updateData['tipo_documento'] = $tipoDocumento;
                            }
                        }

                        $existing->update($updateData);
                        $updated++;
                        continue;
                    }

                    // No crear nuevos aprendices si vienen con estado excluido
                    if ($estadoExcluido) {
                        $skippedExcluded++;
                        continue;
                    }

                    Aprendiz::create([
                        'nombre' => trim($aprendizData['nombre'] ?? ''),
                        'apellido' => trim($aprendizData['apellido'] ?? ''),
                        'tipo_documento' => ($tipoDocumento !== '' && $tipoDocumento !== 'N/A') ? $tipoDocumento : 'CC',
                        'documento' => $documento,
                        'correo' => $correo !== '' ? $correo : null,
                        'telefono' => $telefono !== '' ? $telefono : null,
                        'estado' => $estado !== '' ? $estado : 'EN FORMACION',
                        'alternativa' => null,
                        'resultados_aprendizaje' => '0/0',
                        'password' => Hash::make($documento),
                        'primer_acceso' => true,
                        'fecha_actualizacion' => now()->format('Y-m-d'),
                        'ficha_id' => $ficha->id,
                    ]);

                    $created++;
                }
            }

            DB::commit();

            return redirect()
                ->route('fichas.edit', $ficha->id)
                ->with('success', '¡Ficha actualizada exitosamente!');
        } catch (\Illuminate\Validation\ValidationException $e) {
            throw $e;
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()
                ->back()
                ->withInput()
                ->with('error', 'Error al actualizar la ficha: ' . $e->getMessage());
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        try {
            $ficha = Ficha::findOrFail($id);
            $ficha->delete();

            return redirect()
                ->route('fichas.index')
                ->with('success', '¡Ficha eliminada exitosamente!');

        } catch (\Exception $e) {
            return redirect()
                ->back()
                ->with('error', 'Error al eliminar la ficha: ' . $e->getMessage());
        }   
    }

    /**
     * Importar aprendices desde Excel/CSV
     */
    public function importarAprendices(Request $request)
    {
        $request->validate([
            'archivo' => 'required|mimes:xlsx,xls,csv|max:5120', // 5MB máximo
            'ficha_id' => 'nullable|exists:fichas,id',
        ]);

        try {
            $estadosExcluidos = ['RETIRO VOLUNTARIO', 'CANCELADO', 'TRASLADADO', 'APLAZADO'];

            $import = new AprendicesImport();
            Excel::import($import, $request->file('archivo'));
            
            $aprendices = $import->getAprendices();

            if ($aprendices->isEmpty()) {
                return response()->json([
                    'success' => false,
                    'message' => 'No se encontraron aprendices válidos en el archivo. Verifica que el archivo tenga datos y que las columnas requeridas estén presentes.'
                ], 422);
            }

            // Si no viene ficha_id, mantenemos el comportamiento actual (preview para creación)
            if (!$request->filled('ficha_id')) {
                // En creación/preview: NO incluir aprendices con estados excluidos
                $aprendicesFiltrados = $aprendices->reject(function ($a) use ($estadosExcluidos) {
                    $estadoUpper = strtoupper(trim($a['estado'] ?? ''));
                    return in_array($estadoUpper, $estadosExcluidos, true);
                })->values();

                if ($aprendicesFiltrados->isEmpty()) {
                    return response()->json([
                        'success' => false,
                        'message' => 'No se encontraron aprendices válidos en el archivo. Verifica que el archivo tenga datos, que las columnas requeridas estén presentes y que los aprendices no estén en estados excluidos.'
                    ], 422);
                }

                $aprendicesArray = $aprendicesFiltrados->toArray();

                return response()->json([
                    'success' => true,
                    'message' => count($aprendicesArray) . ' aprendices importados correctamente',
                    'aprendices' => $aprendicesArray,
                    'total' => count($aprendicesArray)
                ]);
            }

            // En edición: SOLO preview (NO guardar en BD). Se aplicará al guardar cambios.
            $ficha = Ficha::with('aprendices')->findOrFail($request->input('ficha_id'));

            $preview = collect();
            $created = 0;
            $updated = 0;
            $skippedExcluded = 0;
            $conflicts = [];

            foreach ($aprendices as $aprendizData) {
                $documento = $this->normalizeDocumento($aprendizData['numero_documento'] ?? '');
                if ($documento === '') {
                    continue;
                }

                $estado = trim($aprendizData['estado'] ?? '');
                $estadoUpper = strtoupper(trim($estado));
                $estadoExcluido = $estadoUpper !== '' && in_array($estadoUpper, $estadosExcluidos, true);

                $existing = Aprendiz::where('documento', $documento)->first();

                if ($existing) {
                    if ((int)$existing->ficha_id !== (int)$ficha->id) {
                        $conflicts[] = [
                            'documento' => $documento,
                            'nombre' => trim(($aprendizData['nombre'] ?? '') . ' ' . ($aprendizData['apellido'] ?? '')),
                            'ficha_id_actual' => $existing->ficha_id,
                            'mensaje' => 'El aprendiz ya existe y está asociado a otra ficha'
                        ];
                        continue;
                    }

                    $updated++;
                    $preview->push(array_merge($aprendizData, ['accion' => 'actualizar']));
                    continue;
                }

                // No proponer creación si viene con estado excluido
                if ($estadoExcluido) {
                    $skippedExcluded++;
                    continue;
                }

                $created++;
                $preview->push(array_merge($aprendizData, ['accion' => 'crear']));
            }

            if ($preview->isEmpty() && empty($conflicts)) {
                return response()->json([
                    'success' => false,
                    'message' => 'No hay aprendices válidos para aplicar en esta ficha.'
                ], 422);
            }

            $message = "Preview listo. Se aplicará al guardar cambios. Nuevos: {$created}, actualizados: {$updated}";
            if ($skippedExcluded > 0) {
                $message .= ". Omitidos por estado excluido: {$skippedExcluded}";
            }
            if (!empty($conflicts)) {
                $message .= ". Conflictos: " . count($conflicts);
            }

            return response()->json([
                'success' => true,
                'message' => $message,
                'created' => $created,
                'updated' => $updated,
                'skipped_excluded' => $skippedExcluded,
                'conflicts' => $conflicts,
                'total' => $preview->count(),
                'aprendices' => $preview->values()->toArray(),
            ]);

        } catch (\Exception $e) {
            if (DB::transactionLevel() > 0) {
                DB::rollBack();
            }
            \Log::error('Error al importar aprendices: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString()
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Error al importar el archivo: ' . $e->getMessage()
            ], 422);
        }
    }

    /**
     * Importar juicios evaluativos desde Excel/CSV y contar resultados por evaluar
     */
    public function importarJuiciosEvaluativos(Request $request)
    {
        $request->validate([
            'archivo' => 'required|mimes:xlsx,xls,csv|max:5120', // 5MB máximo
            'numeros_documento' => 'required|array', // Números de documento de aprendices importados
            'numeros_documento.*' => 'required|string'
        ]);

        try {
            // Obtener números de documento de aprendices importados
            $numerosDocumento = $request->input('numeros_documento', []);
            
            // Limpiar y normalizar números de documento
            $numerosDocumento = array_map(function($doc) {
                return trim($doc);
            }, $numerosDocumento);
            
            $numerosDocumento = array_filter($numerosDocumento); // Eliminar vacíos
            
            if (empty($numerosDocumento)) {
                return response()->json([
                    'success' => false,
                    'message' => 'No hay aprendices importados. Por favor, importa primero los aprendices.'
                ], 422);
            }
            
            $import = new JuiciosEvaluativosImport($numerosDocumento);
            Excel::import($import, $request->file('archivo'));
            
            $conteoPorAprendiz = $import->getConteoPorAprendiz();

            if ($conteoPorAprendiz->isEmpty()) {
                return response()->json([
                    'success' => false,
                    'message' => 'No se encontraron juicios evaluativos válidos en el archivo.'
                ], 422);
            }

            // Convertir la colección a array para JSON
            $conteoArray = $conteoPorAprendiz->toArray();

            return response()->json([
                'success' => true,
                'message' => 'Juicios evaluativos procesados correctamente',
                'conteo_por_aprendiz' => $conteoArray,
                'total_aprendices' => count($conteoArray)
            ]);

        } catch (\Exception $e) {
            \Log::error('Error al importar juicios evaluativos: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString()
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Error al importar el archivo: ' . $e->getMessage()
            ], 422);
        }
    }
    
}
