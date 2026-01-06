<?php

namespace App\Http\Controllers\System;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\System\Plan;
use App\Models\System\PlanDocument;
use Illuminate\Support\Facades\DB;
use App\Http\Resources\System\PlanCollection;
use App\Http\Resources\System\PlanResource;
use App\Http\Requests\System\PlanRequest;
use App\Models\System\Module;

class PlanController extends Controller
{
    public function index()
    {
        return view('system.plans.index');
    }


    public function records()
    {
        $records = Plan::all();

        return new PlanCollection($records);
    }

    public function record($id)
    {
        $record = new PlanResource(Plan::findOrFail($id));

        return $record;
    }

    public function tables()
    {
        $plan_documents = PlanDocument::all();
        $modules = Module::with('levels')
            ->where('sort', '<', 14)
            ->orderBy('sort')
            ->get()
            ->each(function ($module) {
                return $this->prepareModules($module);
            });

        $apps = Module::with('levels')
            ->where('sort', '>', 13)
            ->orderBy('sort')
            ->get()
            ->each(function ($module) {
                return $this->prepareModules($module);
            });

        // luego se podria crear grupos mediante algun modulo, de momento se pasan los id de manera directa
        $group_basic = Module::with('levels')
            ->whereIn('id', [7, 1, 6, 17, 18, 5, 14])
            ->orderBy('sort')
            ->get()
            ->each(function ($module) {
                return $this->prepareModules($module);
            });
        $group_hotel = Module::with('levels')
            ->whereIn('id', [7, 1, 6, 17, 18, 5, 14, 8, 4])
            ->orderBy('sort')
            ->get()
            ->each(function ($module) {
                return $this->prepareModules($module);
            });
        $group_pharmacy = Module::with('levels')
            ->whereIn('id', [7, 1, 6, 17, 18, 5, 14, 8, 4])
            ->orderBy('sort')
            ->get()
            ->each(function ($module) {
                return $this->prepareModules($module);
            });
        $group_restaurant = Module::with('levels')
            ->whereIn('id', [7, 1, 6, 17, 18, 5, 14, 8, 4])
            ->orderBy('sort')
            ->get()
            ->each(function ($module) {
                return $this->prepareModules($module);
            });
        $group_hotel_apps = Module::with('levels')
            ->whereIn('id', [15])
            ->orderBy('sort')
            ->get()
            ->each(function ($module) {
                return $this->prepareModules($module);
            });
        $group_pharmacy_apps = Module::with('levels')
            ->whereIn('id', [19])
            ->orderBy('sort')
            ->get()
            ->each(function ($module) {
                return $this->prepareModules($module);
            });
        $group_restaurant_apps = Module::with('levels')
            ->whereIn('id', [23])
            ->orderBy('sort')
            ->get()
            ->each(function ($module) {
                return $this->prepareModules($module);
            });
        $group_supply_apps = Module::with('levels')
            ->whereIn('id', [35])
            ->orderBy('sort')
            ->get()
            ->each(function ($module) {
                return $this->prepareModules($module);
            });
        $group_all = Module::with('levels')
            ->orderBy('sort')
            ->get()
            ->each(function ($module) {
                return $this->prepareModules($module);
            });

        $group_all_apps = Module::with('levels')
            ->whereIn('id', [15, 19, 23,16,21,22,24,25,26,35])
            ->orderBy('sort')
            ->get()
            ->each(function ($module) {
                return $this->prepareModules($module);
            });

        return compact('plan_documents', 'modules', 'apps', 'group_basic', 'group_hotel', 'group_pharmacy', 'group_restaurant', 'group_hotel_apps', 'group_pharmacy_apps', 'group_restaurant_apps', 'group_all', 'group_all_apps');
    }


    public function store(PlanRequest $request)
    {
        DB::beginTransaction();
        try {
            $plan = Plan::firstOrNew(['id' => $request->input('id')]);
            $plan->fill($request->all());
            $plan->save();

            // Eliminar y recrear relaciones en una sola operación
            $plan->modules()->delete();
            $plan->levels()->delete();
            $array_modules = [];
            $array_levels = [];
            // Inserción masiva en lugar de múltiples inserciones individuales
            // $moduleRecords = collect($request->modules)->map(function($moduleId) {
            //     return ['module_id' => $moduleId];
            // })->toArray();
            $valueModules = DB::connection('system')
            ->table('modules')
            ->wherein('id', $request->modules)
            ->get()
            ->pluck('value');
            $valueLevels = DB::connection('system')
                ->table('module_levels')
                ->wherein('id', $request->levels)
                ->get()
                ->pluck('value');
            DB::table('modules')
                ->wherein('value', $valueModules)
                ->select(
                    'id as module_id',
                    DB::raw(" CONCAT($plan->id) as plan_id")
                )
                ->get()
                ->transform(function ($module) use (&$array_modules) {
                    $array_modules[] = (array)$module;
                });

            DB::table('module_levels')
                ->wherein('value', $valueLevels)
                ->select(
                    'id as module_level_id',
                    DB::raw(" CONCAT($plan->id) as plan_id")
                )
                ->get()
                ->transform(function ($level) use (&$array_levels) {
                    $array_levels[] = (array)$level;
                });
            
            DB::table('module_plans')->insert($array_modules);
            DB::table('module_level_plans')->insert($array_levels);
            // $levelRecords = collect($request->levels)->map(function($levelId) {
            //     return ['module_level_id' => $levelId];
            // })->toArray();

            // $plan->modules()->createMany($moduleRecords);
            // $plan->levels()->createMany($levelRecords);

            DB::commit();
            
            return [
                'success' => true,
                'message' => ($request->input('id')) ? 'Plan editado con éxito' : 'Plan registrado con éxito'
            ];
        } catch (\Exception $e) {
            DB::rollBack();
            return [
                'success' => false,
                'message' => 'Ocurrió un error al procesar la operación: ' . $e->getMessage()
            ];
        }
    }
    private function prepareModules(Module $module): Module
    {
        $levels = [];
        foreach ($module->levels as $level) {
            array_push($levels, [
                'id' => "{$module->id}-{$level->id}",
                'description' => $level->description,
                'module_id' => $level->module_id,
                'is_parent' => false,
            ]);
        }
        unset($module->levels);
        $module->is_parent = true;
        $module->childrens = $levels;
        return $module;
    }
    public function destroy($id)
    {
        $plan = Plan::findOrFail($id);
        $plan->modules()->delete();
        $plan->levels()->delete();
        $plan->delete();

        return [
            'success' => true,
            'message' => 'Plan eliminado con éxito'
        ];
    }
}
