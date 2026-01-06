<?php

namespace App\Http\Controllers\Tenant;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\Tenant\InformationAdditionalPdf;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class InformationAdditionalPdfController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return JsonResponse
     */
    public function index()
    {
        try {
            $records = InformationAdditionalPdf::where('is_active', true)
                ->orderBy('created_at', 'desc')
                ->get();

            return response()->json([
                'success' => true,
                'data' => $records,
                'message' => 'Registros obtenidos correctamente'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener los registros: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function store(Request $request)
    {
        try {
            $items = $request->input('items', []);

            // Primero desactivar todos los registros existentes
            InformationAdditionalPdf::query()->update(['is_active' => false]);

            // Crear los nuevos registros
            foreach ($items as $item) {
                InformationAdditionalPdf::create([
                    'description' => $item['description'] ?? '',
                    'image' => $item['image'] ?? null,
                    'is_active' => true,
                ]);
            }

            return response()->json([
                'success' => true,
                'message' => 'Información guardada correctamente'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al guardar la información: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Upload image file
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function uploadImage(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'image' => 'required|image|mimes:png,jpg,jpeg|max:512', // Reducir a 512KB para PDF
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Error de validación',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $file = $request->file('image');
            
            // Validación adicional de tamaño para PDF
            if ($file->getSize() > 524288) { // 512KB en bytes
                return response()->json([
                    'success' => false,
                    'message' => 'La imagen es demasiado grande para PDF. Máximo 512KB.'
                ], 422);
            }

            // Validar dimensiones de la imagen
            $imageSize = getimagesize($file->getPathname());
            if ($imageSize) {
                $width = $imageSize[0];
                $height = $imageSize[1];
                
                // Verificar que sea cuadrada o al menos proporcional
                $ratio = max($width, $height) / min($width, $height);
                if ($ratio > 2) {
                    return response()->json([
                        'success' => false,
                        'message' => 'La imagen debe ser cuadrada o tener proporciones similares (máximo 2:1).'
                    ], 422);
                }
                
                // Recomendar tamaño óptimo
                $warnings = [];
                if ($width < 30 || $height < 30) {
                    $warnings[] = 'La imagen es muy pequeña. Se recomienda mínimo 30x30px.';
                } elseif ($width > 200 || $height > 200) {
                    $warnings[] = 'La imagen es muy grande. Se recomienda máximo 100x100px para mejor rendimiento.';
                }
            }
            
            // Generar nombre único para el archivo
            $fileName = time() . '_' . uniqid() . '.' . $file->getClientOriginalExtension();
            
            // Guardar en storage/app/public/pdf-additional-info/
            $path = $file->storeAs('pdf-additional-info', $fileName, 'public');

            // Verificar que la imagen se pueda leer correctamente
            $fullPath = storage_path('app/public/' . $path);
            if (!file_exists($fullPath)) {
                throw new \Exception('Error al guardar la imagen');
            }

            // Redimensionar automáticamente si es muy grande
            if (isset($imageSize) && ($imageSize[0] > 200 || $imageSize[1] > 200)) {
                $this->resizeImage($fullPath, $imageSize);
            }

            $response = [
                'success' => true,
                'image_path' => $path,
                'message' => 'Imagen subida correctamente'
            ];
            
            // Agregar advertencias si existen
            if (isset($warnings) && !empty($warnings)) {
                $response['warnings'] = $warnings;
            }

            return response()->json($response);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al subir la imagen: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Display the specified resource.
     *
     * @param int $id
     * @return JsonResponse
     */
    public function show($id)
    {
        try {
            $record = InformationAdditionalPdf::findOrFail($id);
            
            return response()->json([
                'success' => true,
                'data' => $record,
                'message' => 'Registro obtenido correctamente'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener el registro: ' . $e->getMessage()
            ], 404);
        }
    }

    /**
     * Update the specified resource in storage.
     *
     * @param Request $request
     * @param int $id
     * @return JsonResponse
     */
    public function update(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'description' => 'nullable|string|max:150',
            'image' => 'nullable|string',
            'is_active' => 'boolean'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Error de validación',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $record = InformationAdditionalPdf::findOrFail($id);
            
            $record->update([
                'description' => $request->input('description', $record->description),
                'image' => $request->input('image', $record->image),
                'is_active' => $request->input('is_active', $record->is_active),
            ]);

            return response()->json([
                'success' => true,
                'data' => $record,
                'message' => 'Registro actualizado correctamente'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al actualizar el registro: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param int $id
     * @return JsonResponse
     */
    public function destroy($id)
    {
        try {
            $record = InformationAdditionalPdf::findOrFail($id);
            
            // Eliminar imagen del storage si existe
            if ($record->image && Storage::disk('public')->exists($record->image)) {
                Storage::disk('public')->delete($record->image);
            }
            
            $record->delete();

            return response()->json([
                'success' => true,
                'message' => 'Registro eliminado correctamente'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al eliminar el registro: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get active records for PDF generation
     *
     * @return JsonResponse
     */
    public function getActiveRecords()
    {
        try {
            $records = InformationAdditionalPdf::where('is_active', true)
                ->orderBy('created_at', 'desc')
                ->get()
                ->map(function ($record) {
                    return [
                        'id' => $record->id,
                        'description' => $record->description,
                        'image' => $record->image ? asset('storage/' . $record->image) : null,
                        'created_at' => $record->created_at,
                    ];
                });

            return response()->json([
                'success' => true,
                'data' => $records,
                'count' => $records->count()
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener los registros activos: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Toggle active status
     *
     * @param int $id
     * @return JsonResponse
     */
    public function toggleActive($id)
    {
        try {
            $record = InformationAdditionalPdf::findOrFail($id);
            $record->is_active = !$record->is_active;
            $record->save();

            return response()->json([
                'success' => true,
                'data' => $record,
                'message' => 'Estado actualizado correctamente'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al cambiar el estado: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Resize image to optimal dimensions for PDF
     *
     * @param string $imagePath
     * @param array $currentSize
     * @return bool
     */
    private function resizeImage($imagePath, $currentSize)
    {
        try {
            $width = $currentSize[0];
            $height = $currentSize[1];
            
            // Calcular nuevas dimensiones manteniendo proporción
            $maxSize = 100; // Tamaño máximo recomendado
            $ratio = min($maxSize / $width, $maxSize / $height);
            
            $newWidth = round($width * $ratio);
            $newHeight = round($height * $ratio);
            
            // Crear imagen según el tipo
            switch ($currentSize['mime']) {
                case 'image/jpeg':
                case 'image/jpg':
                    $source = imagecreatefromjpeg($imagePath);
                    break;
                case 'image/png':
                    $source = imagecreatefrompng($imagePath);
                    break;
                default:
                    return false;
            }
            
            if (!$source) {
                return false;
            }
            
            // Crear nueva imagen redimensionada
            $resized = imagecreatetruecolor($newWidth, $newHeight);
            
            // Mantener transparencia para PNG
            if ($currentSize['mime'] === 'image/png') {
                imagealphablending($resized, false);
                imagesavealpha($resized, true);
                $transparent = imagecolorallocatealpha($resized, 255, 255, 255, 127);
                imagefill($resized, 0, 0, $transparent);
            }
            
            // Redimensionar
            imagecopyresampled($resized, $source, 0, 0, 0, 0, $newWidth, $newHeight, $width, $height);
            
            // Guardar imagen redimensionada
            switch ($currentSize['mime']) {
                case 'image/jpeg':
                case 'image/jpg':
                    imagejpeg($resized, $imagePath, 90);
                    break;
                case 'image/png':
                    imagepng($resized, $imagePath, 8);
                    break;
            }
            
            // Liberar memoria
            imagedestroy($source);
            imagedestroy($resized);
            
            return true;
            
        } catch (\Exception $e) {
            // Si hay error en el redimensionamiento, continuar con la imagen original
            return false;
        }
    }
}