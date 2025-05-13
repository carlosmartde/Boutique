<!-- resources/views/layouts/app.blade.php -->
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>MINI-MARKET - @yield('title')</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css">
    <!-- SweetAlert2 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css" rel="stylesheet">
    <!-- SweetAlert2 JS -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    
    <!-- Estilos principales -->
    <style>
        :root {
            /* Nueva paleta de colores moderna */
            --primary-color: #3a86ff;
            --primary-dark: #2667cc;
            --secondary-color: #8338ec;
            --accent-color: #ff006e;
            --light-color: #f8f9fa;
            --dark-color: #212529;
            --background-color: #f0f2f5;
            --card-background: #ffffff;
            --sidebar-bg: #212529;
            --sidebar-text: #f8f9fa;
            --sidebar-hover: #FFAFCC;   /*Cambiar este color si no convence*/
            --sidebar-active: #ff006e;
        }

        body {
            display: flex;
            flex-direction: column;
            min-height: 100vh;
            background-color: var(--background-color);
            padding-top: 56px; /* Altura de la navbar */
            margin-bottom: 44px; /* Altura del footer */
        }

        .content-wrapper {
            display: flex;
            flex: 1;
            position: relative;
            min-height: calc(100vh - 56px - 44px); 
            padding-bottom: 44px; /* Espacio para el footer */
        }

        .sidebar {
            width: 250px;
            background: linear-gradient(135deg, var(--primary-color), var(--secondary-color)) !important;
            box-shadow: 0 0 15px rgba(0, 0, 0, 0.1);
            position: fixed;
            top: 56px;
            bottom: 0;
            left: 0;
            z-index: 100;
            padding: 20px 0;
            overflow-y: auto;
            height: calc(100vh - 5px - 44px); /* Altura ajustada para dejar espacio al footer */
        }

        .sidebar .nav-link {
            color: var(--sidebar-text);
            padding: 0.75rem 1.25rem;
            border-radius: 5px;
            margin: 0.25rem 0.75rem;
            transition: all 0.3s ease;
        }

        .sidebar .nav-link:hover {
            background-color: var(--sidebar-hover);
            color: white;
            transform: translateX(5px);
        }

        .sidebar .nav-link.active {
            background-color: var(--sidebar-active);
            color: white;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.2);
        }

        .sidebar .nav-link i {
            margin-right: 0.75rem;
            width: 20px;
            text-align: center;
        }

        .sidebar-header {
            padding: 0.75rem 1.25rem;
            color: var(--sidebar-text);
            text-decoration: none;
            display: block;
            font-weight: bold;
            margin-bottom: 1rem;
            transition: all 0.3s ease;
        }

        .sidebar-header:hover {
            color: var(--sidebar-hover);
            transform: translateX(5px);
        }

        .main-content {
            margin-left: 250px;
            width: calc(100% - 250px);
            padding: 20px;
            transition: margin-left 0.3s;
            min-height: 100%;
        }

        /* Estilos cuando el contenido debe ocupar toda la pantalla */
        .main-content.w-100 {
            margin-left: 0;
            width: 100%;
        }

        footer {
            height: 44px; /* Altura fija para el footer */
            background-color: var(--dark-color) !important;
            color: var(--light-color) !important;
            width: 100%;
            z-index: 101; /* Asegura que esté por encima de otros elementos */
            display: flex;
            align-items: center;
            justify-content: center;
            position: fixed;
            bottom: 0;
            left: 0;
            z-index: 1030;
        }

        /* Estilos para las cards */
        .card {
            border: none;
            border-radius: 10px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
            transition: transform 0.3s ease, box-shadow 0.3s ease;
            background-color: var(--card-background);
            overflow: hidden;
        }

        .card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 20px rgba(0, 0, 0, 0.1);
        }

        /* Estilos para los botones */
        .btn-primary {
            background-color: var(--primary-color);
            border-color: var(--primary-color);
        }

        .btn-primary:hover {
            background-color: var(--primary-dark);
            border-color: var(--primary-dark);
        }

        /* Estilos para la navbar */
        .navbar-dark {
            background: linear-gradient(135deg, var(--primary-color), var(--secondary-color)) !important;
        }

        .navbar-brand {
            font-weight: bold;
            letter-spacing: 1px;
        }

        /* Estilos responsivos */
        @media (max-width: 768px) {
            .sidebar {
                margin-left: -250px;
                transition: margin-left 0.3s;
            }

            .sidebar.show {
                margin-left: 0;
            }

            .main-content {
                margin-left: 0;
                width: 100%;
            }

            .main-content.sidebar-open {
                margin-left: 250px;
                width: calc(100% - 250px);
            }
        }
    </style>
    
    <!-- Estilos del select personalizado -->
    <style>
        /* Estilos para el select personalizado */
        .custom-select-wrapper {
            position: relative;
            width: 100%;
        }

        .custom-select-trigger {
            border: 1px solid #ced4da;
            padding: 0.375rem 0.75rem;
            border-radius: 0.25rem;
            background-color: white;
            width: 100%;
            display: flex;
            justify-content: space-between;
            align-items: center;
            cursor: pointer;
        }

        .custom-select-options {
            position: absolute;
            top: 100%;
            left: 0;
            right: 0;
            z-index: 99999;
            background: white;
            border: 1px solid #ddd;
            border-radius: 0.25rem;
            max-height: 250px;
            overflow-y: auto;
            display: none;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.2);
        }

        .custom-select-options.show {
            display: block;
        }

        .custom-select-option {
            padding: 0.5rem 0.75rem;
            cursor: pointer;
        }

        .custom-select-option:hover {
            background-color: #f8f9fa;
        }

        .custom-select-option.selected {
            background-color: #e9ecef;
        }
    </style>
    
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
    @yield('styles')
</head>

<body>
    @php
        $isWelcomePage = request()->route()->getName() === 'welcome' || request()->path() === '/' || request()->route()->getName() === 'login';
    @endphp

    <nav class="navbar navbar-expand-lg navbar-dark fixed-top">
        <div class="container-fluid">
            <button class="navbar-toggler" type="button" id="sidebarToggle" style="position: absolute; left: 15px;">
                <span class="navbar-toggler-icon"></span>
            </button>
            <a class="navbar-brand mx-auto" href="/dashboard">MINI-MARKET</a>
        </div>
    </nav>

    <div class="content-wrapper">
        @auth
            @if(!$isWelcomePage)
                <div class="sidebar">
                    <a href="/dashboard" class="sidebar-header">
                        <i class="bi bi-house-door"></i> Menu Principal
                    </a>
                    <ul class="nav flex-column">
                        @if(Auth::user()->rol === 'admin')
                            <li class="nav-item">
                                <a class="nav-link {{ request()->routeIs('products.create') ? 'active' : '' }}"
                                    href="{{ route('products.create') }}">
                                    <i class="bi bi-bag-plus"></i> Nuevo Producto
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link {{ request()->routeIs('sales.create') ? 'active' : '' }}"
                                    href="{{ route('sales.create') }}">
                                    <i class="bi bi-cart-plus"></i> Ventas
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link {{ request()->routeIs('inventory.index') ? 'active' : '' }}"
                                    href="{{ route('inventory.index') }}">
                                    <i class="bi bi-box"></i> Inventario
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link {{ request()->routeIs('inventario.mostrar-formulario') ? 'active' : '' }}"
                                    href="{{ route('inventario.mostrar-formulario') }}">
                                    <i class="bi bi-plus-square"></i> Registra Compra
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link {{ request()->routeIs('reports.index') ? 'active' : '' }}"
                                    href="{{ route('reports.index') }}">
                                    <i class="bi bi-file-earmark-bar-graph"></i> Reportes De Ventas
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link {{ request()->routeIs('users.management') ? 'active' : '' }}"
                                    href="{{ route('users.management') }}">
                                    <i class="bi bi-people"></i> Gestión de Usuarios
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link {{ request()->routeIs('register') ? 'active' : '' }}"
                                    href="{{ route('register') }}">
                                    <i class="bi bi-person-plus"></i> Crear Usuario
                                </a>
                            </li>
                        @endif
                        <li class="nav-item mt-3">
                            <form action="{{ route('logout') }}" method="POST" class="px-3">
                                @csrf
                                <button type="submit" class="btn btn-outline-danger w-100" style="border-width: 2px; color: white;">
                                    <i class="bi bi-box-arrow-right"></i> Cerrar Sesión
                                </button>                                                             
                            </form>
                        </li>
                    </ul>
                </div>
            @endif
        @endauth

        <main class="main-content {{ $isWelcomePage ? 'w-100 m-0' : '' }}">
            @yield('content')
        </main>
    </div>

    <footer class="text-center py-3">
        <p class="m-0">MINI-MARKET &copy; {{ date('Y') }}</p>
    </footer>

    <!-- Scripts -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        // Toggle sidebar script
        document.addEventListener('DOMContentLoaded', function () {
            const sidebarToggle = document.getElementById('sidebarToggle');
            const sidebar = document.querySelector('.sidebar');
            const mainContent = document.querySelector('.main-content');

            if (sidebarToggle && sidebar) {
                sidebarToggle.addEventListener('click', function () {
                    sidebar.classList.toggle('show');
                    mainContent.classList.toggle('sidebar-open');
                });
            }
        });
    </script>
    
    <script>
        // Select personalizado script
        document.addEventListener('DOMContentLoaded', function () {
            // Reemplazar los selects problemáticos
            document.querySelectorAll('.form-select').forEach(select => {
                // Solo reemplazar si no está ya procesado
                if (select.dataset.processed) return;
                select.dataset.processed = 'true';

                // Crear wrapper
                const wrapper = document.createElement('div');
                wrapper.className = 'custom-select-wrapper';
                select.parentNode.insertBefore(wrapper, select);

                // Crear trigger (el elemento visible)
                const trigger = document.createElement('div');
                trigger.className = 'custom-select-trigger';
                trigger.innerHTML = `
                    <span>${select.options[select.selectedIndex]?.text || 'Seleccionar'}</span>
                    <i class="bi bi-chevron-down"></i>
                `;
                wrapper.appendChild(trigger);

                // Crear options container (el dropdown)
                const options = document.createElement('div');
                options.className = 'custom-select-options';
                wrapper.appendChild(options);

                // Mover al final del body para asegurar que esté por encima de todo
                document.body.appendChild(options);

                // Poblar opciones
                Array.from(select.options).forEach((option, index) => {
                    const opt = document.createElement('div');
                    opt.className = 'custom-select-option';
                    if (index === select.selectedIndex) opt.classList.add('selected');
                    opt.textContent = option.text;
                    opt.dataset.value = option.value;
                    options.appendChild(opt);

                    // Click en una opción
                    opt.addEventListener('click', () => {
                        select.value = option.value;
                        trigger.querySelector('span').textContent = option.text;
                        options.querySelectorAll('.custom-select-option').forEach(o => {
                            o.classList.remove('selected');
                        });
                        opt.classList.add('selected');
                        options.classList.remove('show');

                        // Disparar evento change en el select original
                        select.dispatchEvent(new Event('change', { bubbles: true }));
                    });
                });

                // Toggle dropdown
                trigger.addEventListener('click', (e) => {
                    e.stopPropagation();
                    const isOpen = options.classList.contains('show');

                    // Cerrar todos los otros dropdowns
                    document.querySelectorAll('.custom-select-options').forEach(o => {
                        o.classList.remove('show');
                    });

                    if (!isOpen) {
                        options.classList.add('show');

                        // Posicionar el dropdown correctamente
                        const rect = trigger.getBoundingClientRect();
                        options.style.width = rect.width + 'px';
                        options.style.left = rect.left + 'px';
                        options.style.top = (rect.bottom + window.scrollY) + 'px';
                    }
                });

                // Cerrar dropdown cuando se hace clic fuera
                document.addEventListener('click', () => {
                    options.classList.remove('show');
                });

                // Ocultar el select original
                select.style.display = 'none';
                wrapper.appendChild(select);
            });
        });
    </script>

    <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
    <script src="https://cdn.jsdelivr.net/npm/flatpickr/dist/l10n/es.js"></script>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            flatpickr('.datepicker', {
                locale: 'es',
                dateFormat: 'Y-m-d',
            });
        });
    </script>

    <script src="{{ asset('js/user-management.js') }}"></script>
    @yield('scripts')
</body>
</html>