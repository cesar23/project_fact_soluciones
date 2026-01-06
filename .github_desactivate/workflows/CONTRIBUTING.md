# Guía de Contribución - Stack Facturador Smart

¡Gracias por tu interés en contribuir a Stack Facturador Smart! 🎉

Esta guía te ayudará a entender cómo contribuir de manera efectiva al proyecto.

## 🚀 ¿Cómo Contribuir?

### 1. 📋 Reportar Bugs
Antes de crear un issue:
1. **Busca issues existentes** - Evita duplicados
2. **Usa la plantilla** - Proporciona información completa
3. **Incluye detalles** - SO, versión, logs, screenshots

### 2. 💡 Sugerir Mejoras
Para nuevas features:
1. **Abre un issue** - Describe la idea claramente
2. **Explica el caso de uso** - ¿Por qué es útil?
3. **Propón solución** - ¿Cómo lo implementarías?
4. **Espera feedback** - Discutimos antes de implementar

### 3. 🔧 Contribuir con Código
Sigue estos pasos:

#### Paso 1: Fork del Repositorio
```bash
# Haz fork del repositorio en GitHub
# Clona tu fork localmente
git clone https://github.com/tu-usuario/stack-facturador-smart.git
cd stack-facturador-smart
```

#### Paso 2: Configura tu Ambiente
```bash
# Instala dependencias
composer install
npm install

# Configura Docker
docker-compose up -d

# Verifica que todo funcione
docker-compose ps
```

#### Paso 3: Crea una Rama
```bash
# Actualiza tu rama main
git checkout main
git pull origin main

# Crea una rama para tu feature
git checkout -b feature/nombre-de-tu-feature
# O para un bugfix
git checkout -b fix/nombre-del-fix
```

#### Paso 4: Desarrolla tu Contribución
```bash
# Haz tus cambios
# Sigue las convenciones de código
# Añade tests si es necesario
# Actualiza documentación

# Verifica que todo funcione
docker-compose restart
./vendor/bin/phpunit
```

#### Paso 5: Commitea tus Cambios
```bash
# Añade los archivos modificados
git add .

# Haz commit con mensaje descriptivo
git commit -m "feat: añadir nueva funcionalidad X"
# O para bugfix
git commit -m "fix: corregir problema Y"
# O para documentación
git commit -m "docs: actualizar guía de instalación"
```

#### Paso 6: Push y Pull Request
```bash
# Sube tus cambios
git push origin feature/nombre-de-tu-feature

# Crea Pull Request en GitHub
# Usa la plantilla proporcionada
# Espera revisión del equipo
```

## 📝 Convenciones de Código

### Mensajes de Commit
Usamos [Conventional Commits](https://www.conventionalcommits.org/):

```bash
feat:     nueva funcionalidad
fix:      corrección de bug
docs:     cambios en documentación
style:    formato, punto y coma, etc. (sin cambios de código)
refactor: refactorización de código
test:     añadir tests
chore:    cambios en build, herramientas, etc.
```

### Estilo de Código PHP
Seguimos [PSR-12](https://www.php-fig.org/psr/psr-12/):

```php
<?php

namespace App;

use Some\Namespace\Class;

class ClassName
{
    public function methodName($parameter)
    {
        if ($condition) {
            // código
        }
        
        return $result;
    }
}
```

### Estilo de Código JavaScript
Usamos [ESLint](https://eslint.org/) y [Prettier](https://prettier.io/):

```javascript
// Buenas prácticas
const variableName = 'valor';
const objectName = { key: 'value' };

function functionName(parameter) {
  if (condition) {
    // código
  }
  
  return result;
}
```

## 🧪 Testing

### Tests Unitarios
```bash
# Ejecutar todos los tests
./vendor/bin/phpunit

# Ejecutar tests específicos
./vendor/bin/phpunit tests/Feature/MyTest.php

# Con cobertura de código
./vendor/bin/phpunit --coverage-html coverage
```

### Tests de Integración
```bash
# Ejecutar tests de Docker
docker-compose exec fpm1 ./vendor/bin/phpunit

# Verificar healthchecks
docker-compose ps
```

## 📚 Documentación

### Actualizar Documentación
1. **README.md** - Información principal del proyecto
2. **GITHUB_ACTIONS_SETUP.md** - Guía de CI/CD
3. **CONTRIBUTING.md** - Esta guía
4. **CHANGELOG.md** - Historial de cambios
5. **SUPPORT.md** - Información de soporte

### Estilo de Documentación
- Usa Markdown para formatear
- Incluye ejemplos de código
- Añade screenshots cuando sea útil
- Mantén la estructura consistente

## 🔍 Proceso de Revisión

### ¿Qué Revisamos?
1. **Funcionalidad** - ¿Funciona como se espera?
2. **Código** - ¿Sigue convenciones?
3. **Tests** - ¿Hay tests adecuados?
4. **Documentación** - ¿Está actualizada?
5. **Seguridad** - ¿Hay vulnerabilidades?
6. **Performance** - ¿Afecta el rendimiento?

### Estados del PR
1. **Draft** - En desarrollo
2. **Ready for Review** - Listo para revisión
3. **Changes Requested** - Necesita cambios
4. **Approved** - Aprobado para merge
5. **Merged** - Integrado al proyecto

## 🐛 Encontraste un Bug?

### Pasos para Reportar
1. **Verifica issues existentes**
2. **Crea issue con plantilla**
3. **Proporciona información**:
   - Descripción clara del problema
   - Pasos para reproducir
   - Comportamiento esperado vs actual
   - Capturas de pantalla/logs
   - Ambiente (SO, PHP, Docker, etc.)

### Ejemplo de Bug Report
```markdown
## Descripción
El sistema no genera facturas electrónicas cuando...

## Pasos para Reproducir
1. Ir a 'Generar Factura'
2. Completar campos obligatorios
3. Hacer clic en 'Generar'
4. Ver error en consola

## Comportamiento Esperado
Debería generar la factura y mostrar confirmación

## Ambiente
- SO: Ubuntu 20.04
- PHP: 7.4.3
- Docker: 20.10.5
- Navegador: Chrome 90

## Logs
[Incluir logs relevantes]
```

## 💡 Quieres Sugerir una Feature?

### Proceso de Feature Request
1. **Busca ideas similares**
2. **Abre issue con plantilla**
3. **Describe el problema que resuelve**
4. **Propón solución**
5. **Espera discusión y aprobación**

### Criterios de Aprobación
- **Utilidad** - ¿Resuelve un problema real?
- **Alcance** - ¿Es apropiado para el proyecto?
- **Mantenibilidad** - ¿Es fácil de mantener?
- **Performance** - ¿Afecta el rendimiento?
- **Compatibilidad** - ¿Rompe cambios existentes?

## 🏗️ Estructura del Proyecto

```
stack-facturador-smart/
├── app/                 # Código de la aplicación Laravel
├── config/              # Configuraciones
├── database/            # Migraciones y seeds
├── public/              # Archivos públicos
├── resources/           # Vistas y assets
├── routes/              # Rutas de la aplicación
├── tests/               # Tests
├── docker-compose.yml   # Configuración de Docker
├── Dockerfile.*         # Dockerfiles
└── .github/workflows/   # GitHub Actions
```

## 🔧 Configuración del Ambiente Local

### Requisitos
- Docker 20.10+
- Docker Compose 1.29+
- PHP 7.4+
- Composer 2.0+
- Node.js 14+
- Git 2.30+

### Configuración Rápida
```bash
# Clona el repositorio
git clone https://github.com/tu-usuario/stack-facturador-smart.git
cd stack-facturador-smart

# Configura variables de entorno
cp .env.example .env

# Inicia servicios
docker-compose up -d

# Instala dependencias
docker-compose exec fpm1 composer install
docker-compose exec fpm1 npm install

# Ejecuta migraciones
docker-compose exec fpm1 php artisan migrate

# Genera clave de aplicación
docker-compose exec fpm1 php artisan key:generate

# Accede a la aplicación
http://localhost
```

## 🤝 Código de Conducta

### Nuestros Valores
- **Respeto** - Trata a todos con respeto
- **Inclusión** - Bienvenidas todas las personas
- **Colaboración** - Trabajamos juntos
- **Transparencia** - Comunicación clara
- **Excelencia** - Calidad en nuestro trabajo

### Comportamiento Esperado
- ✅ Ser respetuoso y inclusivo
- ✅ Aceptar feedback constructivo
- ✅ Colaborar de manera efectiva
- ✅ Mantener comunicación profesional
- ✅ Respetar diferentes perspectivas

### Comportamiento No Aceptable
- ❌ Lenguaje ofensivo o discriminatorio
- ❌ Comportamiento acosador
- ❌ Spam o autopromoción
- ❌ Publicación de información privada
- ❌ Cualquier forma de discriminación

## 📊 Seguimiento de Contribuciones

### Reconocimientos
- **Contribuidores destacados** - Reconocimiento mensual
- **Hall of Fame** - Contribuidores principales
- **Badges especiales** - Por tipo de contribución
- **Menciones en redes** - Visibilidad de tu trabajo

### Estadísticas
- Seguimiento de commits
- Issues resueltos
- Pull requests mergeados
- Documentación mejorada
- Tests añadidos

## 🎓 Recursos para Contribuidores

### Documentación
- [Guía de Laravel](https://laravel.com/docs)
- [Documentación de Docker](https://docs.docker.com/)
- [GitHub Actions](https://docs.github.com/en/actions)
- [Conventional Commits](https://www.conventionalcommits.org/)

### Tutoriales
- [Primeros pasos con Git](https://git-scm.com/book)
- [Guía de Docker Compose](https://docs.docker.com/compose/)
- [PHP Best Practices](https://phptherightway.com/)
- [Laravel Best Practices](https://github.com/alexeymezenin/laravel-best-practices)

### Comunidad
- [GitHub Discussions](https://github.com/tu-usuario/stack-facturador-smart/discussions)
- [Issues de GitHub](https://github.com/tu-usuario/stack-facturador-smart/issues)
- [Canal de Slack](https://solucionessystem.slack.com)
- [Foro de la comunidad](https://foro.solucionessystem.com)

## 🚀 Tu Primera Contribución

### Issues para Principiantes
Buscamos issues etiquetados como:
- `good-first-issue` - Ideal para empezar
- `help-wanted` - Necesitan ayuda
- `documentation` - Mejoras en docs

### Proceso Simplificado
1. Encuentra un issue que te interese
2. Comenta que quieres trabajar en él
3. Sigue los pasos de contribución
4. Pide ayuda si la necesitas
5. Envía tu PR y celebra 🎉

## 📞 ¿Necesitas Ayuda?

### Canales de Soporte
- **GitHub Issues** - Para problemas técnicos
- **GitHub Discussions** - Para preguntas generales
- **Email** - devops@solucionessystem.com
- **Slack** - [#stack-facturador-smart](https://solucionessystem.slack.com)

### Mentores
- **Juan Pérez** - Docker & DevOps
- **María García** - Laravel & PHP
- **Carlos López** - Frontend & UI/UX
- **Ana Martínez** - Testing & QA

## 🎉 ¡Gracias!

Agradecemos enormemente todas las contribuciones, desde el más pequeño fix de typo hasta grandes nuevas features. Cada contribución hace que Stack Facturador Smart sea mejor para todos.

---

**¿Listo para contribuir?** 
- [Busca issues](https://github.com/tu-usuario/stack-facturador-smart/issues)
- [Únete a la discusión](https://github.com/tu-usuario/stack-facturador-smart/discussions)
- [Lee la documentación](README.md)

---

**Última actualización**: 2025-12-13
**Versión**: 1.0
**Equipo**: Soluciones System Perú 🇵🇪
**Contacto**: devops@solucionessystem.com