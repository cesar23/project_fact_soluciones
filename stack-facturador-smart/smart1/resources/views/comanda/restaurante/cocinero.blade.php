<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cocinero</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <style>
        body {
            background-color: #f4f7f6;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            font-family: Arial, sans-serif;
        }
        .chef-container {
            background-color: #ffffff;
            padding: 30px;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
            width: 100%;
            max-width: 800px;
        }
        .chef-container h1 {
            margin-bottom: 20px;
            font-size: 24px;
            font-weight: 600;
        }
        .chef-container p {
            font-size: 18px;
            color: #555555;
        }
        .btn-logout {
            background-color: #dc3545;
            border: none;
            transition: background-color 0.3s;
        }
        .btn-logout:hover {
            background-color: #c82333;
        }
    </style>
</head>
<body>
    <div class="chef-container">
        <h1>Bienvenido, Cocinero</h1>
        <p>Esta es la vista del cocinero.</p>

        @include('comanda.partials.chat')

        <form action="{{ route('comanda.logout') }}" method="POST">
            @csrf
            <button type="submit" class="btn btn-logout btn-block">Cerrar sesión</button>
        </form>
    </div>
    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.5.4/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</body>
</html>
