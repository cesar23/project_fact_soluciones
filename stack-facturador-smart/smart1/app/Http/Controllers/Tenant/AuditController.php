<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Models\Tenant\Audit;
use Illuminate\Http\Request;

class AuditController extends Controller
{
    /**
     * Obtener historial de auditoría
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function history(Request $request)
    {
        $request->validate([
            'auditable_type' => 'required|string',
            'auditable_id' => 'required|integer',
            'page' => 'nullable|integer|min:1',
            'per_page' => 'nullable|integer|min:1|max:100',
        ]);

        $auditableType = $request->input('auditable_type');
        $auditableId = $request->input('auditable_id');
        $perPage = $request->input('per_page', 10);

        // Obtener auditorías solo con user_id (cambios de usuarios, no del sistema)
        $audits = Audit::where('auditable_type', $auditableType)
            ->where('auditable_id', $auditableId)
            ->userChanges() // Solo cambios de usuarios
            ->with('user:id,name')
            ->orderBy('created_at', 'desc')
            ->paginate($perPage);

        // Formatear datos
        $data = $audits->map(function ($audit) {
            return [
                'id' => $audit->id,
                'event' => $audit->event,
                'event_description' => $audit->getEventDescription(),
                'field_name' => $audit->field_name,
                'old_value' => $audit->old_value,
                'new_value' => $audit->new_value,
                'description' => $audit->description,
                'user_name' => $audit->getUserName(),
                'ip_address' => $audit->ip_address,
                'created_at' => $audit->created_at->format('Y-m-d H:i:s'),
                'related_document' => $this->getRelatedDocumentNumber($audit),
            ];
        });

        return response()->json([
            'success' => true,
            'data' => $data,
            'total' => $audits->total(),
            'current_page' => $audits->currentPage(),
            'per_page' => $audits->perPage(),
            'last_page' => $audits->lastPage(),
        ]);
    }

    /**
     * Obtener serie y número del documento relacionado
     *
     * @param Audit $audit
     * @return string|null
     */
    private function getRelatedDocumentNumber($audit)
    {
        if (!$audit->related_type || !$audit->related_id) {
            return null;
        }

        $relatedModel = $audit->getRelatedModel();

        if ($relatedModel && isset($relatedModel->series) && isset($relatedModel->number)) {
            return $relatedModel->series . '-' . $relatedModel->number;
        }

        return null;
    }
}
