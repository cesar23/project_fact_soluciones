<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Models\Tenant\AdminKey;
use App\Models\Tenant\KeyUsageLog;
use App\Models\Tenant\User;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class AdminKeyController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = AdminKey::with(['admin:id,name,email'])
            ->withCount('usageLogs')
            ->orderBy('created_at', 'desc');

        if ($request->filled('admin_id')) {
            $query->where('admin_id', $request->admin_id);
        }

        if ($request->filled('is_active')) {
            $query->where('is_active', $request->boolean('is_active'));
        }

        $adminKeys = $query->paginate($request->get('per_page', 15));

        return response()->json($adminKeys);
    }

    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'description' => 'nullable|string|max:500',
            'expires_at' => 'nullable|date|after:now',
            'max_uses' => 'nullable|integer|min:1|max:1000',
        ]);
        $user_id = $request->user_id;
        if (!$user_id) {
            $user = auth()->user();
        } else {
            $user = User::find($user_id);
        }
        // Verificar que el usuario sea administrador
        if (!$user->type === 'admin') {
            return response()->json(['error' => 'No tienes permisos para crear claves'], 403);
        }

        // Desactivar clave activa anterior si existe
        AdminKey::where('admin_id', $user->id)
            ->where('is_active', true)
            ->update(['is_active' => false]);

        $adminKey = AdminKey::create([
            'admin_id' => $user->id,
            'key_code' => $this->generateKeyCode(),
            'is_active' => true,
            'expires_at' => $request->expires_at,
            'max_uses' => $request->max_uses,
            'current_uses' => 0,
            'description' => $request->description,
        ]);

        $adminKey->load('admin:id,name,email');

        return response()->json([
            'success' => true,
            'message' => 'Clave generada correctamente',
            'data' => $adminKey
        ]);
    }

    public function show(AdminKey $adminKey): JsonResponse
    {
        $adminKey->load(['admin:id,name,email', 'usageLogs' => function ($query) {
            $query->with('seller:id,name,email')
                ->orderBy('created_at', 'desc')
                ->limit(50);
        }]);

        return response()->json($adminKey);
    }

    public function update(Request $request, AdminKey $adminKey): JsonResponse
    {
        $request->validate([
            'description' => 'nullable|string|max:500',
            'expires_at' => 'nullable|date',
            'max_uses' => 'nullable|integer|min:1|max:1000',
        ]);

    

        $adminKey->update($request->only(['description', 'expires_at', 'max_uses']));

        return response()->json([
            'success' => true,
            'message' => 'Clave actualizada correctamente',
            'data' => $adminKey
        ]);
    }

    public function toggleStatus(AdminKey $adminKey): JsonResponse
    {
        $admin_id = $adminKey->admin_id;

        if ($adminKey->is_active) {
            $adminKey->update(['is_active' => false]);
            $message = 'Clave desactivada correctamente';
        } else {
            // Desactivar otras claves activas del mismo admin
            AdminKey::where('admin_id', $admin_id)
                ->where('is_active', true)
                ->update(['is_active' => false]);

            $adminKey->update(['is_active' => true]);
            $message = 'Clave activada correctamente';
        }

        return response()->json([
            'success' => true,
            'message' => $message,
            'data' => $adminKey->fresh()
        ]);
    }

    public function validateKey(Request $request): JsonResponse
    {
        $request->validate([
            'key_code' => 'required|string|size:8',
            'operation_type' => 'required|string|in:voided,credit_note',
            'document_id' => 'nullable|integer',
        ]);
        $adminKey = AdminKey::where('key_code', $request->key_code)
            ->active()
            ->first();

        if (!$adminKey) {
            return response()->json([
                'success' => false,
                'message' => 'Clave inválida o inactiva'
            ], 422);
        }

        if (!$adminKey->canUse()) {
            $reason = 'Clave no disponible';
            if ($adminKey->expires_at && $adminKey->expires_at->isPast()) {
                $reason = 'Clave expirada';
            } elseif ($adminKey->max_uses && $adminKey->current_uses >= $adminKey->max_uses) {
                $reason = 'Clave sin usos disponibles';
            }

            return response()->json([
                'success' => false,
                'message' => $reason
            ], 422);
        }

        return response()->json([
            'success' => true,
            'message' => 'Clave válida',
            'data' => [
                'admin_name' => $adminKey->admin->name,
                'remaining_uses' => $adminKey->max_uses ? ($adminKey->max_uses - $adminKey->current_uses) : null,
                'expires_at' => $adminKey->expires_at ? $adminKey->expires_at->format('Y-m-d H:i:s') : null,
            ]
        ]);
    }

    public function useKey(Request $request): JsonResponse
    {
        $request->validate([
            'key_code' => 'required|string|size:8',
            'operation_type' => 'required|string|in:voided,credit_note',
            'document_id' => 'nullable|integer',
        ]);

        $adminKey = AdminKey::where('key_code', $request->key_code)
            ->active()
            ->first();

        if (!$adminKey || !$adminKey->canUse()) {
            return response()->json([
                'success' => false,
                'message' => 'Clave no válida para usar'
            ], 422);
        }

        // Registrar uso
        KeyUsageLog::create([
            'seller_id' => auth()->id(),
            'admin_key_id' => $adminKey->id,
            'document_id' => $request->document_id,
            'operation_type' => $request->operation_type,
            'created_at' => now(),
        ]);

        // Incrementar contador de usos
        $adminKey->incrementUsage();

        return response()->json([
            'success' => true,
            'message' => 'Operación autorizada correctamente',
            'data' => [
                'remaining_uses' => $adminKey->fresh()->max_uses ?
                    ($adminKey->max_uses - $adminKey->current_uses - 1) : null,
            ]
        ]);
    }

    public function getUsageLogs(Request $request): JsonResponse
    {
        $query = KeyUsageLog::with(['seller:id,name,email', 'adminKey:id,key_code,description'])
            ->orderBy('created_at', 'desc');

        if ($request->filled('seller_id')) {
            $query->where('seller_id', $request->seller_id);
        }

        if ($request->filled('admin_key_id')) {
            $query->where('admin_key_id', $request->admin_key_id);
        }

        if ($request->filled('operation_type')) {
            $query->where('operation_type', $request->operation_type);
        }

        if ($request->filled('date_from')) {
            $query->where('created_at', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->where('created_at', '<=', $request->date_to . ' 23:59:59');
        }

        $document_ids = $query->pluck('document_id')->unique();
        $documentsData = DB::connection('tenant')->table('documents')
            ->select('id', 'number', 'series')
            ->whereIn('id', $document_ids)->get()->groupBy('id');

        $logs = $query->paginate($request->get('per_page', 15));
        $logs->getCollection()->transform(function ($log) use ($documentsData) {
            $document_number = $documentsData->get($log->document_id)->first()->number;
            $document_series = $documentsData->get($log->document_id)->first()->series;
            $log->document_number = $document_series . '-' . $document_number;
            return $log;
        });

        return response()->json($logs);
    }

    public function getCurrentUserActiveKey($id): JsonResponse
    {
        $activeKey = AdminKey::where('admin_id', $id)
            ->available()
            ->first();

        if (!$activeKey) {
            return response()->json([
                'success' => false,
                'message' => 'No tienes clave activa disponible'
            ]);
        }

        return response()->json([
            'success' => true,
            'data' => [
                'id' => $activeKey->id,
                'key_code' => $activeKey->key_code,
                'is_active' => $activeKey->is_active,
                'description' => $activeKey->description,
                'current_uses' => $activeKey->current_uses,
                'max_uses' => $activeKey->max_uses,
                'expires_at' => $activeKey->expires_at ? $activeKey->expires_at->format('Y-m-d H:i:s') : null,
                'remaining_uses' => $activeKey->max_uses ?
                    ($activeKey->max_uses - $activeKey->current_uses) : null,
            ]
        ]);
    }

    private function generateKeyCode(): string
    {
        do {
            $keyCode = strtoupper(Str::random(8));
            $exists = AdminKey::where('key_code', $keyCode)->exists();
        } while ($exists);

        return $keyCode;
    }
}
