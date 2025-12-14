# Configuración de GitHub Actions para Stack Facturador Smart

## 📋 Tabla de Contenidos
1. [Prerrequisitos](#prerrequisitos)
2. [Configuración del Repositorio](#configuración-del-repositorio)
3. [Configuración de Secrets](#configuración-de-secrets)
4. [Configuración de Environments](#configuración-de-environments)
5. [Configuración de Servidores](#configuración-de-servidores)
6. [Proceso de Despliegue](#proceso-de-despliegue)
7. [Solución de Problemas](#solución-de-problemas)

## 🔧 Prerrequisitos

### 1. Cuentas y Servicios
- ✅ Cuenta de GitHub
- ✅ Repositorio creado en GitHub
- ✅ Acceso a los servidores de staging y producción
- ✅ Permisos de administrador en el repositorio

### 2. Configuración de Servidores
```bash
# En cada servidor (staging y producción)
sudo useradd -m -s /bin/bash deploy-user
sudo usermod -aG docker deploy-user
sudo mkdir -p /opt/facturador-smart
sudo chown -R deploy-user:deploy-user /opt/facturador-smart
```

### 3. Configuración de SSH
```bash
# Generar clave SSH para deploy
ssh-keygen -t rsa -b 4096 -f ~/.ssh/github_deploy_key

# Copiar clave pública a los servidores
ssh-copy-id -i ~/.ssh/github_deploy_key.pub deploy-user@staging.fact.solucionessystem.com
ssh-copy-id -i ~/.ssh/github_deploy_key.pub deploy-user@fact.solucionessystem.com
```

## 🚀 Configuración del Repositorio

### 1. Crear Repositorio en GitHub
1. Ir a GitHub → New Repository
2. Nombre: `stack-facturador-smart`
3. Descripción: `Sistema de facturación electrónica Perú`
4. Tipo: Público o Privado
5. Inicializar con README: ✅

### 2. Subir Código al Repositorio
```bash
# Clonar repositorio localmente
git clone https://github.com/tu-usuario/stack-facturador-smart.git
cd stack-facturador-smart

# Copiar todos los archivos del proyecto
cp -r /ruta/completa/project_fact_soluciones/* .

# Commit inicial
git add .
git commit -m "Initial commit: Stack Facturador Smart"
git push origin main
```

### 3. Configurar Branch Protection
1. Ir a Settings → Branches
2. Agregar branch protection rule para `main`
3. Requerir aprobaciones: 1 reviewer
4. Requerir status checks: ✅
5. Requerir conversación lineal: ✅

## 🔑 Configuración de Secrets

### 1. Secrets para Staging
Ir a Settings → Secrets → Actions → New repository secret

```yaml
# Secrets de Staging
STAGING_HOST: "staging.fact.solucionessystem.com"
STAGING_USER: "deploy-user"
STAGING_SSH_KEY: |
  -----BEGIN OPENSSH PRIVATE KEY-----
  [tu clave privada SSH completa]
  -----END OPENSSH PRIVATE KEY-----
```

### 2. Secrets para Producción
```yaml
# Secrets de Producción
PRODUCTION_HOST: "fact.solucionessystem.com"
PRODUCTION_USER: "deploy-user"
PRODUCTION_SSH_KEY: |
  -----BEGIN OPENSSH PRIVATE KEY-----
  [tu clave privada SSH completa]
  -----END OPENSSH PRIVATE KEY-----
```

### 3. Secrets de Aplicación (Opcionales)
```yaml
# Secrets de la aplicación
MYSQL_PASSWORD: "tu_password_mysql"
MYSQL_ROOT_PASSWORD: "tu_root_password"
APP_KEY: "base64:tu_app_key_laravel"
SUNAT_USER: "tu_usuario_sunat"
SUNAT_PASSWORD: "tu_password_sunat"
```

## 🌍 Configuración de Environments

### 1. Crear Environment: Staging
1. Ir a Settings → Environments → New environment
2. Nombre: `staging`
3. Protection rules: 
   - Required reviewers: agregar 1 reviewer
   - Wait timer: 0 minutes

### 2. Crear Environment: Production
1. Ir a Settings → Environments → New environment
2. Nombre: `production`
3. Protection rules:
   - Required reviewers: agregar 2 reviewers
   - Wait timer: 5 minutes (para confirmación manual)

### 3. Agregar Secrets a Environments
Para cada environment, agregar los secrets correspondientes:
- Staging: `STAGING_HOST`, `STAGING_USER`, `STAGING_SSH_KEY`
- Production: `PRODUCTION_HOST`, `PRODUCTION_USER`, `PRODUCTION_SSH_KEY`

## 🖥️ Configuración de Servidores

### 1. Preparación del Servidor
```bash
# Instalar dependencias
sudo apt-get update
sudo apt-get install -y docker.io docker-compose git curl

# Configurar usuario deploy
sudo useradd -m -s /bin/bash deploy-user
sudo usermod -aG docker deploy-user

# Clonar repositorio
cd /opt
sudo git clone https://github.com/tu-usuario/stack-facturador-smart.git facturador-smart
sudo chown -R deploy-user:deploy-user /opt/facturador-smart
```

### 2. Configuración de Docker
```bash
# Iniciar servicio Docker
sudo systemctl start docker
sudo systemctl enable docker

# Configurar red externa (si no existe)
docker network create proxynet || true

# Dar permisos al usuario
sudo usermod -aG docker $USER
newgrp docker
```

### 3. Configuración de SSH para GitHub Actions
```bash
# En tu máquina local, copia la clave privada
cat ~/.ssh/github_deploy_key

# Copia todo el contenido y pégalo en GitHub Secrets como STAGING_SSH_KEY y PRODUCTION_SSH_KEY
```

## 🔄 Proceso de Despliegue

### 1. Flujo Automático (CI/CD)
```
Push a main → Build → Test → Deploy Staging → Deploy Production (con aprobación)
```

### 2. Flujo Manual
1. Ir a Actions → Despliegue Manual
2. Seleccionar environment: staging o production
3. Confirmar: si
4. Esperar que complete

### 3. Verificación del Despliegue
```bash
# Verificar servicios después del despliegue
docker ps
curl http://localhost:8080/php_report.php
docker exec fpm1 php artisan --version
docker logs nginx1 --tail 20
```

## 📊 Monitoreo

### 1. Ver Estado de Pipelines
1. Ir a Actions
2. Ver historial de ejecuciones
3. Click en cada run para ver detalles
4. Ver logs de cada paso

### 2. Verificar Deployments
1. Ir a Actions → Environments
2. Ver historial de despliegues
3. Ver aprobaciones pendientes

### 3. Configurar Notificaciones
1. Ir a Settings → Notifications
2. Configurar notificaciones por email
3. Configurar webhook para Slack (opcional)

## 🚨 Solución de Problemas

### Problema 1: Permiso denegado SSH
```bash
# Solución: Verificar permisos de clave SSH
chmod 600 ~/.ssh/github_deploy_key
chmod 644 ~/.ssh/github_deploy_key.pub

# En el servidor:
chmod 700 ~/.ssh
chmod 600 ~/.ssh/authorized_keys
```

### Problema 2: Docker permission denied
```bash
# Solución: Agregar usuario al grupo docker
sudo usermod -aG docker deploy-user
newgrp docker
```

### Problema 3: Network proxynet no encontrada
```bash
# Solución: Crear la red manualmente
docker network create proxynet
```

### Problema 4: Build falla por falta de extensiones
```bash
# Solución: Actualizar Dockerfile.fpm
# Agregar las extensiones faltantes:
docker-php-ext-install zip soap gd pdo_mysql mysqli bcmath
```

### Problema 5: Composer install falla
```bash
# Solución: Verificar permisos y memoria
docker exec fpm1 php -d memory_limit=-1 /usr/local/bin/composer install
```

## 📈 Mejores Prácticas

### 1. Seguridad
- ✅ Nunca committear archivos .env o secrets
- ✅ Usar siempre GitHub Secrets
- ✅ Revisar regularmente los logs de acceso
- ✅ Mantener actualizadas las dependencias

### 2. Desarrollo
- ✅ Hacer pull requests en lugar de push directo a main
- ✅ Solicitar review para cambios importantes
- ✅ Probar siempre en staging antes de producción
- ✅ Mantener actualizada la documentación

### 3. Despliegue
- ✅ Siempre verificar el estado después del despliegue
- ✅ Tener un plan de rollback listo
- ✅ Mantener backups regulares
- ✅ Monitorear los servicios después del deploy

## 🎯 Workflow de Trabajo Recomendado

### Para Desarrolladores:
1. Crear branch para nueva feature
2. Hacer cambios y commits
3. Subir branch a GitHub
4. Crear Pull Request
5. Esperar aprobación
6. Merge a main (dispara CI/CD automático)

### Para DevOps:
1. Monitorear pipelines automáticos
2. Revisar y aprobar despliegues a producción
3. Verificar logs y métricas
4. Realizar mantenimiento preventivo

## 📞 Soporte y Recursos

### Recursos Útiles:
- [GitHub Actions Documentation](https://docs.github.com/actions)
- [Docker Documentation](https://docs.docker.com/)
- [Laravel Documentation](https://laravel.com/docs)
- [SUNAT Perú](https://www.sunat.gob.pe/)

### Contacto:
- Email: devops@solucionessystem.com
- Issues: [GitHub Issues](https://github.com/tu-usuario/stack-facturador-smart/issues)
- Documentación: Este archivo

---

## 🎉 ¡Felicidades!
Si has llegado hasta aquí, ya tienes configurado un pipeline CI/CD completo con GitHub Actions para tu proyecto de facturación electrónica.

### Próximos pasos:
1. ✅ Configurar todos los secrets
2. ✅ Probar el primer despliegue a staging
3. ✅ Verificar que todo funcione correctamente
4. ✅ Configurar notificaciones
5. ✅ Documentar procesos específicos de tu equipo

**¡Buena suerte con tu proyecto! 🇵🇪**

---

**Última actualización**: 2025-12-13
**Versión**: 1.0
**Mantenedor**: Equipo DevOps - Soluciones System Perú 🇵🇪