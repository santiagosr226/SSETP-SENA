@extends('layouts.app')

@section('content')
<style>
/* Estilos para botones de importación */
.btn-import {
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
    padding: 0.5rem 1rem;
    border-radius: 0.375rem;
    font-size: 0.75rem;
    font-weight: 500;
    transition: all 0.2s;
    cursor: pointer;
    border: none;
    outline: none;
}

.btn-import-aprendices {
    background-color: #3b82f6;
    color: white;
}

.btn-import-aprendices:hover {
    background-color: #2563eb;
}

.btn-import-juicios {
    background-color: #8b5cf6;
    color: white;
}

.btn-import-juicios:hover {
    background-color: #7c3aed;
}

/* Estilos para la tabla de aprendices */
.table-container {
    overflow-x: auto;
}

.table-header {
    background-color: #39A900;
    color: white;
}

.table-row:hover {
    background-color: #f8fafc;
}

/* Badges para estados */
.badge-estado {
    padding: 0.25rem 0.5rem;
    border-radius: 9999px;
    font-size: 0.625rem;
    font-weight: 500;
    text-transform: capitalize;
}

.badge-activo {
    background-color: #d1fae5;
    color: #065f46;
}

.badge-inactivo {
    background-color: #fee2e2;
    color: #991b1b;
}

.badge-pendiente {
    background-color: #fef3c7;
    color: #92400e;
}

/* Input styles */
.input-field {
    width: 100%;
    padding: 0.375rem 0.75rem;
    font-size: 0.75rem;
    border: 1px solid #d1d5db;
    border-radius: 0.375rem;
    transition: border-color 0.2s;
}

.input-field:focus {
    outline: none;
    border-color: #39A900;
    ring: 2px;
    ring-color: rgba(57, 169, 0, 0.2);
}

.select-field {
    width: 100%;
    padding: 0.375rem 0.75rem;
    font-size: 0.75rem;
    border: 1px solid #d1d5db;
    border-radius: 0.375rem;
    background-color: white;
    cursor: pointer;
    transition: border-color 0.2s;
}

.select-field:focus {
    outline: none;
    border-color: #39A900;
    ring: 2px;
    ring-color: rgba(57, 169, 0, 0.2);
}

/* Label styles */
.form-label {
    display: block;
    font-size: 0.75rem;
    font-weight: 500;
    color: #374151;
    margin-bottom: 0.25rem;
}

/* Grid para formularios */
.form-grid {
    display: grid;
    grid-template-columns: repeat(1, 1fr);
    gap: 1rem;
}

@media (min-width: 768px) {
    .form-grid {
        grid-template-columns: repeat(2, 1fr);
    }
}

@media (min-width: 1024px) {
    .form-grid {
        grid-template-columns: repeat(4, 1fr);
    }
}

/* Card styles */
.form-card {
    background-color: white;
    border: 1px solid #e5e7eb;
    border-radius: 0.5rem;
    padding: 1.5rem;
    margin-bottom: 1.5rem;
}

/* Modal de importación */
.modal-overlay {
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background-color: rgba(0, 0, 0, 0.5);
    display: none;
    justify-content: center;
    align-items: center;
    z-index: 9999;
    padding: 1rem;
}

.modal-overlay.show {
    display: flex;
}

.modal-content {
    background: white;
    border-radius: 0.5rem;
    max-width: 500px;
    width: 100%;
    max-height: 90vh;
    overflow-y: auto;
    box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1);
}

.modal-header {
    padding: 1.25rem;
    border-bottom: 1px solid #e2e8f0;
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.modal-body {
    padding: 1.25rem;
}

/* Resultados por evaluar badge */
.badge-resultados {
    padding: 0.25rem 0.5rem;
    border-radius: 0.25rem;
    font-size: 0.625rem;
    font-weight: 600;
    background-color: #dbeafe;
    color: #1e40af;
}
</style>

<div>
    <!-- Header -->
    <div class="mb-6">
        <h1 class="text-xs font-semibold text-verde-sena mb-2 tracking-wide">Crear Ficha</h1>
        
        <!-- Botón para volver atrás -->
        <div class="flex justify-between items-center">
            <a 
                href="{{ route('fichas.index') }}"
                class="inline-flex items-center gap-1 text-slate-600 hover:text-verde-sena text-2xs transition duration-200"
            >
                <i data-lucide="arrow-left" class="w-3 h-3"></i>
                Volver a fichas
            </a>
        </div>
    </div>

    <!-- Formulario principal -->
    <form action="{{ route('fichas.store') }}" method="POST" class="space-y-6">
        @csrf
        
        <!-- Card de información básica -->
        <div class="form-card">
            <h2 class="text-sm font-semibold text-verde-sena mb-4">Información Básica</h2>
            
            <div class="form-grid">
                <!-- Número de Ficha -->
                <div>
                    <label for="numero" class="form-label">Número de Ficha *</label>
                    <input 
                        type="text" 
                        id="numero" 
                        name="numero" 
                        value="{{ old('numero') }}"
                        class="input-field"
                        required
                        placeholder="Ej: 123456"
                    >
                    @error('numero')
                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Programa -->
                <div>
                    <label for="programa_id" class="form-label">Programa *</label>
                    <select 
                        id="programa_id" 
                        name="programa_id" 
                        class="select-field"
                        required
                    >
                        <option value="">Seleccionar programa</option>
                        @foreach($programas as $programa)
                            <option value="{{ $programa->id }}" {{ old('programa_id') == $programa->id ? 'selected' : '' }}>
                                {{ $programa->nombre }} - {{ $programa->nivel }}
                            </option>
                        @endforeach
                    </select>
                    @error('programa_id')
                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Instructor Líder -->
                <div>
                    <label for="instructor_id" class="form-label">Instructor Líder *</label>
                    <select 
                        id="instructor_id" 
                        name="instructor_id" 
                        class="select-field"
                        required
                    >
                        <option value="">Seleccionar instructor</option>
                        @foreach($instructores as $instructor)
                            <option value="{{ $instructor->id }}" {{ old('instructor_id') == $instructor->id ? 'selected' : '' }}>
                                {{ $instructor->nombre }} {{ $instructor->apellido }}
                            </option>
                        @endforeach
                    </select>
                    @error('instructor_id')
                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Estado -->
                <div>
                    <label for="estado" class="form-label">Estado *</label>
                    <select 
                        id="estado" 
                        name="estado" 
                        class="select-field"
                        required
                    >
                        <option value="">Seleccionar estado</option>
                        <option value="activo" {{ old('estado') == 'activo' ? 'selected' : '' }}>Activo</option>
                        <option value="inactivo" {{ old('estado') == 'inactivo' ? 'selected' : '' }}>Inactivo</option>
                        <option value="finalizado" {{ old('estado') == 'finalizado' ? 'selected' : '' }}>Finalizado</option>
                        <option value="en curso" {{ old('estado') == 'en curso' ? 'selected' : '' }}>En Curso</option>
                    </select>
                    @error('estado')
                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>
            </div>
        </div>

        <!-- Card de fechas -->
        <div class="form-card">
            <h2 class="text-sm font-semibold text-verde-sena mb-4">Fechas Importantes</h2>
            
            <div class="form-grid">
                <!-- Fecha Inicial -->
                <div>
                    <label for="fecha_inicial" class="form-label">Fecha Inicial *</label>
                    <input 
                        type="date" 
                        id="fecha_inicial" 
                        name="fecha_inicial" 
                        value="{{ old('fecha_inicial') }}"
                        class="input-field"
                        required
                    >
                    @error('fecha_inicial')
                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Fecha Final Lectiva -->
                <div>
                    <label for="fecha_final_lectiva" class="form-label">Fecha Final Lectiva</label>
                    <input 
                        type="date" 
                        id="fecha_final_lectiva" 
                        name="fecha_final_lectiva" 
                        value="{{ old('fecha_final_lectiva') }}"
                        class="input-field"
                    >
                    @error('fecha_final_lectiva')
                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Fecha Final Formación -->
                <div>
                    <label for="fecha_final_formacion" class="form-label">Fecha Final Formación</label>
                    <input 
                        type="date" 
                        id="fecha_final_formacion" 
                        name="fecha_final_formacion" 
                        value="{{ old('fecha_final_formacion') }}"
                        class="input-field"
                    >
                    @error('fecha_final_formacion')
                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Fecha Límite Productiva -->
                <div>
                    <label for="fecha_limite_productiva" class="form-label">Fecha Límite Productiva</label>
                    <input 
                        type="date" 
                        id="fecha_limite_productiva" 
                        name="fecha_limite_productiva" 
                        value="{{ old('fecha_limite_productiva') }}"
                        class="input-field"
                    >
                    @error('fecha_limite_productiva')
                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>
            </div>
        </div>

        <!-- Card de configuración adicional -->
        <div class="form-card">
            <h2 class="text-sm font-semibold text-verde-sena mb-4">Configuración Adicional</h2>
            
            <div class="form-grid">
                <!-- Modalidad -->
                <div>
                    <label for="modalidad" class="form-label">Modalidad *</label>
                    <select 
                        id="modalidad" 
                        name="modalidad" 
                        class="select-field"
                        required
                    >
                        <option value="">Seleccionar modalidad</option>
                        <option value="presencial" {{ old('modalidad') == 'presencial' ? 'selected' : '' }}>Presencial</option>
                        <option value="virtual" {{ old('modalidad') == 'virtual' ? 'selected' : '' }}>Virtual</option>
                        <option value="mixta" {{ old('modalidad') == 'mixta' ? 'selected' : '' }}>Mixta</option>
                    </select>
                    @error('modalidad')
                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Jornada -->
                <div>
                    <label for="jornada" class="form-label">Jornada *</label>
                    <select 
                        id="jornada" 
                        name="jornada" 
                        class="select-field"
                        required
                    >
                        <option value="">Seleccionar jornada</option>
                        <option value="diurna" {{ old('jornada') == 'diurna' ? 'selected' : '' }}>Diurna</option>
                        <option value="nocturna" {{ old('jornada') == 'nocturna' ? 'selected' : '' }}>Nocturna</option>
                        <option value="mixta" {{ old('jornada') == 'mixta' ? 'selected' : '' }}>Mixta</option>
                    </select>
                    @error('jornada')
                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Fecha Actualización -->
                <div>
                    <label for="fecha_actualizacion" class="form-label">Fecha Actualización</label>
                    <input 
                        type="date" 
                        id="fecha_actualizacion" 
                        name="fecha_actualizacion" 
                        value="{{ old('fecha_actualizacion') }}"
                        class="input-field"
                    >
                    @error('fecha_actualizacion')
                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Resultados de Aprendizaje Totales -->
                <div>
                    <label for="resultados_aprendizaje_totales" class="form-label">Resultados de Aprendizaje Totales</label>
                    <input 
                        type="number" 
                        id="resultados_aprendizaje_totales" 
                        name="resultados_aprendizaje_totales" 
                        value="{{ old('resultados_aprendizaje_totales', 0) }}"
                        class="input-field"
                        min="0"
                    >
                    @error('resultados_aprendizaje_totales')
                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>
            </div>
        </div>

        <!-- Botones de importación -->
        <div class="form-card">
            <h2 class="text-sm font-semibold text-verde-sena mb-4">Importar Datos</h2>
            
            <div class="flex flex-wrap gap-3">
                <button 
                    type="button" 
                    onclick="openImportModal('aprendices')"
                    class="btn-import btn-import-aprendices"
                >
                    <i data-lucide="users" class="w-3 h-3"></i>
                    Importar Aprendices
                </button>
                
                <button 
                    type="button" 
                    onclick="openImportModal('juicios')"
                    class="btn-import btn-import-juicios"
                >
                    <i data-lucide="clipboard-check" class="w-3 h-3"></i>
                    Importar Juicios Evaluativos
                </button>
            </div>
        </div>

        <!-- Listado de aprendices -->
        <div class="form-card">
            <h2 class="text-sm font-semibold text-verde-sena mb-4">Aprendices Asociados</h2>
            
            {{-- @if($aprendices && $aprendices->count() > 0)
                <div class="border border-slate-200 rounded-md overflow-hidden">
                    <div class="table-container">
                        <table class="w-full text-2xs md:text-xs min-w-max md:min-w-full">
                            <thead class="table-header">
                                <tr>
                                    <th class="px-3 py-2 text-center font-medium">Tipo de Doc</th>
                                    <th class="px-3 py-2 text-center font-medium">Número ID</th>
                                    <th class="px-3 py-2 text-center font-medium">Nombre</th>
                                    <th class="px-3 py-2 text-center font-medium">Apellido</th>
                                    <th class="px-3 py-2 text-center font-medium">Celular</th>
                                    <th class="px-3 py-2 text-center font-medium">Email</th>
                                    <th class="px-3 py-2 text-center font-medium">Estado</th>
                                    <th class="px-3 py-2 text-center font-medium">Resultados por Evaluar</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-200 bg-white">
                                @foreach($aprendices as $aprendiz)
                                    <tr class="table-row text-center">
                                        <td class="px-3 py-2 text-slate-700">
                                            {{ $aprendiz->tipo_documento }}
                                        </td>
                                        <td class="px-3 py-2 text-slate-700 font-medium">
                                            {{ $aprendiz->numero_documento }}
                                        </td>
                                        <td class="px-3 py-2 text-slate-700">
                                            {{ $aprendiz->nombre }}
                                        </td>
                                        <td class="px-3 py-2 text-slate-700">
                                            {{ $aprendiz->apellido }}
                                        </td>
                                        <td class="px-3 py-2 text-slate-700">
                                            {{ $aprendiz->celular ?? 'N/A' }}
                                        </td>
                                        <td class="px-3 py-2 text-slate-700">
                                            {{ $aprendiz->email ?? 'N/A' }}
                                        </td>
                                        <td class="px-3 py-2">
                                            @php
                                                $estadoClass = 'badge-pendiente';
                                                $estadoText = 'Pendiente';
                                                
                                                if(isset($aprendiz->estado)) {
                                                    switch(strtolower($aprendiz->estado)) {
                                                        case 'activo':
                                                            $estadoClass = 'badge-activo';
                                                            $estadoText = 'Activo';
                                                            break;
                                                        case 'inactivo':
                                                            $estadoClass = 'badge-inactivo';
                                                            $estadoText = 'Inactivo';
                                                            break;
                                                    }
                                                }
                                            @endphp
                                            <span class="badge-estado {{ $estadoClass }}">
                                                {{ $estadoText }}
                                            </span>
                                        </td>
                                        <td class="px-3 py-2">
                                            <span class="badge-resultados">
                                                {{ $aprendiz->resultados_pendientes ?? 0 }}
                                            </span>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            @else
                <div class="text-center py-8 border border-dashed border-slate-300 rounded-md">
                    <i data-lucide="users" class="w-8 h-8 text-slate-300 mx-auto mb-2"></i>
                    <p class="text-slate-500 text-xs">No hay aprendices asociados a esta ficha</p>
                    <p class="text-slate-400 text-2xs mt-1">Utiliza el botón "Importar Aprendices" para agregar aprendices</p>
                </div>
            @endif --}}
        </div>

        <!-- Botones de acción -->
        <div class="flex justify-end gap-3 pt-4 border-t border-slate-200">
            <a 
                href="{{ route('fichas.index') }}"
                class="px-4 py-2 text-xs font-medium text-slate-700 bg-slate-100 hover:bg-slate-200 rounded-md transition duration-200"
            >
                Cancelar
            </a>
            
            <button 
                type="submit"
                class="px-4 py-2 text-xs font-medium text-white bg-verde-sena hover:bg-green-700 rounded-md transition duration-200 flex items-center gap-1"
            >
                <i data-lucide="save" class="w-3 h-3"></i>
                Crear Ficha
            </button>
        </div>
    </form>

    <!-- Modal de Importación -->
    <div id="importModal" class="modal-overlay" onclick="closeImportModal(event)">
        <div class="modal-content" onclick="event.stopPropagation()">
            <div class="modal-header">
                <h3 id="modalTitle" class="text-sm font-semibold text-verde-sena"></h3>
                <button onclick="closeImportModal()" class="text-slate-400 hover:text-slate-600">
                    <i data-lucide="x" class="w-5 h-5"></i>
                </button>
            </div>
            <div class="modal-body">
                <div id="modalContent">
                    <!-- Contenido dinámico del modal -->
                </div>
            </div>
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
</div>

<script>
// Función para abrir el modal de importación
function openImportModal(type) {
    const modal = document.getElementById('importModal');
    const title = document.getElementById('modalTitle');
    const content = document.getElementById('modalContent');
    
    if (type === 'aprendices') {
        title.textContent = 'Importar Aprendices';
        content.innerHTML = `
            <div class="space-y-4">
                <p class="text-xs text-slate-600">
                    Sube un archivo Excel o CSV con la información de los aprendices. El archivo debe contener las siguientes columnas:
                </p>
                
                <div class="bg-slate-50 p-3 rounded-md">
                    <code class="text-2xs text-slate-700">
                        tipo_documento, numero_documento, nombre, apellido, celular, email, estado
                    </code>
                </div>
                
                <form id="importAprendicesForm" class="space-y-3" enctype="multipart/form-data">
                    @csrf
                    <div>
                        <label for="archivo_aprendices" class="block text-xs font-medium text-slate-700 mb-1">
                            Seleccionar archivo
                        </label>
                        <input 
                            type="file" 
                            id="archivo_aprendices" 
                            name="archivo"
                            accept=".xlsx,.xls,.csv"
                            class="block w-full text-xs text-slate-500
                                file:mr-4 file:py-1.5 file:px-3
                                file:rounded-md file:border-0
                                file:text-xs file:font-medium
                                file:bg-verde-sena file:text-white
                                hover:file:bg-green-700
                                cursor-pointer"
                            required
                        >
                    </div>
                    
                    <div class="flex justify-end gap-2 pt-2">
                        <button 
                            type="button" 
                            onclick="closeImportModal()"
                            class="px-3 py-1.5 text-xs font-medium text-slate-700 bg-slate-100 hover:bg-slate-200 rounded-md transition duration-200"
                        >
                            Cancelar
                        </button>
                        
                        <button 
                            type="submit"
                            class="px-3 py-1.5 text-xs font-medium text-white bg-verde-sena hover:bg-green-700 rounded-md transition duration-200 flex items-center gap-1"
                        >
                            <i data-lucide="upload" class="w-3 h-3"></i>
                            Importar
                        </button>
                    </div>
                </form>
            </div>
        `;
        
        // Agregar evento al formulario
        document.getElementById('importAprendicesForm').addEventListener('submit', async function(e) {
            e.preventDefault();
            await handleImport('aprendices');
        });
        
    } else if (type === 'juicios') {
        title.textContent = 'Importar Juicios Evaluativos';
        content.innerHTML = `
            <div class="space-y-4">
                <p class="text-xs text-slate-600">
                    Sube un archivo Excel o CSV con los juicios evaluativos de los aprendices.
                </p>
                
                <div class="bg-slate-50 p-3 rounded-md">
                    <code class="text-2xs text-slate-700">
                        numero_documento, resultado_aprendizaje, juicio_evaluativo, fecha_evaluacion
                    </code>
                </div>
                
                <form id="importJuiciosForm" class="space-y-3" enctype="multipart/form-data">
                    @csrf
                    <div>
                        <label for="archivo_juicios" class="block text-xs font-medium text-slate-700 mb-1">
                            Seleccionar archivo
                        </label>
                        <input 
                            type="file" 
                            id="archivo_juicios" 
                            name="archivo"
                            accept=".xlsx,.xls,.csv"
                            class="block w-full text-xs text-slate-500
                                file:mr-4 file:py-1.5 file:px-3
                                file:rounded-md file:border-0
                                file:text-xs file:font-medium
                                file:bg-purple-600 file:text-white
                                hover:file:bg-purple-700
                                cursor-pointer"
                            required
                        >
                    </div>
                    
                    <div class="flex justify-end gap-2 pt-2">
                        <button 
                            type="button" 
                            onclick="closeImportModal()"
                            class="px-3 py-1.5 text-xs font-medium text-slate-700 bg-slate-100 hover:bg-slate-200 rounded-md transition duration-200"
                        >
                            Cancelar
                        </button>
                        
                        <button 
                            type="submit"
                            class="px-3 py-1.5 text-xs font-medium text-white bg-purple-600 hover:bg-purple-700 rounded-md transition duration-200 flex items-center gap-1"
                        >
                            <i data-lucide="upload" class="w-3 h-3"></i>
                            Importar
                        </button>
                    </div>
                </form>
            </div>
        `;
        
        // Agregar evento al formulario
        document.getElementById('importJuiciosForm').addEventListener('submit', async function(e) {
            e.preventDefault();
            await handleImport('juicios');
        });
    }
    
    modal.classList.add('show');
    document.body.style.overflow = 'hidden';
    
    // Re-inicializar iconos de Lucide
    if (window.lucide?.createIcons) {
        lucide.createIcons();
    }
}

// Función para manejar la importación
async function handleImport(type) {
    const formId = type === 'aprendices' ? 'importAprendicesForm' : 'importJuiciosForm';
    const form = document.getElementById(formId);
    const formData = new FormData(form);
    
    // Agregar el tipo de importación
    formData.append('tipo', type);
    
    // Mostrar loading
    Swal.fire({
        title: 'Importando...',
        html: 'Por favor espera',
        allowOutsideClick: false,
        allowEscapeKey: false,
        didOpen: () => {
            Swal.showLoading();
        }
    });
    
    try {
        // Aquí deberías hacer la petición AJAX a tu backend
        // Por ejemplo:
        // const response = await fetch('/importar/' + type, {
        //     method: 'POST',
        //     body: formData
        // });
        
        // Por ahora, simulamos una respuesta exitosa
        setTimeout(() => {
            Swal.fire({
                icon: 'success',
                title: '¡Importación exitosa!',
                text: `Los ${type} han sido importados correctamente`,
                confirmButtonColor: '#39A900'
            }).then(() => {
                closeImportModal();
                // Recargar la página o actualizar la tabla
                // window.location.reload();
            });
        }, 1500);
        
    } catch (error) {
        Swal.fire({
            icon: 'error',
            title: 'Error',
            text: 'No se pudo importar el archivo',
            confirmButtonColor: '#39A900'
        });
    }
}

// Función para cerrar el modal
function closeImportModal(event) {
    if (!event || event.target.id === 'importModal') {
        const modal = document.getElementById('importModal');
        modal.classList.remove('show');
        document.body.style.overflow = '';
    }
}

// Cerrar modal con tecla ESC
document.addEventListener('keydown', function(event) {
    if (event.key === 'Escape') {
        closeImportModal();
    }
});

// Inicializar iconos de Lucide cuando se carga la página
document.addEventListener('DOMContentLoaded', () => {
    if (window.lucide?.createIcons) {
        lucide.createIcons();
    }
});
</script>
@endsection