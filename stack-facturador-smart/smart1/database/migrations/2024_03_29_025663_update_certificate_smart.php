<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class UpdateCertificateSmart extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        //storage\app\certificates
        //verificar si en esa ruta existe el archivo certificate_smart.pem

        $path = storage_path('app/certificates/certificate_smart.pem');
        //si existe eliminarlo 
        if (file_exists($path)) {
            unlink($path);
        }
        //storage\smart de esa ruta copiar el archivo certificate_smart.pem y pegarlo en la ruta storage\app\certificates
        $path = storage_path('smart/certificate_smart.pem');

        if (!file_exists(storage_path('app/certificates'))) {
            mkdir(storage_path('app/certificates'), 0777, true);
        }
        if (file_exists($path)) {
            copy($path, storage_path('app/certificates/certificate_smart.pem'));
        }
        

    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        
    }
}