<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

//2025_07_16_153342_add_reports_to_configurations
class AddReportsToConfigurations extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        DB::table('report_configurations')->insert([
            [
                'route_name' => 'tenant.reports.summary_sales.index',
                'name' => 'Consulta de ventas resumidas',
                'convert_pen' => false,
                'route_path' => 'reports/summary-sales',
            ],
            //reports/sales //tenant.reports.sales.index
            [
                'route_name' => 'tenant.reports.sales.index',
                'name' => 'Documentos',
                'convert_pen' => false,
                'route_path' => 'reports/sales',
            ],

            //reports/seller-sales tenant.reports.seller_sales.index
            [
                'route_name' => 'tenant.reports.seller_sales.index',
                'name' => 'Ventas por Vendedor - Detallado - Consolidado',
                'convert_pen' => false,
                'route_path' => 'reports/seller-sales',
            ],
            //reports/sale-notes tenant.reports.sale_notes.index
            [
                'route_name' => 'tenant.reports.sale_notes.index',
                'name' => 'Notas de venta',
                'convert_pen' => false,
                'route_path' => 'reports/sale-notes',
            ],

            //reports/all-sales-consolidated 
            [
                'route_name' => 'tenant.reports.all_sales_consolidated.index',
                'name' => 'Ventas Consolidadas',
                'convert_pen' => false,
                'route_path' => 'reports/all-sales-consolidated',
            ],
            
        ]); 
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
    
    }
}
