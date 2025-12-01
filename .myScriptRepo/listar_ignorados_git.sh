#!/bin/bash

# Script para listar archivos ignorados según .gitignore

# Verifica si estás en un repositorio Git
if ! git rev-parse --is-inside-work-tree &>/dev/null; then
  echo "❌ Este directorio no es un repositorio Git."
  exit 1
fi

# Verifica si existe el archivo .gitignore
if [ ! -f .gitignore ]; then
  echo "❌ No se encontró el archivo .gitignore en este directorio."
  exit 1
fi

echo "🔍 Buscando archivos ignorados según el .gitignore..."

# Lista archivos que están siendo ignorados por Git, usando lo declarado en .gitignore
git ls-files --others --ignored --exclude-standard
