@extends('layouts.app')

@section('content')
 

<div x-data="funcionariosManager()">
    <!-- Header -->
    <div class="mb-3 md:mb-4">
        <h1 class="text-2xs md:text-xs font-semibold text-verde-sena mb-2 md:mb-3 tracking-wide">Gestionar Funcionarios</h1>
        
        <!-- Barra de búsqueda y botón agregar -->
        <div class="flex flex-col md:flex-row gap-1.5 md:gap-2 items-stretch md:items-center justify-between">
            <form action="{{ route('funcionarios.index') }}" method="GET" class="relative flex-1 max-w-md min-w-0">
                <input 
                    type="text" 
                    name="search"
                    value="{{ $search ?? '' }}"
                    placeholder="Buscar por nombre, correo, teléfono o rol..."
                    class="w-full pl-7 pr-2 py-1.5 md:py-1 text-2xs md:text-xs border border-slate-300 rounded-md focus:outline-none focus:ring-2 focus:ring-verde-sena focus:border-transparent"
                >
                <i data-lucide="search" class="absolute left-2 top-1/2 transform -translate-y-1/2 w-3 h-3 text-slate-400"></i>
                
                @if($search ?? false)
                <a href="{{ route('funcionarios.index') }}" 
                   class="absolute right-2 top-1/2 transform -translate-y-1/2 text-slate-400 hover:text-red-500 cursor-pointer"
                   title="Limpiar búsqueda">
                    <i data-lucide="x" class="w-3 h-3"></i>
                </a>
                @endif
            </form>
            
            <button 
                @click="openModal('create')"
                class="cursor-pointer bg-verde-sena hover:bg-green-700 text-white font-medium px-2.5 py-1.5 md:py-1 rounded-md transition duration-200 flex items-center justify-center gap-1 text-2xs md:text-xs whitespace-nowrap"
            >
                <i data-lucide="user-plus" class="w-3 h-3"></i>
                Agregar Funcionario
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

    <!-- Tabla de funcionarios -->
    <div class="border border-slate-200 rounded-md overflow-hidden mb-4">
        <div class="table-container">
            <table class="w-full text-2xs md:text-xs min-w-max md:min-w-full">
                <thead class="bg-verde-sena text-white">
                    <tr>
                        <th class="px-2 py-1.5 text-center font-medium whitespace-nowrap">Nombre</th>
                        <th class="px-2 py-1.5 text-center font-medium whitespace-nowrap">Correo</th>
                        <th class="px-2 py-1.5 text-center font-medium whitespace-nowrap">Teléfono</th>
                        <th class="px-2 py-1.5 text-center font-medium whitespace-nowrap">Rol</th>
                        <th class="px-2 py-1.5 text-center font-medium whitespace-nowrap">Acciones</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-200 bg-white">
                    @forelse($funcionarios as $funcionario)
                    <tr class="hover:bg-slate-50 transition duration-150 text-center align-middle">
                        <!-- Nombre -->
                        <td class="px-2 py-1.5 text-slate-900 align-middle">
                            <div class="font-medium capitalize">
                                {{ $funcionario->nombre }}
                            </div>
                        </td>

                        <!-- Correo -->
                        <td class="px-2 py-1.5 text-slate-700 align-middle">
                            <div class="truncate max-w-[150px] mx-auto">
                                {{ $funcionario->correo }}
                            </div>
                        </td>

                        <!-- Teléfono -->
                        <td class="px-2 py-1.5 text-slate-700 align-middle">
                            {{ $funcionario->telefono }}
                        </td>

                        <!-- Rol -->
                        <td class="px-2 py-1.5 align-middle">
                            <span class="badge-rol badge-{{ $funcionario->rol }}">
                                @switch($funcionario->rol)
                                    @case('administrador')
                                        <i data-lucide="shield" class="w-2.5 h-2.5"></i>
                                        @break
                                    @case('coordinador')
                                        <i data-lucide="users" class="w-2.5 h-2.5"></i>
                                        @break
                                    @case('instructor')
                                        <i data-lucide="graduation-cap" class="w-2.5 h-2.5"></i>
                                        @break
                                @endswitch
                                {{ $funcionario->rol }}
                            </span>
                        </td>

                        <!-- Acciones -->
                        <td class="px-2 py-1.5 whitespace-nowrap align-middle">
                            <div class="flex justify-center items-center flex-wrap gap-1">
                                <button 
                                    @click="openModal('edit', {{ $funcionario->id }}, '{{ addslashes($funcionario->nombre) }}', '{{ $funcionario->correo }}', '{{ $funcionario->telefono }}', '{{ $funcionario->rol }}')"
                                    class="bg-blue-500 hover:bg-blue-600 text-white px-1.5 py-1 rounded-md transition duration-200 flex items-center gap-0.5 text-2xs"
                                    title="Editar"
                                >
                                    <i data-lucide="pencil" class="w-2.5 h-2.5"></i>
                                    <span class="hidden lg:inline">Editar</span>
                                </button>

                                <button 
                                    @click="confirmDelete({{ $funcionario->id }}, '{{ addslashes($funcionario->nombre) }}')"
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
                        <td colspan="5" class="px-2 py-4 text-center text-slate-500 text-2xs md:text-xs">
                            <div class="flex flex-col items-center gap-1">
                                <i data-lucide="users" class="w-6 h-6 text-slate-300"></i>
                                @if($search ?? false)
                                    <p>No se encontraron funcionarios para "{{ $search }}"</p>
                                    <a href="{{ route('funcionarios.index') }}" class="text-verde-sena hover:underline text-2xs">
                                        Ver todos los funcionarios
                                    </a>
                                @else
                                    <p>No hay funcionarios registrados</p>
                                @endif
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- Paginación -->
    @if($funcionarios->hasPages())
    <div class="pagination">
        <div class="pagination-info">
            Mostrando {{ $funcionarios->firstItem() ?? 0 }} - {{ $funcionarios->lastItem() ?? 0 }} de {{ $funcionarios->total() }} funcionarios
        </div>
        
        <nav aria-label="Paginación">
            <ul class="flex flex-wrap items-center gap-1">
                <!-- Enlace Anterior -->
                @if($funcionarios->onFirstPage())
                <li class="page-item disabled">
                    <span class="page-link">
                        <i data-lucide="chevron-left" class="w-3 h-3"></i>
                    </span>
                </li>
                @else
                <li class="page-item">
                    <a href="{{ $funcionarios->previousPageUrl() }}{{ $search ? '&search=' . $search : '' }}" 
                       class="page-link">
                        <i data-lucide="chevron-left" class="w-3 h-3"></i>
                    </a>
                </li>
                @endif

                <!-- Números de página -->
                @php
                    $current = $funcionarios->currentPage();
                    $last = $funcionarios->lastPage();
                    $start = max(1, $current - 2);
                    $end = min($last, $current + 2);
                @endphp

                @if($start > 1)
                <li class="page-item">
                    <a href="{{ $funcionarios->url(1) }}{{ $search ? '&search=' . $search : '' }}" 
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
                    <a href="{{ $funcionarios->url($i) }}{{ $search ? '&search=' . $search : '' }}" 
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
                    <a href="{{ $funcionarios->url($last) }}{{ $search ? '&search=' . $search : '' }}" 
                       class="page-link">{{ $last }}</a>
                </li>
                @endif

                <!-- Enlace Siguiente -->
                @if($funcionarios->hasMorePages())
                <li class="page-item">
                    <a href="{{ $funcionarios->nextPageUrl() }}{{ $search ? '&search=' . $search : '' }}" 
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
                <form @submit.prevent="submitForm" id="funcionarioForm">
                    @csrf
                    <div x-html="methodField"></div>

                    <div class="px-4 py-3 space-y-3">
                        <!-- Nombre -->
                        <div>
                            <label
                                for="nombre"
                                class="block mb-1 text-2xs font-medium text-slate-600"
                            >
                                Nombre Completo
                            </label>
                            <input
                                type="text"
                                id="nombre"
                                name="nombre"
                                x-model="formData.nombre"
                                required
                                :disabled="isSubmitting"
                                placeholder="Ingrese el nombre completo"
                                class="w-full rounded-lg px-3 py-2 text-2xs border border-verde-sena focus:border-verde-sena focus:ring-2 focus:ring-verde-sena/40 focus:outline-none focus:ring-offset-0 appearance-none transition disabled:opacity-50 disabled:cursor-not-allowed"
                            >
                        </div>

                        <!-- Correo -->
                        <div>
                            <label
                                for="correo"
                                class="block mb-1 text-2xs font-medium text-slate-600"
                            >
                                Correo Electrónico
                            </label>
                            <input
                                type="email"
                                id="correo"
                                name="correo"
                                x-model="formData.correo"
                                required
                                :disabled="isSubmitting"
                                placeholder="ejemplo@correo.com"
                                class="w-full rounded-lg px-3 py-2 text-2xs border border-verde-sena focus:border-verde-sena focus:ring-2 focus:ring-verde-sena/40 focus:outline-none focus:ring-offset-0 appearance-none transition disabled:opacity-50 disabled:cursor-not-allowed"
                            >
                        </div>

                        <!-- Teléfono -->
                        <div>
                            <label
                                for="telefono"
                                class="block mb-1 text-2xs font-medium text-slate-600"
                            >
                                Teléfono
                            </label>
                            <input
                                type="text"
                                id="telefono"
                                name="telefono"
                                x-model="formData.telefono"
                                required
                                :disabled="isSubmitting"
                                placeholder="300 123 4567"
                                class="w-full rounded-lg px-3 py-2 text-2xs border border-verde-sena focus:border-verde-sena focus:ring-2 focus:ring-verde-sena/40 focus:outline-none focus:ring-offset-0 appearance-none transition disabled:opacity-50 disabled:cursor-not-allowed"
                            >
                        </div>

                        <!-- Rol -->
                        <div>
                            <label
                                for="rol"
                                class="block mb-1 text-2xs font-medium text-slate-600"
                            >
                                Rol
                            </label>
                            <select
                                id="rol"
                                name="rol"
                                x-model="formData.rol"
                                required
                                :disabled="isSubmitting"
                                class="w-full rounded-lg px-3 py-2 text-2xs border border-verde-sena focus:border-verde-sena focus:ring-2 focus:ring-verde-sena/40 focus:outline-none focus:ring-offset-0 appearance-none transition disabled:opacity-50 disabled:cursor-not-allowed"
                            >
                                <option value="" disabled>Seleccione un rol</option>
                                <option value="administrador">Administrador</option>
                                <option value="coordinador">Coordinador</option>
                                <option value="instructor">Instructor</option>
                            </select>
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
function funcionariosManager() {
    return {
        modalOpen: false,
        modalTitle: 'Agregar Funcionario',
        formAction: '{{ route("funcionarios.store") }}',
        methodField: '',
        isSubmitting: false,
        currentMode: 'create',
        formData: {
            nombre: '',
            correo: '',
            telefono: '',
            rol: ''
        },

        openModal(mode, id = null, nombre = '', correo = '', telefono = '', rol = '') {
            this.currentMode = mode;
            
            if (mode === 'create') {
                this.modalTitle = 'Agregar Funcionario';
                this.formAction = '{{ route("funcionarios.store") }}';
                this.methodField = '';
                this.formData.nombre = '';
                this.formData.correo = '';
                this.formData.telefono = '';
                this.formData.rol = '';
            } else if (mode === 'edit') {
                this.modalTitle = 'Editar Funcionario';
                this.formAction = `/funcionarios/${id}`;
                this.methodField = '@method("PUT")';
                this.formData.nombre = nombre;
                this.formData.correo = correo;
                this.formData.telefono = telefono;
                this.formData.rol = rol;
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
            
            this.modalOpen = false;
            
            setTimeout(() => {
                document.body.classList.remove('modal-open');
            }, 250);
        },

        async submitForm() {
            if (this.isSubmitting) return;

            // Validación básica
            if (!this.formData.nombre || !this.formData.correo || !this.formData.telefono || !this.formData.rol) {
                Swal.fire({
                    icon: 'warning',
                    title: 'Campos requeridos',
                    text: 'Por favor completa todos los campos',
                    confirmButtonColor: '#39A900'
                });
                return;
            }

            // Validar formato de correo
            const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            if (!emailRegex.test(this.formData.correo)) {
                Swal.fire({
                    icon: 'warning',
                    title: 'Correo inválido',
                    text: 'Por favor ingresa un correo electrónico válido',
                    confirmButtonColor: '#39A900'
                });
                return;
            }

            const result = await Swal.fire({
                icon: 'question',
                title: this.currentMode === 'create' ? '¿Crear funcionario?' : '¿Guardar cambios?',
                text: this.currentMode === 'create' 
                    ? `¿Deseas crear el funcionario "${this.formData.nombre}"?`
                    : `¿Deseas guardar los cambios en "${this.formData.nombre}"?`,
                showCancelButton: true,
                confirmButtonColor: '#39A900',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Sí, continuar',
                cancelButtonText: 'Cancelar'
            });

            if (!result.isConfirmed) return;

            this.isSubmitting = true;
            Swal.fire({
                title: this.currentMode === 'create' ? 'Creando funcionario...' : 'Guardando cambios...',
                html: 'Por favor espera',
                allowOutsideClick: false,
                allowEscapeKey: false,
                didOpen: () => {
                    Swal.showLoading();
                }
            });

            try {
                const formData = new FormData();
                formData.append('nombre', this.formData.nombre);
                formData.append('correo', this.formData.correo);
                formData.append('telefono', this.formData.telefono);
                formData.append('rol', this.formData.rol);
                formData.append('_token', '{{ csrf_token() }}');
                
                if (this.currentMode === 'edit') {
                    formData.append('_method', 'PUT');
                }

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
                            ? 'Funcionario creado correctamente' 
                            : 'Funcionario actualizado correctamente',
                        timer: 2000,
                        timerProgressBar: true,
                        showConfirmButton: false
                    }).then(() => {
                        window.location.reload();
                    });
                } else {
                    const data = await response.json();
                    let errorMessage = 'Error al procesar la solicitud';
                    
                    if (data.errors) {
                        errorMessage = Object.values(data.errors).flat().join('\n');
                    } else if (data.message) {
                        errorMessage = data.message;
                    }
                    
                    throw new Error(errorMessage);
                }
            } catch (error) {
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: error.message,
                    confirmButtonColor: '#39A900'
                });
                this.isSubmitting = false;
            }
        },

        async confirmDelete(id, nombre) {
            const result = await Swal.fire({
                icon: 'warning',
                title: '¿Eliminar funcionario?',
                html: `¿Estás seguro de eliminar al funcionario <strong>"${nombre}"</strong>?<br><br>Esta acción no se puede deshacer.`,
                showCancelButton: true,
                confirmButtonColor: '#df0026',
                cancelButtonColor: '#6c757d',
                confirmButtonText: 'Sí, eliminar',
                cancelButtonText: 'Cancelar',
                focusCancel: true
            });

            if (result.isConfirmed) {
                Swal.fire({
                    title: 'Eliminando funcionario...',
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

                    const response = await fetch(`/funcionarios/${id}`, {
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
                            text: 'El funcionario ha sido eliminado correctamente',
                            timer: 2000,
                            timerProgressBar: true,
                            showConfirmButton: false
                        }).then(() => {
                            window.location.reload();
                        });
                    } else {
                        throw new Error('Error al eliminar el funcionario');
                    }
                } catch (error) {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: 'No se pudo eliminar el funcionario',
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