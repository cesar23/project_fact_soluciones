<?php

/**
 * phpMyAdmin - Configuración para Proxy Reverso
 * Ubicación: services/phpmyadmin/config.user.inc.php
 *
 * Este archivo soluciona el error de HTTPS mismatch
 * cuando phpMyAdmin está detrás de Nginx Proxy Manager
 */

// ============================================================================
// DETECTAR HTTPS DESDE EL PROXY REVERSO
// ============================================================================

// Detectar si la petición viene por HTTPS desde el proxy
if (isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] === 'https') {
  $_SERVER['HTTPS'] = 'on';
}

// Alternativa: Detectar por el puerto
if (isset($_SERVER['HTTP_X_FORWARDED_PORT']) && $_SERVER['HTTP_X_FORWARDED_PORT'] == '443') {
  $_SERVER['HTTPS'] = 'on';
}

// Detectar por header de Cloudflare (si lo usas)
if (isset($_SERVER['HTTP_CF_VISITOR'])) {
  $cfVisitor = json_decode($_SERVER['HTTP_CF_VISITOR']);
  if (isset($cfVisitor->scheme) && $cfVisitor->scheme === 'https') {
    $_SERVER['HTTPS'] = 'on';
  }
}

// ============================================================================
// CONFIGURACIÓN DE URL ABSOLUTA
// ============================================================================

// URL pública de phpMyAdmin
#$cfg['PmaAbsoluteUri'] = 'https://phpmyadmin.rog.pe/';

// ============================================================================
// SEGURIDAD
// ============================================================================

// Forzar SSL cuando se detecta proxy HTTPS
#$cfg['ForceSSL'] = true;

// No verificar permisos de configuración (mejora performance)
$cfg['CheckConfigurationPermissions'] = false;

// ============================================================================
// SESIÓN Y COOKIES
// ============================================================================

// Aumentar validez de sesión a 2 horas (7200 segundos)
$cfg['LoginCookieValidity'] = 7200;

// Configuración de cookies para HTTPS
$cfg['CookieSameSite'] = 'Lax';

// ============================================================================
// CONFIGURACIONES ADICIONALES (OPCIONAL)
// ============================================================================

// Tiempo de espera de queries (30 segundos)
$cfg['ExecTimeLimit'] = 300;

// Máximo de rows a mostrar
$cfg['MaxRows'] = 50;

// Permitir login sin password en desarrollo (CAMBIAR EN PRODUCCIÓN)
// $cfg['AllowNoPassword'] = true;

// Deshabilitar el aviso de nueva versión
$cfg['VersionCheck'] = false;

// ============================================================================
// CONFIGURACIÓN DE SUBIDA DE ARCHIVOS
// ============================================================================

// Tamaño máximo de subida (debe coincidir con PHP y Nginx)
$cfg['UploadDir'] = '';
$cfg['SaveDir'] = '';

// ============================================================================
// TEMA Y APARIENCIA
// ============================================================================

// Tema por defecto
$cfg['ThemeDefault'] = 'pmahomme';

// ============================================================================
// DEBUG (DESACTIVAR EN PRODUCCIÓN)
// ============================================================================

// Mostrar errores PHP (solo desarrollo)
// $cfg['Error_Handler'] = ['display' => true];

?>