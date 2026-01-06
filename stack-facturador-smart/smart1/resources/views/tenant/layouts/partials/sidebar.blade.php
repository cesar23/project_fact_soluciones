<?php

use App\Models\Tenant\Configuration;

$path = explode('/', request()->path());
$configuration = Configuration::getConfig();
$inventory_configuration = \Modules\Inventory\Models\InventoryConfiguration::first();
$firstLevel = $path[0] ?? null;
$secondLevel = $path[1] ?? null;
$thridLevel = $path[2] ?? null;
$is_food_dealer = \Modules\BusinessTurn\Models\BusinessTurn::isFoodDealer();
$is_integrate_system = \Modules\BusinessTurn\Models\BusinessTurn::isIntegrateSystem();
$is_weapon_tracking = \Modules\BusinessTurn\Models\BusinessTurn::isWeaponTracking();
$is_superadmin = auth()->user()->type == 'superadmin';
$is_admin = auth()->user()->type == 'admin';
$is_pos_lite = auth()->user()->pos_lite && !$is_admin;
?>
<div class="menu-container flex-grow-1">
    @if (!$is_pos_lite)
        <ul id="menu" class="menu">
            {{-- DASHBOARD --}}
            @if (in_array('dashboard', $vc_modules) || $is_superadmin)
                <li>
                    <a href="/dashboard" class="{{ $firstLevel === 'dashboard' ? 'active' : '' }}">
                        <i class="bi bi-speedometer2"></i>
                        <span class="label">Dashboard</span>
                    </a>
                </li>
            @endif

            {{-- TUTORIALES --}}
            @if ($configuration->view_tutorials)
                <li>
                    <a href="{{ route('shortcuts.index') }}" class="{{ $firstLevel === 'shortcuts' ? 'active' : '' }}">
                        <i class="bi bi-lightning-charge"></i>
                        <span class="label">Acceso Rapido Login</span>
                    </a>
                </li>
            @endif

            {{-- WHATSAPP / CHATBOT --}}
            @if (in_array('whatsapp', $vc_modules) || $is_superadmin)
                @if ($configuration->chatboot)
                    <li class="mega">
                        <a href="#whatsapp" data-bs-toggle="collapse" data-role="button"
                            aria-expanded="{{ in_array($firstLevel, ['whatsapp', 'questions', 'answers']) ? 'true' : 'false' }}"
                            class="{{ in_array($firstLevel, ['whatsapp', 'questions', 'answers']) ? 'active' : '' }}"
                            data-clicked="{{ in_array($firstLevel, ['whatsapp', 'questions', 'answers']) ? 'true' : 'false' }}">
                            <i class="bi bi-whatsapp"></i>
                            <span class="label">Whatsapp</span>
                        </a>
                        {{-- Submenú Whatsapp --}}
                        <ul id="whatsapp"
                            class="collapse {{ in_array($firstLevel, ['whatsapp', 'questions', 'answers']) ? 'show' : '' }}">
                            <li>
                                <a href="{{ route('tenant.account.whatsapp') }}"
                                    class="{{ $firstLevel === 'whatsapp' ? 'active' : '' }} nav-link">
                                    <span class="label">Cuenta de Whatsapp</span>
                                </a>
                            </li>
                            <li class="mega">
                                <a href="#chatboot" data-bs-toggle="collapse" data-role="button"
                                    aria-expanded="{{ in_array($firstLevel, ['questions', 'answers']) ? 'true' : 'false' }}"
                                    class="{{ in_array($firstLevel, ['questions', 'answers']) ? 'active' : '' }} nav-link"
                                    data-clicked="{{ in_array($firstLevel, ['questions', 'answers']) ? 'true' : 'false' }}">
                                    <span class="label"> ChatBoot Whatsapp</span>
                                </a>
                                <ul id="chatboot"
                                    class="collapse {{ in_array($firstLevel, ['questions', 'answers']) ? 'show' : '' }}">
                                    <li>
                                        <a href="{{ route('tenant.questions') }}"
                                            class="{{ $firstLevel === 'questions' ? 'active' : '' }} nav-link">
                                            <span class="label">Preguntas</span>
                                        </a>
                                    </li>
                                    <li>
                                        <a href="{{ route('tenant.answers') }}"
                                            class="{{ $firstLevel === 'answers' ? 'active' : '' }} nav-link">
                                            <span class="label">Respuestas</span>
                                        </a>
                                    </li>
                                </ul>
                            </li>
                        </ul>
                    </li>
                @endif
            @endif

            {{-- ENCUESTAS --}}
            @if (in_array('surveys', $vc_modules) || $is_superadmin)
                <li class="mega">
                    <a href="#survey" data-bs-toggle="collapse" data-role="button"
                        aria-expanded="{{ in_array($firstLevel, ['survey', 'questions', 'answers']) ? 'true' : 'false' }}"
                        class="{{ in_array($firstLevel, ['survey', 'questions', 'answers']) ? 'active' : '' }}"
                        data-clicked="{{ in_array($firstLevel, ['survey', 'questions', 'answers']) ? 'true' : 'false' }}">
                        <i class="bi bi-card-checklist"></i>
                        <span class="label">Encuestas</span>
                    </a>
                    <ul id="survey"
                        class="collapse {{ in_array($firstLevel, ['survey', 'questions', 'answers']) ? 'show' : '' }}">
                        <li>
                            <a href="{{ route('survey.index') }}"
                                class="{{ $firstLevel === 'survey' && $secondLevel == null ? 'active' : '' }} nav-link">
                                <span class="label">Lista</span>
                            </a>
                        </li>
                        <li>
                            <a href="{{ route('survey.respondet.index') }}"
                                class="{{ $firstLevel === 'survey' && $secondLevel == 'respondet' ? 'active' : '' }} nav-link">
                                <span class="label">Participantes</span>
                            </a>
                        </li>
                    </ul>
                </li>
            @endif

            {{-- VENTAS --}}
            @if (in_array('documents', $vc_modules) || $is_superadmin)
                <li class="mega">
                    <a href="#ventas" data-bs-toggle="collapse" data-role="button"
                        aria-expanded="{{ in_array($firstLevel, [
                            'documents',
                            'summaries',
                            'voided',
                            'quotations',
                            'sale-notes',
                            'contingencies',
                            'incentives',
                            'order-notes',
                            'sale-opportunities',
                            'contracts',
                            'production-orders',
                            'technical-services',
                            'user-commissions',
                            'regularize-shipping',
                            'documents-recurrence',
                            'seller',
                        ])
                            ? 'true'
                            : 'false' }}"
                        class="{{ in_array($firstLevel, [
                            'documents',
                            'summaries',
                            'voided',
                            'quotations',
                            'sale-notes',
                            'contingencies',
                            'incentives',
                            'order-notes',
                            'sale-opportunities',
                            'contracts',
                            'production-orders',
                            'technical-services',
                            'user-commissions',
                            'regularize-shipping',
                            'documents-recurrence',
                            'seller',
                        ])
                            ? 'active'
                            : '' }}"
                        data-clicked="{{ in_array($firstLevel, [
                            'documents',
                            'summaries',
                            'voided',
                            'quotations',
                            'sale-notes',
                            'contingencies',
                            'incentives',
                            'order-notes',
                            'sale-opportunities',
                            'contracts',
                            'production-orders',
                            'technical-services',
                            'user-commissions',
                            'regularize-shipping',
                            'documents-recurrence',
                            'seller',
                        ])
                            ? 'true'
                            : 'false' }}">
                        <i class="bi bi-receipt-cutoff"></i>
                        <span class="label">Ventas</span>
                    </a>
                    <ul id="ventas"
                        class="collapse {{ in_array($firstLevel, [
                            'documents',
                            'summaries',
                            'voided',
                            'quotations',
                            'sale-notes',
                            'contingencies',
                            'incentives',
                            'order-notes',
                            'sale-opportunities',
                            'contracts',
                            'production-orders',
                            'technical-services',
                            'user-commissions',
                            'regularize-shipping',
                            'documents-recurrence',
                            'seller',
                        ])
                            ? 'show'
                            : '' }}">
                        {{-- NUEVO COMPROBANTE --}}
                        @if (auth()->user()->type != 'integrator' && $vc_company->soap_type_id != '03')
                            @if (in_array('documents', $vc_modules) || $is_superadmin)
                                @if (in_array('new_document', $vc_module_levels) || $is_superadmin)
                                    <li>
                                        <a class="{{ $firstLevel === 'documents' && $secondLevel === 'create' ? 'active' : '' }} nav-link"
                                            href="{{ route('tenant.documents.create') }}">Nuevo comprobante</a>
                                    </li>
                                @endif
                            @endif
                        @endif
                        {{-- LISTADO DE COMPROBANTES --}}
                        @if (in_array('documents', $vc_modules) || ($is_superadmin && $vc_company->soap_type_id != '03'))
                            @if (in_array('list_document', $vc_module_levels) || $is_superadmin)
                                <li>
                                    <a class="{{ $firstLevel === 'documents' && !in_array($secondLevel, ['create', 'not-sent', 'regularize-shipping']) ? 'active' : '' }} nav-link"
                                        href="{{ route('tenant.documents.index') }}">
                                        Listado de comprobantes
                                    </a>
                                </li>
                            @endif
                        @endif
                        {{-- NOTAS DE VENTA --}}
                        @if (in_array('sale_notes', $vc_module_levels) || $is_superadmin)
                            <li>
                                <a class="{{ $firstLevel === 'sale-notes' ? 'active' : '' }} nav-link"
                                    href="{{ route('tenant.sale_notes.index') }}">Notas de venta</a>
                            </li>
                        @endif
                        {{-- ORDENES DE VACIADO DE CONCRETO --}}
                        @if ($configuration->order_concrete)
                            <li>
                                <a class="{{ $firstLevel === 'order-concrete' ? 'active' : '' }} nav-link"
                                    href="{{ route('tenant.order_concrete.index') }}">Ordenes de vaciado de
                                    concreto</a>
                            </li>
                        @endif
                        {{-- ORDENES PRODUCCION o DESPACHO (solo si is_integrate_system) --}}
                        @if ($is_integrate_system)
                            @if (in_array('production_orders', $vc_module_levels) || $is_superadmin)
                                <li class="mega">
                                    <a href="#production_orders" data-bs-toggle="collapse" data-role="button"
                                        aria-expanded="{{ in_array($firstLevel, ['production-order', 'inventory-review']) ? 'true' : 'false' }}"
                                        class="{{ in_array($firstLevel, ['production-order', 'inventory-review']) ? 'active' : '' }}"
                                        data-clicked="{{ in_array($firstLevel, ['production-order', 'inventory-review']) ? 'true' : 'false' }}">
                                        <span class="label">Ordenes de producción</span>
                                    </a>
                                    <ul id="production_orders"
                                        class="collapse {{ in_array($firstLevel, ['production-order', 'inventory-review']) ? 'show' : '' }}">
                                        <li>
                                            <a class="{{ $firstLevel === 'production-order' && !$secondLevel ? 'active' : '' }} nav-link"
                                                href="{{ route('tenant.production_order.index') }}">Lista</a>
                                        </li>
                                        <li class="{{ $firstLevel === 'inventory-review' ? 'active' : '' }}">
                                            <a class="nav-link"
                                                href="{{ route('tenant.inventory-review-production-orden.index') }}">
                                                <span class="label">Revisión de inventariox</span>
                                            </a>
                                        </li>
                                        <li>
                                            <a class="{{ $firstLevel === 'purchase-orders' ? 'active' : '' }} nav-link"
                                                href="{{ route('tenant.purchase-orders.index') }}">
                                                <span class="label">Ordenes de compra</span>
                                            </a>
                                        </li>
                                    </ul>
                                </li>
                            @endif
                            @if (in_array('dispatch_orders', $vc_module_levels) || $is_superadmin)
                                <li class="mega">
                                    <a href="#dispatch_orders" data-bs-toggle="collapse" data-role="button"
                                        aria-expanded="{{ $firstLevel === 'dispatch-order' ? 'true' : 'false' }}"
                                        class="{{ $firstLevel === 'dispatch-order' ? 'active' : '' }}"
                                        data-clicked="{{ $firstLevel === 'dispatch-order' ? 'true' : 'false' }}">
                                        <span class="label">Ordenes de despacho</span>
                                    </a>
                                    <ul id="dispatch_orders"
                                        class="collapse {{ $firstLevel === 'dispatch-order' ? 'show' : '' }}">
                                        <li>
                                            <a class="{{ $firstLevel === 'dispatch-order' && !$secondLevel ? 'active' : '' }} nav-link"
                                                href="{{ route('tenant.dispatch_order.index') }}">Lista</a>
                                        </li>
                                        <li
                                            class="{{ $firstLevel === 'dispatch-order' && $secondLevel == 'list' ? 'active' : '' }}">
                                            <a class="nav-link"
                                                href="{{ route('tenant.dispatch_order.index_list') }}">
                                                <span class="label">Ruta lima</span>
                                            </a>
                                        </li>
                                    </ul>
                                </li>
                            @endif
                        @endif
                        {{-- TICKETS DE ENCOMIENDA --}}
                        @if ($configuration->package_handlers)
                            <li>
                                <a class="{{ $firstLevel === 'package-handler' ? 'active' : '' }} nav-link"
                                    href="{{ route('tenant.package_handler.index') }}">Tickets de encomienda</a>
                            </li>
                        @endif
                        {{-- COMPROBANTES NO ENVIADOS / POR REGULARIZAR --}}
                        @if (in_array('documents', $vc_modules) || ($is_superadmin && $vc_company->soap_type_id != '03'))
                            @if (in_array('document_not_sent', $vc_module_levels) || $is_superadmin)
                                <li>
                                    <a class="{{ $firstLevel === 'documents' && $secondLevel === 'not-sent' ? 'active' : '' }} nav-link"
                                        href="{{ route('tenant.documents.not_sent') }}">
                                        Comprobantes no enviados
                                    </a>
                                </li>
                            @endif
                            @if (in_array('regularize_shipping', $vc_module_levels) || $is_superadmin)
                                <li>
                                    <a class="{{ $firstLevel === 'documents' && $secondLevel === 'regularize-shipping' ? 'active' : '' }} nav-link"
                                        href="{{ route('tenant.documents.regularize_shipping') }}">
                                        CPE por rectificar
                                    </a>
                                </li>
                            @endif
                        @endif
                        {{-- COMPROBANTES RECURRENTES --}}
                        @if (in_array('documents_recurrence', $vc_module_levels) || $is_superadmin)
                            <li>
                                <a class="{{ $firstLevel === 'documents-recurrence' ? 'active' : '' }} nav-link"
                                    href="{{ route('tenant.documents_recurrence.index') }}">
                                    Listado de recurrente
                                </a>
                            </li>
                        @endif
                        {{-- COMPROBANTE DE CONTINGENCIA --}}
                        @if ((auth()->user()->type != 'integrator' && in_array('documents', $vc_modules)) || $is_superadmin)
                            @if (
                                (auth()->user()->type != 'integrator' && in_array('document_contingengy', $vc_module_levels)) ||
                                    ($is_superadmin && $vc_company->soap_type_id != '03'))
                                <li>
                                    <a class="{{ $firstLevel === 'contingencies' ? 'active' : '' }} nav-link"
                                        href="{{ route('tenant.contingencies.index') }}">
                                        Comprobante contingencia
                                    </a>
                                </li>
                            @endif
                        @endif
                        {{-- COTIZACIONES --}}
                        @if (in_array('quotations', $vc_module_levels) || $is_superadmin)
                            <li>
                                <a class="{{ $firstLevel === 'quotations' && $secondLevel == '' ? 'active' : '' }} nav-link"
                                    href="{{ route('tenant.quotations.index') }}">
                                    Cotizaciones
                                </a>
                            </li>
                        @endif
                        {{-- RESÚMENES --}}
                        @if (in_array('summaries', $vc_module_levels) || $is_superadmin)
                            <li>
                                <a class="{{ $firstLevel === 'summaries' ? 'active' : '' }} nav-link"
                                    href="{{ route('tenant.summaries.index') }}">
                                    Resúmenes
                                </a>
                            </li>
                        @endif
                        {{-- ANULACIONES --}}
                        @if (in_array('voided', $vc_module_levels) || $is_superadmin)
                            <li>
                                <a class="{{ $firstLevel === 'voided' ? 'active' : '' }} nav-link"
                                    href="{{ route('tenant.voided.index') }}">
                                    Anulaciones
                                </a>
                            </li>
                        @endif
                        {{-- OPORTUNIDAD DE VENTA --}}
                        @if (in_array('sale-opportunity', $vc_module_levels) || $is_superadmin)
                            <li>
                                <a class="{{ $firstLevel === 'sale-opportunities' ? 'active' : '' }} nav-link"
                                    href="{{ route('tenant.sale_opportunities.index') }}">
                                    Oportunidad de venta
                                </a>
                            </li>
                        @endif
                        {{-- PEDIDOS --}}
                        @if (in_array('order-note', $vc_module_levels) || $is_superadmin)
                            <li>
                                <a class="{{ $firstLevel === 'order-notes' ? 'active' : '' }} nav-link"
                                    href="{{ route('tenant.order_notes.index') }}">
                                    Pedidos
                                </a>
                            </li>
                        @endif
                        {{-- SERVICIO DE SOPORTE TÉCNICO --}}
                        @if (in_array('technical-service', $vc_module_levels) || $is_superadmin)
                            <li class="{{ $firstLevel === 'technical-services' ? 'active' : '' }}">
                                <a class="nav-link" href="{{ route('tenant.technical_services.index') }}">
                                    Servicio de soporte técnico
                                </a>
                            </li>
                        @endif
                        {{-- SERVICIO DE OPTOMETRÍA (opcional) --}}
                        @if (is_optometry())
                            <li class="{{ $firstLevel === 'optometry-services' ? 'active' : '' }}">
                                <a class="nav-link" href="{{ route('tenant.optometry_services.index') }}">
                                    Servicio de optometría
                                </a>
                            </li>
                        @endif
                        {{-- CONTRATOS y ORDENES DE PRODUCCION --}}
                        @if (in_array('contracts', $vc_module_levels) || $is_superadmin)
                            <li class="mega">
                                <a href="#contrato" data-bs-toggle="collapse" data-role="button"
                                    aria-expanded="{{ in_array($firstLevel, ['contracts', 'production-orders']) ? 'true' : 'false' }}"
                                    class="{{ in_array($firstLevel, ['contracts', 'production-orders']) ? 'active' : '' }}"
                                    data-clicked="{{ in_array($firstLevel, ['contracts', 'production-orders']) ? 'true' : 'false' }}">
                                    <span class="label">Contratos</span>
                                </a>
                                <ul id="contrato"
                                    class="collapse {{ in_array($firstLevel, ['contracts', 'production-orders']) ? 'show' : '' }}">
                                    <li>
                                        <a class="{{ $firstLevel === 'contracts' ? 'active' : '' }} nav-link"
                                            href="{{ route('tenant.contracts.index') }}">
                                            Listado
                                        </a>
                                    </li>
                                    <li>
                                        <a class="{{ $firstLevel === 'production-orders' ? 'active' : '' }} nav-link"
                                            href="{{ route('tenant.production_orders.index') }}">
                                            Ordenes de Producción
                                        </a>
                                    </li>
                                </ul>
                            </li>
                        @endif
                        {{-- COMISIONES / VENDEDORES --}}
                        @if (in_array('incentives', $vc_module_levels) || $is_superadmin)
                            <li class="mega">
                                <a href="#incentives" data-bs-toggle="collapse" data-role="button"
                                    aria-expanded="{{ in_array($firstLevel, ['user-commissions', 'incentives', 'seller']) ? 'true' : 'false' }}"
                                    class="{{ in_array($firstLevel, ['user-commissions', 'incentives', 'seller']) ? 'active' : '' }}"
                                    data-clicked="{{ in_array($firstLevel, ['user-commissions', 'incentives', 'seller']) ? 'true' : 'false' }}">
                                    <span class="label">Comisiones</span>
                                </a>
                                <ul id="incentives"
                                    class="collapse {{ in_array($firstLevel, ['incentives', 'user-commissions', 'seller']) ? 'show' : '' }}">
                                    <li>
                                        <a class="{{ $firstLevel === 'user-commissions' ? 'active' : '' }} nav-link"
                                            href="{{ route('tenant.user_commissions.index') }}">
                                            Vendedores
                                        </a>
                                    </li>
                                    <li>
                                        <a class="{{ $firstLevel === 'seller' && $secondLevel === 'monthly-sales' ? 'active' : '' }} nav-link"
                                            href="{{ route('tenant.seller.monthly-sales') }}">
                                            Ventas mensuales
                                        </a>
                                    </li>
                                    <li>
                                        <a class="{{ $firstLevel === 'incentives' ? 'active' : '' }} nav-link"
                                            href="{{ route('tenant.incentives.index') }}">
                                            Productos
                                        </a>
                                    </li>
                                </ul>
                            </li>
                        @endif
                    </ul>
                </li>
            @endif

            {{-- EXPORTAR CSV MALL --}}
            @if ($configuration->export_mall)
                <li class="mega">
                    <a class="{{ $firstLevel === 'mall' ? 'active' : '' }} nav-link"
                        href="{{ route('tenant.mall.index') }}">
                        <i class="bi bi-download"></i>
                        <span class="label">Exportar CSV Mall</span>
                    </a>
                </li>
            @endif

            {{-- POS --}}
            @if (auth()->user()->type != 'integrator')
                @if (in_array('pos', $vc_modules) || $is_superadmin)
                    <li class="mega">
                        <a href="#pos" data-bs-toggle="collapse" data-role="button"
                            aria-expanded="{{ in_array($firstLevel, ['pos', 'cash', 'advances']) ? 'true' : 'false' }}"
                            class="{{ in_array($firstLevel, ['pos', 'cash', 'advances']) ? 'active' : '' }}"
                            data-clicked="{{ in_array($firstLevel, ['pos', 'cash', 'advances']) ? 'true' : 'false' }}">
                            <i class="bi bi-display"></i>
                            <span class="label">POS</span>
                        </a>
                        <ul id="pos"
                            class="collapse {{ in_array($firstLevel, ['pos', 'cash', 'advances']) ? 'show' : '' }}">
                            @if (in_array('pos', $vc_module_levels) || $is_superadmin)
                                <li>
                                    <a class="{{ $firstLevel === 'pos' && $secondLevel === null ? 'active' : '' }} nav-link"
                                        href="{{ route('tenant.pos.index') }}">Punto de venta</a>
                                </li>
                            @endif
                            @if (in_array('pos_garage', $vc_module_levels) || $is_superadmin)
                                <li>
                                    <a class="{{ $firstLevel === 'pos' && $secondLevel === 'garage' ? 'active' : '' }} nav-link"
                                        href="{{ route('tenant.pos.garage') }}">Venta rápida</a>
                                </li>
                            @endif
                            @if (in_array('cash', $vc_module_levels) || $is_superadmin)
                                <li>
                                    <a class="{{ $firstLevel === 'cash' ? 'active' : '' }} nav-link"
                                        href="{{ route('tenant.cash.index') }}">Listado de cajas</a>
                                </li>
                            @endif
                            @if ($configuration->pos_bottles)
                                <li>
                                    <a class="{{ $firstLevel === 'warranty_document' ? 'active' : '' }} nav-link"
                                        href="{{ route('tenant.warranty_document.index') }}">Botellas</a>
                                </li>
                                <li>
                                    <a class="{{ $firstLevel === 'warranty_document' ? 'active' : '' }} nav-link"
                                        href="{{ route('tenant.warranty_document.report_index') }}">Reporte
                                        Botellas</a>
                                </li>
                            @endif
                            @if (in_array('advances_customers', $vc_module_levels) || $is_superadmin)
                                <li>
                                    <a class="{{ $firstLevel === 'advances' && $thridLevel == 'customers' ? 'active' : '' }} nav-link"
                                        href="{{ route('tenant.advances.index', ['type' => 'customers']) }}">Anticipo
                                        clientes</a>
                                </li>
                            @endif
                            @if (in_array('advances_suppliers', $vc_module_levels) || $is_superadmin)
                                <li>
                                    <a class="{{ $firstLevel === 'advances' && $thridLevel == 'suppliers' ? 'active' : '' }} nav-link"
                                        href="{{ route('tenant.advances.index', ['type' => 'suppliers']) }}">Anticipo
                                        proveedores</a>
                                </li>
                            @endif
                        </ul>
                    </li>
                @endif
            @endif

            {{-- TIENDA VIRTUAL --}}
            @if (in_array('ecommerce', $vc_modules) || $is_superadmin)
                <li class="mega">
                    <a href="#tienda" data-bs-toggle="collapse" data-role="button"
                        aria-expanded="{{ in_array($firstLevel, ['ecommerce', 'items_ecommerce', 'tags', 'promotions', 'orders', 'configuration', 'live-app']) ? 'true' : 'false' }}"
                        class="{{ in_array($firstLevel, ['ecommerce', 'items_ecommerce', 'tags', 'promotions', 'orders', 'configuration', 'live-app']) ? 'active' : '' }}"
                        data-clicked="{{ in_array($firstLevel, ['ecommerce', 'items_ecommerce', 'tags', 'promotions', 'orders', 'configuration', 'live-app']) ? 'true' : 'false' }}">
                        <i class="bi bi-shop"></i>
                        <span class="label">Tienda virtual</span>
                    </a>
                    <ul id="tienda"
                        class="collapse {{ in_array($firstLevel, ['ecommerce', 'items_ecommerce', 'tags', 'promotions', 'orders', 'configuration', 'live-app']) ? 'show' : '' }}">
                        @if (in_array('ecommerce', $vc_module_levels) || $is_superadmin)
                            <li>
                                <a class="nav-link" href="{{ route('tenant.ecommerce.index') }}" target="_blank">
                                    Ir a tienda
                                </a>
                            </li>
                            <li>
                                <a class="nav-link" href="{{ route('tenant.comercios.records') }}" target="_blank">
                                    Ir a tienda 2.0
                                </a>
                            </li>
                            <li>
                                <a class="{{ $firstLevel === 'live-app' && $secondLevel === 'store' ? 'active' : '' }} nav-link"
                                    href="{{ route('tenant.liveapp.store') }}">
                                    Ir a Mi Tienda Smart
                                </a>
                            </li>
                        @endif
                        <li>
                            <a class="{{ $firstLevel === 'store' && $secondLevel === 'users' ? 'active' : '' }} nav-link"
                                href="{{ route('tenant_users_store') }}">Usuarios</a>
                        </li>
                        @if (in_array('ecommerce_orders', $vc_module_levels) || $is_superadmin)
                            <li>
                                <a class="{{ $firstLevel === 'orders' ? 'active' : '' }} nav-link"
                                    href="{{ route('tenant_orders_index') }}">Pedidos</a>
                            </li>
                        @endif
                        @if (in_array('ecommerce_items', $vc_module_levels) || $is_superadmin)
                            <li>
                                <a class="{{ $firstLevel === 'items_ecommerce' ? 'active' : '' }} nav-link"
                                    href="{{ route('tenant.items_ecommerce.index') }}">Productos de tienda
                                    virtual</a>
                            </li>
                        @endif
                        @if (in_array('ecommerce_tags', $vc_module_levels) || $is_superadmin)
                            <li>
                                <a class="{{ $firstLevel === 'tags' ? 'active' : '' }} nav-link"
                                    href="{{ route('tenant.tags.index') }}">Categorias (Etiquetas)</a>
                            </li>
                        @endif
                        @if (in_array('ecommerce_promotions', $vc_module_levels) || $is_superadmin)
                            <li>
                                <a class="{{ $firstLevel === 'promotions' ? 'active' : '' }} nav-link"
                                    href="{{ route('tenant.promotion.index') }}">Promociones (Banners)</a>
                            </li>
                        @endif
                        @if (in_array('ecommerce_settings', $vc_module_levels) || $is_superadmin)
                            <li>
                                <a class="{{ $secondLevel === 'configuration' ? 'active' : '' }} nav-link"
                                    href="{{ route('tenant_ecommerce_configuration') }}">Configuración</a>
                            </li>
                        @endif
                    </ul>
                </li>
            @endif

            {{-- PROYECTOS (Módulo) --}}
            @if (in_array('proyectos', $vc_modules))
                <li class="mega">
                    <a href="#proyect" data-bs-toggle="collapse" data-role="button"
                        aria-expanded="{{ $firstLevel === 'projects' ? 'true' : 'false' }}"
                        class="{{ $firstLevel === 'projects' ? 'active' : '' }}"
                        data-clicked="{{ $firstLevel === 'projects' ? 'true' : 'false' }}">
                        <i class="bi bi-kanban"></i>
                        <span class="label">
                            {{ $configuration->name_module_project ? $configuration->name_module_project : 'Proyectos' }}
                        </span>
                    </a>
                    <ul id="proyect" class="collapse {{ $firstLevel === 'projects' ? 'show' : '' }}">
                        <li>
                            <a class="{{ $firstLevel === 'projects' && $secondLevel === null ? 'active' : '' }} nav-link"
                                href="{{ route('tenant.projects.index') }}">Crear</a>
                        </li>
                        <li>
                            <a class="{{ $firstLevel === 'projects' && $secondLevel === 'list' ? 'active' : '' }} nav-link"
                                href="{{ route('tenant.projects.list') }}">Lista</a>
                        </li>
                    </ul>
                </li>
            @endif

            {{-- PRODUCTOS Y SERVICIOS --}}
            @if (in_array('items', $vc_modules) || $is_superadmin)
                <li class="mega">
                    <a href="#items" data-bs-toggle="collapse" data-role="button"
                        aria-expanded="{{ in_array($firstLevel, [
                            'items',
                            'services',
                            'categories',
                            'brands',
                            'item-lots',
                            'item-sets',
                            'item-lots-group',
                            'coupons',
                            'discount-types',
                            'item-sizes',
                        ])
                            ? 'true'
                            : 'false' }}"
                        class="{{ in_array($firstLevel, [
                            'items',
                            'services',
                            'categories',
                            'brands',
                            'item-lots',
                            'item-sets',
                            'item-lots-group',
                            'coupons',
                            'discount-types',
                            'item-sizes',
                        ])
                            ? 'active'
                            : '' }}"
                        data-clicked="{{ in_array($firstLevel, [
                            'items',
                            'services',
                            'categories',
                            'brands',
                            'item-lots',
                            'item-sets',
                            'item-lots-group',
                            'coupons',
                            'discount-types',
                            'item-sizes',
                        ])
                            ? 'true'
                            : 'false' }}">
                        <i class="bi bi-box-seam"></i>
                        <span class="label">Productos y servicios</span>
                    </a>
                    <ul id="items"
                        class="collapse {{ in_array($firstLevel, [
                            'items',
                            'services',
                            'categories',
                            'brands',
                            'item-lots',
                            'item-sets',
                            'item-lots-group',
                            'coupons',
                            'discount-types',
                            'item-sizes',
                        ])
                            ? 'show'
                            : '' }}">
                        @if (in_array('items', $vc_module_levels) || $is_superadmin)
                            <li class="{{ $firstLevel === 'items' ? 'active' : '' }}">
                                <a class="nav-link" href="{{ route('tenant.items.index') }}">
                                    <span class="label">Productos</span>
                                </a>
                            </li>
                        @endif
                        @if (in_array('items', $vc_module_levels) || $is_superadmin)
                            {{-- <li class="{{ $firstLevel === 'label_colors' ? 'active' : '' }}">
                                <a class="nav-link" href="{{ route('tenant.label_colors.index') }}">
                                    <span class="label">Colores de Etiquetas</span>
                                </a>
                            </li> --}}
                        @endif
                        @if (in_array('items_packs', $vc_module_levels) || $is_superadmin)
                            <li class="{{ $firstLevel === 'item-sets' ? 'active' : '' }}">
                                <a class="nav-link" href="{{ route('tenant.item_sets.index') }}">
                                    <span class="label">Packs y promociones</span>
                                </a>
                            </li>
                        @endif
                        @if (in_array('items_services', $vc_module_levels) || $is_superadmin)
                            <li class="{{ $firstLevel === 'services' ? 'active' : '' }}">
                                <a class="nav-link" href="{{ route('tenant.services') }}">
                                    <span class="label">Servicios</span>
                                </a>
                            </li>
                        @endif
                        @if (in_array('items_categories', $vc_module_levels) || $is_superadmin)
                            <li class="{{ $firstLevel === 'categories' ? 'active' : '' }}">
                                <a class="nav-link" href="{{ route('tenant.categories.index') }}">
                                    <span class="label">Categorías</span>
                                </a>
                            </li>
                        @endif
                        @if (in_array('cupones', $vc_module_levels) || $is_superadmin)
                            <li class="{{ $firstLevel === 'coupons' ? 'active' : '' }}">
                                <a class="nav-link" href="{{ route('tenant.coupons.index') }}">
                                    <span class="label">Cupones</span>
                                </a>
                            </li>
                        @endif
                        @if (in_array('items_brands', $vc_module_levels) || $is_superadmin)
                            <li class="{{ $firstLevel === 'brands' ? 'active' : '' }}">
                                <a class="nav-link" href="{{ route('tenant.brands.index') }}">
                                    <span class="label">Marcas</span>
                                </a>
                            </li>
                        @endif
                        <li class="{{ $firstLevel === 'ingredient-attributes' ? 'active' : '' }}">
                            <a class="nav-link" href="{{ route('tenant.ingredient-attributes.index') }}">
                                <span class="label">Ingredientes</span>
                            </a>
                        </li>
                        <li class="{{ $firstLevel === 'line-attributes' ? 'active' : '' }}">
                            <a class="nav-link" href="{{ route('tenant.line-attributes.index') }}">
                                <span class="label">Líneas</span>
                            </a>
                        </li>
                        @if (in_array('items_lots', $vc_module_levels) || $is_superadmin)
                            <li class="{{ $firstLevel === 'item-lots' ? 'active' : '' }}">
                                <a class="nav-link" href="{{ route('tenant.item-lots.index') }}">
                                    <span class="label">Series</span>
                                </a>
                            </li>
                        @endif
                        @if (in_array('item_lots_group', $vc_module_levels) || $is_superadmin)
                            <li class="{{ $firstLevel === 'item-lots-group' ? 'active' : '' }}">
                                <a class="nav-link" href="{{ route('tenant.item-lots-group.index') }}">
                                    <span class="label">Lotes</span>
                                </a>
                            </li>
                        @endif

                        <li class="{{ $firstLevel === 'price-adjustments' ? 'active' : '' }}">
                            <a class="nav-link" href="{{ route('tenant.price_adjustments.index') }}">
                                <span class="label">Ajustes de precio</span>
                            </a>
                        </li>

                        <li class="{{ $firstLevel === 'discount-types' ? 'active' : '' }}">
                            <a class="nav-link" href="{{ route('tenant.discount_types.index') }}">
                                <span class="label">Tipos de descuentos</span>
                            </a>
                        </li>
                        {{-- <li class="{{ $firstLevel === 'charge-types' ? 'active' : '' }}">
                            <a class="nav-link" href="{{ route('tenant.charge_types.index') }}">
                                <span class="label">Tipos de cargos</span>
                            </a>
                        </li> --}}
                        @if ($configuration->plate_number_config)
                            <li class="{{ $firstLevel === 'plate-numbers' ? 'active' : '' }}">
                                <a class="nav-link" href="{{ route('tenant.plate_numbers.index') }}">
                                    <span class="label">Placas</span>
                                </a>
                            </li>
                            <li class="{{ $firstLevel === 'quotation-technicians' ? 'active' : '' }}">
                                <a class="nav-link" href="{{ route('tenant.quotations_technicians.index') }}">
                                    <span class="label">Técnicos</span>
                                </a>
                            </li>
                        @endif
                        @php
                            $isClothesShoes = \Modules\BusinessTurn\Models\BusinessTurn::isClothesShoes();
                        @endphp
                        @if ($isClothesShoes)
                            <li class="{{ $firstLevel === 'item-sizes' ? 'active' : '' }}">
                                <a class="nav-link" href="{{ route('tenant.item-sizes.index') }}">
                                    <span class="label">Tallas</span>
                                </a>
                            </li>
                        @endif
                    </ul>
                </li>
            @endif
            @if ($is_weapon_tracking)
                <li class="{{ $firstLevel === 'weapon-tracking' ? 'active' : '' }}">
                    <a class="nav-link" href="{{ url('weapon-tracking') }}">
                        <i class="bi bi-shield-fill"></i>
                        <span class="label">Control de armas</span>
                    </a>
                </li>
            @endif
            {{-- CLIENTES --}}
            @if (in_array('persons', $vc_modules) || $is_superadmin)
                <li class="mega">
                    <a href="#customers" data-bs-toggle="collapse" data-role="button"
                        aria-expanded="{{ in_array($firstLevel, ['persons', 'person-types', 'person-packers', 'person-dispatchers', 'massive-message']) ? 'true' : 'false' }}"
                        class="{{ in_array($firstLevel, ['persons', 'person-types', 'person-packers', 'person-dispatchers', 'massive-message']) ? 'active' : '' }}">
                        <i class="bi bi-people"></i>
                        <span class="label">Clientes</span>
                    </a>
                    <ul id="customers"
                        class="collapse {{ in_array($firstLevel, ['persons', 'person-types', 'person-packers', 'person-dispatchers', 'massive-message']) ? 'show' : '' }}">
                        @if (in_array('clients', $vc_module_levels) || $is_superadmin)
                            <li>
                                <a class="{{ $firstLevel === 'persons' && $secondLevel === 'customers' ? 'active' : '' }} nav-link"
                                    href="{{ route('tenant.persons.index', ['type' => 'customers']) }}">
                                    <span class="label"> Listado de clientes </span>
                                </a>
                            </li>
                        @endif

                        @if ($configuration->dispatchers_packers_document)
                            <li>
                                <a class="{{ $firstLevel === 'person-packers' ? 'active' : '' }} nav-link"
                                    href="{{ route('tenant.person_packers.index') }}">
                                    <span class="label">Empacadores</span>
                                </a>
                            </li>
                            <li>
                                <a class="{{ $firstLevel === 'person-dispatchers' ? 'active' : '' }} nav-link"
                                    href="{{ route('tenant.person_dispatchers.index') }}">
                                    <span class="label">Repartidores</span>
                                </a>
                            </li>
                        @endif
                        @if ($configuration->enabled_sales_agents)
                            <li>
                                <a class="{{ $firstLevel === 'agents' ? 'active' : '' }} nav-link"
                                    href="{{ route('tenant.agents.index') }}">
                                    <span class="label">Agentes de venta</span>
                                </a>
                            </li>
                        @endif
                        @if ($configuration->package_handlers)
                            <li>
                                <a class="{{ $firstLevel === 'persons_drivers' ? 'active' : '' }} nav-link"
                                    href="{{ route('tenant.persons_drivers.index') }}">
                                    <span class="label">Conductores</span>
                                </a>
                            </li>
                        @endif
                        @if (in_array('clients_types', $vc_module_levels) || $is_superadmin)
                            <li>
                                <a class="{{ $firstLevel === 'person-types' ? 'active' : '' }} nav-link"
                                    href="{{ route('tenant.person_types.index') }}">
                                    <span class="label">Tipos de clientes</span>
                                </a>
                            </li>
                        @endif
                        <li>
                            <a class="{{ $firstLevel === 'massive-message' ? 'active' : '' }} nav-link"
                                href="{{ route('massive_messages.index') }}">
                                <span class="label">Mensajes whatsapp</span>
                            </a>
                        </li>
                        <li>
                            <a class="{{ $firstLevel === 'zones' ? 'active' : '' }} nav-link"
                                href="{{ route('tenant.zone.index') }}">
                                <span class="label">Lista de zonas</span>
                            </a>
                        </li>
                    </ul>
                </li>
            @endif

            {{-- PROVEEDORES --}}
            @if (in_array('suppliers', $vc_modules) || $is_superadmin)
                <li class="mega">
                    <a class="{{ $firstLevel === 'persons' && $secondLevel === 'suppliers' ? 'active' : '' }} nav-link"
                        href="{{ route('tenant.persons.index', ['type' => 'suppliers']) }}">
                        <i class="bi bi-briefcase"></i>
                        <span class="label">Proveedores</span>
                    </a>
                </li>
            @endif
            @if (in_array('supplies', $vc_modules) || $is_superadmin)
                <li class="mega">
                    <a href="#supplies" data-bs-toggle="collapse" data-role="button"
                        aria-expanded="{{ $firstLevel === 'supplies' ? 'true' : 'false' }}"
                        class="{{ $firstLevel === 'supplies' ? 'active' : '' }}">
                        <i class="bi bi-box-seam me-1"></i>
                        <span class="label">Predios</span>
                    </a>

                    <ul id="supplies" class="collapse {{ $firstLevel === 'supplies' ? 'show' : '' }}">
                        {{-- <li class="mega">
                        <a href="#contribuyentes" data-bs-toggle="collapse" data-role="button"
                            aria-expanded="{{ in_array($firstLevel, ['persons', 'supplies']) ? 'true' : 'false' }}"
                            class="{{ in_array($firstLevel, ['persons', 'supplies']) ? 'active' : '' }}"
                            data-clicked="{{ in_array($firstLevel, ['persons', 'supplies']) ? 'true' : 'false' }}">
                            <i class="bi bi-people me-1"></i>
                            <span class="label">Pagos</span>
                        </a>
                        <ul id="contribuyentes"
                            class="collapse {{ in_array($firstLevel, ['supplies']) && $secondLevel === 'payments' ? 'show' : '' }}">
                            <li>
                                <a class="{{ $firstLevel === 'supplies' && $secondLevel === 'payments' && $thirdLevel === 'consumption' ? 'active' : '' }} nav-link"
                                    href="{{ route('tenant.supplies.registers.index') }}">
                                    Pagos por consumo
                                </a>
                            </li>
                            <li>
                                <a class="{{ $firstLevel === 'supplies' && $secondLevel === 'payments' && $thirdLevel === 'others' ? 'active' : '' }} nav-link"
                                    href="{{ route('tenant.supplies.persons.index', ['type' => 'customers']) }}">
                                    Otros pagos
                                </a>
                            </li>
                        </ul>
                    </li> --}}
                        <li class="mega">
                            <a href="#contribuyentes" data-bs-toggle="collapse" data-role="button"
                                aria-expanded="{{ in_array($firstLevel, ['persons', 'supplies']) && in_array($secondLevel, ['registers', 'customers']) ? 'true' : 'false' }}"
                                class="{{ in_array($firstLevel, ['persons', 'supplies']) && in_array($secondLevel, ['registers', 'customers']) ? 'active' : '' }}"
                                data-clicked="{{ in_array($firstLevel, ['persons', 'supplies']) && in_array($secondLevel, ['registers', 'customers']) ? 'true' : 'false' }}">
                                <i class="bi bi-people me-1"></i>
                                <span class="label">Contribuyente</span>
                            </a>
                            <ul id="contribuyentes"
                                class="collapse {{ in_array($firstLevel, ['persons', 'supplies']) && in_array($secondLevel, ['registers', 'customers']) ? 'show' : '' }}">
                                <li>
                                    <a class="{{ $firstLevel === 'supplies' && $secondLevel === 'registers' ? 'active' : '' }} nav-link"
                                        href="{{ route('tenant.supplies.registers.index') }}">
                                        Registros
                                    </a>
                                </li>
                                <li>
                                    <a class="{{ $firstLevel === 'supplies' && $secondLevel === 'persons' ? 'active' : '' }} nav-link"
                                        href="{{ route('tenant.supplies.persons.index', ['type' => 'customers']) }}">
                                        Contribuyentes
                                    </a>
                                </li>

                            </ul>
                        </li>
                        <li class="mega">
                            <a href="#documentos" data-bs-toggle="collapse" data-role="button"
                                aria-expanded="{{ in_array($secondLevel, ['solicitudes', 'contracts']) ? 'true' : 'false' }}"
                                class="{{ in_array($secondLevel, ['solicitudes', 'contracts']) ? 'active' : '' }}"
                                data-clicked="{{ in_array($secondLevel, ['solicitudes', 'contracts']) ? 'true' : 'false' }}">
                                <i class="bi bi-file-earmark-text me-1"></i>
                                <span class="label">Documentos</span>
                            </a>
                            <ul id="documentos"
                                class="collapse {{ in_array($secondLevel, ['solicitudes', 'contracts']) ? 'show' : '' }}">
                                <li>
                                    <a class="{{ $firstLevel === 'supplies' && $secondLevel === 'solicitudes' ? 'active' : '' }} nav-link"
                                        href="{{ route('tenant.supplies.solicitudes.index') }}">
                                        Solicitudes
                                    </a>
                                </li>
                                <li>
                                    <a class="{{ $firstLevel === 'supplies' && $secondLevel === 'solicitudes' ? 'active' : '' }} nav-link"
                                        href="{{ route('tenant.supplies.solicitudes.index', ['forOperators' => 1]) }}">
                                        Operarios
                                    </a>
                                </li>
                                <li>
                                    <a class="{{ $firstLevel === 'supplies' && $secondLevel === 'contracts' ? 'active' : '' }} nav-link"
                                        href="{{ route('tenant.supplies.contracts.index') }}">
                                        Contratos
                                    </a>
                                </li>
                            </ul>
                        </li>
                        <li class="mega">
                            <a href="#catastro" data-bs-toggle="collapse" data-role="button"
                                aria-expanded="{{ in_array($secondLevel, ['supply-vias', 'sectors']) || ($firstLevel === 'supplies' && $secondLevel === null) ? 'true' : 'false' }}"
                                class="{{ in_array($secondLevel, ['supply-vias', 'sectors']) || ($firstLevel === 'supplies' && $secondLevel === null) ? 'active' : '' }}"
                                data-clicked="{{ in_array($secondLevel, ['supply-vias', 'sectors']) ? 'true' : 'false' }}">
                                <i class="bi bi-map me-1"></i>
                                <span class="label">Catastro</span>
                            </a>
                            <ul id="catastro"
                                class="collapse {{ in_array($secondLevel, ['supply-vias', 'sectors']) || ($firstLevel === 'supplies' && $secondLevel === null) ? 'show' : '' }}">
                                <li>
                                    <a class="{{ $firstLevel === 'supplies' && $secondLevel === 'supply-vias' ? 'active' : '' }} nav-link"
                                        href="{{ route('tenant.supplies.supply_vias.index') }}">
                                        Vías
                                    </a>
                                </li>
                                <li>
                                    <a class="{{ $firstLevel === 'supplies' && $secondLevel === 'sectors' ? 'active' : '' }} nav-link"
                                        href="{{ route('tenant.supplies.sectors.index') }}">
                                        Sectores
                                    </a>
                                </li>
                                <li>
                                    <a class="{{ $firstLevel === 'supplies' && $secondLevel === null ? 'active' : '' }} nav-link"
                                        href="{{ route('tenant.supplies.index') }}">
                                        Predios
                                    </a>
                                </li>
                            </ul>
                        </li>
                        <li class="mega">
                            <a href="#tarifas" data-bs-toggle="collapse" data-role="button"
                                aria-expanded="{{ in_array($secondLevel, ['plans', 'concepts']) ? 'true' : 'false' }}"
                                class="{{ in_array($secondLevel, ['plans', 'concepts']) ? 'active' : '' }}"
                                data-clicked="{{ in_array($secondLevel, ['plans', 'concepts']) ? 'true' : 'false' }}">
                                <i class="bi bi-currency-dollar me-1"></i>
                                <span class="label">Tarifas</span>
                            </a>
                            <ul id="tarifas"
                                class="collapse {{ in_array($secondLevel, ['plans', 'concepts']) ? 'show' : '' }}">
                                <li>
                                    <a class="{{ $firstLevel === 'supplies' && $secondLevel === 'plans' ? 'active' : '' }} nav-link"
                                        href="{{ route('tenant.supplies.plans.index') }}">
                                        Tarifa, Agua y Alcantarillado
                                    </a>
                                </li>

                                <li>
                                    <a class="{{ $firstLevel === 'supplies' && $secondLevel === 'concepts' ? 'active' : '' }} nav-link"
                                        href="{{ route('tenant.supplies.concepts.index') }}">
                                        Conceptos
                                    </a>
                                </li>
                            </ul>
                        </li>
                        <li class="mega">
                            <a href="#mesa-de-partes" data-bs-toggle="collapse" data-role="button"
                                aria-expanded="{{ in_array($secondLevel, ['processes']) ? 'true' : 'false' }}"
                                class="{{ in_array($secondLevel, ['processes']) ? 'active' : '' }}"
                                data-clicked="{{ in_array($secondLevel, ['processes']) ? 'true' : 'false' }}">
                                <i class="bi bi-people me-1"></i>
                                <span class="label">Mesa de partes</span>
                            </a>
                            <ul id="mesa-de-partes"
                                class="collapse {{ in_array($secondLevel, ['processes']) ? 'show' : '' }}">
                                <li>
                                    <a class="{{ $firstLevel === 'supplies' && $secondLevel === 'processes' ? 'active' : '' }} nav-link"
                                        href="{{ route('tenant.supplies.processes.index') }}">
                                        Tramites
                                    </a>
                                </li>
                            </ul>
                        </li>

                        <li class="mega">
                            <a href="#acciones" data-bs-toggle="collapse" data-role="button"
                                aria-expanded="{{ in_array($secondLevel, ['outages']) ? 'true' : 'false' }}"
                                class="{{ in_array($secondLevel, ['outages']) ? 'active' : '' }}"
                                data-clicked="{{ in_array($secondLevel, ['outages']) ? 'true' : 'false' }}">
                                <i class="bi bi-gear me-1"></i>
                                <span class="label">Acciones</span>
                            </a>
                            <ul id="acciones"
                                class="collapse {{ in_array($secondLevel, ['outages']) ? 'show' : '' }}">
                                <li>
                                    <a class="{{ $firstLevel === 'supplies' && $secondLevel === 'outages' && request('type_outage') === 'outage' ? 'active' : '' }} nav-link"
                                        href="{{ route('tenant.supplies.outages.index', ['type_outage' => 'outage']) }}">
                                        Cortes
                                    </a>
                                </li>
                                <li>
                                    <a class="{{ $firstLevel === 'supplies' && $secondLevel === 'outages' && request('type_outage') === 'reconnection' ? 'active' : '' }} nav-link"
                                        href="{{ route('tenant.supplies.outages.index', ['type_outage' => 'reconnection']) }}">
                                        Reconexiones
                                    </a>
                                </li>
                            </ul>
                        </li>

                        <li class="mega">
                            <a href="#reportes" data-bs-toggle="collapse" data-role="button"
                                aria-expanded="{{ in_array($secondLevel, ['debts']) ? 'true' : 'false' }}"
                                class="{{ in_array($secondLevel, ['debts']) ? 'active' : '' }}"
                                data-clicked="{{ in_array($secondLevel, ['debts']) ? 'true' : 'false' }}">
                                <i class="bi bi-file-earmark-text me-1"></i>
                                <span class="label">Reportes</span>
                            </a>
                            <ul id="reportes"
                                class="collapse {{ in_array($secondLevel, ['debts']) ? 'show' : '' }}">
                                <li>
                                    <a class="{{ $firstLevel === 'supplies' && $secondLevel === 'advance-payments' ? 'active' : '' }} nav-link"
                                        href="{{ route('tenant.supplies.advance_payments.index') }}">
                                        Pagos por adelantado
                                    </a>
                                </li>
                                <li>
                                    <a class="{{ $firstLevel === 'supplies' && $secondLevel === 'debts' ? 'active' : '' }} nav-link"
                                        href="{{ route('tenant.supplies.debts.index') }}">
                                        Deudas
                                    </a>
                                </li>
                                <li>
                                    <a class="{{ $firstLevel === 'supplies' && $secondLevel === 'receipts' ? 'active' : '' }} nav-link"
                                        href="{{ route('tenant.supplies.receipts.index') }}">
                                        Recibos
                                    </a>
                                </li>
                            </ul>
                        </li>


                        <li class="mega">
                            <a href="#catalogos" data-bs-toggle="collapse" data-role="button"
                                aria-expanded="{{ in_array($secondLevel, ['offices', 'type_debts']) ? 'true' : 'false' }}"
                                class="{{ in_array($secondLevel, ['offices', 'type_debts']) ? 'active' : '' }}"
                                data-clicked="{{ in_array($secondLevel, ['offices', 'type_debts']) ? 'true' : 'false' }}">
                                <i class="bi bi-list-check me-1"></i>
                                <span class="label">Catálogos</span>
                            </a>
                            <ul id="catalogos"
                                class="collapse {{ in_array($secondLevel, ['offices', 'type_debts']) ? 'show' : '' }}">


                                <li>
                                    <a class="{{ $firstLevel === 'supplies' && $secondLevel === 'offices' ? 'active' : '' }} nav-link"
                                        href="{{ route('tenant.supplies.offices.index') }}">
                                        Oficinas
                                    </a>
                                </li>
                                <li>
                                    <a class="{{ $firstLevel === 'supplies' && $secondLevel === 'type_debts' ? 'active' : '' }} nav-link"
                                        href="{{ route('tenant.supplies.type_debts.index') }}">
                                        Tipos de Deuda
                                    </a>
                                </li>
                            </ul>
                        </li>





                    </ul>
                </li>
            @endif

            {{-- RESERVAS --}}
            @if (in_array('reservation', $vc_modules) || $is_superadmin)
                <li class="mega">
                    <a href="#reservation" data-bs-toggle="collapse" data-role="button"
                        aria-expanded="{{ $firstLevel === 'canchas' ? 'true' : 'false' }}"
                        class="{{ $firstLevel === 'canchas' ? 'active' : '' }}">
                        <i class="bi bi-calendar-week me-1"></i>
                        <span class="label">Reservaciones</span>
                    </a>
                    <ul id="reservation" class="collapse {{ $firstLevel === 'canchas' ? 'show' : '' }}">
                        <li>
                            <a class="{{ $firstLevel === 'canchas' && $secondLevel === null ? 'active' : '' }} nav-link"
                                href="{{ route('tenant.canchas.index') }}">
                                Lista
                            </a>
                        </li>
                        <li>
                            <a class="{{ $firstLevel === 'canchas' && $secondLevel === 'types' ? 'active' : '' }} nav-link"
                                href="{{ route('tenant.canchas_types.index') }}">
                                Tipos
                            </a>
                        </li>
                    </ul>
                </li>
            @endif

            {{-- RESTAURANTE --}}
            @if (in_array('restaurant_app', $vc_modules) || $is_superadmin)
                <li class="mega">
                    <a href="#restaurant" data-bs-toggle="collapse" data-role="button"
                        aria-expanded="{{ in_array($firstLevel, ['restaurant']) ? 'true' : 'false' }}"
                        class="{{ in_array($firstLevel, ['restaurant']) ? 'active' : '' }}">
                        <i class="bi bi-egg-fried me-1"></i>
                        <span class="label">Comanda</span>
                    </a>
                    <ul id="restaurant"
                        class="collapse {{ in_array($firstLevel, [
                            'purchases',
                            'expenses',
                            'purchase-quotations',
                            'fixed-asset',
                            'purchase-settlements',
                            'purchase-orders',
                        ])
                            ? 'show'
                            : '' }}">
                        <li>
                            <a class="{{ $firstLevel === 'restaurant' && $secondLevel === 'attention' ? 'active' : '' }} nav-link"
                                href="{{ route('restaurant.attention') }}">
                                Atención
                            </a>
                        </li>
                        <li>
                            <a class="{{ $firstLevel === 'restaurant' && $secondLevel === 'orders' ? 'active' : '' }} nav-link"
                                href="{{ route('restaurant.ordens') }}">
                                Pedidos
                            </a>
                        </li>
                        <li>
                            <a class="{{ $firstLevel === 'restaurant' && $secondLevel === 'tables' ? 'active' : '' }} nav-link"
                                href="{{ route('restaurant.tables') }}">
                                Mesas
                            </a>
                        </li>

                        <li>
                            <a class="{{ $firstLevel === 'restaurant' && $secondLevel === 'areas' ? 'active' : '' }} nav-link"
                                href="{{ route('restaurant.areas') }}">
                                Áreas
                            </a>
                        </li>

                        <li>
                            <a class="{{ $firstLevel === 'restaurant' && $secondLevel === 'qr-cart' ? 'active' : '' }} nav-link"
                                href="{{ route('restaurant.qr-cart') }}">
                                QR de carta
                            </a>
                        </li>
                    </ul>
                </li>
            @endif

            {{-- COMPRAS --}}
            @if (auth()->user()->type != 'integrator')
                @if (in_array('purchases', $vc_modules) || $is_superadmin)
                    <li class="mega">
                        <a href="#purchases" data-bs-toggle="collapse" data-role="button"
                            aria-expanded="{{ in_array($firstLevel, [
                                'purchases',
                                'expenses',
                                'purchase-quotations',
                                'fixed-asset',
                                'purchase-settlements',
                                'purchase-orders',
                            ])
                                ? 'true'
                                : 'false' }}"
                            class="{{ in_array($firstLevel, [
                                'purchases',
                                'expenses',
                                'purchase-quotations',
                                'fixed-asset',
                                'purchase-settlements',
                                'purchase-orders',
                            ])
                                ? 'active'
                                : '' }}">
                            <i class="bi bi-bag-check"></i>
                            <span class="label">Compras</span>
                        </a>
                        <ul id="purchases"
                            class="collapse {{ in_array($firstLevel, [
                                'purchases',
                                'expenses',
                                'purchase-quotations',
                                'fixed-asset',
                                'purchase-settlements',
                                'purchase-orders',
                            ])
                                ? 'show'
                                : '' }}">
                            @if (in_array('purchases_create', $vc_module_levels) || $is_superadmin)
                                <li>
                                    <a class="{{ $firstLevel === 'purchases' && $secondLevel === 'create' ? 'active' : '' }} nav-link"
                                        href="{{ route('tenant.purchases.create') }}">
                                        Nueva compra
                                    </a>
                                </li>
                            @endif
                            @if (in_array('purchases_list', $vc_module_levels) || $is_superadmin)
                                <li>
                                    <a class="{{ $firstLevel === 'purchases' && $secondLevel != 'create' ? 'active' : '' }} nav-link"
                                        href="{{ route('tenant.purchases.index') }}">
                                        Listado de compras
                                    </a>
                                </li>
                            @endif
                            @if (in_array('purchase_settlements', $vc_module_levels) || $is_superadmin)
                                <li>
                                    <a class="{{ $firstLevel === 'purchase-settlements' ? 'active' : '' }} nav-link"
                                        href="{{ route('tenant.purchase-settlements.index') }}">Liquidación de
                                        compra</a>
                                </li>
                            @endif
                            @if (in_array('purchases_quotations', $vc_module_levels) || $is_superadmin)
                                <li>
                                    <a class="{{ $firstLevel === 'purchase-quotations' ? 'active' : '' }} nav-link"
                                        href="{{ route('tenant.purchase-quotations.index') }}">
                                        Solicitar Cotización
                                    </a>
                                </li>
                            @endif
                            @if (in_array('purchases_orders', $vc_module_levels) || $is_superadmin)
                                <li>
                                    <a class="{{ $firstLevel === 'purchase-orders' ? 'active' : '' }} nav-link"
                                        href="{{ route('tenant.purchase-orders.index') }}">
                                        Ordenes de compra
                                    </a>
                                </li>
                            @endif
                            @if (in_array('purchases_expenses', $vc_module_levels) || $is_superadmin)
                                <li>
                                    <a class="{{ $firstLevel === 'expenses' ? 'active' : '' }} nav-link"
                                        href="{{ route('tenant.expenses.index') }}">
                                        Gastos diversos
                                    </a>
                                </li>
                            @endif
                            @if (in_array('purchases_fixed_assets_purchases', $vc_module_levels) ||
                                    in_array('purchases_fixed_assets_items', $vc_module_levels) ||
                                    $is_superadmin)
                                <li>
                                    <a href="#activos" data-bs-toggle="collapse"
                                        aria-expanded="{{ $firstLevel === 'fixed-asset' ? 'true' : 'false' }}"
                                        class="{{ $firstLevel === 'fixed-asset' ? 'active' : '' }} nav-link">
                                        Activos fijos
                                    </a>
                                    <ul id="activos"
                                        class="collapse {{ $firstLevel === 'fixed-asset' ? 'show' : '' }}">
                                        @if (in_array('purchases_fixed_assets_items', $vc_module_levels) || $is_superadmin)
                                            <li>
                                                <a class="{{ $firstLevel === 'fixed-asset' && $secondLevel === 'items' ? 'active' : '' }} nav-link"
                                                    href="{{ route('tenant.fixed_asset_items.index') }}">
                                                    Ítems
                                                </a>
                                            </li>
                                        @endif
                                        @if (in_array('purchases_fixed_assets_purchases', $vc_module_levels) || $is_superadmin)
                                            <li>
                                                <a class="{{ $firstLevel === 'fixed-asset' && $secondLevel === 'purchases' ? 'active' : '' }} nav-link"
                                                    href="{{ route('tenant.fixed_asset_purchases.index') }}">
                                                    Compras
                                                </a>
                                            </li>
                                        @endif
                                    </ul>
                                </li>
                            @endif
                        </ul>
                    </li>
                @endif
            @endif

            {{-- INVENTARIO --}}
            @if (in_array('inventory', $vc_modules) || $is_superadmin)
                <li class="mega">
                    <a href="#inventario" data-bs-toggle="collapse" data-role="button"
                        aria-expanded="{{ in_array($firstLevel, ['inventory', 'moves', 'transfers', 'devolutions']) ||
                        ($firstLevel === 'reports' &&
                            in_array($secondLevel, ['kardex', 'inventory', 'valued-kardex', 'stock_date', 'average-cost']))
                            ? 'true'
                            : 'false' }}"
                        class="{{ in_array($firstLevel, ['inventory', 'moves', 'transfers', 'devolutions']) ||
                        ($firstLevel === 'reports' &&
                            in_array($secondLevel, ['kardex', 'inventory', 'valued-kardex', 'stock_date', 'average-cost']))
                            ? 'active'
                            : '' }}">
                        <i class="bi bi-journal-bookmark"></i>
                        <span class="label">Inventario</span>
                    </a>
                    <ul id="inventario"
                        class="collapse {{ in_array($firstLevel, ['inventory', 'moves', 'transfers', 'devolutions']) ||
                        ($firstLevel === 'reports' &&
                            in_array($secondLevel, ['kardex', 'inventory', 'valued-kardex', 'stock_date', 'average-cost']))
                            ? 'show'
                            : '' }}">
                        @if (in_array('inventory_references', $vc_module_levels) || $is_superadmin)
                            <li>
                                <a class="{{ $firstLevel === 'inventory-reference' ? 'active' : '' }} nav-link"
                                    href="{{ route('tenant.inventory_references.index') }}">
                                    <span class="label">Referencias</span>
                                </a>
                            </li>
                        @endif
                        @if (in_array('inventory', $vc_module_levels) || $is_superadmin)
                            <li>
                                <a class="{{ $firstLevel === 'inventory' ? 'active' : '' }} nav-link"
                                    href="{{ route('inventory.index') }}">
                                    <span class="label">Movimientos</span>
                                </a>
                            </li>
                        @endif
                        @if (in_array('inventory_transfers', $vc_module_levels) || $is_superadmin)
                            <li>
                                <a class="{{ $firstLevel === 'transfers' ? 'active' : '' }} nav-link"
                                    href="{{ route('transfers.index') }}">
                                    <span class="label">Traslados</span>
                                </a>
                            </li>
                        @endif
                        @if (in_array('inventory_devolutions', $vc_module_levels) || $is_superadmin)
                            <li>
                                <a class="{{ $firstLevel === 'devolutions' ? 'active' : '' }} nav-link"
                                    href="{{ route('devolutions.index') }}">
                                    <span class="label">Devoluciones</span>
                                </a>
                            </li>
                        @endif
                        <li>
                            <a class="{{ $firstLevel === 'inventory' && $secondLevel === 'validate' ? 'active' : '' }} nav-link"
                                href="{{ route('inventory.validate.index') }}">
                                <span class="label">Validar inventario</span>
                            </a>
                        </li>
                        @if (in_array('inventory_report_kardex', $vc_module_levels) || $is_superadmin)
                            <li>
                                <a class="{{ $firstLevel === 'reports' && $secondLevel === 'kardex' ? 'active' : '' }} nav-link"
                                    href="{{ route('reports.kardex.index') }}">
                                    <span class="label">Reporte kardex</span>
                                </a>
                            </li>
                        @endif
                        @if (in_array('inventory_report', $vc_module_levels) || $is_superadmin)
                            <li>
                                <a class="{{ $firstLevel === 'reports' && $secondLevel === 'inventory' ? 'active' : '' }} nav-link"
                                    href="{{ route('reports.inventory.index') }}">
                                    <span class="label">Reporte inventario</span>
                                </a>
                            </li>
                        @endif
                        @if (in_array('inventory_report_kardex', $vc_module_levels) || $is_superadmin)
                            <li>
                                <a class="{{ $firstLevel === 'reports' && $secondLevel === 'valued-kardex' ? 'active' : '' }} nav-link"
                                    href="{{ route('reports.valued_kardex.index') }}">
                                    <span class="label">Kardex valorizado 13.1</span>
                                </a>
                            </li>
                            <li>
                                <a class="{{ $firstLevel === 'item-cost-history' ? 'active' : '' }} nav-link"
                                    href="{{ route('tenant.item-cost-history.index') }}">
                                    <span class="label">Inventario - Margen de ganancia</span>
                                </a>
                            </li>
                        @endif
                        @if ($inventory_configuration->inventory_review || $is_superadmin)
                            <li class="{{ $firstLevel === 'inventory-review' ? 'active' : '' }}">
                                <a class="nav-link" href="{{ route('tenant.inventory-review.index') }}">
                                    <span class="label">Revisión de inventario</span>
                                </a>
                            </li>
                        @endif
                        @if (in_array('stock_date', $vc_module_levels) || $is_superadmin)
                            <li>
                                <a class="{{ $firstLevel === 'reports' && $secondLevel === 'stock_date' ? 'active' : '' }} nav-link"
                                    href="{{ route('tenant.stock-date.index') }}">
                                    <span class="label">Stock histórico</span>
                                </a>
                            </li>
                        @endif
                        @if (in_array('kardexaverage', $vc_module_levels) || $is_superadmin)
                            <li>
                                <a class="{{ $firstLevel === 'reports' && $secondLevel === 'average-cost' ? 'active' : '' }} nav-link"
                                    href="{{ route('reports.kardexaverage.index') }}">
                                    <span class="label">Kardex costo promedio</span>
                                </a>
                            </li>
                        @endif
                    </ul>
                </li>
            @endif

            {{-- PLANILLA --}}
            @if (in_array('payroll', $vc_modules) || $is_superadmin)
                <li class="mega">
                    <a href="#payroll" data-bs-toggle="collapse" data-role="button"
                        aria-expanded="{{ $firstLevel === 'payroll' ? 'true' : 'false' }}"
                        class="{{ $firstLevel === 'payroll' ? 'active' : '' }} nav-link">
                        <i class="bi bi-file-earmark-person"></i>
                        <span class="label">Planilla</span>
                    </a>
                    <ul id="payroll" class="collapse {{ $firstLevel === 'payroll' ? 'show' : '' }}">
                        <li>
                            <a class="{{ $firstLevel === 'payroll' && $secondLevel === null ? 'active' : '' }} nav-link"
                                href="{{ route('payroll.index') }}">
                                <span class="label">Lista</span>
                            </a>
                        </li>
                    </ul>
                </li>
            @endif

            {{-- USUARIOS y LOCALES --}}
            @if (in_array('establishments', $vc_modules) || $is_superadmin)
                <li class="mega">
                    <a href="#establishments" data-bs-toggle="collapse" data-role="button"
                        aria-expanded="{{ in_array($firstLevel, ['users', 'establishments']) ? 'true' : 'false' }}"
                        class="{{ in_array($firstLevel, ['users', 'establishments']) ? 'active' : '' }}">
                        <i class="bi bi-people-fill"></i>
                        <span class="label">Usuarios y locales</span>
                    </a>
                    <ul id="establishments"
                        class="collapse {{ in_array($firstLevel, ['users', 'establishments']) ? 'show' : '' }}">
                        @if (in_array('users', $vc_module_levels) || $is_superadmin)
                            <li>
                                <a class="{{ $firstLevel === 'users' ? 'active' : '' }} nav-link"
                                    href="{{ route('tenant.users.index') }}">
                                    <span class="label">Usuarios</span>
                                </a>
                            </li>
                        @endif
                        @if (in_array('users_establishments', $vc_module_levels) || $is_superadmin)
                            <li>
                                <a class="{{ $firstLevel === 'establishments' ? 'active' : '' }} nav-link"
                                    href="{{ route('tenant.establishments.index') }}">
                                    <span class="label">Establecimientos</span>
                                </a>
                            </li>
                        @endif
                    </ul>
                </li>
            @endif

            {{-- GUIAS DE REMISIÓN (Avanzados) --}}
            @if (in_array('advanced', $vc_modules) || ($is_superadmin && $vc_company->soap_type_id != '03'))
                <li class="mega">
                    <a href="#dispatch" data-bs-toggle="collapse" data-role="button"
                        aria-expanded="{{ in_array($firstLevel, [
                            'dispatches',
                            'dispatch_carrier',
                            'dispatchers',
                            'drivers',
                            'transports',
                            'origin_addresses',
                        ])
                            ? 'true'
                            : 'false' }}"
                        class="{{ in_array($firstLevel, [
                            'dispatches',
                            'dispatch_carrier',
                            'dispatchers',
                            'drivers',
                            'transports',
                            'origin_addresses',
                        ])
                            ? 'active'
                            : '' }}"
                        data-clicked="{{ in_array($firstLevel, [
                            'dispatches',
                            'dispatch_carrier',
                            'dispatchers',
                            'drivers',
                            'transports',
                            'origin_addresses',
                        ])
                            ? 'true'
                            : 'false' }}">
                        <i class="bi bi-truck"></i>
                        <span class="label">Guías de remisión</span>
                    </a>
                    <ul id="dispatch"
                        class="collapse {{ in_array($firstLevel, [
                            'dispatches',
                            'dispatch_carrier',
                            'dispatchers',
                            'drivers',
                            'transports',
                            'origin_addresses',
                        ])
                            ? 'show'
                            : '' }}">
                        <li>
                            <a class="{{ $firstLevel === 'dispatches' ? 'active' : '' }} nav-link"
                                href="{{ route('tenant.dispatches.index') }}">
                                <span class="label">G.R. Remitente</span>
                            </a>
                        </li>
                        <li>
                            <a class="{{ $firstLevel === 'dispatch_carrier' ? 'active' : '' }} nav-link"
                                href="{{ route('tenant.dispatch_carrier.index') }}">
                                <span class="label">G.R. Transportista</span>
                            </a>
                        </li>
                        <li>
                            <a class="{{ $firstLevel === 'dispatchers' ? 'active' : '' }} nav-link"
                                href="{{ route('tenant.dispatchers.index') }}">
                                <span class="label">Transportistas</span>
                            </a>
                        </li>
                        <li>
                            <a class="{{ $firstLevel === 'drivers' ? 'active' : '' }} nav-link"
                                href="{{ route('tenant.drivers.index') }}">
                                <span class="label">Conductores</span>
                            </a>
                        </li>
                        <li>
                            <a class="{{ $firstLevel === 'transports' ? 'active' : '' }} nav-link"
                                href="{{ route('tenant.transports.index') }}">
                                <span class="label">Vehículos</span>
                            </a>
                        </li>
                        <li>
                            <a class="{{ $firstLevel === 'origin_addresses' ? 'active' : '' }} nav-link"
                                href="{{ route('tenant.origin_addresses.index') }}">
                                <span class="label">Direcciones de Partida</span>
                            </a>
                        </li>
                    </ul>
                </li>
            @endif
            {{-- CERTIFICADOS --}}
            @if (in_array('certificates', $vc_modules) || ($is_superadmin && $vc_company->soap_type_id != '03'))
                <li class="mega">
                    <a href="#certificate" data-bs-toggle="collapse" data-role="button"
                        aria-expanded="{{ in_array($firstLevel, ['certificate']) ? 'true' : 'false' }}"
                        class="{{ in_array($firstLevel, ['certificate']) ? 'active' : '' }}">
                        <i class="bi bi-file-earmark-text"></i>
                        <span class="label">Certificados</span>
                    </a>
                    <ul id="certificate"
                        class="collapse {{ in_array($firstLevel, ['certificate']) ? 'show' : '' }}">
                        {{-- @if (in_array('advanced_retentions', $vc_module_levels) || $is_superadmin) --}}
                        <li>
                            <a class="{{ $firstLevel === 'certificate' && $secondLevel === null ? 'active' : '' }} nav-link"
                                href="{{ route('certificate.index') }}">
                                <span class="label">Listado</span>
                            </a>
                        </li>
                        <li>
                            <a class="{{ $firstLevel === 'certificate' && $secondLevel === 'template' ? 'active' : '' }} nav-link"
                                href="{{ route('certificate.template.index') }}">
                                <span class="label">Plantilla</span>
                            </a>
                        </li>
                        {{-- @endif --}}

                    </ul>
                </li>
            @endif
            {{-- COMPROBANTES AVANZADOS --}}
            @if (in_array('advanced', $vc_modules) || ($is_superadmin && $vc_company->soap_type_id != '03'))
                <li class="mega">
                    <a href="#advanced" data-bs-toggle="collapse" data-role="button"
                        aria-expanded="{{ in_array($firstLevel, ['retentions', 'perceptions', 'order-forms', 'order-delivery']) ? 'true' : 'false' }}"
                        class="{{ in_array($firstLevel, ['retentions', 'perceptions', 'order-forms', 'order-delivery']) ? 'active' : '' }}">
                        <i class="bi bi-file-earmark-text"></i>
                        <span class="label">Comprob. avanzados</span>
                    </a>
                    <ul id="advanced"
                        class="collapse {{ in_array($firstLevel, ['retentions', 'perceptions', 'order-forms']) ? 'show' : '' }}">
                        @if (in_array('advanced_retentions', $vc_module_levels) || $is_superadmin)
                            <li>
                                <a class="{{ $firstLevel === 'retentions' ? 'active' : '' }} nav-link"
                                    href="{{ route('tenant.retentions.index') }}">
                                    <span class="label">Retenciones</span>
                                </a>
                            </li>
                        @endif
                        @if (in_array('advanced_perceptions', $vc_module_levels) || $is_superadmin)
                            <li>
                                <a class="{{ $firstLevel === 'perceptions' ? 'active' : '' }} nav-link"
                                    href="{{ route('tenant.perceptions.index') }}">
                                    <span class="label">Percepciones</span>
                                </a>
                            </li>
                        @endif
                        @if (in_array('advanced_order_forms', $vc_module_levels) || $is_superadmin)
                            <li>
                                <a class="{{ $firstLevel === 'order-forms' ? 'active' : '' }} nav-link"
                                    href="{{ route('tenant.order_forms.index') }}">
                                    <span class="label">Ordenes de pedido</span>
                                </a>
                            </li>
                        @endif

                        @if (in_array('order-delivery', $vc_module_levels) || $is_superadmin)
                            <li>
                                <a class="{{ $firstLevel === 'order-delivery' ? 'active' : '' }} nav-link"
                                    href="{{ route('tenant.order_delivery.index') }}">
                                    <span class="label">Ordenes de entrega</span>
                                </a>
                            </li>
                        @endif
                    </ul>
                </li>
            @endif

            {{-- REPORTES --}}
            @if (in_array('reports', $vc_modules) || $is_superadmin)
                <li>
                    <a href="{{ url('list-reports') }}"
                        class="{{ in_array($firstLevel, [
                            'reports',
                            'purchases',
                            'search',
                            'sales',
                            'customers',
                            'items',
                            'general-items',
                            'consistency-documents',
                            'quotations',
                            'sale-notes',
                            'cash',
                            'commissions',
                            'document-hotels',
                            'validate-documents',
                            'document-detractions',
                            'commercial-analysis',
                            'order-notes-consolidated',
                            'order-notes-general',
                            'sales-consolidated',
                            'user-commissions',
                            'fixed-asset-purchases',
                            'massive-downloads',
                        ])
                            ? 'active'
                            : '' }}">
                        <i class="bi bi-bar-chart-line"></i>
                        <span class="label">Reportes</span>
                    </a>
                </li>
            @endif

            {{-- CONTABILIDAD --}}
            @if (in_array('accounting', $vc_modules) || $is_superadmin)
                <li class="mega">
                    <a href="#accounting" data-bs-toggle="collapse" data-role="button"
                        aria-expanded="{{ $firstLevel === 'account' ? 'true' : 'false' }}"
                        class="{{ $firstLevel === 'account' ? 'active' : '' }}">
                        <i class="bi bi-kanban"></i>
                        <span class="label">Contabilidad</span>
                    </a>
                    <ul id="accounting" class="collapse {{ $firstLevel === 'account' ? 'show' : '' }}">
                        @if (in_array('account_report', $vc_module_levels) || $is_superadmin)
                            <li>
                                <a class="{{ $firstLevel === 'account' && $secondLevel === 'format' ? 'active' : '' }} nav-link"
                                    href="{{ route('tenant.account_format.index') }}">
                                    <span class="label">Libros en Excel</span>
                                </a>
                            </li>
                        @endif
                        <li>
                            <a class="{{ $firstLevel === 'account' && $secondLevel === 'ple' ? 'active' : '' }} nav-link"
                                href="{{ route('tenant.account_ple.index') }}">
                                <span class="label">Libros electrónicos</span>
                            </a>
                        </li>
                        @if (in_array('account_formats', $vc_module_levels) || $is_superadmin)
                            <li>
                                <a class="{{ $firstLevel === 'account' && $secondLevel == '' ? 'active' : '' }} nav-link"
                                    href="{{ route('tenant.account.index') }}">
                                    <span class="label">Sistemas contables</span>
                                </a>
                            </li>
                        @endif
                        @if (in_array('account_summary', $vc_module_levels) || $is_superadmin)
                            <li>
                                <a class="{{ $firstLevel === 'account' && $secondLevel == 'summary-report' ? 'active' : '' }} nav-link"
                                    href="{{ route('tenant.account_summary_report.index') }}">
                                    <span class="label">Resumen de venta</span>
                                </a>
                            </li>
                            <li>
                                <a class="{{ $firstLevel === 'account' && $secondLevel == 'tax_return' ? 'active' : '' }} nav-link"
                                    href="{{ route('tenant.tax_return.index') }}">
                                    <span class="label">Declaración mensual</span>
                                </a>
                            </li>
                        @endif
                        <li>
                            <a class="{{ $firstLevel === 'account' && $secondLevel == 'ledger_accounts' ? 'active' : '' }} nav-link"
                                href="{{ route('tenant.account_ledger_accounts.index') }}">
                                <span class="label">Plan de cuentas</span>
                            </a>
                        </li>
                        <li>
                            <a class="{{ $firstLevel === 'account' && $secondLevel == 'sub_diaries' && $thridLevel == 'create_automatic' ? 'active' : '' }} nav-link"
                                href="{{ route('tenant.account_sub_diaries.create_automatic') }}">
                                <span class="label">Asientos automáticos</span>
                            </a>
                        </li>
                        <li>
                            <a class="{{ $firstLevel === 'account' && $secondLevel == 'sub_diaries' && $thridLevel == 'create' ? 'active' : '' }} nav-link"
                                href="{{ route('tenant.account_sub_diaries.create_with_selection') }}">
                                <span class="label">Libro diario</span>
                            </a>
                        </li>
                    </ul>
                </li>

                <li class="mega">
                    <a href="#sire" data-bs-toggle="collapse" data-role="button"
                        aria-expanded="{{ $firstLevel === 'sire' ? 'true' : 'false' }}"
                        class="{{ $firstLevel === 'sire' ? 'active' : '' }}">
                        <i class="bi bi-kanban-fill"></i>
                        <span class="label">SIRE</span>
                    </a>
                    <ul class="collapse {{ $firstLevel === 'sire' ? 'show' : '' }}" id="sire">
                        <li>
                            <a class="{{ $firstLevel === 'sire' && $secondLevel === 'appendix' ? 'active' : '' }} nav-link"
                                href="{{ url('/sire/appendix') }}">
                                <span class="label">Anexos</span>
                            </a>
                        </li>
                        <li>
                            <a class="{{ $firstLevel === 'sire' && $secondLevel === 'purchase' ? 'active' : '' }} nav-link"
                                href="{{ route('tenant.sire.purchase') }}">
                                <span class="label">Compras</span>
                            </a>
                        </li>
                        <li>
                            <a class="{{ $firstLevel === 'sire' && $secondLevel === 'sale' ? 'active' : '' }} nav-link"
                                href="{{ route('tenant.sire.sale') }}">
                                <span class="label">Ventas</span>
                            </a>
                        </li>
                    </ul>
                </li>
            @endif

            {{-- FINANZAS --}}
            @if (in_array('finance', $vc_modules) || $is_superadmin)
                <li class="mega">
                    <a href="#finance" data-bs-toggle="collapse"
                        aria-expanded="{{ $firstLevel === 'finances' &&
                        in_array($secondLevel, [
                            'global-payments',
                            'balance',
                            'payment-method-types',
                            'unpaid',
                            'to-pay',
                            'income',
                            'movements',
                        ])
                            ? 'true'
                            : 'false' }}"
                        class="{{ $firstLevel === 'finances' &&
                        in_array($secondLevel, [
                            'global-payments',
                            'balance',
                            'payment-method-types',
                            'unpaid',
                            'to-pay',
                            'income',
                            'movements',
                        ])
                            ? 'active'
                            : '' }}">
                        <i class="bi bi-currency-dollar"></i>
                        <span class="label">Finanzas</span>
                    </a>
                    <ul class="collapse {{ $firstLevel === 'finances' &&
                    in_array($secondLevel, [
                        'global-payments',
                        'balance',
                        'payment-method-types',
                        'unpaid',
                        'to-pay',
                        'income',
                        'movements',
                    ])
                        ? 'show'
                        : '' }}"
                        id="finance">
                        @if (in_array('finances_movements', $vc_module_levels) || $is_superadmin)
                            <li
                                class="{{ $firstLevel === 'finances' && $secondLevel == 'movements' ? 'active' : '' }}">
                                <a class="nav-link" href="{{ route('tenant.finances.movements.index') }}">
                                    <span class="label">Movimientos</span>
                                </a>
                            </li>
                        @endif
                        @if (in_array('finances_incomes', $vc_module_levels) || $is_superadmin)
                            <li class="{{ $firstLevel === 'finances' && $secondLevel == 'income' ? 'active' : '' }}">
                                <a class="nav-link" href="{{ route('tenant.finances.income.index') }}">
                                    <span class="label">Ingresos diversos</span>
                                </a>
                            </li>
                        @endif
                        @if (in_array('finances_unpaid', $vc_module_levels) || $is_superadmin)
                            <li class="{{ $firstLevel === 'finances' && $secondLevel == 'unpaid' ? 'active' : '' }}">
                                <a class="nav-link" href="{{ route('tenant.finances.unpaid.index') }}">
                                    Cuentas por cobrar
                                </a>
                            </li>
                            <li class="{{ $firstLevel === 'bill-of-exchange' ? 'active' : '' }}">
                                <a class="nav-link" href="{{ route('tenant.bill_of_exchange.index') }}">
                                    <span class="label">Letras por cobrar</span>
                                </a>
                            </li>
                        @endif
                        @if (in_array('finances_to_pay', $vc_module_levels) || $is_superadmin)
                            <li class="{{ $firstLevel === 'finances' && $secondLevel == 'to-pay' ? 'active' : '' }}">
                                <a class="nav-link" href="{{ route('tenant.finances.to_pay.index') }}">
                                    Cuentas por pagar
                                </a>
                            </li>
                            <li class="{{ $firstLevel === 'bill-of-exchange-pay' ? 'active' : '' }}">
                                <a class="nav-link" href="{{ route('tenant.bill_of_exchange_pay.index') }}">
                                    <span class="label">Letras por pagar</span>
                                </a>
                            </li>
                        @endif
                        @if (in_array('finances_payments', $vc_module_levels) || $is_superadmin)
                            <li
                                class="{{ $firstLevel === 'finances' && $secondLevel == 'global-payments' ? 'active' : '' }}">
                                <a class="nav-link" href="{{ route('tenant.finances.global_payments.index') }}">
                                    Reporte de pagos
                                </a>
                            </li>
                        @endif
                        @if (in_array('finances_balance', $vc_module_levels) || $is_superadmin)
                            <li
                                class="{{ $firstLevel === 'finances' && $secondLevel == 'balance' ? 'active' : '' }}">
                                <a class="nav-link" href="{{ route('tenant.finances.balance.index') }}">
                                    Balance
                                </a>
                            </li>
                        @endif
                        @if (in_array('finances_payment_method_types', $vc_module_levels) || $is_superadmin)
                            <li
                                class="{{ $firstLevel === 'finances' && $secondLevel == 'payment-method-types' ? 'active' : '' }}">
                                <a class="nav-link"
                                    href="{{ route('tenant.finances.payment_method_types.index') }}">
                                    Ingresos y egresos
                                </a>
                            </li>
                        @endif
                    </ul>
                </li>
            @endif

            {{-- CONFIGURACIÓN (list-settings) --}}
            {{-- @if (in_array('woocommerce', $vc_modules) || $is_superadmin)
                <li>
                    <a href="{{ url('list-settings') }}"
                        data-active="{{ in_array($firstLevel, [
                            'list-platforms',
                            'list-cards',
                            'list-currencies',
                            'list-bank-accounts',
                            'list-banks',
                            'list-attributes',
                            'list-detractions',
                            'list-units',
                            'list-payment-methods',
                            'list-incomes',
                            'list-payments',
                            'company_accounts',
                            'list-vouchers-type',
                            'companies',
                            'advanced',
                            'tasks',
                            'inventories',
                            'bussiness_turns',
                            'offline-configurations',
                            'series-configurations',
                            'configurations',
                            'login-page',
                            'list-settings',
                        ])
                            ? 'true'
                            : 'false' }}"
                        aria-expanded="{{ in_array($firstLevel, [
                            'list-platforms',
                            'list-cards',
                            'list-currencies',
                            'list-bank-accounts',
                            'list-banks',
                            'list-attributes',
                            'list-detractions',
                            'list-units',
                            'list-payment-methods',
                            'list-incomes',
                            'list-payments',
                            'company_accounts',
                            'list-vouchers-type',
                            'companies',
                            'advanced',
                            'tasks',
                            'inventories',
                            'bussiness_turns',
                            'offline-configurations',
                            'series-configurations',
                            'configurations',
                            'login-page',
                            'list-settings',
                        ])
                            ? 'true'
                            : 'false' }}"
                        class="{{ in_array($firstLevel, [
                            'list-platforms',
                            'list-cards',
                            'list-currencies',
                            'list-bank-accounts',
                            'list-banks',
                            'list-attributes',
                            'list-detractions',
                            'list-units',
                            'list-payment-methods',
                            'list-incomes',
                            'list-payments',
                            'company_accounts',
                            'list-vouchers-type',
                            'companies',
                            'advanced',
                            'tasks',
                            'inventories',
                            'bussiness_turns',
                            'offline-configurations',
                            'series-configurations',
                            'configurations',
                            'login-page',
                            'list-settings',
                        ])
                            ? 'active'
                            : '' }}">
                        <i class="bi bi-tools"></i>
                        <span class="label">Configuración</span>
                    </a>
                </li>
            @endif --}}
            @if (in_array('configuration', $vc_modules) || $is_superadmin)
                <li
                    class="{{ in_array($firstLevel, [
                        'list-platforms',
                        'list-cards',
                        'list-currencies',
                        'list-bank-accounts',
                        'list-banks',
                        'list-attributes',
                        'list-detractions',
                        'list-units',
                        'list-payment-methods',
                        'list-incomes',
                        'list-payments',
                        'company_accounts',
                        'list-vouchers-type',
                        'companies',
                        'advanced',
                        'tasks',
                        'inventories',
                        'bussiness_turns',
                        'offline-configurations',
                        'series-configurations',
                        'configurations',
                        'login-page',
                        'list-settings',
                    ])
                        ? 'active'
                        : '' }}">
                    <a class="nav-link" href="{{ url('list-settings') }}">
                        <i class="bi bi-gear"></i>
                        <span class="label">Configuración</span>
                    </a>
                    @if ($is_admin || $is_superadmin)
                        <a class="nav-link" href="{{ route('tenant.companies.download_all_info') }}">
                            <i class="bi bi-database"></i>
                            <span class="label">Copia de seguridad</span>
                        </a>
                    @endif
                </li>
            @endif

            {{-- HOTELES --}}
            @if (in_array('hotels', $vc_modules) || $is_superadmin)
                <li class="mega">
                    <a href="#hotels" data-bs-toggle="collapse" data-role="button"
                        aria-expanded="{{ $firstLevel === 'hotels' ? 'true' : 'false' }}"
                        class="{{ $firstLevel === 'hotels' ? 'active' : '' }}">
                        <i class="bi bi-building"></i>
                        <span class="label">Hoteles</span>
                    </a>
                    <ul id="hotels" class="collapse {{ $firstLevel === 'hotels' ? 'show' : '' }}">
                        @if (!$configuration->hotel_reservation_type_2)
                            <li>
                                <a class="{{ $firstLevel === 'hotels' && $secondLevel === 'reservations' ? 'active' : '' }} nav-link"
                                    href="{{ url('hotels/reservations') }}">Reservaciones</a>
                            </li>
                        @endif
                        @if (in_array('hotels_reception', $vc_module_levels) || $is_superadmin)
                            <li>
                                <a class="{{ $firstLevel === 'hotels' && $secondLevel === 'reception' ? 'active' : '' }} nav-link"
                                    href="{{ url('hotels/reception') }}">Recepción</a>
                            </li>
                        @endif
                        @if (in_array('hotels_rates', $vc_module_levels) || $is_superadmin)
                            <li>
                                <a class="{{ $firstLevel === 'hotels' && $secondLevel === 'rates' ? 'active' : '' }} nav-link"
                                    href="{{ url('hotels/rates') }}">Tarifas</a>
                            </li>
                        @endif
                        @if (in_array('hotels_floors', $vc_module_levels) || $is_superadmin)
                            <li>
                                <a class="{{ $firstLevel === 'hotels' && $secondLevel === 'floors' ? 'active' : '' }} nav-link"
                                    href="{{ url('hotels/floors') }}">Pisos</a>
                            </li>
                        @endif
                        @if (in_array('hotels_cats', $vc_module_levels) || $is_superadmin)
                            <li>
                                <a class="{{ $firstLevel === 'hotels' && $secondLevel === 'categories' ? 'active' : '' }} nav-link"
                                    href="{{ url('hotels/categories') }}">Categorías</a>
                            </li>
                        @endif
                        @if (in_array('hotels_rooms', $vc_module_levels) || $is_superadmin)
                            <li>
                                <a class="{{ $firstLevel === 'hotels' && $secondLevel === 'rooms' ? 'active' : '' }}"
                                    href="{{ url('hotels/rooms') }}">Habitaciones</a>
                            </li>
                        @endif
                    </ul>
                </li>
            @endif

            {{-- TRÁMITE DOCUMENTARIO --}}
            @if (in_array('documentary-procedure', $vc_modules) || $is_superadmin)
                <li class="mega">
                    <a href="#documentary-procedure" data-bs-toggle="collapse" data-role="button"
                        aria-expanded="{{ $firstLevel === 'documentary-procedure' ? 'true' : 'false' }}"
                        class="{{ $firstLevel === 'documentary-procedure' ? 'active' : '' }}">
                        <i class="bi bi-building-check"></i>
                        <span class="label">Trámite documentario</span>
                    </a>
                    <ul id="documentary-procedure"
                        class="collapse {{ $firstLevel === 'documentary-procedure' ? 'show' : '' }}">
                        @if (in_array('documentary_offices', $vc_module_levels) || $is_superadmin)
                            <li>
                                <a class="{{ $firstLevel === 'documentary-procedure' && $secondLevel === 'offices' ? 'active' : '' }} nav-link"
                                    href="{{ route('documentary.offices') }}">
                                    Listado de Etapas
                                </a>
                            </li>
                        @endif
                        @if (in_array('documentary_requirements', $vc_module_levels) || $is_superadmin)
                            <li>
                                <a class="{{ $firstLevel === 'documentary-procedure' && $secondLevel === 'requirements' ? 'active' : '' }} nav-link"
                                    href="{{ route('documentary.requirements') }}">
                                    Listado de requerimientos
                                </a>
                            </li>
                        @endif
                        @if (in_array('documentary_process', $vc_module_levels) || $is_superadmin)
                            <li>
                                <a class="{{ $firstLevel === 'documentary-procedure' && $secondLevel === 'processes' ? 'active' : '' }} nav-link"
                                    href="{{ route('documentary.processes') }}">
                                    Tipos de tramites
                                </a>
                            </li>
                        @endif
                        @if (in_array('documentary_files', $vc_module_levels) || $is_superadmin)
                            <li>
                                <a class="{{ $firstLevel === 'documentary-procedure' && $secondLevel === 'files' ? 'active' : '' }} nav-link"
                                    href="{{ route('documentary.files') }}">
                                    Listado de tramites
                                </a>
                            </li>
                        @endif
                    </ul>
                </li>
            @endif

            {{-- DIGEMID / FARMACIA --}}
            @if (in_array('digemid', $vc_modules) || ($is_superadmin && $configuration->isPharmacy()))
                <li class="mega">
                    <a data-bs-toggle="collapse" data-role="button"
                        aria-expanded="{{ $firstLevel === 'digemid' ? 'true' : 'false' }}"
                        class="{{ $firstLevel === 'digemid' ? 'active' : '' }}" href="#digemid">
                        <i class="bi bi-capsule"></i>
                        <span class="label">Farmacia</span>
                    </a>
                    <ul id="digemid" class="collapse {{ $firstLevel === 'digemid' ? 'show' : '' }}">
                        @if (in_array('digemid', $vc_module_levels) || $is_superadmin)
                            <li>
                                <a class="{{ $firstLevel === 'digemid' ? 'active' : '' }} nav-link"
                                    href="{{ route('tenant.digemid.index') }}">
                                    Productos
                                </a>
                            </li>
                        @endif
                    </ul>
                </li>
            @endif

            {{-- FULL SUSCRIPTION (SAAS) --}}
            @if (in_array('full_suscription_app', $vc_modules) || $is_superadmin)
                <li class="mega">
                    <a data-bs-toggle="collapse" data-role="button"
                        aria-expanded="{{ $firstLevel === 'full_suscription' ? 'true' : 'false' }}"
                        class="{{ $firstLevel === 'full_suscription' ? 'active' : '' }}" href="#saas">
                        <i class="bi bi-calendar-check"></i>
                        <span class="label">Servicios SAAS</span>
                    </a>
                    <ul id="saas" class="collapse {{ $firstLevel === 'full_suscription' ? 'show' : '' }}">
                        <li>
                            <a class="{{ $firstLevel === 'full_suscription' && $secondLevel === 'client' ? 'active' : '' }} nav-link"
                                href="{{ route('tenant.fullsuscription.client.index') }}">
                                Clientes
                            </a>
                        </li>
                        <li>
                            <a class="{{ $firstLevel === 'full_suscription' && $secondLevel === 'plans' ? 'active' : '' }} nav-link"
                                href="{{ route('tenant.fullsuscription.plans.index') }}">
                                Planes
                            </a>
                        </li>
                        <li>
                            <a class="{{ $firstLevel === 'full_suscription' && $secondLevel === 'payments' ? 'active' : '' }} nav-link"
                                href="{{ route('tenant.fullsuscription.payments.index') }}">
                                Suscripciones
                            </a>
                        </li>
                        <li>
                            <a class="{{ $firstLevel === 'full_suscription' && $secondLevel === 'payment_receipt' ? 'active' : '' }} nav-link"
                                href="{{ route('tenant.fullsuscription.payment_receipt.index') }}">
                                Recibos de pago
                            </a>
                        </li>
                    </ul>
                </li>
            @endif

            {{-- SUSCRIPCION ESCOLAR --}}
            @if (in_array('suscription_app', $vc_modules) || $is_superadmin)
                <li class="mega">
                    <a data-bs-toggle="collapse" data-role="button"
                        aria-expanded="{{ $firstLevel === 'suscription' ? 'true' : 'false' }}"
                        class="{{ $firstLevel === 'suscription' ? 'active' : '' }}" href="#suscription_app">
                        <i class="bi bi-calendar-check-fill"></i>
                        <span class="label">Suscripción Escolar</span>
                    </a>
                    <ul id="suscription_app" class="collapse {{ $firstLevel === 'suscription' ? 'show' : '' }}">
                        <li>
                            <a class="{{ $firstLevel === 'suscription' && $secondLevel === 'client' && $thridLevel !== 'childrens' ? 'active' : '' }} nav-link"
                                href="{{ route('tenant.suscription.client.index') }}">
                                @if (isset($vc_suscription_name) && isset($vc_suscription_name->parents))
                                    {{ $vc_suscription_name->parents }}
                                @else
                                    Padres
                                @endif
                            </a>
                        </li>
                        <li>
                            <a class="{{ $firstLevel === 'suscription' && $secondLevel === 'client' && $thridLevel === 'childrens' ? 'active' : '' }} nav-link"
                                href="{{ route('tenant.suscription.client_children.index') }}">
                                @if (isset($vc_suscription_name) && isset($vc_suscription_name->children))
                                    {{ $vc_suscription_name->children }}
                                @else
                                    Hijos
                                @endif
                            </a>
                        </li>
                        <li>
                            <a class="{{ $firstLevel === 'documents' && $secondLevel === 'create' ? 'active' : '' }} nav-link"
                                href="{{ route('tenant.documents.create') }}">
                                Nuevo
                            </a>
                        </li>
                        <li>
                            <a class="{{ $firstLevel === 'suscription' && $secondLevel === 'plans' ? 'active' : '' }} nav-link"
                                href="{{ route('tenant.suscription.plans.index') }}">
                                Planes
                            </a>
                        </li>
                        <li>
                            <a class="{{ $firstLevel === 'suscription' && $secondLevel === 'payments' ? 'active' : '' }} nav-link"
                                href="{{ route('tenant.suscription.payments.index') }}">
                                Matrículas
                            </a>
                        </li>
                        <li>
                            <a class="{{ $firstLevel === 'suscription' && $secondLevel === 'payment_receipt' ? 'active' : '' }} nav-link"
                                href="{{ route('tenant.suscription.payment_receipt.index') }}">
                                Recibos de pago
                            </a>
                        </li>
                        <li>
                            <a class="{{ $firstLevel === 'suscription' && $secondLevel === 'grade_section' ? 'active' : '' }} nav-link"
                                href="{{ route('tenant.suscription.grade_section.index') }}">
                                @php
                                    $name = null;
                                    if (isset($vc_suscription_name) && isset($vc_suscription_name->grades)) {
                                        $name = $vc_suscription_name->grades;
                                    }
                                    if (isset($vc_suscription_name) && isset($vc_suscription_name->sections)) {
                                        $name = $name
                                            ? $name . ' y ' . $vc_suscription_name->sections
                                            : $vc_suscription_name->sections;
                                    }
                                @endphp
                                @if ($name)
                                    {{ $name }}
                                @else
                                    Grados y Secciones
                                @endif
                            </a>
                        </li>
                    </ul>
                </li>
            @endif

            {{-- PREPARACIÓN --}}

            {{-- PRODUCCIÓN (APP) --}}
            @if (in_array('production_app', $vc_modules) || $is_superadmin)
                @if ($configuration->production_2)
                    <li class="mega">
                        <a data-bs-toggle="collapse" data-role="button"
                            aria-expanded="{{ in_array($firstLevel, ['preparation']) ? 'true' : 'false' }}"
                            class="{{ in_array($firstLevel, ['preparation']) ? 'active' : '' }}"
                            href="#preparation">
                            <i class="bi bi-building-gear"></i>
                            <span class="label">Producción</span>
                        </a>
                        <ul id="preparation"
                            class="collapse {{ in_array($firstLevel, ['preparation']) ? 'show' : '' }}">
                            <li>
                                <a class="{{ $firstLevel === 'preparation' ? 'active' : '' }} nav-link"
                                    href="{{ route('tenant.preparation.index') }}">
                                    Insumos
                                </a>
                            </li>
                            <li>
                                <a class="{{ $firstLevel === 'input-movements' ? 'active' : '' }} nav-link"
                                    href="{{ route('tenant.register-inputs-movements.index') }}">
                                    Movimientos de insumos
                                </a>
                            </li>
                            <li>
                                <a class="{{ $firstLevel === 'order-transformation' ? 'active' : '' }} nav-link"
                                    href="{{ route('tenant.order-transformation.index') }}">
                                    Transformación de pedidos
                                </a>
                            </li>

                        </ul>
                    </li>
                @else
                    <li class="mega">
                        <a data-bs-toggle="collapse" data-role="button"
                            aria-expanded="{{ in_array($firstLevel, [
                                'production',
                                'mill-production',
                                'machine-type-production',
                                'machine-production',
                                'packaging',
                                'workers',
                            ])
                                ? 'true'
                                : 'false' }}"
                            class="{{ in_array($firstLevel, [
                                'production',
                                'mill-production',
                                'machine-type-production',
                                'machine-production',
                                'packaging',
                                'workers',
                            ])
                                ? 'active'
                                : '' }}"
                            href="#production">
                            <i class="bi bi-building-gear"></i>
                            <span class="label">Producción</span>
                        </a>
                        <ul id="production"
                            class="collapse {{ in_array($firstLevel, [
                                'production',
                                'mill-production',
                                'machine-type-production',
                                'machine-production',
                                'packaging',
                                'workers',
                            ])
                                ? 'show'
                                : '' }}">
                            <li>
                                <a class="{{ $firstLevel === 'production' ? 'active' : '' }} nav-link"
                                    href="{{ route('tenant.production.index') }}">
                                    Productos Fabricados
                                </a>
                            </li>
                            <li>
                                <a class="{{ $firstLevel === 'mill-production' ? 'active' : '' }} nav-link"
                                    href="{{ route('tenant.mill_production.index') }}">
                                    Ingresos de Insumos
                                </a>
                            </li>
                            <li>
                                <a class="{{ $firstLevel === 'machine-type-production' ? 'active' : '' }} nav-link"
                                    href="{{ route('tenant.machine_type_production.index') }}">
                                    Tipos de maquinarias
                                </a>
                            </li>
                            <li>
                                <a class="{{ $firstLevel === 'machine-production' ? 'active' : '' }} nav-link"
                                    href="{{ route('tenant.machine_production.index') }}">
                                    Maquinarias
                                </a>
                            </li>
                            <li>
                                <a class="{{ $firstLevel === 'packaging' ? 'active' : '' }} nav-link"
                                    href="{{ route('tenant.packaging.index') }}">
                                    Zona de embalaje
                                </a>
                            </li>
                            <li>
                                <a class="{{ $firstLevel === 'workers' ? 'active' : '' }} nav-link"
                                    href="{{ route('tenant.workers.index') }}">
                                    Empleados
                                </a>
                            </li>
                        </ul>
                    </li>
                @endif
            @endif

            {{-- APLICATIVO MÓVIL --}}
            @if (in_array('app_2_generator', $vc_modules) || $is_superadmin)
                <li>
                    <a class="{{ $firstLevel === 'live-app' && $secondLevel === 'configuration' ? 'active' : '' }} nav-link"
                        href="{{ route('tenant.liveapp.configuration') }}">
                        <i class="bi bi-phone"></i>
                        <span class="label">Aplicativo móvil</span>
                    </a>
                </li>
            @endif

            {{-- GENERADOR LINK DE PAGO --}}
            @if (in_array('generate_link_app', $vc_modules) || $is_superadmin)
                <li>
                    <a class="{{ $firstLevel === 'live-app' && $secondLevel === 'payment' ? 'active' : '' }} nav-link"
                        href="{{ route('tenant.payment.generate.index') }}">
                        <i class="bi bi-link-45deg"></i>
                        <span class="label">Generador link de pago</span>
                    </a>
                </li>
            @endif

            {{-- APPS (extra) --}}
            @if (in_array('apps', $vc_modules) || $is_superadmin)
                <li>
                    <a class="{{ $firstLevel === 'list-extras' ? 'active' : '' }} nav-link"
                        href="{{ url('list-extras') }}">
                        <i class="bi bi-puzzle-fill"></i>
                        <span class="label">Apps</span>
                    </a>
                </li>
            @endif

            {{-- TIPO DE CAMBIO --}}
            @if (in_array('exchange_currency', $vc_modules) || $is_superadmin)
                <li>
                    <a href="{{ route('tenant.exchange_currency.index') }}"
                        class="{{ $firstLevel === 'exchange_currency' ? 'active' : '' }} nav-link">
                        <i class="bi bi-cash-coin"></i>
                        <span class="label">Tipo de cambio</span>
                    </a>
                </li>
            @endif

            {{-- MIS PAGOS --}}
            @if (in_array('payment_list', $vc_modules) || $is_superadmin)
                <li class="mega">
                    <a href="{{ route('tenant.payment.index') }}"
                        class="{{ $firstLevel === 'cuenta' && $secondLevel === 'payment_index' ? 'active' : '' }} nav-link">
                        <i class="bi bi-wallet-fill"></i>
                        <span class="label">Mis pagos</span>
                    </a>
                </li>
            @endif

        </ul>
    @else
        <ul id="menu" class="menu">
            @if (in_array('documents', $vc_modules) || $is_superadmin)
                <li class="mega">
                    <a href="#ventas" data-bs-toggle="collapse" data-role="button"
                        aria-expanded="{{ in_array($firstLevel, [
                            'documents',
                            'summaries',
                            'voided',
                            'quotations',
                            'sale-notes',
                            'contingencies',
                            'incentives',
                            'order-notes',
                            'sale-opportunities',
                            'contracts',
                            'production-orders',
                            'technical-services',
                            'user-commissions',
                            'regularize-shipping',
                            'documents-recurrence',
                            'seller',
                        ])
                            ? 'true'
                            : 'false' }}"
                        class="{{ in_array($firstLevel, [
                            'documents',
                            'summaries',
                            'voided',
                            'quotations',
                            'sale-notes',
                            'contingencies',
                            'incentives',
                            'order-notes',
                            'sale-opportunities',
                            'contracts',
                            'production-orders',
                            'technical-services',
                            'user-commissions',
                            'regularize-shipping',
                            'documents-recurrence',
                            'seller',
                        ])
                            ? 'active'
                            : '' }}"
                        data-clicked="{{ in_array($firstLevel, [
                            'documents',
                            'summaries',
                            'voided',
                            'quotations',
                            'sale-notes',
                            'contingencies',
                            'incentives',
                            'order-notes',
                            'sale-opportunities',
                            'contracts',
                            'production-orders',
                            'technical-services',
                            'user-commissions',
                            'regularize-shipping',
                            'documents-recurrence',
                            'seller',
                        ])
                            ? 'true'
                            : 'false' }}">
                        <i class="bi bi-receipt-cutoff"></i>
                        <span class="label">Ventas</span>
                    </a>
                    <ul id="ventas" class="collapse show">
                        {{-- NUEVO COMPROBANTE --}}
                        @if (auth()->user()->type != 'integrator' && $vc_company->soap_type_id != '03')
                            @if (in_array('documents', $vc_modules) || $is_superadmin)
                                @if (in_array('new_document', $vc_module_levels) || $is_superadmin)
                                    <li>
                                        <a class="{{ $firstLevel === 'documents' && $secondLevel === 'create' ? 'active' : '' }} nav-link"
                                            href="{{ route('tenant.documents.create') }}">Nuevo comprobante</a>
                                    </li>
                                @endif
                            @endif
                        @endif
                        {{-- LISTADO DE COMPROBANTES --}}
                        @if (in_array('documents', $vc_modules) || ($is_superadmin && $vc_company->soap_type_id != '03'))
                            @if (in_array('list_document', $vc_module_levels) || $is_superadmin)
                                <li>
                                    <a class="{{ $firstLevel === 'documents' && !in_array($secondLevel, ['create', 'not-sent', 'regularize-shipping']) ? 'active' : '' }} nav-link"
                                        href="{{ route('tenant.documents.index') }}">
                                        Listado de comprobantes
                                    </a>
                                </li>
                            @endif
                        @endif







                    </ul>
                </li>
            @endif




            {{-- PRODUCTOS Y SERVICIOS --}}
            @if (in_array('items', $vc_modules) || $is_superadmin)
                <li class="mega">
                    <a href="#items" data-bs-toggle="collapse" data-role="button"
                        aria-expanded="{{ in_array($firstLevel, [
                            'items',
                            'services',
                            'categories',
                            'brands',
                            'item-lots',
                            'item-sets',
                            'item-lots-group',
                            'coupons',
                            'discount-types',
                            'item-sizes',
                        ])
                            ? 'true'
                            : 'false' }}"
                        class="{{ in_array($firstLevel, [
                            'items',
                            'services',
                            'categories',
                            'brands',
                            'item-lots',
                            'item-sets',
                            'item-lots-group',
                            'coupons',
                            'discount-types',
                            'item-sizes',
                        ])
                            ? 'active'
                            : '' }}"
                        data-clicked="{{ in_array($firstLevel, [
                            'items',
                            'services',
                            'categories',
                            'brands',
                            'item-lots',
                            'item-sets',
                            'item-lots-group',
                            'coupons',
                            'discount-types',
                            'item-sizes',
                        ])
                            ? 'true'
                            : 'false' }}">
                        <i class="bi bi-box-seam"></i>
                        <span class="label">Productos y servicios</span>
                    </a>
                    <ul id="items" class="collapse show">
                        @if (in_array('items', $vc_module_levels) || $is_superadmin)
                            <li class="{{ $firstLevel === 'items' ? 'active' : '' }}">
                                <a class="nav-link" href="{{ route('tenant.items.index') }}">
                                    <span class="label">Productos</span>
                                </a>
                            </li>
                        @endif
                        @if (in_array('items_packs', $vc_module_levels) || $is_superadmin)
                            <li class="{{ $firstLevel === 'item-sets' ? 'active' : '' }}">
                                <a class="nav-link" href="{{ route('tenant.item_sets.index') }}">
                                    <span class="label">Packs y promociones</span>
                                </a>
                            </li>
                        @endif
                        @if (in_array('items_services', $vc_module_levels) || $is_superadmin)
                            <li class="{{ $firstLevel === 'services' ? 'active' : '' }}">
                                <a class="nav-link" href="{{ route('tenant.services') }}">
                                    <span class="label">Servicios</span>
                                </a>
                            </li>
                        @endif
                        @if (in_array('items_categories', $vc_module_levels) || $is_superadmin)
                            <li class="{{ $firstLevel === 'categories' ? 'active' : '' }}">
                                <a class="nav-link" href="{{ route('tenant.categories.index') }}">
                                    <span class="label">Categorías</span>
                                </a>
                            </li>
                        @endif
                        @if (in_array('cupones', $vc_module_levels) || $is_superadmin)
                            <li class="{{ $firstLevel === 'coupons' ? 'active' : '' }}">
                                <a class="nav-link" href="{{ route('tenant.coupons.index') }}">
                                    <span class="label">Cupones</span>
                                </a>
                            </li>
                        @endif
                        @if (in_array('items_brands', $vc_module_levels) || $is_superadmin)
                            <li class="{{ $firstLevel === 'brands' ? 'active' : '' }}">
                                <a class="nav-link" href="{{ route('tenant.brands.index') }}">
                                    <span class="label">Marcas</span>
                                </a>
                            </li>
                        @endif
                        @if (in_array('items_lots', $vc_module_levels) || $is_superadmin)
                            <li class="{{ $firstLevel === 'item-lots' ? 'active' : '' }}">
                                <a class="nav-link" href="{{ route('tenant.item-lots.index') }}">
                                    <span class="label">Series</span>
                                </a>
                            </li>
                        @endif
                        @if (in_array('item_lots_group', $vc_module_levels) || $is_superadmin)
                            <li class="{{ $firstLevel === 'item-lots-group' ? 'active' : '' }}">
                                <a class="nav-link" href="{{ route('tenant.item-lots-group.index') }}">
                                    <span class="label">Lotes</span>
                                </a>
                            </li>
                        @endif

                        @if ($configuration->plate_number_config)
                            <li class="{{ $firstLevel === 'plate-numbers' ? 'active' : '' }}">
                                <a class="nav-link" href="{{ route('tenant.plate_numbers.index') }}">
                                    <span class="label">Placas</span>
                                </a>
                            </li>
                            <li class="{{ $firstLevel === 'quotation-technicians' ? 'active' : '' }}">
                                <a class="nav-link" href="{{ route('tenant.quotations_technicians.index') }}">
                                    <span class="label">Técnicos</span>
                                </a>
                            </li>
                        @endif
                        @php
                            $isClothesShoes = \Modules\BusinessTurn\Models\BusinessTurn::isClothesShoes();
                        @endphp
                        @if ($isClothesShoes)
                            <li class="{{ $firstLevel === 'item-sizes' ? 'active' : '' }}">
                                <a class="nav-link" href="{{ route('tenant.item-sizes.index') }}">
                                    <span class="label">Tallas</span>
                                </a>
                            </li>
                        @endif
                    </ul>
                </li>
            @endif

            {{-- CLIENTES --}}
            @if (in_array('persons', $vc_modules) || $is_superadmin)
                <li class="mega">
                    <a href="#customers" data-bs-toggle="collapse" data-role="button"
                        aria-expanded="{{ in_array($firstLevel, ['persons', 'person-types', 'person-packers', 'person-dispatchers', 'massive-message']) ? 'true' : 'false' }}"
                        class="{{ in_array($firstLevel, ['persons', 'person-types', 'person-packers', 'person-dispatchers', 'massive-message']) ? 'active' : '' }}">
                        <i class="bi bi-people"></i>
                        <span class="label">Clientes</span>
                    </a>
                    <ul id="customers" class="collapse show">
                        @if (in_array('clients', $vc_module_levels) || $is_superadmin)
                            <li>
                                <a class="{{ $firstLevel === 'persons' && $secondLevel === 'customers' ? 'active' : '' }} nav-link"
                                    href="{{ route('tenant.persons.index', ['type' => 'customers']) }}">
                                    <span class="label"> Listado de clientes </span>
                                </a>
                            </li>
                        @endif
                        @if ($configuration->dispatchers_packers_document)
                            <li>
                                <a class="{{ $firstLevel === 'person-packers' ? 'active' : '' }} nav-link"
                                    href="{{ route('tenant.person_packers.index') }}">
                                    <span class="label">Empacadores</span>
                                </a>
                            </li>
                            <li>
                                <a class="{{ $firstLevel === 'person-dispatchers' ? 'active' : '' }} nav-link"
                                    href="{{ route('tenant.person_dispatchers.index') }}">
                                    <span class="label">Repartidores</span>
                                </a>
                            </li>
                        @endif
                        @if ($configuration->enabled_sales_agents)
                            <li>
                                <a class="{{ $firstLevel === 'agents' ? 'active' : '' }} nav-link"
                                    href="{{ route('tenant.agents.index') }}">
                                    <span class="label">Agentes de venta</span>
                                </a>
                            </li>
                        @endif
                        @if ($configuration->package_handlers)
                            <li>
                                <a class="{{ $firstLevel === 'persons_drivers' ? 'active' : '' }} nav-link"
                                    href="{{ route('tenant.persons_drivers.index') }}">
                                    <span class="label">Conductores</span>
                                </a>
                            </li>
                        @endif
                        @if (in_array('clients_types', $vc_module_levels) || $is_superadmin)
                            <li>
                                <a class="{{ $firstLevel === 'person-types' ? 'active' : '' }} nav-link"
                                    href="{{ route('tenant.person_types.index') }}">
                                    <span class="label">Tipos de clientes</span>
                                </a>
                            </li>
                        @endif

                    </ul>
                </li>
            @endif





            {{-- USUARIOS y LOCALES --}}
            @if (in_array('establishments', $vc_modules) || $is_superadmin)
                <li class="mega">
                    <a href="#establishments" data-bs-toggle="collapse" data-role="button"
                        aria-expanded="{{ in_array($firstLevel, ['users', 'establishments']) ? 'true' : 'false' }}"
                        class="{{ in_array($firstLevel, ['users', 'establishments']) ? 'active' : '' }}">
                        <i class="bi bi-people-fill"></i>
                        <span class="label">Usuarios y locales</span>
                    </a>
                    <ul id="establishments"
                        class="collapse {{ in_array($firstLevel, ['users', 'establishments']) ? 'show' : '' }}">
                        @if (in_array('users', $vc_module_levels) || $is_superadmin)
                            <li>
                                <a class="{{ $firstLevel === 'users' ? 'active' : '' }} nav-link"
                                    href="{{ route('tenant.users.index') }}">
                                    <span class="label">Usuarios</span>
                                </a>
                            </li>
                        @endif
                        @if (in_array('users_establishments', $vc_module_levels) || $is_superadmin)
                            <li>
                                <a class="{{ $firstLevel === 'establishments' ? 'active' : '' }} nav-link"
                                    href="{{ route('tenant.establishments.index') }}">
                                    <span class="label">Establecimientos</span>
                                </a>
                            </li>
                        @endif
                    </ul>
                </li>
            @endif






            {{-- CONFIGURACIÓN (list-settings) --}}
            @if (in_array('woocommerce', $vc_modules) || $is_superadmin)
                <li>
                    <a href="{{ url('list-settings') }}"
                        data-active="{{ in_array($firstLevel, [
                            'list-platforms',
                            'list-cards',
                            'list-currencies',
                            'list-bank-accounts',
                            'list-banks',
                            'list-attributes',
                            'list-detractions',
                            'list-units',
                            'list-payment-methods',
                            'list-incomes',
                            'list-payments',
                            'company_accounts',
                            'list-vouchers-type',
                            'companies',
                            'advanced',
                            'tasks',
                            'inventories',
                            'bussiness_turns',
                            'offline-configurations',
                            'series-configurations',
                            'configurations',
                            'login-page',
                            'list-settings',
                        ])
                            ? 'true'
                            : 'false' }}"
                        aria-expanded="{{ in_array($firstLevel, [
                            'list-platforms',
                            'list-cards',
                            'list-currencies',
                            'list-bank-accounts',
                            'list-banks',
                            'list-attributes',
                            'list-detractions',
                            'list-units',
                            'list-payment-methods',
                            'list-incomes',
                            'list-payments',
                            'company_accounts',
                            'list-vouchers-type',
                            'companies',
                            'advanced',
                            'tasks',
                            'inventories',
                            'bussiness_turns',
                            'offline-configurations',
                            'series-configurations',
                            'configurations',
                            'login-page',
                            'list-settings',
                        ])
                            ? 'true'
                            : 'false' }}"
                        class="{{ in_array($firstLevel, [
                            'list-platforms',
                            'list-cards',
                            'list-currencies',
                            'list-bank-accounts',
                            'list-banks',
                            'list-attributes',
                            'list-detractions',
                            'list-units',
                            'list-payment-methods',
                            'list-incomes',
                            'list-payments',
                            'company_accounts',
                            'list-vouchers-type',
                            'companies',
                            'advanced',
                            'tasks',
                            'inventories',
                            'bussiness_turns',
                            'offline-configurations',
                            'series-configurations',
                            'configurations',
                            'login-page',
                            'list-settings',
                        ])
                            ? 'active'
                            : '' }}">
                        <i class="bi bi-tools"></i>
                        <span class="label">Configuración</span>
                    </a>
                </li>
            @endif
            @if (in_array('configuration', $vc_modules) || $is_superadmin)
                <li
                    class="{{ in_array($firstLevel, [
                        'list-platforms',
                        'list-cards',
                        'list-currencies',
                        'list-bank-accounts',
                        'list-banks',
                        'list-attributes',
                        'list-detractions',
                        'list-units',
                        'list-payment-methods',
                        'list-incomes',
                        'list-payments',
                        'company_accounts',
                        'list-vouchers-type',
                        'companies',
                        'advanced',
                        'tasks',
                        'inventories',
                        'bussiness_turns',
                        'offline-configurations',
                        'series-configurations',
                        'configurations',
                        'login-page',
                        'list-settings',
                    ])
                        ? 'active'
                        : '' }}">
                    <a class="nav-link" href="{{ url('list-settings') }}">
                        <i class="bi bi-gear"></i>
                        <span class="label">Configuración</span>
                    </a>
                    @if ($is_admin || $is_superadmin)
                        <a class="nav-link" href="{{ route('tenant.companies.download_all_info') }}">
                            <i class="bi bi-database"></i>
                            <span class="label">Copia de seguridad</span>
                        </a>
                    @endif
                </li>
            @endif







        </ul>

    @endif
</div>

<script>
    // Mantener la posición de scroll del menú lateral
    if (typeof localStorage !== 'undefined') {
        if (localStorage.getItem('sidebar-left-position') !== null) {
            var initialPosition = localStorage.getItem('sidebar-left-position');
            var sidebarLeft = document.querySelector('#sidebar-left .nano-content');
            if (sidebarLeft) {
                sidebarLeft.scrollTop = initialPosition;
            }
        }
    }
</script>
