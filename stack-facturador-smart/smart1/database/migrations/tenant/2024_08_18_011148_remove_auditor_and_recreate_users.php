<?php

use App\Models\Tenant\User;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

class RemoveAuditorAndRecreateUsers extends Migration
{
    public function up()
    {
        try {
            DB::beginTransaction();

            User::where('type', '<>', 'superadmin')
                ->update(['auditor' => 0, 'recreate_documents' => 0]);

            DB::commit();
            return ['success' => true, 'message' => 'Usuarios actualizados correctamente'];
        } catch (\Exception $e) {
            DB::rollBack();
            return ['success' => false, 'message' => 'Error al actualizar usuarios', 'error' => $e->getMessage()];
        }
    }

    public function down()
    {
    }
}
