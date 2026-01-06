<!DOCTYPE html>
<html lang="en">
<?php
if($company->bg_default!=null) { 
 // $logo =  asset('storage/uploads/logos/'. $company->logo);
  $bg_default = asset('storage/uploads/logos/'. $company->bg_default);
  
}else {
//   $logo = asset("logo/logo-light.svg");
   $bg_default = asset('images/fondo-5.jpg');
}
if($company->logo!=null) { 
   $logo = asset('storage/uploads/logos/'. $company->logo);
  
}else {
    $logo = asset('logo/logo-blue-light.png');
}
?>
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1" />
    <title>Facturación Electrónica</title>
    <meta name="description" content="Reset Password Page" />
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.3.0/font/bootstrap-icons.css">
    <link rel="preconnect" href="https://fonts.gstatic.com" />
    <link href="https://fonts.googleapis.com/css2?family=Nunito+Sans:wght@300;400;700&display=swap" rel="stylesheet" />
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@300;400;700&display=swap" rel="stylesheet" />
    <link rel="stylesheet" href="{{ asset('acorn/font/CS-Interface/style.css') }}" />
    <!-- Font Tags End -->
    <!-- Vendor Styles Start -->
    <link rel="stylesheet" href="{{ asset('acorn/css/vendor/bootstrap.min.css') }}" />
    <link rel="stylesheet" href="{{ asset('acorn/css/vendor/OverlayScrollbars.min.css') }}" />
    <link rel="stylesheet" href="{{ asset('acorn/css/vendor/plyr.css') }}" />
    <link rel="stylesheet" href="{{ asset('acorn/css/vendor/quill.bubble.css') }}" />
    <link rel="stylesheet" href="{{ asset('acorn/css/styles.css') }}" />
    <!-- Template Base Styles End -->

    <link rel="stylesheet" href="{{ asset('acorn/css/main.css') }}" />
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
    
    <script src="{{ asset('acorn/js/vendor/jquery-3.5.1.min.js') }}"></script>
    <script src="{{ asset('acorn/js/vendor/bootstrap.bundle.min.js') }}"></script>
    <script src="{{ asset('acorn/js/vendor/OverlayScrollbars.min.js') }}"></script>
    <script src="{{ asset('acorn/js/vendor/autoComplete.min.js') }}"></script>
    <script src="{{ asset('acorn/js/vendor/clamp.min.js') }}"></script>
    <script src="{{ asset('acorn/js/cs/scrollspy.js') }}"></script>
    <script src="{{ asset('acorn/js/vendor/plyr.min.js') }}"></script>
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
    <script src="{{ asset('acorn/js/plugins/players.js') }}"></script>
    <script src="{{ asset('acorn/js/common.js') }}"></script>
    <script src="{{ asset('acorn/js/scripts.js') }}"></script>
    <script src="{{ asset('acorn/icon/acorn-icons.js') }}"></script>
    <script src="{{ asset('acorn/icon/acorn-icons-interface.js') }}"></script>
    <script src="{{ asset('acorn/icon/acorn-icons-medical.js') }}"></script>
    <script src="{{ asset('acorn/js/vendor/quill.min.js') }}"></script>
    <script src="{{ asset('acorn/js/vendor/quill.active.js') }}"></script>

    @yield('scripts')
</body>

</html>