@extends('layouts.app')

@section('content')

<div x-data="aprendicesManager()">
    <div class="mb-3 md:mb-4">
        <h1 class="text-2xs md:text-xs font-semibold text-verde-sena mb-2 md:mb-3 tracking-wide">Gestionar Aprendices</h1>
    </div>

    <div class="border border-slate-200 rounded-md overflow-hidden">
        <div class="table-container">
            <table class="w-full text-2xs md:text-xs min-w-max md:min-w-full">
                <thead class="bg-verde-sena text-white">
                    <tr>
                        <th class="px-2 py-1.5 text-center font-medium whitespace-nowrap">Tipo Doc</th>
                        <th class="px-2 py-1.5 text-center font-medium whitespace-nowrap">Número Doc</th>
                        <th class="px-2 py-1.5 text-center font-medium whitespace-nowrap">Nombres</th>
                        <th class="px-2 py-1.5 text-center font-medium whitespace-nowrap">Apellidos</th>
                        <th class="px-2 py-1.5 text-center font-medium whitespace-nowrap">Ficha</th>
                        <th class="px-2 py-1.5 text-center font-medium whitespace-nowrap">Programa Formación</th>
                        <th class="px-2 py-1.5 text-center font-medium whitespace-nowrap">Estado</th>
                        <th class="px-2 py-1.5 text-center font-medium whitespace-nowrap">RA</th>
                        <th class="px-2 py-1.5 text-center font-medium whitespace-nowrap">Alternativa</th>
                        <th class="px-2 py-1.5 text-center font-medium whitespace-nowrap">Acciones</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-200 bg-white">
                    @forelse($aprendices as $aprendiz)
                        @php
                            $nivel = strtolower($aprendiz->ficha->programa->nivel ?? '');
                            if (str_contains($nivel, 'tecnólogo') || str_contains($nivel, 'tecnologo')) {
                                $suffix = 'TGO';
                            } elseif (str_contains($nivel, 'operario')) {
                                $suffix = 'OPER';
                            } elseif (str_contains($nivel, 'auxiliar')) {
                                $suffix = 'AUX';
                            } else {
                                $suffix = 'TEC';
                            }

                            $programaNombre = $aprendiz->ficha->programa->nombre ?? 'N/A';
                            $programaFormacion = $programaNombre !== 'N/A' ? ($suffix . '-' . $programaNombre) : 'N/A';

                            $alt = trim((string)($aprendiz->alternativa ?? ''));
                            $tieneOtras = ($aprendiz->otrasAlternativas && $aprendiz->otrasAlternativas->count() > 0);
                            $rowClass = 'hover:bg-slate-50';

                            $estadoUpper = strtoupper(trim((string)($aprendiz->estado ?? '')));
                            $estadosRojo = ['RETIRO VOLUNTARIO', 'CANCELADO', 'TRASLADADO', 'APLAZADO'];
                            $isRojo = in_array($estadoUpper, $estadosRojo, true);
                            $isVerde = ($estadoUpper === 'CERTIFICADO');

                            $badgeEstadoClass = 'inline-flex items-center rounded px-2 py-0.5 text-[11px] bg-slate-100 text-slate-700 border border-slate-200 capitalize';
                            $badgeRAClass = 'inline-flex items-center gap-1 px-2 py-0.5 rounded-md text-[11px] font-medium bg-slate-100 text-slate-700 border border-slate-200';

                            if ($isRojo) {
                                $rowClass = 'bg-red-200 hover:bg-red-100';
                                $badgeEstadoClass = 'inline-flex items-center rounded px-2 py-0.5 text-[11px] bg-red-600 text-white border border-red-700 capitalize';
                                $badgeRAClass = 'inline-flex items-center gap-1 px-2 py-0.5 rounded-md text-[11px] font-medium bg-red-600 text-white border border-red-700';
                            } elseif ($isVerde) {
                                $rowClass = 'bg-green-200 hover:bg-green-100';
                                $badgeEstadoClass = 'inline-flex items-center rounded px-2 py-0.5 text-[11px] bg-green-600 text-white border border-green-700 capitalize';
                                $badgeRAClass = 'inline-flex items-center gap-1 px-2 py-0.5 rounded-md text-[11px] font-medium bg-green-600 text-white border border-green-700';
                            } else {
                                if ($alt !== '') {
                                    if (str_contains(strtolower($alt), 'contrato')) {
                                        $rowClass = 'bg-orange-200 hover:bg-orange-100';
                                        $badgeEstadoClass = 'inline-flex items-center rounded px-2 py-0.5 text-[11px] bg-orange-600 text-white border border-orange-700 capitalize';
                                        $badgeRAClass = 'inline-flex items-center gap-1 px-2 py-0.5 rounded-md text-[11px] font-medium bg-orange-600 text-white border border-orange-700';
                                    } else {
                                        $rowClass = 'bg-purple-200 hover:bg-purple-100';
                                        $badgeEstadoClass = 'inline-flex items-center rounded px-2 py-0.5 text-[11px] bg-purple-600 text-white border border-purple-700 capitalize';
                                        $badgeRAClass = 'inline-flex items-center gap-1 px-2 py-0.5 rounded-md text-[11px] font-medium bg-purple-600 text-white border border-purple-700';
                                    }
                                } elseif ($tieneOtras) {
                                    $rowClass = 'bg-purple-200 hover:bg-purple-100';
                                    $badgeEstadoClass = 'inline-flex items-center rounded px-2 py-0.5 text-[11px] bg-purple-600 text-white border border-purple-700 capitalize';
                                    $badgeRAClass = 'inline-flex items-center gap-1 px-2 py-0.5 rounded-md text-[11px] font-medium bg-purple-600 text-white border border-purple-700';
                                }
                            }

                            $resultados = $aprendiz->resultados_aprendizaje ?? '0/0';
                        @endphp
                        <tr class="transition duration-150 text-center align-middle {{ $rowClass }}">
                            <td class="px-2 py-1.5 text-slate-700">{{ $aprendiz->tipo_documento ?? 'CC' }}</td>
                            <td class="px-2 py-1.5 text-slate-900 font-medium">{{ $aprendiz->documento }}</td>
                            <td class="px-2 py-1.5 text-slate-700 capitalize">{{ $aprendiz->nombre }}</td>
                            <td class="px-2 py-1.5 text-slate-700 capitalize">{{ $aprendiz->apellido }}</td>
                            <td class="px-2 py-1.5 text-slate-700">{{ $aprendiz->ficha->numero ?? 'N/A' }}</td>
                            <td class="px-2 py-1.5 text-slate-700">
                                <div class="truncate max-w-[220px] mx-auto" title="{{ $programaFormacion }}">{{ $programaFormacion }}</div>
                            </td>
                            <td class="px-2 py-1.5">
                                <span class="{{ $badgeEstadoClass }}">{{ $aprendiz->estado ?? 'N/A' }}</span>
                            </td>
                            <td class="px-2 py-1.5">
                                <span class="{{ $badgeRAClass }}">{{ $resultados }}</span>
                            </td>
                            <td class="px-2 py-1.5 text-slate-700">
                                {{ $alt !== '' ? $alt : ($tieneOtras ? 'Otra alternativa' : 'N/A') }}
                            </td>
                            <td class="px-2 py-1.5 whitespace-nowrap align-middle">
                                <button 
                                    @click="openView(@js([
                                        'id' => $aprendiz->id,
                                        'tipo_documento' => $aprendiz->tipo_documento ?? 'CC',
                                        'documento' => $aprendiz->documento,
                                        'nombre' => $aprendiz->nombre,
                                        'apellido' => $aprendiz->apellido,
                                        'telefono' => $aprendiz->telefono ?? 'N/A',
                                        'correo' => $aprendiz->correo ?? 'N/A',
                                        'estado' => $aprendiz->estado ?? 'N/A',
                                        'resultados_aprendizaje' => $resultados,
                                        'alternativa' => $alt !== '' ? $alt : ($tieneOtras ? 'Otra alternativa' : 'N/A'),
                                        'ficha' => [
                                            'numero' => $aprendiz->ficha->numero ?? 'N/A',
                                            'estado' => $aprendiz->ficha->estado ?? 'N/A',
                                            'modalidad' => $aprendiz->ficha->modalidad ?? 'N/A',
                                            'jornada' => $aprendiz->ficha->jornada ?? 'N/A',
                                            'fecha_inicial' => $aprendiz->ficha->fecha_inicial ?? null,
                                            'fecha_final_lectiva' => $aprendiz->ficha->fecha_final_lectiva ?? null,
                                            'fecha_final_formacion' => $aprendiz->ficha->fecha_final_formacion ?? null,
                                            'fecha_limite_productiva' => $aprendiz->ficha->fecha_limite_productiva ?? null,
                                        ],
                                        'programa' => [
                                            'nombre' => $aprendiz->ficha->programa->nombre ?? 'N/A',
                                            'nivel' => $aprendiz->ficha->programa->nivel ?? 'N/A',
                                        ],
                                        'instructor' => [
                                            'nombre' => $aprendiz->ficha->instructor->nombre ?? 'N/A',
                                            'correo' => $aprendiz->ficha->instructor->correo ?? 'N/A',
                                            'telefono' => $aprendiz->ficha->instructor->telefono ?? 'N/A',
                                        ],
                                    ]))"
                                    class="bg-emerald-500 hover:bg-emerald-600 text-white px-1.5 py-1 rounded-md transition duration-200 flex items-center gap-0.5 text-2xs mx-auto cursor-pointer"
                                    title="Ver más"
                                >
                                    <i data-lucide="eye" class="w-2.5 h-2.5"></i>
                                    <span>Ver más</span>
                                </button>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="10" class="px-2 py-4 text-center text-slate-500 text-2xs md:text-xs">
                                <div class="flex flex-col items-center gap-1">
                                    <i data-lucide="users" class="w-6 h-6 text-slate-300"></i>
                                    <p>No hay aprendices registrados</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- Modal Ver Aprendiz -->
    <div 
        x-show="showViewModal"
        x-transition.opacity
        class="fixed inset-0 z-50 flex items-center justify-center bg-black/60 backdrop-blur-sm p-2 md:p-4"
        style="display: none;"
        @keydown.escape.window="closeView()"
    >
        <div 
            x-show="showViewModal"
            x-transition:enter="transition ease-out duration-300"
            x-transition:enter-start="opacity-0 transform scale-95"
            x-transition:enter-end="opacity-100 transform scale-100"
            x-transition:leave="transition ease-in duration-200"
            x-transition:leave-start="opacity-100 transform scale-100"
            x-transition:leave-end="opacity-0 transform scale-95"
            class="bg-white rounded-lg shadow-2xl w-full max-w-4xl overflow-hidden max-h-[90vh] flex flex-col"
            @click.outside="closeView()"
        >
            <!-- Header con gradiente -->
            <div class="relative bg-gradient-to-r from-verde-sena to-green-600 text-white px-4 md:px-6 py-4 md:py-5">
                <div class="flex items-start justify-between">
                    <div class="flex items-center gap-3">
                        <div class="bg-white/20 backdrop-blur-sm rounded-full p-2.5">
                            <i data-lucide="user" class="w-5 h-5 md:w-6 md:h-6"></i>
                        </div>
                        <div>
                            <h2 class="text-sm md:text-base font-bold">
                                <span x-text="selectedAprendiz?.nombre || ''"></span> <span x-text="selectedAprendiz?.apellido || ''"></span>
                            </h2>
                            <p class="text-xs md:text-sm text-white/90 mt-0.5">
                                <span x-text="selectedAprendiz?.tipo_documento || 'CC'"></span>: <span x-text="selectedAprendiz?.documento || 'N/A'"></span>
                            </p>
                        </div>
                    </div>
                    <button class="text-white/90 hover:text-white hover:bg-white/20 rounded-full p-2 transition-all duration-200" @click="closeView()">
                        <i data-lucide="x" class="w-5 h-5 cursor-pointer"></i>
                    </button>
                </div>
            </div>

            <!-- Contenido con scroll -->
            <div class="overflow-y-auto flex-1 p-4 md:p-6 bg-gradient-to-b from-slate-50 to-white">
                <div class="space-y-5">
                    <!-- Información Personal -->
                    <div class="bg-white rounded-lg shadow-sm border border-slate-200 p-4 hover:shadow-md transition-shadow duration-200">
                        <div class="flex items-center gap-2 mb-4">
                            <div class="bg-verde-sena/10 rounded-lg p-2">
                                <i data-lucide="user-circle" class="w-4 h-4 text-verde-sena"></i>
                            </div>
                            <h3 class="text-sm md:text-base font-bold text-slate-800">Información Personal</h3>
                        </div>
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                            <div class="space-y-1">
                                <p class="text-xs text-slate-500 font-medium">Estado</p>
                                <span class="capitalize inline-flex items-center rounded-md px-2.5 py-1 text-xs font-semibold bg-gradient-to-r from-slate-100 to-slate-50 text-slate-700 border border-slate-200 shadow-sm"
                                      x-text="selectedAprendiz?.estado || 'N/A'"></span>
                            </div>
                            <div class="space-y-1 md:col-span-1">
                                <p class="text-xs text-slate-500 font-medium">Correo Electrónico</p>
                                <div class="flex items-center gap-2 text-sm font-semibold text-slate-900 truncate">
                                    <i data-lucide="mail" class="w-3.5 h-3.5 text-verde-sena flex-shrink-0"></i>
                                    <span class="truncate" x-text="selectedAprendiz?.correo || 'N/A'"></span>
                                </div>
                            </div>
                            <div class="space-y-1">
                                <p class="text-xs text-slate-500 font-medium">Teléfono</p>
                                <div class="flex items-center gap-2 text-sm font-semibold text-slate-900">
                                    <i data-lucide="phone" class="w-3.5 h-3.5 text-verde-sena"></i>
                                    <span x-text="selectedAprendiz?.telefono || 'N/A'"></span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Información Académica -->
                    <div class="bg-white rounded-lg shadow-sm border border-slate-200 p-4 hover:shadow-md transition-shadow duration-200">
                        <div class="flex items-center gap-2 mb-4">
                            <div class="bg-blue-500/10 rounded-lg p-2">
                                <i data-lucide="graduation-cap" class="w-4 h-4 text-blue-600"></i>
                            </div>
                            <h3 class="text-sm md:text-base font-bold text-slate-800">Información Académica</h3>
                        </div>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div class="space-y-1">
                                <p class="text-xs text-slate-500 font-medium">Resultados de Aprendizaje</p>
                                <div class="flex items-center gap-2">
                                    <div class="bg-gradient-to-r from-blue-50 to-blue-100 rounded-lg px-3 py-2 border border-blue-200">
                                        <span class="text-sm font-bold text-blue-700" x-text="selectedAprendiz?.resultados_aprendizaje || '0/0'"></span>
                                    </div>
                                </div>
                            </div>
                            <div class="space-y-1">
                                <p class="text-xs text-slate-500 font-medium">Alternativa</p>
                                <p class="text-sm font-semibold text-slate-900" x-text="selectedAprendiz?.alternativa || 'N/A'"></p>
                            </div>
                        </div>
                    </div>

                    <!-- Información de la Ficha y Programa en dos columnas -->
                    <div class="grid grid-cols-1 lg:grid-cols-2 gap-4">
                        <!-- Información de la Ficha -->
                        <div class="bg-white rounded-lg shadow-sm border border-slate-200 p-4 hover:shadow-md transition-shadow duration-200">
                            <div class="flex items-center gap-2 mb-4">
                                <div class="bg-purple-500/10 rounded-lg p-2">
                                    <i data-lucide="file-text" class="w-4 h-4 text-purple-600"></i>
                                </div>
                                <h3 class="text-sm md:text-base font-bold text-slate-800">Ficha</h3>
                            </div>
                            <div class="space-y-3">
                                <div class="flex items-center justify-between py-2 border-b border-slate-100">
                                    <span class="text-xs text-slate-500">Número</span>
                                    <span class="text-sm font-bold text-verde-sena" x-text="selectedAprendiz?.ficha?.numero || 'N/A'"></span>
                                </div>
                                <div class="flex items-center justify-between py-2 border-b border-slate-100">
                                    <span class="text-xs text-slate-500">Estado</span>
                                    <span class="text-sm font-semibold text-slate-900 capitalize" x-text="selectedAprendiz?.ficha?.estado || 'N/A'"></span>
                                </div>
                                <div class="flex items-center justify-between py-2 border-b border-slate-100">
                                    <span class="text-xs text-slate-500">Modalidad</span>
                                    <span class="text-sm font-semibold text-slate-900 capitalize" x-text="selectedAprendiz?.ficha?.modalidad || 'N/A'"></span>
                                </div>
                                <div class="flex items-center justify-between py-2">
                                    <span class="text-xs text-slate-500">Jornada</span>
                                    <span class="text-sm font-semibold text-slate-900 capitalize" x-text="selectedAprendiz?.ficha?.jornada || 'N/A'"></span>
                                </div>
                            </div>
                        </div>

                        <!-- Programa de Formación -->
                        <div class="bg-white rounded-lg shadow-sm border border-slate-200 p-4 hover:shadow-md transition-shadow duration-200">
                            <div class="flex items-center gap-2 mb-4">
                                <div class="bg-orange-500/10 rounded-lg p-2">
                                    <i data-lucide="book-open" class="w-4 h-4 text-orange-600"></i>
                                </div>
                                <h3 class="text-sm md:text-base font-bold text-slate-800">Programa</h3>
                            </div>
                            <div class="space-y-3">
                                <div class="space-y-1">
                                    <p class="text-xs text-slate-500 font-medium">Nombre del Programa</p>
                                    <p class="text-sm font-semibold text-slate-900 leading-tight" x-text="selectedAprendiz?.programa?.nombre || 'N/A'"></p>
                                </div>
                                <div class="space-y-1 pt-2">
                                    <p class="text-xs text-slate-500 font-medium">Nivel</p>
                                    <span class="inline-flex items-center rounded-md px-2.5 py-1 text-xs font-bold bg-gradient-to-r from-orange-50 to-orange-100 text-orange-700 border border-orange-200" 
                                          x-text="selectedAprendiz?.programa?.nivel || 'N/A'"></span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Fechas de Formación -->
                    <div class="bg-white rounded-lg shadow-sm border border-slate-200 p-4 hover:shadow-md transition-shadow duration-200">
                        <div class="flex items-center gap-2 mb-4">
                            <div class="bg-emerald-500/10 rounded-lg p-2">
                                <i data-lucide="calendar" class="w-4 h-4 text-emerald-600"></i>
                            </div>
                            <h3 class="text-sm md:text-base font-bold text-slate-800">Fechas de Formación</h3>
                        </div>
                        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3">
                            <div class="bg-gradient-to-br from-slate-50 to-white rounded-lg p-3 border border-slate-200">
                                <p class="text-xs text-slate-500 font-medium mb-1">Inicial Lectiva</p>
                                <p class="text-sm font-bold text-slate-900" x-text="formatDate(selectedAprendiz?.ficha?.fecha_inicial)"></p>
                            </div>
                            <div class="bg-gradient-to-br from-slate-50 to-white rounded-lg p-3 border border-slate-200">
                                <p class="text-xs text-slate-500 font-medium mb-1">Final Lectiva</p>
                                <p class="text-sm font-bold text-slate-900" x-text="formatDate(selectedAprendiz?.ficha?.fecha_final_lectiva)"></p>
                            </div>
                            <div class="bg-gradient-to-br from-slate-50 to-white rounded-lg p-3 border border-slate-200">
                                <p class="text-xs text-slate-500 font-medium mb-1">Final Formación</p>
                                <p class="text-sm font-bold text-slate-900" x-text="formatDate(selectedAprendiz?.ficha?.fecha_final_formacion)"></p>
                            </div>
                            <div class="bg-gradient-to-br from-slate-50 to-white rounded-lg p-3 border border-slate-200">
                                <p class="text-xs text-slate-500 font-medium mb-1">Límite Productiva</p>
                                <p class="text-sm font-bold text-slate-900" x-text="formatDate(selectedAprendiz?.ficha?.fecha_limite_productiva)"></p>
                            </div>
                        </div>
                    </div>

                    <!-- Instructor -->
                    <div class="bg-gradient-to-br from-verde-sena/5 to-white rounded-lg shadow-sm border border-verde-sena/20 p-4 hover:shadow-md transition-shadow duration-200">
                        <div class="flex items-center gap-2 mb-4">
                            <div class="bg-verde-sena/10 rounded-lg p-2">
                                <i data-lucide="user-check" class="w-4 h-4 text-verde-sena"></i>
                            </div>
                            <h3 class="text-sm md:text-base font-bold text-slate-800">Instructor Lider</h3>
                        </div>
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                            <div class="space-y-1">
                                <p class="text-xs text-slate-500 font-medium">Nombre</p>
                                <p class="text-sm font-semibold text-slate-900" x-text="selectedAprendiz?.instructor?.nombre || 'N/A'"></p>
                            </div>
                            <div class="space-y-1">
                                <p class="text-xs text-slate-500 font-medium">Correo</p>
                                <p class="text-sm font-semibold text-slate-900 truncate" x-text="selectedAprendiz?.instructor?.correo || 'N/A'"></p>
                            </div>
                            <div class="space-y-1">
                                <p class="text-xs text-slate-500 font-medium">Teléfono</p>
                                <p class="text-sm font-semibold text-slate-900" x-text="selectedAprendiz?.instructor?.telefono || 'N/A'"></p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Footer -->
            <div class="border-t border-slate-200 bg-slate-50 px-4 md:px-6 py-3 md:py-4 flex justify-end gap-2">
                <button class="px-4 py-2 rounded-lg text-sm font-medium border border-slate-300 text-slate-700 hover:bg-white hover:shadow-sm transition-all duration-200 cursor-pointer" @click="closeView()">
                    Cerrar
                </button>
            </div>
        </div>
    </div>

    @if($aprendices->hasPages())
    <div class="pagination mt-3">
        <div class="pagination-info">
            Mostrando {{ $aprendices->firstItem() ?? 0 }} - {{ $aprendices->lastItem() ?? 0 }} de {{ $aprendices->total() }} aprendices
        </div>
        <nav aria-label="Paginación aprendices">
            <ul class="flex flex-wrap items-center gap-1">
                @if($aprendices->onFirstPage())
                    <li class="page-item disabled">
                        <span class="page-link">
                            <i data-lucide="chevron-left" class="w-3 h-3"></i>
                        </span>
                    </li>
                @else
                    <li class="page-item">
                        <a href="{{ $aprendices->previousPageUrl() }}" class="page-link">
                            <i data-lucide="chevron-left" class="w-3 h-3"></i>
                        </a>
                    </li>
                @endif

                @php
                    $current = $aprendices->currentPage();
                    $last = $aprendices->lastPage();
                    $start = max(1, $current - 2);
                    $end = min($last, $current + 2);
                @endphp

                @if($start > 1)
                    <li class="page-item">
                        <a href="{{ $aprendices->url(1) }}" class="page-link">1</a>
                    </li>
                    @if($start > 2)
                        <li class="page-item disabled"><span class="page-link">...</span></li>
                    @endif
                @endif

                @for($i = $start; $i <= $end; $i++)
                    <li class="page-item {{ $i == $current ? 'active' : '' }}">
                        @if($i == $current)
                            <span class="page-link">{{ $i }}</span>
                        @else
                            <a href="{{ $aprendices->url($i) }}" class="page-link">{{ $i }}</a>
                        @endif
                    </li>
                @endfor

                @if($end < $last)
                    @if($end < $last - 1)
                        <li class="page-item disabled"><span class="page-link">...</span></li>
                    @endif
                    <li class="page-item">
                        <a href="{{ $aprendices->url($last) }}" class="page-link">{{ $last }}</a>
                    </li>
                @endif

                @if($aprendices->hasMorePages())
                    <li class="page-item">
                        <a href="{{ $aprendices->nextPageUrl() }}" class="page-link">
                            <i data-lucide="chevron-right" class="w-3 h-3"></i>
                        </a>
                    </li>
                @else
                    <li class="page-item disabled">
                        <span class="page-link">
                            <i data-lucide="chevron-right" class="w-3 h-3"></i>
                        </span>
                    </li>
                @endif
            </ul>
        </nav>
    </div>
    @endif
</div>

<script>
function aprendicesManager() {
    return {
        showViewModal: false,
        selectedAprendiz: null,
        openView(data) {
            this.selectedAprendiz = data;
            this.showViewModal = true;
            if (window.lucide?.createIcons) {
                setTimeout(() => lucide.createIcons(), 0);
            }
        },
        closeView() {
            this.showViewModal = false;
            this.selectedAprendiz = null;
        },
        formatDate(value) {
            if (!value) return 'N/A';
            const d = new Date(value);
            if (isNaN(d.getTime())) return value;
            return d.toLocaleDateString('es-CO', { day: '2-digit', month: '2-digit', year: 'numeric' });
        }
    }
}

document.addEventListener('DOMContentLoaded', () => {
    if (window.lucide?.createIcons) {
        lucide.createIcons();
    }
});
</script>

@endsection