<?php
namespace App\Http\Controllers\System;

use App\Http\Controllers\Controller;
use Exception;
use Illuminate\Http\Request;
use App\Models\System\Configuration;
use Illuminate\Support\Facades\DB;
use App\Models\System\Client;
use Hyn\Tenancy\Environment;
use Modules\Finance\Helpers\UploadFileHelper;


class WhatsappController extends Controller
{

    public function questions()
    {
        $sender =env('SENDER');
        $api_whatsapp = env('API_WHATSAPP');
        return view('system.whatsapp.questions', compact('sender','api_whatsapp'));
    }

    public function answers()
    {
        $sender =env('SENDER');
        $api_whatsapp = env('API_WHATSAPP');
         return view('system.whatsapp.answers', compact('sender','api_whatsapp'));
    }
      

}
