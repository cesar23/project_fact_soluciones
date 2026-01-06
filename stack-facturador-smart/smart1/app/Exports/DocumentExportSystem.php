<?php

namespace App\Exports;

use App\Models\Tenant\Item;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Illuminate\Support\Collection;

class DocumentExportSystem implements FromCollection, WithHeadings, ShouldAutoSize
{
    use Exportable;
    protected $filters;
    protected $records;
    protected $company;
    protected $establishment;
    protected $document_types_transform =[
        "01" => "AFT",
        "03" => "BBT",
        "07" => "NCTF",
    ];
    protected $state_type_accepted = [
        '01',
        '03',
        '05',
    ];

    public function records($records) {
        $this->records = $records;
        return $this;
    }
    
    public function company($company) {
        $this->company = $company;
        return $this;
    }

    public function filters($filters) {
        $this->filters = $filters;
        return $this;
    }

    public function establishment($establishment) {
        $this->establishment = $establishment;
        return $this;
    }
    
    public function headings(): array
    {
        return[];
        // return [
        //     'Destino del Documento',
        //     'Documento impreso se genera nulo',
        //     'Documento impreso Genera Rebaja Stock', 
        //     'Tipo de Documento',
        //     'Número',
        //     'Número final, sólo boletas',
        //     'Fecha',
        //     'Local',
        //     'Vendedor',
        //     'Moneda Referencia',
        //     'Tasa Referencia',
        //     'Condición de Pago',
        //     'Fecha de Vencimiento',
        //     'Código del Cliente',
        //     'Tipo de Cliente',
        //     'Centro de Negocios',
        //     'Clasificador 1',
        //     'Clasificador 2', 
        //     'Origen del Documento u origen ft',
        //     'Lista de Precio',
        //     'Código del Proyecto',
        //     'Afecto',
        //     'Exento',
        //     'Total',
        //     'Bodega Inventario',
        //     'Motivo de movimiento Inventario',
        //     'Centro de Negocios Inventario',
        //     'Tipo de Cuenta Inventario',
        //     'Proveedor Inventario',
        //     'Dirección de Despacho',
        //     'Clasificador 1 Inventario',
        //     'Clasificador 2 Inventario',
        //     'Código Legal (RUC)',
        //     'Nombre',
        //     'Giro',
        //     'Dirección',
        //     'Ciudad',
        //     'Rubro',
        //     'Glosa',
        //     'Línea de Detalle',
        //     'Articulo / Servicio',
        //     'Código del Producto',
        //     'Cantidad',
        //     'Tipo de Recargo y Descuento',
        //     'Precio Unitario',
        //     'Descuento',
        //     'Tipo de Descuento',
        //     'Tipo de Venta',
        //     'Total del Producto',
        //     'Precio Lista',
        //     'Total Neto',
        //     'Ficha Producto',
        //     'Centro de Negocios Producto',
        //     'Clasificador 1 Producto',
        //     'Clasificador 2 Producto',
        //     'Cantidad de Unidad Equivalente',
        //     'Cantidad de Periodos',
        //     'Comentario Producto',
        //     'Análisis Atributo1 Producto',
        //     'Análisis Atributo2 Producto',
        //     'Análisis Atributo3 Producto',
        //     'Análisis Atributo4 Producto',
        //     'Análisis Atributo5 Producto',
        //     'Análisis Lote Producto',
        //     'Fecha de Vencimiento Lote',
        //     'Ingreso Manual',
        //     'Tipo de Inventario',
        //     'Clasificador 1 Inventario Línea',
        //     'Clasificador 2 Inventario Línea',
        //     'Número de Descuento',
        //     'Descuento',
        //     'Tipo de Descuento',
        //     'Numero de Impuesto',
        //     'Código de Impuesto',
        //     'Valor de Impuesto',
        //     'Monto de Impuesto',
        //     'Centro de Negocios Producto',
        //     'Clasificador 1 Impuesto',
        //     'Clasificador 2 Impuesto',
        //     'Número de Cuota',
        //     'Fecha de Cuota',
        //     'Monto de Cuota',
        //     'Relación linea Series',
        //     'Sufijo Artículo Inventario',
        //     'Prefijo Artículo Inventario',
        //     'Serie Artículo Inventario',
        //     'Distrito',
        //     'Transacción',
        //     'Fecha Facturación Desde',
        //     'Fecha Facturación Hasta',
        //     'Vía de Transporte',
        //     'País Destino Receptor',
        //     'País Destino Embarque',
        //     'Modalidad Venta',
        //     'Tipo Despacho',
        //     'Indicador de Servicio',
        //     'Claúsula de Venta',
        //     'Total Claúsula de Venta',
        //     'Puerto Embarque',
        //     'Puerto Desembarque',
        //     'Unidad de Medida Tara',
        //     'Total Medida Tara',
        //     'Unidad Peso Bruto',
        //     'Total Peso Bruto',
        //     'Unidad Peso Neto',
        //     'Total Peso Neto',
        //     'Tipo de Bulto',
        //     'Total de Bultos',
        //     'Forma de Pago',
        //     'Tipo Documento Asociado',
        //     'Folio Documento Asociado',
        //     'Fecha Documento Asociado',
        //     'Comentario Documento Asociado',
        //     'Email',
        //     'Es documento de traspaso',
        //     'Contacto',
        //     'Serie alfanumérica',
        //     'Es linea Exenta'
        // ];
    }

    public function collection(): Collection
    {
        $rows = collect();
        
        foreach($this->records as $record) {
            // Determinamos cuál es más largo entre items y fees
            $items_count = $record->items ? count($record->items) : 0;
            $fees_count = $record->fee ? count($record->fee) : 0;
            $max_rows = max($items_count, $fees_count);
            $isCreditNote = $record->document_type_id == "07";
            if($max_rows > 0) {
                for($index = 0; $index < $max_rows; $index++) {
                    $row = array_fill(0, 118, '');
                    
                    // Solo en la primera fila incluimos la información general del documento
                    if($index === 0) {
                        $row[0] = "A";
                        $row[1] = in_array($record->state_type_id, $this->state_type_accepted) ? "N" : "S";
                        $row[2] = $record->sale_note_id == null ? "S" : "N";
                        $row[3] = $this->document_types_transform[$record->document_type_id];
                        $row[4] = $this->format_number($record);
                        $row[5] = $record->document_type_id == "03" ? $record->number : "";
                        $row[6] = $record->date_of_issue->format('d/m/Y');
                        $row[7] = "PRINCIPAL";
                        $row[8] = "RC";
                        $row[9] = $record->currency_type_id == "PEN" ? "SOL" : "DOLAR";
                        $row[10] = $record->currency_type_id == "PEN" ? 1 : $record->exchange_rate;
                        $row[11] = $record->payment_condition_id == "01" ? "CONTADO" : "CREDITO";
                        $row[12] = $this->getPaymentConditionDate($record);
                        $row[13] = $record->customer->number;
                        $row[14] = $record->currency_type_id == "PEN" ? "CXC_SOL" : "CXC_USD";
                        $row[15] = "EMP004000000000";
                        $row[16] = "";
                        $row[17] = "";
                        $row[18] = "";
                        $row[19] = "1";
                        $row[20] = "";
                        $row[21] = $isCreditNote ? $record->total_value * -1 : $record->total_value;
                        $row[22] = $record->total_igv > 0 ? "0" : "";
                        $row[23] = $isCreditNote ? $record->total * -1 : $record->total;
                        $row[24] = "A_CENTRAL";
                        $row[25] = "01";
                        $row[26] = "";
                        $row[27] = "";
                        $row[28] = "";
                        $row[29] = "";
                        $row[30] = "";
                        $row[31] = "";
                        $row[32] = $record->customer->number;
                        $row[33] = "";
                        $row[34] = "";
                        $row[35] = "";
                        $row[36] = "";
                        $row[37] = "";
                        $row[38] = "VENTA DE MERCADERIA";
                        $row[72] = "1";
                        $row[73] = "IGV";
                        $row[74] = "18";
                        $row[75] = $isCreditNote ? $record->total_igv * -1 : $record->total_igv;
                        $row[77] = "0";
                        $row[82] = "";
                        $row[83] = "";
                        $row[84] = "";
                        $row[85] = "";
                        $row[86] = "";
                        $row[87] = "";
                        $row[88] = "";
                        $row[89] = "";
                        $row[90] = "";
                        $row[91] = "";
                        $row[92] = "";
                        $row[93] = "";
                        $row[94] = "";
                        $row[95] = "";
                        $row[96] = "";
                        $row[97] = "";
                        $row[98] = "";
                        $row[99] = "";
                        $row[100] = "";
                        $row[101] = "";
                        $row[102] = "";
                        $row[103] = "";
                        $row[104] = "";
                        $row[105] = "";
                        $row[106] = "";
                        $row[107] = "";
                        $row[108] = "";
                        $row[109] = "";
                        $row[110] = "";
                        $row[111] = "";
                        $row[112] = "";
                        $row[113] = "";
                        $row[114] = "";
                        $row[115] = "";                    
                        $row[114] = "S";
                        $row[116] = $this->getSerie($record);
                        $row[117] = "";

                        $row[79] = "1";
                        $row[80] = $record->date_of_issue->format('d/m/Y');
                        $row[81] = $isCreditNote ? $record->total * -1 : $record->total;
                        

                    }

                    // Si hay un item para este índice, incluimos su información
                    if($index < $items_count && isset($record->items[$index])) {
                        $item = $record->items[$index];
                        // $item_db = Item::select('internal_id')->where('id', $item->item_id)->first();
                        // if($item_db){
                        //     $row[41] = $item_db->internal_id;
                        // }else{
                        //     $row[41] = "";
                        // }
                        $row[39] = $index + 1;
                        $row[40] = $item->item->unit_type_id == "ZZ" ? "S" : "A";
                        $row[41] = "DB5S5001";
                        $row[42] = $item->quantity;
                        $row[43] = "";
                        $row[44] = $item->unit_value;
                        $row[45] = "";
                        $row[46] = "P";
                        $row[47] = "";
                        $row[48] = $isCreditNote ? $item->total * -1 : $item->total;
                        $row[49] = "";
                        $row[50] = $isCreditNote ? $item->total_value * -1 : $item->total_value;
                        $row[51] = "";
                        $row[52] = "";
                        $row[53] = "";
                        $row[54] = "";
                        $row[55] = "";
                        $row[56] = "";
                        $row[57] = "";
                        $row[58] = "";
                        $row[59] = "";
                        $row[60] = "";
                        $row[61] = "";
                        $row[62] = "";
                        $row[63] = "";
                        $row[64] = "";
                        $row[65] = "";
                        $row[66] = "";
                        $row[67] = "";
                        $row[68] = "";
                        $row[69] = "";
                        $row[70] = "";
                        $row[71] = "";
                
                        $row[76] = "";
                        $row[77] = "";
                        $row[78] = "";
                    
                        

                    }

                    // Si hay una cuota para este índice, incluimos su información
                    if($index < $fees_count && isset($record->fee[$index])) {
                        $fee = $record->fee[$index];
                        $row[79] = $index + 1; // Total de cuotas
                        $row[80] = $fee->date->format('d/m/Y');         // Fecha de cuota
                        $row[81] = $fee->amount;                        // Monto de cuota
                    }

                    $rows->push($row);
                }
            } else {
                // Si no hay items ni cuotas, al menos agregamos una fila con los datos del documento
                $row = array_fill(0, 118, '');
                $row[3] = $record->document_type->description;
                $row[4] = $this->format_number($record);
                $row[6] = $record->date_of_issue->format('d/m/Y');
                $row[13] = $record->customer->number;
                $row[33] = $record->customer->number;
                $row[34] = $record->customer->name;
                $row[35] = $record->customer->trade_name;
                $rows->push($row);
            }
        }
    
        return $rows;
    }

    function getSerie($record){
        $series = $record->series;
        $serie_without_last = substr($series, 0, -1);
        return $serie_without_last;
    }
    function format_number($record){
        $series = $record->series;
        $number = $record->number;
        
        $last_char_series = substr($series, -1);
        $format_number = $last_char_series . str_pad($number, 8, '0', STR_PAD_LEFT);
        return $format_number;
        
    }

    function getPaymentConditionDate($record){

        $payment_condition = $record->payment_condition_id;
        if($payment_condition == "01" || !isset($record->invoice)){
            $date = $record->date_of_issue;
        }else{
            $date = $record->invoice->date_of_due;
        }
        return $date->format('d/m/Y');
    
    }
}
