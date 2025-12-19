@extends('layouts.app')

@section('content')
<!-- Header -->
<div class="mb-3 md:mb-4">
    <h1 class="text-2xs md:text-xs font-semibold text-verde-sena mb-2 md:mb-3 tracking-wide">Gestionar Programas de Formación</h1>
    
    <!-- Barra de búsqueda y botón agregar -->
    <div class="flex flex-col md:flex-row gap-1.5 md:gap-2 items-stretch md:items-center justify-between">
        <div class="relative flex-1 max-w-md min-w-0">
            <input 
                type="text" 
                id="searchInput"
                placeholder="Buscar programa..."
                class="w-full pl-7 pr-2 py-1.5 md:py-1 text-2xs md:text-xs border border-slate-300 rounded-md focus:outline-none focus:ring-2 focus:ring-verde-sena focus:border-transparent"
            >
            <i data-lucide="search" class="absolute left-2 top-1/2 transform -translate-y-1/2 w-3 h-3 text-slate-400"></i>
        </div>
        
        <button 
            onclick="openModal('create')"
            class="bg-verde-sena hover:bg-green-700 text-white font-medium px-2.5 py-1.5 md:py-1 rounded-md transition duration-200 flex items-center justify-center gap-1 text-2xs md:text-xs whitespace-nowrap"
        >
            <i data-lucide="plus" class="w-3 h-3"></i>
            Agregar Programa
        </button>
    </div>
</div>

<!--Mensaje de éxito al crear/editar/eliminar-->
@if(session('success'))
    <div class="mb-3 p-2 bg-green-100 border border-green-300 text-green-800 rounded-md text-2xs">
        {{ session('success') }}
    </div>
@endif
<!--Mensajes de error-->
@if ($errors->any())
    <div class="mb-3 p-2 bg-red-100 border border-red-300 text-red-800 rounded-md text-2xs">
        <ul class="list-disc list-inside">
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
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
            <tbody class="divide-y divide-slate-200 bg-white" id="tableBody">
                @forelse($programas as $programa)
                <tr class="hover:bg-slate-50 transition duration-150 text-center align-middle">
                    <!-- Nivel -->
                    <td class="px-2 py-1.5 whitespace-nowrap align-middle">
                        <span
                            class="inline-block uppercase px-1.5 py-0.5 rounded-full text-2xs font-medium
                                   bg-blue-100 text-blue-800"
                        >
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
                                onclick="openModal(
                                    'edit',
                                    {{ $programa->id }},
                                    '{{ $programa->nivel }}',
                                    '{{ addslashes($programa->nombre) }}'
                                )"
                                class="bg-blue-500 hover:bg-blue-600 text-white
                                       px-1.5 py-1 rounded-md transition duration-200
                                       flex items-center gap-0.5 text-2xs"
                                title="Editar"
                            >
                                <i data-lucide="pencil" class="w-2.5 h-2.5"></i>
                                <span class="hidden sm:inline">Editar</span>
                            </button>

                            <form
                                action="{{ route('programas.destroy', $programa->id) }}"
                                method="POST"
                                onsubmit="return confirm('¿Estás seguro de eliminar este programa?');"
                            >
                                @csrf
                                @method('DELETE')
                                <button 
                                    type="submit"
                                    class="bg-red-500 hover:bg-red-600 text-white
                                           px-1.5 py-1 rounded-md transition duration-200
                                           flex items-center gap-0.5 text-2xs"
                                    title="Eliminar"
                                >
                                    <i data-lucide="trash-2" class="w-2.5 h-2.5"></i>
                                    <span class="hidden sm:inline">Eliminar</span>
                                </button>
                            </form>
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="4" class="px-2 py-4 text-center text-slate-500 text-2xs md:text-xs">
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
<div id="programModal" class="hidden fixed inset-0 bg-black/80 z-50 flex items-center justify-center p-3">
    <div class="bg-white rounded-md shadow-xl w-full max-w-sm mx-2">
        <div class="bg-verde-sena text-white px-3 py-2 rounded-t-md flex justify-between items-center">
            <h2 id="modalTitle" class="text-2xs font-semibold">Agregar Programa</h2>
            <button onclick="closeModal()" class="text-white hover:text-slate-200 transition">
                <i data-lucide="x" class="w-3.5 h-3.5"></i>
            </button>
        </div>
        
        <form id="programForm" method="POST">
            @csrf
            <div id="methodField"></div>
            
            <div class="p-3 space-y-2">
                <!-- Nivel (Select) -->
                <div>
                    <label for="nivel" class="block text-2xs font-medium text-slate-700 mb-0.5">
                        Nivel
                    </label>
                    <select
                        id="nivel"
                        name="nivel"
                        required
                        class="w-full px-2 py-1.5 text-2xs border border-slate-300 rounded-md
                               focus:outline-none focus:ring-2 focus:ring-verde-sena
                               focus:border-transparent bg-white"
                    >
                        <option value="" disabled selected>Seleccione un nivel</option>
                        <option value="Auxiliar">Auxiliar</option>
                        <option value="Operario">Operario</option>
                        <option value="Técnico">Técnico</option>
                        <option value="Tecnólogo">Tecnólogo</option>
                    </select>
                </div>
                
                <!-- Nombre del Programa -->
                <div>
                    <label for="nombre" class="block text-2xs font-medium text-slate-700 mb-0.5">
                        Nombre del Programa
                    </label>
                    <input 
                        type="text"
                        id="nombre"
                        name="nombre"
                        required
                        class="w-full px-2 py-1.5 text-2xs border border-slate-300 rounded-md
                               focus:outline-none focus:ring-2 focus:ring-verde-sena
                               focus:border-transparent"
                        placeholder="Ingrese el nombre del programa"
                    >
                </div>
            </div>
            
            <!-- Footer -->
            <div class="bg-slate-50 px-3 py-2 rounded-b-md flex justify-end gap-1.5">
                <button 
                    type="button"
                    onclick="closeModal()"
                    class="px-2.5 py-1 text-2xs border border-slate-300 rounded-md
                           text-slate-700 hover:bg-slate-100 transition duration-200 font-medium"
                >
                    Cancelar
                </button>
                <button 
                    type="submit"
                    class="px-2.5 py-1 text-2xs bg-verde-sena hover:bg-green-700
                           text-white rounded-md transition duration-200 font-medium"
                >
                    Guardar
                </button>
            </div>
        </form>
    </div>
</div>

<script>
// Inicializar iconos de Lucide cuando se carga la página
document.addEventListener('DOMContentLoaded', () => {
    if (window.lucide?.createIcons) {
        lucide.createIcons();
    }
});

function openModal(mode, id = null, nivel = '', nombre = '') {
    const modal = document.getElementById('programModal');
    const form = document.getElementById('programForm');
    const modalTitle = document.getElementById('modalTitle');
    const methodField = document.getElementById('methodField');
    const nivelSelect = document.getElementById('nivel');
    const nombreInput = document.getElementById('nombre');
    
    if (mode === 'create') {
        modalTitle.textContent = 'Agregar Programa';
        form.action = '{{ route("programas.store") }}';
        methodField.innerHTML = '';
        nivelSelect.value = '';
        nombreInput.value = '';
    } else if (mode === 'edit') {
        modalTitle.textContent = 'Editar Programa';
        form.action = `/programas/${id}`;
        methodField.innerHTML = '@method("PUT")';
        nivelSelect.value = nivel;
        nombreInput.value = nombre;
    }
    
    modal.classList.remove('hidden');
    document.body.style.overflow = 'hidden';
    
    setTimeout(() => {
        if (window.lucide?.createIcons) {
            lucide.createIcons();
        }
    }, 50);
}

function closeModal() {
    const modal = document.getElementById('programModal');
    modal.classList.add('hidden');
    document.body.style.overflow = '';
}

// Cerrar modal al hacer clic fuera
document.getElementById('programModal').addEventListener('click', function(e) {
    if (e.target === this) {
        closeModal();
    }
});

// Cerrar modal con tecla Escape
document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') {
        closeModal();
    }
});

// Funcionalidad de búsqueda (frontend)
document.getElementById('searchInput').addEventListener('keyup', function() {
    const searchText = this.value.toLowerCase();
    const rows = document.querySelectorAll('#tableBody tr');
    
    rows.forEach(row => {
        const text = row.textContent.toLowerCase();
        row.style.display = text.includes(searchText) ? '' : 'none';
    });
});
</script>
@endsection