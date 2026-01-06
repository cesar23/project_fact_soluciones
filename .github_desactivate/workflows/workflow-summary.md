# Resumen de Workflows - Stack Facturador Smart

## 📊 Descripción General

Este proyecto utiliza GitHub Actions para automatizar todo el ciclo de vida del desarrollo, desde CI/CD hasta mantenimiento y seguridad.

## 🔄 Workflows Disponibles

### 1. 🚀 CI/CD Pipeline (ci-cd.yml)
**Frecuencia**: Cada push a main/master y PRs
**Propósito**: Construcción, pruebas y despliegue automático
**Jobs**:
- Build and Test: Construye imágenes Docker y ejecuta pruebas
- Deploy Staging: Despliegue automático a staging
- Deploy Production: Despliegue a producción con aprobación

### 2. 🛠️ Despliegue Manual (manual-deploy.yml)
**Frecuencia**: Manual (workflow_dispatch)
**Propósito**: Despliegue manual a cualquier entorno
**Uso**: Actions → Despliegue Manual → Run workflow

### 3. 🔒 Escaneo de Seguridad (security-scan.yml)
**Frecuencia**: Push, PRs y programado (domingos 2 AM)
**Propósito**: Detectar vulnerabilidades en dependencias e imágenes
**Jobs**: Security Scan (PHP, Docker, secretos, código)

### 4. 💾 Backup Automático (backup.yml)
**Frecuencia**: Programado (diario 3 AM) y manual
**Propósito**: Crear backups de bases de datos y archivos
**Jobs**: Backup (staging y producción)

### 5. 🧹 Limpieza Automática (cleanup.yml)
**Frecuencia**: Programado (domingos 4 AM) y manual
**Propósito**: Limpiar imágenes Docker y caché
**Jobs**: Cleanup (contenedores, imágenes, volúmenes)

### 6. 📝 Generar Changelog (changelog.yml)
**Frecuencia**: Push a main y releases
**Propósito**: Generar changelog automático
**Jobs**: Generate Changelog

### 7. 🏷️ Marcar Issues Inactivos (stale.yml)
**Frecuencia**: Programado (diario 6 AM) y manual
**Propósito**: Gestionar issues y PRs inactivos
**Jobs**: Stale (marcar y cerrar issues inactivos)

### 8. 🏷️ Etiquetado Automático (label.yml)
**Frecuencia**: Issues y PRs abiertos/editados
**Propósito**: Asignar etiquetas automáticamente
**Jobs**: Label (por contenido y título)

### 9. 👋 Bienvenida (welcome.yml)
**Frecuencia**: Issues y PRs abiertos
**Propósito**: Dar la bienvenida a nuevos contribuidores
**Jobs**: Welcome (mensaje de bienvenida)

### 10. 🔍 Revisión de Dependencias (dependency-review.yml)
**Frecuencia**: PRs con cambios en dependencias
**Propósito**: Revisar vulnerabilidades en dependencias
**Jobs**: Dependency Review

### 11. 🤖 Automatización de Issues (issue-automation.yml)
**Frecuencia**: Cambios en issues y PRs
**Propósito**: Automatizar gestión de issues
**Jobs**: Automation (proyectos, cierre automático)

### 12. 🔄 Sincronizar Fork (sync-fork.yml)
**Frecuencia**: Programado (diario 8 AM) y manual
**Propósito**: Sincronizar forks con upstream
**Jobs**: Sync (merge con upstream)

### 13. 📦 Crear Release (release.yml)
**Frecuencia**: Push de tags (v*)
**Propósito**: Crear releases automáticos
**Jobs**: Create Release

## 📅 Cronograma de Ejecución

| Hora UTC | Workflow | Descripción |
|----------|----------|-------------|
| 02:00 | Security Scan | Escaneo de vulnerabilidades |
| 03:00 | Backup | Backup de bases de datos |
| 04:00 | Cleanup | Limpieza de Docker |
| 06:00 | Stale | Marcar issues inactivos |
| 08:00 | Sync Fork | Sincronizar forks |

## 🎯 Uso por Rol

### 👨‍💻 Desarrolladores
- **Push a main**: Dispara CI/CD automático
- **Crear PR**: Dispara revisión de dependencias
- **Despliegue manual**: Para cambios urgentes
- **Issues**: Reciben bienvenida y etiquetado automático

### 👨‍💼 Mantenedores
- **Aprobar PRs**: Revisar y mergear cambios
- **Gestionar releases**: Crear tags para releases
- **Monitorear**: Verificar ejecución de workflows
- **Resolver conflictos**: Sincronizar forks

### 🔧 DevOps
- **Configurar secrets**: Mantener secrets actualizados
- **Monitorear seguridad**: Revisar alerts de seguridad
- **Gestionar backups**: Verificar backups automáticos
- **Optimizar**: Ajustar workflows según necesidades

## 📈 Métricas y Monitoreo

### Métricas Clave
- **Tiempo de build**: Duración de CI/CD
- **Tasa de éxito**: Porcentaje de builds exitosos
- **Tiempo de deploy**: Duración de despliegues
- **Issues resueltos**: Velocidad de resolución
- **Vulnerabilidades**: Tendencias de seguridad

### Dashboards Recomendados
1. **CI/CD Dashboard**: Build times y success rates
2. **Security Dashboard**: Vulnerabilidades y alerts
3. **Deployment Dashboard**: Frecuencia y éxito de deploys
4. **Community Dashboard**: Contribuciones y engagement

## 🚨 Solución de Problemas

### Problemas Comunes
1. **Build Failed**: Revisar logs de GitHub Actions
2. **Deploy Timeout**: Verificar conectividad con servidores
3. **Secrets Missing**: Confirmar secrets configurados
4. **Permission Denied**: Verificar permisos SSH

### Debugging
```bash
# Ver logs de workflows
gh run list
gh run view [run-id]

# Ver logs de jobs específicos
gh run view [run-id] --log-failed

# Re-ejecutar workflows
gh run rerun [run-id]
```

## 🔗 Recursos Adicionales

- [GitHub Actions Documentation](https://docs.github.com/actions)
- [Configuración Completa](GITHUB_ACTIONS_SETUP.md)
- [Guía de Contribución](../CONTRIBUTING.md)
- [Código de Conducta](../CODE_OF_CONDUCT.md)

---

**Mantenedor**: Equipo DevOps - Soluciones System Perú 🇵🇪
**Última actualización**: 2025-12-13
**Versión**: 1.0