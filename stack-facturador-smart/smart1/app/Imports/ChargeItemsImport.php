<?php

namespace App\Imports;

use App\Models\Tenant\Item;
use App\Models\Tenant\ChargeType;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Illuminate\Support\Collection;

class ChargeItemsImport implements ToCollection, WithHeadingRow
{
    private $charge_type_id;
    private $results;

    public function __construct($charge_type_id)
    {
        $this->charge_type_id = $charge_type_id;
        $this->results = [
            'success' => [],
            'errors' => [],
            'duplicates' => []
        ];
    }

    public function collection(Collection $rows)
    {
        $charge_type = ChargeType::findOrFail($this->charge_type_id);

        foreach ($rows as $row) {
            $internal_id = $row['codigo_interno'];

            // Buscar el item por código interno
            $item = Item::where('internal_id', $internal_id)->first();

            if (!$item) {
                $this->results['errors'][] = "El producto con código {$internal_id} no existe";
                continue;
            }

            // Verificar si el item ya está registrado directamente
            $exists_as_item = $charge_type->charge_type_items()
                ->where('item_id', $item->id)
                ->exists();

            if ($exists_as_item) {
                $this->results['duplicates'][] = "El producto {$item->description} ya está registrado en el cargo";
                continue;
            }

            // Verificar si el item pertenece a una marca que ya está en cargo
            if (!is_null($item->brand_id)) {
                $exists_in_brand = $charge_type->charge_type_items()
                    ->where('brand_id', $item->brand_id)
                    ->exists();

                if ($exists_in_brand) {
                    $this->results['duplicates'][] = "El producto {$item->description} pertenece a una marca que ya está en cargo";
                    continue;
                }
            }

            // Verificar si el item pertenece a una categoría que ya está en cargo
            if (!is_null($item->category_id)) {
                $exists_in_category = $charge_type->charge_type_items()
                    ->where('category_id', $item->category_id)
                    ->exists();

                if ($exists_in_category) {
                    $this->results['duplicates'][] = "El producto {$item->description} pertenece a una categoría que ya está en cargo";
                    continue;
                }
            }

            // Si pasa todas las validaciones, crear el registro
            $charge_type->charge_type_items()->create([
                'item_id' => $item->id
            ]);

            $this->results['success'][] = "Producto {$item->description} agregado correctamente";
        }
    }

    public function getResults()
    {
        return $this->results;
    }
}