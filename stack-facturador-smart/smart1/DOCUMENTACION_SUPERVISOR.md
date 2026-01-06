# Documentación: supervisor.conf

## Descripción General

El archivo `supervisor.conf` es un archivo de configuración para **Supervisor**, un sistema de control de procesos que gestiona la ejecución de trabajadores de colas de Laravel en segundo plano.

## ¿Qué es Supervisor?

Supervisor es un administrador de procesos para sistemas Unix/Linux que permite:
- **Autostart**: Iniciar procesos automáticamente al arrancar el sistema
- **Autorestart**: Reiniciar procesos automáticamente si se caen
- **Gestión de múltiples procesos**: Crear y gestionar varias instancias de un proceso
- **Monitoreo**: Seguimiento del estado de los procesos
- **Logs**: Gestión centralizada de registros

## Configuración del Trabajador de Laravel

### Sección Principal: `[program:laravel-worker]`

Define un programa llamado `laravel-worker` que gestiona los procesos de cola de Laravel.

### Parámetros de Configuración

#### `process_name=%(program_name)s_%(process_num)02d`
- **Descripción**: Formato del nombre para cada proceso individual
- **Ejemplo**: `laravel-worker_00`, `laravel-worker_01`, ..., `laravel-worker_07`
- **Propósito**: Facilita la identificación y gestión de cada proceso

#### `command=php /var/www/html/artisan queue:work --sleep=3 --tries=3`
- **Comando**: Inicia un trabajador de colas de Laravel
- **Parámetros**:
  - `--sleep=3`: Tiempo de espera (3 segundos) cuando no hay trabajos en la cola
  - `--tries=3`: Número máximo de intentos antes de marcar un trabajo como fallido
- **Ruta**: `/var/www/html/artisan` - Ubicación del archivo Artisan de Laravel

#### `autostart=true`
- **Descripción**: El proceso se inicia automáticamente cuando Supervisor arranca
- **Importancia**: Asegura que los trabajadores estén siempre disponibles

#### `autorestart=true`
- **Descripción**: Reinicia automáticamente el proceso si se detiene inesperadamente
- **Beneficio**: Alta disponibilidad y recuperación ante fallos

#### `stopasgroup=true`
- **Descripción**: Cuando se detiene el programa, se detienen todos los procesos del grupo
- **Utilidad**: Asegura una parada limpia de todos los trabajadores

#### `killasgroup=true`
- **Descripción**: Cuando se mata el programa, se mata todo el grupo de procesos
- **Propósito**: Evita procesos huérfanos

#### `user=root`
- **Descripción**: El proceso se ejecuta con permisos de root
- **Consideración**: En entornos de producción, se recomienda usar un usuario con menos privilegios

#### `numprocs=8`
- **Descripción**: Número de procesos trabajadores a crear
- **Beneficio**: Procesamiento paralelo de hasta 8 trabajos simultáneos
- **Optimización**: Ideal para aplicaciones con alta carga de trabajo en segundo plano

#### `redirect_stderr=true`
- **Descripción**: Redirige la salida de error estándar a la salida estándar
- **Ventaja**: Centraliza todos los logs en un solo archivo

#### `stdout_logfile=/var/www/html/storage/logs/supervisor.log`
- **Ubicación**: `/var/www/html/storage/logs/supervisor.log`
- **Propósito**: Archivo donde se almacenan todos los logs de los trabajadores
- **Gestión**: Permite monitorear el estado y rendimiento de los trabajadores

#### `stopwaitsecs=3600`
- **Descripción**: Tiempo máximo de espera (1 hora) para que los procesos terminen gracefully
- **Propósito**: Permite que los trabajos en progreso terminen correctamente antes de forzar el cierre
- **Valor**: 3600 segundos = 1 hora

## Integración con Docker

El archivo se utiliza en el contenedor `supervisor1` definido en `docker-compose.yml`:

```yaml
supervisor1:
  image: rash07/php7.4-supervisor
  working_dir: /var/www/html
  volumes:
    - ./:/var/www/html
    - ./supervisor.conf:/etc/supervisor/conf.d/supervisor.conf
  restart: always
```

## Funciones en el Sistema de Facturación

Los trabajadores gestionados por este Supervisor se encargan de procesar en segundo plano:

1. **Envío de Comprobantes Electrónicos**
   - Generación y envío de facturas
   - Comunicación con la SUNAT
   - Procesamiento de boletas y notas de crédito/débito

2. **Tareas de Background**
   - Envío de correos electrónicos
   - Procesamiento de archivos PDF
   - Generación de reportes

3. **Integraciones con Servicios Externos**
   - Comunicación con APIs de terceros
   - Sincronización de datos
   - Actualización de estados

## Monitoreo y Mantenimiento

### Archivos de Log
- **Ubicación**: `/var/www/html/storage/logs/supervisor.log`
- **Contenido**: Registros de todos los trabajadores
- **Uso**: Monitoreo de errores, rendimiento y auditoría

### Comandos Útiles

```bash
# Ver estado de los procesos
supervisorctl status

# Reiniciar todos los trabajadores
supervisorctl restart laravel-worker:*

# Ver logs en tiempo real
supervisorctl tail -f laravel-worker:*

# Detener todos los trabajadores
supervisorctl stop laravel-worker:*

# Iniciar todos los trabajadores
supervisorctl start laravel-worker:*
```

## Consideraciones de Seguridad

1. **Usuario de Ejecución**: Actualmente se ejecuta como `root`, se recomienda crear un usuario específico
2. **Permisos**: Asegurar que el usuario tenga acceso al directorio de la aplicación
3. **Logs**: Monitorear regularmente los archivos de log para detectar problemas

## Rendimiento

- **8 Procesos Concurrentes**: Capacidad para procesar 8 trabajos simultáneamente
- **Sleep de 3 segundos**: Equilibrio entre respuesta rápida y uso eficiente de recursos
- **3 Intentos**: Tolerancia a fallos transitorios
- **Tiempo de parada de 1 hora**: Permite trabajos largos sin interrupción forzada

## Mejores Prácticas

1. **Monitoreo Regular**: Revisar los logs diariamente
2. **Ajuste de Procesos**: Ajustar `numprocs` según la carga del servidor
3. **Tiempos de Espera**: Ajustar `--sleep` según la frecuencia de trabajos
4. **Número de Intentos**: Ajustar `--tries` según la criticidad de los trabajos
5. **Gestión de Errores**: Implementar manejo adecuado de excepciones en los trabajos