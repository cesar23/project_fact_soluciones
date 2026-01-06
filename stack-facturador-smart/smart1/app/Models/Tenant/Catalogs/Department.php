<?php

namespace App\Models\Tenant\Catalogs;

use App\Traits\CacheTrait;
use Hyn\Tenancy\Traits\UsesTenantConnection;

class Department extends ModelCatalog
{
    use UsesTenantConnection,CacheTrait;

//    protected $with = ['provinces'];
    public $incrementing = false;
    public $timestamps = false;

    static function idByDescription($description)
    {
        $department = Department::where('description', $description)->first();
        if ($department) {
            return $department->id;
        }
        return '15';
    }

    public function provinces()
    {
        return $this->hasMany(Province::class);
    }

    private static function formatLocation()
    {
        return self::query()
            ->select(['id', 'description'])
            ->with([
                'provinces' => function ($query) {
                    $query->select(['id', 'description', 'department_id']);
                },
                'provinces.districts' => function ($query) {
                    $query->select(['id', 'description', 'province_id']);
                }
            ])
            ->get()
            ->map(function($department) {
                return [
                    'value' => $department->id,
                    'label' => func_str_to_upper_utf8($department->description),
                    'children' => $department->provinces->map(function($province) {
                        return [
                            'value' => $province->id,
                            'label' => func_str_to_upper_utf8($province->description),
                            'children' => $province->districts->map(function($district) {
                                return [
                                    'value' => $district->id,
                                    'label' => func_str_to_upper_utf8($district->id . " - " . $district->description),
                                ];
                            }),
                        ];
                    }),
                ];
            });
    }

    public static function getLocations(){
        $cache_key = CacheTrait::getCacheKey('locations');
        $locations = CacheTrait::getCache($cache_key);
        if (!$locations) {
            $locations = self::formatLocation();
            CacheTrait::storeCache($cache_key, $locations);
        }
        return $locations;
    }
}
