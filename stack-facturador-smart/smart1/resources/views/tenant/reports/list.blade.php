@extends('tenant.layouts.app')
{{-- @if (in_array('hotels', $vc_modules)) --}}
@section('content')
    @php
        $is_food_dealer = \Modules\BusinessTurn\Models\BusinessTurn::isFoodDealer();
        $show_extra_info_to_item = (bool) $configuration->show_extra_info_to_item;
    @endphp
    <div class="page-header pr-0">
        <h2>
            <a href="/dashboard">
                {{-- Se deja el ícono original de FontAwesome para Dashboard --}}
                <i class="fas fa-home"></i>
            </a>
        </h2>
        <ol class="breadcrumbs">
            <li class="active">
                <span>Dashboard</span>
            </li>
            <li>
                <span class="text-muted">Reportes</span>
            </li>
        </ol>
    </div>

    <div class="row">
        <!-- General -->
        <div class="col-6 col-md-4 mb-4">
            <div class="card card-dashboard card-reports">
                <div class="card-body">
                    <h6 class="card-title">General</h6>
                    <ul class="card-report-links">
                        @if ($vc_company->soap_type_id != '03')
                            <li>
                                <a href="{{ route('tenant.consistency-documents.index') }}">
                                    <i class="bi bi-card-checklist" style="font-size: 16px;"></i>
                                    Consistencia documentos
                                </a>
                            </li>
                            <li>
                                <a href="{{ route('tenant.validate_documents.index') }}">
                                    <i class="bi bi-clipboard-check" style="font-size: 16px;"></i>
                                    Validador de documentos
                                </a>
                            </li>
                        @endif
                        @if (in_array('hotels', $vc_modules))
                            <li>
                                <a href="{{ route('tenant.reports.hotel.mincetur') }}">
                                    <i class="bi bi-building" style="font-size: 16px;"></i>
                                    Reporte Mincetur - Hotel
                                </a>
                            </li>
                        @endif
                        @if (in_array('hotel', $vc_business_turns))
                            <li>
                                <a href="{{ route('tenant.reports.document_hotels.index') }}">
                                    <i class="bi bi-buildings" style="font-size: 16px;"></i>
                                    Giro negocio hoteles
                                </a>
                            </li>
                            <li>
                                <a href="{{ route('tenant.reports.report_hotel.index') }}">
                                    <i class="bi bi-file-earmark-spreadsheet" style="font-size: 16px;"></i>
                                    Reporte de Habitaciones
                                </a>
                            </li>
                        @endif
                        <li>
                            <a href="{{ route('tenant.reports.commercial_analysis.index') }}">
                                <i class="bi bi-bar-chart" style="font-size: 16px;"></i>
                                Análisis comercial
                            </a>
                        </li>
                        <li>
                            <a href="{{ route('tenant.reports.massive-downloads.index') }}">
                                <i class="bi bi-cloud-arrow-down-fill" style="font-size: 16px;"></i>
                                Descarga masiva - documentos
                            </a>
                        </li>
                        <li>
                            <a href="{{ route('tenant.reports.download-tray.index') }}">
                                <i class="bi bi-inbox-fill" style="font-size: 16px;"></i>
                                Bandeja descarga de reportes
                            </a>
                        </li>

                        {{-- Actividades del sistema --}}
                        <li>
                            <a href="#" data-toggle="collapse" data-target="#system_activity_logs_id">
                                <i class="bi bi-activity" style="font-size: 16px;"></i>
                                Actividades del sistema
                            </a>
                        </li>
                        <ul id="system_activity_logs_id" class="collapse">
                            <li>
                                <a href="{{ route('tenant.system_activity_logs.generals.index') }}">
                                    <i class="bi bi-server" style="font-size: 16px;"></i>
                                    Generales
                                </a>
                            </li>
                            <li>
                                <a href="{{ route('tenant.system_activity_logs.transactions.index') }}">
                                    <i class="bi bi-file-earmark-medical" style="font-size: 16px;"></i>
                                    Documentos electrónicos
                                </a>
                            </li>
                        </ul>
                        {{-- Fin Actividades del sistema --}}
                    </ul>
                </div>
            </div>
        </div>

        <!-- Compras -->
        <div class="col-6 col-md-4 mb-4">
            <div class="card card-dashboard card-reports">
                <div class="card-body">
                    <h6 class="card-title">Compras</h6>
                    <ul class="card-report-links">
                        <li>
                            <a href="{{ route('tenant.reports.purchases.index') }}">
                                <i class="bi bi-basket2-fill" style="font-size: 16px;"></i>
                                Compras totales
                            </a>
                        </li>
                        <li>
                            <a href="{{ route('tenant.reports.fixed-asset-purchases.index') }}">
                                <i class="bi bi-building" style="font-size: 16px;"></i>
                                Activos fijos
                            </a>
                        </li>
                        <li>
                            <a href="{{ route('tenant.reports.purchases.items.index') }}">
                                <i class="bi bi-search" style="font-size: 16px;"></i>
                                Producto - busqueda individual
                            </a>
                        </li>
                        <li>
                            <a href="{{ route('tenant.reports.purchases.general_items.index') }}">
                                <i class="bi bi-box-seam" style="font-size: 16px;"></i>
                                Productos
                            </a>
                        </li>
                        <li>
                            <a href="{{ route('report.purchase-orders.index') }}">
                                <i class="bi bi-cart-check" style="font-size: 16px;"></i>
                                Ordenes de compra
                            </a>
                        </li>
                    </ul>
                </div>
            </div>
        </div>

        <!-- Ventas -->
        <div class="col-6 col-md-4 mb-4">
            <div class="card card-dashboard card-reports">
                <div class="card-body">
                    <h6 class="card-title">Ventas</h6>
                    <ul class="card-report-links">
                        <li>
                            <a href="{{ route('tenant.reports.summary_sales.index') }}">
                                <i class="bi bi-card-list" style="font-size: 16px;"></i>
                                Ventas resumidas
                            </a>
                        </li>
                        @if ($vc_company->soap_type_id != '03')
                            <li>
                                <a href="{{ route('tenant.reports.sales.index') }}">
                                    <i class="bi bi-file-earmark-text" style="font-size: 16px;"></i>
                                    Documentos
                                </a>
                            </li>
                        @endif
                        <li>
                            <a href="{{ route('tenant.reports.customers.index') }}">
                                <i class="bi bi-person" style="font-size: 16px;"></i>
                                Clientes
                            </a>
                        </li>
                        <li>
                            <a href="{{ route('tenant.reports.seller_sales.index') }}">
                                <i class="bi bi-person-workspace" style="font-size: 16px;"></i>
                                Ventas por Vendedor - Detallado - Consolidado
                            </a>
                        </li>
                        @if ($configuration->dispatchers_packers_document)
                            <li>
                                <a href="{{ route('tenant.reports.packer_dispatcher_sales.index') }}">
                                    <i class="bi bi-people-fill" style="font-size: 16px;"></i>
                                    Ventas por Empaquetador - Repartidor
                                </a>
                            </li>
                        @endif
                        <li>
                            <a href="{{ route('tenant.reports.carrier_document_settlement.index') }}">
                                <i class="bi bi-truck" style="font-size: 16px;"></i>
                                Liquidación de documentos por transportista
                            </a>
                        </li>
                        <li>
                            <a href="{{ route('tenant.reports.carrier_document.index') }}">
                                <i class="bi bi-truck" style="font-size: 16px;"></i>
                                Transporte acumulado por transportista
                            </a>
                        </li>
                        <li>
                            <a href="{{ route('tenant.reports.sales_sabana.index') }}">
                                <i class="bi bi-file-earmark-ruled" style="font-size: 16px;"></i>
                                Reporte de venta (sabana)
                            </a>
                        </li>
                        <li>
                            <a href="{{ route('tenant.reports.items.index') }}">
                                <i class="bi bi-search" style="font-size: 16px;"></i>
                                Producto - busqueda individual
                            </a>
                        </li>
                        @if ($show_extra_info_to_item == true)
                            <li>
                                <a href="{{ route('tenant.reports.extra.items.index') }}">
                                    <i class="bi bi-search" style="font-size: 16px;"></i>
                                    Producto - busqueda individual - Por atributos
                                    <el-tooltip
                                        class="item"
                                        content="Reporte con los campos opcionales del item"
                                        effect="dark"
                                        placement="top-start"
                                    >
                                        <i class="fa fa-info-circle" style="font-size: 14px;"></i>
                                    </el-tooltip>
                                </a>
                            </li>
                        @endif
                        <li>
                            <a href="{{ route('tenant.reports.general_items.index') }}">
                                <i class="bi bi-box-seam" style="font-size: 16px;"></i>
                                Productos y servicios
                            </a>
                        </li>
                        <li>
                            <a href="{{ route('tenant.reports.quotations.index') }}">
                                <i class="bi bi-card-text" style="font-size: 16px;"></i>
                                Cotizaciones
                            </a>
                        </li>
                        <li>
                            <a href="{{ route('tenant.reports.sale_notes.index') }}">
                                <i class="bi bi-card-heading" style="font-size: 16px;"></i>
                                Notas de Venta
                            </a>
                        </li>
                        @if ($configuration->nc_payment_nv)
                            <li>
                                <a href="{{ route('tenant.reports.credit_notes.index') }}">
                                    <i class="bi bi-credit-card" style="font-size: 16px;"></i>
                                    Notas de crédito
                                </a>
                            </li>
                        @endif
                        @if ($vc_company->soap_type_id != '03')
                            <li>
                                <a href="{{ route('tenant.reports.document_detractions.index') }}">
                                    <i class="bi bi-cash-stack" style="font-size: 16px;"></i>
                                    Detracciones
                                </a>
                            </li>
                        @endif
                        <li>
                            <a href="{{ route('tenant.reports.sales_consolidated.index') }}">
                                <i class="bi bi-collection" style="font-size: 16px;"></i>
                                Consolidado de items
                            </a>
                        </li>
                        <li>
                            <a href="{{ route('tenant.reports.tips.index') }}">
                                <i class="bi bi-currency-dollar" style="font-size: 16px;"></i>
                                Propinas
                            </a>
                        </li>
                        <li>
                            <a href="{{ route('tenant.reports.state_account.index') }}">
                                <i class="bi bi-clipboard-data" style="font-size: 16px;"></i>
                                Estado de cuenta
                            </a>
                        </li>
                        <li>
                            <a href="{{ route('tenant.reports.documents_paid.index') }}">
                                <i class="bi bi-check-circle" style="font-size: 16px;"></i>
                                Documentos cancelados
                            </a>
                        </li>
                        <li>
                            <a href="{{ route('tenant.reports.all_sales_consolidated.index') }}">
                                <i class="bi bi-collection" style="font-size: 16px;"></i>
                                Ventas consolidado
                            </a>
                        </li>
                    </ul>
                </div>
            </div>
        </div>

        <!-- Ventas/Comisiones -->
        <div class="col-6 col-md-4 mb-4">
            <div class="card card-dashboard card-reports">
                <div class="card-body">
                    <h6 class="card-title">Ventas/Comisiones</h6>
                    <ul class="card-report-links">
                        <li>
                            <a href="{{ route('tenant.reports.user_commissions.index') }}">
                                <i class="bi bi-cash-coin" style="font-size: 16px;"></i>
                                Utilidad ventas
                            </a>
                        </li>
                        <li>
                            <a href="{{ route('tenant.reports.commissions.index') }}">
                                <i class="bi bi-receipt" style="font-size: 16px;"></i>
                                Ventas
                            </a>
                        </li>
                        <li>
                            <a href="{{ route('tenant.reports.commissions_detail.index') }}">
                                <i class="bi bi-file-spreadsheet" style="font-size: 16px;"></i>
                                Utilidad detallado
                            </a>
                        </li>
                        <li>
                            <a href="{{ route('tenant.reports.cash_closures.index') }}">
                                <i class="bi bi-file-spreadsheet" style="font-size: 16px;"></i>
                                Cierres de caja
                            </a>
                        </li>
                    </ul>
                </div>
            </div>
        </div>

        <!-- Pedidos -->
        <div class="col-6 col-md-4 mb-4">
            <div class="card card-dashboard card-reports">
                <div class="card-body">
                    <h6 class="card-title">Pedidos</h6>
                    <ul class="card-report-links">
                        <li>
                            <a href="{{ route('tenant.reports.order_notes_general.index') }}">
                                <i class="bi bi-card-list" style="font-size: 16px;"></i>
                                General
                            </a>
                        </li>
                        <li>
                            <a href="{{ route('tenant.reports.order_notes_consolidated.index') }}">
                                <i class="bi bi-collection" style="font-size: 16px;"></i>
                                Consolidado de items
                            </a>
                        </li>
                        <li>
                            <a href="{{ route('tenant.reports.order_notes_variable.index') }}">
                                <i class="bi bi-menu-button-wide" style="font-size: 16px;"></i>
                                Reporte variable
                            </a>
                        </li>
                    </ul>
                </div>
            </div>
        </div>

        <!-- Guias -->
        <div class="col-6 col-md-4 mb-4">
            <div class="card card-dashboard card-reports">
                <div class="card-body">
                    <h6 class="card-title">Guias</h6>
                    <ul class="card-report-links">
                        <li>
                            <a href="{{ route('tenant.reports.guides.index') }}">
                                <i class="bi bi-collection" style="font-size: 16px;"></i>
                                Consolidado de items
                            </a>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
@endsection
