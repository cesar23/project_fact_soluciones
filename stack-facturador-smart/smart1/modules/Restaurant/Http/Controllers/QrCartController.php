<?php

namespace Modules\Restaurant\Http\Controllers;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Log;

class QrCartController extends Controller
{
    const FIXED_PDF_FILENAME = 'menu.pdf';
    
    public function index()
    {
        return view('restaurant::qr-pdf.index');
    }

    public function upload(Request $request)
    {
        Log::info('QR Cart: Inicio de solicitud de carga', [
            'has_file' => $request->hasFile('pdf'),
            'all_files' => $request->allFiles(),
            'content_type' => $request->header('Content-Type'),
            'request_method' => $request->method(),
            'request_all' => $request->all(),
            'files_global' => $_FILES // Verificar los archivos a nivel global
        ]);

        try {
            // Verificar si hay archivos en la solicitud global
            if (empty($_FILES)) {
                Log::error('QR Cart: No hay archivos en $_FILES');
                return response()->json([
                    'success' => false,
                    'message' => 'No se recibieron archivos'
                ], 422);
            }

            // Verificar el archivo específico
            if (!$request->hasFile('pdf')) {
                Log::error('QR Cart: No se encontró archivo PDF', [
                    'files' => $_FILES,
                    'request_files' => $request->allFiles()
                ]);
                return response()->json([
                    'success' => false,
                    'message' => 'No se encontró el archivo PDF'
                ], 422);
            }

            $file = $request->file('pdf');
            
            // Verificar que el archivo sea válido
            if (!$file->isValid()) {
                Log::error('QR Cart: Archivo no válido', [
                    'error' => $file->getError(),
                    'error_message' => $file->getErrorMessage()
                ]);
                return response()->json([
                    'success' => false,
                    'message' => 'El archivo no es válido: ' . $file->getErrorMessage()
                ], 422);
            }

            // Log de información del archivo
            Log::info('QR Cart: Archivo recibido', [
                'original_name' => $file->getClientOriginalName(),
                'mime_type' => $file->getMimeType(),
                'size' => $file->getSize(),
                'error' => $file->getError()
            ]);

            // Validación detallada
            $validator = \Validator::make($request->all(), [
                'pdf' => [
                    'required',
                    'file',
                    'mimes:pdf',
                    'max:5120' // 5MB
                ]
            ]);

            if ($validator->fails()) {
                Log::error('QR Cart: Error de validación', [
                    'errors' => $validator->errors()->toArray()
                ]);
                return response()->json([
                    'success' => false,
                    'message' => 'Error de validación',
                    'errors' => $validator->errors()
                ], 422);
            }

            // Verificar el mime type manualmente
            $mimeType = $file->getMimeType();
            if ($mimeType !== 'application/pdf') {
                Log::error('QR Cart: Tipo MIME no válido', ['mime_type' => $mimeType]);
                return response()->json([
                    'success' => false,
                    'message' => 'El archivo debe ser un PDF válido'
                ], 422);
            }

            // Verificar el tamaño manualmente
            $size = $file->getSize();
            $maxSize = 5 * 1024 * 1024; // 5MB en bytes
            if ($size > $maxSize) {
                Log::error('QR Cart: Archivo demasiado grande', ['size' => $size]);
                return response()->json([
                    'success' => false,
                    'message' => 'El archivo no debe superar los 5MB'
                ], 422);
            }

            // Si todo está bien, proceder con el guardado
            $filename = self::FIXED_PDF_FILENAME;
            
            Storage::disk('tenant')->put('pdf_viewer/' . $filename, file_get_contents($file));
            
            $pdf_url = route('restaurant.pdf.show');
            
            Log::info('QR Cart: Archivo subido exitosamente', [
                'filename' => $filename,
                'url' => $pdf_url
            ]);

            return response()->json([
                'success' => true,
                'pdf_url' => $pdf_url,
                'message' => 'PDF subido correctamente. El código QR apunta a una URL fija.'
            ]);

        } catch (\Exception $e) {
            Log::error('QR Cart: Error en upload', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Error al procesar el archivo: ' . $e->getMessage()
            ], 500);
        }
    }

    public function show()
    {
        $filename = self::FIXED_PDF_FILENAME;
        $path = 'pdf_viewer/' . $filename;

        if (!Storage::disk('tenant')->exists($path)) {
            abort(404, 'No hay PDF disponible');
        }

        // Leer el archivo directamente y devolverlo como respuesta
        $file = Storage::disk('tenant')->get($path);
        
        return response()->make($file, 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'inline; filename="'.$filename.'"'
        ]);
    }
}