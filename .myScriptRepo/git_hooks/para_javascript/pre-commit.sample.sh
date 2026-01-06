#!/bin/bash

# Ejecuta ESLint para verificar el formato del código JavaScript
eslint . --ext .js

# Captura el código de salida de ESLint
result=$?

# Si el código de salida es diferente de 0 (error), muestra un mensaje de error y aborta el commit
if [ $result -ne 0 ]; then
    echo "Error: Se encontraron problemas de formato en el código JavaScript. Corrígelos antes de hacer el commit."
    exit 1
fi
