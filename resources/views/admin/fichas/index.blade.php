@extends('layouts.app')

@section('content')
 

<div x-data="fichasManager()">
    <!-- Header -->
    <div class="mb-3 md:mb-4">
        <h1 class="text-2xs md:text-xs font-semibold text-verde-sena mb-2 md:mb-3 tracking-wide">Gestionar Fichas</h1>
        
        <!-- Barra de búsqueda y botón agregar -->
        <div class="flex flex-col md:flex-row gap-1.5 md:gap-2 items-stretch md:items-center justify-between">
            <form action="{{ route('fichas.index') }}" method="GET" class="relative flex-1 max-w-md min-w-0">
                <input 
                    type="text" 
                    name="search"
                    value="{{ $search ?? '' }}"
                    placeholder="Buscar por número, programa..."
                    class="w-full pl-7 pr-2 py-1.5 md:py-1 text-2xs md:text-xs border border-slate-300 rounded-md focus:outline-none focus:ring-2 focus:ring-verde-sena focus:border-transparent"
                >
                <i data-lucide="search" class="absolute left-2 top-1/2 transform -translate-y-1/2 w-3 h-3 text-slate-400"></i>
                
                @if($search ?? false)
                <a href="{{ route('fichas.index') }}" 
                   class="absolute right-2 top-1/2 transform -translate-y-1/2 text-slate-400 hover:text-red-500 cursor-pointer"
                   title="Limpiar búsqueda">
                    <i data-lucide="x" class="w-3 h-3"></i>
                </a>
                @endif
            </form>
            
            <a 
                href="{{ route('fichas.create') }}"
                class="cursor-pointer bg-verde-sena hover:bg-green-700 text-white font-medium px-2.5 py-1.5 md:py-1 rounded-md transition duration-200 flex items-center justify-center gap-1 text-2xs md:text-xs whitespace-nowrap"
            >
                <i data-lucide="plus" class="w-3 h-3"></i>
                Agregar Ficha
            </a>
        </div>
    </div>

    <!--Mensaje de éxito (manejado por SweetAlert2)-->
    @if(session('success'))
        <div x-init="
            Swal.fire({
                icon: 'success',
                title: '¡Éxito!',
                text: '{{ session('success') }}',
                timer: 3000,
                timerProgressBar: true,
                showConfirmButton: false,
                toast: true,
                position: 'top-end'
            })
        "></div>
    @endif

    <!--Mensajes de error (manejado por SweetAlert2)-->
    @if ($errors->any())
        <div x-init="
            Swal.fire({
                icon: 'error',
                title: 'Error',
                html: '<ul class=\'text-left text-sm\'>' +
                    @foreach ($errors->all() as $error)
                        '<li>{{ $error }}</li>' +
                    @endforeach
                    '</ul>',
                confirmButtonColor: '#39A900'
            })
        "></div>
    @endif

    <!-- Tabla de fichas -->
    <div class="border border-slate-200 rounded-md overflow-hidden mb-4">
        <div class="table-container">
            <table class="w-full text-2xs md:text-xs min-w-max md:min-w-full">
                <thead class="bg-verde-sena text-white">
                    <tr>
                        <th class="px-2 py-1.5 text-center font-medium whitespace-nowrap">Número</th>
                        <th class="px-2 py-1.5 text-center font-medium whitespace-nowrap">Nivel</th>
                        <th class="px-2 py-1.5 text-center font-medium whitespace-nowrap">Programa</th>
                        <th class="px-2 py-1.5 text-center font-medium whitespace-nowrap">Fecha Inicial Lectiva</th>
                        <th class="px-2 py-1.5 text-center font-medium whitespace-nowrap">Fecha Final Lectiva</th>
                        <th class="px-2 py-1.5 text-center font-medium whitespace-nowrap">Fecha Final Formación</th>
                        <th class="px-2 py-1.5 text-center font-medium whitespace-nowrap">Fecha Límite Productiva</th>
                        <th class="px-2 py-1.5 text-center font-medium whitespace-nowrap">Acciones</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-200 bg-white">
                    @forelse($fichas as $ficha)
                    <tr class="hover:bg-slate-50 transition duration-150 text-center align-middle">
                        <!-- Número -->
                        <td class="px-2 py-1.5 text-slate-900 align-middle font-medium">
                            {{ $ficha->numero }}
                        </td>

                        <!-- Nivel del Programa -->
                        <td class="px-2 py-1.5 align-middle">
                            @php
                                $nivel = strtolower($ficha->programa->nivel ?? '');
                                $badgeNivel = 'badge-tco';
                                $nivelTexto = 'TCO';
                                
                                if (str_contains($nivel, 'auxiliar')) {
                                    $badgeNivel = 'badge-aux';
                                    $nivelTexto = 'AUX';
                                } elseif (str_contains($nivel, 'operario')) {
                                    $badgeNivel = 'badge-oper';
                                    $nivelTexto = 'OPER';
                                } elseif (str_contains($nivel, 'tecnologo') || str_contains($nivel, 'tecnólogo')) {
                                    $badgeNivel = 'badge-tgo';
                                    $nivelTexto = 'TGO';
                                } elseif (str_contains($nivel, 'tecnico') || str_contains($nivel, 'técnico')) {
                                    $badgeNivel = 'badge-tco';
                                    $nivelTexto = 'TCO';
                                }
                            @endphp
                            <span class="badge-nivel {{ $badgeNivel }}">
                                {{ $nivelTexto }}
                            </span>
                        </td>

                        <!-- Programa -->
                        <td class="px-2 py-1.5 text-slate-700 align-middle">
                            <div class="truncate max-w-[180px] mx-auto" title="{{ $ficha->programa->nombre ?? 'N/A' }}">
                                {{ $ficha->programa->nombre ?? 'N/A' }}
                            </div>
                        </td>

                        <!-- Fecha Inicial Lectiva -->
                        <td class="px-2 py-1.5 text-slate-700 align-middle">
                            {{ $ficha->fecha_inicial ? \Carbon\Carbon::parse($ficha->fecha_inicial)->format('d/m/Y') : 'N/A' }}
                        </td>

                        <!-- Fecha Final Lectiva -->
                        <td class="px-2 py-1.5 text-slate-700 align-middle">
                            {{ $ficha->fecha_final_lectiva ? \Carbon\Carbon::parse($ficha->fecha_final_lectiva)->format('d/m/Y') : 'N/A' }}
                        </td>

                        <!-- Fecha Final Formación -->
                        <td class="px-2 py-1.5 text-slate-700 align-middle">
                            {{ $ficha->fecha_final_formacion ? \Carbon\Carbon::parse($ficha->fecha_final_formacion)->format('d/m/Y') : 'N/A' }}
                        </td>

                        <!-- Fecha Límite Productiva -->
                        <td class="px-2 py-1.5 text-slate-700 align-middle">
                            {{ $ficha->fecha_limite_productiva ? \Carbon\Carbon::parse($ficha->fecha_limite_productiva)->format('d/m/Y') : 'N/A' }}
                        </td>

                        <!-- Acciones -->
                        <td class="px-2 py-1.5 whitespace-nowrap align-middle">
                            <div class="flex justify-center items-center flex-wrap gap-1">
                                <button 
                                    @click="openView(@js([
                                        'id' => $ficha->id,
                                        'numero' => $ficha->numero,
                                        'estado' => $ficha->estado,
                                        'modalidad' => $ficha->modalidad,
                                        'jornada' => $ficha->jornada,
                                        'fecha_inicial' => $ficha->fecha_inicial,
                                        'fecha_final_lectiva' => $ficha->fecha_final_lectiva,
                                        'fecha_final_formacion' => $ficha->fecha_final_formacion,
                                        'fecha_limite_productiva' => $ficha->fecha_limite_productiva,
                                        'fecha_actualizacion' => $ficha->fecha_actualizacion,
                                        'resultados_aprendizaje_totales' => $ficha->resultados_aprendizaje_totales,
                                        'programa' => [
                                            'nombre' => $ficha->programa->nombre ?? 'N/A',
                                            'nivel' => $ficha->programa->nivel ?? 'N/A',
                                        ],
                                        'instructor' => [
                                            'nombre' => $ficha->instructor->nombre ?? 'N/A',
                                            'correo' => $ficha->instructor->correo ?? 'N/A',
                                            'telefono' => $ficha->instructor->telefono ?? 'N/A',
                                        ],
                                    ]))"
                                    class="bg-emerald-500 hover:bg-emerald-600 text-white px-1.5 py-1 rounded-md transition duration-200 flex items-center gap-0.5 text-2xs cursor-pointer"
                                    title="Ver"
                                >
                                    <i data-lucide="eye" class="w-2.5 h-2.5"></i>
                                    <span class="hidden lg:inline">Ver</span>
                                </button>
                                <a 
                                    href="{{ route('fichas.edit', $ficha->id) }}"
                                    class="bg-blue-500 hover:bg-blue-600 text-white px-1.5 py-1 rounded-md transition duration-200 flex items-center gap-0.5 text-2xs"
                                    title="Editar"
                                >
                                    <i data-lucide="pencil" class="w-2.5 h-2.5"></i>
                                    <span class="hidden lg:inline">Editar</span>
                                </a>

                                <button 
                                    @click="confirmDelete({{ $ficha->id }}, '{{ $ficha->numero }}')"
                                    class="bg-red-500 cursor-pointer hover:bg-red-600 text-white px-1.5 py-1 rounded-md transition duration-200 flex items-center gap-0.5 text-2xs"
                                    title="Eliminar"
                                >
                                    <i data-lucide="trash-2" class="w-2.5 h-2.5"></i>
                                    <span class="hidden lg:inline">Eliminar</span>
                                </button>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="8" class="px-2 py-4 text-center text-slate-500 text-2xs md:text-xs">
                            <div class="flex flex-col items-center gap-1">
                                <i data-lucide="file-text" class="w-6 h-6 text-slate-300"></i>
                                @if($search ?? false)
                                    <p>No se encontraron fichas para "{{ $search }}"</p>
                                    <a href="{{ route('fichas.index') }}" class="text-verde-sena hover:underline text-2xs">
                                        Ver todas las fichas
                                    </a>
                                @else
                                    <p>No hay fichas registradas</p>
                                @endif
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- Modal Ver Ficha -->
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
                            <i data-lucide="file-text" class="w-5 h-5 md:w-6 md:h-6"></i>
                        </div>
                        <div>
                            <h2 class="text-sm md:text-base font-bold">
                                Ficha <span x-text="selectedFicha?.numero || ''"></span>
                            </h2>
                            <p class="text-xs md:text-sm text-white/90 mt-0.5" x-text="selectedFicha?.programa?.nombre || 'N/A'"></p>
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
                    <!-- Programa y Estado -->
                    <div class="bg-white rounded-lg shadow-sm border border-slate-200 p-4 hover:shadow-md transition-shadow duration-200">
                        <div class="flex items-center gap-2 mb-4">
                            <div class="bg-orange-500/10 rounded-lg p-2">
                                <i data-lucide="book-open" class="w-4 h-4 text-orange-600"></i>
                            </div>
                            <h3 class="text-sm md:text-base font-bold text-slate-800">Programa de Formación</h3>
                        </div>
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                            <div class="md:col-span-2 space-y-1">
                                <p class="text-xs text-slate-500 font-medium">Nombre del Programa</p>
                                <p class="text-sm font-semibold text-slate-900" x-text="selectedFicha?.programa?.nombre || 'N/A'"></p>
                            </div>
                            <div class="space-y-1">
                                <p class="text-xs text-slate-500 font-medium">Nivel</p>
                                <span class="inline-flex items-center rounded-md px-2.5 py-1 text-xs font-bold bg-slate-100 text-slate-900 border border-slate-200"
                                      x-text="selectedFicha?.programa?.nivel || 'N/A'"></span>
                            </div>
                            <div class="space-y-1">
                                <p class="text-xs text-slate-500 font-medium">Estado</p>
                                <span class="capitalize inline-flex items-center rounded-md px-2.5 py-1 text-xs font-semibold bg-gradient-to-r from-slate-100 to-slate-50 text-slate-700 border border-slate-200"
                                      x-text="selectedFicha?.estado || 'N/A'"></span>
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
                        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-3">
                            <div class="bg-gradient-to-br from-slate-50 to-white rounded-lg p-3 border border-slate-200">
                                <p class="text-xs text-slate-500 font-medium mb-1">Inicial Lectiva</p>
                                <p class="text-sm font-bold text-slate-900" x-text="formatDate(selectedFicha?.fecha_inicial)"></p>
                            </div>
                            <div class="bg-gradient-to-br from-slate-50 to-white rounded-lg p-3 border border-slate-200">
                                <p class="text-xs text-slate-500 font-medium mb-1">Final Lectiva</p>
                                <p class="text-sm font-bold text-slate-900" x-text="formatDate(selectedFicha?.fecha_final_lectiva)"></p>
                            </div>
                            <div class="bg-gradient-to-br from-slate-50 to-white rounded-lg p-3 border border-slate-200">
                                <p class="text-xs text-slate-500 font-medium mb-1">Final Formación</p>
                                <p class="text-sm font-bold text-slate-900" x-text="formatDate(selectedFicha?.fecha_final_formacion)"></p>
                            </div>
                            <div class="bg-gradient-to-br from-slate-50 to-white rounded-lg p-3 border border-slate-200">
                                <p class="text-xs text-slate-500 font-medium mb-1">Límite Productiva</p>
                                <p class="text-sm font-bold text-slate-900" x-text="formatDate(selectedFicha?.fecha_limite_productiva)"></p>
                            </div>
                            <div class="bg-gradient-to-br from-slate-50 to-white rounded-lg p-3 border border-slate-200">
                                <p class="text-xs text-slate-500 font-medium mb-1">Actualización</p>
                                <p class="text-sm font-bold text-slate-900" x-text="formatDate(selectedFicha?.fecha_actualizacion)"></p>
                            </div>
                        </div>
                    </div>

                    <!-- Información General -->
                    <div class="bg-white rounded-lg shadow-sm border border-slate-200 p-4 hover:shadow-md transition-shadow duration-200">
                        <div class="flex items-center gap-2 mb-4">
                            <div class="bg-blue-500/10 rounded-lg p-2">
                                <i data-lucide="info" class="w-4 h-4 text-blue-600"></i>
                            </div>
                            <h3 class="text-sm md:text-base font-bold text-slate-800">Información General</h3>
                        </div>
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                            <div class="bg-gradient-to-br from-slate-50 to-white rounded-lg p-3 border border-slate-200">
                                <p class="text-xs text-slate-500 font-medium mb-1">Modalidad</p>
                                <p class="text-sm font-bold text-slate-900 capitalize" x-text="selectedFicha?.modalidad || 'N/A'"></p>
                            </div>
                            <div class="bg-gradient-to-br from-slate-50 to-white rounded-lg p-3 border border-slate-200">
                                <p class="text-xs text-slate-500 font-medium mb-1">Jornada</p>
                                <p class="text-sm font-bold text-slate-900 capitalize" x-text="selectedFicha?.jornada || 'N/A'"></p>
                            </div>
                            <div class="bg-gradient-to-br from-blue-50 to-blue-100 rounded-lg p-3 border border-blue-200">
                                <p class="text-xs text-blue-600 font-medium mb-1">Resultados Totales</p>
                                <p class="text-sm font-bold text-blue-700" x-text="selectedFicha?.resultados_aprendizaje_totales ?? '0'"></p>
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
                                <p class="text-sm font-semibold text-slate-900" x-text="selectedFicha?.instructor?.nombre || 'N/A'"></p>
                            </div>
                            <div class="space-y-1">
                                <p class="text-xs text-slate-500 font-medium">Correo</p>
                                <div class="flex items-center gap-2">
                                    <i data-lucide="mail" class="w-3.5 h-3.5 text-verde-sena flex-shrink-0"></i>
                                    <p class="text-sm font-semibold text-slate-900 truncate" x-text="selectedFicha?.instructor?.correo || 'N/A'"></p>
                                </div>
                            </div>
                            <div class="space-y-1">
                                <p class="text-xs text-slate-500 font-medium">Teléfono</p>
                                <div class="flex items-center gap-2">
                                    <i data-lucide="phone" class="w-3.5 h-3.5 text-verde-sena"></i>
                                    <p class="text-sm font-semibold text-slate-900" x-text="selectedFicha?.instructor?.telefono || 'N/A'"></p>
                                </div>
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

    <!-- Paginación -->
    @if($fichas->hasPages())
    <div class="pagination">
        <div class="pagination-info">
            Mostrando {{ $fichas->firstItem() ?? 0 }} - {{ $fichas->lastItem() ?? 0 }} de {{ $fichas->total() }} fichas
        </div>
        
        <nav aria-label="Paginación">
            <ul class="flex flex-wrap items-center gap-1">
                <!-- Enlace Anterior -->
                @if($fichas->onFirstPage())
                <li class="page-item disabled">
                    <span class="page-link">
                        <i data-lucide="chevron-left" class="w-3 h-3"></i>
                    </span>
                </li>
                @else
                <li class="page-item">
                    <a href="{{ $fichas->previousPageUrl() }}{{ $search ? '&search=' . $search : '' }}" 
                       class="page-link">
                        <i data-lucide="chevron-left" class="w-3 h-3"></i>
                    </a>
                </li>
                @endif

                <!-- Números de página -->
                @php
                    $current = $fichas->currentPage();
                    $last = $fichas->lastPage();
                    $start = max(1, $current - 2);
                    $end = min($last, $current + 2);
                @endphp

                @if($start > 1)
                <li class="page-item">
                    <a href="{{ $fichas->url(1) }}{{ $search ? '&search=' . $search : '' }}" 
                       class="page-link">1</a>
                </li>
                @if($start > 2)
                <li class="page-item disabled">
                    <span class="page-link">...</span>
                </li>
                @endif
                @endif

                @for($i = $start; $i <= $end; $i++)
                <li class="page-item {{ $i == $current ? 'active' : '' }}">
                    @if($i == $current)
                    <span class="page-link">{{ $i }}</span>
                    @else
                    <a href="{{ $fichas->url($i) }}{{ $search ? '&search=' . $search : '' }}" 
                       class="page-link">{{ $i }}</a>
                    @endif
                </li>
                @endfor

                @if($end < $last)
                @if($end < $last - 1)
                <li class="page-item disabled">
                    <span class="page-link">...</span>
                </li>
                @endif
                <li class="page-item">
                    <a href="{{ $fichas->url($last) }}{{ $search ? '&search=' . $search : '' }}" 
                       class="page-link">{{ $last }}</a>
                </li>
                @endif

                <!-- Enlace Siguiente -->
                @if($fichas->hasMorePages())
                <li class="page-item">
                    <a href="{{ $fichas->nextPageUrl() }}{{ $search ? '&search=' . $search : '' }}" 
                       class="page-link">
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
function fichasManager() {
    return {
        showViewModal: false,
        selectedFicha: null,
        openView(data) {
            this.selectedFicha = data;
            this.showViewModal = true;
            if (window.lucide?.createIcons) {
                setTimeout(() => lucide.createIcons(), 0);
            }
        },
        closeView() {
            this.showViewModal = false;
            this.selectedFicha = null;
        },
        formatDate(value) {
            if (!value) return 'N/A';
            const d = new Date(value);
            if (isNaN(d.getTime())) return value;
            return d.toLocaleDateString('es-CO', { day: '2-digit', month: '2-digit', year: 'numeric' });
        },
        async confirmDelete(id, numero) {
            const result = await Swal.fire({
                icon: 'warning',
                title: '¿Eliminar ficha?',
                html: `¿Estás seguro de eliminar la ficha <strong>"${numero}"</strong>?<br><br>Esta acción no se puede deshacer.`,
                showCancelButton: true,
                confirmButtonColor: '#df0026',
                cancelButtonColor: '#6c757d',
                confirmButtonText: 'Sí, eliminar',
                cancelButtonText: 'Cancelar',
                focusCancel: true
            });

            if (result.isConfirmed) {
                Swal.fire({
                    title: 'Eliminando ficha...',
                    html: 'Por favor espera',
                    allowOutsideClick: false,
                    allowEscapeKey: false,
                    didOpen: () => {
                        Swal.showLoading();
                    }
                });

                try {
                    const formData = new FormData();
                    formData.append('_token', '{{ csrf_token() }}');
                    formData.append('_method', 'DELETE');

                    const response = await fetch(`/fichas/${id}`, {
                        method: 'POST',
                        body: formData,
                        headers: {
                            'X-Requested-With': 'XMLHttpRequest'
                        }
                    });

                    if (response.ok) {
                        Swal.fire({
                            icon: 'success',
                            title: '¡Eliminado!',
                            text: 'La ficha ha sido eliminada correctamente',
                            timer: 2000,
                            timerProgressBar: true,
                            showConfirmButton: false
                        }).then(() => {
                            window.location.reload();
                        });
                    } else {
                        throw new Error('Error al eliminar la ficha');
                    }
                } catch (error) {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: 'No se pudo eliminar la ficha',
                        confirmButtonColor: '#39A900'
                    });
                }
            }
        }
    }
}

// Inicializar iconos de Lucide cuando se carga la página
document.addEventListener('DOMContentLoaded', () => {
    if (window.lucide?.createIcons) {
        lucide.createIcons();
    }
});
</script>
@endsection