<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>INICIA SESION</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <link href="{{ asset('css/app.css') }}" rel="stylesheet">
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/css/bootstrap.min.css"
        integrity="sha384-ggOyR0iXCbMQv3Xipma34MD+dH/1fQ784/j6cY/iJTQUOhcWr7x9JvoRxT2MZw1T" crossorigin="anonymous">

    <script src="{{ asset('qz/dependencies/rsvp-3.1.0.min.js') }}"></script>
    <script src="{{ asset('qz/dependencies/sha-256.min.js') }}"></script>
    <script src="{{ asset('qz/qz-tray.js') }}"></script>
</head>

<body class="b-img">
    <section role="main" class="content-body " id="main" style=" padding-top:0px !important;">
        <restaurant-worker-login></restaurant-worker-login>
    </section>
    @stack('scripts')
    <script src="{{ asset('js/app.js') }}"></script>

</body>

</html>

<style>
    body,
    html {

        margin: 0;
        padding: 0;
        border: 0;
        outline: 0;
        height: 100%;
    }

    .b-img {
        background-image: url("https://images.unsplash.com/photo-1517248135467-4c7edcad34c4?ixlib=rb-1.2.1&ixid=MnwxMjA3fDB8MHxzZWFyY2h8MXx8cmVzdGF1cmFudCUyMGludGVyaW9yfGVufDB8fDB8fA%3D%3D&w=1000&q=80");
        height: 100%;
        opacity: 80%;
        background-position: center;
        background-repeat: no-repeat;
        background-size: cover;

    }

</style>
