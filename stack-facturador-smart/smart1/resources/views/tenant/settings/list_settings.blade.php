@extends('tenant.layouts.app')

@section('content')
    <?php
    use App\Models\Tenant\Configuration;
    $configuration = Configuration::first();
    $is_integrate_system = \Modules\BusinessTurn\Models\BusinessTurn::isIntegrateSystem();
    $is_superadmin = auth()->user()->type == 'superadmin';
    ?>
    <div class="page-header pr-0">
        <h2>
            <a href="/dashboard">
                {{-- Ícono de “home” con Bootstrap Icons --}}
                <i class="bi bi-house-fill" style="font-size: 18px;"></i>
            </a>
        </h2>
        <ol class="breadcrumbs">
            <li class="active">
                <span>Dashboard</span>
            </li>
            <li>
                <span class="text-muted">Configuración</span>
            </li>
        </ol>
    </div>

    <div class="row">
        <div class="col-6 col-md-4 mb-4">
            <div class="card card-dashboard card-reports">
                <div class="card-body">
                    <h6 class="card-title">General</h6>
                    <ul class="card-report-links">
                        @if ($user->type != 'integrator')
                            <li>
                                <a href="{{ url('list-banks') }}">
                                    <i class="bi bi-bank" style="font-size: 18px;"></i>
                                    Listado de bancos
                                </a>
                            </li>
                            <li>
                                <a href="{{ url('list-bank-accounts') }}">
                                    <i class="bi bi-credit-card-2-back" style="font-size: 18px;"></i>
                                    Listado de cuentas bancarias
                                </a>
                            </li>
                            <li>
                                <a href="{{ url('list-currencies') }}">
                                    <i class="bi bi-cash-coin" style="font-size: 18px;"></i>
                                    Lista de monedas
                                </a>
                            </li>
                            <li>
                                <a href="{{ url('list-cards') }}">
                                    <i class="bi bi-credit-card" style="font-size: 18px;"></i>
                                    Listado de tarjetas
                                </a>
                            </li>
                            <li>
                                <a href="{{ url('warehouses') }}">
                                    <i class="bi bi-box-seam" style="font-size: 18px;"></i>
                                    Lista de almacenes
                                </a>
                            </li>
                            <li>
                                <a href="{{ url('list-agencies') }}">
                                    <i class="bi bi-building" style="font-size: 18px;"></i>
                                    Lista de agencias
                                </a>
                            </li>
                            <li>
                                <a href="{{ url('list-platforms') }}">
                                    <i class="bi bi-globe" style="font-size: 18px;"></i>
                                    Plataformas
                                </a>
                            </li>
                            <li>
                                <a href="{{ url('list-state-deliveries') }}">
                                    <i class="bi bi-globe" style="font-size: 18px;"></i>
                                    Estados de entrega
                                </a>
                            </li>
                            <li>
                                <a href="{{ url('list-state-technical-services') }}">
                                    <i class="bi bi-globe" style="font-size: 18px;"></i>
                                    Estados de servicio técnico
                                </a>
                            </li>
                            @if ($configuration->show_channels_documents)
                            <li>
                                <a href="{{ route('tenant.channels.index') }}">
                                    <i class="bi bi-broadcast" style="font-size: 18px;"></i>
                                    Canales de documentos
                                </a>
                            </li>
                            @endif
                            <li>
                                <a href="{{ route('tenant.label_colors.index') }}">
                                    <i class="bi bi-palette" style="font-size: 18px;"></i>
                                    Colores de Etiquetas
                                </a>
                            </li>

                            @if ($is_integrate_system)
                                <li>
                                    <a href="{{ url('message-integrate-system') }}">
                                        <i class="bi bi-envelope" style="font-size: 18px;"></i>
                                        Mensajes predeterminados - Sistema integrado
                                    </a>
                                </li>
                            @endif
                        @endif
                    </ul>
                </div>
            </div>
        </div>

        @if (!empty($companyMenu) || $is_superadmin)
            <div class="col-6 col-md-4 mb-4">
                <div class="card card-dashboard card-reports">
                    <div class="card-body">
                        <h6 class="card-title">Empresa</h6>
                        <ul class="card-report-links">
                            <li>
                                <a href="{{ route('tenant.companies.create') }}">
                                    <i class="bi bi-building" style="font-size: 18px;"></i>
                                    Empresa
                                </a>
                            </li>
                            <li>
                                <a href="{{ route('tenant.multi_companies.index') }}">
                                    <i class="bi bi-diagram-3" style="font-size: 18px;"></i>
                                    MultiEmpresa
                                </a>
                            </li>
                            <li>
                                <a href="{{ route('tenant.bussiness_turns.index') }}">
                                    <i class="bi bi-shop" style="font-size: 18px;"></i>
                                    Giro de negocio
                                </a>
                            </li>
                            <li>
                                <a href="{{ route('tenant.advanced.index') }}">
                                    <i class="bi bi-gear-wide-connected" style="font-size: 18px;"></i>
                                    Avanzado
                                </a>
                            </li>
                            <li>
                                <a href="{{ route('tenant.shortcuts.index') }}">
                                    <i class="bi bi-lightning-charge" style="font-size: 18px;"></i>
                                    Accesos directos
                                </a>
                            </li>
                            @if ($configuration->quick_access)
                            <li>
                                <a href="{{ route('tenant.quickaccess.index') }}">
                                    <i class="bi bi-link-45deg" style="font-size: 18px;"></i>
                                    Atajos Rapidos (barra superior)
                                </a>
                            </li>
                            @endif
                            <li>
                                <a href="{{ route('tenant.suscription_names.index') }}">
                                    <i class="bi bi-file-earmark-text" style="font-size: 18px;"></i>
                                    Denominación suscripción
                                </a>
                            </li>
                            <li>
                                <a href="{{ route('tenant.dashboard.sales') }}">
                                    <i class="bi bi-bar-chart-line" style="font-size: 18px;"></i>
                                    Dashboard - Ventas - Compras
                                </a>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>
        @endif

        <div class="col-6 col-md-4 mb-4">
            <div class="card card-dashboard card-reports">
                <div class="card-body">
                    <h6 class="card-title">SUNAT</h6>
                    <ul class="card-report-links">
                        @if ($user->type != 'integrator' || $is_superadmin)
                            <li>
                                <a href="{{ url('list-attributes') }}">
                                    <i class="bi bi-card-checklist" style="font-size: 18px;"></i>
                                    Listado de Atributos
                                </a>
                            </li>
                            <li>
                                <a href="{{ url('list-detractions') }}">
                                    <i class="bi bi-percent" style="font-size: 18px;"></i>
                                    Listado de tipos de detracciones
                                </a>
                            </li>
                            <li>
                                <a href="{{ url('list-units') }}">
                                    {{-- No existe “bi-rulers”, usaremos un ícono genérico de lista o documento --}}
                                    <i class="bi bi-list-check" style="font-size: 18px;"></i>
                                    Listado de unidades
                                </a>
                            </li>
                            <li>
                                <a href="{{ url('list-transfer-reason-types') }}">
                                    <i class="bi bi-arrow-left-right" style="font-size: 18px;"></i>
                                    Tipos de motivos de transferencias
                                </a>
                            </li>
                        @endif
                    </ul>
                </div>
            </div>
        </div>

        <div class="col-6 col-md-4 mb-4">
            <div class="card card-dashboard card-reports">
                <div class="card-body">
                    <h6 class="card-title">Ingresos/Egresos</h6>
                    <ul class="card-report-links">
                        @if ($user->type != 'integrator' || $is_superadmin)
                            <li>
                                <a href="{{ url('list-payment-methods') }}">
                                    <i class="bi bi-cash-stack" style="font-size: 18px;"></i>
                                    Métodos de pago - ingreso / gastos
                                </a>
                            </li>
                            <li>
                                <a href="{{ url('list-incomes') }}">
                                    <i class="bi bi-piggy-bank" style="font-size: 18px;"></i>
                                    Motivos de ingresos / Gastos
                                </a>
                            </li>
                            <li>
                                <a href="{{ url('list-payments') }}">
                                    <i class="bi bi-list-check" style="font-size: 18px;"></i>
                                    Listado de métodos de pago
                                </a>
                            </li>
                        @endif
                        @if ($user->type != 'integrator' || $is_superadmin)
                            <li>
                                <a href="{{ url('list-vouchers-type') }}">
                                    <i class="bi bi-file-earmark-check" style="font-size: 18px;"></i>
                                    Comprobantes Ingreso / Gastos
                                </a>
                            </li>
                        @endif
                    </ul>
                </div>
            </div>
        </div>

        <div class="col-6 col-md-4 mb-4">
            <div class="card card-dashboard card-reports">
                <div class="card-body">
                    <h6 class="card-title">Plantillas PDF</h6>
                    <ul class="card-report-links">
                        <li>
                            <a href="{{ route('tenant.advanced.pdf_templates') }}">
                                <i class="bi bi-file-earmark-pdf" style="font-size: 18px;"></i>
                                PDF
                            </a>
                        </li>
                        <li>
                            <a href="{{ route('tenant.advanced.pdf_ticket_templates') }}">
                                <i class="bi bi-file-earmark-pdf-fill" style="font-size: 18px;"></i>
                                PDF - Ticket
                            </a>
                        </li>
                        <li>
                            <a href="{{ route('tenant.advanced.pdf_preprinted_templates') }}">
                                <i class="bi bi-file-earmark-diff" style="font-size: 18px;"></i>
                                Pre Impresos
                            </a>
                        </li>
                        <li>
                            <a href="{{ url('list-units/pdf') }}">
                                <i class="bi bi-file-earmark-spreadsheet" style="font-size: 18px;"></i>
                                PDF - Unidades de medida
                            </a>
                        </li>
                        <li>
                            <a href="{{ route('tenant.document_names.index') }}">
                                <i class="bi bi-file-earmark-text" style="font-size: 18px;"></i>
                                PDF - Denominación
                            </a>
                        </li>
                        <li>
                            <a href="{{ route('tenant.document_quotations.index') }}">
                                <i class="bi bi-file-earmark-plus" style="font-size: 18px;"></i>
                                PDF - Casilla personalizada
                            </a>
                        </li>
                        <li>
                            <a href="{{ route('tenant.yape_plin_qr.index') }}">
                                <i class="bi bi-qr-code-scan" style="font-size: 18px;"></i>
                                PDF - QR - Yape/Plin
                            </a>
                        </li>
                        <li>
                            <a href="{{ route('tenant.pdf_additional_info.index') }}">
                                <i class="bi bi-info-circle" style="font-size: 18px;"></i>
                                PDF - Información adicional
                            </a>
                        </li>
                        @if ($configuration->document_columns)
                            <li>
                                <a href="{{ route('tenant.document_columns.index') }}">
                                    <i class="bi bi-layout-text-sidebar" style="font-size: 18px;"></i>
                                    PDF - Columnas personalizadas
                                </a>
                            </li>
                        @endif
                    </ul>
                </div>
            </div>
        </div>

        @if (!empty($advanceMenu) || $is_superadmin)
            <div class="col-6 col-md-4 mb-4">
                <div class="card card-dashboard card-reports">
                    <div class="card-body">
                        <h6 class="card-title">Varios</h6>
                        <ul class="card-report-links">
                            @if ($user->type != 'integrator' && $vc_company->soap_type_id != '03')
                                <li>
                                    <a href="{{ route('tenant.tasks.index') }}">
                                        <i class="bi bi-alarm" style="font-size: 18px;"></i>
                                        Tareas programadas
                                    </a>
                                </li>
                            @endif
                            @if ($vc_company->soap_type_id != '03')
                                <li>
                                    <a href="{{ route('tenant.series_configurations.index') }}">
                                        <i class="bi bi-list-ol" style="font-size: 18px;"></i>
                                        Numeración de facturación
                                    </a>
                                </li>
                            @endif
                            <li>
                                <a href="{{ route('tenant.company_accounts.create') }}">
                                    <i class="bi bi-gear-wide-connected" style="font-size: 18px;"></i>
                                    Avanzado - Contable
                                </a>
                            </li>
                            @if ($user->type != 'integrator' && $vc_company->soap_type_id != '03')
                                <li>
                                    <a href="{{ route('tenant.inventories.configuration.index') }}">
                                        <i class="bi bi-box-seam" style="font-size: 18px;"></i>
                                        Inventarios
                                    </a>
                                </li>
                            @endif
                            @if ($user->type === 'admin' || $user->type === 'superadmin')
                                <li>
                                    <a href="{{ route('tenant.sale_notes.configuration') }}">
                                        <i class="bi bi-file-earmark-plus" style="font-size: 18px;"></i>
                                        Nota de ventas
                                    </a>
                                </li>
                            @endif
                            @if ($configuration->isMiTiendaPe() == true)
                                <li>
                                    <a href="{{ route('tenant.mi_tienda_pe.configuration.index') }}">
                                        <i class="bi bi-shop" style="font-size: 18px;"></i>
                                        MiTienda.PE
                                    </a>
                                </li>
                            @endif
                        </ul>
                    </div>
                </div>
            </div>
        @endif

    </div>
@endsection
