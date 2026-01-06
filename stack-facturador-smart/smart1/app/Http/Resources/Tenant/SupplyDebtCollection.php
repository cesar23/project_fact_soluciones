<?php

namespace App\Http\Resources\Tenant;

use App\Models\Tenant\SupplyDebt;
use Illuminate\Http\Resources\Json\ResourceCollection;

class SupplyDebtCollection extends ResourceCollection
{
    public function toArray($request)
    {
        // Cache de meses para evitar recrear el array en cada iteración
        static $meses = [
            "Enero", "Febrero", "Marzo", "Abril", "Mayo", "Junio",
            "Julio", "Agosto", "Septiembre", "Octubre", "Noviembre", "Diciembre"
        ];

        // Pre-cargar datos para optimización
        $this->preloadData();

        return $this->collection->transform(function($row, $key) use ($meses) {
            // Si es un Resource, obtener el modelo subyacente
            $model = $row instanceof \App\Http\Resources\Tenant\SupplyDebtResource ? $row->resource : $row;
            return $this->transformRow($model, $meses);
        });
    }

    /**
     * Pre-carga datos para optimización
     */
    private function preloadData(): void
    {
        // Si necesitas hacer consultas adicionales por lotes, aquí es el lugar
        // Por ejemplo, cargar todos los conceptos de una vez si no están eager loaded
        
        // Ejemplo: Si necesitas calcular pagos por lotes
        // $this->preloadPayments();
    }

    /**
     * Pre-carga información de pagos por lotes (ejemplo)
     */
    private function preloadPayments(): void
    {
        // Obtener todos los IDs de deudas activas
        $debtIds = $this->collection->where('active', true)->pluck('id');
        
        if ($debtIds->isNotEmpty()) {
            // Hacer una sola consulta para obtener todos los pagos
            // $payments = DB::table('supply_debt_payments')
            //     ->whereIn('supply_debt_id', $debtIds)
            //     ->where('active', true)
            //     ->select('supply_debt_id', DB::raw('SUM(amount) as total_paid'))
            //     ->groupBy('supply_debt_id')
            //     ->get()
            //     ->keyBy('supply_debt_id');
            
            // Almacenar en una propiedad de la clase para uso posterior
            // $this->paymentsCache = $payments;
        }
    }

    /**
     * Transforma una fila individual
     */
    private function transformRow(SupplyDebt $row, array $meses): array
    {
        if (empty($row->serie_receipt) && empty($row->correlative_receipt)) {
            $receipt = '-';
        }else{
            $receipt = $row->serie_receipt . ' - ' . $row->correlative_receipt;
        }
        $supply = $row->supply;
        $address = '-';
        $supply_name = null;
        $via = null;
        if($supply){
            $supply_name =  $supply->old_code;
            $via = $supply->supplyVia;
            $sector = $supply->sector;
            $supply_type_via = $via->supplyTypeVia;
            $address = $sector->name . ' | ' . $supply_type_via->short .' '.$via->name;
        }
        
        return [
            'id' => $row->id,
            'address' => $address,
            'receipt' => $receipt,
            'supply_name' => $supply_name,
            'generation_date' => $row->generation_date->format('Y-m-d'),
            'person_name' => $row->person ? $row->person->name : '-',
            'supply_id' => $row->supply_id,
            'amount' => $this->calculateAmount($row),
            'year' => $row->year,
            'month' => $row->month,
            'due_date' => $row->due_date,
            'active' => $row->active,
            'type' => $row->type,
            'description' => $this->generateDescription($row, $meses),
            
            'created_at' => $row->created_at,
            'updated_at' => $row->updated_at
        ];
    }

    /**
     * Genera la descripción según el tipo de deuda y mes
     */
    private function generateDescription(SupplyDebt $row, array $meses): string
    {
        // Si no hay mes y es tipo de deuda 1 y tipo 'r'
        if (empty($row->month) && $row->supply_type_debt_id == 1 && $row->type == 'r') {
            return "Deuda generada manualmente";
        }
        
        // Si es tipo 'c' (concepto)
        if ($row->type == 'c' && $row->supplyConcept) {
            return $row->supplyConcept->name;
        }
        
        // Para otros casos, generar descripción basada en mes y año
        if (!empty($row->month)) {
            $mesNumero = (int)$row->month;
            if ($mesNumero > 0 && $mesNumero <= 12) {
                $nombreMes = $meses[$mesNumero - 1];
                $anio = $row->year ? ' - ' . $row->year : '';
                return "Deuda del mes " . $nombreMes . $anio;
            }
        }
        
        return "Mes no válido";
    }

    /**
     * Calcula el monto total de pagos si la deuda está activa
     */
    private function calculateAmount(SupplyDebt $row): float
    {
        
        return  $row->amount;
    }

    /**
     * Formatea la información del tipo de deuda
     */
    private function formatSupplyTypeDebt($supplyTypeDebt): ?array
    {
        return $supplyTypeDebt ? [
            'id' => $supplyTypeDebt->id,
            'description' => $supplyTypeDebt->description,
            'code' => $supplyTypeDebt->code
        ] : null;
    }

    /**
     * Formatea la información del concepto
     */
    private function formatSupplyConcept($supplyConcept): ?array
    {
        return $supplyConcept ? [
            'id' => $supplyConcept->id,
            'name' => $supplyConcept->name,
            'code' => $supplyConcept->code,
            'cost' => $supplyConcept->cost,
            'type' => $supplyConcept->type
        ] : null;
    }

    /**
     * Formatea la información de la persona
     */
    private function formatPerson($person): ?array
    {
        return $person ? [
            'id' => $person->id,
            'name' => $person->name,
            'number' => $person->number
        ] : null;
    }

    /**
     * Formatea la información del suministro
     */
    private function formatSupply($supply): ?array
    {
        return $supply ? [
            'id' => $supply->id,
            'number' => $supply->number,
            'address' => $supply->address
        ] : null;
    }
}