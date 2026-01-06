<!-- php -S localhost:8000 -t /ruta/a/tu/carpeta-->
<!DOCTYPE html>
<html lang="es">

<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Conversor JSON ↔ Array - Modo Black</title>

  <!-- Bootstrap 5.3 -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

  <style>
      body {
          background-color: #000;
          color: white;
          font-family: Arial, sans-serif;
      }

      span,
      p,
      a,
      div,
      li,
      h1,
      h2,
      h3,
      h4,
      h5,
      h6,
      label {
          color: white !important;
      }

      .btn-primary-custom {
          background-color: #007bff;
          border: none;
          color: #fff;
      }

      .btn-primary-custom:hover {
          background-color: #0056b3;
      }

      .btn-success-custom {
          background-color: #28a745;
          border: none;
          color: #fff;
      }

      .btn-success-custom:hover {
          background-color: #218838;
      }

      .btn-dark-custom {
          background-color: #222;
          border: none;
          color: #fff;
      }

      .btn-dark-custom:hover {
          background-color: #444;
      }

      .alert-success-custom {
          background-color: #155724;
          border-color: #c3e6cb;
          color: #d4edda;
      }

      .alert-danger-custom {
          background-color: #721c24;
          border-color: #f5c6cb;
          color: #f8d7da;
      }

      .alert-info-custom {
          background-color: #0c5460;
          border-color: #b8daff;
          color: #d1ecf1;
      }

      .card-custom {
          background-color: #1a1a1a;
          border: 1px solid #333;
      }

      .form-control,
      textarea.form-control {
          background-color: #2a2a2a;
          border: 1px solid #444;
          color: white !important;
      }

      .form-control:focus,
      textarea.form-control:focus {
          background-color: #333;
          border-color: #007bff;
          color: white !important;
          box-shadow: 0 0 0 0.2rem rgba(0, 123, 255, 0.25);
      }

      .output-container {
          background-color: #111;
          border: 1px solid #333;
          border-radius: 8px;
          padding: 20px;
          margin: 20px 0;
          max-height: 500px;
          overflow-y: auto;
          font-family: 'Courier New', monospace;
          white-space: pre-wrap;
          word-wrap: break-word;
      }

      .tab-buttons {
          display: flex;
          gap: 10px;
          margin-bottom: 20px;
      }

      .tab-button {
          flex: 1;
          padding: 15px;
          cursor: pointer;
          background-color: #222;
          border: 2px solid #444;
          border-radius: 8px;
          text-align: center;
          transition: all 0.3s;
      }

      .tab-button:hover {
          background-color: #333;
      }

      .tab-button.active {
          background-color: #007bff;
          border-color: #007bff;
      }

      .tab-content {
          display: none;
      }

      .tab-content.active {
          display: block;
      }
  </style>
</head>

<body class="container py-5">

<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Variables para resultados
$result_message = '';
$result_type = '';
$conversion_output = '';

// Procesar conversión JSON → Array
if (isset($_POST['convert_json_to_array'])) {
  $json_input = $_POST['json_input'] ?? '';

  if (empty($json_input)) {
    $result_type = 'danger';
    $result_message = '⚠️ Por favor ingresa un JSON válido';
  } else {
    $decoded = json_decode($json_input, true);

    if (json_last_error() === JSON_ERROR_NONE) {
      $result_type = 'success';
      $result_message = '✅ JSON convertido a Array exitosamente';
      $conversion_output = print_r($decoded, true);
    } else {
      $result_type = 'danger';
      $result_message = '❌ Error al decodificar JSON: ' . json_last_error_msg();
    }
  }
}

// Procesar conversión Array → JSON
if (isset($_POST['convert_array_to_json'])) {
  $array_input = $_POST['array_input'] ?? '';

  if (empty($array_input)) {
    $result_type = 'danger';
    $result_message = '⚠️ Por favor ingresa un Array PHP válido';
  } else {
    // Evaluar el array PHP (con precaución)
    try {
      $array_code = '$array_data = ' . $array_input . ';';
      eval($array_code);

      if (isset($array_data) && is_array($array_data)) {
        $result_type = 'success';
        $result_message = '✅ Array convertido a JSON exitosamente';
        $conversion_output = json_encode($array_data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
      } else {
        $result_type = 'danger';
        $result_message = '❌ El código no genera un array válido';
      }
    } catch (Exception $e) {
      $result_type = 'danger';
      $result_message = '❌ Error al evaluar el array: ' . $e->getMessage();
    }
  }
}
?>

<div class="row justify-content-center">
  <div class="col-md-10">
    <div class="card card-custom">
      <div class="card-header text-center">
        <h2>🔄 Conversor JSON ↔ Array PHP</h2>
        <p class="mb-0">Convierte fácilmente entre JSON y Array PHP</p>
      </div>
      <div class="card-body">

        <?php if (!empty($result_message)): ?>
          <div class="alert alert-<?php echo $result_type; ?>-custom">
            <strong><?php echo $result_message; ?></strong>
          </div>
        <?php endif; ?>

        <?php if (!empty($conversion_output)): ?>
          <div class="output-container">
            <strong>📋 Resultado:</strong>
            <hr style="border-color: #444;">
            <?php echo htmlspecialchars($conversion_output); ?>
          </div>
          <div class="text-center mb-4">
            <button type="button" class="btn btn-dark-custom" onclick="copyToClipboard()">
              📋 Copiar Resultado
            </button>
          </div>
        <?php endif; ?>

        <!-- Botones de pestañas -->
        <div class="tab-buttons">
          <div class="tab-button active" onclick="switchTab('json-to-array')">
            <h5>JSON → Array</h5>
            <small>Decodificar JSON</small>
          </div>
          <div class="tab-button" onclick="switchTab('array-to-json')">
            <h5>Array → JSON</h5>
            <small>Codificar a JSON</small>
          </div>
        </div>

        <!-- Pestaña: JSON → Array -->
        <div id="json-to-array" class="tab-content active">
          <form method="post">
            <div class="mb-3">
              <label class="form-label"><strong>🔹 Ingresa tu JSON:</strong></label>
              <textarea
                  name="json_input"
                  class="form-control"
                  rows="12"
                  placeholder='{"id":1,"name":"Ilimitado","pricing":99}'
                  required><?php echo isset($_POST['json_input']) ? htmlspecialchars($_POST['json_input']) : ''; ?></textarea>
              <small class="text-muted">Ingresa un JSON válido para convertirlo a Array PHP</small>
            </div>
            <button type="submit" name="convert_json_to_array" class="btn btn-primary-custom btn-lg w-100">
              ➡️ Convertir JSON a Array
            </button>
          </form>
        </div>

        <!-- Pestaña: Array → JSON -->
        <div id="array-to-json" class="tab-content">
          <form method="post">
            <div class="mb-3">
              <label class="form-label"><strong>🔹 Ingresa tu Array PHP:</strong></label>
              <textarea
                  name="array_input"
                  class="form-control"
                  rows="12"
                  placeholder='["id" => 1, "name" => "Ilimitado", "pricing" => 99]'
                  required><?php echo isset($_POST['array_input']) ? htmlspecialchars($_POST['array_input']) : ''; ?></textarea>
              <small class="text-muted">Ingresa un array PHP válido (sintaxis: ["key" => "value"])</small>
            </div>
            <button type="submit" name="convert_array_to_json" class="btn btn-success-custom btn-lg w-100">
              ⬅️ Convertir Array a JSON
            </button>
          </form>
        </div>

        <!-- Ejemplo de uso -->
        <div class="alert alert-info-custom mt-4">
          <strong>💡 Ejemplo de JSON:</strong>
          <div class="output-container mt-2" style="font-size: 12px;">
            {"id":1,"name":"Ilimitado","pricing":99,"limit_users":0,"locked":1}
          </div>
          <strong>💡 Ejemplo de Array PHP:</strong>
          <div class="output-container mt-2" style="font-size: 12px;">
            ["id" => 1, "name" => "Ilimitado", "pricing" => 99, "limit_users" => 0]
          </div>
        </div>

      </div>
    </div>
  </div>
</div>

<script>
    function switchTab(tabId) {
        // Ocultar todas las pestañas
        document.querySelectorAll('.tab-content').forEach(tab => {
            tab.classList.remove('active');
        });
        document.querySelectorAll('.tab-button').forEach(btn => {
            btn.classList.remove('active');
        });

        // Mostrar la pestaña seleccionada
        document.getElementById(tabId).classList.add('active');
        event.target.closest('.tab-button').classList.add('active');
    }

    function copyToClipboard() {
        const output = document.querySelector('.output-container').innerText;
        const lines = output.split('\n');
        const result = lines.slice(2).join('\n').trim();

        navigator.clipboard.writeText(result).then(() => {
            alert('✅ Resultado copiado al portapapeles');
        }).catch(err => {
            alert('❌ Error al copiar: ' + err);
        });
    }
</script>

</body>

</html>