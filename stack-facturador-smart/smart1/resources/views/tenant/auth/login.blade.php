@extends('tenant.layouts.auth')
@php
    $client = App\Models\System\Client::whereHas('hostname', function($query) {
        $query->where('fqdn', request()->getHost());
    })->first();
    $show_eyes_in_login = $client->show_eyes_in_login ?? false;
@endphp   
@section('content')
    <div id="root" class="h-100">
        <!-- Background Start -->
        <div class="fixed-background"></div>
        <!-- Background End -->
        <div class="container-fluid p-0 h-100 position-relative">
            <div class="row g-0 h-100">
                <!-- Left Side Start -->
                <?php
                $text_style = $vc_config->view_tutorials == true ? 'text-dark' : 'text-white';
                ?>
                @if ($vc_videos != null && $vc_config->view_tutorials)
                    <div class="offset-0 col-12 d-none d-lg-flex offset-md-1 col-lg h-lg-100">
                        <div class="d-flex align-items-center w-100">
                            <div class="w-100">

                                <p class="h6 {{ $text_style }}">
                                <section class="scroll-section mx-auto" id="youtube">
                                    <div class="row">
                                        <div class="col-12 col-md-12 col-xxl-7 mx-auto">
                                            <div class="card bg-transparent">
                                                <div class="plyr__video-embed player">
                                                    <?php
                                                    $video_url = $vc_videos->link . '?origin=https://plyr.io&amp;iv_load_policy=3&amp;modestbranding=1&amp;playsinline=1&amp;showinfo=0&amp;rel=0&amp;enablejsapi=1';
                                                    ?>
                                                    <iframe src="{{ $video_url }}" allowfullscreen
                                                        allow="autoplay"></iframe>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </section>
                                </p>
                                @if ($vc_shortcuts_left != null && $vc_config->view_tutorials)
                                    <div class="row justify-content-md-center">
                                        @foreach ($vc_shortcuts_left as $data)
                                            <div class="col-md-3">

                                                <a href="{{ $data->link }}" target="_blank">
                                                    <div class="card p-0">
                                                        <div
                                                            class="card-body text-center align-items-center d-flex flex-column justify-content-between ">
                                                            <div
                                                                class="d-flex rounded-xl bg-gradient-light sw-6 sh-6 justify-content-center align-items-center">
                                                                <?php
                                                                $image_logo = asset('storage' . DIRECTORY_SEPARATOR . 'uploads' . DIRECTORY_SEPARATOR . 'shortcuts' . DIRECTORY_SEPARATOR . $data->image);
                                                                ?>
                                                                <img src="{{ $image_logo }}"
                                                                    class="sw-7 sh-7 me-1 mb-1 d-inline-block bg-separator d-flex d-flex justify-content-center rounded-xl d-flex justify-content-center"
                                                                    alt="thumb" />
                                                            </div>
                                                            <p
                                                                class="card-text d-flex {{ $text_style }} font-weight-bold">
                                                                {{ strtoupper($data->title) }}
                                                            </p>
                                                        </div>
                                                    </div>
                                                </a>
                                            </div>
                                        @endforeach
                                    </div>
                                @endif


                            </div>
                        </div>
                    </div>
                    <!-- Left Side End -->
                @endif
                <!-- Right Side Start -->
                <div class="col-12 col-lg-auto h-100 pb-4 px-4 pt-0 p-lg-0">
                    <?php
                    $text_style = $vc_config->view_tutorials == true ? 'text-dark' : 'text-white';
                    $bg_foreground = $vc_config->view_tutorials == true ? 'bg-foreground' : '';
                    ?>
                    {{-- <div style="padding:150px;" class=" sw-lg-70 min-h-100 d-flex justify-content-center align-items-center  py-5 full-page-content-right-border {{$bg_foreground}}"> --}}
                    <div
                        class=" sw-lg-70 min-h-100 d-flex justify-content-center align-items-center  py-5 full-page-content-right-border {{ $bg_foreground }}">
                        <div class="sw-lg-50 px-5">
                            <div class="sh-11">
                                <a href="javascript:void(0)">
                                    <?php
                                    $logo = $company->logo != null ? "storage/uploads/logos/{$company->logo}" : 'logo/logo-blue-light.png';
                                    ?>
                                    <img src="{{ $logo }}" height="70px">
                                </a>
                            </div>
                            <div class="text-center">
                                <h1 class="auth__title {{ $text_style }}">Bienvenido a<br>{{ $company->trade_name }}</h1>
                                <p class="{{ $text_style }}">Ingresa a tu cuenta</p>
                            </div>
                            <div class="bg-card-login">


                                <div>
                                    <form id="resetForm" class="tooltip-end-bottom text-end" method="POST"
                                        action="{{ route('login') }}">
                                        @csrf
                                        <div class="mb-3 filled">
                                            <i data-cs-icon="email"></i>
                                            <input id="email" type="email" placeholder="Correo Electronico"
                                                name="email" class="form-control" value="{{ old('email') }}">
                                            @if ($errors->has('email'))
                                                <label class="error text-danger">
                                                    <strong>{{ $errors->first('email') }}</strong>
                                                </label>
                                            @endif
                                        </div>
                                        <div class="mb-3 filled position-relative">
                                            <i data-cs-icon="lock"></i>
                                            <input name="password" type="password" placeholder="Contraseña"
                                                class="form-control" id="password">
                                            @if ($show_eyes_in_login)
                                                <i class="bi bi-eye toggle-password position-absolute" id="togglePassword"
                                                style="right: 15px; top: 50%; transform: translateY(-50%); cursor: pointer; color: #666; font-size: 1.2em;"></i>
                                            @endif
                                            @if ($errors->has('password'))
                                                <label class="error">
                                                    <strong>{{ $errors->first('password') }}</strong>
                                                </label>
                                            @endif
                                        </div>
                                        <button type="submit" class="btn btn-lg btn-primary">Ingresar</button>
                                    </form>
                                </div>
                                @if ($vc_shortcuts_right != null && $vc_config->view_tutorials)
                                    <div class="mt-3 filled w-100 text-center">

                                        <div class="row justify-content-md-center">
                                            @foreach ($vc_shortcuts_right as $data)
                                                <div class="col-md-4">
                                                    <a href="{{ $data->link }}" target="_blank">
                                                        <div class="card bg-transparent">
                                                            <div
                                                                class="card-body text-center align-items-center d-flex flex-column justify-content-between ">
                                                                <div
                                                                    class="d-flex rounded-xl bg-gradient-light sw-6 sh-6 mb-3 justify-content-center align-items-center">
                                                                    <?php
                                                                    $image_logo = asset('storage' . DIRECTORY_SEPARATOR . 'uploads' . DIRECTORY_SEPARATOR . 'shortcuts' . DIRECTORY_SEPARATOR . $data->image);
                                                                    ?>
                                                                    <img src="{{ $image_logo }}"
                                                                        class="sw-7 sh-7 me-1 mb-1 d-inline-block bg-separator d-flex d-flex justify-content-center rounded-xl d-flex justify-content-center"
                                                                        alt="thumb" />
                                                                </div>
                                                                <p
                                                                    class="card-text mb-2 d-flex {{ $text_style }} font-weight-bold">
                                                                    {{ strtoupper($data->title) }}
                                                                </p>
                                                            </div>
                                                        </div>
                                                    </a>
                                                </div>
                                            @endforeach
                                        </div>
                                    </div>
                                @endif
                            </div>

                        </div>
                    </div>
                </div>

                <!-- Right Side End -->
            </div>
            <div class="position-absolute bottom-0 end-0 p-3">
                @php
                    $commit = trim(shell_exec('git log -1 --format="%H"'));
                    $commitDate = trim(shell_exec('git log -1 --format="%ci"'));
                    $daysDiff = \Carbon\Carbon::parse($commitDate)->diffInDays(\Carbon\Carbon::now());
                @endphp
                @if ($daysDiff > 30)
                    <p class="text-danger mb-0" style="font-size: 12px; font-weight: bolder;">Actualice su sistema</p>
                @endif
                <p class="text-muted small mb-0">{{ substr($commit, 0, 7) }}</p>
                <p class="text-muted small mb-0">{{ \Carbon\Carbon::parse($commitDate)->format('d/m/Y H:i:s') }}</p>
            </div>
        </div>
    </div>

@section('scripts')
    <script>
        // Función simplificada para mostrar/ocultar contraseña
        function togglePasswordVisibility() {
            const password = document.getElementById('password');
            const togglePassword = document.getElementById('togglePassword');

            if (!password || !togglePassword) {
                console.log('Elementos no encontrados');
                return;
            }

            // Toggle the type attribute
            const type = password.getAttribute('type') === 'password' ? 'text' : 'password';
            password.setAttribute('type', type);

            // Toggle the icon
            if (type === 'password') {
                togglePassword.classList.remove('bi-eye-slash');
                togglePassword.classList.add('bi-eye');
            } else {
                togglePassword.classList.remove('bi-eye');
                togglePassword.classList.add('bi-eye-slash');
            }
        }

        // Inicializar cuando el DOM esté listo
        document.addEventListener('DOMContentLoaded', function() {
            console.log('DOMContentLoaded ejecutado');

            const togglePassword = document.getElementById('togglePassword');
            const password = document.getElementById('password');

            if (!togglePassword || !password) {
                console.log('Elementos no encontrados en DOMContentLoaded');
                return;
            }

            console.log('Elementos encontrados correctamente');

            // Inicializar el ícono
            if (password.getAttribute('type') === 'text') {
                togglePassword.classList.remove('bi-eye');
                togglePassword.classList.add('bi-eye-slash');
            } else {
                togglePassword.classList.remove('bi-eye-slash');
                togglePassword.classList.add('bi-eye');
            }

            // Agregar event listener
            togglePassword.addEventListener('click', togglePasswordVisibility);

            // También escuchar cambios en el campo por si el navegador autocompleta
            password.addEventListener('input', function() {
                if (password.getAttribute('type') === 'text') {
                    togglePassword.classList.remove('bi-eye');
                    togglePassword.classList.add('bi-eye-slash');
                } else {
                    togglePassword.classList.remove('bi-eye-slash');
                    togglePassword.classList.add('bi-eye');
                }
            });
        });
    </script>
@endsection
@endsection
