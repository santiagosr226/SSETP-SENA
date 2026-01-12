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
                                    class="bg-emerald-500 hover:bg-emerald-600 text-white px-1.5 py-1 rounded-md transition duration-200 flex items-center gap-0.5 text-2xs"
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
        class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 p-2 md:p-4"
        style="display: none;"
        @keydown.escape.window="closeView()"
    >
        <div 
            class="bg-white rounded-md shadow-lg w-full max-w-2xl overflow-hidden"
            @click.outside="closeView()"
        >
            <div class="flex items-center justify-between px-3 py-2 bg-verde-sena text-white">
                <h2 class="text-xs md:text-sm font-semibold">Ficha <span x-text="selectedFicha?.numero || ''"></span></h2>
                <button class="text-white/90 hover:text-white" @click="closeView()">
                    <i data-lucide="x" class="w-4 h-4 cursor-pointer"></i>
                </button>
            </div>

            <div class="p-3 md:p-4 text-2xs md:text-xs space-y-4">
                <!-- Encabezado con estado y programa -->
                <div class="grid grid-cols-1 md:grid-cols-3 gap-3 items-start">
                    <div class="md:col-span-2 space-y-1">
                        <p class="text-slate-500">Programa</p>
                        <div class="flex items-center gap-2">
                            <p class="font-semibold truncate" x-text="selectedFicha?.programa?.nombre || 'N/A'"></p>
                            <span class="inline-flex items-center rounded px-1.5 py-0.5 text-[10px] bg-slate-100 text-slate-700 border border-slate-200" x-text="selectedFicha?.programa?.nivel || 'N/A'"></span>
                        </div>
                    </div>
                    <div class="space-y-1">
                        <p class="text-slate-500">Estado</p>
                        <span class="capitalize inline-flex items-center rounded px-2 py-0.5 text-[11px] font-medium bg-slate-100 text-slate-500 border border-slate-200 "
                              x-text="selectedFicha?.estado || 'N/A'"></span>
                    </div>
                </div>

                <hr class="border-slate-200">

                <!-- Fechas -->
                <div>
                    <p class="text-slate-600 font-semibold mb-2">Fechas</p>
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-3">
                        <div>
                            <p class="text-slate-500">Inicial lectiva</p>
                            <p class="font-medium" x-text="formatDate(selectedFicha?.fecha_inicial)"></p>
                        </div>
                        <div>
                            <p class="text-slate-500">Final lectiva</p>
                            <p class="font-medium" x-text="formatDate(selectedFicha?.fecha_final_lectiva)"></p>
                        </div>
                        <div>
                            <p class="text-slate-500">Final formación</p>
                            <p class="font-medium" x-text="formatDate(selectedFicha?.fecha_final_formacion)"></p>
                        </div>
                        <div>
                            <p class="text-slate-500">Límite productiva</p>
                            <p class="font-medium" x-text="formatDate(selectedFicha?.fecha_limite_productiva)"></p>
                        </div>
                        <div>
                            <p class="text-slate-500">Actualización</p>
                            <p class="font-medium" x-text="formatDate(selectedFicha?.fecha_actualizacion)"></p>
                        </div>
                    </div>
                </div>

                <hr class="border-slate-200">

                <!-- Información general -->
                <div>
                    <p class="text-slate-600 font-semibold mb-2">Información</p>
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-3">
                        <div>
                            <p class="text-slate-500">Modalidad</p>
                            <p class="font-medium capitalize" x-text="selectedFicha?.modalidad || 'N/A'"></p>
                        </div>
                        <div>
                            <p class="text-slate-500">Jornada</p>
                            <p class="font-medium capitalize" x-text="selectedFicha?.jornada || 'N/A'"></p>
                        </div>
                        <div>
                            <p class="text-slate-500">Resultados totales</p>
                            <p class="font-medium" x-text="selectedFicha?.resultados_aprendizaje_totales ?? '0'"></p>
                        </div>
                    </div>
                </div>

                <hr class="border-slate-200">

                <!-- Instructor -->
                <div>
                    <p class="text-slate-600 font-semibold mb-2">Instructor</p>
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-3">
                        <div>
                            <p class="text-slate-500">Nombre</p>
                            <p class="font-medium" x-text="selectedFicha?.instructor?.nombre || 'N/A'"></p>
                        </div>
                        <div>
                            <p class="text-slate-500">Correo</p>
                            <p class="font-medium truncate" x-text="selectedFicha?.instructor?.correo || 'N/A'"></p>
                        </div>
                        <div>
                            <p class="text-slate-500">Teléfono</p>
                            <p class="font-medium" x-text="selectedFicha?.instructor?.telefono || 'N/A'"></p>
                        </div>
                    </div>
                </div>

                <div class="pt-1 flex justify-end gap-2">
                    <button class="px-2.5 py-1 rounded-md border border-slate-300 text-slate-700 hover:bg-slate-50 cursor-pointer" @click="closeView()">Cerrar</button>
                    <a :href="`/fichas/${selectedFicha?.id}/edit`" class="px-2.5 py-1 rounded-md bg-verde-sena text-white hover:bg-green-700">Editar</a>
                </div>
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