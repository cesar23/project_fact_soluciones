# Guía de Contribución - Stack Facturador Smart

## 🎯 Gracias por Contribuir

¡Gracias por tu interés en contribuir a Stack Facturador Smart! Este proyecto se enfoca en proporcionar un sistema de facturación electrónica para Perú, y tu ayuda es invaluable.

## 📋 Tabla de Contenidos

- [Cómo Contribuir](#cómo-contribuir)
- [Reportar Bugs](#reportar-bugs)
- [Sugerir Features](#sugerir-features)
- [Primeros Pasos](#primeros-pasos)
- [Proceso de Desarrollo](#proceso-de-desarrollo)
- [Estándares de Código](#estándares-de-código)
- [Pull Requests](#pull-requests)
- [Code Review](#code-review)

## 🚀 Cómo Contribuir

### Tipos de Contribuciones
- 🐛 **Reportar bugs**: Ayúdanos a encontrar y solucionar problemas
- 💡 **Sugerir features**: Propón nuevas funcionalidades
- 📝 **Mejorar documentación**: Ayuda a mejorar la documentación
- 🔧 **Corregir bugs**: Soluciona problemas existentes
- 🚀 **Implementar features**: Desarrolla nuevas funcionalidades
- 🧪 **Agregar pruebas**: Mejora la cobertura de tests

## 🐛 Reportar Bugs

### Antes de Reportar
1. Busca en [Issues](https://github.com/tu-usuario/stack-facturador-smart/issues) si ya existe el bug
2. Verifica que el bug no esté ya solucionado en la última versión

### Cómo Reportar
Usa nuestra [plantilla de bug report](.github/ISSUE_TEMPLATE/bug_report.md) e incluye:
- **Descripción clara**: Qué está pasando
- **Pasos para reproducir**: Cómo hacer que ocurra el bug
- **Comportamiento esperado**: Qué debería pasar
- **Capturas de pantalla**: Si aplica
- **Información del ambiente**: SO, versión, etc.

## 💡 Sugerir Features

### Antes de Sugerir
1. Busca en [Issues](https://github.com/tu-usuario/stack-facturador-smart/issues) si ya existe la feature
2. Piensa si la feature encaja con el proyecto

### Cómo Sugerir
Usa nuestra [plantilla de feature request](.github/ISSUE_TEMPLATE/feature_request.md) e incluye:
- **Resumen claro**: Qué quieres que se agregue
- **Motivación**: Por qué necesitas esta feature
- **Solución propuesta**: Cómo implementarías la feature
- **Alternativas**: Otras opciones consideradas

## 🏁 Primeros Pasos

### 1. Fork el Repositorio
```bash
# Ve a GitHub y haz fork del repositorio
# Luego clona tu fork localmente
git clone https://github.com/tu-usuario/stack-facturador-smart.git
cd stack-facturador-smart
```

### 2. Configurar Ambiente
```bash
# Instalar dependencias
cd stack-facturador-smart/smart1
docker-compose up -d

# Instalar composer dependencies
docker exec fpm1 composer install

# Configurar base de datos
docker exec fpm1 php artisan migrate
```

### 3. Crear Branch
```bash
# Sincronizar con upstream
git fetch upstream
git checkout main
git merge upstream/main

# Crear branch para tu feature
git checkout -b feature/nombre-de-tu-feature
# o para bug fixes
git checkout -b fix/nombre-del-fix
```

## 🔄 Proceso de Desarrollo

### 1. Hacer Cambios
- Sigue los [estándares de código](#estándares-de-código)
- Escribe código limpio y legible
- Agrega comentarios cuando sea necesario
- Mantén commits pequeños y descriptivos

### 2. Probar Cambios
```bash
# Ejecutar tests
docker exec fpm1 php artisan test

# Verificar que todo funcione
docker exec fpm1 php artisan serve
```

### 3. Commits
```bash
# Agregar cambios
git add .

# Hacer commit con mensaje descriptivo
git commit -m "feat: agregar nueva funcionalidad de facturación

- Agregar validación de RUC
- Mejorar interfaz de usuario
- Agregar tests unitarios"

# Subir cambios
git push origin feature/nombre-de-tu-feature
```

### Convención de Commits
Usamos [Conventional Commits](https://www.conventionalcommits.org/):
- `feat:` Nueva feature
- `fix:` Corrección de bug
- `docs:` Cambios en documentación
- `style:` Cambios de formato (espacios, comas, etc.)
- `refactor:` Refactorización de código
- `test:` Agregar o corregir tests
- `chore:` Cambios en build process o herramientas

## 📝 Estándares de Código

### PHP (Laravel)
- Seguir [PSR-12](https://www.php-fig.org/psr/psr-12/)
- Usar type hints donde sea posible
- Documentar funciones con PHPDoc
- Mantener líneas menores a 120 caracteres

### JavaScript
- Usar ES6+ features
- Seguir estilo de Airbnb
- Usar `const` y `let` en lugar de `var`

### Docker
- Usar multi-stage builds cuando sea posible
- Mantener imágenes livianas
- Usar .dockerignore apropiadamente

## 🔀 Pull Requests

### Antes de Enviar PR
1. Asegúrate que tu código pase todos los tests
2. Actualiza la documentación si es necesario
3. Sincroniza tu branch con main
4. Usa la [plantilla de PR](.github/PULL_REQUEST_TEMPLATE.md)

### Proceso de PR
1. **Crear PR**: Desde tu fork a main
2. **Descripción**: Completa toda la plantilla
3. **Review**: Espera feedback del equipo
4. **Cambios**: Realiza los cambios solicitados
5. **Merge**: Cuando sea aprobado

### Qué Incluir en PR
- Descripción clara de los cambios
- Screenshots si aplica
- Tests si es una nueva feature
- Actualización de documentación
- Lista de cambios en CHANGELOG.md

## 👀 Code Review

### Como Reviewer
- Sé constructivo y respetuoso
- Enfócate en el código, no en la persona
- Explica por qué sugieres cambios
- Revisa tanto funcionalidad como seguridad

### Como Author
- Responde a todos los comentarios
- Aplica cambios sugeridos o explica por qué no
- Mantén una actitud positiva
- Aprende de las críticas constructivas

## 🏷️ Etiquetas de Issues

- `bug`: Error en el sistema
- `enhancement`: Mejora de funcionalidad existente
- `feature`: Nueva funcionalidad
- `documentation`: Mejoras en documentación
- `good first issue`: Ideal para nuevos contribuidores
- `help wanted`: Necesita ayuda adicional

## 📞 Comunicación

### Canales
- **Issues**: Para bugs y features
- **Pull Requests**: Para código
- **Discusiones**: Para preguntas generales
- **Email**: devops@solucionessystem.com

### Reglas de Comunicación
- Mantén conversaciones respetuosas
- Usa español claro y profesional
- Proporciona contexto suficiente
- Respeta el tiempo de otros

## 🎉 Reconocimiento

Todas las contribuciones son valoradas y reconocidas:
- Mención en README.md
- Agradecimiento en releases
- Posibilidad de convertirse en mantenedor

## 📜 Licencia

Al contribuir, aceptas que tus contribuciones serán licenciadas bajo la [LICENSE](LICENSE) del proyecto.

---

## ❓ Preguntas Frecuentes

### ¿Cómo empiezo si soy nuevo?
Busca issues con la etiqueta `good first issue` o `help wanted`.

### ¿Qué pasa si mi PR no es aceptado?
No te desanimes. Aprende de los comentarios y mejora tu código.

### ¿Puedo contribuir si no sé programar?
¡Sí! Puedes ayudar con documentación, testing, o reportando bugs.

---

**Gracias por contribuir a Stack Facturador Smart! 🇵🇪**

Para más información, contacta a: devops@solucionessystem.com

---

**Última actualización**: 2025-12-13  
**Versión**: 1.0