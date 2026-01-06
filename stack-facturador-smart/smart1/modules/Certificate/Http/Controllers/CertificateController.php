<?php

namespace Modules\Certificate\Http\Controllers;

use App\CoreFacturalo\Helpers\QrCode\QrCodeGenerate;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\DB;
use Modules\Certificate\Http\Resources\CertificateCollection;
use Modules\Certificate\Http\Resources\CertificatePersonCollection;
use Modules\Certificate\Models\Certificate;
use Modules\Certificate\Models\CertificatePerson;
use Illuminate\Support\Str;
use Barryvdh\DomPDF\Facade\Pdf;
use Intervention\Image\Facades\Image;
use Exception;
use Illuminate\Support\Facades\Storage;
use Maatwebsite\Excel\Excel;
use Modules\Certificate\Import\CertificateImport;
use Modules\Finance\Helpers\UploadFileHelper;

class CertificateController extends Controller
{
    /**
     * Display a listing of the resource.
     * @return Response
     */
    public function index()
    {
        return view('certificate::index');
    }

    public function template()
    {
        return view('certificate::template');
    }

    public function recordTemplate($id)
    {
        $certificate = Certificate::with('items')->find($id);
        return response()->json($certificate);
    }

    public function createTemplate($id = null)
    {
        $certificate = null;
        if ($id) {
            $certificate = Certificate::with('items')->find($id);
            $certificate->image_url = ($certificate->water_mark_image !== null)
                ? asset('storage' . DIRECTORY_SEPARATOR . 'uploads' . DIRECTORY_SEPARATOR . 'certificates' . DIRECTORY_SEPARATOR . $certificate->water_mark_image)
                : null;
        }
        return view('certificate::create_template', compact('certificate'));
    }

    public function recordsTemplate()
    {
        $records = Certificate::query();
        return new CertificateCollection($records->paginate(20));
    }


    public function createQr($id)
    {
        $certificate = CertificatePerson::find($id);
        $url = url('certificate/print/' . $certificate->external_id);
        $qrCode = new QrCodeGenerate();

        return $qrCode->displayPNGBase64($url, 250);
    }

    public function delete($id)
    {
        $certificate = CertificatePerson::find($id);
        if ($certificate) {
            $certificate->delete();
            return response()->json([
                'success' => true,
                'message' => 'Certificado eliminado correctamente',
            ]);
        }
        return response()->json([
            'success' => false,
            'message' => 'Certificado no encontrado',
        ]);
    }

    public function allRecordsTemplate()
    {
        $records = Certificate::all()->transform(function ($item) {
            return [
                'id' => $item->id,
                'certificate_name' => $item->tag_1,
            ];
        });
        return response()->json($records);
    }

    public function deleteTemplate($id)
    {
        $certificate = Certificate::find($id);
        if ($certificate) {
            $certificate->items()->delete();
            $certificate->delete();
            return response()->json([
                'success' => true,
                'message' => 'Plantilla eliminada correctamente',
            ]);
        }
        return response()->json([
            'success' => false,
            'message' => 'Plantilla no encontrada',
        ]);
    }
    public function storeTemplate(Request $request)
    {
        DB::beginTransaction();
        try {
            $certificate = Certificate::firstOrCreate([
                'id' => $request->id,
            ]);
            $id = $request->id;
            $certificate->items()->delete();
            $certificate->fill($request->all());
            $temp_path = $request->input('temp_path');
            if ($temp_path) {

                $directory = 'public' . DIRECTORY_SEPARATOR . 'uploads' . DIRECTORY_SEPARATOR . 'certificates' . DIRECTORY_SEPARATOR;

                $slug_name = Str::slug($certificate->tag_1);
                $prefix_name = Str::limit($slug_name, 20, '');


                $file_name_old = $request->input('image');
                $file_name_old_array = explode('.', $file_name_old);
                $file_content = file_get_contents($temp_path);
                $datenow = date('YmdHis');
                $file_name = $prefix_name . '-' . $datenow . '.' . $file_name_old_array[1];

                UploadFileHelper::checkIfValidFile($file_name, $temp_path, true);

                Storage::put($directory . $file_name, $file_content);
                $certificate->water_mark_image = $file_name;
            } else if (!$request->input('image') && !$request->input('temp_path') && !$request->input('image_url')) {
                if (!$id) {
                    $certificate->water_mark_image = null;
                }
            }
            $certificate->save();
            foreach ($request->items as $item) {
                $certificate->items()->create($item);
            }
            DB::commit();

            return response()->json([
                'success' => true,
                'message' =>  $request->id ? 'Plantilla actualizada correctamente' : 'Plantilla creada correctamente',
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ]);
        }
    }

    private function getLastCorrelative($certificate)
    {
        $last_correlative = CertificatePerson::where('certificate_id', $certificate->certificate_id)->max('number');
        $last_correlative = $last_correlative ? $last_correlative + 1 : 1;
        return $last_correlative;
    }

    /**
     * Store a newly created resource in storage.
     * @param Request $request
     * @return Response
     */
    public function store(Request $request)
    {
        DB::beginTransaction();
        try {
            $certificate = CertificatePerson::firstOrCreate([
                'id' => $request->id,
            ]);

            $certificate->fill($request->all());
            if (!$request->id) {
                $certificate->external_id = Str::uuid();
                $certificate->series = "CERT";
                $certificate->number = $this->getLastCorrelative($certificate);
            }
            $certificate->save();
            DB::commit();
            return response()->json([
                'success' => true,
                'message' => $request->id ? 'Certificado actualizado correctamente' : 'Certificado creado correctamente',
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ]);
        }
    }

    public function print($uuid)
    {
        $certificate = CertificatePerson::where('external_id', $uuid)->first();
        
        $certificate_template = Certificate::find($certificate->certificate_id);
        $mpdf = new \Mpdf\Mpdf([
            'format' => 'A4-L', // A4 Landscape
            'fontDir' => array_merge(
                (new \Mpdf\Config\ConfigVariables())->getDefaults()['fontDir'],
                [public_path('new_fonts')]
            ),
            'fontdata' => array_merge(
                (new \Mpdf\Config\FontVariables())->getDefaults()['fontdata'],
                [
                    'certificatefont' => [
                        'R' => 'aargau-bold.ttf',
                        'B' => 'aargau-bold.ttf',
                    ],
                    'personfont' => [
                        'R' => 'ELEPHNT.ttf',
                    ],
                    'coursefont' => [
                        'R' => 'times.ttf',
                    ],
                ]
            ),
        ]);
        if ($certificate_template->water_mark_image) {
            $mpdf->showWatermarkImage = true;
            $mpdf->SetWatermarkImage(public_path('storage' . DIRECTORY_SEPARATOR . 'uploads' . DIRECTORY_SEPARATOR . 'certificates' . DIRECTORY_SEPARATOR . $certificate_template->water_mark_image), 1, [297, 210]);
        }
        $html = view('certificate::certificate_pdf', compact('certificate'))->render();

        $mpdf->WriteHTML($html);
        $mpdf->Output('certificado_' . $certificate->external_id . '.pdf', 'I');
    }

    public function import(Request $request)
    {
        $request->validate([
            'certicate_id' => 'required|numeric|min:1'
        ]);
        if ($request->hasFile('file')) {
            try {
                $import = new CertificateImport();
                $import->import($request->file('file'), null, Excel::XLSX);
                $data = $import->getData();
                return [
                    'success' => true,
                    'message' =>  __('app.actions.upload.success'),
                    'data' => $data
                ];
            } catch (Exception $e) {
                return [
                    'success' => false,
                    'message' =>  $e->getMessage()
                ];
            }
        }
        return [
            'success' => false,
            'message' =>  __('app.actions.upload.error'),
        ];
    }
    function upload_image($request)
    {
        $file = $request['file'];
        $type = $request['type'];

        $temp = tempnam(sys_get_temp_dir(), $type);
        file_put_contents($temp, file_get_contents($file));

        $mime = mime_content_type($temp);
        $data = file_get_contents($temp);

        return [
            'success' => true,
            'data' => [
                'filename' => $file->getClientOriginalName(),
                'temp_path' => $temp,
                'temp_image' => 'data:' . $mime . ';base64,' . base64_encode($data)
            ]
        ];
    }
    public function upload(Request $request)
    {

        $type = $request->input('type');
        if ($type == 'certificate_water_mark_image') {
            $file = $request->file('file');
            $extension = $file->getClientOriginalExtension();
            $isImage = in_array($extension, ['jpg', 'jpeg', 'png', 'gif', 'svg']);
            $validate_upload = UploadFileHelper::validateUploadFile($request, 'file', 'jpg,jpeg,png,pdf', $isImage);
        } else {
            $validate_upload = UploadFileHelper::validateUploadFile($request, 'file', 'jpg,jpeg,png,gif,svg');
        }
        if (!$validate_upload['success']) {
            return $validate_upload;
        }
        if ($request->hasFile('file')) {
            $new_request = [
                'file' => $request->file('file'),
                'type' => $request->input('type'),
            ];
            return $this->upload_image($new_request);
        }
        return [
            'success' => false,
            'message' =>  __('app.actions.upload.error'),
        ];
    }

    public function records(Request $request)
    {
        $column = $request->column;
        $value = $request->value;
        $records = CertificatePerson::query();
        if ($column == 'person' && $value) {
            $records->where(function ($query) use ($value) {
                $query->where('tag_1', 'like', '%' . $value . '%')
                    ->orWhere('tag_2', 'like', '%' . $value . '%');
            });
        }
        if ($column == 'course' && $value) {
            $records->where('tag_3', 'like', '%' . $value . '%');
        }
        if ($column == 'date' && $value) {
            $records->whereDate('created_at', $value);
        }
        $records = $records->orderBy('created_at', 'desc');
        return new CertificatePersonCollection($records->paginate(20));
    }

    public function record($id)
    {
        $certificate = CertificatePerson::find($id);
        return response()->json($certificate);
    }

    public function create($id = null)
    {
        $certificate = null;
        $templates = Certificate::all()->map(function ($item) {
            return [
                'id' => $item->id,
                'name' => $item->tag_1,
            ];
        });
        if ($id) {
            $certificate = CertificatePerson::find($id);
        }
        return view('certificate::create', compact('certificate', 'templates'));
    }

    public function getRecords(Request $request)
    {
        $records = CertificatePerson::query();
        return $records;
    }
}
