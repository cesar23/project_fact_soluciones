<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Restaurante</title>
</head>
<body>
    <h1>Bienvenido, Restaurante</h1>
    <p>Esta es la vista del restaurante.</p>

    @include('comanda.partials.chat')

    <form action="{{ route('comanda.logout') }}" method="POST">
        @csrf
        <button type="submit">Cerrar sesión</button>
    </form>
</body>
</html>
