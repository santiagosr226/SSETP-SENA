<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ config('app.name', 'Laravel') }}</title>

    <link rel="icon" type="image/svg+xml" href="{{ asset('assets/sena.svg') }}">

    <script>
        function pjaxLayout() {
            return {
                open: false,
                pjaxLoading: false,
                init() {
                    window.addEventListener('popstate', () => {
                        this.navigate(window.location.href, { push: false });
                    });

                    window.addEventListener('pjax:navigate', (e) => {
                        const url = e?.detail?.url || window.location.href;
                        const push = typeof e?.detail?.push === 'boolean' ? e.detail.push : false;
                        this.navigate(url, { push });
                    });
                },
                isPjaxAllowed(url) {
                    try {
                        const u = new URL(url, window.location.origin);
                        return u.origin === window.location.origin;
                    } catch (e) {
                        return false;
                    }
                },
                async navigate(url, options = {}) {
                    const { push = true } = options;
                    if (!this.isPjaxAllowed(url)) {
                        window.location.href = url;
                        return;
                    }

                    this.pjaxLoading = true;

                    try {
                        const response = await fetch(url, {
                            headers: {
                                'X-Requested-With': 'XMLHttpRequest',
                                'X-PJAX': 'true'
                            },
                            credentials: 'same-origin'
                        });

                        if (!response.ok) {
                            throw new Error('HTTP ' + response.status);
                        }

                        const html = await response.text();
                        const doc = new DOMParser().parseFromString(html, 'text/html');

                        const nextContainer = doc.querySelector('#pjax-container');
                        if (!nextContainer) {
                            window.location.href = url;
                            return;
                        }

                        const currentContainer = document.querySelector('#pjax-container');
                        if (!currentContainer) {
                            window.location.href = url;
                            return;
                        }

                        const nextNav = doc.querySelector('#sidebar-nav');
                        const currentNav = document.querySelector('#sidebar-nav');
                        if (nextNav && currentNav) {
                            currentNav.innerHTML = nextNav.innerHTML;
                            this.execScripts(currentNav);
                        }

                        currentContainer.innerHTML = nextContainer.innerHTML;
                        this.execScripts(currentContainer);

                        const nextTitle = doc.querySelector('title');
                        if (nextTitle) {
                            document.title = nextTitle.textContent || document.title;
                        }

                        if (push) {
                            window.history.pushState({}, '', url);
                        }

                        window.scrollTo({ top: 0, behavior: 'auto' });

                        if (window.Alpine && typeof window.Alpine.initTree === 'function') {
                            window.Alpine.initTree(currentContainer);
                            if (currentNav) {
                                window.Alpine.initTree(currentNav);
                            }
                        }

                        if (window.lucide?.createIcons) {
                            window.lucide.createIcons();
                        }
                    } catch (e) {
                        window.location.href = url;
                    } finally {
                        this.pjaxLoading = false;
                    }
                },
                handleNavClick(event) {
                    const a = event.target.closest('a');
                    if (!a) return;
                    if (this.pjaxLoading) return;
                    if (a.hasAttribute('data-no-pjax')) return;
                    if (a.target && a.target !== '_self') return;
                    if (event.metaKey || event.ctrlKey || event.shiftKey || event.altKey) return;

                    const href = a.getAttribute('href');
                    if (!href || href === '#') return;
                    if (href.startsWith('mailto:') || href.startsWith('tel:') || href.startsWith('javascript:')) return;

                    event.preventDefault();
                    this.open = false;
                    this.navigate(href);
                },
                handleContentClick(event) {
                    const a = event.target.closest('a');
                    if (!a) return;
                    if (this.pjaxLoading) return;
                    if (a.hasAttribute('data-no-pjax')) return;
                    if (a.target && a.target !== '_self') return;
                    if (event.metaKey || event.ctrlKey || event.shiftKey || event.altKey) return;

                    const href = a.getAttribute('href');
                    if (!href || href === '#') return;
                    if (href.startsWith('#')) return;
                    if (href.startsWith('mailto:') || href.startsWith('tel:') || href.startsWith('javascript:')) return;

                    event.preventDefault();
                    this.navigate(href);
                },
                handleContentSubmit(event) {
                    const form = event.target.closest('form');
                    if (!form) return;
                    if (this.pjaxLoading) return;
                    if (form.hasAttribute('data-no-pjax')) return;

                    const method = (form.getAttribute('method') || 'get').toLowerCase();
                    if (method !== 'get') {
                        return;
                    }

                    event.preventDefault();

                    const actionAttr = form.getAttribute('action') || '';
                    const action = actionAttr.trim() !== '' ? actionAttr : window.location.href;

                    const url = new URL(action, window.location.origin);
                    const params = new URLSearchParams(url.search);

                    const fd = new FormData(form);

                    // Si el formulario tiene múltiples submit buttons con name/value
                    // e.g. "Buscar" vs "Limpiar", incluir el que disparó el submit.
                    const submitter = event.submitter;
                    if (submitter && submitter.name) {
                        fd.append(submitter.name, submitter.value ?? '');
                    }

                    const map = new Map();
                    for (const [key, value] of fd.entries()) {
                        if (!map.has(key)) {
                            map.set(key, []);
                        }
                        map.get(key).push(value);
                    }

                    for (const [key, values] of map.entries()) {
                        params.delete(key);
                        values.forEach(v => params.append(key, v));
                    }

                    url.search = params.toString();
                    this.navigate(url.toString());
                },
                execScripts(rootEl) {
                    const scripts = Array.from(rootEl.querySelectorAll('script'));
                    scripts.forEach(oldScript => {
                        const newScript = document.createElement('script');

                        for (const attr of oldScript.attributes) {
                            newScript.setAttribute(attr.name, attr.value);
                        }

                        newScript.text = oldScript.text || '';
                        oldScript.parentNode.replaceChild(newScript, oldScript);
                    });
                }
            }
        }
    </script>

    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <!-- Lucide -->
    <script defer src="https://unpkg.com/lucide@latest"></script>

    <!-- Alpine.js x-cloak -->
    <style>
        [x-cloak] { display: none !important; }
    </style>
    
</head>

<body class="bg-slate-50 text-verde-sena" x-data="pjaxLayout()" x-init="init()">

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
            <nav id="sidebar-nav" class="mt-1 flex-1 space-y-0.5" @click="handleNavClick($event)">
                @php
                    $linkBase = 'nav-link flex items-center gap-2 px-2 py-1.5 rounded-md text-xs';
                @endphp

                <a href="{{ url('/admin') }}"
                    class="{{ $linkBase }} {{ request()->is('admin') ? 'bg-verde-sena text-white' : 'text-verde-sena hover:bg-slate-100' }}">
                    <i data-lucide="home" class="w-3.5 h-3.5"></i>
                    <span>Inicio</span>
                </a>

                <a href="/fichas"
                    class="{{ $linkBase }} {{ request()->is('fichas*') ? 'bg-verde-sena text-white' : 'text-verde-sena hover:bg-slate-100' }}">
                    <i data-lucide="layers" class="w-3.5 h-3.5"></i>
                    <span>Fichas</span>
                </a>

                <a href="/aprendices"
                    class="{{ $linkBase }} {{ request()->is('aprendices*') ? 'bg-verde-sena text-white' : 'text-verde-sena hover:bg-slate-100' }}">
                    <i data-lucide="users" class="w-3.5 h-3.5"></i>
                    <span>Aprendices</span>
                </a>

                <a href="#"
                    class="{{ $linkBase }} {{ request()->is('etapa-productiva*') ? 'bg-verde-sena text-white' : 'text-verde-sena hover:bg-slate-100' }}">
                    <i data-lucide="briefcase" class="w-3.5 h-3.5"></i>
                    <span>Etapa productiva</span>
                </a>

                <a href="/funcionarios"
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
                <div id="pjax-container" class="relative bg-white border border-slate-200 rounded-xl p-3 md:p-4 lg:p-6 shadow-sm overflow-hidden" :class="pjaxLoading ? 'opacity-60 pointer-events-none' : ''" @click="handleContentClick($event)" @submit="handleContentSubmit($event)">
                    <!-- PJAX overlay + loader -->
<div x-cloak x-show="pjaxLoading"
     class="absolute inset-0 z-20 bg-white/85 backdrop-blur-[1px]">

    <!-- Loader más grande y separado -->
    <div class="flex flex-col items-center justify-start
                pt-15 gap-3">
        
        <svg class="w-10 h-10 animate-spin text-verde-sena"
             xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
            <circle class="opacity-25" cx="12" cy="12" r="10"
                    stroke="currentColor" stroke-width="3"></circle>
            <path class="opacity-75" fill="currentColor"
                  d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
        </svg>

        <span class="text-sm font-semibold text-slate-700">
            Cargando información...
        </span>
    </div>
</div>


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