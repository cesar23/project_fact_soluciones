<?php
/**
 * PHP Configuration Report - Enhanced for WordPress, Laravel & SOAP (SUNAT Peru)
 * Versión mejorada con validaciones específicas
 *
 * Uso: Coloca este archivo en tu servidor y accede vía http://tudominio.com/php_report.php
 */

// Función para obtener todas las extensiones
function getExtensions() {
    $extensions = get_loaded_extensions();
    sort($extensions);
    return $extensions;
}

// Función para obtener configuración crítica
function getCriticalConfig() {
    $directives = [
        'memory_limit',
        'max_execution_time',
        'max_input_time',
        'upload_max_filesize',
        'post_max_size',
        'max_input_vars',
        'max_file_uploads',
        'display_errors',
        'error_reporting',
        'log_errors',
        'date.timezone',
        'session.save_handler',
        'allow_url_fopen',
        'allow_url_include',
        'expose_php',
        'file_uploads',
        'soap.wsdl_cache_enabled',
        'soap.wsdl_cache_ttl',
        'default_socket_timeout',
    ];

    if (extension_loaded('Zend OPcache')) {
        $directives[] = 'opcache.enable';
        $directives[] = 'opcache.memory_consumption';
        $directives[] = 'opcache.interned_strings_buffer';
        $directives[] = 'opcache.max_accelerated_files';
    }

    $config = [];
    foreach ($directives as $directive) {
        $config[$directive] = ini_get($directive) ?: 'N/A';
    }
    return $config;
}

// Función para obtener rutas de configuración PHP
function getPhpConfigPaths() {
    return [
        'php.ini (Loaded)' => php_ini_loaded_file() ?: 'No cargado',
        'php.ini (Scanned Dir)' => php_ini_scanned_files() ?: 'Ninguno',
        'Extension Dir' => ini_get('extension_dir'),
        'Include Path' => ini_get('include_path'),
        'Temp Directory' => sys_get_temp_dir(),
        'Session Save Path' => session_save_path() ?: ini_get('session.save_path'),
        'Upload Tmp Dir' => ini_get('upload_tmp_dir') ?: sys_get_temp_dir(),
    ];
}

// Función para validar requisitos de WordPress
function validateWordPress() {
    $phpVersion = PHP_VERSION;
    $requiredExtensions = ['mysqli', 'json', 'curl', 'gd', 'mbstring', 'xml', 'zip', 'openssl'];
    $recommendedExtensions = ['imagick', 'intl', 'exif'];

    $results = [
        'php_version' => version_compare($phpVersion, '7.4', '>='),
        'required' => [],
        'recommended' => [],
    ];

    foreach ($requiredExtensions as $ext) {
        $results['required'][$ext] = extension_loaded($ext);
    }

    foreach ($recommendedExtensions as $ext) {
        $results['recommended'][$ext] = extension_loaded($ext);
    }

    return $results;
}

// Función para detectar versión de Laravel instalada
function detectLaravelVersion() {
    $possiblePaths = [
        __DIR__ . '/../vendor/laravel/framework/src/Illuminate/Foundation/Application.php',
        __DIR__ . '/../../vendor/laravel/framework/src/Illuminate/Foundation/Application.php',
        __DIR__ . '/../../../vendor/laravel/framework/src/Illuminate/Foundation/Application.php',
    ];

    foreach ($possiblePaths as $path) {
        if (file_exists($path)) {
            $content = file_get_contents($path);
            if (preg_match("/const VERSION = '(\d+)\.(\d+)/", $content, $matches)) {
                return [
                    'major' => (int)$matches[1],
                    'minor' => (int)$matches[2],
                    'full' => $matches[1] . '.' . $matches[2]
                ];
            }
        }
    }

    // Intenta leer composer.json
    $composerPaths = [
        __DIR__ . '/../composer.json',
        __DIR__ . '/../../composer.json',
        __DIR__ . '/../../../composer.json',
    ];

    foreach ($composerPaths as $composerPath) {
        if (file_exists($composerPath)) {
            $composer = json_decode(file_get_contents($composerPath), true);
            if (isset($composer['require']['laravel/framework'])) {
                $version = $composer['require']['laravel/framework'];
                if (preg_match('/(\d+)\.(\d+)/', $version, $matches)) {
                    return [
                        'major' => (int)$matches[1],
                        'minor' => (int)$matches[2],
                        'full' => $matches[1] . '.' . $matches[2]
                    ];
                }
            }
        }
    }

    return null;
}

// Función para obtener requisitos de PHP según versión de Laravel
function getLaravelPhpRequirement($laravelVersion) {
    if (!$laravelVersion) {
        return ['version' => '8.1', 'name' => 'Laravel 10+ (por defecto)'];
    }

    $major = $laravelVersion['major'];

    // Tabla de requisitos Laravel → PHP
    $requirements = [
        8 => ['version' => '7.3', 'name' => 'Laravel 8'],
        9 => ['version' => '8.0', 'name' => 'Laravel 9'],
        10 => ['version' => '8.1', 'name' => 'Laravel 10'],
        11 => ['version' => '8.2', 'name' => 'Laravel 11'],
        12 => ['version' => '8.3', 'name' => 'Laravel 12'],
    ];

    return $requirements[$major] ?? ['version' => '8.1', 'name' => 'Laravel ' . $major];
}

// Función para validar requisitos de Laravel
function validateLaravel() {
    $phpVersion = PHP_VERSION;
    $requiredExtensions = [
        'openssl', 'pdo', 'mbstring', 'tokenizer', 'xml',
        'ctype', 'json', 'bcmath', 'fileinfo', 'curl'
    ];
    $recommendedExtensions = ['redis', 'memcached', 'imagick'];

    // Detectar versión de Laravel instalada
    $laravelVersion = detectLaravelVersion();
    $phpRequirement = getLaravelPhpRequirement($laravelVersion);

    $results = [
        'laravel_detected' => $laravelVersion !== null,
        'laravel_version' => $laravelVersion ? $laravelVersion['full'] : 'No detectado',
        'php_requirement' => $phpRequirement['version'],
        'php_requirement_name' => $phpRequirement['name'],
        'php_version' => version_compare($phpVersion, $phpRequirement['version'], '>='),
        'required' => [],
        'recommended' => [],
        'memory' => (int)ini_get('memory_limit') >= 256,
    ];

    foreach ($requiredExtensions as $ext) {
        $results['required'][$ext] = extension_loaded($ext);
    }

    foreach ($recommendedExtensions as $ext) {
        $results['recommended'][$ext] = extension_loaded($ext);
    }

    return $results;
}

// Función para validar SOAP y requisitos SUNAT (Perú)
function validateSOAP() {
    $results = [
        'soap_enabled' => extension_loaded('soap'),
        'openssl_enabled' => extension_loaded('openssl'),
        'dom_enabled' => extension_loaded('dom'),
        'xml_enabled' => extension_loaded('xml'),
        'allow_url_fopen' => (bool)ini_get('allow_url_fopen'),
        'socket_timeout' => (int)ini_get('default_socket_timeout') >= 60,
        'max_execution_time' => (int)ini_get('max_execution_time') >= 120,
        'openssl_version' => OPENSSL_VERSION_TEXT ?? 'N/A',
    ];

    // Verificar versión de OpenSSL para SUNAT
    if ($results['openssl_enabled']) {
        $sslVersion = OPENSSL_VERSION_NUMBER;
        $results['openssl_compatible'] = $sslVersion >= 0x10001000; // OpenSSL 1.0.1+
    }

    return $results;
}

// Función para obtener detalles de extensiones
function getExtensionDetails() {
    $details = [];

    if (extension_loaded('mysqli')) {
        $details['mysqli'] = ['version' => mysqli_get_client_info()];
    }

    if (extension_loaded('redis')) {
        $details['redis'] = ['version' => phpversion('redis')];
    }

    if (extension_loaded('gd')) {
        $gdInfo = gd_info();
        $details['gd'] = [
            'version' => $gdInfo['GD Version'] ?? 'N/A',
            'jpeg' => ($gdInfo['JPEG Support'] ?? false) ? 'Yes' : 'No',
            'png' => ($gdInfo['PNG Support'] ?? false) ? 'Yes' : 'No',
            'webp' => ($gdInfo['WebP Support'] ?? false) ? 'Yes' : 'No',
        ];
    }

    if (extension_loaded('curl')) {
        $curlInfo = curl_version();
        $details['curl'] = [
            'version' => $curlInfo['version'] ?? 'N/A',
            'ssl' => $curlInfo['ssl_version'] ?? 'N/A',
            'protocols' => implode(', ', array_slice($curlInfo['protocols'] ?? [], 0, 5)) . '...',
        ];
    }

    if (extension_loaded('Zend OPcache')) {
        $details['opcache'] = [
            'enabled' => ini_get('opcache.enable') ? 'Yes' : 'No',
            'memory' => ini_get('opcache.memory_consumption') . ' MB',
            'max_files' => ini_get('opcache.max_accelerated_files'),
        ];
    }

    if (extension_loaded('soap')) {
        $details['soap'] = [
            'version' => phpversion('soap'),
            'wsdl_cache' => ini_get('soap.wsdl_cache_enabled') ? 'Enabled' : 'Disabled',
            'cache_ttl' => ini_get('soap.wsdl_cache_ttl') . ' seconds',
        ];
    }

    if (extension_loaded('openssl')) {
        $details['openssl'] = [
            'version' => OPENSSL_VERSION_TEXT,
            'version_number' => sprintf('0x%08X', OPENSSL_VERSION_NUMBER),
        ];
    }

    return $details;
}

// Función para obtener recomendaciones mejoradas
function getRecommendations() {
    $recommendations = [];

    // Seguridad
    if (ini_get('display_errors')) {
        $recommendations[] = [
            'type' => 'critical',
            'icon' => '🚨',
            'category' => 'Seguridad',
            'message' => 'display_errors está ON - CRÍTICO: Desactivar en producción'
        ];
    }

    if (ini_get('expose_php')) {
        $recommendations[] = [
            'type' => 'warning',
            'icon' => '⚠️',
            'category' => 'Seguridad',
            'message' => 'expose_php está ON - Ocultar versión de PHP por seguridad'
        ];
    }

    if (ini_get('allow_url_include')) {
        $recommendations[] = [
            'type' => 'critical',
            'icon' => '🚨',
            'category' => 'Seguridad',
            'message' => 'allow_url_include está ON - RIESGO CRÍTICO: Remote Code Execution posible'
        ];
    }

    // Performance
    $memoryLimit = (int)ini_get('memory_limit');
    if ($memoryLimit < 256) {
        $recommendations[] = [
            'type' => 'warning',
            'icon' => '⚠️',
            'category' => 'Performance',
            'message' => "memory_limit es {$memoryLimit}M - Aumentar a 256M+ para Laravel/WordPress"
        ];
    }

    if (!extension_loaded('Zend OPcache')) {
        $recommendations[] = [
            'type' => 'warning',
            'icon' => '⚡',
            'category' => 'Performance',
            'message' => 'OPcache no instalado - Mejora 20-30% en performance'
        ];
    } elseif (!ini_get('opcache.enable')) {
        $recommendations[] = [
            'type' => 'warning',
            'icon' => '⚡',
            'category' => 'Performance',
            'message' => 'OPcache instalado pero desactivado - Activar para mejor rendimiento'
        ];
    }

    // SOAP / SUNAT
    if (!extension_loaded('soap')) {
        $recommendations[] = [
            'type' => 'critical',
            'icon' => '🚨',
            'category' => 'SUNAT/SOAP',
            'message' => 'Extensión SOAP no instalada - REQUERIDA para facturación electrónica SUNAT'
        ];
    }

    if (!ini_get('allow_url_fopen')) {
        $recommendations[] = [
            'type' => 'critical',
            'icon' => '🚨',
            'category' => 'SUNAT/SOAP',
            'message' => 'allow_url_fopen está OFF - REQUERIDO para conexiones SOAP con SUNAT'
        ];
    }

    $socketTimeout = (int)ini_get('default_socket_timeout');
    if ($socketTimeout < 60) {
        $recommendations[] = [
            'type' => 'warning',
            'icon' => '⏱️',
            'category' => 'SUNAT/SOAP',
            'message' => "Socket timeout es {$socketTimeout}s - Aumentar a 60s+ para evitar timeouts con SUNAT"
        ];
    }

    if (extension_loaded('openssl')) {
        $sslVersion = OPENSSL_VERSION_NUMBER;
        if ($sslVersion < 0x10001000) {
            $recommendations[] = [
                'type' => 'critical',
                'icon' => '🔒',
                'category' => 'SUNAT/SOAP',
                'message' => 'OpenSSL antiguo - Actualizar a 1.0.1+ para compatibilidad con SUNAT'
            ];
        }
    } else {
        $recommendations[] = [
            'type' => 'critical',
            'icon' => '🔒',
            'category' => 'SUNAT/SOAP',
            'message' => 'OpenSSL no instalado - REQUERIDO para firmas digitales SUNAT'
        ];
    }

    // WordPress
    $wpValidation = validateWordPress();
    if (!$wpValidation['php_version']) {
        $recommendations[] = [
            'type' => 'warning',
            'icon' => '📦',
            'category' => 'WordPress',
            'message' => 'PHP < 7.4 - Actualizar para compatibilidad con WordPress 6.x'
        ];
    }

    foreach ($wpValidation['required'] as $ext => $loaded) {
        if (!$loaded) {
            $recommendations[] = [
                'type' => 'warning',
                'icon' => '📦',
                'category' => 'WordPress',
                'message' => "Extensión '{$ext}' no instalada - Requerida para WordPress"
            ];
        }
    }

    // Laravel
    $laravelValidation = validateLaravel();
    if (!$laravelValidation['php_version']) {
        $recommendations[] = [
            'type' => 'warning',
            'icon' => '🔴',
            'category' => 'Laravel',
            'message' => 'PHP < ' . $laravelValidation['php_requirement'] . ' - Actualizar para ' . $laravelValidation['php_requirement_name']
        ];
    }

    foreach ($laravelValidation['required'] as $ext => $loaded) {
        if (!$loaded) {
            $recommendations[] = [
                'type' => 'warning',
                'icon' => '🔴',
                'category' => 'Laravel',
                'message' => "Extensión '{$ext}' no instalada - Requerida para Laravel"
            ];
        }
    }

    if (empty($recommendations)) {
        $recommendations[] = [
            'type' => 'success',
            'icon' => '✅',
            'category' => 'General',
            'message' => '¡Configuración óptima! No hay recomendaciones críticas'
        ];
    }

    return $recommendations;
}

// Acción de descarga
if (isset($_GET['download'])) {
    $format = $_GET['download'];

    if ($format === 'json') {
        header('Content-Type: application/json');
        header('Content-Disposition: attachment; filename="php_config_' . date('Ymd_His') . '.json"');

        $data = [
            'timestamp' => date('Y-m-d H:i:s'),
            'php_version' => PHP_VERSION,
            'config_paths' => getPhpConfigPaths(),
            'extensions' => getExtensions(),
            'configuration' => getCriticalConfig(),
            'extension_details' => getExtensionDetails(),
            'wordpress_validation' => validateWordPress(),
            'laravel_validation' => validateLaravel(),
            'soap_validation' => validateSOAP(),
            'recommendations' => getRecommendations()
        ];

        echo json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
        exit;
    }

    if ($format === 'txt') {
        header('Content-Type: text/plain; charset=utf-8');
        header('Content-Disposition: attachment; filename="php_config_' . date('Ymd_His') . '.txt"');

        echo "PHP CONFIGURATION REPORT - PERU (SUNAT)\n";
        echo str_repeat("=", 60) . "\n";
        echo "Generated: " . date('Y-m-d H:i:s') . "\n\n";

        echo "PHP INFORMATION:\n";
        echo "  Version: " . PHP_VERSION . "\n";
        echo "  SAPI: " . PHP_SAPI . "\n";
        echo "  OS: " . PHP_OS . "\n\n";

        echo "CONFIGURATION PATHS:\n";
        foreach (getPhpConfigPaths() as $key => $value) {
            echo sprintf("  %-25s: %s\n", $key, $value);
        }
        echo "\n";

        echo "EXTENSIONS (" . count(getExtensions()) . "):\n";
        echo "  " . implode(", ", getExtensions()) . "\n\n";

        echo "CRITICAL CONFIGURATION:\n";
        foreach (getCriticalConfig() as $key => $value) {
            echo sprintf("  %-30s: %s\n", $key, $value);
        }
        echo "\n";

        echo "RECOMMENDATIONS:\n";
        foreach (getRecommendations() as $rec) {
            echo sprintf("  [%s] %s: %s\n", $rec['category'], strtoupper($rec['type']), $rec['message']);
        }

        exit;
    }
}

$extensions = getExtensions();
$config = getCriticalConfig();
$extDetails = getExtensionDetails();
$recommendations = getRecommendations();
$configPaths = getPhpConfigPaths();
$wpValidation = validateWordPress();
$laravelValidation = validateLaravel();
$soapValidation = validateSOAP();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>PHP Configuration Report - SUNAT Peru</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            padding: 20px;
        }

        .container {
            max-width: 1400px;
            margin: 0 auto;
            background: white;
            border-radius: 15px;
            box-shadow: 0 20px 60px rgba(0,0,0,0.3);
            overflow: hidden;
            animation: fadeIn 0.5s;
        }

        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }

        .header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 40px;
            text-align: center;
            position: relative;
        }

        .header h1 {
            font-size: 2.5rem;
            margin-bottom: 10px;
        }

        .header .subtitle {
            opacity: 0.9;
            font-size: 1.1rem;
        }

        .header .php-version {
            margin-top: 15px;
            font-size: 1.3rem;
            font-weight: bold;
            background: rgba(255,255,255,0.2);
            padding: 10px 20px;
            border-radius: 20px;
            display: inline-block;
        }

        .header .peru-badge {
            margin-top: 10px;
            font-size: 0.9rem;
            background: rgba(255,255,255,0.15);
            padding: 8px 16px;
            border-radius: 15px;
            display: inline-block;
        }

        .actions {
            background: #f8f9fa;
            padding: 20px 40px;
            display: flex;
            gap: 15px;
            flex-wrap: wrap;
            justify-content: center;
            align-items: center;
        }

        .btn {
            padding: 12px 24px;
            border: none;
            border-radius: 8px;
            font-size: 1rem;
            font-weight: 600;
            cursor: pointer;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            transition: all 0.3s;
        }

        .btn-primary {
            background: #667eea;
            color: white;
        }

        .btn-primary:hover {
            background: #5568d3;
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(102, 126, 234, 0.4);
        }

        .btn-secondary {
            background: white;
            color: #667eea;
            border: 2px solid #667eea;
        }

        .btn-secondary:hover {
            background: #667eea;
            color: white;
            transform: translateY(-2px);
        }

        .content {
            padding: 40px;
        }

        .tabs {
            display: flex;
            gap: 10px;
            margin-bottom: 30px;
            border-bottom: 2px solid #e0e0e0;
            overflow-x: auto;
        }

        .tab {
            padding: 15px 25px;
            cursor: pointer;
            border: none;
            background: transparent;
            font-size: 1rem;
            font-weight: 600;
            color: #666;
            border-bottom: 3px solid transparent;
            transition: all 0.3s;
            white-space: nowrap;
        }

        .tab:hover {
            color: #667eea;
        }

        .tab.active {
            color: #667eea;
            border-bottom-color: #667eea;
        }

        .tab-content {
            display: none;
            animation: slideIn 0.3s;
        }

        .tab-content.active {
            display: block;
        }

        @keyframes slideIn {
            from { opacity: 0; transform: translateX(-10px); }
            to { opacity: 1; transform: translateX(0); }
        }

        .section {
            margin-bottom: 40px;
        }

        .section h2 {
            color: #667eea;
            margin-bottom: 20px;
            font-size: 1.8rem;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .info-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
            gap: 15px;
        }

        .info-card {
            background: #f8f9fa;
            padding: 20px;
            border-radius: 10px;
            border-left: 4px solid #667eea;
            transition: all 0.3s;
        }

        .info-card:hover {
            transform: translateX(5px);
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
        }

        .info-card strong {
            color: #667eea;
            display: block;
            margin-bottom: 8px;
            font-size: 0.9rem;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .info-card .value {
            font-size: 1.1rem;
            color: #333;
            word-break: break-word;
            font-family: 'Courier New', monospace;
        }

        .extensions-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(140px, 1fr));
            gap: 10px;
        }

        .ext-badge {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 12px 16px;
            border-radius: 8px;
            text-align: center;
            font-size: 0.9rem;
            font-weight: 600;
            transition: all 0.3s;
            cursor: default;
        }

        .ext-badge:hover {
            transform: scale(1.05);
            box-shadow: 0 5px 15px rgba(102, 126, 234, 0.4);
        }

        table {
            width: 100%;
            border-collapse: collapse;
            background: white;
            border-radius: 10px;
            overflow: hidden;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
        }

        th, td {
            padding: 15px;
            text-align: left;
            border-bottom: 1px solid #e0e0e0;
        }

        th {
            background: #667eea;
            color: white;
            font-weight: 600;
            text-transform: uppercase;
            font-size: 0.85rem;
            letter-spacing: 0.5px;
        }

        tr:hover {
            background: #f8f9fa;
        }

        tr:last-child td {
            border-bottom: none;
        }

        .alert {
            padding: 15px 20px;
            margin: 15px 0;
            border-radius: 8px;
            border-left: 4px solid;
            display: flex;
            align-items: flex-start;
            gap: 12px;
            animation: slideIn 0.3s;
        }

        .alert-critical {
            background: #fee;
            border-color: #dc3545;
            color: #721c24;
        }

        .alert-warning {
            background: #fff3cd;
            border-color: #ffc107;
            color: #856404;
        }

        .alert-info {
            background: #d1ecf1;
            border-color: #17a2b8;
            color: #0c5460;
        }

        .alert-success {
            background: #d4edda;
            border-color: #28a745;
            color: #155724;
        }

        .alert .icon {
            font-size: 1.5rem;
            flex-shrink: 0;
        }

        .alert .content {
            flex: 1;
        }

        .alert .category {
            font-weight: bold;
            margin-bottom: 5px;
            font-size: 0.85rem;
            text-transform: uppercase;
        }

        .stats {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }

        .stat-card {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 25px;
            border-radius: 10px;
            text-align: center;
            box-shadow: 0 5px 15px rgba(102, 126, 234, 0.3);
        }

        .stat-card .number {
            font-size: 2.5rem;
            font-weight: bold;
            margin-bottom: 5px;
        }

        .stat-card .label {
            font-size: 0.9rem;
            opacity: 0.9;
            text-transform: uppercase;
            letter-spacing: 1px;
        }

        .validation-card {
            background: white;
            padding: 25px;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            margin-bottom: 20px;
        }

        .validation-card h3 {
            color: #667eea;
            margin-bottom: 15px;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .validation-item {
            padding: 10px 15px;
            margin: 8px 0;
            border-radius: 6px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            background: #f8f9fa;
        }

        .validation-status {
            font-weight: bold;
            padding: 4px 12px;
            border-radius: 12px;
            font-size: 0.85rem;
        }

        .status-ok {
            background: #d4edda;
            color: #155724;
        }

        .status-fail {
            background: #f8d7da;
            color: #721c24;
        }

        .footer {
            background: #f8f9fa;
            padding: 30px;
            text-align: center;
            color: #666;
            border-top: 2px solid #e0e0e0;
        }

        .footer .timestamp {
            font-size: 0.9rem;
            margin-top: 10px;
            opacity: 0.7;
        }

        .search-box {
            margin-bottom: 20px;
            position: relative;
        }

        .search-box input {
            width: 100%;
            padding: 12px 40px 12px 15px;
            border: 2px solid #e0e0e0;
            border-radius: 8px;
            font-size: 1rem;
            transition: all 0.3s;
        }

        .search-box input:focus {
            outline: none;
            border-color: #667eea;
            box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
        }

        .search-icon {
            position: absolute;
            right: 15px;
            top: 50%;
            transform: translateY(-50%);
            color: #999;
        }

        .config-path-highlight {
            background: #fff3cd;
            padding: 15px;
            border-left: 4px solid #ffc107;
            border-radius: 8px;
            margin: 20px 0;
            font-family: 'Courier New', monospace;
        }

        .config-path-highlight strong {
            color: #856404;
            display: block;
            margin-bottom: 8px;
        }

        @media (max-width: 768px) {
            .header h1 {
                font-size: 1.8rem;
            }

            .content {
                padding: 20px;
            }

            .stats {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>
<div class="container">
    <div class="header">
        <h1>🐘 PHP Configuration Report</h1>
        <p class="subtitle">WordPress • Laravel • SOAP SUNAT</p>
        <div class="php-version">PHP <?php echo PHP_VERSION; ?> • <?php echo PHP_SAPI; ?></div>
        <div class="peru-badge">🇵🇪 Optimizado para Facturación Electrónica Perú (SUNAT)</div>
    </div>

    <div class="actions">
        <button class="btn btn-primary" onclick="window.print()">🖨️ Imprimir</button>
        <a href="?download=json" class="btn btn-secondary">📥 Descargar JSON</a>
        <a href="?download=txt" class="btn btn-secondary">📄 Descargar TXT</a>
        <button class="btn btn-secondary" onclick="location.reload()">🔄 Actualizar</button>
    </div>

    <div class="content">
        <!-- PHP Config Path Highlight -->
        <div class="config-path-highlight">
            <strong>📁 Archivo de Configuración PHP (php.ini):</strong>
            <?php echo php_ini_loaded_file() ?: 'No se encontró php.ini cargado'; ?>
        </div>

        <!-- Stats Overview -->
        <div class="stats">
            <div class="stat-card">
                <div class="number"><?php echo count($extensions); ?></div>
                <div class="label">Extensiones</div>
            </div>
            <div class="stat-card">
                <div class="number"><?php echo ini_get('memory_limit'); ?></div>
                <div class="label">Memory Limit</div>
            </div>
            <div class="stat-card">
                <div class="number"><?php echo ini_get('max_execution_time'); ?>s</div>
                <div class="label">Max Execution</div>
            </div>
            <div class="stat-card">
                <div class="number"><?php echo ini_get('upload_max_filesize'); ?></div>
                <div class="label">Max Upload</div>
            </div>
        </div>

        <!-- Tabs -->
        <div class="tabs">
            <button class="tab active" onclick="showTab(event, 'overview')">📋 Overview</button>
            <button class="tab" onclick="showTab(event, 'paths')">📁 Rutas Config</button>
            <button class="tab" onclick="showTab(event, 'wordpress')">📦 WordPress</button>
            <button class="tab" onclick="showTab(event, 'laravel')">🔴 Laravel</button>
            <button class="tab" onclick="showTab(event, 'soap')">🧾 SOAP/SUNAT</button>
            <button class="tab" onclick="showTab(event, 'extensions')">🔌 Extensiones</button>
            <button class="tab" onclick="showTab(event, 'config')">⚙️ Configuración</button>
            <button class="tab" onclick="showTab(event, 'details')">🔍 Detalles</button>
            <button class="tab" onclick="showTab(event, 'recommendations')">💡 Recomendaciones</button>
        </div>

        <!-- Tab: Overview -->
        <div id="overview" class="tab-content active">
            <div class="section">
                <h2>📋 Información General</h2>
                <div class="info-grid">
                    <div class="info-card">
                        <strong>PHP Version</strong>
                        <div class="value"><?php echo PHP_VERSION; ?></div>
                    </div>
                    <div class="info-card">
                        <strong>PHP SAPI</strong>
                        <div class="value"><?php echo PHP_SAPI; ?></div>
                    </div>
                    <div class="info-card">
                        <strong>Operating System</strong>
                        <div class="value"><?php echo PHP_OS; ?></div>
                    </div>
                    <div class="info-card">
                        <strong>Architecture</strong>
                        <div class="value"><?php echo php_uname('m'); ?></div>
                    </div>
                    <div class="info-card">
                        <strong>Zend Engine</strong>
                        <div class="value"><?php echo zend_version(); ?></div>
                    </div>
                    <div class="info-card">
                        <strong>PHP Int Size</strong>
                        <div class="value"><?php echo (PHP_INT_SIZE * 8) . ' bits'; ?></div>
                    </div>
                    <div class="info-card">
                        <strong>Server Software</strong>
                        <div class="value"><?php echo $_SERVER['SERVER_SOFTWARE'] ?? 'N/A'; ?></div>
                    </div>
                    <div class="info-card">
                        <strong>Document Root</strong>
                        <div class="value"><?php echo $_SERVER['DOCUMENT_ROOT'] ?? 'N/A'; ?></div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Tab: Config Paths -->
        <div id="paths" class="tab-content">
            <div class="section">
                <h2>📁 Rutas de Configuración PHP</h2>
                <div class="info-grid">
                    <?php foreach ($configPaths as $key => $value): ?>
                        <div class="info-card">
                            <strong><?php echo $key; ?></strong>
                            <div class="value"><?php echo htmlspecialchars($value); ?></div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>

        <!-- Tab: WordPress -->
        <div id="wordpress" class="tab-content">
            <div class="section">
                <h2>📦 Validación WordPress</h2>

                <div class="validation-card">
                    <h3>🔧 Requisitos del Sistema</h3>
                    <div class="validation-item">
                        <span>PHP Version >= 7.4</span>
                        <span class="validation-status <?php echo $wpValidation['php_version'] ? 'status-ok' : 'status-fail'; ?>">
                            <?php echo $wpValidation['php_version'] ? '✔ OK' : '✗ FAIL'; ?>
                        </span>
                    </div>
                </div>

                <div class="validation-card">
                    <h3>✅ Extensiones Requeridas</h3>
                    <?php foreach ($wpValidation['required'] as $ext => $loaded): ?>
                        <div class="validation-item">
                            <span><?php echo $ext; ?></span>
                            <span class="validation-status <?php echo $loaded ? 'status-ok' : 'status-fail'; ?>">
                                <?php echo $loaded ? '✔ Instalada' : '✗ Faltante'; ?>
                            </span>
                        </div>
                    <?php endforeach; ?>
                </div>

                <div class="validation-card">
                    <h3>⭐ Extensiones Recomendadas</h3>
                    <?php foreach ($wpValidation['recommended'] as $ext => $loaded): ?>
                        <div class="validation-item">
                            <span><?php echo $ext; ?></span>
                            <span class="validation-status <?php echo $loaded ? 'status-ok' : 'status-fail'; ?>">
                                <?php echo $loaded ? '✔ Instalada' : '⚠ No instalada'; ?>
                            </span>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>

        <!-- Tab: Laravel -->
        <div id="laravel" class="tab-content">
            <div class="section">
                <h2>🔴 Validación Laravel</h2>

                <?php if ($laravelValidation['laravel_detected']): ?>
                    <div class="alert alert-info">
                        <div class="icon">📦</div>
                        <div class="content">
                            <div class="category">Laravel Detectado</div>
                            <strong>Versión instalada: Laravel <?php echo $laravelValidation['laravel_version']; ?></strong>
                        </div>
                    </div>
                <?php else: ?>
                    <div class="alert alert-warning">
                        <div class="icon">⚠️</div>
                        <div class="content">
                            <div class="category">Laravel No Detectado</div>
                            No se detectó una instalación de Laravel. Validando con requisitos de Laravel 10+ (por defecto).
                        </div>
                    </div>
                <?php endif; ?>

                <div class="validation-card">
                    <h3>🔧 Requisitos del Sistema</h3>
                    <div class="validation-item">
                        <span>PHP Version >= <?php echo $laravelValidation['php_requirement']; ?> (<?php echo $laravelValidation['php_requirement_name']; ?>)</span>
                        <span class="validation-status <?php echo $laravelValidation['php_version'] ? 'status-ok' : 'status-fail'; ?>">
                            <?php echo $laravelValidation['php_version'] ? '✔ OK' : '✗ FAIL'; ?>
                        </span>
                    </div>
                    <div class="validation-item">
                        <span>Memory Limit >= 256M</span>
                        <span class="validation-status <?php echo $laravelValidation['memory'] ? 'status-ok' : 'status-fail'; ?>">
                            <?php echo $laravelValidation['memory'] ? '✔ OK' : '✗ FAIL'; ?>
                        </span>
                    </div>
                </div>

                <div class="validation-card">
                    <h3>✅ Extensiones Requeridas</h3>
                    <?php foreach ($laravelValidation['required'] as $ext => $loaded): ?>
                        <div class="validation-item">
                            <span><?php echo $ext; ?></span>
                            <span class="validation-status <?php echo $loaded ? 'status-ok' : 'status-fail'; ?>">
                                <?php echo $loaded ? '✔ Instalada' : '✗ Faltante'; ?>
                            </span>
                        </div>
                    <?php endforeach; ?>
                </div>

                <div class="validation-card">
                    <h3>⭐ Extensiones Recomendadas (Performance)</h3>
                    <?php foreach ($laravelValidation['recommended'] as $ext => $loaded): ?>
                        <div class="validation-item">
                            <span><?php echo $ext; ?></span>
                            <span class="validation-status <?php echo $loaded ? 'status-ok' : 'status-fail'; ?>">
                                <?php echo $loaded ? '✔ Instalada' : '⚠ No instalada'; ?>
                            </span>
                        </div>
                    <?php endforeach; ?>
                </div>

                <div class="alert alert-info">
                    <div class="icon">💡</div>
                    <div class="content">
                        <div class="category">Tabla de Requisitos Laravel → PHP</div>
                        <ul style="margin-top: 10px; padding-left: 20px;">
                            <li><strong>Laravel 8:</strong> PHP >= 7.3</li>
                            <li><strong>Laravel 9:</strong> PHP >= 8.0</li>
                            <li><strong>Laravel 10:</strong> PHP >= 8.1</li>
                            <li><strong>Laravel 11:</strong> PHP >= 8.2</li>
                            <li><strong>Laravel 12:</strong> PHP >= 8.3 (proyectado)</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>

        <!-- Tab: SOAP/SUNAT -->
        <div id="soap" class="tab-content">
            <div class="section">
                <h2>🧾 Validación SOAP / SUNAT (Perú)</h2>

                <div class="validation-card">
                    <h3>🔐 Extensiones Críticas para Facturación Electrónica</h3>
                    <div class="validation-item">
                        <span>SOAP Extension</span>
                        <span class="validation-status <?php echo $soapValidation['soap_enabled'] ? 'status-ok' : 'status-fail'; ?>">
                            <?php echo $soapValidation['soap_enabled'] ? '✔ Instalada' : '✗ CRÍTICO: Faltante'; ?>
                        </span>
                    </div>
                    <div class="validation-item">
                        <span>OpenSSL Extension</span>
                        <span class="validation-status <?php echo $soapValidation['openssl_enabled'] ? 'status-ok' : 'status-fail'; ?>">
                            <?php echo $soapValidation['openssl_enabled'] ? '✔ Instalada' : '✗ CRÍTICO: Faltante'; ?>
                        </span>
                    </div>
                    <div class="validation-item">
                        <span>DOM Extension</span>
                        <span class="validation-status <?php echo $soapValidation['dom_enabled'] ? 'status-ok' : 'status-fail'; ?>">
                            <?php echo $soapValidation['dom_enabled'] ? '✔ Instalada' : '✗ Faltante'; ?>
                        </span>
                    </div>
                    <div class="validation-item">
                        <span>XML Extension</span>
                        <span class="validation-status <?php echo $soapValidation['xml_enabled'] ? 'status-ok' : 'status-fail'; ?>">
                            <?php echo $soapValidation['xml_enabled'] ? '✔ Instalada' : '✗ Faltante'; ?>
                        </span>
                    </div>
                </div>

                <div class="validation-card">
                    <h3>⚙️ Configuración SOAP</h3>
                    <div class="validation-item">
                        <span>allow_url_fopen (Requerido para WSDL)</span>
                        <span class="validation-status <?php echo $soapValidation['allow_url_fopen'] ? 'status-ok' : 'status-fail'; ?>">
                            <?php echo $soapValidation['allow_url_fopen'] ? '✔ Habilitado' : '✗ CRÍTICO: Deshabilitado'; ?>
                        </span>
                    </div>
                    <div class="validation-item">
                        <span>Socket Timeout >= 60s (Evita timeouts SUNAT)</span>
                        <span class="validation-status <?php echo $soapValidation['socket_timeout'] ? 'status-ok' : 'status-fail'; ?>">
                            <?php echo $soapValidation['socket_timeout'] ? '✔ OK' : '⚠ Muy bajo'; ?>
                        </span>
                    </div>
                    <div class="validation-item">
                        <span>Max Execution Time >= 120s</span>
                        <span class="validation-status <?php echo $soapValidation['max_execution_time'] ? 'status-ok' : 'status-fail'; ?>">
                            <?php echo $soapValidation['max_execution_time'] ? '✔ OK' : '⚠ Muy bajo'; ?>
                        </span>
                    </div>
                </div>

                <div class="validation-card">
                    <h3>🔒 Información OpenSSL</h3>
                    <div class="info-card">
                        <strong>Versión OpenSSL</strong>
                        <div class="value"><?php echo $soapValidation['openssl_version']; ?></div>
                    </div>
                    <?php if (isset($soapValidation['openssl_compatible'])): ?>
                        <div class="validation-item">
                            <span>Compatible con SUNAT (>= 1.0.1)</span>
                            <span class="validation-status <?php echo $soapValidation['openssl_compatible'] ? 'status-ok' : 'status-fail'; ?>">
                                <?php echo $soapValidation['openssl_compatible'] ? '✔ Compatible' : '✗ Actualizar'; ?>
                            </span>
                        </div>
                    <?php endif; ?>
                </div>

                <div class="alert alert-info">
                    <div class="icon">💡</div>
                    <div class="content">
                        <div class="category">Información SUNAT</div>
                        Para facturación electrónica en Perú se requiere SOAP con SSL/TLS para comunicación segura con los servicios de SUNAT (OSE). Asegúrate de tener certificados digitales válidos.
                    </div>
                </div>
            </div>
        </div>

        <!-- Tab: Extensions -->
        <div id="extensions" class="tab-content">
            <div class="section">
                <h2>🔌 Extensiones Cargadas (<?php echo count($extensions); ?>)</h2>

                <div class="search-box">
                    <input type="text" id="extensionSearch" placeholder="Buscar extensión..." onkeyup="filterExtensions()">
                    <span class="search-icon">🔍</span>
                </div>

                <div class="extensions-grid" id="extensionsList">
                    <?php foreach ($extensions as $ext): ?>
                        <div class="ext-badge" data-ext="<?php echo strtolower($ext); ?>">
                            <?php echo $ext; ?>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>

        <!-- Tab: Configuration -->
        <div id="config" class="tab-content">
            <div class="section">
                <h2>⚙️ Configuración Crítica</h2>
                <table>
                    <thead>
                    <tr>
                        <th>Directiva</th>
                        <th>Valor</th>
                    </tr>
                    </thead>
                    <tbody>
                    <?php foreach ($config as $key => $value): ?>
                        <tr>
                            <td><strong><?php echo $key; ?></strong></td>
                            <td><?php echo htmlspecialchars($value); ?></td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Tab: Details -->
        <div id="details" class="tab-content">
            <div class="section">
                <h2>🔍 Detalles de Extensiones Importantes</h2>
                <?php if (!empty($extDetails)): ?>
                    <div class="info-grid">
                        <?php foreach ($extDetails as $extName => $extInfo): ?>
                            <div class="info-card">
                                <strong><?php echo strtoupper($extName); ?></strong>
                                <div class="value">
                                    <?php foreach ($extInfo as $key => $value): ?>
                                        <div style="margin: 5px 0; font-size: 0.9rem;">
                                            <span style="color: #667eea;"><?php echo ucfirst($key); ?>:</span>
                                            <?php echo htmlspecialchars($value); ?>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php else: ?>
                    <p>No hay detalles adicionales disponibles para las extensiones cargadas.</p>
                <?php endif; ?>
            </div>
        </div>

        <!-- Tab: Recommendations -->
        <div id="recommendations" class="tab-content">
            <div class="section">
                <h2>💡 Recomendaciones</h2>
                <?php
                $recsByCategory = [];
                foreach ($recommendations as $rec) {
                    $recsByCategory[$rec['category']][] = $rec;
                }
                foreach ($recsByCategory as $category => $recs):
                    ?>
                    <h3 style="color: #667eea; margin: 25px 0 15px 0; font-size: 1.3rem;">📌 <?php echo $category; ?></h3>
                    <?php foreach ($recs as $rec): ?>
                    <div class="alert alert-<?php echo $rec['type']; ?>">
                        <div class="icon"><?php echo $rec['icon']; ?></div>
                        <div class="content">
                            <div><?php echo $rec['message']; ?></div>
                        </div>
                    </div>
                <?php endforeach; ?>
                <?php endforeach; ?>
            </div>
        </div>
    </div>

    <div class="footer">
        <p><strong>PHP Configuration Report Generator - Peru Edition</strong></p>
        <p>🐘 PHP • 📦 WordPress • 🔴 Laravel • 🧾 SOAP/SUNAT</p>
        <p class="timestamp">Generado: <?php echo date('Y-m-d H:i:s'); ?> 🇵🇪</p>
    </div>
</div>

<script>
    function showTab(evt, tabName) {
        const tabContents = document.getElementsByClassName('tab-content');
        for (let i = 0; i < tabContents.length; i++) {
            tabContents[i].classList.remove('active');
        }

        const tabs = document.getElementsByClassName('tab');
        for (let i = 0; i < tabs.length; i++) {
            tabs[i].classList.remove('active');
        }

        document.getElementById(tabName).classList.add('active');
        evt.currentTarget.classList.add('active');
    }

    function filterExtensions() {
        const input = document.getElementById('extensionSearch');
        const filter = input.value.toLowerCase();
        const badges = document.querySelectorAll('.ext-badge');

        badges.forEach(badge => {
            const text = badge.getAttribute('data-ext');
            if (text.includes(filter)) {
                badge.style.display = '';
            } else {
                badge.style.display = 'none';
            }
        });
    }

    // Easter egg: Konami code
    let konamiCode = [];
    const konamiPattern = ['ArrowUp', 'ArrowUp', 'ArrowDown', 'ArrowDown', 'ArrowLeft', 'ArrowRight', 'ArrowLeft', 'ArrowRight', 'b', 'a'];

    document.addEventListener('keydown', (e) => {
        konamiCode.push(e.key);
        konamiCode = konamiCode.slice(-10);

        if (JSON.stringify(konamiCode) === JSON.stringify(konamiPattern)) {
            alert('🎉 ¡CÓDIGO KONAMI ACTIVADO!\n\n"Con gran poder de PHP viene una gran responsabilidad" 🇵🇪\n\nPHP Version: <?php echo PHP_VERSION; ?>\nExtensions: <?php echo count($extensions); ?>\nSOAP: <?php echo $soapValidation['soap_enabled'] ? 'OK' : 'Falta instalar'; ?>');
            konamiCode = [];
        }
    });

    console.log('%c🐘 PHP Config Report - Peru Edition', 'font-size: 20px; color: #667eea; font-weight: bold;');
    console.log('%c🇵🇪 Optimizado para SUNAT', 'font-size: 14px; color: #764ba2;');
    console.log('%cPista: Intenta el código Konami ↑↑↓↓←→←→BA', 'font-size: 12px; color: #999;');
</script>
</body>
</html>
