<?php

namespace Modules\Finance\Traits;

use Carbon\Carbon;
use App\Models\Tenant\DocumentPayment;
use App\Models\Tenant\SaleNotePayment;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Modules\Finance\Helpers\UploadFileHelper;


trait FilePaymentTrait
{

    public function saveFiles($record, $request, $type)
    {

        $temp_path = $request->temp_path;
        if($temp_path) {

            $file_name_old = $request->filename;
            $file_name_old_array = explode('.', $file_name_old);
            $file_content = file_get_contents($temp_path);
            $extension = $file_name_old_array[1];
            $file_name = Str::slug($file_name_old_array[0])."-{$type}-".$record->id.'.'.$extension;

            // validaciones archivos
            $allowed_file_types_images = ['image/jpg', 'image/jpeg', 'image/png', 'image/gif', 'image/svg'];
            $is_image = UploadFileHelper::getIsImage($temp_path, $allowed_file_types_images);

            $allowed_file_types = ['image/jpg', 'image/jpeg', 'image/png', 'image/gif', 'image/svg', 'application/pdf'];
            UploadFileHelper::checkIfValidFile($file_name, $temp_path, $is_image, 'jpg,jpeg,png,gif,svg,pdf', $allowed_file_types);
            // validaciones archivos

            // Comprimir imagen si es necesario
            if ($is_image && in_array(strtolower($extension), ['jpg', 'jpeg', 'png', 'gif'])) {
                $file_content = $this->compressImage($temp_path, $extension);
            }

            $record->payment_file()->create([
                'filename' => $file_name
            ]);

            Storage::disk('tenant')->put('payment_files'.DIRECTORY_SEPARATOR.$type.DIRECTORY_SEPARATOR.$file_name, $file_content);


        }

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
        $max_width = 400;  // Ancho máximo
        $max_height = 400; // Alto máximo
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
    

    /**
     * 
     * Guardar archivos de los pagos relacionados a cada documento
     * 
     * Usado en:
     * Facturalo
     * SaleNoteController
     *
     * @param  array $row
     * @param  DocumentPayment|SaleNotePayment $record
     * @param  string $append_folder
     * @return void
     */
    private function saveFilesFromPayments($row, $record, $append_folder)
    {
        $temp_path = $row['temp_path'] ?? false;
        $filename = $row['filename'] ?? false;
        $filename_quotation = isset($row['filename_quotation']) ? $row['filename_quotation'] : false;
        if($temp_path && $filename)
        {
            $params_payment_file = [
                'temp_path' => $temp_path,
                'filename' => $filename
            ];

            $this->saveFiles($record, (object) $params_payment_file, $append_folder);
        }

        if($filename_quotation)
        {
            $payment_file = DB::connection('tenant')->table('payment_files')->where('filename', $filename_quotation)->first();
            if($payment_file)
            {
                $record->payment_file()->create([
                    'filename' => $payment_file->filename,
                    'payment_id' => $record->id,
                    'payment_type' => SaleNotePayment::class,
                ]);
            }
        }
    }

}
