<?php

namespace Modules\Ecommerce\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\Tenant\ConfigurationEcommerce;
use App\Models\Tenant\Company;
use App\Http\Requests\Tenant\ConfigurationEcommerceRequest;
use App\Http\Resources\Tenant\ConfigurationEcommerceResource;
use Illuminate\Support\Facades\Storage;
use Modules\Finance\Helpers\UploadFileHelper;
use Illuminate\Support\Str;

class ConfigurationController extends Controller
{
    /**
     * Display a listing of the resource.
     * @return Response
     */
    public function index()
    {
        return view('ecommerce::configuration.index');
    }

    public function record()
    {
        $configuration = ConfigurationEcommerce::first();
        $record = new ConfigurationEcommerceResource($configuration);
        return $record;
    }

    public function get_configuration_digital_payments()
    {
        $configuration = ConfigurationEcommerce::first();
        $record = new ConfigurationEcommerceResource($configuration);
        return $record;
    }

    public function store_configuration_digital_payments(Request $request)
    {
        $id = $request->input('id');
        $configuration = ConfigurationEcommerce::find($id);
        $yape_number = $request->input('yape_number');
        $yape_name = $request->input('yape_name');
        $plin_number = $request->input('plin_number');
        $plin_name = $request->input('plin_name');

        $temp_path_plin = $request->input('temp_path_plin');
        $temp_path_yape = $request->input('temp_path_yape');
        if ($temp_path_plin) {
            $directory = 'public' . DIRECTORY_SEPARATOR . 'uploads' . DIRECTORY_SEPARATOR . 'ecommerce' . DIRECTORY_SEPARATOR;
            $slug_name = "plin";
            $prefix_name = Str::limit($slug_name, 20, '');
            $file_name_old = $request->input('filename_plin');
            $file_name_old_array = explode('.', $file_name_old);
            $file_content = file_get_contents($temp_path_plin);
            $file_name = $prefix_name . '.' . end($file_name_old_array);
            UploadFileHelper::checkIfValidFile($file_name, $temp_path_plin, true);
            Storage::put($directory . $file_name, $file_content);
            $image_plin = $file_name;
            $configuration->plin_qr = $image_plin;
        }
        if ($temp_path_yape) {
            $directory = 'public' . DIRECTORY_SEPARATOR . 'uploads' . DIRECTORY_SEPARATOR . 'ecommerce' . DIRECTORY_SEPARATOR;
            $slug_name = "yape";
            $prefix_name = Str::limit($slug_name, 20, '');
            $file_name_old = $request->input('filename_yape');
            $file_name_old_array = explode('.', $file_name_old);
            $file_content = file_get_contents($temp_path_yape);
            $file_name = $prefix_name . '.' .  end($file_name_old_array);
            UploadFileHelper::checkIfValidFile($file_name, $temp_path_yape, true);
            Storage::put($directory . $file_name, $file_content);
            $image_yape = $file_name;
            $configuration->yape_qr = $image_yape;
        }
        $configuration->yape_number = $yape_number;
        $configuration->yape_name = $yape_name;
        $configuration->plin_number = $plin_number;
        $configuration->plin_name = $plin_name;
        $configuration->save();

        return [
            'success' => true,
            'message' => 'Configuración adicional actualizada'
        ];
    }
    public function store_configuration(ConfigurationEcommerceRequest $request)
    {
        $id = $request->input('id');
        $configuration = ConfigurationEcommerce::find($id);
        $configuration->fill($request->all());
        $configuration->save();

        return [
            'success' => true,
            'message' => 'Configuración actualizada'
        ];
    }

    public function store_configuration_culqui(Request $request)
    {
        $id = $request->input('id');
        $configuration = ConfigurationEcommerce::find($id);
        $configuration->fill($request->all());
        $configuration->save();

        return [
            'success' => true,
            'message' => 'Configuración Culqui actualizada'
        ];
    }

    public function store_configuration_aditional(Request $request)
    {
        $id = $request->input('id');
        $configuration = ConfigurationEcommerce::find($id);
        $columns_virtual_store = $request->input('columns_virtual_store');
        $copyrigth_text = $request->input('copyrigth_text');
        $url_complaints = $request->input('url_complaints');
        $image_payment_methods = null;
        $image_complaints = null;
        $temp_path = $request->input('temp_path');
        $temp_path_complaints = $request->input('temp_path_complaints');
        if ($temp_path) {
            $directory = 'public' . DIRECTORY_SEPARATOR . 'uploads' . DIRECTORY_SEPARATOR . 'ecommerce' . DIRECTORY_SEPARATOR;
            $slug_name = "payment_methods";
            $prefix_name = Str::limit($slug_name, 20, '');
            $file_name_old = $request->input('image');
            $file_name_old_array = explode('.', $file_name_old);
            $file_content = file_get_contents($temp_path);
            $file_name = $prefix_name . '.' . end($file_name_old_array);
            UploadFileHelper::checkIfValidFile($file_name, $temp_path, true);
            Storage::put($directory . $file_name, $file_content);
            $image_payment_methods = $file_name;
            $configuration->image_payment_methods = $image_payment_methods;
        }
        if ($temp_path_complaints) {
            $directory = 'public' . DIRECTORY_SEPARATOR . 'uploads' . DIRECTORY_SEPARATOR . 'ecommerce' . DIRECTORY_SEPARATOR;
            $slug_name = "complaints";
            $prefix_name = Str::limit($slug_name, 20, '');
            $file_name_old = $request->input('image_c');
            $file_name_old_array = explode('.', $file_name_old);
            $file_content = file_get_contents($temp_path_complaints);
            $file_name = $prefix_name . '.' .  end($file_name_old_array);
            UploadFileHelper::checkIfValidFile($file_name, $temp_path_complaints, true);
            Storage::put($directory . $file_name, $file_content);
            $image_complaints = $file_name;
            $configuration->image_complaints = $image_complaints;
        }
        $configuration->columns_virtual_store = $columns_virtual_store;
        $configuration->copyrigth_text = $copyrigth_text;
        $configuration->url_complaints = $url_complaints;
        $configuration->save();

        return [
            'success' => true,
            'message' => 'Configuración adicional actualizada'
        ];
    }
    public function store_configuration_columns(Request $request)
    {
        $id = $request->input('id');
        $configuration = ConfigurationEcommerce::find($id);
        $configuration->fill($request->all());
        $configuration->save();

        return [
            'success' => true,
            'message' => 'Configuración columnas actualizada'
        ];
    }
    public function store_configuration_paypal(Request $request)
    {
        $id = $request->input('id');
        $configuration = ConfigurationEcommerce::find($id);
        $configuration->fill($request->all());
        $configuration->save();

        return [
            'success' => true,
            'message' => 'Configuración Paypal actualizada'
        ];
    }

    public function store_configuration_tag(Request $request)
    {
        $id = $request->input('id');
        $configuration = ConfigurationEcommerce::find($id);
        $configuration->fill($request->all());
        $configuration->save();

        return [
            'success' => true,
            'message' => 'Configuración Tags actualizada'
        ];
    }

    public function store_configuration_social(Request $request)
    {
        $id = $request->input('id');
        $configuration = ConfigurationEcommerce::find($id);
        $configuration->fill($request->all());
        $configuration->save();

        return [
            'success' => true,
            'message' => 'Configuración de Redes Sociales actualizada'
        ];
    }

    public function uploadFile(Request $request)
    {
        if ($request->hasFile('file')) {

            $config = ConfigurationEcommerce::first();
            $company = Company::first();

            $type = $request->input('type'); //logo_store

            $file = $request->file('file');
            $ext = $file->getClientOriginalExtension();
            $name = $type . '_' . $company->number . '.' . $ext;

            request()->validate(['file' => 'required|image|mimes:jpeg,png,jpg,gif,svg|max:2048']);

            UploadFileHelper::checkIfValidFile($name, $file->getPathName(), true);
            if ($type === 'logo_store') {
                $file->storeAs('public/uploads/logos', $name);
                $config->logo = $name;
            }
            if ($type === 'favicon_store') {
                $file->storeAs('public/uploads/favicons', $name);
                $config->favicon = $name;
            }


            $config->save();

            return [
                'success' => true,
                'message' => __('app.actions.upload.success'),
                'name' => $name,
                'type' => $type
            ];
        }
        return [
            'success' => false,
            'message' =>  __('app.actions.upload.error'),
        ];
    }
}
