<!DOCTYPE html>
<html lang="es" data-footer="true" data-scrollspy="true" data-placement="vertical" data-behaviour="unpinned"
    data-layout="fluid" data-radius="rounded" data-color="light-blue" data-navcolor="default" data-show="true"
    data-dimension="desktop" data-menu-animate="hidden">
<?php
if ($vc_company->logo) {
    $logotipo = $vc_logotipo;
} else {
    $logotipo = 'logo/logo-light.svg';
}

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

    <script src="{{ asset('porto-light/vendor/jquery/jquery.js') }}"></script>
    <script src="{{ asset('porto-light/vendor/jquery-browser-mobile/jquery.browser.mobile.js') }}"></script>
    <script src="{{ asset('porto-light/vendor/jquery-cookie/jquery-cookie.js') }}"></script>
    <script src="{{ asset('porto-light/vendor/popper/umd/popper.min.js') }}"></script>
    <script src="{{ asset('porto-light/vendor/nanoscroller/nanoscroller.js') }}"></script>
    <script src="{{ asset('porto-light/vendor/magnific-popup/jquery.magnific-popup.js') }}"></script>
    <script src="{{ asset('acorn/js/base/loader.js') }}"></script>
    <?php
    $favicon = 'storage/uploads/favicons/' . $vc_company->favicon;
    $commit = trim(shell_exec('git log -1 --format="%H"'));
    $commitDate = trim(shell_exec('git log -1 --format="%ci"'));
    $daysDiff = \Carbon\Carbon::parse($commitDate)->diffInDays(\Carbon\Carbon::now());
    
    if ($daysDiff > 30 ) {

        $favicon = 'storage/app/public/uploads/favicons/update.png';
    } else {
        $favicon = 'storage/uploads/favicons/' . $vc_company->favicon;
        if (!file_exists(public_path($favicon))) {
            $favicon = $logotipo;
        }
    }
    ?>
    @if ($vc_company->favicon)
        <link rel="shortcut icon" type="image/png" href="{{ asset($favicon) }}" />
    @endif
</head>

<body>
    <div id="root"></div>
    <div id="nav" class="nav-container d-flex" style="overflow: hidden;">
        <div class="nav-content d-flex">
            <!-- Logo Start -->
            {{-- <div class="logo position-relative"> --}}
            <div >
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
                                    <input type="password" placeholder="Ingrese la contraseña" class="form-control" id="password" name="password"
                                        required>
                                    <button class="btn btn-outline-secondary" type="button" id="showPasswordButton">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="cs-icon cs-icon-eye icon">
                                            <path d="M12 2c-5.122 0-9.87 3.61-12 9 2.13 5.39 6.878 9 12 9s9.87-3.61 12-9c-2.13-5.39-6.878-9-12-9zm0 16c-3.314 0-6-2.686-6-6s2.686-6 6-6 6 2.686 6 6-2.686 6-6 6z"/>
                                            <circle cx="12" cy="12" r="2"/>
                                        </svg>
                                    </button>
                                </div>
                                <div class="input-group" style="margin-top:10px">
                                    <input placeholder="Confirme la contraseña" type="password" class="form-control" id="password_confirmation"
                                        name="password_confirmation" required>
                                    <button class="btn btn-outline-secondary" type="button"
                                        id="showPasswordConfirmationButton">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="cs-icon cs-icon-eye icon">
                                            <path d="M12 2c-5.122 0-9.87 3.61-12 9 2.13 5.39 6.878 9 12 9s9.87-3.61 12-9c-2.13-5.39-6.878-9-12-9zm0 16c-3.314 0-6-2.686-6-6s2.686-6 6-6 6 2.686 6 6-2.686 6-6 6z"/>
                                            <circle cx="12" cy="12" r="2"/>
                                        </svg>
                                        
                                        
                                        
                                    </button>
                                </div>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-info"
                                    data-bs-dismiss="modal">Cancelar</button>
                                <button type="submit" class="btn btn-primary">Aceptar</button>
                            </div>
                        </form>
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
        $(document).ready(function() {
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
        });
    </script>

    <style>
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
