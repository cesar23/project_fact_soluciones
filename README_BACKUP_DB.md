# 📚 Documentación: Script de Backup de Bases de Datos

## 📋 Descripción General

Script avanzado para realizar backups automáticos de múltiples bases de datos MariaDB/MySQL desde contenedores Docker. Utiliza un sistema de carpeta temporal para organizar los archivos SQL antes de comprimirlos en un único archivo tar.gz, optimizando el espacio y facilitando la gestión de backups.

---

## 🆕 Cambios Principales (Versión 3.0)

### ✨ Nuevas Funcionalidades

1. **Sistema de carpeta temporal**
   - Crea carpeta temporal `facturador_db_FECHA` para organizar backups
   - Extrae todas las BDs en formato .sql sin comprimir primero
   - Comprime todo en un único archivo tar.gz al final
   - Limpia automáticamente la carpeta temporal después

2. **Compresión unificada con tar.gz**
   - Todos los backups de BDs en un solo archivo comprimido
   - Uso de `pv` para mostrar progreso de compresión en tiempo real
   - Nomenclatura clara: `facturador_db_FECHA.tar.gz`
   - Rotación de archivos tar.gz completos

3. **Soporte para múltiples bases de datos**
   - Procesa múltiples BDs en un solo script
   - Configuración simple mediante array
   - Todos los archivos .sql organizados en carpeta temporal

4. **Validación previa de bases de datos**
   - Verifica que cada BD existe antes del backup
   - Informa si una BD no está disponible
   - Continúa con las demás BDs si una falla

5. **Reporte detallado y estadísticas**
   - Resumen de backups exitosos y fallidos
   - Información de tamaño del archivo tar.gz final
   - Lista de todas las BDs incluidas en el backup
   - Historial de backups tar.gz disponibles

---

## 🚀 Uso del Script

### Configuración Básica

#### 1. Definir Bases de Datos a Respaldar

Edita el array `DB_NAMES` en el script (línea ~281):

```bash
# Array de nombres de bases de datos a respaldar
DB_NAMES=("smart1" "tenancy")
```

**Para agregar más bases de datos:**

```bash
# Ejemplo con 4 bases de datos
DB_NAMES=("smart1" "tenancy" "analytics" "logs")
```

**Para respaldar una sola base de datos:**

```bash
# Ejemplo con una sola BD
DB_NAMES=("smart1")
```

#### 2. Configuración de Parámetros

```bash
# Contenedor de MariaDB
CONTAINER_NAME="${MYSQL_CONTAINER}"

# Credenciales (desde .env)
DB_USER="root"
DB_PASSWORD="${MYSQL_ROOT_PASSWORD}"

# Directorio de backups
DIR_BACKUP="/home/cesar/backup-bd-facturador"

# Cantidad de backups a mantener POR CADA BD
MAX_BACKUPS=10
```

---

## 📁 Estructura de Archivos de Backup

### Nomenclatura

Los backups tar.gz se nombran con el siguiente formato:

```
facturador_db_{fecha_hora}.tar.gz
```

**Ejemplos:**

```
facturador_db_2025-10-28_140530.tar.gz
facturador_db_2025-10-27_140530.tar.gz
facturador_db_2025-10-26_140530.tar.gz
```

### Contenido de cada archivo tar.gz

Cada archivo tar.gz contiene una carpeta con todos los archivos SQL:

```
facturador_db_2025-10-28_140530.tar.gz
└── facturador_db_2025-10-28_140530/
    ├── smart1.sql
    ├── tenancy_demo.sql
    └── tenancy_tienda.sql
```

### Organización en Disco

**Directorio principal:**
```
/home/cesar/backup-bd-facturador/
├── facturador_db_2025-10-28_140530.tar.gz
├── facturador_db_2025-10-27_140530.tar.gz
├── facturador_db_2025-10-26_140530.tar.gz
├── facturador_db_2025-10-25_140530.tar.gz
└── ... (hasta MAX_BACKUPS archivos)
```

**Proceso durante la ejecución:**
```
/home/cesar/backup-bd-facturador/
├── facturador_db_2025-10-28_140530/    <- Carpeta temporal (eliminada al final)
│   ├── smart1.sql
│   ├── tenancy_demo.sql
│   └── tenancy_tienda.sql
└── facturador_db_2025-10-28_140530.tar.gz  <- Archivo final
```

---

## 🔧 Ejecución del Script

### Método 1: Ejecución Manual

```bash
# Dar permisos de ejecución (primera vez)
chmod +x backup_db_facturador.sh

# Ejecutar el script
./backup_db_facturador.sh
```

### Método 2: Programar con Cron

Para ejecutar automáticamente todos los días a las 2:00 AM:

```bash
# Editar crontab
crontab -e

# Agregar la siguiente línea
0 2 * * * /ruta/completa/backup_db_facturador.sh >> /var/log/backup_db.log 2>&1
```

---

## 📊 Interpretación del Output

### Ejemplo de Ejecución Exitosa

```
╔══════════════════════════════════════════════════════════════╗
║        💾 BACKUP DE BASES DE DATOS FACTURADOR          ║
╚══════════════════════════════════════════════════════════════╝

[INFO] Iniciando proceso de backup de múltiples bases de datos
  ▶ Bases de datos a respaldar: 3
  ▶ Lista: smart1 tenancy_demo tenancy_tienda
────────────────────────────────────────────────────────────────

🗂  PASO 1: Preparando entorno
────────────────────────────────────────────────────────────────
[INFO] Usando directorio existente: /home/cesar/backup-bd-facturador
[INFO] Carpeta temporal creada: facturador_db_2025-10-28_140530
[SUCCESS] Contenedor 'smart1-mariadb1-1' está corriendo correctamente

💾 PASO 2: Extrayendo bases de datos a carpeta temporal
────────────────────────────────────────────────────────────────
  ▶ Contenedor: smart1-mariadb1-1
  ▶ Carpeta temporal: facturador_db_2025-10-28_140530

┌─────────────────────────────────────────────────────────────┐
│  💾 Procesando: smart1
└─────────────────────────────────────────────────────────────┘
  ▶ Base de Datos: smart1
  ▶ Archivo: smart1.sql

  ⏳ Extrayendo base de datos....... [10s]
  ⏱ Tiempo: 12s
[SUCCESS] Backup de 'smart1' creado exitosamente (Tamaño: 45M)

┌─────────────────────────────────────────────────────────────┐
│  💾 Procesando: tenancy_demo
└─────────────────────────────────────────────────────────────┘
  ▶ Base de Datos: tenancy_demo
  ▶ Archivo: tenancy_demo.sql

  ⏳ Extrayendo base de datos..... [5s]
  ⏱ Tiempo: 6s
[SUCCESS] Backup de 'tenancy_demo' creado exitosamente (Tamaño: 28M)

┌─────────────────────────────────────────────────────────────┐
│  💾 Procesando: tenancy_tienda
└─────────────────────────────────────────────────────────────┘
  ▶ Base de Datos: tenancy_tienda
  ▶ Archivo: tenancy_tienda.sql

  ⏳ Extrayendo base de datos.... [4s]
  ⏱ Tiempo: 5s
[SUCCESS] Backup de 'tenancy_tienda' creado exitosamente (Tamaño: 15M)

📦 PASO 3: Comprimiendo carpeta temporal
────────────────────────────────────────────────────────────────

📦 Comprimiendo carpeta temporal con tar.gz
────────────────────────────────────────────────────────────────
  ▶ Carpeta: facturador_db_2025-10-28_140530
  ▶ Archivo de salida: facturador_db_2025-10-28_140530.tar.gz

  ⏳ Comprimiendo con barra de progreso:
  ─────────────────────────────────────────────────────────
  88.0MiB 0:00:45 [1.95MiB/s] [==================>      ] 75%
  ─────────────────────────────────────────────────────────
  ⏱ Tiempo de compresión: 58s
[SUCCESS] Compresión completada exitosamente (Tamaño: 22M)

🗑️  PASO 4: Limpiando carpeta temporal
────────────────────────────────────────────────────────────────
[SUCCESS] Carpeta temporal eliminada correctamente: facturador_db_2025-10-28_140530

🔄 PASO 5: Rotación de backups antiguos
────────────────────────────────────────────────────────────────
[INFO] Backups tar.gz actuales: 8/10

🔐 PASO 6: Ajustando permisos
────────────────────────────────────────────────────────────────
[SUCCESS] Permisos ajustados correctamente para usuario: cesar

╔══════════════════════════════════════════════════════════════╗
║              ✅ PROCESO DE BACKUP COMPLETADO              ║
╚══════════════════════════════════════════════════════════════╝

📊 ESTADÍSTICAS GENERALES:
────────────────────────────────────────────────────────────────
  📁 Ubicación: /home/cesar/backup-bd-facturador/
  ✓ Backups exitosos: 3
  ✗ Backups fallidos: 0
  🗄️ Bases de datos procesadas: 3

📦 ARCHIVO COMPRIMIDO CREADO:
────────────────────────────────────────────────────────────────
  📦 Archivo: facturador_db_2025-10-28_140530.tar.gz
  💾 Tamaño: 22M
  📂 Ruta completa: /home/cesar/backup-bd-facturador/facturador_db_2025-10-28_140530.tar.gz

🗄️  BASES DE DATOS INCLUIDAS EN EL BACKUP:
────────────────────────────────────────────────────────────────
  ✓ smart1
  ✓ tenancy_demo
  ✓ tenancy_tienda

📚 BACKUPS TAR.GZ DISPONIBLES:
────────────────────────────────────────────────────────────────
  • facturador_db_2025-10-28_140530.tar.gz (22M)
  • facturador_db_2025-10-27_140530.tar.gz (21M)
  • facturador_db_2025-10-26_140530.tar.gz (20M)
  ...

[SUCCESS] Proceso completado exitosamente - Todas las bases de datos respaldadas y comprimidas
────────────────────────────────────────────────────────────────
```

### Ejemplo con Errores

```
❌ BASES DE DATOS NO INCLUIDAS (FALLIDAS):
────────────────────────────────────────────────────────────────
  ✗ analytics

[WARNING] Proceso completado con advertencias - Algunas bases de datos no se pudieron respaldar
```

### Cómo Restaurar un Backup

Para restaurar las bases de datos desde un archivo tar.gz:

```bash
# 1. Extraer el archivo tar.gz
cd /home/cesar/backup-bd-facturador/
tar -xzf facturador_db_2025-10-28_140530.tar.gz

# 2. Entrar a la carpeta extraída
cd facturador_db_2025-10-28_140530/

# 3. Restaurar cada base de datos
docker exec -i nombre_contenedor mysql -uroot -p"password" smart1 < smart1.sql
docker exec -i nombre_contenedor mysql -uroot -p"password" tenancy_demo < tenancy_demo.sql
docker exec -i nombre_contenedor mysql -uroot -p"password" tenancy_tienda < tenancy_tienda.sql

# 4. Limpiar carpeta temporal
cd ..
rm -rf facturador_db_2025-10-28_140530/
```

---

## 🔍 Funciones Principales del Script

### 1. `msg()`
Función de logging con niveles:
- `INFO`: Información general
- `SUCCESS`: Operaciones exitosas
- `WARNING`: Advertencias
- `ERROR`: Errores críticos
- `DEBUG`: Información de depuración

### 2. `verificar_base_datos_existe()`
Verifica si una base de datos existe antes de hacer backup.

**Parámetros:**
- Container name
- Database name
- DB user
- DB password

**Retorna:**
- 0 si existe
- 1 si no existe

### 3. `backup_base_datos()`
Realiza el backup de una base de datos específica en formato SQL sin comprimir.

**Parámetros:**
- Container name
- Database name
- DB user
- DB password
- Directorio temporal
- (Timestamp ya no necesario, se usa carpeta temporal)

**Retorna:**
- 0 si exitoso
- 1 si falla

**Cambios en v3.0:**
- Ahora guarda en carpeta temporal
- Archivos .sql sin comprimir
- No retorna path y tamaño (se gestiona en el flujo principal)

### 4. `comprimir_carpeta_temporal()`
**Nueva función en v3.0**

Comprime toda la carpeta temporal con tar.gz usando pv para mostrar progreso.

**Parámetros:**
- Directorio temporal
- Directorio de backups
- Timestamp

**Retorna:**
- Path del archivo comprimido y tamaño si exitoso
- Código de error si falla

**Características:**
- Usa `pv` para barra de progreso visual
- Calcula tamaño de carpeta antes de comprimir
- Soporte para compresión sin pv (fallback)
- Mide tiempo de compresión

### 5. `rotar_backups()`
Mantiene solo los últimos N backups tar.gz.

**Parámetros:**
- Backup directory
- Max backups to keep

**Cambios en v3.0:**
- Ya no recibe nombre de BD individual
- Rota archivos tar.gz completos
- Más simple y eficiente

---

## 🛠️ Troubleshooting

### Problema: "El contenedor no está corriendo"

**Solución:**
```bash
# Verificar estado del contenedor
docker ps -a | grep mariadb

# Iniciar el contenedor si está detenido
docker start nombre_contenedor
```

### Problema: "La base de datos no existe"

**Solución:**
```bash
# Listar bases de datos disponibles
docker exec -it nombre_contenedor mysql -uroot -p -e "SHOW DATABASES;"

# Verificar el nombre exacto y actualizar el array DB_NAMES
```

### Problema: "No hay espacio en disco"

**Solución:**
```bash
# Verificar espacio disponible
df -h

# Reducir el número de backups a mantener
MAX_BACKUPS=5  # En lugar de 10

# Limpiar backups antiguos manualmente
rm /home/cesar/backup-bd-facturador/backup-*_2024-*.sql.gz
```

### Problema: "Permisos insuficientes"

**Solución:**
```bash
# Verificar permisos del directorio
ls -la /home/cesar/backup-bd-facturador/

# Corregir permisos
sudo chown -R $USER:$USER /home/cesar/backup-bd-facturador/
chmod 755 /home/cesar/backup-bd-facturador/
```

---

## 📈 Mejoras Futuras Sugeridas

1. **Backup remoto automático**
   - Sincronización a S3/storage remoto
   - Encriptación de backups

2. **Notificaciones**
   - Email al completar
   - Alertas en caso de fallos
   - Integración con Slack/Discord

3. **Compresión mejorada**
   - Opciones de compresión avanzada
   - Deduplicación

4. **Métricas y monitoreo**
   - Integración con Prometheus
   - Dashboard de backups

---

## 📞 Soporte

Para problemas o sugerencias:
1. Revisar logs del script
2. Verificar configuración de .env
3. Consultar documentación de Docker/MariaDB
4. Revisar permisos de archivos y directorios

---

## 📝 Changelog

### Versión 3.0 (2025-10-28) - Sistema de Carpeta Temporal
- ✨ **Sistema de carpeta temporal** para organizar archivos SQL
- ✨ **Compresión unificada tar.gz** de todos los backups
- ✨ **Barra de progreso con pv** durante la compresión
- ✨ Limpieza automática de carpeta temporal
- ✨ Rotación de archivos tar.gz completos
- 🔧 Nomenclatura mejorada: `facturador_db_FECHA.tar.gz`
- 📦 Un solo archivo comprimido por ejecución
- 💾 Mejor gestión de espacio en disco
- 📊 Reporte mejorado con tamaño del archivo final

### Versión 2.0 (2025-10-28)
- ✨ Soporte para múltiples bases de datos
- ✨ Validación previa de existencia de BDs
- ✨ Rotación individual por BD
- ✨ Reporte detallado con estadísticas
- 🔧 Refactorización completa del código
- 📚 Documentación completa

### Versión 1.0 (Original)
- ✅ Backup de una sola base de datos
- ✅ Compresión con gzip
- ✅ Barra de progreso con pv
- ✅ Rotación de backups básica

## 🎯 Ventajas del Sistema de Carpeta Temporal (v3.0)

### Por qué usar carpeta temporal + tar.gz

1. **Organización mejorada**
   - Todos los archivos SQL juntos en una carpeta
   - Fácil de identificar qué BDs están incluidas
   - Estructura clara al extraer

2. **Gestión de espacio eficiente**
   - Un solo archivo comprimido por fecha
   - Mejor ratio de compresión con tar.gz
   - Rotación más simple (eliminar un archivo vs múltiples)

3. **Facilidad de restauración**
   - Extraer una sola vez
   - Todos los archivos SQL disponibles inmediatamente
   - Menos pasos para restaurar múltiples BDs

4. **Mejor rendimiento**
   - Compresión en batch más eficiente
   - Menos I/O de disco
   - Proceso más predecible

5. **Mantenimiento simplificado**
   - Rotación de backups más simple
   - Menos archivos que gestionar
   - Logs más claros
