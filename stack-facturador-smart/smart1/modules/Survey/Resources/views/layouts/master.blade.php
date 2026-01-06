    <!DOCTYPE html>
    <html lang="en">

    <head>
        <meta charset="utf-8">
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">
        <link rel="preconnect" href="https://fonts.gstatic.com" />
        <link href="https://fonts.googleapis.com/css2?family=Nunito+Sans:wght@300;400;600&display=swap"
            rel="stylesheet" />
        <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@300;400;700&display=swap"
            rel="stylesheet" />
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
        <title>
            @yield('title')
        </title>
        <?php
        if ($vc_company->logo) {
            $logotipo = $vc_logotipo;
        } else {
            $logotipo = 'logo/logo-light.svg';
        }
        
        ?>

        {{-- Laravel Mix - CSS File --}}
        {{-- <link rel="stylesheet" href="{{ mix('css/survey.css') }}"> --}}

    </head>

    <body>
        <div id="root"></div>

        <div class="container">
            <div class="row">
                <div class="col">
                    <div id="main-wrapper" style="width: 100%;">
                        @yield('content')
                    </div>
                </div>
            </div>
        </div>
    </body>
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

    </html>
