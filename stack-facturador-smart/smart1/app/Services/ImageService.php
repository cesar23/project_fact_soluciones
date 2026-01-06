<?php

namespace App\Services;

use Intervention\Image\Facades\Image;
use GuzzleHttp\Client;
use Exception;
use GuzzleHttp\Psr7\Uri;
use Illuminate\Support\Facades\Log;

class ImageService
{
    /**
     * Descarga un PDF desde una URL y lo convierte a imagen
     * 
     * @param string $url URL del PDF a descargar
     * @param string $paperSize Tamaño del papel (80mm o 58mm por defecto)
     * @param string $format Formato de salida de imagen (webp, jpg o png)
     * @param int $quality Calidad de compresión (1-100)
     * @return \Intervention\Image\Image
     */
    public function getImageFromTicket($url, $paperSize = '80mm', $format = 'webp', $quality = 85)
    {
        try {
            // Validar que la URL sea un string válido
            if (!is_string($url) || empty(trim($url))) {
                throw new Exception("La URL proporcionada no es válida");
            }
            
            // Asegurarse de que la URL esté correctamente formateada
            if (strpos($url, '@') === 0) {
                // Si la URL comienza con @, quitarlo
                $url = substr($url, 1);
            }
            
            // Verificar y corregir protocolo si es necesario
            if (!filter_var($url, FILTER_VALIDATE_URL)) {
                // Intenta corregir la URL añadiendo el protocolo si falta
                if (strpos($url, 'http') !== 0) {
                    $url = 'https://' . ltrim($url, '/');
                    
                    // Verificar de nuevo
                    if (!filter_var($url, FILTER_VALIDATE_URL)) {
                        throw new Exception("No se pudo convertir a una URL válida: $url");
                    }
                } else {
                    throw new Exception("URL mal formateada: $url");
                }
            }
            
            // Crear cliente HTTP y descargar el PDF
            $client = new Client();
            // Registrar la URL para depuración
            Log::info("Descargando PDF desde: " . $url);
            
            // Configuración específica para esta URL
            $options = [
                'headers' => [
                    'User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/91.0.4472.124 Safari/537.36',
                    'Accept' => 'application/pdf,*/*'
                ],
                'timeout' => 30,
                'connect_timeout' => 30,
                'verify' => false // Desactivar verificación SSL
            ];
            
            $response = $client->get($url, $options);
            
            if ($response->getStatusCode() != 200) {
                throw new Exception("Error descargando el PDF: Código " . $response->getStatusCode());
            }
            
            $contentType = $response->getHeaderLine('Content-Type');
            if (strpos($contentType, 'application/pdf') === false && strpos($contentType, 'application/octet-stream') === false) {
                Log::warning("Tipo de contenido inesperado: $contentType para URL: $url");
            }
            
            // Guardar el PDF temporalmente
            $tempPath = storage_path('app/temp_' . uniqid() . '.pdf');
            file_put_contents($tempPath, $response->getBody()->getContents());
            
            // Verificar que el archivo se guardó correctamente y tiene tamaño
            if (!file_exists($tempPath) || filesize($tempPath) === 0) {
                throw new Exception("Error al guardar el PDF descargado o archivo vacío");
            }
            
            // Determinar el ancho del papel
            $paperWidth = ($paperSize == "80mm") ? 576 : 384;
            $scale = 2; // Factor de escala para mejor calidad
            
            // Validar formato
            $format = strtolower($format);
            if (!in_array($format, ['webp', 'jpg', 'jpeg', 'png'])) {
                $format = 'webp'; // Usar WebP por defecto si el formato no es válido
            }
            
            // Usar formato compatible con GhostScript
            $outputImagePath = storage_path('app/temp_' . uniqid() . '.png');
            
            // Aumentar resolución para mejor calidad
            $resolution = 200 * $scale;
            $command = "gs -dQUIET -dBATCH -dNOPAUSE -sDEVICE=pngalpha -r{$resolution} -dFirstPage=1 -dLastPage=1 -sOutputFile={$outputImagePath} {$tempPath} 2>&1";
            
            $output = [];
            $returnVar = 0;
            exec($command, $output, $returnVar);
            
            if ($returnVar !== 0 || !file_exists($outputImagePath)) {
                // Si falla con Ghostscript, intentar con otra herramienta como ImageMagick si está disponible
                $command = "convert -density {$resolution} {$tempPath}[0] {$outputImagePath}";
                exec($command, $output, $returnVar);
                
                if ($returnVar !== 0 || !file_exists($outputImagePath)) {
                    throw new Exception("Error al convertir PDF a imagen: " . implode("\n", $output));
                }
            }
            
            // Cargar la imagen con Intervention Image
            $image = Image::make($outputImagePath);
            
            // Redimensionar a la anchura final del papel
            $image->resize($paperWidth, null, function ($constraint) {
                $constraint->aspectRatio();
            });
            
            // Recortar espacio en blanco inferior
            $height = $image->height();
            $width = $image->width();
            
            // Encontrar la última línea no blanca
            $lastNonWhiteY = $this->findLastNonWhiteLine($image);
            
            // Recortar la imagen añadiendo un pequeño margen
            $margin = 50;
            $croppedHeight = min($lastNonWhiteY + $margin, $height);
            $image->crop($width, $croppedHeight, 0, 0);
            
            // Mejorar el contraste para impresión térmica
            $image->contrast(10);
            
            // Optimizar para impresoras térmicas (convertir a escala de grises si es necesario)
            if ($format == 'webp' || $format == 'jpg' || $format == 'jpeg') {
                $image->greyscale();
                
                // Para impresoras térmicas, a veces el dithering puede mejorar la calidad de impresión
                if ($format == 'jpg' || $format == 'jpeg') {
                    // Ajustar brillo y contraste para impresión térmica
                    $image->brightness(10)->contrast(20);
                }
            }
            
            // Establecer calidad de compresión según formato
            $encodedImage = null;
            if ($format == 'webp') {
                // WebP generalmente ofrece mejor compresión que JPG manteniendo calidad
                $encodedImage = $image->encode('webp', $quality);
            } elseif ($format == 'jpg' || $format == 'jpeg') {
                $encodedImage = $image->encode('jpg', $quality);
            } else {
                // PNG para casos donde se necesita transparencia
                $encodedImage = $image->encode('png', 9); // PNG usa compresión 0-9
            }
            
            // Limpiar archivos temporales
            @unlink($tempPath);
            @unlink($outputImagePath);
            
            return $encodedImage;
            
        } catch (Exception $e) {
            // Limpiar archivos temporales en caso de error
            if (isset($tempPath) && file_exists($tempPath)) {
                @unlink($tempPath);
            }
            if (isset($outputImagePath) && file_exists($outputImagePath)) {
                @unlink($outputImagePath);
            }
            
            // Registrar el error para depuración
            Log::error("Error en ImageService::getImageFromTicket: " . $e->getMessage());
            Log::error("URL proporcionada: " . $url);
            
            throw $e;
        }
    }
    
    /**
     * Encuentra la última línea no blanca en una imagen
     * 
     * @param \Intervention\Image\Image $image
     * @return int La posición Y del último píxel no blanco
     */
    private function findLastNonWhiteLine($image)
    {
        $height = $image->height();
        $width = $image->width();
        
        // Umbral para detectar contenido (similar al código Dart)
        $threshold = 250;
        
        // Recorrer la imagen de abajo hacia arriba
        for ($y = $height - 1; $y >= 0; $y--) {
            for ($x = 0; $x < $width; $x++) {
                $pixel = $image->pickColor($x, $y, 'array');
                $r = $pixel[0];
                $g = $pixel[1];
                $b = $pixel[2];
                
                if ($r < $threshold || $g < $threshold || $b < $threshold) {
                    return $y;
                }
            }
        }
        
        return $height - 1; // Si no se encuentra, devolver la altura completa
    }
}