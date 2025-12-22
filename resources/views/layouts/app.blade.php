<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ config('app.name', 'Laravel') }}</title>

    <link rel="icon" type="image/svg+xml" href="{{ asset('assets/sena.svg') }}">

    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <!-- Lucide -->
    <script defer src="https://unpkg.com/lucide@latest"></script>

    <!-- Evita salto inicial -->
    <style>
        [x-cloak] {
            display: none !important;
        }

        /* Animación suave para hover */
        .nav-link {
            transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1);
        }

        .nav-link:hover {
            transform: translateX(4px);
        }
        
        /* Mejora para scroll horizontal en tablas */
        .table-container {
            overflow-x: auto;
            -webkit-overflow-scrolling: touch;
        }
        
        /* Fijar el sidebar en desktop */
        @media (min-width: 768px) {
            .sidebar-fixed {
                position: fixed;
                left: 0;
                top: 0;
                bottom: 0;
                height: 100vh;
                overflow-y: auto;
                z-index: 40;
            }
            
            .content-with-sidebar {
                margin-left: 12rem; /* Ancho del sidebar (w-48 = 12rem) */
            }
            
            /* Asegurar que el contenido no se esconda detrás del sidebar */
            body {
                min-height: 100vh;
            }
        }
    </style>
</head>

<body class="bg-slate-50 text-verde-sena" x-data="{ open: false }">

    <!-- Topbar mobile - CAMBIADO A md:hidden (768px) para mejor responsive -->
    <header class="md:hidden sticky top-0 z-30 bg-white border-b border-slate-200">
        <div class="flex items-center gap-3 px-4 h-14">
            <button @click="open = !open" aria-label="Abrir menú"
                class="p-2 rounded-md border border-slate-200 hover:bg-slate-100 transition">
                <i data-lucide="menu" class="w-5 h-5"></i>
            </button>

            <img src="{{ asset('assets/sena.svg') }}" alt="SENA" class="h-6 w-auto">
            <span class="font-semibold text-sm">{{ config('app.name', 'Laravel') }}</span>
        </div>
    </header>

    <div class="min-h-screen flex">

        <!-- Overlay (mobile) -->
        <div x-cloak x-show="open" @click="open = false" class="fixed inset-0 z-30 bg-black/40 md:hidden"
            x-transition.opacity></div>

        <!-- Sidebar - Añadida clase sidebar-fixed -->
        <aside x-cloak
            class="sidebar-fixed fixed inset-y-0 left-0 z-40 w-48
                   bg-white p-3 flex flex-col gap-2
                   border-r border-slate-200
                   transform transition-transform duration-300 ease-in-out
                   will-change-transform
                   md:relative md:translate-x-0 md:flex-shrink-0"
            :class="open ? 'translate-x-0' : '-translate-x-full md:translate-x-0'"
            style="min-width: 12rem;">

            <!-- Brand -->
            <div class="flex items-center justify-center gap-3 px-2 py-3">
                <img src="{{ asset('assets/sena.svg') }}" alt="SENA" class="h-8 w-auto">
                <span class="font-semibold text-sm tracking-wide text-verde-sena">
                    {{ config('app.name', 'Laravel') }}
                </span>
            </div>

            <!-- Menu -->
            <nav class="mt-1 flex-1 space-y-0.5">
                @php
                    $linkBase = 'nav-link flex items-center gap-2 px-2 py-1.5 rounded-md text-xs';
                @endphp

                <a href="{{ url('/admin') }}"
                    class="{{ $linkBase }} {{ request()->is('admin') ? 'bg-verde-sena text-white' : 'text-verde-sena hover:bg-slate-100' }}">
                    <i data-lucide="home" class="w-3.5 h-3.5"></i>
                    <span>Inicio</span>
                </a>

                <a href="#"
                    class="{{ $linkBase }} {{ request()->is('fichas*') ? 'bg-verde-sena text-white' : 'text-verde-sena hover:bg-slate-100' }}">
                    <i data-lucide="layers" class="w-3.5 h-3.5"></i>
                    <span>Fichas</span>
                </a>

                <a href="#"
                    class="{{ $linkBase }} {{ request()->is('aprendices*') ? 'bg-verde-sena text-white' : 'text-verde-sena hover:bg-slate-100' }}">
                    <i data-lucide="users" class="w-3.5 h-3.5"></i>
                    <span>Aprendices</span>
                </a>

                <a href="#"
                    class="{{ $linkBase }} {{ request()->is('etapa-productiva*') ? 'bg-verde-sena text-white' : 'text-verde-sena hover:bg-slate-100' }}">
                    <i data-lucide="briefcase" class="w-3.5 h-3.5"></i>
                    <span>Etapa productiva</span>
                </a>

                <a href="#"
                    class="{{ $linkBase }} {{ request()->is('funcionarios*') ? 'bg-verde-sena text-white' : 'text-verde-sena hover:bg-slate-100' }}">
                    <i data-lucide="id-card" class="w-3.5 h-3.5"></i>
                    <span>Funcionarios</span>
                </a>

                <a href="/programas"
                    class="{{ $linkBase }} {{ request()->is('programas*') ? 'bg-verde-sena text-white' : 'text-verde-sena hover:bg-slate-100' }}">
                    <i data-lucide="book-open" class="w-3.5 h-3.5"></i>
                    <span>Programas</span>
                </a>
            </nav>

            <!-- Logout -->
            <form method="POST" action="#" class="mt-auto">
                <button type="submit"
                    class="w-full inline-flex items-center justify-center gap-1.5
                           px-2 py-1 rounded-md text-xs
                           bg-red-500 hover:bg-red-600
                           text-white font-medium
                           transition-colors duration-200">
                    <i data-lucide="log-out" class="w-3.5 h-3.5"></i>
                    <span>Cerrar sesión</span>
                </button>
            </form>
        </aside>

        <!-- Content - Añadida clase content-with-sidebar -->
        <main class="flex-1 min-w-0 content-with-sidebar">
            <div class="p-3 md:p-4 lg:p-6 max-w-full">
                <div class="bg-white border border-slate-200 rounded-xl p-3 md:p-4 lg:p-6 shadow-sm overflow-hidden">
                    @yield('content')
                </div>
            </div>
        </main>
    </div>

    <!-- Icons -->
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            if (window.lucide?.createIcons) {
                lucide.createIcons()
            }
        })
    </script>

</body>

</html>