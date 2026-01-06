<?php

namespace Modules\Finance\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Routing\Controller;
use Modules\Finance\Models\PaymentFile;
use App\Models\Tenant\Cash;
use App\Models\Tenant\SaleNotePayment;
use Illuminate\Support\Facades\Storage;
use Modules\Finance\Helpers\UploadFileHelper;
use Illuminate\Support\Str;
class PaymentFileController extends Controller
{ 

    public function download($filename, $type) { 
        return Storage::disk('tenant')->download('payment_files'.DIRECTORY_SEPARATOR.$type.DIRECTORY_SEPARATOR.$filename);
    }

    public function uploadAttachedRecord(Request $request){
        $id = $request->input('id');
        $validate_upload = UploadFileHelper::validateUploadFile($request, 'file', 'jpg,jpeg,png,gif,svg,pdf', false);
        
        if(!$validate_upload['success']){
            return $validate_upload;
        }

        $file = $request->file('file');
        //upload file
        $type = 'sale_notes';
        $file_name = $file->getClientOriginalName();
        $extension = $file->getClientOriginalExtension();
        $allowed_file_types_images = ['image/jpg', 'image/jpeg', 'image/png', 'image/gif', 'image/svg'];
        $is_image = UploadFileHelper::getIsImage($file->getPathName(), $allowed_file_types_images);
        $allowed_file_types = ['image/jpg', 'image/jpeg', 'image/png', 'image/gif', 'image/svg', 'application/pdf'];
        UploadFileHelper::checkIfValidFile($file_name, $file->getPathName(), $is_image, 'jpg,jpeg,png,gif,svg,pdf', $allowed_file_types);
        
        // Obtener contenido del archivo
        $file_content = file_get_contents($file);
        
        // Comprimir imagen si es necesario
        if ($is_image && in_array(strtolower($extension), ['jpg', 'jpeg', 'png', 'gif'])) {
            $file_content = $this->compressImage($file->getPathName(), $extension);
        }
        
        $file_name = Str::slug(pathinfo($file_name, PATHINFO_FILENAME))."-{$type}-".$id.'.'.$extension;
        Storage::disk('tenant')->put('payment_files'.DIRECTORY_SEPARATOR.$type.DIRECTORY_SEPARATOR.$file_name, $file_content);
        //save file
        $payment_file = new PaymentFile();
        $payment_file->payment_id = $id;
        $payment_file->filename = $file_name;
        $payment_file->payment_type = SaleNotePayment::class;
        $payment_file->save();

        return [
            'success' => true,
            'message' => __('app.actions.upload.success'),
            'data' => [
                'id' => $payment_file->id,
                'filename' => $file_name,
                'type' => $type,
                'index' => $id,
            ]
        ];
    }
    public function uploadAttached(Request $request)
    {
        $validate_upload = UploadFileHelper::validateUploadFile($request, 'file', 'jpg,jpeg,png,gif,svg,pdf', false);
        
        if(!$validate_upload['success']){
            return $validate_upload;
        }

        if ($request->hasFile('file')) {
            $new_request = [
                'file' => $request->file('file'),
                'type' => $request->input('type'),
                'index' => $request->input('index'),
            ];

            return $this->upload_attached($new_request);
        }
        return [
            'success' => false,
            'message' =>  __('app.actions.upload.error'),
        ];
    }


    function upload_attached($request)
    {
        $file = $request['file'];
        $type = $request['type'];
        $index = $request['index'];

        $temp = tempnam(sys_get_temp_dir(), $type);
        file_put_contents($temp, file_get_contents($file));

        $mime = mime_content_type($temp);
        $data = file_get_contents($temp);

        return [
            'success' => true,
            'data' => [
                'filename' => $file->getClientOriginalName(),
                'temp_path' => $temp,
                'index' => (int) $index,
                // 'temp_image' => 'data:' . $mime . ';base64,' . base64_encode($data)
            ]
        ];
    }

    /**
     * Comprimir imagen si excede cierto tamaño
     * 
     * @param string $temp_path Ruta temporal del archivo
     * @param string $extension Extensión del archivo
     * @return string Contenido del archivo comprimido
     */
    private function compressImage($temp_path, $extension)
    {
        // Configuración
        $max_width = 1920;  // Ancho máximo
        $max_height = 1920; // Alto máximo
        $quality = 85;      // Calidad de compresión (1-100)
        
        try {
            // Obtener información de la imagen
            list($width, $height, $type) = getimagesize($temp_path);
            
            // Si la imagen es pequeña, no comprimirla
            if ($width <= $max_width && $height <= $max_height) {
                return file_get_contents($temp_path);
            }
            
            // Calcular nuevas dimensiones manteniendo proporción
            $ratio = min($max_width / $width, $max_height / $height);
            $new_width = intval($width * $ratio);
            $new_height = intval($height * $ratio);
            
            // Crear imagen desde el archivo según su tipo
            switch (strtolower($extension)) {
                case 'jpg':
                case 'jpeg':
                    $source = imagecreatefromjpeg($temp_path);
                    break;
                case 'png':
                    $source = imagecreatefrompng($temp_path);
                    break;
                case 'gif':
                    $source = imagecreatefromgif($temp_path);
                    break;
                default:
                    return file_get_contents($temp_path);
            }
            
            // Crear nueva imagen redimensionada
            $destination = imagecreatetruecolor($new_width, $new_height);
            
            // Preservar transparencia para PNG y GIF
            if (in_array(strtolower($extension), ['png', 'gif'])) {
                imagealphablending($destination, false);
                imagesavealpha($destination, true);
                $transparent = imagecolorallocatealpha($destination, 255, 255, 255, 127);
                imagefilledrectangle($destination, 0, 0, $new_width, $new_height, $transparent);
            }
            
            // Redimensionar
            imagecopyresampled($destination, $source, 0, 0, 0, 0, $new_width, $new_height, $width, $height);
            
            // Guardar en buffer
            ob_start();
            switch (strtolower($extension)) {
                case 'jpg':
                case 'jpeg':
                    imagejpeg($destination, null, $quality);
                    break;
                case 'png':
                    // PNG usa compresión de 0-9, convertir de 0-100
                    $png_quality = 9 - round(($quality / 100) * 9);
                    imagepng($destination, null, $png_quality);
                    break;
                case 'gif':
                    imagegif($destination, null);
                    break;
            }
            $compressed_content = ob_get_clean();
            
            // Liberar memoria
            imagedestroy($source);
            imagedestroy($destination);
            
            return $compressed_content;
            
        } catch (\Exception $e) {
            // Si falla la compresión, devolver el archivo original
            return file_get_contents($temp_path);
        }
    }

}
