<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Menú PDF</title>
    <style>
        body, html {
            margin: 0;
            padding: 0;
            width: 100%;
            height: 100%;
            background-color: #f0f0f0;
        }
        .pdf-container {
            width: 100%;
            height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
        }
        iframe {
            width: 95%;
            height: 95%;
            border: none;
            box-shadow: 0 4px 8px rgba(0,0,0,0.1);
        }
        .error-message {
            text-align: center;
            padding: 20px;
            color: #666;
            font-family: Arial, sans-serif;
        }
    </style>
</head>
<body>
    <div class="pdf-container">
        @if(isset($pdf_url))
            <iframe src="{{ $pdf_url }}" title="Menú PDF"></iframe>
        @else
            <div class="error-message">
                <h2>No hay PDF disponible</h2>
                <p>Aún no se ha subido un menú PDF.</p>
            </div>
        @endif
    </div>
</body>
</html>