# GitHub Actions - Stack Facturador Smart

## 📋 Descripción
Este directorio contiene los workflows de GitHub Actions para automatizar el CI/CD del proyecto Stack Facturador Smart.

## 🔄 Workflows Disponibles

### 1. CI/CD Pipeline (ci-cd.yml)
**Descripción**: Pipeline principal de integración y despliegue continuo
**Trigger**: Push a main/master y Pull Requests
**Jobs**:
- Build and Test: Construye imágenes Docker y ejecuta pruebas
- Deploy Staging: Despliega automáticamente a staging
- Deploy Production: Despliega a producción con aprobación

### 2. Despliegue Manual (manual-deploy.yml)
**Descripción**: Permite desplegar manualmente a cualquier entorno
**Trigger**: Manual (workflow_dispatch)
**Uso**: Ir a Actions → Despliegue Manual → Run workflow

### 3. Escaneo de Seguridad (security-scan.yml)
**Descripción**: Escanea vulnerabilidades en dependencias e imágenes
**Trigger**: Push, PR y programado (domingos 2 AM)
**Jobs**:
- Security Scan: Escanea PHP dependencies, Docker images y código

### 4. Backup Automático (backup.yml)
**Descripción**: Crea backups automáticos de bases de datos y archivos
**Trigger**: Programado (diario 3 AM) y manual
**Jobs**:
- Backup: Crea backups en staging y producción

### 5. Limpieza Automática (cleanup.yml)
**Descripción**: Limpia imágenes Docker y caché periódicamente
**Trigger**: Programado (domingos 4 AM) y manual
**Jobs**:
- Cleanup: Limpia contenedores, imágenes y volúmenes sin usar

## 🚀 Uso Rápido

### Para Desarrolladores:
1. Haz push a main → Se ejecuta CI/CD automático
2. Para deploy manual: Actions → Despliegue Manual
3. Para ver logs: Actions → Click en el workflow

### Para DevOps:
1. Revisar Actions diariamente
2. Aprobar despliegues a producción
3. Monitorear backups y limpiezas

## 🔧 Configuración

### Secrets Requeridos:
```yaml
STAGING_HOST: "staging.fact.solucionessystem.com"
STAGING_USER: "deploy-user"
STAGING_SSH_KEY: "[clave SSH privada]"
PRODUCTION_HOST: "fact.solucionessystem.com"
PRODUCTION_USER: "deploy-user"
PRODUCTION_SSH_KEY: "[clave SSH privada]"
```

### Environments:
- `staging`: Despliegue automático después de build
- `production`: Despliegue con aprobación manual

## 📊 Monitoreo

### Ver Estado:
1. Ir a Actions
2. Click en workflow específico
3. Ver logs de cada paso

### Notificaciones:
- Configurar en Settings → Notifications
- Email automático en éxito/fallo
- Slack webhook opcional

## 🚨 Solución de Problemas

### Problemas Comunes:
1. **SSH Permission Denied**: Verificar permisos de clave SSH
2. **Docker Build Failed**: Verificar Dockerfile y recursos
3. **Deploy Timeout**: Verificar conectividad con servidores
4. **Secrets Missing**: Verificar que todos los secrets estén configurados

### Logs de Depuración:
```bash
# En el servidor
docker logs nginx1 --tail 50
docker logs fpm1 --tail 50
docker logs mariadb1 --tail 50
```

## 📈 Mejores Prácticas

1. ✅ Nunca committear secrets
2. ✅ Usar siempre GitHub Secrets
3. ✅ Probar en staging antes de producción
4. ✅ Revisar logs después de cada deploy
5. ✅ Mantener backups actualizados

## 🔗 Recursos

- [GitHub Actions Docs](https://docs.github.com/actions)
- [Docker Documentation](https://docs.docker.com/)
- [Configuración Completa](GITHUB_ACTIONS_SETUP.md)

---

**Mantenedor**: Equipo DevOps - Soluciones System Perú 🇵🇪
**Última actualización**: 2025-12-13