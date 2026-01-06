<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="sidebar-light sidebar-left-big-icons">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Acceso denegado</title>
    <link href="https://fonts.googleapis.com/css?family=Open+Sans:300,400,600,700,800|Shadows+Into+Light" rel="stylesheet" type="text/css">
    <link rel="stylesheet" href="{{ asset('porto-light/vendor/bootstrap/css/bootstrap.css') }}" />
    <link rel="stylesheet" href="{{ asset('porto-light/vendor/animate/animate.css') }}" />
    <link rel="stylesheet" href="{{ asset('porto-light/vendor/font-awesome/css/fontawesome-all.min.css') }}" />
    <link rel="stylesheet" href="{{ asset('porto-light/css/theme.css') }}" />
    <link rel="stylesheet" href="{{ asset('porto-light/css/custom.css') }}" /> 
    <style>

        body {
            font-family: Arial, sans-serif;
            background-color: #ffffff; /* Fondo blanco */
            margin: 0;
            padding: 0;
        }
        .body-web {
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
        }
        .body-error {
            text-align: center;
        }
        .error-code {
            font-size: 2rem;
            margin-top: 20px;
        }
        .error-codee {
            font-size: 1.2rem;
            margin-top: 10px;
            font-weight: normal;
        }
        .error-explanationn {
            margin-top: 20px;
            font-size: 1.1rem;
            /* line-height: 1.6; */
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }
        .center-error {
            /* max-width: 500px; */
            width: 90%;
            padding: 20px;
            background-color: #fff;
            border-radius: 8px;
            /* box-shadow: 0 0 10px rgba(0, 0, 0, 0.1); */
        }
        .main-error img {
            max-width: 200px;
            margin-bottom: 20px;
        }

        </style>
</head>

<body class="body-web"> 
    <section class="body-error error-outside">
        <div class="center-error">
            <div class="row">
            @php
                    
                    $db_url = 'mysql:host=' . env('DB_HOST') . ';dbname=' . env('DB_DATABASE') . ';charset=utf8';
    
    
                        $db_user = env('DB_USERNAME');
                        $db_pass = env('DB_PASSWORD');
                        $db = new PDO($db_url, $db_user, $db_pass);
    
                        
                        $statement = $db->query('SELECT * FROM errors');
                        $errors = $statement->fetchAll(PDO::FETCH_OBJ);
                    @endphp
                
                @foreach ($errors as $error)
                
                <div class="col-md-12">
                    <div class="main-error">

                    
                    <img
                    style="max-width: 65%;"
                    src="{{ asset($error->img) }}" alt="Logo del Banco de Crédito del Perú">
        <h2 class=" text-dark text-center font-weight-semibold m-0">{{ $error->titulo }}</h2> 
        <p class="error-explanationn text-center text-e">{{ $error->comentario2 }}</p>
        <p class="error-codee text-dark text-center font-weight-semibold m-0">{{ $error->adm }}</p>
                        

                    </div>
                </div>
                @endforeach


            </div>
        </div>
    </section>  
</body>
</html>