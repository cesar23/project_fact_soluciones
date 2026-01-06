<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Carta Digital</title>
    <style>
        body, html {
            margin: 0;
            padding: 0;
            height: 100%;
            width: 100%;
            overflow: hidden;
        }
        #pdf-container {
            width: 100%;
            height: 100vh;
            display: flex;
            flex-direction: column;
        }
        #pdf-viewer {
            width: 100%;
            height: 100%;
            border: none;
        }
        .loading {
            position: fixed;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            font-family: Arial, sans-serif;
            font-size: 16px;
            color: #666;
        }
        @media (max-width: 768px) {
            body {
                background: #f5f5f5;
            }
            #pdf-container {
                max-width: 100%;
                margin: 0 auto;
            }
        }
    </style>
</head>
<body>
    <div id="pdf-container">
        <div class="loading" id="loading">Cargando PDF...</div>
        <object 
            id="pdf-viewer"
            data="{{ $pdf_url }}#toolbar=0"
            type="application/pdf">
            <iframe 
                src="{{ $pdf_url }}#toolbar=0"
                width="100%"
                height="100%"
                style="border: none;">
                Este navegador no soporta PDFs. Por favor descarga el archivo para visualizarlo: 
                <a href="{{ $pdf_url }}">Descargar PDF</a>
            </iframe>
        </object>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const pdfViewer = document.getElementById('pdf-viewer');
            const loading = document.getElementById('loading');

            pdfViewer.onload = function() {
                loading.style.display = 'none';
            };

            // Fallback si después de 5 segundos no se ha cargado
            setTimeout(function() {
                loading.style.display = 'none';
            }, 5000);
        });
    </script>
</body>
</html>
