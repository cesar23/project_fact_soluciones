<?php

namespace Modules\Payroll\Http\Controllers;

use App\Models\Tenant\Company;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Routing\Controller;
use Modules\Payroll\Http\Resources\PayrollCollection;
use Modules\Payroll\Models\Payroll;
use Modules\Payroll\Exports\PayrollExport;
use Maatwebsite\Excel\Facades\Excel;
use Carbon\Carbon;
use PDF; // Add this import

class PayrollController extends Controller
{
    /**
     * Display a listing of the resource.
     * @return Response
     */
    public function index()
    {
        return view('payroll::index');
    }

    function getCode()
    {
    }
    public function store(Request $request)
    {
        $data = $request->all();
        if (!array_key_exists('id', $data) || empty($data['id'])) {
            $data['id'] = null;
        }
        $requiredFields = ['name', 'last_name', 'sex'];
        foreach ($requiredFields as $field) {
            if (empty($data[$field])) {
                return response()->json(['success' => false, 'message' => "El campo $field es obligatorio"], 400);
            }
        }

        if (empty($data['code'])) {
            $fullName = $data['name'] . ' ' . $data['last_name'];
            $nameParts = explode(' ', $fullName);
            $initials = '';
            foreach ($nameParts as $part) {
                $initials .= strtoupper(substr($part, 0, 1));
            }
            $randomNumber = rand(100, 999);
            $data['code'] = $initials . $randomNumber;
        }

        $record = Payroll::firstOrNew(['id' => $data['id']]);
        $record->fill($data);
        $record->save();

        return response()->json(['success' => true, 'message' => 'Registro guardado']);
    }

    public function records(Request $request)
    {
        $query = $this->getRecords($request);
        return new PayrollCollection($this->getPaginatedRecords($query));
    }

    protected function getRecords(Request $request)
    {
        $query = Payroll::query();
        if ($request->has('name') && !empty($request->name)) {
            $query->where(function ($q) use ($request) {
                $q->where('name', 'like', '%' . $request->name . '%')
                    ->orWhere('last_name', 'like', '%' . $request->name . '%')
                    ->orWhere('code', 'like', '%' . $request->name . '%');
            });
        }

        if ($request->has('job_title') && !empty($request->job_title)) {
            $query->where('job_title', 'like', '%' . $request->job_title . '%');
        }

        if ($request->has('sex') && !empty($request->sex)) {
            $query->where('sex', $request->sex);
        }

        if ($request->has('admission_date_start') && !empty($request->admission_date_start)) {
            $query->where('admission_date', '>=', $request->admission_date_start);
        }

        if ($request->has('admission_date_end') && !empty($request->admission_date_end)) {
            $query->where('admission_date', '<=', $request->admission_date_end);
        }

        if ($request->has('cessation_date_start') && !empty($request->cessation_date_start)) {
            $query->where('cessation_date', '>=', $request->cessation_date_start);
        }

        if ($request->has('cessation_date_end') && !empty($request->cessation_date_end)) {
            $query->where('cessation_date', '<=', $request->cessation_date_end);
        }

        if ($request->has('age') && !empty($request->age)) {
            $query->where('age', $request->age);
        }

        return $query;
    }

    protected function getPaginatedRecords($query)
    {
        return $query->paginate(config('tenant.items_per_page'));
    }

    public function tables()
    {
        return response()->json(['success' => true]);
    }

    public function destroy($id)
    {
        $record = Payroll::findOrFail($id);
        $record->delete();

        return response()->json(['success' => true, 'message' => 'Registro eliminado']);
    }

    public function record($id)
    {
        $record = Payroll::findOrFail($id);

        return $record->getCollectionData();
    }
    public function columns()
    {

        return [
            'code' => 'Código',
            'name' => 'Nombre',
            'last_name' => 'Apellido',
            'age' => 'Edad',
            'sex' => 'Sexo',
            'job_title' => 'Puesto',
            'admission_date' => 'Fecha de ingreso',
            'cessation_date' => 'Fecha de cese',
        ];
    }

    public function exportExcel(Request $request)
    {
        $query = $this->getRecords($request);
        $records = $query->get()->toArray();
        $currentDateTime = Carbon::now()->format('Ymd_His');
        $fileName = 'planilla_' . $currentDateTime . '.xlsx';
        return Excel::download(new PayrollExport($records), $fileName);
    }

    public function exportPdf(Request $request)
    {
        $query = $this->getRecords($request);
        $records = $query->get();
        $company = Company::first();
        $pdf = PDF::loadView('payroll::reports.pdf', compact('records','company'))->setPaper('a4', 'landscape');
        $currentDateTime = Carbon::now()->format('Ymd_His');
        return $pdf->stream('planilla_' . $currentDateTime . '.pdf');
    }
}
