<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class ImageCacheController extends Controller
{
    /**
     * Serve cached item images with optimized cache headers
     *
     * @param string $filename
     * @return \Illuminate\Http\Response
     */
    public function item($filename)
    {
        // Construir la ruta del archivo
        $path = storage_path("app/public/uploads/items/{$filename}");

        // Si la imagen no existe, usar imagen por defecto
        if (!file_exists($path)) {
            $path = public_path("logo/imagen-no-disponible.jpg");

            // Si tampoco existe la imagen por defecto, usar fallback
            if (!file_exists($path)) {
                $path = public_path("logo/imagen-no-disponible.png");

                // Último fallback - crear respuesta 404 si no hay imagen
                if (!file_exists($path)) {
                    return response()->json(['error' => 'Image not found'], 404);
                }
            }
        }

        // Generar ETag basado en el archivo
        $etag = md5_file($path);
        $lastModified = filemtime($path);

        // Verificar si el cliente ya tiene la imagen en cache
        $clientEtag = request()->header('If-None-Match');
        $clientLastModified = request()->header('If-Modified-Since');

        if ($clientEtag === '"' . $etag . '"' ||
            ($clientLastModified && strtotime($clientLastModified) >= $lastModified)) {
            return response('', 304);
        }

        // Headers de cache agresivos para imágenes
        $headers = [
            'Cache-Control' => 'public, max-age=31536000, immutable', // 1 año
            'Expires' => gmdate('D, d M Y H:i:s \G\M\T', time() + 31536000),
            'ETag' => '"' . $etag . '"',
            'Last-Modified' => gmdate('D, d M Y H:i:s \G\M\T', $lastModified),
            'Content-Type' => $this->getMimeType($path),
            'Vary' => 'Accept-Encoding',
        ];

        return response()->file($path, $headers);
    }

    /**
     * Serve multiple cached item images in batch
     * Optimized for batch requests with proper cache headers
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function batchItems(Request $request)
    {
        $filenames = $request->input('filenames', []);

        if (empty($filenames) || !is_array($filenames)) {
            return response()->json(['images' => []]);
        }

        $images = [];
        $cacheHeaders = [
            'Cache-Control' => 'public, max-age=31536000, immutable',
            'Expires' => gmdate('D, d M Y H:i:s \\G\\M\\T', time() + 31536000),
        ];

        foreach ($filenames as $filename) {
            $path = storage_path("app/public/uploads/items/{$filename}");

            // Verificar si existe la imagen
            if (!file_exists($path)) {
                // Usar imagen por defecto si no existe
                $path = public_path("logo/imagen-no-disponible.jpg");
                if (!file_exists($path)) {
                    $path = public_path("logo/imagen-no-disponible.png");
                    if (!file_exists($path)) {
                        continue; // Saltar si no hay fallback
                    }
                }
            }

            $etag = md5_file($path);
            $lastModified = filemtime($path);

            $images[] = [
                'filename' => $filename,
                'url' => route('cached.item.image', ['filename' => $filename]),
                'etag' => $etag,
                'last_modified' => $lastModified,
                'size' => filesize($path),
                'mime_type' => $this->getMimeType($path)
            ];
        }

        return response()->json([
            'images' => $images,
            'count' => count($images),
            'cache_info' => [
                'max_age' => 31536000,
                'generated_at' => time()
            ]
        ], 200, $cacheHeaders);
    }

    /**
     * Get MIME type for the file
     *
     * @param string $path
     * @return string
     */
    private function getMimeType($path)
    {
        $mimeType = mime_content_type($path);

        // Fallback para tipos de imagen comunes
        if (!$mimeType) {
            $extension = strtolower(pathinfo($path, PATHINFO_EXTENSION));

            $mimeTypes = [
                'jpg' => 'image/jpeg',
                'jpeg' => 'image/jpeg',
                'png' => 'image/png',
                'gif' => 'image/gif',
                'webp' => 'image/webp',
                'svg' => 'image/svg+xml',
            ];

            return $mimeTypes[$extension] ?? 'application/octet-stream';
        }

        return $mimeType;
    }
}