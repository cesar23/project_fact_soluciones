<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Http\Requests\Tenant\SeriesRequest;
use App\Http\Resources\Tenant\SeriesCollection;
use App\Models\Tenant\Catalogs\DocumentType;
use App\Models\Tenant\Company;
use App\Models\Tenant\Series;
use App\Models\Tenant\User;
use App\Traits\CacheTrait;

class SeriesController extends Controller
{
    use CacheTrait;
    public function create()
    {
        return view('tenant.series.form');
    }
        public function recordsWithoutEstablishment($document_type = null)
    {
        $records = Series::query();
        if (!empty($document_type)) {
            $records->FilterDocumentType($document_type);
        }
        $company = Company::active();
        if ($company->is_rus) {
            $records = $records
                ->where('document_type_id', '<>', '01');
        }
        $records = $records->get();
        return new SeriesCollection($records);
    }
    public function records($establishmentId, $document_type = null)
    {
        $records = Series::FilterEstablishment($establishmentId);
        if (!empty($document_type)) {
            $records->FilterDocumentType($document_type);
        }
        $company = Company::active();
        if ($company->is_rus) {
            $records = $records
                ->where('document_type_id', '<>', '01');
        }
        $records = $records->get();
        return new SeriesCollection($records);
    }

    public function tables()
    {
        $document_types = DocumentType::OnlyAvaibleDocuments()->get();

        return compact('document_types');
    }

    public function store(SeriesRequest $request)
    {

        $validate_series = $this->validateSeries($request);
        if (!$validate_series['success']) return $validate_series;

        $id = $request->input('id');
        $series = Series::firstOrNew(['id' => $id]);
        $series->fill($request->all());
        $series->save();

        $users_id = User::all()->pluck('id');
        foreach ($users_id as $user_id) {
            CacheTrait::clearCache("series_by_user_id_{$user_id}");
        }

        return [
            'success' => true,
            'message' => ($id) ? 'Serie editada con éxito' : 'Serie registrada con éxito'
        ];
    }


    /**
     * 
     * Validar datos
     *
     * @param  SeriesRequest $request
     * @return array
     */
    public function validateSeries(SeriesRequest $request)
    {

        $record = Series::where([['document_type_id', $request->document_type_id], ['number', $request->number]])->first();

        if ($record) {
            return [
                'success' => false,
                'message' => 'La serie ya ha sido registrada'
            ];
        }


        return [
            'success' => true,
            'message' => null
        ];
    }


    public function destroy($id)
    {
        $user_type = auth()->user()->type;
        $item = Series::findOrFail($id);
        $establishment_id = $item->establishment_id;
        $document_type_id = $item->document_type_id;
        $series_count = Series::where('establishment_id', $establishment_id)
                            ->where('document_type_id', $document_type_id)
                            ->count();

        if($series_count <= 1 && $user_type != 'superadmin') {
            return [
                'success' => false,
                'message' => 'No se puede eliminar la serie. Debe existir al menos una serie para este tipo de documento y establecimiento.'
            ];
        }
        $users_id = User::all()->pluck('id');
        foreach ($users_id as $user_id) {
            CacheTrait::clearCache("series_by_user_id_{$user_id}");
        }
        $item->delete();

        return [
            'success' => true,
            'message' => 'Serie eliminada con éxito'
        ];
    }
}
