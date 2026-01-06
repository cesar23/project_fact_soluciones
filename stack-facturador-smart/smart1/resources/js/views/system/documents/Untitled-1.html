<template>
    <div>
        <div class="page-header pr-0">
            <h2>
                <a href="/dashboard"><i class="fas fa-tachometer-alt"></i></a>
            </h2>
            <ol class="breadcrumbs">
                <li class="active"><span>Comprobantes</span></li>
                <li>
                    <span class="text-muted">Facturas - Boletas</span>
                </li>
            </ol>
            <div
                class="right-wrapper pull-right"
                v-if="typeUser != 'integrator'"
            >
                <span v-if="import_documents == true">
                    <button
                        type="button"
                        class="btn btn-custom btn-sm mt-2 mr-2"
                        @click.prevent="clickImport()"
                    >
                        <i class="fa fa-upload"></i> Importar Formato 1
                    </button>
                </span>
                <span v-if="import_documents_second == true">
                    <button
                        type="button"
                        class="btn btn-custom btn-sm mt-2 mr-2"
                        @click.prevent="clickImportSecond()"
                    >
                        <i class="fa fa-upload"></i> Importar Formato 2
                    </button>
                </span>
                <span v-if="document_import_excel">
                    <button
                        type="button"
                        class="btn btn-custom btn-sm mt-2 mr-2"
                        @click.prevent="clickImportExcel"
                    >
                        <i class="fa fa-upload"></i> Importar Formato
                    </button>
                </span>
                    <a
                    v-if="!isComercial"
                    :href="`/${resource}/note_other`"
                    class="btn btn-custom btn-sm mt-2 mr-2"
                    ><i class="fa fa-plus-circle"></i> Nueva Nota dcto externo</a
                >
                <a
                    v-if="!isComercial"
                    :href="`/${resource}/create`"
                    class="btn btn-custom btn-sm mt-2 mr-2"
                    ><i class="fa fa-plus-circle"></i> Nuevo</a
                >
                <div class="btn-group flex-wrap" v-if="!isComercial">
                    <button
                        type="button"
                        class="btn btn-custom btn-sm mt-2 mr-2 dropdown-toggle"
                        data-toggle="dropdown"
                        aria-expanded="false"
                    >
                        <i class="fa fa-money-bill-wave-alt"></i> Reporte de
                        Pagos <span class="caret"></span>
                    </button>
                    <!-- validadores apiperu  -->
                    <a
                        href="#"
                        @click.prevent="showDialogApiPeruDevValidate = true"
                        v-if="view_apiperudev_validator_cpe"
                        class="btn btn-custom btn-sm mt-2 mr-2"
                        ><i class="fa fa-check"></i> Validación masiva</a
                    >
                    <a
                        href="#"
                        @click.prevent="showDialogValidate = true"
                        v-if="view_validator_cpe"
                        class="btn btn-custom btn-sm mt-2 mr-2"
                        ><i class="fa fa-file"></i> Validar CPE</a
                    >

                    <div
                        class="dropdown-menu"
                        role="menu"
                        x-placement="bottom-start"
                        style="
                            position: absolute;
                            will-change: transform;
                            top: 0px;
                            left: 0px;
                            transform: translate3d(0px, 42px, 0px);
                        "
                    >
                        <a
                            class="dropdown-item text-1"
                            href="#"
                            @click.prevent="clickReportPayments()"
                            >Generar Reporte</a
                        >
                        <a
                            class="dropdown-item text-1"
                            href="#"
                            @click.prevent="clickDownloadReportPagos()"
                            >Descargar Excel</a
                        >
                    </div>
                </div>
            </div>
        </div>
        <div class="card mb-0">
            <!--
            <div class="data-table-visible-columns">

                <el-dropdown :hide-on-click="false">
                    <el-button type="primary">
                        Mostrar/Ocultar columnas<i class="el-icon-arrow-down el-icon--right"></i>
                    </el-button>
                    <el-dropdown-menu slot="dropdown">
                        <el-dropdown-item v-for="(column, index) in columns"
                                          :key="index">
                            <el-checkbox v-model="column.visible">{{ column.title }}</el-checkbox>
                        </el-dropdown-item>
                    </el-dropdown-menu>
                </el-dropdown>
            </div>
            -->
            <div class="card-body">
                <data-table
                    ref="dataTable"
                    :to_anulate="to_anulate"
                    :resource="resource"
                    v-loading="loading_data"
                    element-loading-text="Espere..."
                >
                    <el-dropdown :hide-on-click="false" slot="showhide">
                        <el-button type="primary">
                            Mostrar/Ocultar columnas<i
                                class="el-icon-arrow-down el-icon--right"
                            ></i>
                        </el-button>
                        <el-dropdown-menu slot="dropdown">
                            <el-dropdown-item
                                v-for="(column, index) in columns"
                                :key="index"
                            >
                                <el-checkbox
                                    @change="getColumnsToShow(1)"
                                    v-model="column.visible"
                                    >{{ column.title }}
                                </el-checkbox>
                            </el-dropdown-item>
                        </el-dropdown-menu>
                    </el-dropdown>

                    <tr slot="heading">
                        <th>#</th>
                        <th v-if="columns.soap_type.visible">SOAP</th>
                        <th class="text-center" style="min-width: 95px">
                            Emisión
                        </th>
                        <th
                            v-if="columns.date_payment.visible"
                            class="text-center"
                            style="min-width: 95px"
                        >
                            Fecha de pago
                        </th>
                        <th
                            class="text-center"
                            v-if="columns.date_of_due.visible"
                        >
                            Fecha Vencimiento
                        </th>
                        <th v-if="configuration.multi_companies">Empresa</th>
                        <th>Cliente</th>
                        <th>Número</th>
                        <th v-if="columns.document_type_id.visible">
                            Comprobante
                        </th>
                        <th v-if="columns.notes.visible">Notas C/D</th>
                        <th v-if="columns.dispatch.visible">
                            Guía de Remisión
                        </th>
                        <th v-if="columns.sales_note.visible">Nota de venta</th>
                        <th v-if="columns.order_note.visible">Pedidos</th>
                        <th v-if="columns.send_it.visible">Email Enviado</th>
                        <th v-if="columns.sire.visible">SIRE</th>
                        <th>Estado</th>
                        <th v-if="columns.user_name.visible">Usuario</th>
                        <th v-if="columns.exchange_rate_sale.visible">T.C.</th>
                        <th
                            class="text-center"
                            v-if="columns.currency_type_id.visible"
                        >
                            Moneda
                        </th>
                        <th class="text-end" v-if="columns.guides.visible">
                            Guia
                        </th>

                        <th
                            class="text-center"
                            v-if="columns.plate_numbers.visible"
                        >
                            Placa
                        </th>

                        <th
                            class="text-end"
                            v-if="columns.total_exportation.visible"
                        >
                            T.Exportación
                        </th>
                        <th class="text-end" v-if="columns.total_free.visible">
                            T.Gratuita
                        </th>
                        <th
                            class="text-end"
                            v-if="columns.total_unaffected.visible"
                        >
                            T.Inafecta
                        </th>
                        <th
                            class="text-end"
                            v-if="columns.total_exonerated.visible"
                        >
                            T.Exonerado
                        </th>
                        <th
                            class="text-end"
                            v-if="columns.total_charge.visible"
                        >
                            {{ columns.total_charge.title }}
                        </th>
                        <th class="text-end">T.Gravado</th>
                        <th class="text-end">T.Igv</th>
                        <th class="text-end" v-if="columns.total.visible">
                            Total
                        </th>
                        <th class="text-center" v-if="columns.balance.visible">
                            Saldo
                        </th>
                        <th
                            class="text-center"
                            style="min-width: 95px"
                            v-if="columns.purchase_order.visible"
                        >
                            Orden de compra
                        </th>
                        <th class="text-center"></th>
                        <th
                            class="text-end"
                            v-if="typeUser != 'integrator'"
                        ></th>
                        <th v-if="configuration.college">Periodo</th>
                    </tr>
                    <tr
                        slot-scope="{ index, row }"
                        :class="{
                            'text-danger': row.state_type_id === '11',
                            'text-warning': row.state_type_id === '13',
                        }"
                    >
                        <td>{{ index }}</td>
                        <td v-if="columns.soap_type.visible">
                            {{ row.soap_type_description }}
                        </td>
                        <td class="text-center">
                            {{ row.date_of_issue }}
                            <!-- ticket_single_shipment
                            force_send_by_summary -->
                            <template
                                v-if="
                                    row.document_type_id == '03' ||
                                    ((row.document_type_id == '07' ||
                                        row.document_type_id == '08') &&
                                        affectedReceived(
                                            row.affected_documents
                                        ) &&
                                        row.state_type_id == '01')
                                "
                            >
                                <template
                                    v-if="
                                        row.ticket_single_shipment &&
                                        row.force_send_by_summary == false
                                    "
                                >
                                    <br />
                                    <small class="text-primary"
                                        >Env. Individual</small
                                    >
                                </template>
                                <template
                                    v-if="
                                        row.ticket_single_shipment == false &&
                                        row.force_send_by_summary
                                    "
                                >
                                    <br />
                                    <small class="text-success"
                                        >Env. Resumen</small
                                    >
                                </template>
                            </template>
                        </td>
                        <td
                            class="text-center"
                            v-if="columns.date_payment.visible"
                        >
                            {{ row.date_of_payment }}
                        </td>
                        <td
                            class="text-center"
                            :class="{
                                'text-danger':
                                    row.balance > 0 &&
                                    isDateWarning(row.date_of_due),
                            }"
                            v-if="columns.date_of_due.visible"
                        >
                            {{ row.date_of_due }}
                        </td>
                        <td v-if="configuration.multi_companies">
                            <template v-if="row.alter_company && row.alter_company.name && row.alter_company.number">
                                <strong>
                                {{ row.alter_company.name.toUpperCase() }}
                                </strong>
                                <br />
                                <small>
                                    {{ row.alter_company.number }}
                                </small>
                            </template>
                        </td>
                        <td>
                            <a
                                @click="openCustomerDetail(row)"
                                href="javascript:void(0)"
                            >
                                {{ row.customer_name }}
                            </a>
                            <br />

                            <small v-text="row.customer_number"></small>
                        </td>
                        <td>
                            {{ row.number }}
                            <small
                                v-if="row.affected_document"
                                v-text="row.affected_document"
                            ></small>
                        </td>
                        <td v-if="columns.document_type_id.visible">
                            {{ row.document_type_description }}
                        </td>
                        <td v-if="columns.notes.visible">
                            <template v-for="(row, index) in row.notes">
                                <label class="d-block" :key="index"
                                    >{{ row.note_type_description }}:
                                    {{ row.description }}</label
                                >
                            </template>
                        </td>
                        <td v-if="columns.dispatch.visible">
                            <template v-for="(row, index) in row.dispatches">
                                <label class="d-block" :key="index">{{
                                    row.description
                                }}</label>
                            </template>
                        </td>
                        <td v-if="columns.sales_note.visible">
                            <template v-for="(row, index) in row.sales_note">
                                <label class="d-block" :key="index"
                                    >{{ row.number_full }} ({{
                                        row.state_type_description
                                    }})</label
                                >
                            </template>
                        </td>
                        <td v-if="columns.order_note.visible">
                            <template
                                v-if="
                                    row.order_note && row.order_note.identifier
                                "
                            >
                                {{ row.order_note.identifier }}
                            </template>
                        </td>
                        <td v-if="columns.send_it.visible">
                            <!--
                            <el-tooltip
                                        class="item"
                                        effect="dark"
                                        placement="bottom">
                                <div slot="content">
                                    <span v-for="(item, i) in row.email_send_it_array"
                                          :key="i">
                                        {{ (item.email_send_it === false)?'No enviado':'Enviado' }} - {{ item.email }}  - {{ item.send_date }} <br>
                                    </span>
                                </div>
                                <span class="badge "
                                      :class="
                                      {'text-danger': (row.email_send_it === false), 'text-success': (row.email_send_it === true), }">
                                    <i class="fas fa-lg"
                                       :class="{ 'fa-times': (row.email_send_it === false), 'fa-check': (row.email_send_it === true), }"
                                    ></i>
                                </span>
                            </el-tooltip>-->

                            <span
                                class="badge"
                                :class="{
                                    'text-danger': row.email_send_it === false,
                                    'text-success': row.email_send_it === true,
                                }"
                            >
                                <i
                                    class="fas fa-lg"
                                    :class="{
                                        'fa-times': row.email_send_it === false,
                                        'fa-check': row.email_send_it === true,
                                    }"
                                ></i>
                            </span>
                        </td>
                        <td v-if="columns.sire.visible">
                            <el-tooltip
                                v-for="appendix in [2, 3, 4, 5]"
                                :key="appendix"
                                :content="`Anexo ${appendix}`"
                            >
                                <el-tag
                                    @click="changeSire(row, appendix)"
                                    role="button"
                                    class="m-1"
                                    :type="
                                        row[`appendix_${appendix}`]
                                            ? 'success'
                                            : 'danger'
                                    "
                                >
                                    A{{ appendix }}
                                </el-tag>
                            </el-tooltip>
                        </td>
                        <td>
                            <el-tooltip
                                v-if="tooltip(row, false)"
                                class="item"
                                effect="dark"
                                placement="bottom"
                            >
                                <div slot="content">{{ tooltip(row) }}</div>
                                <span
                                    class="badge bg-secondary text-white"
                                    :class="{
                                        'bg-danger': row.state_type_id === '11',
                                        'bg-warning':
                                            row.state_type_id === '13',
                                        'bg-secondary':
                                            row.state_type_id === '01',
                                        'bg-info': row.state_type_id === '03',
                                        'bg-success':
                                            row.state_type_id === '05',
                                        'bg-secondary':
                                            row.state_type_id === '07',
                                        'bg-dark': row.state_type_id === '09',
                                    }"
                                >
                                    {{ row.state_type_description }}
                                </span>
                            </el-tooltip>
                            <template v-else>
                                <el-popover
                                    v-if="isAuditor"
                                    placement="top"
                                    width="160"
                                >
                                    <table>
                                        <tbody>
                                            <tr
                                                v-for="(
                                                    state, idx
                                                ) in document_state_types"
                                                :key="idx"
                                                @click="
                                                    sendState(state.id, row)
                                                "
                                            >
                                                <td>
                                                    <span
                                                        class="badge bg-secondary text-white"
                                                        :class="{
                                                            'bg-danger':
                                                                state.id ===
                                                                '11',
                                                            'bg-warning':
                                                                state.id ===
                                                                '13',
                                                            'bg-secondary':
                                                                state.id ===
                                                                '01',
                                                            'bg-info':
                                                                state.id ===
                                                                '03',
                                                            'bg-success':
                                                                state.id ===
                                                                '05',
                                                            'bg-secondary':
                                                                state.id ===
                                                                '07',
                                                            'bg-dark':
                                                                state.id ===
                                                                '09',
                                                        }"
                                                        role="button"
                                                    >
                                                        {{ state.description }}
                                                    </span>
                                                </td>
                                            </tr>
                                        </tbody>
                                    </table>

                                    <span
                                        slot="reference"
                                        class="badge bg-secondary text-white"
                                        :class="{
                                            'bg-danger':
                                                row.state_type_id === '11',
                                            'bg-warning':
                                                row.state_type_id === '13',
                                            'bg-secondary':
                                                row.state_type_id === '01',
                                            'bg-info':
                                                row.state_type_id === '03',
                                            'bg-success':
                                                row.state_type_id === '05',
                                            'bg-secondary':
                                                row.state_type_id === '07',
                                            'bg-dark':
                                                row.state_type_id === '09',
                                        }"
                                    >
                                        {{ row.state_type_description }}
                                    </span>
                                </el-popover>
                                <span
                                    v-else
                                    class="badge bg-secondary text-white"
                                    :class="{
                                        'bg-danger': row.state_type_id === '11',
                                        'bg-warning':
                                            row.state_type_id === '13',
                                        'bg-secondary':
                                            row.state_type_id === '01',
                                        'bg-info': row.state_type_id === '03',
                                        'bg-success':
                                            row.state_type_id === '05',
                                        'bg-secondary':
                                            row.state_type_id === '07',
                                        'bg-dark': row.state_type_id === '09',
                                    }"
                                >
                                    {{ row.state_type_description }}
                                </span>
                            </template>

                            <template
                                v-if="
                                    row.regularize_shipping &&
                                    row.state_type_id === '01'
                                "
                            >
                                <el-tooltip
                                    class="item"
                                    effect="dark"
                                    :content="row.message_regularize_shipping"
                                    placement="top-start"
                                >
                                    <i
                                        class="fas fa-exclamation-triangle fa-lg"
                                        style="color: #d2322d !important"
                                    ></i>
                                </el-tooltip>
                            </template>
                        </td>
                        <td v-if="columns.user_name.visible">
                            <template v-if="isAuditor">
                                <div>
                                    <el-popover placement="top" width="250">
                                        <table>
                                            <thead>
                                                <tr>
                                                    <th>Usuarios:</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <tr
                                                    v-for="(user, idx) in users"
                                                    :key="idx"
                                                    @click="
                                                        updateUser(user.id, row)
                                                    "
                                                >
                                                    <td>
                                                        <span
                                                            role="button"
                                                            class="m-1"
                                                        >
                                                            {{ user.name }}
                                                        </span>
                                                    </td>
                                                </tr>
                                            </tbody>
                                        </table>

                                        <span slot="reference">
                                            {{ row.user_name }}
                                            <br /><small
                                                v-text="row.user_email"
                                            ></small>
                                        </span>
                                    </el-popover>
                                </div>
                            </template>
                            <template v-else>
                                {{ row.user_name }}
                                <br /><small v-text="row.user_email"></small>
                            </template>
                        </td>
                        <td v-if="columns.exchange_rate_sale.visible">
                            {{ row.exchange_rate_sale }}
                        </td>
                        <td
                            class="text-center"
                            v-if="columns.currency_type_id.visible"
                        >
                            {{ row.currency_type_id }}
                        </td>
                        <td class="text-center" v-if="columns.guides.visible">
                            <span v-for="(item, i) in row.guides" :key="i">
                                {{ item.number }} <br />
                            </span>
                        </td>

                        <td
                            class="text-center"
                            v-if="columns.plate_numbers.visible"
                        >
                            <span
                                v-for="(item, i) in row.plate_numbers"
                                :key="i"
                            >
                                {{ item.description }} <br />
                            </span>
                        </td>

                        <td
                            class="text-end"
                            v-if="columns.total_exportation.visible"
                        >
                            {{ row.total_exportation }}
                        </td>

                        <td class="text-end" v-if="columns.total_free.visible">
                            {{ row.total_free }}
                        </td>

                        <td
                            class="text-end"
                            v-if="columns.total_unaffected.visible"
                        >
                            {{ row.total_unaffected }}
                        </td>
                        <td
                            class="text-end"
                            v-if="columns.total_exonerated.visible"
                        >
                            {{ row.total_exonerated }}
                        </td>
                        <td
                            class="text-end"
                            v-if="columns.total_charge.visible"
                        >
                            {{ row.total_charge }}
                        </td>
                        <td class="text-end">{{ row.total_taxed }}</td>
                        <td class="text-end">{{ row.total_igv }}</td>
                        <td class="text-end" v-if="columns.total.visible">
                            {{ row.total.toFixed(2) }}
                        </td>
                        <td
                            class="text-end"
                            v-if="columns.balance.visible"
                            :class="{
                                'text-warning': row.balance > 0,
                                'text-success': row.balance == 0,
                            }"
                        >
                            {{ row.balance }}
                        </td>
                        <td v-if="columns.purchase_order.visible">
                            {{ row.purchase_order }}
                        </td>
                        <td class="text-end">
                            <!-- <button
                                type="button"
                                style="min-width: 41px"
                                class="btn waves-effect waves-light btn-sm btn-info m-1__2"
                                @click.prevent="sendPse(row.id)"
                            >
                                PSE
                            </button> -->
                            <el-tooltip
                                v-if="row.btn_pdf_voided"
                                effect="dark"
                                placement="top-start"
                                content="PDF, comunicación de baja."
                            >
                                <button
                                    type="button"
                                    style="min-width: 41px"
                                    class="btn btn-danger mb-1"
                                    @click.prevent="voidedPdf(row.id)"
                                >
                                    PDF
                                </button>
                            </el-tooltip>
                            <button
                                type="button"
                                style="min-width: 41px"
                                class="btn btn-outline-primary mb-1"
                                @click.prevent="clickDownload(row.download_xml)"
                                v-if="row.has_xml"
                            >
                                XML
                            </button>
                            <button
                                type="button"
                                style="min-width: 41px"
                                class="btn btn-outline-primary mb-1"
                                @click.prevent="clickDownload(row.download_pdf)"
                                v-if="row.has_pdf"
                            >
                                PDF
                            </button>
                            <template v-if="row.has_cdr">
                                <button
                                    type="button"
                                    style="min-width: 41px"
                                    class="btn btn-outline-primary mb-1"
                                    @click.prevent="
                                        clickDownload(row.download_cdr)
                                    "
                                    v-if="row.auditor_state == false"
                                >
                                    CDR
                                </button>
                                <el-tooltip
                                    effect="dark"
                                    placement="top-start"
                                    content="CDR no existe, estado registrado por auditor."
                                    v-else
                                >
                                    <button
                                        type="button"
                                        style="min-width: 41px"
                                        class="btn waves-effect waves-light btn-sm btn-danger m-1__2"
                                    >
                                        CDR
                                    </button>
                                </el-tooltip>
                            </template>

                            <button
                                type="button"
                                style="min-width: 41px"
                                class="btn btn-outline-danger mb-1"
                                @click.prevent="clickKillDocument(row.id)"
                                v-if="
                                    configuration.delete_documents &&
                                    !isComercial && !isOptometry
                                "
                            >
                                ELIMINAR
                            </button>
                        </td>

                        <td class="text-end" v-if="typeUser != 'integrator'">
                            <div class="ms-1">
                                <button
                                    type="button"
                                    class="btn btn-outline-primary btn-icon btn-icon-only"
                                    data-bs-offset="0,3"
                                    data-bs-toggle="dropdown"
                                    aria-haspopup="true"
                                    aria-expanded="false"
                                >
                                    <i class="fas fa-ellipsis-v"></i>
                                </button>
                                <div class="dropdown-menu dropdown-menu-end">
                                    <template v-if="!isComercial">
                                        <div
                                            v-if="
                                                configuration.permission_to_edit_cpe
                                            "
                                        >
                                            <a
                                                :href="`/documents/${row.id}/edit`"
                                                class="dropdown-item"
                                                v-if="
                                                    row.state_type_id ===
                                                        '01' &&
                                                    userPermissionEditCpe &&
                                                    row.is_editable
                                                "
                                            >
                                                Editar
                                            </a>
                                        </div>
                                        <div v-else>
                                            <a
                                                :href="`/documents/${row.id}/edit`"
                                                class="dropdown-item"
                                                v-if="
                                                    row.state_type_id ===
                                                        '01' &&
                                                    userId == row.user_id &&
                                                    row.is_editable
                                                "
                                            >
                                                Editar
                                            </a>
                                        </div>
                                        <template
                                            v-if="
                                                row.document_type_id == '03' ||
                                                ((row.document_type_id ==
                                                    '07' ||
                                                    row.document_type_id ==
                                                        '08') &&
                                                    affectedReceived(
                                                        row.affected_documents
                                                    ) &&
                                                    row.state_type_id == '01')
                                            "
                                        >
                                            <button
                                                class="dropdown-item"
                                                @click.prevent="
                                                    clickSendRes(row.id)
                                                "
                                            >
                                                Cambiar a envío por resumen
                                            </button>
                                            <button
                                                class="dropdown-item"
                                                @click.prevent="
                                                    clickSendInd(row.id)
                                                "
                                            >
                                                Cambiar a envío individual
                                            </button>
                                        </template>
                                        <button
                                            class="dropdown-item"
                                            @click.prevent="
                                                clickSendPSE(row.id)
                                            "
                                            v-if="row.btn_send_pse"
                                        >
                                            Enviar (PSE)
                                        </button>
                                        <button
                                            class="dropdown-item"
                                            @click.prevent="clickCheck(row.id)"
                                            v-if="row.btn_check_pse"
                                        >
                                            Consultar (PSE)
                                        </button>
                                        <!-- <button
                                        class="dropdown-item"
                                        @click.prevent="clickJson(row.id)"
                                        v-if="
                                            row.btn_check_voided_pse
                                        "
                                    >
                                         Consultar ticket anulation (PSE)
                                    </button> -->
                                        <button
                                            class="dropdown-item"
                                            @click.prevent="clickJson(row.id)"
                                            v-if="
                                                row.btn_send_pse ||
                                                row.btn_check_pse
                                            "
                                        >
                                            Json (PSE)
                                        </button>
                                        <button
                                            class="dropdown-item"
                                            @click.prevent="clickResend(row.id)"
                                            v-if="row.btn_resend && !isClient"
                                        >
                                            Reenviar
                                        </button>
                                        <button
                                            class="dropdown-item"
                                            @click.prevent="
                                                clickReStore(row.id)
                                            "
                                            v-if="row.btn_recreate_document"
                                        >
                                            Volver a recrear
                                        </button>
                                        <button
                                            class="dropdown-item"
                                            @click.prevent="
                                                clickChangeToRegisteredStatus(
                                                    row.id
                                                )
                                            "
                                            v-if="
                                                row.btn_change_to_registered_status
                                            "
                                        >
                                            Cambiar a estado registrado
                                        </button>
                                        <a
                                            :href="`/${resource}/note/${row.id}`"
                                            class="dropdown-item"
                                            v-if="row.btn_note"
                                        >
                                            Nota
                                        </a>
                                        <a
                                            :href="`/dispatches/create_new/document/${row.id}`"
                                            class="dropdown-item"
                                            v-if="row.btn_guide"
                                        >
                                            Guía
                                        </a>
                                        <button
                                            class="dropdown-item"
                                            @click.prevent="clickVoided(row.id)"
                                            v-if="row.btn_voided"
                                        >
                                            Anular
                                        </button>
                                        <button
                                            class="dropdown-item"
                                            @click.prevent="
                                                clickVoidedPse(row.id)
                                            "
                                            v-if="row.btn_voided_pse"
                                        >
                                            Anular
                                        </button>
                                        <button
                                            class="dropdown-item"
                                            @click.prevent="
                                                clickVoidedCheckPse(row.id)
                                            "
                                            v-if="row.btn_check_voided_pse"
                                        >
                                            Verificar anulación
                                        </button>

                                        <a
                                            type="button"
                                            class="dropdown-item"
                                            @click.prevent="copy(row.id)"
                                        >
                                            Copiar
                                        </a>
                                        <a
                                            type="button"
                                            class="dropdown-item"
                                            @click.prevent="duplicate(row.id)"
                                        >
                                            Duplicar
                                        </a>
                                        <a
                                            type="button"
                                            class="dropdown-item"
                                            @click.prevent="
                                                clickDeleteDocument(row.id)
                                            "
                                            v-if="row.btn_delete_doc_type_03"
                                        >
                                            Eliminar
                                        </a>
                                        <a
                                            class="dropdown-item"
                                            @click.prevent="
                                                clickSendOnline(row.id)
                                            "
                                            v-if="isClient && !row.send_server"
                                        >
                                            Enviar Servidor
                                        </a>
                                        <a
                                            class="dropdown-item"
                                            @click.prevent="
                                                clickCheckOnline(row.id)
                                            "
                                            v-if="
                                                isClient &&
                                                row.send_server &&
                                                (row.state_type_id === '01' ||
                                                    row.state_type_id === '03')
                                            "
                                        >
                                            Consultar Servidor
                                        </a>
                                        <a
                                            v-if="row.btn_constancy_detraction"
                                            class="dropdown-item"
                                            @click.prevent="
                                                clickCDetraction(row.id)
                                            "
                                        >
                                            C. Detracción
                                        </a>
                                        <template
                                            v-if="
                                                row.btn_force_send_by_summary &&
                                                typeUser === 'admin'
                                            "
                                        >
                                            <button
                                                class="dropdown-item"
                                                @click.prevent="
                                                    clickForceSendBySummary(
                                                        row.id
                                                    )
                                                "
                                            >
                                                Enviar por resumen
                                            </button>
                                        </template>

                                        <button
                                            class="dropdown-item"
                                            @click.prevent="
                                                clickPayment(row.id)
                                            "
                                        >
                                            Pagos
                                        </button>
                                        <template v-if="row.btn_retention">
                                            <div class="dropdown-divider"></div>
                                            <button
                                                class="dropdown-item"
                                                @click.prevent="
                                                    clickRetention(row.id)
                                                "
                                            >
                                                Retención
                                            </button>
                                        </template>
                                    </template>
                                    <button
                                        class="dropdown-item"
                                        @click.prevent="clickOptions(row.id)"
                                    >
                                        Opciones
                                    </button>
                                </div>
                            </div>
                        </td>
                        <td v-if="configuration.college">
                            <el-button type="primary" @click="openPeriod(row)">
                                Editar
                            </el-button>
                        </td>
                    </tr>
                </data-table>
            </div>

            <documents-voided
                :showDialog.sync="showDialogVoided"
                :recordId="recordId"
            ></documents-voided>

            <items-import :showDialog.sync="showImportDialog"></items-import>

            <document-import-second
                :showDialog.sync="showImportSecondDialog"
            ></document-import-second>

            <document-options
                :showDialog.sync="showDialogOptions"
                :recordId="recordId"
                :showClose="true"
                :configuration="configuration"
            ></document-options>

            <document-payments
                :showDialog.sync="showDialogPayments"
                :documentId="recordId"
            ></document-payments>

            <document-constancy-detraction
                :showDialog.sync="showDialogCDetraction"
                :recordId="recordId"
            ></document-constancy-detraction>
            <report-payment
                :showDialog.sync="showDialogReportPayment"
            ></report-payment>

            <report-payment-complete
                :showDialog.sync="showDialogReportPaymentComplete"
            ></report-payment-complete>

            <DocumentValidate
                :showDialogValidate.sync="showDialogValidate"
            ></DocumentValidate>

            <massive-validate-cpe
                :showDialogValidate.sync="showDialogApiPeruDevValidate"
            ></massive-validate-cpe>

            <document-import-excel
                :showDialog.sync="showImportExcelDialog"
            ></document-import-excel>

            <document-retention
                :showDialog.sync="showDialogRetention"
                :documentId="recordId"
            ></document-retention>

            <customer-detail
                :showDialog.sync="showCustomerDetail"
                :number="currentCustomer"
            >
            </customer-detail>
            <period-modal
                :showDialog.sync="showPeriod"
                :document="currentDocument"
            ></period-modal>
        </div>
    </div>
</template>

<script>
import DocumentsVoided from "./partials/voided.vue";
import DocumentOptions from "./partials/options.vue";
import DocumentPayments from "./partials/payments.vue";
import DocumentImportSecond from "./partials/import_second";
import DocumentImportExcel from "./partials/ImportExcel";
import DataTable from "../../../components/DataTableDocuments.vue";
import CustomerDetail from "../../../components/CustomerDetail.vue";
import ItemsImport from "./import.vue";
import PeriodModal from "../../../../../modules/Suscription/Resources/assets/js/components/PeriodModal.vue";
import { deletable } from "../../../mixins/deletable";
import DocumentConstancyDetraction from "./partials/constancy_detraction.vue";
import ReportPayment from "./partials/report_payment.vue";
import ReportPaymentComplete from "./partials/report_payment_complete.vue";
import DocumentValidate from "./partials/validate.vue";
import MassiveValidateCpe from "../../../../../modules/ApiPeruDev/Resources/assets/js/components/MassiveValidateCPE";
import { mapActions, mapState } from "vuex/dist/vuex.mjs";
import DocumentRetention from "./partials/retention";
import moment from "moment";

export default {
    mixins: [deletable],
    props: [
        "to_anulate",
        "isAuditor",
        "isComercial",
        "isOptometry",
        "isClient",
        "typeUser",
        "import_documents",
        "import_documents_second",
        "document_import_excel",
        "document_state_types",
        "userId",
        "configuration",
        "userPermissionEditCpe",
        "api_service_token",
        "view_apiperudev_validator_cpe",
        "view_validator_cpe",
    ],
    computed: {
        ...mapState(["config"]),
    },
    components: {
        PeriodModal,
        CustomerDetail,
        DocumentsVoided,
        ItemsImport,
        DocumentImportSecond,
        DocumentOptions,
        DocumentPayments,
        DataTable,
        DocumentConstancyDetraction,
        ReportPayment,
        ReportPaymentComplete,
        DocumentValidate,
        MassiveValidateCpe,
        DocumentImportExcel,
        DocumentRetention,
    },
    data() {
        return {
            visible: false,
            currentDocument: null,
            showPeriod: false,
            currentCustomer: null,
            showCustomerDetail: false,
            showDialogApiPeruDevValidate: false,
            showDialogValidate: false,
            showDialogReportPayment: false,
            showDialogReportPaymentComplete: false,
            showDialogVoided: false,
            showImportDialog: false,
            showDialogCDetraction: false,
            showImportSecondDialog: false,
            showImportExcelDialog: false,
            showDialogRetention: false,
            loading_data: false,
            resource: "documents",
            recordId: null,
            showDialogOptions: false,
            showDialogPayments: false,
            users: [],
            columns: {
                document_type_id: {
                    title: "Comprobante de Pago",
                    visible: false,
                },
                notes: {
                    title: "Notas C/D",
                    visible: false,
                },
                dispatch: {
                    title: "Guía de Remisión",
                    visible: false,
                },
                plate_numbers: {
                    title: "Placa",
                    visible: false,
                },
                user_name: {
                    title: "Usuario",
                    visible: false,
                },
                exchange_rate_sale: {
                    title: "Tipo de cambio",
                    visible: false,
                },
                total_exportation: {
                    title: "T.Exportación",
                    visible: false,
                },
                total_free: {
                    title: "T.Gratuito",
                    visible: false,
                },
                total_unaffected: {
                    title: "T.Inafecto",
                    visible: false,
                },
                total_exonerated: {
                    title: "T.Exonerado",
                    visible: false,
                },
                date_of_due: {
                    title: "F. Vencimiento",
                    visible: false,
                },
                guides: {
                    title: "Guias",
                    visible: false,
                },
                sales_note: {
                    title: "Nota de ventas",
                    visible: false,
                },
                order_note: {
                    title: "Pedidos",
                    visible: false,
                },
                send_it: {
                    title: "Correo enviado al destinatario",
                    visible: false,
                },
                total: {
                    title: "Total",
                    visible: true,
                },
                currency_type_id: {
                    title: "Moneda",
                    visible: false,
                },
                purchase_order: {
                    title: "Orden de Compra",
                    visible: false,
                },
                soap_type: {
                    title: "Soap",
                    visible: false,
                },
                balance: {
                    title: "Saldo",
                    visible: true,
                },
                total_charge: {
                    title: "T.Cargos",
                    visible: false,
                },
                date_payment: {
                    title: "Fecha de pago",
                    visible: false,
                },
                sire: {
                    title: "SIRE",
                    visible: false,
                },
            },
        };
    },
    created() {
        this.$store.commit("setConfiguration", this.configuration);

        this.loadConfiguration();
        this.getColumnsToShow();
        this.getUsers();
    },
    methods: {
        voidedPdf(id) {
            window.open(`documents/voided_pdf/${id}`, "_blank");
        },
        async updateUser(user_id, document) {
            let { id } = document;
            try {
                const response = await this.$http(
                    `/documents/update-user/${user_id}/${id}`
                );
                if (response.status == 200) {
                    this.$message.success(response.data.message);
                    this.$refs.dataTable.getRecords();
                }
            } catch (e) {
                this.$message.error(e.message);
            }
        },
        async getUsers() {
            try {
                const response = await this.$http("/users/records-lite");
                if (response.status == 200) {
                    this.users = response.data;
                }
            } catch (e) {
                console.log(e);
            }
        },
        changeUser() {
            console.log(
                "🚀 ~ file: index.vue:1239 ~ changeUser ~ this.isAuditor:",
                this.isAuditor
            );
        },
        async clickVoidedCheckPse(id) {
            try {
                const response = await this.$http(
                    `documents/voided_check_pse/${id}`
                );
                let { data } = response;
                if (data.sent) {
                    let message =
                        data.message || data.description || data.mensaje;
                    this.$message.success(message);
                    this.$refs.dataTable.getRecords();
                } else {
                    let message = data.message || data.description;
                    this.$message.error(message);
                }
            } catch (e) {
                console.log(e);
            } finally {
            }
        },
        clickJson(id) {
            window.open(`/documents/json_pse/${id}`, "_blank");
        },
        async clickVoidedPse(id) {
            try {
                const response = await this.$http(`documents/voided_pse/${id}`);
                let { data } = response;
                if (data.sent) {
                    let message = data.message || data.description;
                    this.$message.success(message);
                    this.$refs.dataTable.getRecords();
                } else {
                    let message = data.message || data.description;
                    this.$message.error(message);
                }
            } catch (e) {
                console.log(e);
            } finally {
            }
        },
        async clickSendPSE(id) {
            try {
                const response = await this.$http(`documents/send_pse/${id}`);
                let { data } = response;
                if (data.sent) {
                    let message = data.message || data.description;
                    this.$message.success(message);
                    this.$refs.dataTable.getRecords();
                } else {
                    let message = data.message || data.description;
                    this.$message.error(message);
                }
            } catch (e) {
                console.log(e);
            } finally {
            }
        },
        async clickCheck(id) {
            try {
                const response = await this.$http(`documents/check_pse/${id}`);
                let { data } = response;
                let message = data.mensaje || data.description;
                if (data.rejected) {
                    this.$message.error(message);
                } else {
                    this.$message.success(message);
                }
                this.$refs.dataTable.getRecords();
            } catch (e) {
                console.log(e);
            } finally {
            }
        },
        async changeSire(row, appendix) {
            try {
                const response = await this.$http(
                    `documents/change_sire/${row.id}/${appendix}`
                );
                if (response.data.success) {
                    this.$message.success(response.data.message);
                    this.$refs.dataTable.getRecords();
                } else {
                    this.$message.error(response.data.message);
                }
            } catch (e) {
                console.log(e);
            } finally {
            }
        },
        async sendState(state_type_id, row) {
            try {
                const response = await this.$http(
                    `documents/change_state/${state_type_id}/${row.id}`
                );
                if (response.data.success) {
                    this.$message.success(response.data.message);
                    this.$refs.dataTable.getRecords();
                } else {
                    this.$message.error(response.data.message);
                }
            } catch (e) {
                console.log(e);
            } finally {
            }

            row.showing = false;
        },
        changeState(row) {
            row.total = 66.0;
        },
        affectedReceived(docs = []) {
            let affected = false;
            for (let i = 0; i < docs.length; i++) {
                if (docs[i].document_type_id == "03") {
                    affected = true;
                    break;
                }
            }
            return affected;
        },
        async clickSendRes(id) {
            const response = await this.$http.get(`documents/res/${id}`);

            if (response.status == 200) {
                this.$message.success(response.data.message);
                this.$refs.dataTable.getRecords();
                console.log(response);
            }
        },
        async clickSendInd(id) {
            const response = await this.$http.get(`documents/ind/${id}`);
            if (response.status == 200) {
                this.$message.success(response.data.message);
                this.$refs.dataTable.getRecords();
                console.log(response);
            }
        },
        async sendPse(id) {
            try {
                const response = await this.$http.get(
                    `/documents/send_pse/${id}`
                );
                if (response.data.success) {
                    this.$message.success(response.data.message);
                } else {
                    this.$message.error(response.data.message);
                }
            } catch (e) {
                console.log(e);
                return;
            }
        },
        async clickKillDocument(id) {
            //use a $confirm
            try {
                const confirm = await this.$confirm(
                    "¿Está seguro de eliminar el documento y todos los registros relacionados?",
                    "Advertencia",
                    {
                        confirmButtonText: "Eliminar",
                        cancelButtonText: "Cancelar",
                        type: "warning",
                    }
                );
                if (confirm) {
                    const response = await this.$http.get(
                        `/${this.resource}/kill/${id}`
                    );
                    console.log(response);
                    if (response.data.success) {
                        this.$message.success(response.data.message);
                        this.$refs.dataTable.getRecords();
                    } else {
                        this.$message.error(response.data.message);
                    }
                }
            } catch (e) {
                return;
            }
        },
        openPeriod(row) {
            this.currentDocument = row;
            this.showPeriod = true;
        },
        openCustomerDetail(document) {
            this.currentCustomer = document.customer_number;
            this.showCustomerDetail = true;
        },
        ...mapActions(["loadConfiguration"]),

        getColumnsToShow(updated) {
            this.$http
                .post("/validate_columns", {
                    columns: this.columns,
                    report: "document_index", // Nombre del reporte.
                    updated: updated !== undefined,
                })
                .then((response) => {
                    if (updated === undefined) {
                        let currentCols = response.data.columns;
                        if (currentCols !== undefined) {
                            this.columns = currentCols;
                        }
                    }
                })
                .catch((error) => {
                    console.error(error);
                });
        },
        clickVoided(recordId = null) {
            this.recordId = recordId;
            this.showDialogVoided = true;
        },
        clickDownload(download) {
            window.open(download, "_blank");
        },
        clickResend(document_id) {
            this.$http
                .get(`/${this.resource}/send/${document_id}`)
                .then((response) => {
                    if (response.data.success) {
                        this.$message.success(response.data.message);
                        this.$eventHub.$emit("reloadData");
                    } else {
                        this.$message.error(response.data.message);
                    }
                })
                .catch((error) => {
                    this.$message.error(error.response.data.message);
                });
        },
        clickSendOnline(document_id) {
            this.$http
                .get(`/${this.resource}/send_server/${document_id}/1`)
                .then((response) => {
                    if (response.data.success) {
                        this.$message.success(
                            "Se envio satisfactoriamente el comprobante."
                        );
                        this.$eventHub.$emit("reloadData");

                        this.clickCheckOnline(document_id);
                    } else {
                        this.$message.error(response.data.message);
                    }
                })
                .catch((error) => {
                    this.$message.error(error.response.data.message);
                });
        },
        clickCheckOnline(document_id) {
            this.$http
                .get(`/${this.resource}/check_server/${document_id}`)
                .then((response) => {
                    if (response.data.success) {
                        this.$message.success("Consulta satisfactoria.");
                        this.$eventHub.$emit("reloadData");
                    } else {
                        this.$message.error(response.data.message);
                    }
                })
                .catch((error) => {
                    this.$message.error(error.response.data.message);
                });
        },
        clickCDetraction(recordId) {
            this.recordId = recordId;
            this.showDialogCDetraction = true;
        },
        clickOptions(recordId = null) {
            this.recordId = recordId;
            this.showDialogOptions = true;
        },
        clickReStore(document_id) {
            this.$http
                .get(`/${this.resource}/re_store/${document_id}`)
                .then((response) => {
                    if (response.data.success) {
                        this.$message.success(response.data.message);
                        this.$eventHub.$emit("reloadData");
                    } else {
                        this.$message.error(response.data.message);
                    }
                })
                .catch((error) => {
                    this.$message.error(error.response.data.message);
                });
        },
        tooltip(row, message = true) {
            if (message) {
                if (row.shipping_status) return row.shipping_status.message;

                if (row.sunat_shipping_status)
                    return row.sunat_shipping_status.message;

                if (row.query_status) return row.query_status.message;
            }

            if (
                row.shipping_status ||
                row.sunat_shipping_status ||
                row.query_status
            )
                return true;

            return false;
        },
        clickPayment(recordId) {
            this.recordId = recordId;
            this.showDialogPayments = true;
        },
        clickChangeToRegisteredStatus(document_id) {
            this.$http
                .get(
                    `/${this.resource}/change_to_registered_status/${document_id}`
                )
                .then((response) => {
                    if (response.data.success) {
                        this.$message.success(response.data.message);
                        this.$eventHub.$emit("reloadData");
                    } else {
                        this.$message.error(response.data.message);
                    }
                })
                .catch((error) => {
                    this.$message.error(error.response.data.message);
                });
        },
        clickImport() {
            this.showImportDialog = true;
        },
        clickDownloadReportPagos() {
            this.showDialogReportPaymentComplete = true;
        },
        clickImportSecond() {
            this.showImportSecondDialog = true;
        },
        clickImportExcel() {
            this.showImportExcelDialog = true;
        },
        duplicate(document_id) {
            this.loading_data = true;
            this.$http
                .get(`/${this.resource}/duplicate/${document_id}`)
                .then((response) => {
                    this.loading_data = false;
                    this.$message.success(
                        "Se duplico con exito el comprobante...."
                    );
                    this.$eventHub.$emit("reloadData");
                })
                .catch((error) => {
                    this.loading_data = false;
                    this.$message.success(
                        "Ocurrio um error intente nuevamente"
                    );
                })
                .finally(() => {
                    this.loading_data = false;
                });
        },
        copy(document_id) {
            window.open(`/${this.resource}/copy/${document_id}`, "_blank");
        },
        clickDeleteDocument(document_id) {
            this.destroy(
                `/${this.resource}/delete_document/${document_id}`
            ).then(() => this.$eventHub.$emit("reloadData"));
        },
        clickReportPayments() {
            this.showDialogReportPayment = true;
        },
        clickForceSendBySummary(id) {
            this.forceSendBySummary(`/${this.resource}/force-send-by-summary`, {
                id: id,
            }).then(() => this.$eventHub.$emit("reloadData"));
        },
        clickRetention(recordId) {
            this.recordId = recordId;
            this.showDialogRetention = true;
        },
        isDateWarning(date_due) {
            let today = Date.now();
            return moment(date_due).isBefore(today);
        },
    },
};
</script>
