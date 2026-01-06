<?php

namespace App\Imports;

use App\Models\Tenant\Document;
use App\Models\Tenant\Item;
use App\Models\Tenant\Person;
use App\Models\Tenant\Warehouse;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\Importable;
use Maatwebsite\Excel\Concerns\ToCollection;
use Carbon\Carbon;
use PhpOffice\PhpSpreadsheet\Shared\Date;
use Exception;
use App\Http\Controllers\SearchItemController;


class DocumentsSaleImport implements ToCollection
{
    use Importable;

    protected $data;
    protected $documents = [];
    protected $current_document_key = null;
    protected $errors = [];
    protected $document_types = [
        "AFT" => "01",
        "BBT" => "03",
        "NCTF" => "07",
    ];

    protected function formatDate($date)
    {
        if (!$date) return null;

        try {
            if (is_numeric($date)) {
                return Date::excelToDateTimeObject($date)->format('Y-m-d');
            }

            $formats = ['d/m/Y', 'd-m-Y', 'Y-m-d', 'd/m/y', 'd-m-y'];

            foreach ($formats as $format) {
                $dateObj = Carbon::createFromFormat($format, $date);
                if ($dateObj !== false) {
                    return $dateObj->format('Y-m-d');
                }
            }

            return Carbon::parse($date)->format('Y-m-d');
        } catch (\Exception $e) {
            return null;
        }
    }

    protected function getDocumentType($document_type)
    {   
        $document_type = strtoupper($document_type);
        foreach ($this->document_types as $key => $value) {
            if (strpos($document_type, $key) !== false) {
                return $value;
            }
        }
        return "01";
    }

    public function collection(Collection $rows)
    {
        $total = count($rows);
        $registered = 0;
        unset($rows[0]);

        foreach ($rows as $row_index => $row) {
            $document_destiny = $row[0]; // A
            $is_valid = $row[1]; // B
            $affected_stock = $row[2]; // C
            $document_type = $this->getDocumentType($row[3]);
            $document_number = $row[4]; // E
            $bv_number = $row[5]; // F
            $document_date = $row[6]; // G
            $establishment_name = $row[7]; // H
            $seller_name = $row[8]; // I
            $currency = $row[9]; // J
            $exchange_rate = $row[10]; // K
            $payment_condition = $row[11]; // L
            $document_date_due = $row[12]; // M
            $customer_number = $row[13]; // N
            $customer_type = $row[14]; // O
            $column_p = $row[15]; // P
            $column_q = $row[16]; // Q
            $column_r = $row[17]; // R
            $column_s = $row[18]; // S
            $prices_list = $row[19]; // T
            $column_u = $row[20]; // U
            $total_value = $row[21]; // V
            $has_igv = $row[22]; // W
            $total = $row[23]; // X
            $column_y = $row[24]; // Y
            $column_z = $row[25]; // Z
            $column_aa = $row[26]; // AA
            $column_ab = $row[27]; // AB
            $column_ac = $row[28]; // AC
            $column_ad = $row[29]; // AD
            $column_ae = $row[30]; // AE
            $column_af = $row[31]; // AF
            $customer_number_2 = $row[32]; // AG
            $column_ah = $row[33]; // AH
            $column_ai = $row[34]; // AI
            $column_aj = $row[35]; // AJ
            $column_ak = $row[36]; // AK
            $column_al = $row[37]; // AL
            $column_am = $row[38]; // AM
            $number_item = $row[39]; // AN
            $column_ao = $row[40]; // AO
            $internal_id = $row[41]; // AP
            $quantity = $row[42]; // AQ
            $column_ar = $row[43]; // AR
            $unit_value = $row[44]; // AS
            $column_at = $row[45]; // AT
            $discount_type = $row[46]; // AU
            $column_av = $row[47]; // AV
            $total_item = $row[48]; // AW
            $column_ax = $row[49]; // AX
            $sub_total_item = $row[50]; // AY
            $column_az = $row[51]; // AZ
            $column_ba = $row[52]; // BA
            $column_bb = $row[53]; // BB
            $column_bc = $row[54]; // BC
            $column_bd = $row[55]; // BD
            $column_be = $row[56]; // BE
            $column_bf = $row[57]; // BF
            $column_bg = $row[58]; // BG
            $column_bh = $row[59]; // BH
            $column_bi = $row[60]; // BI
            $column_bj = $row[61]; // BJ
            $column_bk = $row[62]; // BK
            $column_bl = $row[63]; // BL
            $column_bm = $row[64]; // BM
            $column_bn = $row[65]; // BN
            $column_bo = $row[66]; // BO
            $column_bp = $row[67]; // BP
            $column_bq = $row[68]; // BQ
            $column_br = $row[69]; // BR
            $column_bs = $row[70]; // BS
            $column_bt = $row[71]; // BT
            $column_bu = $row[72]; // BU
            $column_bv = $row[73]; // BV
            $percentage_igv = $row[74]; // BW
            $total_igv = $row[75]; // BX
            $column_by = $row[76]; // BY
            $column_bz = $row[77]; // BZ
            $column_ca = $row[78]; // CA
            $fee_number = $row[79]; // CB
            $fee_date = $row[80]; // CC
            $fee_amount = $row[81]; // CD
            $column_ce = $row[82]; // CE
            $column_cf = $row[83]; // CF
            $column_cg = $row[84]; // CG
            $column_ch = $row[85]; // CH
            $column_ci = $row[86]; // CI
            $column_cj = $row[87]; // CJ
            $column_ck = $row[88]; // CK
            $column_cl = $row[89]; // CL
            $column_cm = $row[90]; // CM
            $column_cn = $row[91]; // CN
            $column_co = $row[92]; // CO
            $column_cp = $row[93]; // CP
            $column_cq = $row[94]; // CQ
            $column_cr = $row[95]; // CR
            $column_cs = $row[96]; // CS
            $column_ct = $row[97]; // CT    
            $column_cu = $row[98]; // CU
            $column_cv = $row[99]; // CV
            $column_cw = $row[100]; // CW
            $column_cx = $row[101]; // CX
            $column_cy = $row[102]; // CY
            $column_cz = $row[103]; // CZ
            $column_da = $row[104]; // DA
            $column_db = $row[105]; // DB
            $column_dc = $row[106]; // DC
            $column_dd = $row[107]; // DD
            $column_de = $row[108]; // DE
            $column_df = $row[109]; // DF
            $column_dg = $row[110]; // DG
            $column_dh = $row[111]; // DH
            $column_di = $row[112]; // DI
            $column_dj = $row[113]; // DJ
            $column_dk = $row[114]; // DK
            $column_dl = $row[115]; // DL
            $series = $row[116]; // DM
            $column_dn = $row[117]; // DN

            if (!empty($document_type) && !empty($document_number)) {
                $this->current_document_key = $document_type . '-' . $document_number;
            }

            if (empty($this->current_document_key)) {
                continue;
            }

            // Validar cliente
            $customer = null;
            if (!empty($customer_number)) {
                $customer = Person::where('number', $customer_number)
                    ->where('type', 'customers')
                    ->first();

                if (!$customer) {
                    $this->errors[$this->current_document_key][] = "Cliente no encontrado: {$customer_number}";
                    continue;
                }
            }

            // Validar item
            $item = null;
            if (!empty($internal_id)) {
                $item = Item::where('internal_id', $internal_id)->first();
                if (!$item) {
                    $this->errors[$this->current_document_key][] = "Producto no encontrado: {$internal_id}";
                    continue;
                }
            }

            if (isset($this->documents[$this->current_document_key])) {
                if (!empty($number_item) && $item) {
                    $item_data = (new SearchItemController)->getItemsToDocuments(null, $item->id);
                    if ($item_data && isset($item_data[0])) {
                        $item_found = $item_data[0];
                        $this->documents[$this->current_document_key]['items'][] = [
                            'number_item' => $number_item,
                            'internal_id' => $internal_id,
                            'item_id' => $item->id,
                            'unit_type_id' => $item_found['unit_type_id'],
                            'description' => $item_found['description'],
                            'quantity' => $quantity,
                            'unit_value' => $unit_value,
                            'total_item' => $total_item,
                            'sub_total_item' => $sub_total_item,
                            'affectation_igv_type_id' => $item_found['sale_affectation_igv_type_id']
                        ];
                    }
                }

                if (!empty($fee_number)) {
                    $this->documents[$this->current_document_key]['fee'][] = [
                        'fee_number' => $fee_number,
                        'fee_date' => $this->formatDate($fee_date),
                        'fee_amount' => $fee_amount
                    ];
                }
            } else {
                if ($customer) {
                    $this->documents[$this->current_document_key] = [
                        'is_valid' => $is_valid,
                        'affected_stock' => $affected_stock,
                        'document_type_id' => $document_type,
                        'document_number' => $document_number,
                        'customer_id' => $customer->id,
                        'customer' => $customer,
                        'bv_number' => $bv_number,
                        'document_date' => $this->formatDate($document_date),
                        'establishment_name' => $establishment_name,
                        'seller_name' => $seller_name,
                        'currency' => $currency,
                        'exchange_rate' => $exchange_rate,
                        'payment_condition' => $payment_condition,
                        'document_date_due' => $this->formatDate($document_date_due),
                        'customer_number' => $customer_number,
                        'customer_type' => $customer_type,
                        'prices_list' => $prices_list,
                        'total_value' => $total_value,
                        'has_igv' => $has_igv,
                        'total' => $total,
                        'percentage_igv' => $percentage_igv,
                        'series' => $series,
                        'items' => [],
                        'fee' => []
                    ];

                    // Agregar primer item si existe
                    if (!empty($number_item) && $item) {
                        $item_data = (new SearchItemController)->getItemsToDocuments(null, $item->id);
                        if ($item_data && isset($item_data[0])) {
                            $item_found = $item_data[0];
                            $this->documents[$this->current_document_key]['items'][] = [
                                'number_item' => $number_item,
                                'internal_id' => $internal_id,
                                'item_id' => $item->id,
                                'unit_type_id' => $item_found['unit_type_id'],
                                'description' => $item_found['description'],
                                'quantity' => $quantity,
                                'unit_value' => $unit_value,
                                'total_item' => $total_item,
                                'sub_total_item' => $sub_total_item,
                                'affectation_igv_type_id' => $item_found['sale_affectation_igv_type_id']
                            ];
                        }
                    }

                    if (!empty($fee_number)) {
                        $this->documents[$this->current_document_key]['fee'][] = [
                            'fee_number' => $fee_number,
                            'fee_date' => $this->formatDate($fee_date),
                            'fee_amount' => $fee_amount
                        ];
                    }
                }
            }

            $registered += 1;
        }

        $this->data = compact('total', 'registered');
    }

    public function getData()
    {
        return [
            'documents' => $this->documents,
            'errors' => $this->errors,
            'data' => $this->data
        ];
    }
}
