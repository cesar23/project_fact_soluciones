<?php

namespace Modules\Finance\Traits;

use App\Models\Tenant\Person;
use App\Models\Tenant\Purchase;
use App\Models\Tenant\PurchasePayment;
use App\Models\Tenant\PurchaseFee;
use App\Models\Tenant\BillOfExchangePay;
use Modules\Expense\Models\Expense;
use Modules\Expense\Models\ExpensePayment;
use Modules\Expense\Models\BillOfExchangePaymentPay;
use Illuminate\Support\Collection;
use Carbon\Carbon;

trait ToPayTrait
{
    public function transformRecords($records, $detail = false)
    {
        return $records->map(function($row) use ($detail) {
            $total_to_pay = $this->getTotalToPayToPay($row);
            $delay_payment = $this->getDelayPayment($row);
            $payments = $detail ? $this->getPaymentsToPay($row) : [];
            $date_payment_last = $this->getLastPaymentToPay($row);

            return [
                'id' => $row->id,
                'type' => $row->type,
                'supplier_name' => $row->supplier_name,
                'supplier_id' => $row->supplier_id,
                'supplier_number' => $this->getSupplierNumber($row),
                'number_full' => $row->number_full,
                'date_of_issue' => $row->date_of_issue,
                'date_of_due' => $row->date_of_due ?? '',
                'currency_type_id' => $row->currency_type_id,
                'total' => number_format((float)$row->total, 2, '.', ''),
                'total_to_pay' => number_format($total_to_pay, 2, '.', ''),
                'delay_payment' => $delay_payment,
                'payments' => $payments,
                'date_payment_last' => $date_payment_last,
                'exchange_rate_sale' => (float)$row->exchange_rate_sale,
                'user_id' => $row->user_id,
            ];
        });
    }

    public function transformRecordsForPdf($records)
    {
        return $records->map(function($row) {
            $total_to_pay = $this->getTotalToPayToPay($row);
            $delay_payment = $this->getDelayPayment($row);

            return [
                'id' => $row->id,
                'type' => $row->type,
                'supplier_name' => $row->supplier_name,
                'supplier_id' => $row->supplier_id,
                'supplier_number' => $this->getSupplierNumber($row),
                'number_full' => $row->number_full,
                'date_of_issue' => $row->date_of_issue,
                'date_of_due' => $row->date_of_due ?? '',
                'currency_type_id' => $row->currency_type_id,
                'total' => $row->total,
                'total_to_pay' => $total_to_pay,
                'delay_payment' => $delay_payment,
                'exchange_rate_sale' => (float)$row->exchange_rate_sale,
                'user_id' => $row->user_id,
            ];
        });
    }

    public function transformRecordsOptimized($records)
    {
        $now = Carbon::now();

        return $records->map(function($row) use ($now) {
            $total_to_pay = $this->getTotalToPayToPay($row);

            // Filtrar solo registros con saldo > 0
            if ($total_to_pay <= 0) {
                return null;
            }

            $supplier_info = $this->getSupplierInfo($row);
            $document_info = $this->getDocumentInfo($row);
            $delay_payment = $this->getDelayPayment($row);

            return [
                'id' => $row->id,
                'type' => $row->type,
                'supplier_name' => $row->supplier_name,
                'supplier_id' => $row->supplier_id,
                'supplier_number' => $supplier_info['number'] ?? '-',
                'supplier_telephone' => $supplier_info['telephone'] ?? '-',
                'supplier_address' => $supplier_info['address'] ?? '-',
                'supplier_zone' => $supplier_info['zone'] ?? '-',
                'number_full' => $row->number_full,
                'document_related' => $document_info['related'] ?? '-',
                'date_of_issue' => $row->date_of_issue,
                'date_of_due' => $row->date_of_due ?? '',
                'currency_type_id' => $row->currency_type_id,
                'total' => number_format((float)$row->total, 2, '.', ''),
                'total_payment' => number_format((float)$row->total_payment, 2, '.', ''),
                'total_to_pay' => number_format($total_to_pay, 2, '.', ''),
                'delay_payment' => $delay_payment,
                'exchange_rate_sale' => (float)$row->exchange_rate_sale,
                'user_id' => $row->user_id,
                'user_name' => $this->getUserName($row),
            ];
        })->filter(); // Remover nulos
    }

    private function getTotalToPayToPay($row)
    {
        // Manejar tanto arrays como objetos
        if (is_array($row)) {
            return (float)$row['total'] - (float)$row['total_payment'];
        }
        return (float)$row->total - (float)$row->total_payment;
    }

    private function getDelayPayment($row)
    {
        // Manejar tanto arrays como objetos
        try{
            $date_of_due = is_array($row) ? ($row['date_of_due'] ?? null) : $row->date_of_due;
        }catch(\Exception $e){
        }

        if (!$date_of_due) {
            return null;
        }

        $due = Carbon::parse($date_of_due);
        $now = Carbon::now();

        if ($now > $due) {
            return $now->diffInDays($due);
        }

        return null;
    }

    private function getSupplierNumber($row)
    {
        // Manejar tanto arrays como objetos
        $supplier_id = is_array($row) ? ($row['supplier_id'] ?? null) : $row->supplier_id;

        if ($supplier_id) {
            $supplier = Person::find($supplier_id);
            return $supplier ? $supplier->number : '';
        }
        return '';
    }

    private function getSupplierInfo($row)
    {
        // Manejar tanto arrays como objetos
        $supplier_id = is_array($row) ? ($row['supplier_id'] ?? null) : $row->supplier_id;

        if ($supplier_id) {
            $supplier = Person::select('number', 'telephone', 'address', 'zone_id')
                ->with('zoneRelation:id,name')
                ->find($supplier_id);

            if ($supplier) {
                $zone = $supplier->getZone(); // Usar el mismo método que UnpaidTrait
                return [
                    'number' => $supplier->number,
                    'telephone' => $supplier->telephone,
                    'address' => $supplier->address,
                    'zone' => $zone ? $zone->name : '-'
                ];
            }
        }
        return [];
    }

    private function getDocumentInfo($row)
    {
        $related = null;

        switch ($row->type) {
            case 'purchase':
                $related = $row->number_full;
                break;
            case 'purchase_fee':
                // Para cuotas, obtener el documento padre
                $fee = PurchaseFee::select('purchase_id')->find($row->id);
                if ($fee) {
                    $purchase = Purchase::select('series', 'number')->find($fee->purchase_id);
                    $related = $purchase ? $purchase->series . '-' . $purchase->number : '-';
                }
                break;
            case 'bill_of_exchange':
                // Para letras de cambio, obtener documentos relacionados
                $bill = BillOfExchangePay::with('items.purchase:id,series,number')->find($row->id);
                if ($bill && $bill->items) {
                    $related = $bill->items->map(function($item) {
                        return $item->purchase->series . '-' . $item->purchase->number;
                    })->implode(', ');
                }
                break;
            case 'expense':
                $related = $row->number_full;
                break;
            default:
                $related = $row->number_full;
        }

        return ['related' => $related];
    }

    private function getUserName($row)
    {
        if ($row->user_id) {
            $user = \App\Models\Tenant\User::select('name')->find($row->user_id);
            return $user ? $user->name : '-';
        }
        return '-';
    }

    private function getPaymentsToPay($row)
    {
        $payments = [];

        switch ($row->type) {
            case 'purchase':
                $payments = PurchasePayment::where('purchase_id', $row->id)
                    ->with('payment_method_type:id,description')
                    ->get()
                    ->map(function($payment) {
                        return [
                            'id' => $payment->id,
                            'date_of_payment' => $payment->date_of_payment->format('Y-m-d'),
                            'payment' => $payment->payment,
                            'payment_method_type_description' => $payment->payment_method_type->description ?? '',
                        ];
                    });
                break;
            case 'bill_of_exchange':
                $payments = BillOfExchangePaymentPay::where('bill_of_exchange_id', $row->id)
                    ->with('payment_method_type:id,description')
                    ->get()
                    ->map(function($payment) {
                        return [
                            'id' => $payment->id,
                            'date_of_payment' => $payment->date_of_payment->format('Y-m-d'),
                            'payment' => $payment->payment,
                            'payment_method_type_description' => $payment->payment_method_type->description ?? '',
                        ];
                    });
                break;
            case 'expense':
                $payments = ExpensePayment::where('expense_id', $row->id)
                    ->with('payment_method_type:id,description')
                    ->get()
                    ->map(function($payment) {
                        return [
                            'id' => $payment->id,
                            'date_of_payment' => $payment->date_of_payment->format('Y-m-d'),
                            'payment' => $payment->payment,
                            'payment_method_type_description' => $payment->payment_method_type->description ?? '',
                        ];
                    });
                break;
        }

        return $payments;
    }

    private function getLastPaymentToPay($row)
    {
        $lastPayment = null;

        switch ($row->type) {
            case 'purchase':
                $lastPayment = PurchasePayment::where('purchase_id', $row->id)
                    ->orderBy('date_of_payment', 'desc')
                    ->first();
                break;
            case 'bill_of_exchange':
                $lastPayment = BillOfExchangePaymentPay::where('bill_of_exchange_id', $row->id)
                    ->orderBy('date_of_payment', 'desc')
                    ->first();
                break;
            case 'expense':
                $lastPayment = ExpensePayment::where('expense_id', $row->id)
                    ->orderBy('date_of_payment', 'desc')
                    ->first();
                break;
        }

        return $lastPayment ? $lastPayment->date_of_payment->format('Y-m-d') : null;
    }

    /**
     * Método especial para excel_s que incluye zona y vendedor como en UnpaidTrait
     */
    public function transformRecordsToPayEspecial($records)
    {
        return collect($records)->map(function ($row, $key) {
            // Los datos vienen como array, no como objeto
            $total_to_pay = (float)$row['total'] - (float)$row['total_payment'];
            $date_of_issue = $row['date_of_issue'];
            $delay_payment = $row['delay_payment'] ?? 0;

            // Información del proveedor y zona (similar a customer en UnpaidTrait)
            $supplier_number = '';
            $supplier_zone_name = null;
            $supplier_telephone = null;
            $supplier_address = null;
            $seller_name = null; // En to_pay será el usuario/comprador
            $currency_symbol = '';

            // Obtener información del proveedor usando el mismo método que UnpaidTrait
            if (isset($row['supplier_id']) && $row['supplier_id']) {
                $supplier = Person::find($row['supplier_id']);
                if ($supplier) {
                    $supplier_number = $supplier->number ?? '';
                    $supplier_telephone = $supplier->telephone ?? '';
                    $supplier_address = $supplier->address ?? '';

                    // Usar el mismo método getZone() que en UnpaidTrait
                    $zone = $supplier->getZone();
                    if ($zone) {
                        $supplier_zone_name = $zone->name;
                    }
                }
            }

            // Obtener información del vendedor/usuario (mismo concepto que seller en UnpaidTrait)
            if (isset($row['user_id']) && $row['user_id']) {
                $user = \App\Models\Tenant\User::find($row['user_id']);
                $seller_name = $user ? $user->name : null;
            }

            // Obtener símbolo de moneda
            if (isset($row['currency_type_id'])) {
                $currency = \App\Models\Tenant\Catalogs\CurrencyType::find($row['currency_type_id']);
                $currency_symbol = $currency ? $currency->symbol : '';
            }

            return [
                'id' => $row['id'],
                'date_of_issue' => $date_of_issue,
                'supplier_name' => $row['supplier_name'],
                'supplier_id' => $row['supplier_id'] ?? null,
                'supplier_number' => $supplier_number,
                'supplier_zone_name' => $supplier_zone_name, // Equivalente a customer_zone_name
                'supplier_telephone' => $supplier_telephone,
                'supplier_address' => $supplier_address,
                'seller_name' => $seller_name, // En to_pay es el comprador/usuario
                'number_full' => $row['number_full'],
                'total' => number_format((float)$row['total'], 2, '.', ''),
                'total_payment' => number_format((float)$row['total_payment'], 2, '.', ''),
                'total_to_pay' => number_format($total_to_pay, 2, '.', ''),
                'type' => $row['type'],
                'delay_payment' => $delay_payment,
                'date_of_due' => $row['date_of_due'] ?? '',
                'currency_type_id' => $row['currency_type_id'] ?? 'PEN',
                'currency_symbol' => $currency_symbol,
                'exchange_rate_sale' => (float)($row['exchange_rate_sale'] ?? 1),
                'user_id' => $row['user_id'] ?? null,
            ];
        });
    }

    public function transformRecords3OptimizedForPdf($records)
    {
        $now = Carbon::now();
        $results = collect();

        // Procesar cada registro individualmente para usar menos memoria
        foreach ($records as $row) {
            $total_to_pay = $this->getTotalToPayToPay($row);

            // Filtrar temprano - si no hay saldo, continuar
            if ($total_to_pay <= 0) {
                continue;
            }

            // Usar los datos que ya vienen de DashboardView
            $description = $row->number_full ?? '-';
            $code = $row->id ?? '-';
            $date_of_issue = $row->date_of_issue ?? '-';
            $date_of_due = $row->date_of_due ?? '-';
            $document_related = $description;

            // Datos del proveedor que ya vienen de DashboardView
            $supplier_ruc = $row->supplier_ruc ?? '-';
            $supplier_name = $row->supplier_name ?? '-';
            $supplier_telephone = $row->supplier_telephone ?? '-';
            $user_name = $row->username ?? '-';

            // Calcular días de atraso
            $delay_payment = 0;
            if ($date_of_due && $date_of_due !== '-') {
                try {
                    $due = Carbon::parse($date_of_due);
                    if ($now > $due) {
                        $delay_payment = $now->diffInDays($due);
                    }
                } catch (\Exception $e) {
                    $delay_payment = 0;
                }
            }

            // Obtener zona del proveedor si existe
            $supplier_zone = '-';
            if ($row->supplier_id) {
                $supplier = Person::select('zone_id')->with('zoneRelation:id,name')->find($row->supplier_id);
                if ($supplier && $supplier->zoneRelation) {
                    $supplier_zone = $supplier->zoneRelation->name;
                }
            }

            // Construir el resultado final con la misma estructura que UnpaidTrait
            $results->push([
                'document_related' => $document_related,
                'description' => $description,
                'number_full' => $description, // Para Excel que busca number_full
                'code' => $code,
                'date_of_issue' => $date_of_issue,
                'date_of_due' => $date_of_due,
                'currency_type_id' => $row->currency_type_id ?? 'PEN',
                'total' => number_format((float) $row->total, 2, ".", ""),
                'total_payment' => number_format((float) $row->total_payment, 2, ".", ""),
                'total_subtraction' => number_format((float) $row->total_subtraction, 2, ".", ""),
                'total_to_pay' => number_format($total_to_pay, 2, ".", ""),
                'delay_payment' => $delay_payment,
                'supplier_ruc' => $supplier_ruc,
                'supplier_name' => $supplier_name,
                'supplier_number' => $supplier_ruc, // Para Excel que busca supplier_number
                'supplier_telephone' => $supplier_telephone,
                'supplier_zone' => $supplier_zone,
                'seller_name' => $user_name,
                'type' => $row->type ?? '-', // Para Excel que busca type
                'line_credit' => '-', // No aplica para proveedores
            ]);
        }

        return $results;
    }
}