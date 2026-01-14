@extends('layouts.app')

@section('content')

@php
    $oldImportedAprendices = old('imported_aprendices');
    $oldImportedAprendicesArray = [];
    if (!empty($oldImportedAprendices)) {
        $tmp = json_decode($oldImportedAprendices, true);
        if (is_array($tmp)) {
            $oldImportedAprendicesArray = $tmp;
        }
    }

    $oldImportedJuicios = old('imported_juicios');
    $oldImportedJuiciosArray = [];
    if (!empty($oldImportedJuicios)) {
        $tmp2 = json_decode($oldImportedJuicios, true);
        if (is_array($tmp2)) {
            $oldImportedJuiciosArray = $tmp2;
        }
    }
@endphp

<div x-data="fichaEditManager()">
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
                <h3 class="text-lg font-semibold text-slate-800 mb-2">Actualizando ficha...</h3>
                <p class="text-sm text-slate-600">Por favor espera, esto puede tomar unos momentos</p>
            </div>
        </div>
    </div>

    <div class="mb-6">
        <h1 class="text-xs font-semibold text-verde-sena mb-2 tracking-wide">Editar Ficha</h1>
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

    @if(session('error'))
        <div x-init="
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: '{{ session('error') }}',
                confirmButtonColor: '#39A900'
            })
        "></div>
    @endif

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

    <form action="{{ route('fichas.update', $ficha->id) }}" method="POST" class="space-y-6" @submit="isSubmitting = true">
        @csrf
        @method('PUT')

        <input type="hidden" name="imported_aprendices" :value="JSON.stringify(importedData.aprendices)">
        <input type="hidden" name="imported_juicios" :value="JSON.stringify(importedData.juicios)">

        <div class="form-card">
            <h2 class="text-sm font-semibold text-verde-sena mb-4">Información Básica</h2>
            <div class="form-grid">
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

        <div class="form-card">
            <h2 class="text-sm font-semibold text-verde-sena mb-4">Fechas Importantes</h2>
            <div class="form-grid">
                <div>
                    <label for="fecha_inicial" class="form-label">Fecha Inicial Formación</label>
                    <input 
                        type="date" 
                        id="fecha_inicial" 
                        name="fecha_inicial" 
                        x-model="formData.fecha_inicial"
                        class="input-field"
                        required
                    >
                    @error('fecha_inicial')
                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="fecha_final_lectiva" class="form-label">Fecha Final Lectiva</label>
                    <input 
                        type="date" 
                        id="fecha_final_lectiva" 
                        name="fecha_final_lectiva" 
                        x-model="formData.fecha_final_lectiva"
                        class="input-field"
                    >
                    @error('fecha_final_lectiva')
                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="fecha_final_formacion" class="form-label">Fecha Final Formación</label>
                    <input 
                        type="date" 
                        id="fecha_final_formacion" 
                        name="fecha_final_formacion" 
                        x-model="formData.fecha_final_formacion"
                        class="input-field"
                    >
                    @error('fecha_final_formacion')
                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="fecha_limite_productiva" class="form-label">Fecha Límite Productiva</label>
                    <input 
                        type="date" 
                        id="fecha_limite_productiva" 
                        name="fecha_limite_productiva" 
                        x-model="formData.fecha_limite_productiva"
                        class="input-field"
                    >
                    @error('fecha_limite_productiva')
                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>
            </div>
        </div>

        <div class="form-card">
            <h2 class="text-sm font-semibold text-verde-sena mb-4">Configuración Adicional</h2>
            <div class="form-grid">
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

                <div>
                    <label for="fecha_actualizacion" class="form-label">Fecha Actualización</label>
                    <input 
                        type="date" 
                        id="fecha_actualizacion" 
                        name="fecha_actualizacion" 
                        x-model="formData.fecha_actualizacion"
                        class="input-field bg-slate-50"
                        readonly
                    >
                    @error('fecha_actualizacion')
                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>

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
                           focus:outline-none focus:ring-2 focus:ring-purple-500 focus:ring-offset-2
                           active:scale-95 cursor-pointer"
                >
                    <i data-lucide="clipboard-check" class="w-4 h-4"></i>
                    Importar Juicios Evaluativos
                </button>
            </div>
        </div>

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
                    <div class="flex items-center justify-between px-4 py-3 rounded-t-xl" :class="currentImportType === 'juicios' ? 'bg-purple-600 text-white' : 'bg-blue-500 text-white'">
                        <h2 class="text-xs font-semibold tracking-wide" x-text="currentImportType === 'juicios' ? 'Importar Juicios Evaluativos' : 'Importar Aprendices'"></h2>
                        <button
                            @click="closeImportModal()"
                            class="p-1 rounded-full hover:bg-white/20 transition"
                        >
                            <i data-lucide="x" class="w-4 h-4"></i>
                        </button>
                    </div>

                    <div class="px-4 py-3 space-y-4">
                        <p class="text-xs text-slate-600" x-text="currentImportType === 'juicios' ? 'Sube un archivo Excel o CSV con los juicios evaluativos.' : 'Sube un archivo Excel o CSV con la información de los aprendices.'"></p>

                        <div class="bg-slate-50 p-3 rounded-md">
                            <code class="text-2xs text-slate-700 block font-mono" x-text="currentImportType === 'juicios' ? 'Tipo de Documento, Número de Documento, Nombre Completo, Estado, Juicio de Evaluación' : 'Tipo de Documento, Número de Documento, Nombre, Apellidos, Celular, Correo Electrónico, Estado'"></code>
                        </div>

                        <form @submit.prevent="handleImport" class="space-y-3">
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
                                        file:cursor-pointer transition
                                        file:bg-blue-500 file:text-white hover:file:bg-blue-600"
                                    required
                                >
                            </div>

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
                                    class="px-3 py-1.5 text-xs font-medium text-white rounded-md transition duration-200 flex items-center gap-1 disabled:opacity-50 bg-blue-500 hover:bg-blue-600"
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

        <div class="form-card">
            <div class="flex justify-between items-center mb-4">
                <h2 class="text-sm font-semibold text-verde-sena">Aprendices Asociados</h2>
                <div class="flex items-center gap-3">
                    <span x-text="`${displayAprendices.length} aprendices`" class="text-xs text-slate-500"></span>
                    <button 
                        type="button"
                        @click="clearPreview()"
                        :disabled="importedData.aprendices.length === 0"
                        class="inline-flex items-center gap-1 px-3 py-1.5 text-xs font-medium text-red-600 bg-red-50 border border-red-200 rounded-md transition duration-200"
                        :class="importedData.aprendices.length === 0 ? 'opacity-50 cursor-not-allowed' : 'hover:bg-red-100'"
                        title="Eliminar todos los aprendices"
                    >
                        <i data-lucide="trash-2" class="w-3 h-3"></i>
                        Limpiar aprendices
                    </button>
                </div>
            </div>

            <div x-show="importedData.aprendices.length > 0" x-cloak class="mb-3">
                <div class="import-status import-success">
                    <div class="flex items-center gap-2">
                        <i data-lucide="check-circle" class="w-4 h-4"></i>
                        <div>
                            <p class="font-medium text-xs">Hay cambios pendientes por aplicar</p>
                            <p class="text-2xs opacity-75">Se guardarán cuando presiones <strong>Guardar cambios</strong></p>
                        </div>
                    </div>
                </div>
            </div>

            <div x-show="displayAprendices.length === 0">
                <div class="text-center py-8 border border-dashed border-slate-300 rounded-md">
                    <i data-lucide="users" class="w-8 h-8 text-slate-300 mx-auto mb-2"></i>
                    <p class="text-slate-500 text-xs">No hay aprendices asociados a esta ficha</p>
                    <p class="text-slate-400 text-2xs mt-1">Utiliza el módulo de aprendices para agregarlos a la ficha</p>
                </div>
            </div>

            <div x-show="displayAprendices.length > 0" class="border border-slate-200 rounded-md overflow-hidden">
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
                            <template x-for="(aprendiz, index) in displayAprendices" :key="`${aprendiz.numero_documento || index}-${index}`">
                                <tr class="table-row text-center"
                                    :class="getRowClass(aprendiz)">
                                    <td class="px-3 py-2 text-slate-700" x-text="aprendiz.tipo_documento || 'CC'"></td>
                                    <td class="px-3 py-2 text-slate-700 font-medium" x-text="aprendiz.numero_documento"></td>
                                    <td class="px-3 py-2 text-slate-700" x-text="aprendiz.nombre"></td>
                                    <td class="px-3 py-2 text-slate-700" x-text="aprendiz.apellido"></td>
                                    <td class="px-3 py-2 text-slate-700" x-text="aprendiz.celular || 'N/A'"></td>
                                    <td class="px-3 py-2 text-slate-700" x-text="aprendiz.email || 'N/A'"></td>
                                    <td class="px-3 py-2">
                                        <span class="badge-estado" x-text="aprendiz.estado || 'EN FORMACION'"></span>
                                    </td>
                                    <td class="px-3 py-2 text-slate-700">
                                        <template x-if="true">
                                            <span class="inline-flex items-center gap-1 px-2 py-1 rounded-md text-xs font-medium"
                                                :class="getResultadoClass(aprendiz.resultados_aprendizaje || '0/0')">
                                                <span x-text="`${parseResultados(aprendiz.resultados_aprendizaje || '0/0').porEvaluar} por evaluar`"></span>
                                                <template x-if="parseResultados(aprendiz.resultados_aprendizaje || '0/0').total > 0">
                                                    <span class="text-2xs opacity-75" x-text="`/${parseResultados(aprendiz.resultados_aprendizaje || '0/0').total}`"></span>
                                                </template>
                                            </span>
                                        </template>
                                    </td>
                                    <td class="px-3 py-2">
                                        <button 
                                            type="button"
                                            @click="removeAprendiz(aprendiz)"
                                            class="text-red-500 hover:text-red-700"
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
                <span x-text="isSubmitting ? 'Guardando...' : 'Guardar cambios'"></span>
            </button>
        </div>
    </form>
</div>

<script>
function fichaEditManager() {
    return {
        formData: {
            numero: @js(old('numero', $ficha->numero)),
            programa_id: @js(old('programa_id', $ficha->programa_id)),
            instructor_id: @js(old('instructor_id', $ficha->funcionario_id)),
            estado: @js(old('estado', $ficha->estado)),
            fecha_inicial: @js(old('fecha_inicial', $ficha->fecha_inicial ? \Illuminate\Support\Carbon::parse($ficha->fecha_inicial)->format('Y-m-d') : '')),
            fecha_final_lectiva: @js(old('fecha_final_lectiva', $ficha->fecha_final_lectiva ? \Illuminate\Support\Carbon::parse($ficha->fecha_final_lectiva)->format('Y-m-d') : '')),
            fecha_final_formacion: @js(old('fecha_final_formacion', $ficha->fecha_final_formacion ? \Illuminate\Support\Carbon::parse($ficha->fecha_final_formacion)->format('Y-m-d') : '')),
            fecha_limite_productiva: @js(old('fecha_limite_productiva', $ficha->fecha_limite_productiva ? \Illuminate\Support\Carbon::parse($ficha->fecha_limite_productiva)->format('Y-m-d') : '')),
            modalidad: @js(old('modalidad', $ficha->modalidad)),
            jornada: @js(old('jornada', $ficha->jornada)),
            fecha_actualizacion: @js(old('fecha_actualizacion', $ficha->fecha_actualizacion ? \Illuminate\Support\Carbon::parse($ficha->fecha_actualizacion)->format('Y-m-d') : '')),
            resultados_aprendizaje_totales: @js(old('resultados_aprendizaje_totales', $ficha->resultados_aprendizaje_totales))
        },

        async removeAprendiz(aprendiz) {
            const documento = (aprendiz.numero_documento || '').toString();
            if (!documento) return;

            const isPreviewOnly = !aprendiz.id && (aprendiz._previewNew || aprendiz._previewUpdated);

            const result = await Swal.fire({
                icon: 'question',
                title: '¿Eliminar aprendiz?',
                text: isPreviewOnly
                    ? '¿Deseas quitar este aprendiz del preview? (No se eliminará de la base de datos hasta guardar)'
                    : '¿Estás seguro de eliminar este aprendiz de la ficha? Esta acción eliminará también sus registros relacionados.',
                showCancelButton: true,
                confirmButtonColor: '#df0026',
                cancelButtonColor: '#6c757d',
                confirmButtonText: 'Sí, eliminar',
                cancelButtonText: 'Cancelar'
            });

            if (!result.isConfirmed) return;

            // Si es solo preview, remover localmente
            if (isPreviewOnly) {
                this.importedData.aprendices = (this.importedData.aprendices || []).filter(a => (a.numero_documento || '').toString() !== documento);
                this.importedData.juicios = (this.importedData.juicios || []).filter(j => (j.numero_documento || '').toString() !== documento);
                this.$nextTick(() => {
                    if (window.lucide?.createIcons) {
                        lucide.createIcons();
                    }
                });
                return;
            }

            // Si existe en BD, eliminar en servidor
            if (!aprendiz.id) {
                return;
            }

            try {
                const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '{{ csrf_token() }}';
                const url = `{{ url('/fichas') }}/${@js($ficha->id)}/aprendices/${aprendiz.id}`;
                const response = await fetch(url, {
                    method: 'DELETE',
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                        'X-CSRF-TOKEN': csrfToken,
                        'Accept': 'application/json'
                    }
                });

                const data = await response.json().catch(() => ({}));
                if (!response.ok || !data.success) {
                    throw new Error(data.message || 'No se pudo eliminar el aprendiz');
                }

                // Quitar de la lista base y del preview si existía
                this.initialAprendices = (this.initialAprendices || []).filter(a => (a.numero_documento || '').toString() !== documento);
                this.importedData.aprendices = (this.importedData.aprendices || []).filter(a => (a.numero_documento || '').toString() !== documento);
                this.importedData.juicios = (this.importedData.juicios || []).filter(j => (j.numero_documento || '').toString() !== documento);

                await Swal.fire({
                    icon: 'success',
                    title: 'Eliminado',
                    text: data.message || 'Aprendiz eliminado correctamente',
                    confirmButtonColor: '#39A900'
                });

                this.$nextTick(() => {
                    if (window.lucide?.createIcons) {
                        lucide.createIcons();
                    }
                });
            } catch (e) {
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: e.message || 'Ocurrió un error al eliminar el aprendiz.',
                    confirmButtonColor: '#39A900'
                });
            }
        },
        importedData: {
            aprendices: @js($oldImportedAprendicesArray),
            juicios: @js($oldImportedJuiciosArray),
        },
        initialAprendices: @js($ficha->aprendices->map(function ($a) {
            return [
                'id' => $a->id,
                'tipo_documento' => $a->tipo_documento,
                'numero_documento' => $a->documento,
                'nombre' => $a->nombre,
                'apellido' => $a->apellido,
                'celular' => $a->telefono,
                'email' => $a->correo,
                'estado' => $a->estado,
                'resultados_aprendizaje' => $a->resultados_aprendizaje,
            ];
        })->values()->toArray()),
        isSubmitting: false,
        importModalOpen: false,
        currentImportType: 'aprendices',
        selectedFile: null,
        isImporting: false,

        openImportModal(type) {
            this.currentImportType = type;
            this.selectedFile = null;
            this.importModalOpen = true;
            document.body.classList.add('modal-open');
            this.$nextTick(() => {
                if (window.lucide?.createIcons) {
                    lucide.createIcons();
                }
            });
        },

        closeImportModal() {
            if (this.isImporting) {
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
            if (!file) return;
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
            const maxSize = 5 * 1024 * 1024;
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
        },

        formatFileSize(bytes) {
            if (bytes === 0) return '0 Bytes';
            const k = 1024;
            const sizes = ['Bytes', 'KB', 'MB', 'GB'];
            const i = Math.floor(Math.log(bytes) / Math.log(k));
            return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i];
        },

        isEstadoExcluido(estado) {
            const estadoUpper = (estado || '').toString().trim().toUpperCase();
            return ['RETIRO VOLUNTARIO', 'CANCELADO', 'TRASLADADO', 'APLAZADO'].includes(estadoUpper);
        },

        clearPreview() {
            this.importedData.aprendices = [];
            this.importedData.juicios = [];
            this.$nextTick(() => {
                if (window.lucide?.createIcons) {
                    lucide.createIcons();
                }
            });
        },

        parseResultados(value) {
            const raw = (value || '0/0').toString();
            if (!raw.includes('/')) return { porEvaluar: 0, total: 0 };
            const parts = raw.split('/');
            const porEvaluar = parseInt(parts[0] || '0', 10) || 0;
            const total = parseInt(parts[1] || '0', 10) || 0;
            return { porEvaluar, total };
        },

        getResultadoClass(value) {
            const { porEvaluar, total } = this.parseResultados(value);
            if (total === 0) return 'bg-slate-100 text-slate-600';
            if (porEvaluar > 0) return 'bg-yellow-100 text-yellow-800';
            return 'bg-green-100 text-green-800';
        },

        getRowClass(aprendiz) {
            const estado = (aprendiz.estado || '').toString();
            if (this.isEstadoExcluido(estado)) {
                return 'bg-red-50 hover:bg-red-100';
            }

            if (aprendiz._previewNew) {
                return 'bg-blue-50 hover:bg-blue-100';
            }
            if (aprendiz._previewUpdated) {
                return 'bg-amber-50 hover:bg-amber-100';
            }
            return 'hover:bg-slate-50';
        },

        get displayAprendices() {
            const base = Array.isArray(this.initialAprendices) ? this.initialAprendices : [];
            const preview = Array.isArray(this.importedData.aprendices) ? this.importedData.aprendices : [];
            const juicios = Array.isArray(this.importedData.juicios) ? this.importedData.juicios : [];

            const juiciosMap = new Map();
            juicios.forEach(j => {
                const doc = (j.numero_documento || '').toString();
                if (doc) {
                    juiciosMap.set(doc, j);
                }
            });

            const map = new Map();
            base.forEach(a => {
                const doc = (a.numero_documento || '').toString();
                if (doc) map.set(doc, { ...a });
            });

            preview.forEach(p => {
                const doc = (p.numero_documento || '').toString();
                if (!doc) return;

                const existing = map.get(doc);
                if (existing) {
                    map.set(doc, {
                        ...existing,
                        estado: (p.estado || existing.estado),
                        celular: (p.celular !== '' && typeof p.celular !== 'undefined') ? p.celular : existing.celular,
                        email: (p.email !== '' && typeof p.email !== 'undefined') ? p.email : existing.email,
                        _previewUpdated: true,
                    });
                } else {
                    map.set(doc, {
                        tipo_documento: p.tipo_documento || 'CC',
                        numero_documento: doc,
                        nombre: p.nombre,
                        apellido: p.apellido,
                        celular: p.celular,
                        email: p.email,
                        estado: p.estado,
                        resultados_aprendizaje: '0/0',
                        _previewNew: true,
                    });
                }
            });

            // Aplicar conteo de juicios evaluativos (preview)
            for (const [doc, item] of juiciosMap.entries()) {
                const existing = map.get(doc);
                if (!existing) continue;
                const total = parseInt(item.total_resultados ?? 0, 10) || 0;
                const porEvaluar = parseInt(item.por_evaluar ?? 0, 10) || 0;
                map.set(doc, {
                    ...existing,
                    resultados_aprendizaje: `${porEvaluar}/${total}`,
                });
            }

            return Array.from(map.values());
        },

        async handleImport() {
            if (!this.selectedFile || this.isImporting) return;
            this.isImporting = true;

            try {
                const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '{{ csrf_token() }}';

                if (this.currentImportType === 'juicios') {
                    if (this.displayAprendices.length === 0) {
                        throw new Error('No hay aprendices para relacionar los juicios evaluativos.');
                    }

                    const formData = new FormData();
                    formData.append('archivo', this.selectedFile);
                    const numerosDocumento = this.displayAprendices
                        .map(a => (a.numero_documento || '').toString())
                        .filter(v => v);
                    numerosDocumento.forEach(numDoc => {
                        formData.append('numeros_documento[]', numDoc);
                    });

                    const response = await fetch('{{ route("fichas.importar-juicios-evaluativos") }}', {
                        method: 'POST',
                        body: formData,
                        headers: {
                            'X-Requested-With': 'XMLHttpRequest',
                            'X-CSRF-TOKEN': csrfToken
                        }
                    });

                    const data = await response.json();
                    if (!response.ok || !data.success) {
                        throw new Error(data.message || 'Error al importar el archivo');
                    }

                    const conteo = Array.isArray(data.conteo_por_aprendiz)
                        ? data.conteo_por_aprendiz
                        : (data.conteo_por_aprendiz && typeof data.conteo_por_aprendiz === 'object'
                            ? Object.values(data.conteo_por_aprendiz)
                            : []);

                    if (!Array.isArray(conteo) || conteo.length === 0) {
                        throw new Error(data.message || 'No se encontraron juicios evaluativos válidos');
                    }

                    this.importedData.juicios = conteo;

                    await Swal.fire({
                        icon: 'success',
                        title: '¡Juicios procesados!',
                        html: `<p class="text-sm text-slate-700">${data.message || 'Juicios evaluativos procesados correctamente'}</p>
                               <div class="mt-3 text-xs text-slate-600">
                                    <p><strong>Aprendices con conteo:</strong> ${data.total_aprendices ?? conteo.length}</p>
                               </div>`,
                        confirmButtonColor: '#39A900'
                    });
                } else {
                    const formData = new FormData();
                    formData.append('archivo', this.selectedFile);
                    formData.append('ficha_id', @js($ficha->id));

                    const response = await fetch('{{ route("fichas.importar-aprendices") }}', {
                        method: 'POST',
                        body: formData,
                        headers: {
                            'X-Requested-With': 'XMLHttpRequest',
                            'X-CSRF-TOKEN': csrfToken
                        }
                    });

                    const data = await response.json();

                    if (!response.ok || !data.success) {
                        throw new Error(data.message || 'Error al importar el archivo');
                    }

                    let html = `<p class="text-sm text-slate-700">${data.message || 'Importación completada'}</p>`;
                    if (typeof data.created !== 'undefined' || typeof data.updated !== 'undefined') {
                        html += `<div class="mt-3 text-xs text-slate-600">
                            <p><strong>Nuevos:</strong> ${data.created ?? 0}</p>
                            <p><strong>Actualizados:</strong> ${data.updated ?? 0}</p>
                            <p><strong>Total en preview:</strong> ${data.total ?? 0}</p>
                            <p><strong>Omitidos por estado excluido:</strong> ${data.skipped_excluded ?? 0}</p>
                        </div>`;
                    }
                    if (data.conflicts && data.conflicts.length > 0) {
                        html += `<div class="mt-3 p-2 bg-yellow-50 border border-yellow-200 rounded-md">
                            <p class="text-xs font-semibold text-yellow-800 mb-1">Conflictos (${data.conflicts.length})</p>
                            <div class="max-h-32 overflow-y-auto">
                                <ul class="text-2xs text-yellow-900 space-y-1">
                                    ${data.conflicts.map(c => `<li>${c.documento} - ${c.nombre}</li>`).join('')}
                                </ul>
                            </div>
                        </div>`;
                    }

                    await Swal.fire({
                        icon: (data.conflicts && data.conflicts.length > 0) ? 'warning' : 'success',
                        title: (data.conflicts && data.conflicts.length > 0) ? 'Importación con advertencias' : '¡Importación exitosa!',
                        html,
                        confirmButtonColor: '#39A900'
                    });

                    const listaAprendices = Array.isArray(data.aprendices)
                        ? data.aprendices
                        : (data.aprendices && typeof data.aprendices === 'object'
                            ? Object.values(data.aprendices)
                            : []);
                    this.importedData.aprendices = listaAprendices;
                }

                // Permitir el cierre del modal después de confirmar el mensaje
                this.isImporting = false;
                this.selectedFile = null;
                const fileInput = document.getElementById('importFile');
                if (fileInput) {
                    fileInput.value = '';
                }
                this.closeImportModal();
            } catch (error) {
                Swal.fire({
                    icon: 'error',
                    title: 'Error en la importación',
                    text: error.message || 'Ocurrió un error al importar el archivo.',
                    confirmButtonColor: '#39A900'
                });
            } finally {
                this.isImporting = false;
                this.$nextTick(() => {
                    if (window.lucide?.createIcons) {
                        lucide.createIcons();
                    }
                });
            }
        },
    }
}

document.addEventListener('DOMContentLoaded', () => {
    if (window.lucide?.createIcons) {
        lucide.createIcons();
    }
});
</script>

@endsection