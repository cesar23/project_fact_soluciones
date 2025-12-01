# 📚 Índice de Documentación - Stack Facturador Smart

## 🎯 Documentación Principal

### 📖 Documentación Completa
- **[2_STACK_DOCUMENTATION.md](3_STACK_DOCUMENTATION.md)** - Documentación técnica completa de todos los stacks
- **[README.md](stack-facturador-smart/README.md)** - Guía de inicio rápido y resumen del sistema
- **[CLAUDE.md](stack-facturador-smart/smart1/CLAUDE.md)** - Guía para desarrolladores (Laravel)

## 🏗️ Arquitectura y Stacks

### 🚀 Stack Principal - Aplicación Laravel
- **Ubicación:** `smart1/`
- **Archivo:** `smart1/docker-compose.yml`
- **Servicios:** nginx1, fpm1, mariadb1, redis1, scheduling1, supervisor1
- **Puerto:** 8080
- **Propósito:** Aplicación principal de facturación electrónica

### 🌐 Stack Nginx Proxy Manager
- **Ubicación:** `npm/`
- **Archivo:** `npm/docker-compose.yml`
- **Servicios:** npm
- **Puertos:** 80, 443, 81
- **Propósito:** Proxy reverso con SSL automático

### 🔒 Stack Cloudflare Tunnel
- **Ubicación:** `cloudflare/`
- **Archivo:** `cloudflare/docker-compose.yml`
- **Servicios:** cloudflared
- **Propósito:** Túnel seguro sin abrir puertos

### 🛠️ Stack Utilidades
- **Ubicación:** `utils/`
- **Archivo:** `utils/docker-compose.yml`
- **Servicios:** phpmyadmin
- **Puerto:** 9090
- **Propósito:** Herramientas de administración



### 🌐 URLs de Acceso
- **Aplicación Principal:** http://localhost:8080
- **Dominio Público:** https://fact.rog.pe
- **NPM Admin:** http://localhost:81
- **phpMyAdmin:** http://localhost:9090

## 🔒 Seguridad y Monitoreo

### 🛡️ Configuraciones de Seguridad
- SSL automático con Let's Encrypt
- Túnel seguro con Cloudflare
- Headers de seguridad
- Aislamiento de contenedores
- Redes Docker aisladas

### 📊 Monitoreo
- Estado de contenedores
- Uso de recursos
- Logs de errores
- Conectividad de red
- Espacio en disco

## 🚨 Solución de Problemas

### ❌ Problemas Comunes
1. **Contenedores no inician:** `docker-compose logs`
2. **Base de datos no conecta:** Verificar variables .env
3. **SSL no funciona:** Verificar configuración NPM
4. **Puertos ocupados:** Cambiar puertos en docker-compose.yml

### 🔍 Comandos de Diagnóstico
```bash
# Estado de contenedores
docker ps

# Logs de errores
docker-compose logs --tail=100

# Verificar conectividad
docker-compose exec fpm1 ping mariadb1

# Verificar recursos
docker stats
```

