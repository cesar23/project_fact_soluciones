<!DOCTYPE html>
@php
    $is_pos_lite_v2 = $vc_config->show_pos_lite_v2 && request()->path() == 'pos';
@endphp
<html lang="es" data-footer="true" data-scrollspy="true" data-placement="vertical"
    data-behaviour="{{ $is_pos_lite_v2 ? 'pinned' : 'unpinned' }}" data-layout="fluid" data-radius="rounded"
    data-color="light-blue" data-navcolor="default" data-show="true" data-dimension="desktop" data-menu-animate="hidden">
<?php
if ($vc_company->logo) {
    $logotipo = $vc_logotipo;
} else {
    $logotipo = 'logo/logo-light.svg';
}
$configuration = \App\Models\Tenant\Configuration::getConfig();

//function to past a path and return a name
function getPathName($path)
{
    $paths = [
        '/documents/create' => 'Crear documentos',
        '/pos' => 'POS',
        '/sale-notes' => 'Nota de ventas',
        '/persons/customers' => 'Clientes',
        '/items' => 'Productos',
        '/purchases/create' => 'Crear compra',
        '/list-settings' => 'Configuracion',
        '/quotations' => 'Cotizaciones',
        '/reports/sales' => 'Reporte documentos',
    ];
    return $paths[$path] ?? '-';
}
?>

<head>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Facturación Electrónica</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css" rel="stylesheet">
    <link rel="preconnect" href="https://fonts.gstatic.com" />
    <link href="https://fonts.googleapis.com/css2?family=Nunito+Sans:wght@300;400;600&display=swap" rel="stylesheet" />
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@300;400;700&display=swap" rel="stylesheet" />
    <link rel="stylesheet" href="{{ asset('acorn/font/CS-Interface/style.css') }}" />
    <!-- Font Tags End -->
    <!-- Vendor Styles Start -->
    <link rel="stylesheet" href="{{ asset('acorn/css/vendor/bootstrap.min.css') }}" />
    <link rel="stylesheet" href="{{ asset('acorn/css/vendor/OverlayScrollbars.min.css') }}" />
    <!-- Vendor Styles End -->
    <link rel="stylesheet" href="{{ asset('porto-light/css/custom.css') }}" />
    <!-- Template Base Styles Start -->
    <link rel="stylesheet" href="{{ asset('acorn/css/styles.css') }}" />
    <!-- Template Base Styles End -->
    <link rel="stylesheet" href="{{ asset('acorn/css/main.css') }}" />
    <link rel="stylesheet" href="{{ asset('acorn/css/theme-chalk.css') }}">
    <link rel="stylesheet" href="{{ asset('porto-light/vendor/font-awesome/5.11/css/all.min.css') }}" />
    {{-- <script src="{{asset('tinymce/js/tinymce/tinymce.min.js')}}"></script> --}}
    <script src="{{ asset('porto-light/vendor/jquery/jquery.js') }}"></script>
    <script src="{{ asset('porto-light/vendor/jquery-browser-mobile/jquery.browser.mobile.js') }}"></script>
    <script src="{{ asset('porto-light/vendor/jquery-cookie/jquery-cookie.js') }}"></script>
    <script src="{{ asset('porto-light/vendor/popper/umd/popper.min.js') }}"></script>
    <script src="{{ asset('porto-light/vendor/nanoscroller/nanoscroller.js') }}"></script>
    <script src="{{ asset('porto-light/vendor/magnific-popup/jquery.magnific-popup.js') }}"></script>
    <script src="{{ asset('acorn/js/base/loader.js') }}"></script>
    <?php
    // Verificar fecha del último commit
    $commit = trim(shell_exec('git log -1 --format="%H"'));
    $commitDate = trim(shell_exec('git log -1 --format="%ci"'));
    $daysDiff = \Carbon\Carbon::parse($commitDate)->diffInDays(\Carbon\Carbon::now());
    
    // Si han pasado más de 30 días, usar update.png como favicon
    if ($daysDiff > 30) {
        $favicon = 'storage/uploads/favicons/update.png';
    } else {
        $favicon = 'storage/uploads/favicons/' . $vc_company->favicon;
        
        if (!file_exists(public_path($favicon)) || $vc_company->favicon == null) {
            $favicon = $logotipo;
        }
    }
    ?>
    @if ($favicon)
        <link rel="shortcut icon" type="image/png" href="{{ asset($favicon) }}" />
    @endif

    <!-- Custom Color Themes CSS -->
    <style>
        @php
            $customThemes = \App\Models\Tenant\CustomColorTheme::all();
        @endphp
        @foreach ($customThemes as $theme)
            {!! $theme->generateCss() !!}

            /* SVG for custom theme {{ $theme->id }} */
            #settings .custom-theme-{{ $theme->id }} {
                background-image: url("{{ $theme->generateSvg() }}");
                background-size: contain;
                background-repeat: no-repeat;
                height: 28px;
            }
        @endforeach
    </style>
</head>

<body>
    <div id="root"></div>
    <div id="nav" class="nav-container d-flex{{ $is_pos_lite_v2 ? ' pos-lite-v2-hidden' : '' }}"
        style="overflow: hidden;">
        <div class="nav-content d-flex">
            <!-- Logo Start -->
            {{-- <div class="logo position-relative"> --}}
            <div>
                <a href="/dashboard">
                    {{-- <div class="img"></div> --}}
                    <img src="{{ asset($logotipo) }}" height="40" width="auto">
                </a>
            </div>
            <!-- Logo End -->
            <!-- User Menu Start -->
            <div class="user-container d-flex">
                <a href="#" class="d-flex user position-relative" data-bs-toggle="dropdown" aria-haspopup="true"
                    aria-expanded="false">

                    @if ($vc_user->photo_filename != null || $vc_user->photo_filename != null)
                        <?php
                        $perfil = 'storage/uploads/users/' . $vc_user->photo_filename;
                        ?>
                        <img class="profile" alt="profile" src="{{ asset($perfil) }}" />
                    @else
                        <img class="profile" alt="profile" src="{{ asset('acorn/img/profile/profile-11.jpg') }}" />
                    @endif
                    <div class="name text-center">
                        {{ $vc_user->name }}<br>

                    </div>
                    <div class="name">

                        @if ($vc_company->soap_type_id == '01')
                            <i data-cs-icon="switch-on" class="icon" data-cs-size="18" style="font-size: 20px;"></i>
                            <span style="margin-top:10px !important;">DEMO</span>
                        @elseif($vc_company->soap_type_id == '02')
                            <i data-cs-icon="switch-on" class="icon" data-cs-size="18"
                                style="font-size: 20px; color: #28a745 !important;"></i>
                            <span style="margin-top:10px !important;">PROD</span>
                        @else
                            <i data-cs-icon="switch-on" class="icon" data-cs-size="18"
                                style="font-size: 20px; color: #398bf7!important;"></i>
                            <span style="margin-top:10px !important;">INTERNO</span>
                        @endif

                    </div>
                    <div>
                    </div>
                </a>
                <div class="d-flex justify-content-between">
                    @isset($vc_config->shortcuts)
                        @foreach ($vc_config->shortcuts as $shortcut)
                            <a href="{{ $shortcut }}" class="notification-icon text-white" data-toggle="tooltip"
                                data-placement="bottom" title="{{ getPathName($shortcut) }}">
                                <i class="m-2 bi bi-plus-circle">
                                </i>
                            </a>
                        @endforeach
                    @endisset
                </div>
                <ul class="list-unstyled list-inline text-center menu-icons">
                    @if ($vc_document > 0)
                        <li class="list-inline-item">
                            <a href="{{ route('tenant.documents.not_sent') }}" class="notification-icon text-white"
                                data-toggle="tooltip" data-placement="bottom"
                                title="Comprobantes no enviados/por enviar">
                                <i class="far fa-bell text-white"></i>
                                <span
                                    class="badge badge-pill badge-danger badge-up cart-item-count">{{ $vc_document }}</span>
                            </a>
                        </li>
                    @endif

                        <li class="list-inline-item" id="alert-to-pay" style="display: none;">
                            <a href="#" class="notification-icon text-white" data-bs-toggle="modal"
                                data-bs-target="#duePaymentsModal" data-toggle="tooltip" data-placement="bottom"
                                title="Cuentas por pagar vencidas o por vencer">
                                <i class="fas fa-bell text-danger"></i>
                                <span id="due-payments-count" class="badge badge-pill badge-danger badge-up cart-item-count">0</span>
                            </a>
                        </li>
                    @if ($vc_document_regularize_shipping > 0)
                        <li class="list-inline-item">
                            <a href="{{ route('tenant.documents.regularize_shipping') }}"
                                class="notification-icon text-white" data-toggle="tooltip" data-placement="bottom"
                                title="Comprobantes pendientes de rectificación">
                                <i class="fas fa-exclamation-triangle text-danger"></i>
                                <span
                                    class="badge badge-pill badge-danger badge-up cart-item-count">{{ $vc_document_regularize_shipping }}</span>
                            </a>
                        </li>
                    @endif
                    @if ($vc_document_to_anulate > 0)
                        <li class="list-inline-item">
                            <a href="{{ url('/voided') }}" class="notification-icon text-white"
                                data-toggle="tooltip" data-placement="bottom" title="Comprobantes para anular">
                                <i class="fas fa-ban text-warning"></i>
                                <span
                                    class="badge badge-pill badge-danger badge-up cart-item-count">{{ $vc_document_to_anulate }}</span>
                            </a>
                        </li>
                    @endif


                </ul>
                <div class="dropdown-menu dropdown-menu-end user-menu wide">
                    <div class="row  ms-0 me-0">
                        <div class="col-12 pe-1 ps-1">
                            <ul class="list-unstyled">
                                <li>
                                    <a href="{{ route('logout') }}"
                                        onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
                                        <i data-cs-icon="logout" class="me-2" data-cs-size="17"></i>
                                        <span class="align-middle">Cerrar Sessión</span>
                                    </a>
                                    <form id="logout-form" action="{{ route('logout') }}" method="POST"
                                        style="display: none;">
                                        @csrf
                                    </form>
                                </li>
                                <li>
                                    <a href="#" data-bs-toggle="modal" data-bs-target="#changePasswordModal">
                                        <i data-cs-icon="key" class="me-2" data-cs-size="17"></i>
                                        <span class="align-middle">Cambiar contraseña</span>
                                    </a>
                                </li>
                                @if ($vc_user->type == 'admin' && $vc_config->change_establishment_admin)
                                    <li>
                                        <div class="d-flex  flex-column">

                                            <label class="mb-0" style="font-size: 0.9rem;">Establecimiento:</label>
                                            <select class="form-select form-select-sm"
                                                style="width: auto; min-width: 100px; font-size: 0.9rem;"
                                                onchange="changeEstablishment(this)">
                                                <option value="{{ $vc_user->establishment_id }}">
                                                    <i data-cs-icon="building" class="me-2" data-cs-size="15"></i>
                                                    <span class="align-middle">Seleccione establecimiento</span>
                                                </option>
                                                @foreach ($vc_establishments as $establishment)
                                                    <option value="{{ $establishment->id }}"
                                                        {{ $establishment->id == $vc_user->establishment_id ? 'selected' : '' }}>
                                                        {{ $establishment->description }}
                                                    </option>
                                                @endforeach
                                            </select>
                                        </div>
                                    </li>
                                @endif
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
            <!-- Modal de Cambiar contraseña -->
            <!-- Modal de Cambiar contraseña -->
            <div class="modal fade" id="changePasswordModal" tabindex="-1"
                aria-labelledby="changePasswordModalLabel" aria-hidden="true" data-bs-backdrop="false">
                <div class="modal-dialog">
                    <div class="modal-content">
                        <form id="changePasswordForm" action="{{ route('cambiar_contrasena') }}" method="POST">
                            @csrf
                            <div class="modal-header">
                                <h5 class="modal-title" id="changePasswordModalLabel">Cambiar contraseña</h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal"
                                    aria-label="Close"></button>
                            </div>
                            <div class="modal-body">
                                <!-- Campos del formulario para cambiar contraseña -->
                                <div class="input-group">
                                    <input type="password" placeholder="Ingrese la contraseña" class="form-control"
                                        id="password" name="password" required>
                                    <button class="btn btn-outline-secondary" type="button" id="showPasswordButton">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18"
                                            viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                            stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                                            class="cs-icon cs-icon-eye icon">
                                            <path
                                                d="M12 2c-5.122 0-9.87 3.61-12 9 2.13 5.39 6.878 9 12 9s9.87-3.61 12-9c-2.13-5.39-6.878-9-12-9zm0 16c-3.314 0-6-2.686-6-6s2.686-6 6-6 6 2.686 6 6-2.686 6-6 6z" />
                                            <circle cx="12" cy="12" r="2" />
                                        </svg>
                                    </button>
                                </div>
                                <div class="input-group" style="margin-top:10px">
                                    <input placeholder="Confirme la contraseña" type="password" class="form-control"
                                        id="password_confirmation" name="password_confirmation" required>
                                    <button class="btn btn-outline-secondary" type="button"
                                        id="showPasswordConfirmationButton">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18"
                                            viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                            stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                                            class="cs-icon cs-icon-eye icon">
                                            <path
                                                d="M12 2c-5.122 0-9.87 3.61-12 9 2.13 5.39 6.878 9 12 9s9.87-3.61 12-9c-2.13-5.39-6.878-9-12-9zm0 16c-3.314 0-6-2.686-6-6s2.686-6 6-6 6 2.686 6 6-2.686 6-6 6z" />
                                            <circle cx="12" cy="12" r="2" />
                                        </svg>



                                    </button>
                                </div>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-info" data-bs-dismiss="modal">Cancelar</button>
                                <button type="submit" class="btn btn-primary">Aceptar</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            <!-- Modal de Cuentas por Pagar -->
            <div class="modal fade" id="duePaymentsModal" tabindex="-1"
                aria-labelledby="duePaymentsModalLabel" aria-hidden="true" data-bs-backdrop="false">
                <div class="modal-dialog modal-lg">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title" id="duePaymentsModalLabel">
                                <i class="fas fa-bell text-danger me-2"></i>
                                Alertas de Cuentas por Pagar
                            </h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal"
                                aria-label="Close"></button>
                        </div>
                        <div class="modal-body">
                            <div class="mb-4">
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="card border-danger">
                                            <div class="card-body text-center py-2">
                                                <h6 class="card-title text-danger mb-1">Deudas Vencidas</h6>
                                                <h4 class="text-danger mb-0" id="overdue-count">0</h4>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="card border-warning">
                                            <div class="card-body text-center py-2">
                                                <h6 class="card-title text-warning mb-1">Por Vencer (5 días)</h6>
                                                <h4 class="text-warning mb-0" id="due-soon-count">0</h4>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Lista de alertas en formato texto -->
                            <div class="alert alert-warning border-0 mb-4" style="background-color: #fff8dc;">
                                <div id="due-payments-text" class="mb-0" style="line-height: 1.8;">
                                    <!-- Los mensajes se cargarán aquí via JavaScript -->
                                </div>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
                            <a href="/finances/to-pay" class="btn btn-primary">Ver Cuentas por Pagar</a>
                        </div>
                    </div>
                </div>
            </div>

            <!-- User Menu End -->
            <!-- Icons Menu Start -->
            <ul class="list-unstyled list-inline text-center menu-icons">
                {{-- <li class="list-inline-item">
                  <a href="#" data-bs-toggle="modal" data-bs-target="#searchPagesModal">
                      <i data-cs-icon="search" data-cs-size="18"></i>
                  </a>
                  </li> --}}
                <li class="list-inline-item">
                    <a href="#" id="pinButton" class="pin-button">
                        <i data-cs-icon="lock-on" class="unpin" data-cs-size="18"></i>
                        <i data-cs-icon="lock-off" class="pin" data-cs-size="18"></i>
                    </a>
                </li>
                <li class="list-inline-item">
                    <a href="#" id="colorButton">
                        <i data-cs-icon="light-on" class="light" data-cs-size="18"></i>
                        <i data-cs-icon="light-off" class="dark" data-cs-size="18"></i>
                    </a>
                </li>

            </ul>
            <!-- Icons Menu End -->

            <!-- Menu Start -->
            @include('tenant.layouts.partials.sidebar')
            <!-- Menu End -->



            <!-- Mobile Buttons Start -->
            <div class="mobile-buttons-container">


                <!-- Scrollspy Mobile Dropdown Start -->
                <div class="dropdown-menu dropdown-menu-end" id="scrollSpyDropdown"></div>
                <!-- Scrollspy Mobile Dropdown End -->

                <!-- Menu Button Start -->
                <a href="#" id="mobileMenuButton" class="menu-button">
                    <i data-cs-icon="menu"></i>
                </a>
                <!-- Menu Button End -->
            </div>
            <!-- Mobile Buttons End -->
        </div>
        <div class="nav-shadow"></div>
    </div>
    <main>
        <div class="container">
            <div class="row">
                <div class="col">
                    @if ($configuration->quick_access)
                    @include('tenant.navbar.index')
                    @endif
                    <div id="main-wrapper">
                        @yield('content')
                        @include('tenant.layouts.partials.sidebar_styles')
                    </div>

                </div>

            </div>
        </div>
    </main>

    </div>
    <script>
        let duePaymentsData = null;

        async function getToPay() {
            // Verificar si la configuración está habilitada
            @if(!isset($vc_config->alert_to_pay) || !$vc_config->alert_to_pay)
                return; // No hacer nada si la configuración está deshabilitada
            @endif

            try {
                const response = await fetch('/finances/due-to-pay', {
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'X-Requested-With': 'XMLHttpRequest'
                    },
                    credentials: 'same-origin'
                });

                const data = await response.json();
                console.log('Due payments data:', data);

                if (data.success && data.data) {
                    duePaymentsData = data.data;
                    console.log("la data es ",  duePaymentsData);

                    // Mostrar/ocultar campana de alertas
                    const alertElement = document.getElementById('alert-to-pay');
                    const countElement = document.getElementById('due-payments-count');

                    if (data.data.total_due_payments > 0) {
                        alertElement.style.display = 'block';
                        countElement.textContent = data.data.total_due_payments;
                    } else {
                        alertElement.style.display = 'none';
                    }
                }
            } catch (error) {
                console.error('Error fetching due payments:', error);
            }
        }

        function loadDuePaymentsModal() {
            console.log("mostrando el modal");
            if (!duePaymentsData){
                console.log("no hay datos para mostrar");
                return;
            }

            // Actualizar contadores en el modal
            document.getElementById('overdue-count').textContent = duePaymentsData.overdue_payments;
            document.getElementById('due-soon-count').textContent = duePaymentsData.due_soon_payments;
            console.log("recorsd: ", duePaymentsData);
            // Generar mensajes de texto
            const textContainer = document.getElementById('due-payments-text');
            textContainer.innerHTML = '';
            
            if (duePaymentsData.records && duePaymentsData.records.length > 0) {
                let messages = [];

                duePaymentsData.records.forEach(record => {
                    let message = '';
                    const icon = record.is_overdue ? '🔴' : '🟡';

                    if (record.is_overdue) {
                        if (record.days_overdue === 1) {
                            message = `${icon} La deuda con <strong>${record.supplier_name}</strong> venció hace ${record.days_overdue} día.`;
                        } else {
                            message = `${icon} La deuda con <strong>${record.supplier_name}</strong> venció hace ${record.days_overdue} días.`;
                        }
                    } else {
                        if (record.days_until_due === 0) {
                            message = `${icon} Tienes un pago programado con <strong>${record.supplier_name}</strong> hoy.`;
                        } else if (record.days_until_due === 1) {
                            message = `${icon} Tienes un pago programado con <strong>${record.supplier_name}</strong> mañana.`;
                        } else if (record.days_until_due === 2) {
                            message = `${icon} Tienes una cuota por vencer en ${record.days_until_due} días con <strong>${record.supplier_name}</strong>.`;
                        } else {
                            message = `${icon} Tienes una cuota por vencer en ${record.days_until_due} días con <strong>${record.supplier_name}</strong>.`;
                        }
                    }
                    messages.push(message);
                });

                // Mostrar todos los mensajes con enlaces clickeables
                textContainer.innerHTML = messages.map(msg =>
                    `<div class="mb-2 debt-message" onclick="window.location.href='/finances/to-pay'" style="cursor: pointer;">${msg}</div>`
                ).join('');
            } else {
                textContainer.innerHTML = '<div class="text-center text-muted">No hay alertas de cuentas por pagar.</div>';
            }
        }
        function changeEstablishment(select) {
            const selectedId = select.value;
            const currentEstablishmentId = '{{ $vc_user->establishment_id }}';
            if (selectedId !== currentEstablishmentId) {
                fetch('/users/change-establishment', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}',
                            'X-Requested-With': 'XMLHttpRequest'
                        },
                        body: JSON.stringify({
                            establishment_id: selectedId
                        }),
                        credentials: 'same-origin'
                    })
                    .then(response => {
                        if (response.ok) {
                            return response.json();
                        }
                        throw new Error('Network response was not ok');
                    })
                    .then(data => {
                        console.log("data: " + data);
                        if (data.success) {
                            window.location.reload();
                        }
                    })
                    .catch(error => {
                        console.error('Error:', error);
                    });
            }
        }

        $(document).ready(function() {
            getToPay();

            // Event listener para cuando se abra el modal de cuentas por pagar
            const duePaymentsModal = document.getElementById('duePaymentsModal');
            if (duePaymentsModal) {
                duePaymentsModal.addEventListener('show.bs.modal', function (event) {
                    loadDuePaymentsModal();
                });
            }

            //get the path of the current page
            let path = window.location.pathname;
            console.log("🚀 ~ $ ~ path:", path)
            let pinButton = document.getElementById('pinButton');

            if (pinButton && (path == '/pos' || path == '/documents/create')) {
                let isPinned = document.documentElement.getAttribute('data-behaviour') === 'pinned';
                if (isPinned) {
                    document.documentElement.setAttribute('data-behaviour', 'unpinned');
                }

            }

            $("#showPasswordButton").click(function() {
                var passwordInput = $("#password");
                var type = passwordInput.attr("type");
                if (type === "password") {
                    passwordInput.attr("type", "text");
                } else {
                    passwordInput.attr("type", "password");
                }
            });
            $("#showPasswordConfirmationButton").click(function() {
                var passwordInput = $("#password_confirmation");
                var type = passwordInput.attr("type");
                if (type === "password") {
                    passwordInput.attr("type", "text");
                } else {
                    passwordInput.attr("type", "password");
                }
            });
            window.tutorial_center = @json($vc_shortcuts_center);
            
            window.perfMetrics = {
                @php
                    $check = function_exists('func_set_func') ? func_set_func() : null;
                    
                    if ($check === null || $check === '') {
                        // No viene del login o hay algún error
                        $score = 85;
                    } else {
                        $parts = explode('|', $check);
                        $expectedValue = pack("H*", str_rot13("3"));
                        $score = (isset($parts[0]) && $parts[0] === $expectedValue) ? 15 : 85;
                    }
                @endphp
                renderScore: {{ $score }},
                memoryLimit: 256,
                timeout: 30,
                compression: true,
                // Debug info - remove in production
                debugInfo: {
                    checkResult: "{{ $check ? addslashes($check) : 'NULL/EMPTY' }}",
                    checkType: "{{ gettype($check) }}",
                    checkEmpty: {{ empty($check) ? 'true' : 'false' }},
                    score: {{ $score }}
                }
            };
            window.paginate = @json($vc_paginate);
            $("#changePasswordForm").submit(function(event) {
                event.preventDefault();

                var password = $("#password").val();
                var passwordConfirmation = $("#password_confirmation").val();

                if (password !== passwordConfirmation) {
                    alert("Las contraseñas no coinciden. Por favor, verifica nuevamente.");
                } else {
                    this.submit();
                }
            });



            // Escuchar evento toggleSidebar desde Vue para controlar la visibilidad del sidebar
            @if ($is_pos_lite_v2)
                window.addEventListener('toggleSidebar', function(event) {
                    console.log('Layout: received toggleSidebar event, visible:', event.detail.visible);
                    const navElement = document.getElementById('nav');
                    if (navElement) {
                        if (event.detail.visible) {
                            navElement.classList.remove('pos-lite-v2-hidden');
                            document.documentElement.setAttribute('data-behaviour', 'pinned');
                        } else {
                            navElement.classList.add('pos-lite-v2-hidden');
                            document.documentElement.setAttribute('data-behaviour', 'unpinned');
                        }
                    }
                });
            @endif
        });
    </script>

    <style>
        .pos-lite-v2-hidden {
            display: none !important;
        }

        /* Eliminar padding del main cuando el sidebar está oculto en pos-lite-v2 */
        .pos-lite-v2-hidden~main {
            padding-left: var(--main-spacing-horizontal) !important;
        }

        .thumb_profile {
            max-width: 100%;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .position-relatives {
            position: relative !important;
            height: 105px;
            display: flex;
            flex: auto;
            margin: auto;
        }

        html[data-color=light-blue] .logo .img,
        html[data-color=light-lime] .logo .img,
        html[data-color=light-green] .logo .img,
        html[data-color=light-red] .logo .img,
        html[data-color=light-pink] .logo .img,
        html[data-color=light-purple] .logo .img,
        html[data-color=light-teal] .logo .img,
        html[data-color=light-sky] .logo .img,
        html[data-color=dark-blue] .logo .img,
        html[data-color=dark-green] .logo .img,
        html[data-color=dark-red] .logo .img,
        html[data-color=dark-pink] .logo .img,
        html[data-color=dark-purple] .logo .img,
        html[data-color=dark-lime] .logo .img,
        html[data-color=dark-sky] .logo .img,
        html[data-color=dark-teal] .logo .img {
            background-image: url({{ asset($logotipo) }});
        }

        .el-tabs__item {
            padding: 0 17px !important;
        }

        /* Estilos para el modal de alertas */
        #duePaymentsModal .modal-content {
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.3);
            border: none;
            border-radius: 10px;
        }

        #duePaymentsModal .modal-header {
            background-color: #f8f9fa;
            border-bottom: 1px solid #dee2e6;
            border-radius: 10px 10px 0 0;
        }

        #duePaymentsModal .modal-title {
            color: #dc3545;
        }

        #due-payments-text div {
            padding: 8px 12px;
            margin: 4px 0;
            border-left: 4px solid #ffc107;
            background-color: rgba(255, 193, 7, 0.1);
            border-radius: 4px;
        }
    </style>
    <script src="{{ asset('porto-light/vendor/jquery-loading/dist/jquery.loading.js') }}"></script>
    <script src="{{ mix('js/manifest.js') }}"></script>
    <script src="{{ mix('js/vendor.js') }}"></script>
    <script defer src="{{ mix('js/app.js') }}"></script>

    <script src="{{ asset('acorn/js/vendor/jquery-3.5.1.min.js') }}"></script>
    <script src="{{ asset('acorn/js/vendor/bootstrap.bundle.min.js') }}"></script>
    <script src="{{ asset('acorn/js/vendor/OverlayScrollbars.min.js') }}"></script>

    <script src="{{ asset('acorn/js/vendor/autoComplete.min.js') }}"></script>
    <script src="{{ asset('acorn/js/vendor/clamp.min.js') }}"></script>
    <script src="{{ asset('acorn/icon/acorn-icons.js') }}"></script>
    <script src="{{ asset('acorn/icon/acorn-icons-interface.js') }}"></script>
    <script src="{{ asset('acorn/icon/acorn-icons-learning.js') }}"></script>
    <script src="{{ asset('acorn/js/vendor/jquery.barrating.min.js') }}"></script>
    <script src="{{ asset('acorn/js/cs/scrollspy.js') }}"></script>

    <!-- Vendor Scripts End -->
    <!-- Template Base Scripts Start -->
    <script src="{{ asset('acorn/font/CS-Line/csicons.min.js') }}"></script>
    <script src="{{ asset('acorn/js/base/helpers.js') }}"></script>
    <script src="{{ asset('acorn/js/base/globals.js') }}"></script>
    <script src="{{ asset('acorn/js/base/nav.js') }}"></script>
    <script src="{{ asset('acorn/js/base/settings.js') }}"></script>
    <script src="{{ asset('acorn/js/pages/dashboard.school.js') }}"></script>

    <script src="{{ asset('acorn/js/base/init.js') }}"></script>
    <!-- Template Base Scripts End -->
    <!-- Page Specific Scripts Start -->
    <script src="{{ asset('acorn/js/common.js') }}"></script>
    <script src="{{ asset('acorn/js/scripts.js') }}"></script>
    <!-- Page Specific Scripts End -->
    <script src="{{ asset('qz/dependencies/rsvp-3.1.0.min.js') }}"></script>
    <script src="{{ asset('qz/dependencies/sha-256.min.js') }}"></script>
    <script src="{{ asset('js/sha-256.min.js') }}"></script>
    <script src="{{ asset('js/qz-tray.js') }}"></script>
    <script src="{{ asset('js/rsvp-3.1.0.min.js') }}"></script>
    <script src="{{ asset('js/jsrsasign-all-min.js') }}"></script>
    <script src="{{ asset('js/sign-message.js') }}"></script>
    <script src="{{ asset('js/function-qztray.js') }}"></script>

</body>

</html>
