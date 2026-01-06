<!DOCTYPE html>
<html lang="en">
    <?php
   use App\Models\System\Configuration;
   $configuration = Configuration::first();
   
    if($configuration->bg_image!=null) { 
      $bg_default = asset('storage/uploads/logos/'. $configuration->bg_image);
   
    }else {
        $bg_default = asset('images/fondo-5.jpg');
    }
    ?>
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1" />
    <title>Facturación Electrónica</title>
    <meta name="description" content="Reset Password Page" />
    <link rel="icon" type="image/x-icon" href="{{ asset('/images/fondos/seguridad.png') }}">

    <link rel="preconnect" href="https://fonts.gstatic.com" />
    <link href="https://fonts.googleapis.com/css2?family=Nunito+Sans:wght@300;400;600&display=swap" rel="stylesheet" />
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@300;400;700&display=swap" rel="stylesheet" />
    <link rel="stylesheet" href="{{ asset('acorn/font/CS-Interface/style.css') }}" />
    <link rel="stylesheet" href="{{ asset('acorn/css/vendor/bootstrap.min.css') }}" />
    <link rel="stylesheet" href="{{ asset('acorn/css/vendor/OverlayScrollbars.min.css') }}" />
    <link rel="stylesheet" href="{{ asset('acorn/css/styles.css') }}" />
    <link rel="stylesheet" href="{{ asset('acorn/css/main.css') }}" />
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <script src="{{ asset('acorn/js/base/loader.js') }}"></script>
    <style>
        .sh-11 {
            justify-content: center;
            align-items: center;
            display: flex;
        }
 
        .fixed-background {
            background: url({{$bg_default}}) no-repeat center center fixed;
            background-size: cover;
            width: 100%;
            height: 100%;
            position: fixed;
            top: 0;
            right: 0;
            bottom: 0;
            left: 0;
        }
    </style>
</head>
 

<body class="h-100">
    @yield('content')
    

    <!-- Vendor Scripts Start -->
    <script src="{{ asset('acorn/js/vendor/jquery-3.5.1.min.js') }}"></script>
    <script src="{{ asset('acorn/js/vendor/bootstrap.bundle.min.js') }}"></script>
    <script src="{{ asset('acorn/js/vendor/OverlayScrollbars.min.js') }}"></script>
    <script src="{{ asset('acorn/js/vendor/autoComplete.min.js') }}"></script>
    <script src="{{ asset('acorn/js/vendor/clamp.min.js') }}"></script>
    <script src="{{ asset('acorn/js/vendor/jquery.validate/jquery.validate.min.js') }}"></script>
    <script src="{{ asset('acorn/js/vendor/jquery.validate/additional-methods.min.js') }}"></script>
    <!-- Vendor Scripts End -->

    <!-- Template Base Scripts Start -->
    <script src="{{ asset('acorn/font/CS-Line/csicons.min.js') }}"></script>
    <script src="{{ asset('acorn/js/base/helpers.js') }}"></script>
    <script src="{{ asset('acorn/js/base/globals.js') }}"></script>
    <script src="{{ asset('acorn/js/base/nav.js') }}"></script>
    <script src="{{ asset('acorn/js/base/search.js') }}"></script>
    <script src="{{ asset('acorn/js/base/settings.js') }}"></script>
    <script src="{{ asset('acorn/js/base/init.js') }}"></script>
    <!-- Template Base Scripts End -->
    <!-- Page Specific Scripts Start -->
    <script src="{{ asset('acorn/js/common.js') }}"></script>
    <script src="{{ asset('acorn/js/scripts.js') }}"></script>
    <!-- Page Specific Scripts End -->
</body>

</html>