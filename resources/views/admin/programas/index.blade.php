@extends('layouts.app')

@section('content')
<style>
/* Prevenir el salto del scrollbar */
body.modal-open {
    overflow: hidden !important;
}

/* Asegurar que el fondo del modal no cause saltos */
.modal-backdrop {
    position: fixed;
    inset: 0;
    background-color: rgba(0, 0, 0, 0.8);
    z-index: 50;
}

.modal-content {
    position: relative;
    z-index: 51;
}
</style>

<div x-data="programasManager()">
    <!-- Header -->
    <div class="mb-3 md:mb-4">
        <h1 class="text-2xs md:text-xs font-semibold text-verde-sena mb-2 md:mb-3 tracking-wide">Gestionar Programas de Formación</h1>
        
        <!-- Barra de búsqueda y botón agregar -->
        <div class="flex flex-col md:flex-row gap-1.5 md:gap-2 items-stretch md:items-center justify-between">
            <div class="relative flex-1 max-w-md min-w-0">
                <input 
                    type="text" 
                    x-model="searchQuery"
                    placeholder="Buscar programa..."
                    class="w-full pl-7 pr-2 py-1.5 md:py-1 text-2xs md:text-xs border border-slate-300 rounded-md focus:outline-none focus:ring-2 focus:ring-verde-sena focus:border-transparent"
                >
                <i data-lucide="search" class="absolute left-2 top-1/2 transform -translate-y-1/2 w-3 h-3 text-slate-400"></i>
            </div>
            
            <button 
                @click="openModal('create')"
                class=" cursor-pointer bg-verde-sena hover:bg-green-700 text-white font-medium px-2.5 py-1.5 md:py-1 rounded-md transition duration-200 flex items-center justify-center gap-1 text-2xs md:text-xs whitespace-nowrap"
            >
                <i data-lucide="plus" class="w-3 h-3"></i>
                Agregar Programa
            </button>
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

    <!-- Tabla de programas -->
    <div class="border border-slate-200 rounded-md overflow-hidden">
        <div class="table-container">
            <table class="w-full text-2xs md:text-xs min-w-max md:min-w-full">
                <thead class="bg-verde-sena text-white">
                    <tr>
                        <th class="px-2 py-1.5 text-center font-medium whitespace-nowrap">Nivel</th>
                        <th class="px-2 py-1.5 text-center font-medium whitespace-nowrap">Nombre del Programa</th>
                        <th class="px-2 py-1.5 text-center font-medium whitespace-nowrap">Acciones</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-200 bg-white">
                    @forelse($programas as $programa)
                    <tr 
                        x-show="matchesSearch('{{ strtolower($programa->nivel) }} {{ strtolower($programa->nombre) }}')"
                        x-transition
                        class="hover:bg-slate-50 transition duration-150 text-center align-middle"
                    >
                        <!-- Nivel -->
                        <td class="px-2 py-1.5 whitespace-nowrap align-middle">
                            <span class="inline-block uppercase px-1.5 py-0.5 rounded-full text-2xs font-medium bg-blue-100 text-blue-800">
                                {{ $programa->nivel }}
                            </span>
                        </td>

                        <!-- Nombre -->
                        <td class="px-2 py-1.5 text-slate-900 min-w-[180px] align-middle">
                            <div class="truncate max-w-xs mx-auto capitalize font-semibold">
                                {{ $programa->nombre }}
                            </div>
                        </td>

                        <!-- Acciones -->
                        <td class="px-2 py-1.5 whitespace-nowrap align-middle">
                            <div class="flex justify-center items-center flex-wrap gap-1">
                                <button 
                                    @click="openModal('edit', {{ $programa->id }}, '{{ $programa->nivel }}', '{{ addslashes($programa->nombre) }}')"
                                    class="bg-blue-500 hover:bg-blue-600 text-white px-1.5 py-1 rounded-md transition duration-200 flex items-center gap-0.5 text-2xs"
                                    title="Editar"
                                >
                                    <i data-lucide="pencil" class="w-2.5 h-2.5"></i>
                                    <span class="hidden sm:inline">Editar</span>
                                </button>

                                <button 
                                    @click="confirmDelete({{ $programa->id }}, '{{ addslashes($programa->nombre) }}')"
                                    class="bg-red-500 cursor-pointer hover:bg-red-600 text-white px-1.5 py-1 rounded-md transition duration-200 flex items-center gap-0.5 text-2xs"
                                    title="Eliminar"
                                >
                                    <i data-lucide="trash-2" class="w-2.5 h-2.5"></i>
                                    <span class="hidden sm:inline">Eliminar</span>
                                </button>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="3" class="px-2 py-4 text-center text-slate-500 text-2xs md:text-xs">
                            <div class="flex flex-col items-center gap-1">
                                <i data-lucide="inbox" class="w-6 h-6 text-slate-300"></i>
                                <p>No hay programas registrados</p>
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

<!-- Modal para crear/editar -->
<template x-if="modalOpen">
    <div
        x-show="modalOpen"
        @click.self="closeModal()"
        @keydown.escape.window="closeModal()"
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
            class="w-full max-w-md bg-white rounded-xl shadow-2xl border border-slate-200"
            x-transition:enter="transition ease-out duration-300"
            x-transition:enter-start="opacity-0 scale-95 translate-y-2"
            x-transition:enter-end="opacity-100 scale-100 translate-y-0"
            x-transition:leave="transition ease-in duration-200"
            x-transition:leave-start="opacity-100 scale-100"
            x-transition:leave-end="opacity-0 scale-95"
        >
            <!-- Header -->
            <div class="flex items-center justify-between px-4 py-3 rounded-t-xl bg-verde-sena text-white">
                <h2 x-text="modalTitle" class="text-xs font-semibold tracking-wide"></h2>
                <button
                    @click="closeModal()"
                    class="p-1 rounded-full cursor-pointer hover:bg-white/20 transition"
                >
                    <i data-lucide="x" class="w-4 h-4"></i>
                </button>
            </div>

            <!-- Form -->
            <form @submit.prevent="submitForm" id="programForm">
                @csrf
                <div x-html="methodField"></div>

                <div class="px-4 py-3 space-y-3">
                    <!-- Nivel -->
                    <div>
                        <label
                            for="nivel"
                            class="block mb-1 text-2xs font-medium text-slate-600"
                        >
                            Nivel
                        </label>
                        <select
                            id="nivel"
                            name="nivel"
                            x-model="formData.nivel"
                            required
                            :disabled="isSubmitting"
                            class="w-full rounded-lg px-3 py-2 text-2xs border border-verde-sena focus:border-verde-sena focus:ring-2 focus:ring-verde-sena/40 focus:outline-none focus:ring-offset-0 appearance-none transition disabled:opacity-50 disabled:cursor-not-allowed"
                        >
                            <option value="" disabled>Seleccione un nivel</option>
                            <option>Auxiliar</option>
                            <option>Operario</option>
                            <option>Técnico</option>
                            <option>Tecnólogo</option>
                        </select>
                    </div>

                    <!-- Nombre -->
                    <div>
                        <label
                            for="nombre"
                            class="block mb-1 text-2xs font-medium text-slate-600"
                        >
                            Nombre del Programa
                        </label>
                        <input
                            type="text"
                            id="nombre"
                            name="nombre"
                            x-model="formData.nombre"
                            required
                            :disabled="isSubmitting"
                            placeholder="Ingrese el nombre del programa"
                            class="w-full rounded-lg px-3 py-2 text-2xs border border-verde-sena focus:border-verde-sena focus:ring-2 focus:ring-verde-sena/40 focus:outline-none focus:ring-offset-0 appearance-none transition disabled:opacity-50 disabled:cursor-not-allowed"

                        >
                    </div>
                </div>

                <!-- Footer -->
                <div class="flex justify-end gap-2 px-4 py-3 bg-slate-50 rounded-b-xl border-t">
                    <button
                        type="button"
                        @click="closeModal()"
                        :disabled="isSubmitting"
                        class="px-3 cursor-pointer py-1.5 text-2xs rounded-lg border border-slate-300
                               text-slate-700 hover:bg-slate-100 transition
                               disabled:opacity-50 disabled:cursor-not-allowed"
                    >
                        Cancelar
                    </button>

                    <button
                        type="submit"
                        :disabled="isSubmitting"
                        class="px-4 cursor-pointer py-1.5 text-2xs rounded-lg bg-verde-sena text-white
                               hover:bg-green-700 transition font-medium
                               disabled:opacity-50 disabled:cursor-not-allowed
                               flex items-center gap-1.5"
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

                        <span x-text="isSubmitting ? 'Guardando...' : 'Guardar'"></span>
                    </button>
                </div>
            </form>
        </div>
    </div>
</template>


    <!-- Formulario oculto para eliminar -->
    <form id="deleteForm" method="POST" style="display: none;">
        @csrf
        @method('DELETE')
    </form>
</div>

<script>
function programasManager() {
    return {
        searchQuery: '',
        modalOpen: false,
        modalTitle: 'Agregar Programa',
        formAction: '{{ route("programas.store") }}',
        methodField: '',
        isSubmitting: false,
        currentMode: 'create',
        formData: {
            nivel: '',
            nombre: ''
        },

        matchesSearch(text) {
            if (!this.searchQuery) return true;
            return text.includes(this.searchQuery.toLowerCase());
        },

        openModal(mode, id = null, nivel = '', nombre = '') {
            this.currentMode = mode;
            
            if (mode === 'create') {
                this.modalTitle = 'Agregar Programa';
                this.formAction = '{{ route("programas.store") }}';
                this.methodField = '';
                this.formData.nivel = '';
                this.formData.nombre = '';
            } else if (mode === 'edit') {
                this.modalTitle = 'Editar Programa';
                this.formAction = `/programas/${id}`;
                this.methodField = '@method("PUT")';
                this.formData.nivel = nivel;
                this.formData.nombre = nombre;
            }
            
            // Bloquear scroll
            document.body.classList.add('modal-open');
            
            // Abrir modal
            this.modalOpen = true;
            
            // Inicializar iconos
            this.$nextTick(() => {
                if (window.lucide?.createIcons) {
                    lucide.createIcons();
                }
            });
        },

        closeModal() {
            if (this.isSubmitting) {
                Swal.fire({
                    icon: 'warning',
                    title: 'Espera',
                    text: 'Hay una operación en proceso. Por favor espera.',
                    confirmButtonColor: '#39A900'
                });
                return;
            }
            
            // Cerrar modal (la transición se encarga de la animación)
            this.modalOpen = false;
            
            // Restaurar scroll después de que termine la animación
            setTimeout(() => {
                document.body.classList.remove('modal-open');
            }, 250);
        },

        async submitForm() {
            if (this.isSubmitting) return;

            // Validación básica
            if (!this.formData.nivel || !this.formData.nombre) {
                Swal.fire({
                    icon: 'warning',
                    title: 'Campos requeridos',
                    text: 'Por favor completa todos los campos',
                    confirmButtonColor: '#39A900'
                });
                return;
            }

            // Confirmación antes de enviar
            const result = await Swal.fire({
                icon: 'question',
                title: this.currentMode === 'create' ? '¿Crear programa?' : '¿Guardar cambios?',
                text: this.currentMode === 'create' 
                    ? `¿Deseas crear el programa "${this.formData.nombre}"?`
                    : `¿Deseas guardar los cambios en "${this.formData.nombre}"?`,
                showCancelButton: true,
                confirmButtonColor: '#39A900',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Sí, continuar',
                cancelButtonText: 'Cancelar'
            });

            if (!result.isConfirmed) return;

            // Mostrar spinner
            this.isSubmitting = true;
            Swal.fire({
                title: this.currentMode === 'create' ? 'Creando programa...' : 'Guardando cambios...',
                html: 'Por favor espera',
                allowOutsideClick: false,
                allowEscapeKey: false,
                didOpen: () => {
                    Swal.showLoading();
                }
            });

            try {
                // Crear FormData
                const formData = new FormData();
                formData.append('nivel', this.formData.nivel);
                formData.append('nombre', this.formData.nombre);
                formData.append('_token', '{{ csrf_token() }}');
                
                if (this.currentMode === 'edit') {
                    formData.append('_method', 'PUT');
                }

                // Enviar petición
                const response = await fetch(this.formAction, {
                    method: 'POST',
                    body: formData,
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                });

                if (response.ok) {
                    Swal.fire({
                        icon: 'success',
                        title: '¡Éxito!',
                        text: this.currentMode === 'create' 
                            ? 'Programa creado correctamente' 
                            : 'Programa actualizado correctamente',
                        timer: 2000,
                        timerProgressBar: true,
                        showConfirmButton: false
                    }).then(() => {
                        window.location.reload();
                    });
                } else {
                    const data = await response.json();
                    throw new Error(data.message || 'Error al procesar la solicitud');
                }
            } catch (error) {
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: error.message || 'Ocurrió un error al procesar la solicitud',
                    confirmButtonColor: '#39A900'
                });
                this.isSubmitting = false;
            }
        },

        async confirmDelete(id, nombre) {
            const result = await Swal.fire({
                icon: 'warning',
                title: '¿Eliminar programa?',
                html: `¿Estás seguro de eliminar el programa <strong>"${nombre}"</strong>?<br><br>Esta acción no se puede deshacer.`,
                showCancelButton: true,
                confirmButtonColor: '#df0026',
                cancelButtonColor: '#6c757d',
                confirmButtonText: 'Sí, eliminar',
                cancelButtonText: 'Cancelar',
                focusCancel: true
            });

            if (result.isConfirmed) {
                // Mostrar spinner
                Swal.fire({
                    title: 'Eliminando programa...',
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

                    const response = await fetch(`/programas/${id}`, {
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
                            text: 'El programa ha sido eliminado correctamente',
                            timer: 2000,
                            timerProgressBar: true,
                            showConfirmButton: false
                        }).then(() => {
                            window.location.reload();
                        });
                    } else {
                        throw new Error('Error al eliminar el programa');
                    }
                } catch (error) {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: 'No se pudo eliminar el programa',
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