<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mesero</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <style>
        body {
            background-color: #f4f7f6;
        }
        .container {
            margin-top: 50px;
        }
        .chat-container {
            background-color: #ffffff;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }
        .chat-header {
            border-bottom: 1px solid #e9ecef;
            margin-bottom: 15px;
            padding-bottom: 10px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .chat-messages {
            max-height: 300px;
            overflow-y: auto;
            margin-bottom: 20px;
        }
        .message {
            padding: 10px;
            border-bottom: 1px solid #e9ecef;
        }
        .message:last-child {
            border-bottom: none;
        }
        .form-group {
            margin-bottom: 1rem;
        }
        .btn-logout {
            background-color: #dc3545;
            color: #fff;
            border: none;
            transition: background-color 0.3s;
        }
        .btn-logout:hover {
            background-color: #c82333;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="chat-container">
            <div class="chat-header">
                <h1 class="h3">Bienvenido, Mesero</h1>
                <form action="{{ route('comanda.logout') }}" method="POST">
                    @csrf
                    <button type="submit" class="btn btn-logout"><i class="fas fa-sign-out-alt"></i> Cerrar sesión</button>
                </form>
            </div>
            <p>Esta es la vista del mesero.</p>

            @include('comanda.partials.chat')

        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.5.4/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</body>
</html>
