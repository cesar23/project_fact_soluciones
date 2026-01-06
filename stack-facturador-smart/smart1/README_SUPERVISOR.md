# Configuración de Supervisor para Laravel Workers

## Resumen Ejecutivo

Este documento describe la configuración del Supervisor para gestionar trabajadores de colas de Laravel en el sistema de facturación electrónica.

## Archivo: supervisor.conf

### Propósito
Gestionar 8 procesos trabajadores que ejecutan tareas en segundo plano para:

- ✅ Generación y envío de facturas electrónicas
- ✅ Comunicación con la SUNAT  
- ✅ Envío de correos electrónicos
- ✅ Procesamiento de archivos PDF
- ✅ Tareas de integración con APIs

### Configuración Clave

| Parámetro      | Valor                       | Descripción                   |
| -------------- | --------------------------- | ----------------------------- |
| **Procesos**   | 8                           | Trabajadores concurrentes     |
| **Sleep**      | 3 seg                       | Espera cuando no hay trabajos |
| **Reintentos** | 3                           | Intentos antes de fallar      |
| **Logs**       | storage/logs/supervisor.log | Archivo de registro           |
| **Timeout**    | 1 hora                      | Tiempo para terminar trabajos |

### Comandos de Gestión

```bash
# Estado de trabajadores
supervisorctl status

# Reiniciar trabajadores  
supervisorctl restart laravel-worker:*

# Ver logs en vivo
supervisorctl tail -f laravel-worker:*
```

### Integración Docker

- Contenedor: `supervisor1`
- Imagen: `rash07/php7.4-supervisor`
- Volumen: Monta este archivo en `/etc/supervisor/conf.d/`

### Beneficios

- **Alta Disponibilidad**: Autostart y autorestart
- **Escalabilidad**: 8 procesos paralelos
- **Resilencia**: Reintentos y reinicios automáticos
- **Monitoreo**: Logs centralizados
- **Gestión**: Parada limpia de procesos

---

*Documentación generada automáticamente - Actualizado: Noviembre 2025*