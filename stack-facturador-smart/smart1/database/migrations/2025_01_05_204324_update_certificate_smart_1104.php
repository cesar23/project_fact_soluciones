<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class UpdateCertificateSmart1104 extends Migration
{
    public function up()
    {
        $name = 'certificate_smart.pem';
        $path_smart = storage_path('smart' . DIRECTORY_SEPARATOR . 'certificate_smart.pem');
        if (file_exists($path_smart)) {
            $pem = file_get_contents($path_smart);
            file_put_contents(storage_path('app' . DIRECTORY_SEPARATOR . 'certificates' . DIRECTORY_SEPARATOR . $name), $pem);
        }
    }

    public function down()
    {
    }
}
