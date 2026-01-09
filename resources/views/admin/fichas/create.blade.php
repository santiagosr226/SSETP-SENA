@extends('layouts.app')

@section('content')
 

<div x-data="fichaManager()">
    <!-- Spinner de carga fullscreen -->
    <div 
        x-show="isSubmitting"
        x-cloak
        x-transition:enter="transition ease-out duration-300"
        x-transition:enter-start="opacity-0"
        x-transition:enter-end="opacity-100"
        x-transition:leave="transition ease-in duration-200"
        x-transition:leave-start="opacity-100"
        x-transition:leave-end="opacity-0"
        class="fixed inset-0 z-[9999] flex items-center justify-center bg-black/85 backdrop-blur-sm"
    >
        <div class="bg-white rounded-lg shadow-2xl p-8 flex flex-col items-center gap-4 min-w-[300px]">
            <div class="relative">
                <div class="w-16 h-16 border-4 border-verde-sena border-t-transparent rounded-full animate-spin"></div>
            </div>
            <div class="text-center">
                <h3 class="text-lg font-semibold text-slate-800 mb-2">Creando ficha...</h3>
                <p class="text-sm text-slate-600">Por favor espera, esto puede tomar unos momentos</p>
            </div>
        </div>
    </div>

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

    <!-- Estado de importación (si hay datos importados) -->
    <template x-if="importedData.aprendices.length > 0">
        <div class="import-status import-success mb-4">
            <div class="flex items-center gap-2">
                <i data-lucide="check-circle" class="w-4 h-4"></i>
                <div>
                    <p class="font-medium text-xs" x-text="`${importedData.aprendices.length} aprendices importados correctamente`"></p>
                    <p class="text-2xs opacity-75">Los aprendices se agregarán a la ficha cuando crees el registro</p>
                </div>
            </div>
        </div>
    </template>

    <!-- Formulario principal -->
    <form action="{{ route('fichas.store') }}" method="POST" class="space-y-6" @submit.prevent="submitForm">
        @csrf
        
        <!-- Campo oculto para datos importados -->
        <input type="hidden" name="imported_aprendices" :value="JSON.stringify(importedData.aprendices)">
        <input type="hidden" name="imported_juicios" :value="JSON.stringify(importedData.juicios)">
        
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
                        x-model="formData.numero"
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
                        x-model="formData.programa_id"
                        class="select-field"
                        required
                    >
                        <option value="">Seleccionar programa</option>
                        @foreach($programas as $programa)
                            <option value="{{ $programa->id }}">
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
                        x-model="formData.instructor_id"
                        class="select-field"
                        required
                    >
                        <option value="">Seleccionar instructor</option>
                        @foreach($instructores as $instructor)
                            <option value="{{ $instructor->id }}">
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
                        x-model="formData.estado"
                        class="select-field"
                        required
                    >
                        <option value="">Seleccionar estado</option>
                        <option value="en ejecución">En Ejecución</option>
                        <option value="terminada por fecha">Terminada por Fecha</option>
                        <option value="cancelada">Cancelada</option>
                        <option value="en curso">En Curso</option>
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
                <div 
    @click="$refs.fechaInicial.showPicker()" 
    class="cursor-pointer"
>
    <label for="fecha_inicial" class="form-label">
        Fecha Inicial Formación
    </label>

    <input 
        type="date" 
        id="fecha_inicial" 
        name="fecha_inicial" 
        x-ref="fechaInicial"
        x-model="formData.fecha_inicial"
        class="input-field"
        required
    >

    @error('fecha_inicial')
        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
    @enderror
</div>


                <!-- Fecha Final Lectiva -->
                <div @click="$refs.fechaFinalLectiva.showPicker()" class="cursor-pointer">
                    <label for="fecha_final_lectiva" class="form-label">Fecha Final Lectiva</label>
                    <input 
                        type="date" 
                        id="fecha_final_lectiva" 
                        name="fecha_final_lectiva" 
                        x-ref="fechaFinalLectiva"
                        x-model="formData.fecha_final_lectiva"
                        class="input-field"
                    >
                    @error('fecha_final_lectiva')
                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Fecha Final Formación -->
                <div @click="$refs.fechaFinalFormacion.showPicker()" class="cursor-pointer">
                    <label for="fecha_final_formacion" class="form-label">Fecha Final Formación</label>
                    <input 
                        type="date" 
                        id="fecha_final_formacion" 
                        name="fecha_final_formacion" 
                        x-ref="fechaFinalFormacion"
                        x-model="formData.fecha_final_formacion"
                        class="input-field"
                    >
                    @error('fecha_final_formacion')
                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Fecha Límite Productiva -->
                <div @click="$refs.fechaLimiteProductiva.showPicker()" class="cursor-pointer">
                    <label for="fecha_limite_productiva" class="form-label">Fecha Límite Productiva</label>
                    <input 
                        type="date" 
                        id="fecha_limite_productiva" 
                        name="fecha_limite_productiva" 
                        x-ref="fechaLimiteProductiva"
                        x-model="formData.fecha_limite_productiva"
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
                        x-model="formData.modalidad"
                        class="select-field"
                        required
                    >
                        <option value="">Seleccionar modalidad</option>
                        <option value="presencial">Presencial</option>
                        <option value="virtual">Virtual</option>
                        <option value="mixta">Mixta</option>
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
                        x-model="formData.jornada"
                        class="select-field"
                        required
                    >
                        <option value="">Seleccionar jornada</option>
                        <option value="mañana">Mañana</option>
                        <option value="tarde">Tarde</option>
                        <option value="noche">Noche</option>
                        <option value="virtual">Virtual</option>
                        <option value="otra">Otra...</option>
                    </select>
                    @error('jornada')
                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Fecha Actualización -->
                <div @click="$refs.fechaActualizacion.showPicker()" class="cursor-pointer">
                    <label for="fecha_actualizacion" class="form-label">Fecha Actualización</label>
                    <input 
                        type="date" 
                        id="fecha_actualizacion" 
                        name="fecha_actualizacion" 
                        x-ref="fechaActualizacion"
                        x-model="formData.fecha_actualizacion"
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
                        x-model="formData.resultados_aprendizaje_totales"
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
        @click="openImportModal('aprendices')"
        class="inline-flex items-center gap-2 rounded-lg 
               bg-blue-600 px-4 py-2 text-sm font-semibold text-white
               shadow-sm transition
               hover:bg-blue-700
               focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2
               active:scale-95 cursor-pointer"
    >
        <i data-lucide="users" class="w-4 h-4"></i>
        Importar Aprendices
    </button>

    <button 
        type="button"
        @click="openImportModal('juicios')"
        class="inline-flex items-center gap-2 rounded-lg
               bg-purple-600 px-4 py-2 text-sm font-semibold text-white
               shadow-sm transition
               hover:bg-purple-700
               focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2
               active:scale-95 cursor-pointer"
    >
        <i data-lucide="clipboard-check" class="w-4 h-4"></i>
        Importar Juicios Evaluativos
    </button>
    
    <button 
        type="button"
        @click="clearJuicios()"
        x-show="importedData.juicios.length > 0"
        class="inline-flex items-center gap-2 rounded-lg
               bg-red-50 px-4 py-2 text-sm font-semibold text-red-600
               border border-red-200 shadow-sm transition
               hover:bg-red-100
               focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-2
               active:scale-95 cursor-pointer"
    >
        <i data-lucide="x-circle" class="w-4 h-4"></i>
        Limpiar Juicios
    </button>
</div>

        </div>

        <!-- Listado de aprendices -->
        <div class="form-card">
            <div class="flex justify-between items-center mb-4">
                <h2 class="text-sm font-semibold text-verde-sena">Aprendices Asociados</h2>
                <div class="flex items-center gap-3">
                    <span x-text="`${importedData.aprendices.length} aprendices`" class="text-xs text-slate-500"></span>
                    <button 
                        type="button"
                        @click="clearAprendices()"
                        x-show="importedData.aprendices.length > 0"
                        class="inline-flex items-center gap-1 px-3 py-1.5 text-xs font-medium text-red-600 bg-red-50 hover:bg-red-100 border border-red-200 rounded-md transition duration-200"
                        title="Eliminar todos los aprendices importados"
                    >
                        <i data-lucide="trash-2" class="w-3 h-3"></i>
                        Limpiar aprendices
                    </button>
                </div>
            </div>
            
            <div x-show="importedData.aprendices.length === 0">
                <div class="text-center py-8 border border-dashed border-slate-300 rounded-md">
                    <i data-lucide="users" class="w-8 h-8 text-slate-300 mx-auto mb-2"></i>
                    <p class="text-slate-500 text-xs">No hay aprendices asociados a esta ficha</p>
                    <p class="text-slate-400 text-2xs mt-1">Utiliza el botón "Importar Aprendices" para agregar aprendices</p>
                </div>
            </div>

            <div x-show="importedData.aprendices.length > 0" class="border border-slate-200 rounded-md overflow-hidden">
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
                                <th class="px-3 py-2 text-center font-medium">Resultado Aprendizaje</th>
                                <th class="px-3 py-2 text-center font-medium">Acciones</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-200 bg-white">
                            <template x-for="(aprendiz, index) in importedData.aprendices" :key="index">
                                <tr class="table-row text-center" :class="isEstadoExcluido(aprendiz.estado) ? 'bg-red-50 hover:bg-red-100' : 'hover:bg-slate-50'">
                                    <td class="px-3 py-2 text-slate-700" x-text="aprendiz.tipo_documento || 'CC'"></td>
                                    <td class="px-3 py-2 text-slate-700 font-medium" x-text="aprendiz.numero_documento"></td>
                                    <td class="px-3 py-2 text-slate-700" x-text="aprendiz.nombre"></td>
                                    <td class="px-3 py-2 text-slate-700" x-text="aprendiz.apellido"></td>
                                    <td class="px-3 py-2 text-slate-700" x-text="aprendiz.celular || 'N/A'"></td>
                                    <td class="px-3 py-2 text-slate-700" x-text="aprendiz.email || 'N/A'"></td>
                                    <td class="px-3 py-2">
                                        <span :class="{
                                            'badge-estado badge-activo': (aprendiz.estado || '').toLowerCase() === 'activo',
                                            'badge-estado badge-inactivo': (aprendiz.estado || '').toLowerCase() === 'inactivo',
                                            'badge-estado badge-pendiente': !aprendiz.estado || (aprendiz.estado || '').toLowerCase() === 'pendiente'
                                        }" x-text="aprendiz.estado || 'Pendiente'"></span>
                                    </td>
                                    <td class="px-3 py-2 text-slate-700">
                                        <span class="inline-flex items-center gap-1 px-2 py-1 rounded-md text-xs font-medium"
                                              :class="{
                                                  'bg-yellow-100 text-yellow-800': (aprendiz.por_evaluar || 0) > 0,
                                                  'bg-green-100 text-green-800': (aprendiz.por_evaluar || 0) === 0 && (aprendiz.total_resultados || 0) > 0,
                                                  'bg-slate-100 text-slate-600': !aprendiz.total_resultados || aprendiz.total_resultados === 0
                                              }">
                                            <span x-text="(aprendiz.por_evaluar || 0) + ' por evaluar'"></span>
                                            <span x-show="aprendiz.total_resultados" class="text-2xs opacity-75" 
                                                  x-text="'/' + (aprendiz.total_resultados || 0)"></span>
                                        </span>
                                    </td>
                                    <td class="px-3 py-2">
                                        <button 
                                            type="button"
                                            @click="removeAprendiz(index)"
                                            class="text-red-500 hover:text-red-700 cursor-pointer"
                                            title="Eliminar aprendiz"
                                        >
                                            <i data-lucide="trash-2" class="w-3 h-3"></i>
                                        </button>
                                    </td>
                                </tr>
                            </template>
                        </tbody>
                    </table>
                </div>
            </div>
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
                :disabled="isSubmitting"
                class="px-4 py-2 text-xs font-medium text-white bg-verde-sena hover:bg-green-700 rounded-md transition duration-200 flex items-center gap-1 disabled:opacity-50 disabled:cursor-not-allowed"
            >
                <svg
                    x-show="isSubmitting"
                    class="w-3 h-3 animate-spin"
                    xmlns="http://www.w3.org/2000/svg"
                    fill="none"
                    viewBox="0 0 24 24"
                >
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
                </svg>
                <i data-lucide="save" x-show="!isSubmitting" class="w-3 h-3"></i>
                <span x-text="isSubmitting ? 'Creando...' : 'Crear Ficha'"></span>
            </button>
        </div>
    </form>

    <!-- Modal de Importación -->
    <template x-if="importModalOpen">
        <div
            x-show="importModalOpen"
            @click.self="closeImportModal()"
            @keydown.escape.window="closeImportModal()"
            class="fixed inset-0 z-50 flex items-center justify-center bg-black/70 backdrop-blur-sm p-4"
            x-transition:enter="transition ease-out duration-300"
            x-transition:enter-start="opacity-0"
            x-transition:enter-end="opacity-100"
            x-transition:leave="transition ease-in duration-200"
            x-transition:leave-start="opacity-100"
            x-transition:leave-end="opacity-0"
        >
            <div
                @click.stop
                class="w-full max-w-md bg-white rounded-xl shadow-2xl border border-slate-200 modal-content"
                x-transition:enter="transition ease-out duration-300"
                x-transition:enter-start="opacity-0 scale-95 translate-y-2"
                x-transition:enter-end="opacity-100 scale-100 translate-y-0"
                x-transition:leave="transition ease-in duration-200"
                x-transition:leave-start="opacity-100 scale-100"
                x-transition:leave-end="opacity-0 scale-95"
            >
                <!-- Header -->
                <div class="flex items-center justify-between px-4 py-3 rounded-t-xl"
                     :class="currentImportType === 'aprendices' ? 'bg-blue-500 text-white' : 'bg-purple-600 text-white'">
                    <h2 x-text="currentImportType === 'aprendices' ? 'Importar Aprendices' : 'Importar Juicios Evaluativos'" 
                        class="text-xs font-semibold tracking-wide"></h2>
                    <button
                        @click="closeImportModal()"
                        class="p-1 rounded-full hover:bg-white/20 transition"
                    >
                        <i data-lucide="x" class="w-4 h-4"></i>
                    </button>
                </div>

                <!-- Contenido del modal -->
                <div class="px-4 py-3 space-y-4">
                    <p class="text-xs text-slate-600" x-text="currentImportType === 'aprendices' ? 
                        'Sube un archivo Excel o CSV con la información de los aprendices. El archivo debe contener las siguientes columnas:' :
                        'Sube un archivo Excel o CSV con los juicios evaluativos de los aprendices.'"></p>
                    
                    <div class="bg-slate-50 p-3 rounded-md">
                        <code class="text-2xs text-slate-700 block font-mono" x-show="currentImportType === 'aprendices'">
                            Tipo de Documento, Número de Documento, Nombre, Apellidos, Celular, Correo Electrónico, Estado
                        </code>
                        <code class="text-2xs text-slate-700 block font-mono" x-show="currentImportType === 'juicios'">
                            Tipo de Documento, Número de Documento, Nombre, Apellidos, Estado, Competencia, Resultado de Aprendizaje, Juicio de Evaluación
                        </code>
                    </div>
                    
                    <form @submit.prevent="handleImport" id="importForm" class="space-y-3">
                        <div>
                            <label for="importFile" class="block text-xs font-medium text-slate-700 mb-1">
                                Seleccionar archivo
                            </label>
                            <input 
                                type="file" 
                                id="importFile" 
                                name="archivo"
                                accept=".xlsx,.xls,.csv"
                                @change="handleFileSelect"
                                :disabled="isImporting"
                                class="block w-full text-xs text-slate-500
                                    file:mr-4 file:py-1.5 file:px-3
                                    file:rounded-md file:border-0
                                    file:text-xs file:font-medium
                                    file:cursor-pointer transition"
                                :class="currentImportType === 'aprendices' ? 
                                    'file:bg-blue-500 file:text-white hover:file:bg-blue-600' :
                                    'file:bg-purple-600 file:text-white hover:file:bg-purple-700'"
                                required
                            >
                        </div>
                        
                        <!-- Vista previa del archivo -->
                        <div x-show="selectedFile" class="bg-slate-50 p-3 rounded-md">
                            <div class="flex items-center gap-2">
                                <i data-lucide="file-text" class="w-4 h-4 text-slate-500"></i>
                                <div class="flex-1">
                                    <p class="text-xs font-medium text-slate-700 truncate" x-text="selectedFile ? selectedFile.name : ''"></p>
                                    <p class="text-2xs text-slate-500" x-text="selectedFile ? formatFileSize(selectedFile.size) : ''"></p>
                                </div>
                                <button 
                                    type="button" 
                                    @click="selectedFile = null"
                                    class="text-slate-400 hover:text-slate-600 cursor-pointer"
                                >
                                    <i data-lucide="x" class="w-3 h-3"></i>
                                </button>
                            </div>
                        </div>
                        
                        <div class="flex justify-end gap-2 pt-2">
                            <button 
                                type="button" 
                                @click="closeImportModal()"
                                :disabled="isImporting"
                                class="px-3 py-1.5 text-xs font-medium text-slate-700 bg-slate-100 hover:bg-slate-200 rounded-md transition duration-200 disabled:opacity-50"
                            >
                                Cancelar
                            </button>
                            
                            <button 
                                type="submit"
                                :disabled="!selectedFile || isImporting"
                                class="px-3 py-1.5 text-xs font-medium text-white rounded-md transition duration-200 flex items-center gap-1 disabled:opacity-50"
                                :class="currentImportType === 'aprendices' ? 
                                    'bg-blue-500 hover:bg-blue-600' : 
                                    'bg-purple-600 hover:bg-purple-700'"
                            >
                                <svg
                                    x-show="isImporting"
                                    class="w-3 h-3 animate-spin"
                                    xmlns="http://www.w3.org/2000/svg"
                                    fill="none"
                                    viewBox="0 0 24 24"
                                >
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
                                </svg>
                                <i data-lucide="upload" x-show="!isImporting" class="w-3 h-3"></i>
                                <span x-text="isImporting ? 'Importando...' : 'Importar'"></span>
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </template>
</div>

<script>
function fichaManager() {
    return {
        formData: {
            numero: '',
            programa_id: '',
            instructor_id: '',
            estado: '',
            fecha_inicial: '',
            fecha_final_lectiva: '',
            fecha_final_formacion: '',
            fecha_limite_productiva: '',
            modalidad: '',
            jornada: '',
            fecha_actualizacion: '',
            resultados_aprendizaje_totales: 0
        },
        importedData: {
            aprendices: [],
            juicios: []
        },
        importModalOpen: false,
        currentImportType: 'aprendices',
        selectedFile: null,
        isImporting: false,
        isSubmitting: false,

        isEstadoExcluido(estado) {
            const v = (estado || '').toString().trim().toUpperCase();
            return ['RETIRO VOLUNTARIO', 'CANCELADO', 'TRASLADADO', 'APLAZADO'].includes(v);
        },
        
        openImportModal(type) {
            this.currentImportType = type;
            this.selectedFile = null;
            this.importModalOpen = true;
            
            // Bloquear scroll
            document.body.classList.add('modal-open');
            
            this.$nextTick(() => {
                if (window.lucide?.createIcons) {
                    lucide.createIcons();
                }
            });
        },
        
        closeImportModal() {
            if (this.isImporting) {
                Swal.fire({
                    icon: 'warning',
                    title: 'Espera',
                    text: 'Hay una importación en proceso. Por favor espera.',
                    confirmButtonColor: '#39A900'
                });
                return;
            }
            
            this.importModalOpen = false;
            this.selectedFile = null;
            
            setTimeout(() => {
                document.body.classList.remove('modal-open');
            }, 250);
        },
        
        handleFileSelect(event) {
            const file = event.target.files[0];
            if (file) {
                // Validar tipo de archivo
                const allowedExtensions = ['.xlsx', '.xls', '.csv'];
                const fileExtension = '.' + file.name.split('.').pop().toLowerCase();
                
                if (!allowedExtensions.includes(fileExtension)) {
                    Swal.fire({
                        icon: 'error',
                        title: 'Archivo no válido',
                        text: 'Solo se permiten archivos Excel (.xlsx, .xls) o CSV (.csv)',
                        confirmButtonColor: '#39A900'
                    });
                    event.target.value = '';
                    return;
                }
                
                // Validar tamaño (máximo 5MB)
                const maxSize = 5 * 1024 * 1024; // 5MB en bytes
                if (file.size > maxSize) {
                    Swal.fire({
                        icon: 'error',
                        title: 'Archivo demasiado grande',
                        text: 'El archivo no debe exceder los 5MB',
                        confirmButtonColor: '#39A900'
                    });
                    event.target.value = '';
                    return;
                }
                
                this.selectedFile = file;
            }
        },
        
        formatFileSize(bytes) {
            if (bytes === 0) return '0 Bytes';
            const k = 1024;
            const sizes = ['Bytes', 'KB', 'MB', 'GB'];
            const i = Math.floor(Math.log(bytes) / Math.log(k));
            return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i];
        },
        
        async handleImport() {
            if (!this.selectedFile || this.isImporting) return;
            
            this.isImporting = true;
            
            try {
                if (this.currentImportType === 'aprendices') {
                    // Crear FormData para enviar el archivo
                    const formData = new FormData();
                    formData.append('archivo', this.selectedFile);
                    
                    // Realizar petición al endpoint de importación
                    const response = await fetch('{{ route("fichas.importar-aprendices") }}', {
                        method: 'POST',
                        body: formData,
                        headers: {
                            'X-Requested-With': 'XMLHttpRequest',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '{{ csrf_token() }}'
                        }
                    });
                    
                    const data = await response.json();
                    
                    if (response.ok && data.success) {
                        // Agregar los aprendices importados a la lista
                        // Inicializar campos de conteo para cada aprendiz
                        const listaAprendices = Array.isArray(data.aprendices)
                            ? data.aprendices
                            : (data.aprendices && typeof data.aprendices === 'object'
                                ? Object.values(data.aprendices)
                                : []);

                        if (!Array.isArray(listaAprendices) || listaAprendices.length === 0) {
                            throw new Error(data.message || 'No se encontraron aprendices válidos en el archivo');
                        }

                        const aprendicesConConteo = listaAprendices.map(aprendiz => ({
                            ...aprendiz,
                            total_resultados: 0,
                            por_evaluar: 0,
                            aprobados: 0,
                            no_aprobados: 0
                        }));
                        this.importedData.aprendices.push(...aprendicesConConteo);
                        
                        Swal.fire({
                            icon: 'success',
                            title: '¡Importación exitosa!',
                            text: data.message || `${data.total} aprendices importados correctamente`,
                            confirmButtonColor: '#39A900'
                        });
                        
                        // Resetear el estado antes de cerrar
                        this.isImporting = false;
                        this.selectedFile = null;
                        // Limpiar el input file
                        const fileInput = document.getElementById('importFile');
                        if (fileInput) {
                            fileInput.value = '';
                        }
                        
                        this.closeImportModal();
                    } else {
                        throw new Error(data.message || 'Error al importar el archivo');
                    }
                } else {
                    // Verificar que haya aprendices importados
                    if (this.importedData.aprendices.length === 0) {
                        Swal.fire({
                            icon: 'warning',
                            title: 'No hay aprendices importados',
                            text: 'Por favor, importa primero los aprendices antes de importar los juicios evaluativos.',
                            confirmButtonColor: '#39A900'
                        });
                        this.isImporting = false;
                        return;
                    }
                    
                    // Importar juicios evaluativos
                    const formData = new FormData();
                    formData.append('archivo', this.selectedFile);
                    
                    // Agregar números de documento de aprendices importados
                    const numerosDocumento = this.importedData.aprendices.map(a => a.numero_documento);
                    numerosDocumento.forEach(numDoc => {
                        formData.append('numeros_documento[]', numDoc);
                    });
                    
                    // Realizar petición al endpoint de importación
                    const response = await fetch('{{ route("fichas.importar-juicios-evaluativos") }}', {
                        method: 'POST',
                        body: formData,
                        headers: {
                            'X-Requested-With': 'XMLHttpRequest',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '{{ csrf_token() }}'
                        }
                    });
                    
                    const data = await response.json();
                    
                    if (response.ok && data.success) {
                        // Relacionar el conteo con los aprendices importados
                        const conteoPorAprendiz = data.conteo_por_aprendiz || [];
                        
                        // Crear un mapa de conteo por número de documento
                        const conteoMap = {};
                        conteoPorAprendiz.forEach(item => {
                            conteoMap[item.numero_documento] = item;
                        });
                        
                        // Actualizar los aprendices con el conteo
                        this.importedData.aprendices.forEach(aprendiz => {
                            const conteo = conteoMap[aprendiz.numero_documento];
                            if (conteo) {
                                aprendiz.total_resultados = conteo.total_resultados || 0;
                                aprendiz.por_evaluar = conteo.por_evaluar || 0;
                                aprendiz.aprobados = conteo.aprobados || 0;
                                aprendiz.no_aprobados = conteo.no_aprobados || 0;
                            } else {
                                // Si no se encuentra, inicializar en 0
                                aprendiz.total_resultados = 0;
                                aprendiz.por_evaluar = 0;
                                aprendiz.aprobados = 0;
                                aprendiz.no_aprobados = 0;
                            }
                        });
                        
                        Swal.fire({
                            icon: 'success',
                            title: '¡Importación exitosa!',
                            text: data.message || `Juicios evaluativos procesados para ${data.total_aprendices} aprendices`,
                            confirmButtonColor: '#39A900'
                        });
                        
                        // Resetear el estado antes de cerrar
                        this.isImporting = false;
                        this.selectedFile = null;
                        // Limpiar el input file
                        const fileInput = document.getElementById('importFile');
                        if (fileInput) {
                            fileInput.value = '';
                        }
                        
                        this.closeImportModal();
                    } else {
                        throw new Error(data.message || 'Error al importar el archivo');
                    }
                }
            } catch (error) {
                Swal.fire({
                    icon: 'error',
                    title: 'Error en la importación',
                    text: error.message || 'Ocurrió un error al importar el archivo. Por favor, verifica el formato del archivo.',
                    confirmButtonColor: '#39A900'
                });
            } finally {
                this.isImporting = false;
                
                // Re-inicializar iconos
                this.$nextTick(() => {
                    if (window.lucide?.createIcons) {
                        lucide.createIcons();
                    }
                });
            }
        },
        
        removeAprendiz(index) {
            Swal.fire({
                icon: 'question',
                title: '¿Eliminar aprendiz?',
                text: '¿Estás seguro de eliminar este aprendiz de la lista?',
                showCancelButton: true,
                confirmButtonColor: '#df0026',
                cancelButtonColor: '#6c757d',
                confirmButtonText: 'Sí, eliminar',
                cancelButtonText: 'Cancelar'
            }).then((result) => {
                if (result.isConfirmed) {
                    this.importedData.aprendices.splice(index, 1);
                }
            });
        },
        
        clearAprendices() {
            Swal.fire({
                icon: 'question',
                title: '¿Limpiar aprendices?',
                text: `¿Estás seguro de eliminar todos los ${this.importedData.aprendices.length} aprendices importados? Esta acción también limpiará los juicios evaluativos asociados.`,
                showCancelButton: true,
                confirmButtonColor: '#df0026',
                cancelButtonColor: '#6c757d',
                confirmButtonText: 'Sí, limpiar',
                cancelButtonText: 'Cancelar'
            }).then((result) => {
                if (result.isConfirmed) {
                    this.importedData.aprendices = [];
                    this.importedData.juicios = [];
                    
                    // Actualizar iconos después de limpiar
                    this.$nextTick(() => {
                        if (window.lucide?.createIcons) {
                            lucide.createIcons();
                        }
                    });
                    
                    Swal.fire({
                        icon: 'success',
                        title: '¡Limpiado!',
                        text: 'Todos los aprendices y juicios han sido eliminados.',
                        timer: 2000,
                        timerProgressBar: true,
                        showConfirmButton: false,
                        toast: true,
                        position: 'top-end'
                    });
                }
            });
        },
        
        clearJuicios() {
            Swal.fire({
                icon: 'question',
                title: '¿Limpiar juicios evaluativos?',
                text: '¿Estás seguro de eliminar todos los juicios evaluativos importados? Los conteos de resultados de aprendizaje se perderán.',
                showCancelButton: true,
                confirmButtonColor: '#df0026',
                cancelButtonColor: '#6c757d',
                confirmButtonText: 'Sí, limpiar',
                cancelButtonText: 'Cancelar'
            }).then((result) => {
                if (result.isConfirmed) {
                    this.importedData.juicios = [];
                    // También limpiar los conteos de los aprendices
                    this.importedData.aprendices.forEach(aprendiz => {
                        aprendiz.total_resultados = 0;
                        aprendiz.por_evaluar = 0;
                        aprendiz.aprobados = 0;
                        aprendiz.no_aprobados = 0;
                    });
                    
                    // Actualizar iconos después de limpiar
                    this.$nextTick(() => {
                        if (window.lucide?.createIcons) {
                            lucide.createIcons();
                        }
                    });
                    
                    Swal.fire({
                        icon: 'success',
                        title: '¡Limpiado!',
                        text: 'Todos los juicios evaluativos han sido eliminados.',
                        timer: 2000,
                        timerProgressBar: true,
                        showConfirmButton: false,
                        toast: true,
                        position: 'top-end'
                    });
                }
            });
        },
        
        async submitForm() {
            if (this.isSubmitting) return;
            
            // Validar campos requeridos
            const requiredFields = [
                'numero', 'programa_id', 'instructor_id', 'estado', 
                'fecha_inicial', 'modalidad', 'jornada'
            ];
            
            const missingFields = requiredFields.filter(field => !this.formData[field]);
            
            if (missingFields.length > 0) {
                Swal.fire({
                    icon: 'warning',
                    title: 'Campos requeridos',
                    text: 'Por favor completa todos los campos obligatorios',
                    confirmButtonColor: '#39A900'
                });
                return;
            }
            
            const result = await Swal.fire({
                icon: 'question',
                title: '¿Crear ficha?',
                html: `¿Deseas crear la ficha <strong>${this.formData.numero}</strong>?<br><br>
                      ${this.importedData.aprendices.length > 0 ? 
                       `Se importarán ${this.importedData.aprendices.length} aprendices.` : 
                       'No se importarán aprendices.'}`,
                showCancelButton: true,
                confirmButtonColor: '#39A900',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Sí, crear',
                cancelButtonText: 'Cancelar'
            });

            if (!result.isConfirmed) return;

            this.isSubmitting = true;
            
            console.log('Iniciando envío de formulario...');
            console.log('Datos del formulario:', this.formData);
            
            // Crear FormData directamente desde los datos de Alpine.js
            const formData = new FormData();
            
            // Agregar token CSRF
            const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '{{ csrf_token() }}';
            formData.append('_token', csrfToken);
            
            // Agregar todos los campos del formulario
            formData.append('numero', this.formData.numero || '');
            formData.append('programa_id', this.formData.programa_id || '');
            formData.append('instructor_id', this.formData.instructor_id || '');
            formData.append('estado', this.formData.estado || '');
            formData.append('fecha_inicial', this.formData.fecha_inicial || '');
            formData.append('fecha_final_lectiva', this.formData.fecha_final_lectiva || '');
            formData.append('fecha_final_formacion', this.formData.fecha_final_formacion || '');
            formData.append('fecha_limite_productiva', this.formData.fecha_limite_productiva || '');
            formData.append('fecha_actualizacion', this.formData.fecha_actualizacion || '');
            formData.append('modalidad', this.formData.modalidad || '');
            formData.append('jornada', this.formData.jornada || '');
            formData.append('resultados_aprendizaje_totales', this.formData.resultados_aprendizaje_totales || 0);
            
            // Agregar aprendices importados
            formData.append('imported_aprendices', JSON.stringify(this.importedData.aprendices || []));
            
            // Debug: mostrar qué se está enviando
            console.log('Datos a enviar:');
            for (let [key, value] of formData.entries()) {
                if (key === 'imported_aprendices') {
                    console.log(`${key}: ${value.substring(0, 100)}...`);
                } else {
                    console.log(`${key}: ${value}`);
                }
            }
            
            // Usar la ruta correcta explícitamente
            const url = '{{ route("fichas.store") }}';
            console.log('URL:', url);
            
            // Enviar datos a través de fetch con timeout
            try {
                console.log('Enviando request...');
                const controller = new AbortController();
                const timeoutId = setTimeout(() => {
                    console.error('Timeout alcanzado');
                    controller.abort();
                }, 60000); // 60 segundos timeout
                
                const response = await fetch(url, {
                    method: 'POST',
                    body: formData,
                    signal: controller.signal,
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                        'X-CSRF-TOKEN': csrfToken
                    }
                });
                
                clearTimeout(timeoutId);
                
                console.log('Response recibida:', response.status, response.statusText);

                const data = await response.json();
                console.log('Datos recibidos:', data);
                
                if (response.ok && data.success) {
                    this.isSubmitting = false;
                    
                    // Construir el HTML del mensaje
                    let htmlContent = `<p class="text-base mb-3">${data.message || 'Ficha creada correctamente'}</p>`;
                    
                    // Mostrar aprendices creados exitosamente
                    if (data.aprendices_creados > 0) {
                        htmlContent += `<p class="text-sm text-green-700 mb-2">
                            <i class="inline-block w-4 h-4 mr-1">✓</i>
                            <strong>${data.aprendices_creados}</strong> aprendices creados exitosamente.
                        </p>`;
                    }
                    
                    // Mostrar aprendices con errores
                    if (data.aprendices_error && data.aprendices_error.length > 0) {
                        htmlContent += `<div class="mt-4 p-3 bg-red-50 border border-red-200 rounded-md">
                            <p class="text-sm font-semibold text-red-800 mb-2">
                                <i class="inline-block w-4 h-4 mr-1">⚠</i>
                                ${data.aprendices_error.length} aprendices no pudieron ser guardados:
                            </p>
                            <div class="max-h-48 overflow-y-auto space-y-1">
                                <ul class="text-xs text-red-700 space-y-1">`;
                        
                        data.aprendices_error.forEach(aprendiz => {
                            htmlContent += `<li class="flex items-start gap-2">
                                <span class="font-medium">${aprendiz.numero_documento}</span>
                                <span class="text-slate-600">-</span>
                                <span class="flex-1">${aprendiz.nombre || 'Sin nombre'}</span>
                                <span class="text-red-600 font-medium">(${aprendiz.error})</span>
                            </li>`;
                        });
                        
                        htmlContent += `</ul>
                            </div>
                        </div>`;
                    }
                    
                    await Swal.fire({
                        icon: data.aprendices_error && data.aprendices_error.length > 0 ? 'warning' : 'success',
                        title: data.aprendices_error && data.aprendices_error.length > 0 ? 'Ficha creada con advertencias' : '¡Éxito!',
                        html: htmlContent,
                        confirmButtonText: 'OK',
                        confirmButtonColor: '#39A900',
                        allowOutsideClick: false,
                        allowEscapeKey: false,
                        width: '600px'
                    });
                    
                    window.location.href = '{{ route("fichas.index") }}';
                } else {
                    let errorMessage = 'Error al crear la ficha';
                    
                    if (data.errors) {
                        errorMessage = Object.values(data.errors).flat().join('\n');
                    } else if (data.message) {
                        errorMessage = data.message;
                    }
                    
                    throw new Error(errorMessage);
                }
            } catch (error) {
                if (error.name === 'AbortError') {
                    Swal.fire({
                        icon: 'error',
                        title: 'Timeout',
                        text: 'La operación está tomando demasiado tiempo. Por favor, verifica los logs del servidor.',
                        confirmButtonColor: '#39A900'
                    });
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: error.message || 'Ocurrió un error al crear la ficha',
                        confirmButtonColor: '#39A900'
                    });
                }
                this.isSubmitting = false;
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