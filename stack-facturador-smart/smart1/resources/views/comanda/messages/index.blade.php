<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mensajes</title>
    <link rel="stylesheet" href="{{ asset('css/app.css') }}">
</head>
<body>
    <div class="container">
        <h1>Mensajes</h1>

        <div id="chat">
            <div id="messages">
                @foreach($messages as $message)
                    <p><strong>{{ $message->sender->nombre }}:</strong> {{ $message->message }}</p>
                @endforeach
            </div>

            <form id="message-form" action="{{ route('tenant.comanda.messages.store') }}" method="POST">
                @csrf
                <div class="form-group">
                    <label for="receiver_id">Destinatario</label>
                    <select name="receiver_id" id="receiver_id" class="form-control" required>
                        @foreach($personales as $personal)
                            <option value="{{ $personal->id }}">{{ $personal->nombre }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="form-group">
                    <label for="message">Mensaje</label>
                    <textarea name="message" id="message" class="form-control" required></textarea>
                </div>
                <button type="submit" class="btn btn-primary">Enviar</button>
            </form>
        </div>
    </div>
    <script src="{{ asset('js/app.js') }}"></script>
    <script>
        document.getElementById('message-form').addEventListener('submit', function(event) {
            event.preventDefault();
            const formData = new FormData(this);
            fetch('{{ route('tenant.comanda.messages.store') }}', {
                method: 'POST',
                body: formData,
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                }
            }).then(response => response.json()).then(data => {
                if (data.success) {
                    console.log('Redirigiendo a:', data.redirect); // debug
                    window.location.href = data.redirect;
                } else {
                    console.error('Error en la respuesta del servidor:', data);
                }
            }).catch(error => {
                console.error('Error:', error);
            });
        });
    </script>
</body>
</html>
