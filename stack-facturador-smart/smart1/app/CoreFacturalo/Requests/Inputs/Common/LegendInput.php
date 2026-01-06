<?php

namespace App\CoreFacturalo\Requests\Inputs\Common;

use App\CoreFacturalo\Helpers\Number\NumberLetter;
use App\Models\Tenant\Company;
// use Modules\Document\Services\DocumentXmlService;
use App\Models\Tenant\Configuration;
use App\Models\Tenant\Catalogs\LegendType;


class LegendInput
{
    public static function set($inputs)
    {
        $legends = [];
        if (array_key_exists('legends', $inputs)) {
            if ($inputs['legends']) {
                foreach ($inputs['legends'] as $row) {
                    $code = $row['code'];
                    $value = $row['value'];

                    $legends[] = [
                        'code' => $code,
                        'value' => $value
                    ];
                }
            }
        }

        // if(Company::active()->operation_amazonia && in_array($inputs['document_type_id'], ['01', '03'])){

        //     $legends[] = [
        //         'code' => 2002,
        //         'value' => 'SERVICIOS PRESTADOS EN LA AMAZONÍA  REGIÓN SELVA PARA SER CONSUMIDOS EN LA MISMA'
        //     ];

        //     $legends[] = [
        //         'code' => 2001,
        //         'value' => 'BIENES TRANSFERIDOS EN LA AMAZONÍA REGIÓN SELVA PARA SER CONSUMIDOS EN LA MISMA'
        //     ];

        // }

        self::setLegendsForest($legends, $inputs);


        if (array_key_exists('total', $inputs)) {
            $legends[] = [
                'code' => 1000,
                'value' => NumberLetter::convertToLetter($inputs['total'])
            ];
        }

        return $legends;
    }


    /**
     * 
     * Agregar leyendas region selva, amazonia
     *
     * @param  array $legends
     * @param  array $inputs
     * @return void
     */
    public static function setLegendsForest(&$legends, $inputs)
    {

        if (Configuration::isEnabledLegendForestToXml() && in_array($inputs['document_type_id'], ['01', '03'])) {

            $search_legends = LegendType::filterLegendsForest()->get();
            $to_check = self::checkIsAService($inputs['items']);
            foreach ($search_legends as $value) {
                if ($value->id == '2001' && $to_check['has_unit']) {
                    $legends[] = [
                        'code' => $value->id,
                        'value' => $value->description
                    ];
                }
                if ($value->id == '2002' && $to_check['has_service']) {
                    $legends[] = [
                        'code' => $value->id,
                        'value' => $value->description
                    ];
                }
            }
        }
    }

    static function checkIsAService($items)
    {
        $has_service = false;
        $has_unit = false;

        foreach ($items as $row) {
            if (isset($row['item']['unit_type_id']) && $row['item']['unit_type_id'] == 'ZZ') {
                $has_service = true;
            }
            if (isset($row['item']['unit_type_id']) && $row['item']['unit_type_id'] != 'ZZ') {
                $has_unit = true;
            }
        }
        if($has_service == false && $has_unit == false){
            $has_unit = true;
        }
        return ['has_service' => $has_service, 'has_unit' => $has_unit];
    }
}
