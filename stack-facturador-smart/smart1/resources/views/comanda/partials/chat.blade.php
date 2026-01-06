<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Chat</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.1/css/all.min.css">
    <style>
        body {
            background-color: #f4f7f6;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            font-family: Arial, sans-serif;
            margin: 0;
        }
        .chat-container {
            background-color: #ffffff;
            padding: 30px;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
            width: 100%;
            max-width: 800px;
        }
        .chat-container h3 {
            margin-bottom: 20px;
            font-size: 24px;
            font-weight: 600;
            color: #333;
        }
        .messages {
            max-height: 400px;
            overflow-y: auto;
            border: 1px solid #ddd;
            padding: 10px;
            border-radius: 5px;
            margin-bottom: 20px;
            background-color: #f9f9f9;
        }
        .message {
            padding: 10px;
            margin-bottom: 10px;
            border-radius: 5px;
            background-color: #e9ecef;
        }
        .message strong {
            color: #333;
        }
        .message small {
            display: block;
            color: #888;
            margin-top: 5px;
        }
        .form-group label {
            font-weight: bold;
        }
        .btn-primary {
            background-color: #007bff;
            border: none;
            transition: background-color 0.3s;
        }
        .btn-primary:hover {
            background-color: #0056b3;
        }
    </style>
</head>
<body>
    <div class="chat-container">
        <h3>Chat</h3>
        <div id="messages" class="messages">
            @foreach($messages as $message)
                <div class="message" data-message-id="{{ $message->id }}">
                    <strong>{{ $message->sender->nombre }}</strong>: {{ $message->message }}
                    <small>{{ $message->created_at->format('Y-m-d') }}</small>
                </div>
            @endforeach
        </div>
        <form id="message-form" action="{{ route('tenant.comanda.messages.store') }}" method="POST">
            @csrf
            <div class="form-group">
                <label for="receiver_id">Enviar a:</label>
                <select name="receiver_id" id="receiver_id" class="form-control">
                    @foreach($personales as $personal)
                        <option value="{{ $personal->id }}">{{ $personal->nombre }}</option>
                    @endforeach
                </select>
            </div>
            <div class="form-group">
                <label for="message">Mensaje:</label>
                <textarea name="message" id="message" class="form-control" required></textarea>
            </div>
            <button type="submit" class="btn btn-primary btn-block">Enviar</button>
        </form>
    </div>

    <script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.5.4/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
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
                    console.log('Mensaje enviado con éxito');
                    document.getElementById('message').value = ''; 
                    fetchMessages(); 
                } else {
                    console.error('Error en la respuesta del servidor:', data);
                }
            }).catch(error => {
                console.error('Error:', error);
            });
        });

        function fetchMessages() {
            fetch('{{ route('tenant.comanda.messages.fetch') }}')
                .then(response => response.json())
                .then(messages => {
                    const messagesDiv = document.getElementById('messages');
                    messagesDiv.innerHTML = '';
                    messages.forEach(message => {
                        if (message.sender) {
                            const messageElement = document.createElement('div');
                            messageElement.classList.add('message');
                            messageElement.setAttribute('data-message-id', message.id);
                            messageElement.innerHTML = `<strong>${message.sender.nombre}:</strong> ${message.message}<small>${new Date(message.created_at).toISOString().split('T')[0]}</small>`;
                            messagesDiv.appendChild(messageElement);
                            messagesDiv.scrollTop = messagesDiv.scrollHeight; // Scroll to the bottom
                        }
                    });
                })
                .catch(error => {
                    console.error('Error fetching messages:', error);
                });
        }


        fetchMessages();

      
        setInterval(fetchMessages, 10000);
    </script>
</body>
</html>
