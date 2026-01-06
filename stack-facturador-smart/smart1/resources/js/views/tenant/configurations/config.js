
import TermsCondition from "@views/quotations/partials/terms_condition.vue";
import TermsConditionSale from "@views/documents/partials/terms_condition.vue";
import TermsConditionDispatches from "../../tenant/dispatches/partials/terms_condition.vue";
import AllowanceCharge from "./partials/allowance_charge.vue";
import { mapActions, mapState } from "vuex";
import ReportConfigurationsIndex from "./partials/report_configurations_index.vue";
import PdfFooterImages from "./partials/pdf_footer_images.vue";
import WarningDiscount from "./partials/warning_discount.vue";
import AppConfigurationTaxo from "./partials/app_configuration_taxo.vue";
export default {
    props: ["typeUser", "configuration", "company"],
    components: {
        TermsCondition,
        TermsConditionSale,
        AllowanceCharge,
        ReportConfigurationsIndex,
        PdfFooterImages,
        TermsConditionDispatches,
        WarningDiscount,
        AppConfigurationTaxo,
    },
    computed: {
        getSplitLabel() {
            return (text) => {
                let maxLength = 50;
                if (text.length <= maxLength) {
                    return [text];
                }
                const words = text.split(' ');
                const parts = [];
                let currentPart = '';
                
                for (let i = 0; i < words.length; i++) {
                    const word = words[i];
                    const testPart = currentPart ? currentPart + ' ' + word : word;
                    
                    if (testPart.length <= maxLength) {
                        currentPart = testPart;
                    } else {
                        if (currentPart) {
                            parts.push(currentPart);
                        }
                        currentPart = word;
                    }
                }
                
                if (currentPart) {
                    parts.push(currentPart);
                }
                
                return parts;
            };
        },
        ...mapState(["config"]),
    },
    data() {
        return {
            isMobile:false,
            tabPanesConfig: [
                {
                    label: "Servicios",
                    name: "first",
                    icon: "fa fa-cogs",
                    elements: [
                        {
                            label: "Anulación de comprobantes automática",
                            value: "form.anulate_auto",
                            icon: "el-icon-switch",
                        },
                        {
                            label: "Enviar email automáticamente",
                            value: "form.send_auto_email",
                            icon: "el-icon-switch",
                        },
                        {
                            label: "Envío de comprobantes automático",
                            value: "form.send_auto",
                            icon: "el-icon-switch",
                        },
                        {
                            label: "Crontab tareas programadas",
                            value: "form.cron",
                            icon: "el-icon-switch",
                        },
                        {
                            label: "Envío de guía de remisión automático",
                            value: "form.auto_send_dispatchs_to_sunat",
                            icon: "el-icon-switch",
                        },
                        {
                            label: "Enviar boletas de forma individual",
                            value: "form.ticket_single_shipment",
                            icon: "el-icon-switch",
                        },
                        {
                            label: "Validación automática",
                            value: "form.validate_automatic",
                            icon: "el-icon-switch",
                        },
                    ],
                },
                {
                    label: "Visual",
                    name: "second",
                    icon: "fa fa-cog",
                    elements: [
                        {
                            label: "Imprimir vouchers",
                            value: "form.print_vouchers",
                            icon: "el-icon-switch",
                        },
                        {
                            label: "Mostrar sumarios en documentos",
                            value: "form.show_summaries_in_documents",
                            icon: "el-icon-switch",
                        },
                        {
                            label: "Ventas de hoy",
                            value: "form.sales_today",
                            icon: "el-icon-switch",
                        },
                        {
                            label: "Estados de ventas",
                            value: "form.audit_sales",
                            icon: "el-icon-switch",
                        },
                        // {
                        //     label: "Base de descuento (-IGV)",
                        //     value: "form.igv_discount_base",
                        //     icon: "el-icon-switch",
                        // },
                        {
                            label: "Mostrar acceso rápido",
                            value: "form.quick_access",
                            icon: "el-icon-switch",
                        },
                        {
                            label: "Mostrar atributos de productos",
                            value: "form.show_item_attributes",
                            icon: "el-icon-switch",
                        },
                        {
                            label: "Activar producción 2",
                            value: "form.production_2",
                            icon: "el-icon-switch",
                        },
                        {
                            label: "Actualizar precios de productos con tasa de cambio de venta",
                            value: "form.update_items_prices_with_exchange_rate_sale",
                        },
                        {
                            label: "Abrir caja con tasa de cambio de venta",
                            value: "form.open_cash_with_exchange_rate_sale",
                        },
                        {
                            label: "No pagos menores al total",
                            value: "form.no_payments_less_than_total",
                        },
                        {
                            label: "Permitir cambiar vendedor a vendedores",
                            value: "form.allow_seller_change",
                        },
                        {
                            label: "Separar cotización a documentos de servicios y no servicios",
                            value: "form.split_quotation_to_document_services_and_not_services",
                        },
                        {
                            label: "Lista productos individuales por almacén - Productos compuestos",
                            value: "form.list_individual_products_by_warehouse_compound",
                        },
                        {
                            label: "Mantener númeración orden de compra(prefix - id)",
                            value: "form.restore_num_order_purchase",
                        },
                        {
                            label: "Mostrar filtros de productos (pack o individual)",
                            value: "form.show_filters_set_items_for_users",
                        },
                        {
                            label: "Suscripción SAAS emisión mensual",
                            value: "form.full_suscription_list_type",
                        },
                        {
                            label: "Mostrar restricción de stock en formulario de productos",
                            value: "form.show_restrict_stock_in_items_form",
                        },
                        {
                            label: "Permitir nota de crédito como pago",
                            value: "form.nc_payment_nv",
                        },
                        {
                            label: "Restringir fecha de comprobante",
                            value: "form.restrict_receipt_date",
                        },
                        {
                            label: "Permitir generar comprobante de pago desde cotización a vendedores",
                            value: "form.quotation_allow_seller_generate_sale",
                        },
                        {
                            label: "Permitir editar precio unitario a vendedores",
                            value: "form.allow_edit_unit_price_to_seller",
                        },
                        {
                            label: "Permitir editar notas de venta a vendedores",
                            value: "form.show_edit_button",
                        },
                        {
                            label: "Permitir crear productos a vendedores",
                            value: "form.seller_can_create_product",
                        },
                        {
                            label: "Permitir Ver el saldo en balance de finanzas a vendedores",
                            value: "form.seller_can_view_balance",
                        },
                        {
                            label: "Permite habilitar las acciones para vendedores",
                            value: "form.seller_can_generate_sale_opportunities",
                        },
                        {
                            label: "Productos de una ubicación (Stock)",
                            value: "form.product_only_location",
                        },
                        {
                            label: "Vendedor puede ajustar inventario",
                            value: "form.seller_can_ajust_inventory",
                        },
                        {
                            label: "Habilitar suscripción escolar",
                            value: "form.college",
                        },
                        {
                            label: "Habilita elementos de farmacia",
                            value: "form.is_pharmacy",
                        },
                        {
                            label: "Aplicar precios por almacén",
                            value: "form.active_warehouse_prices",
                        },
                        {
                            label: "Control de productos para farmacia",
                            value: "form.pharmacy_control",
                        },
                        {
                            label: "Buscar productos únicamente por serie",
                            value: "form.search_item_by_series",
                        },
                        {
                            label: "Seleccionar precio disponible - Lista de precios",
                            value: "form.select_available_price_list",
                        },
                        {
                            label: "Muestra campos opcionales para los Items a modo informativo",
                            value: "form.show_extra_info_to_item",
                        },
                        {
                            label: "Agrupar productos y cantidades - Generar CPE",
                            value: "form.group_items_generate_document",
                        },
                        {
                            label: "Mostrar el nombre del PDF",
                            value: "form.show_pdf_name",
                        },
                        {
                            label: "Permitir Colocar direccion de llegada en guía",
                            value: "form.dispatches_address_text",
                        },
                        {
                            label: "Asignar dirección de partida - guía",
                            value: "form.set_address_by_establishment",
                        },
                        {
                            label: "Habilitar permiso para editar CPE",
                            value: "form.permission_to_edit_cpe",
                        },
                        {
                            label: "Mostrar totales en el listado de CPE",
                            value: "form.show_totals_on_cpe_list",
                        },
                        {
                            label: "Mostrar precio de última venta",
                            value: "form.show_last_price_sale",
                        },
                        {
                            label: "Días de plazo de envío",
                            value: "form.shipping_time_days",
                        },
                        {
                            label: "Cantidad de elementos en el validador",
                            value: "form.new_validator_pagination",
                        },
                        {
                            label: "Filtrar clientes según vendedor asignado",
                            value: "form.customer_filter_by_seller",
                        },
                        {
                            label: "Habilitar registro de propinas",
                            value: "form.enabled_tips_pos",
                        },
                        {
                            label: "Habilitar búsqueda avanzada",
                            value: "form.enabled_advanced_records_search",
                        },
                        {
                            label: "Separar y ordenar transacciones en caja",
                            value: "form.separate_cash_transactions",
                        },
                        {
                            label: "Ordenar transacciones en R. Ingreso",
                            value: "form.order_cash_income",
                        },
                        {
                            label: "Permitir generar pedidos desde cotización a vendedores",
                            value: "form.generate_order_note_from_quotation",
                        },
                        {
                            label: "Listar productos por almacén",
                            value: "form.list_items_by_warehouse",
                        },
                        {
                            label: "Almacén principal",
                            value: "form.main_warehouse",
                        },
                        {
                            label: "Restringir selección de serie para vendedor",
                            value: "form.restrict_series_selection_seller",
                        },
                        {
                            label: "Vendedor puede acceder a las series de todos los establecimientos",
                            value: "form.seller_establishments_all",
                        },
                        {
                            label: "Cargar voucher - Pagos",
                            value: "form.show_load_voucher",
                        },
                        {
                            label: "Buscar productos según sus especificaciones.",
                            value: "form.search_factory_code_items",
                        },
                        {
                            label: "Restringir venta de productos",
                            value: "form.restrict_sale_items_cpe",
                        },
                        {
                            label: "Visualizar Iconos en Login",
                            value: "form.view_tutorials",
                        },
                
                        {
                            label: "Ocultar reportes de caja",
                            value: "form.cash_report_hidden",
                        },
                        {
                            label: "Mostrar la opción de no descontar stock",
                            value: "form.show_no_stock",
                        },
                        {
                            label: "Facturas/Boletas no afectan stock",
                            value: "form.document_no_stock",
                        },
                        {
                            label: "Pedidos afectan stock",
                            value: "form.discount_order_note",
                        },
                        {
                            label: "Administrador administra cajas - vendedores",
                            value: "form.admin_seller_cash",
                        },
                        {
                            label: "Cotización proyectos",
                            value: "form.quotation_projects",
                        },
                        {
                            label: "Tickets de encomienda",
                            value: "form.package_handlers",
                        },
                        {
                            label: "Re-abrir caja",
                            value: "form.reopen_cash",
                        },
                        {
                            label: "Bloquear edición de notas de venta",
                            value: "form.block_seller_sale_note_edit",
                        },
                        {
                            label: "Todos los vendedores",
                            value: "form.all_sellers",
                        },
                        {
                            label: "Mostrar todos los productos",
                            value: "form.all_products",
                        },
                        {
                            label: "Seleccionar el almacén del producto al vender",
                            value: "form.select_warehouse_to_sell",
                        },
                        {
                            label: "Responsable y placa en compras",
                            value: "form.purchases_control",
                        },
                        {
                            label: "Buscar productos con especificaciones similares.",
                            value: "form.search_by_factory_code",
                        },
                        {
                            label: "Descuentos acumulativos",
                            value: "form.discounts_acc",
                        },
                        {
                            label: "Edición rápida - productos",
                            value: "form.edit_info_documents",
                        },
                        {
                            label: "Producto favoritos - acceso rápido",
                            value: "form.show_favorites_documents",
                        },
                        {
                            label: "Generar varios documentos de un pedido",
                            value: "form.order_note_not_blocked",
                        },
                        {
                            label: "Precio de compra - documentos",
                            value: "form.show_purchase_unit_price",
                        },
                        {
                            label: "Modificar precio de compra - documentos",
                            value: "form.modify_purchase_unit_price",
                        },
                        {
                            label: "Descuento item - documentos",
                            value: "form.show_item_discounts",
                        },
                        {
                            label: "Stock item - documentos",
                            value: "form.show_item_stock",
                        },
                        {
                            label: "Modificar stock item - documentos",
                            value: "form.modify_item_stock",
                        },
                        {
                            label: "Modificar stock item - Productos",
                            value: "form.modify_stock_item_direct",
                        },
                        {
                            label: "Mostrar historial de productos - vendedores",
                            value: "form.sellers_see_history",
                        },
                        {
                            label: "Destino pagos - documentos duplicados",
                            value: "form.current_cash_destination_duplicate",
                        },
                        {
                            label: "Ingresar total unidades x item - Nota de venta",
                            value: "form.count_unit_sale_note",
                        },
                        {
                            label: "Descontar unidades documentos - nota de venta",
                            value: "form.discount_unit_document",
                        },
                        {
                            label: "Buscar cliente por telefono",
                            value: "form.search_by_phone",
                        },
                        {
                            label: "Mostrar suma total de productos",
                            value: "form.show_total_quantity_document",
                        },
                        {
                            label: "Suma total de productos (presentación)",
                            value: "form.show_total_quantity_document_presentation",
                        },
                        {
                            label: "Agregar Empacador y Repartidor al documento",
                            value: "form.dispatchers_packers_document",
                        },
                        {
                            label: "Mostrar alerta por cliente con créditos pendientes",
                            value: "form.show_pending_credits",
                        },
                        {
                            label: "Creación rápida de productos",
                            value: "form.create_items_fast",
                        },
                        {
                            label: "Editar nombres de productos con un click",
                            value: "form.change_name_click_item",
                        },
                        {
                            label: "Emitir anticipos por pagos de notas de venta",
                            value: "form.emit_prepayment_document_from_sale_note",
                        },
                        {
                            label: "Subir productos por excel en documentos",
                            value: "form.show_upload_items_excel_document",
                        },
                        {
                            label: "Productos favoritos en POS",
                            value: "form.pos_items_favorite",
                        },
                        {
                            label: "Ingresar billetes y monedas al cerrar caja",
                            value: "form.pos_cash_counter",
                        },
                        {
                            label: "Crédito en POS",
                            value: "form.pos_credit",
                        },
                        {
                            label: "Prestamos de botellas",
                            value: "form.pos_bottles",
                        },
                        {
                            label: "CSV Mall",
                            value: "form.export_mall",
                        },
                        {
                            label: "Buscar producto por serie, descripción, código interno",
                            value: "form.search_by_series_and_all",
                        },
                        {
                            label: "Precio por condición de pago",
                            value: "form.condition_payment_items",
                        },
                        {
                            label: "Mostrar transportistas en documentos",
                            value: "form.show_dispatcher_documents_sale_notes_order_note",
                        },
                        {
                            label: "Mostrar buscador avanzado de productos",
                            value: "form.search_advance_items",
                        },
                        {
                            label: "Editar información del cliente directamente",
                            value: "form.edit_customer_info_direct",
                        },
                        {
                            label: "Mostrar dirección del establecimiento en transportista",
                            value: "form.establishment_address_dispatch_carrier",
                        },
                        {
                            label: "Filtrar lotes por almacén",
                            value: "form.item_lots_group_filter_by_warehouse",
                        },
                        {
                            label: "Condición de pago crédito por defecto en cotizaciones",
                            value: "form.credit_default_quotation",
                        },
                        {
                            label: "Agregar pago automático al entrar a cotización",
                            value: "form.quotation_payment_default",
                        },
                        {
                            label: "Buscar productos durante la escritura",
                            value: "form.search_items_writing",
                        },
                        {
                            label: "Verificar referencia única de pagos",
                            value: "form.check_reference",
                        },
                        {
                            label: "Decimales en stock",
                            value: "form.stock_decimal",
                        },
                        {
                            label: "Formato de transporte",
                            value: "form.transport_format",
                        },
                        {
                            label: "Rango en presentaciones",
                            value: "form.range_item_unit_type",
                        },
                        {
                            label: "Tipo de descuentos",
                            value: "form.type_discount",
                        },
                        {
                            label: "Mostrar código interno en lista de productos",
                            value: "form.show_internal_id_list",
                        },
                        {
                            label: "Mostrar marca en lista de productos",
                            value: "form.show_brands",
                        },
                        {
                            label: "Mostrar modelo en lista de productos",
                            value: "form.show_models",
                        },
                        {
                            label: "Mostrar categoría en lista de productos",
                            value: "form.show_categories",
                        },
                        {
                            label: "Orden de vaciado de concreto",
                            value: "form.order_concrete",
                        },
                        {
                            label: "Exportar documentos en sistema",
                            value: "form.show_export_system",
                        },
                        {
                            label: "Establecer fechas automáticas en filtro de documentos",
                            value: "form.date_documents_filter",
                        },
                        {
                            label: "Limitar venta diaria por item",
                            value: "form.limit_sale_daily_item",
                        },
                        {
                            label: "Activar ingreso placa",
                            value: "form.plate_number_config",
                        },
                        {
                            label: "Activar opciones especiales de Letras de Cambio",
                            value: "form.bill_of_exchange_special",
                        },
                        {
                            label: "Mostrar cambio de establecimiento rápido para administradores",
                            value: "form.change_establishment_admin",
                        },
                        {
                            label: "Ocultar pagos en cotizaciones",
                            value: "form.hide_quotations_payment",
                        },
                        {
                            label: "Reporte de caja con movimientos de bancos",
                            value: "form.cash_report_with_banks",
                        },
                        {
                            label: "Mostrar brocha para seleccionar color de la app",
                            value: "form.show_set_color",
                        },
                        {
                            label: "Etiqueta de color por producto",
                            value: "form.label_item_color",
                        },
                        {
                            label: "Canales - documentos",
                            value: "form.show_channels_documents",
                        },
                        {
                            label: "Aumentar cantidad de producto en lectura de código de barras",
                            value: "form.add_quantity_in_read_barcode",
                        },
                        {
                            label: "Agregar item al buscar por código de barras",
                            value: "form.add_item_barcode_read",
                        },
                        {
                            label: "Reporte a4 caja con egresos detallados",
                            value: "form.report_box_egress",
                        },
                        {
                            label: "Orden de compra requerido cuando se vende un pack",
                            value: "form.purchase_orden_in_item_set",
                        },
                        {
                            label: "Mostrar estados de entrega",
                            value: "form.items_delivery_states",
                        },
                        {
                            label: "Mostrar descripción de plataforma",
                            value: "form.show_platform_description",
                        },
                        {
                            label: "Incluir packs en la búsqueda de documentos",
                            value: "form.search_packs_document_sale_note",
                        },
                        {
                            label: "Mostrar plataforma en documentos de venta y productos packs",
                            value: "form.show_web_platform_document_sale_note",
                        },
                        {
                            label: "Activar reserva tipo 2",
                            value: "form.hotel_reservation_type_2",
                        },
                        {
                            label: "Presentación por almacén",
                            value: "form.item_unit_type_by_warehouse",
                        },
                        {
                            label: "Mostrar notas en recepción - Hotel",
                            value: "form.show_notes_reception",
                        },
                        {
                            label: "Guardar edición directa de producto en CPE Ligero",
                            value: "form.save_unit_type_price_cpe_lite",
                        },
                        {
                            label: "Manejar cajas por establecimiento",
                            value: "form.cash_by_establishment",
                        },
                        {
                            label: "Generar saldo inicial de caja automáticamente",
                            value: "form.automatic_cash_beginning_balance",
                        },
                        {
                            label: "Manejar tipo de moneda en caja",
                            value: "form.show_currency_in_cash",
                        },
                        // {
                        //     label: "Ticket por defecto",
                        //     value: "form.show_first_ticket_document",
                        // },
                        {
                            label: "Precio por almacén en productos compuestos",
                            value: "form.item_set_warehouse_price",
                        },
                        {
                            label: "Generar varios documentos de una cotización",
                            value: "form.generate_multiple_documents_sale_note",
                        },
                        {
                            label: "Solicitar clave para anulación/nota crédito",
                            value: "form.require_key_for_cancellation",
                        },
                    ],
                },
                {
                    label: "Contable",
                    name: "third",
                    icon: "fa fa-chart-bar",
                    elements: [
                    
                        {
                            label: "Margen de ganancia por producto",
                            value: "form.profit_margin",
                            icon: "el-icon-input-number",
                        },
                        {
                            label: "Impuesto bolsa plástica",
                            value: "form.amount_plastic_bag_taxes",
                            icon: "el-icon-input-number",
                        },
                        {
                            label: "Tipo de afectación venta",
                            value: "form.affectation_igv_type_id",
                            icon: "el-icon-select",
                        },
                        {
                            label: "Tipo de afectación compra",
                            value: "form.purchase_affectation_igv_type_id",
                            icon: "el-icon-select",
                        },
                        {
                            label: "Incluye Igv",
                            value: "form.include_igv",
                            icon: "el-icon-switch",
                        },
                        {
                            label: "Enviar código GS1 en XML",
                            value: "form.send_gs1_xml",
                            icon: "el-icon-switch",
                        },
                        {
                            label: "Notificar cuentas por pagar",
                            value: "form.alert_to_pay",
                            icon: "el-icon-switch",
                        },
                        {
                            label: "Caja General seleccionada por defecto",
                            value: "form.destination_sale",
                            icon: "el-icon-switch",
                        },
                        {
                            label: "Modificar Tipo de afectación (Gravado - Bonificación)",
                            value: "form.change_free_affectation_igv",
                            icon: "el-icon-switch",
                        },
                        {
                            label: "Moneda predeterminada",
                            value: "form.currency_type_id",
                            icon: "el-icon-select",
                        },
                        {
                            label: "Porcentaje retención IGV",
                            value: "form.igv_retention_percentage",
                            icon: "el-icon-input-number",
                        },
                        {
                            label: "Nombre producto PDF para XML",
                            value: "form.name_product_pdf_to_xml",
                            icon: "el-icon-switch",
                        },
                        {
                            label: "Redondear monto de detracción a valor entero",
                            value: "form.detraction_amount_rounded_int",
                            icon: "el-icon-switch",
                        },
                        {
                            label: "Tipo de descuento global",
                            value: "form.global_discount_type_id",
                            icon: "el-icon-select",
                        },
                        {
                            label: "Restringir venta de productos menores al precio de compra",
                            value: "form.validate_purchase_sale_unit_price",
                            icon: "el-icon-switch",
                        },
                        {
                            label: "Restringir venta menores al precio unitario original",
                            value: "form.limit_unit_price_sale",
                            icon: "el-icon-switch",
                        },
                        {
                            label: "Restringir envío de comunicación de baja (RA)",
                            value: "form.restrict_voided_send",
                            icon: "el-icon-switch",
                        },
                        {
                            label: "Días de plazo de envío de la comunicación de baja",
                            value: "form.shipping_time_days_voided",
                            icon: "el-icon-input-number",
                        },
                        {
                            label: "Asignar precio unitario a los productos desde registro relacionado",
                            value: "form.set_unit_price_dispatch_related_record",
                            icon: "el-icon-switch",
                        },
                        {
                            label: "Incluir Leyenda 2001 o 2002 en el XML",
                            value: "form.legend_forest_to_xml",
                            icon: "el-icon-switch",
                        },
                        {
                            label: "Modificar moneda al agregar producto",
                            value: "form.change_currency_item",
                            icon: "el-icon-switch",
                        },
                        {
                            label: "Agregar series al XML - Datos de vehículos",
                            value: "form.register_series_invoice_xml",
                            icon: "el-icon-switch",
                        },
                        {
                            label: "Admitir precio unitario 0.07 del producto",
                            value: "form.price_item_007",
                            icon: "el-icon-switch",
                        },
                        {
                            label: "Activa actualizar precio de venta al realizar una venta",
                            value: "form.default_price_change_item",
                            icon: "el-icon-switch",
                        },
                        {
                            label: "Activa actualizar precio de venta al realizar una compra",
                            value: "form.update_unit_price_sale_in_purchase",
                            icon: "el-icon-switch",
                        },
                        {
                            label: "Activa actualizar precio de compra al realizar una compra",
                            value: "form.default_purchase_price_change_item",
                            icon: "el-icon-switch",
                        },
                    ],
                },
                {
                    label: "PDF",
                    name: "fourth",
                    icon: "fa fa-file-pdf",
                    elements: [
                        {
                            label: "Tamaño de papel en el modal de impresión",
                            value: "form.paper_size_modal_documents",
                        },
                        {
                            label: "Mostrar ticket 80mm",
                            value: "form.show_ticket_80",
                        },
                        {
                            label: "Mostrar ticket 58mm",
                            value: "form.show_ticket_58",
                        },
                        {
                            label: "Mostrar ticket 50mm",
                            value: "form.show_ticket_50",
                        },
                        {
                            label: "Editar nombre de productos",
                            value: "form.edit_name_product",
                        },
                        {
                            label: "Mostrar cotización en finanzas",
                            value: "form.cotizaction_finance",
                        },
                        {
                            label: "Afectacion de terminos y condiciones - ventas en todos los comprobantes",
                            value: "form.affect_all_documents",
                        },
                        {
                            label: "Mostrar leyenda en footer - pdf",
                            value: "form.legend_footer",
                        },
                        {
                            label: "Descripción en pdf",
                            value: "form.name_pdf",
                        },
                        {
                            label: "Presentación en pdf",
                            value: "form.presentation_pdf",
                        },
                        {
                            label: "Código interno cliente en pdf",
                            value: "form.show_internal_code_person",
                        },
                        {
                            label: "Celular del cliente en pdf",
                            value: "form.show_customer_telephone",
                        },
                        {
                            label: "Mostrar precio de venta en compras",
                            value: "form.show_sale_price_pdf",
                        },
                        {
                            label: "Imágen para encabezado - pdf",
                            value: "form.header_image",
                        },
                        {
                            label: "Actualizar documento al generar guía.",
                            value: "form.update_document_on_dispaches",
                        },
                        {
                            label: "Papel membretado",
                            value: "form.background_image",
                        },
                        {
                            label: "Usar la descripcion como nombre del producto PDF",
                            value: "form.item_name_pdf_description",
                        },
                        {
                            label: "Orden de compra logo",
                            value: "form.order_purchase_logo",
                        },
                        {
                            label: "Mostrar Logo por sucursal",
                            value: "form.show_logo_by_establishment",
                        },
                        {
                            label: "Permite imprimir linea en el campo de observación",
                            value: "form.print_new_line_to_observation",
                        },
                        {
                            label: "Mostrar precio en etiqueta",
                            value: "form.show_price_barcode_ticket",
                        },
                        {
                            label: "Modificar cantidad de decimales",
                            value: "form.change_decimal_quantity_unit_price_pdf",
                        },
                        {
                            label: "Cantidad de decimales",
                            value: "form.decimal_quantity_unit_price_pdf",
                        },
                        {
                            label: "Mostrar solo la primera cuota de pago",
                            value: "form.show_the_first_cuota_document",
                        },
                        {
                            label: "Activar columnas personalizadas",
                            value: "form.document_columns",
                        },
                        {
                            label: "Mostrar detalle del producto compuesto",
                            value: "form.show_item_sets",
                        },
                        {
                            label: "Visualizar detalle del comprobante (Facturas y boletas)",
                            value: "form.taxed_igv_visible_doc",
                        },
                        {
                            label: "Visualizar detalle del comprobante (Cotizaciones)",
                            value: "form.taxed_igv_visible_cot",
                        },
                        {
                            label: "Visualizar detalle del comprobante",
                            value: "form.taxed_igv_visible_nv",
                        },
                        {
                            label: "Mostrar fecha de vencimiento",
                            value: "form.date_of_due_pdf",
                        },
                        {
                            label: "Mostrar Qr y detalle de pagos",
                            value: "form.qr_payments_pdf",
                        },
                        {
                            label: "Mostrar información del cliente",
                            value: "form.info_customer_pdf",
                        },
                        {
                            label: "Mostrar ubigeos",
                            value: "form.show_ubigeo",
                        },
                        {
                            label: "Mostrar email de empresa",
                            value: "form.show_email",
                        },
                        {
                            label: "Mostrar dirección de empresa",
                            value: "form.show_company_address",
                        },
                        {
                            label: "Imagenes de lote documentos",
                            value: "form.img_lots_in_documents",
                        },
                        {
                            label: "Imagenes de lote guías",
                            value: "form.img_lots_in_dispatches",
                        },
                        {
                            label: "Kits en PDF",
                            value: "form.kit_pdf",
                        },
                        {
                            label: "Mostrar nombre comercial RUC 10",
                            value: "form.trade_name_pdf",
                        },
                        {
                            label: "Agrega margen inferior",
                            value: "form.add_margin_bottom",
                        },
                        {
                            label: "Margen del pie de pagina",
                            value: "form.footer_margin",
                        },
                        {
                            label: "Ocultar ciertos datos de la detracción",
                            value: "form.detraction_transport_not_require_fields",
                        },
                        {
                            label: "Mostrar peso en guías remitentes",
                            value: "form.show_weigth_dispatches",
                        },
                        {
                            label: "Imprimir ticket de despacho",
                            value: "form.print_dispatch_ticket_pdf",
                        },
                        
                    ],
                },
                {
                    label: "Finanzas",
                    name: "five",
                    icon: "fa fa-money-bill",
                    elements: [
                        {
                            label: "Aplicar penalidad a los pagos vencidos",
                            value: "form.finances.apply_arrears",
                            icon: "el-icon-switch",
                        },
                        {
                            label: "Cantidad a aplicar por día",
                            value: "form.finances.arrears_amount",
                            icon: "el-icon-input",
                        },
                        {
                            label: "Mostrar todas las cuentas por cobrar",
                            value: "form.show_all_unpaid",
                            icon: "el-icon-switch",
                        },
                        {
                            label: "Mostrar todos los reportes de cuentas por cobrar",
                            value: "form.export_detail_unpaid",
                            icon: "el-icon-switch",
                        },
                        {
                            label: "Término de glosa",
                            value: "form.glosa_term",
                            icon: "el-icon-input",
                        },
                        {
                            label: "Mostrar Zona en vez de Motivo - Reporte Pagos",
                            value: "form.show_zone_instead_subject",
                            icon: "el-icon-switch",
                        },
                    ],
                },
                {
                    label: "Datos",
                    name: "six",
                    icon: "fa fa-building",
                    elements: [
                        {
                            label: "Eliminar documentos de prueba",
                            value: "tenant-options-form",
                            icon: "el-icon-component",
                        },
                        {
                            label: "Eliminar catálogo de productos",
                            value: "tenant-options-form",
                            icon: "el-icon-component",
                        },
                        {
                            label: "Restaurar configuración por defecto",
                            value: "tenant-options-form",
                            icon: "el-icon-component",
                        },
                        {
                            label: "Limpiar caché",
                            value: "tenant-options-form",
                            icon: "el-icon-component",
                        },
                        {
                            label: "Eliminar item individualmente",
                            value: "erase_item",
                            icon: "el-icon-component",
                        },
                        {
                            label: "Eliminar documentos",
                            value: "erase_documents",
                            icon: "el-icon-component",
                        },
                        {
                            label: "Eliminar notas de venta",
                            value: "erase_sale_notes",
                            icon: "el-icon-component",
                        },
                    ],
                },
                {
                    label: "Compras",
                    name: "seven",
                    icon: "fa fa-shopping-cart",
                    elements: [
                        {
                            label: "Prorratear compras",
                            value: "form.purchase_apportionment",
                        },
                        {
                            label: "Poder cambiar el IGV global de los items en la compra.",
                            value: "form.enabled_global_igv_to_purchase",
                        },
                        {
                            label: "Seleccionar por defecto Poder cambiar el IGV global de los items en la compra",
                            value: "form.checked_global_igv_to_purchase",
                        },
                        {
                            label: "Actualizar precio de compra",
                            value: "form.checked_update_purchase_price",
                        },
                        {
                            label: "Asignar moneda global de la compra a los items",
                            value: "form.set_global_purchase_currency_items",
                        },
                    ],
                },
                {
                    label: "POS",
                    name: "eight",
                    icon: "fa fa-cash-register",
                    elements: [
                        {
                            label: "% de cargo por visa",
                            value: "form.visa_charge_percentage",
                            icon: "el-icon-switch",
                        },
                        {
                            label: "Cargo por visa",
                            value: "form.visa_charge",
                            icon: "el-icon-switch",
                        },
                        {
                            label: "Habilitar opciones avanzadas",
                            value: "form.options_pos",
                        },
                        {
                            label: "Cantidad de columnas en productos",
                            value: "form.colums_grid_item",
                        },
                        {
                            label: "Seleccionar boleta por defecto",
                            value: "form.default_document_type_03",
                        },
                        {
                            label: "Listar servicios al inicio de Pos",
                            value: "form.show_service_on_pos",
                        },
                        {
                            label: "Seleccionar nota de venta por defecto",
                            value: "form.default_document_type_80",
                        },
                        {
                            label: "Seleccionar cotización por defecto",
                            value: "form.default_document_type_cot",
                        },
                        {
                            label: "Habilitar busqueda con escáner de código de barras",
                            value: "form.search_item_by_barcode",
                        },
                        {
                            label: "Mostrar historial de compras",
                            value: "form.pos_history",
                        },
                        {
                            label: "Mostrar historial de precio de costos",
                            value: "form.pos_cost_price",
                        },
                        {
                            label: "Cantidad decimales POS",
                            value: "form.decimal_quantity",
                        },
                        {
                            label: "Impresión de PDF automática",
                            value: "form.auto_print",
                        },
                        {
                            label: "Ocultar vista previa de PDF",
                            value: "form.hide_pdf_view_documents",
                        },
                        {
                            label: "Mostrar términos y condiciones",
                            value: "form.show_terms_condition_pos",
                        },
                        {
                            label: "Mostrar nombre completo",
                            value: "form.show_complete_name_pos",
                        },
                        {
                            label: "Habilitar restricción para descuento",
                            value: "form.restrict_seller_discount",
                        },
                        {
                            label: "Porcentaje límite de descuento",
                            value: "form.sellers_discount_limit",
                        },
                        {
                            label: "Habilitar vista categorias y productos",
                            value: "form.enable_categories_products_view",
                        },
                        {
                            label: "Habilitar descuentos a vendedores - POS",
                            value: "form.show_discount_seller_pos",
                        },
                        {
                            label: "Habilitar Agente de ventas",
                            value: "form.enabled_sales_agents",
                        },
                        {
                            label: "Modificar tipo de afectación",
                            value: "form.change_affectation_exonerated_igv",
                        },
                        {
                            label: "Activar descuento por cliente",
                            value: "form.enable_discount_by_customer",
                        },
                        {
                            label: "Habilitar ticket de despacho",
                            value: "form.enabled_dispatch_ticket_pdf",
                        },
                        {
                            label: "Agregar producto al seleccionar precio",
                            value: "form.price_selected_add_product",
                        },
                        {
                            label: "Convertir a CPE",
                            value: "form.show_convert_cpe_pos",
                        },
                        {
                            label: "Vendedor por producto",
                            value: "form.multi_sellers",
                        },
                        {
                            label: "Modo pedido",
                            value: "form.order_note_mode",
                        },
                        {
                            label: "Atajos",
                            value: "form.pos_keyboard",
                        },
                        {
                            label: "Agrupar productos",
                            value: "form.group_items",
                        },
                        {
                            label: "Mostrar especificaciones del producto",
                            value: "form.item_complements",
                        },
                    
                        {
                            label: "Venta directa",
                            value: "form.pos_direct",
                        },
                        {
                            label: "POS modo listado",
                            value: "form.pos_mode_pharmacy",

                        },
                        {
                            label: "Presentación en tarjeta",
                            value: "form.list_unit_type_pos",
                        },
                        {
                            label: "Botones de medios de pago",
                            value: "form.list_payments_pos",
                        },
                        {
                            label: "Activar POS ligero",
                            value: "form.show_pos_lite",
                        },
                        {
                            label: "Activar POS Altoque",
                            value: "form.show_pos_lite_v2",
                        },  
                        {
                            label: "Buscar también por modelo y descripción",
                            value: "form.search_by_model_and_description",
                        },
                        {
                            label: "Venta libre",
                            value: "form.pos_quick_sale",
                        },
                    ],
                },
                {
                    label: "Google play",
                    name: "nine",
                    icon: "fa fa-mobile-alt",
                    elements: [
                        {
                            label: "Configuración Taxo",
                            value: "app-configuration-taxo",
                            icon: "el-icon-component",
                        },
                        {
                            label: "Mostrar precio de compra",
                            value: "form.taxo_show_purchase_price",
                            icon: "el-icon-switch",
                        },
                    ],
                },
                {
                    label: "Reportes",
                    name: "ten",
                    icon: "fa fa-file-alt",
                    elements: [
                        {
                            label: "Configuraciones de reportes",
                            value: "report-configurations-index",
                            icon: "el-icon-component",
                        },
                    ],
                },
                {
                    label: "Dashboard",
                    name: "eleven",
                    icon: "fa fa-chart-bar",
                    elements: [
                        {
                            label: "Ventas",
                            value: "form.dashboard_sales",
                            icon: "el-icon-switch",
                        },
                        {
                            label: "Productos",
                            value: "form.dashboard_products",
                            icon: "el-icon-switch",
                        },
                        {
                            label: "Balance general - compras",
                            value: "form.dashboard_general",
                            icon: "el-icon-switch",
                        },
                        {
                            label: "Clientes",
                            value: "form.dashboard_clients",
                            icon: "el-icon-switch",
                        },
                    ],
                },
                {
                    label: "Puntos",
                    name: "tab_point_system",
                    icon: "fa fa-star",
                    elements: [
                        {
                            label: "Habilitar sistema por puntos",
                            value: "form.enabled_point_system",
                            icon: "el-icon-switch",
                        },
                        {
                            label: "Monto de venta",
                            value: "form.point_system_sale_amount",
                            icon: "el-icon-input-number",
                        },
                        {
                            label: "N° de puntos",
                            value: "form.quantity_of_points",
                            icon: "el-icon-input-number",
                        },
                    ],
                },
                {
                    label: "Usuario",
                    name: "twelve",
                    icon: "fa fa-user",
                    elements: [
                        {
                            label: "Recordar cambio de contraseña",
                            value: "form.enabled_remember_change_password",
                            icon: "el-icon-switch",
                        },
                        {
                            label: "N° Meses",
                            value: "form.quantity_month_remember_change_password",
                            icon: "el-icon-input-number",
                        },
                        {
                            label: "Habilitar contraseña segura",
                            value: "form.regex_password_user",
                            icon: "el-icon-switch",
                        },
                    ],
                },
                // {
                //     label: "Restaurant",
                //     name: "restaurant",
                //     icon: "fa fa-utensils",
                //     elements: [
                //         {
                //             label: "Impresión Directa",
                //             value: "form.print_direct",
                //             icon: "el-icon-switch",
                //         },
                //         {
                //             label: "Multiples Caja (Usuario Cajero)",
                //             value: "form.multiple_boxes",
                //             icon: "el-icon-switch",
                //         },
                //         {
                //             label: "Imprimir Comanda(Modulo de Caja)",
                //             value: "form.print_commands",
                //             icon: "el-icon-switch",
                //         },
                //         {
                //             label: "Imprimir en Zona Preparación",
                //             value: "form.print_kitchen",
                //             icon: "el-icon-switch",
                //         },
                //         {
                //             label: "N° Columnas",
                //             value: "form.num_column_kitchen",
                //             icon: "el-icon-input-number",
                //         },
                //     ],
                // },
            ],
            options: [],

            loading: false,
            timer: null,
            useDacta: false,
            headers: headers_token,
            showDialogTermsCondition: false,
            showDialogTermsConditionSales: false,
            showDialogTermsConditionDispatches: false,
            showDialogPdfFooterImages: false,
            showDialogAllowanceCharge: false,
            loading_submit: false,
            resource: "configurations",
            errors: {},
            form: {
                finances: {},
                visual: {},
                dispatches_address_text: false,
                show_edit_button: false,
            },
            affectation_igv_types: [],
            global_discount_types: [],
            placeholder: "",
            activeName: "first",
            warehouses: [],
            showWarningDiscount: false,
            discountTypeId: null,
            discountTypeIdOld: null,
            valueToSearch: null,
        };
    },
    created() {
        this.$store.commit("setConfiguration", this.configuration);
        this.$store.commit("setTypeUser", this.typeUser);
        this.loadConfiguration();
        this.fetchConfiguration();
        this.form = this.config;
        window.addEventListener('resize', () => {
            this.isMobile = window.innerWidth <= 768;
        });
        this.isMobile = window.innerWidth <= 768;
    },
    mounted() {
        let { pse, type_send_pse } = this.company;
        if (pse && type_send_pse == 2) {
            this.useDacta = true;
        }
        this.loadTables();
        this.initForm();
        this.$http.get(`/${this.resource}/record`).then((response) => {
            if (response.data !== "") {
                this.form = response.data.data;
                this.discountTypeIdOld = this.form.global_discount_type_id;
                if (
                    this.form.main_warehouse == null &&
                    this.warehouses.length > 0
                ) {
                    this.form.main_warehouse = this.warehouses[0].id;
                }
                this.$store.commit("setConfiguration", this.form);
            }
            // console.log(this.placeholder)
        });

        this.events();
    },
    methods: {
        moveToValue(option) {
            if (!option) return;
            let { name, value } = option;
            this.activeName = name;

            let element = document.getElementById(value);
            if (!element) {
                return;
            }

            // Scroll suave
            setTimeout(() => {
                element.scrollIntoView({ behavior: "smooth" });
            }, 100);

            // Agregar clase y empezar animación
            setTimeout(() => {
                element.classList.add("selected-container");
                this.startTextAnimation(element);
            }, 100);

            // Remover clase después de 5 segundos
            setTimeout(() => {
                element.classList.remove("selected-container");
                this.stopTextAnimation(element);
            }, 6000);

            this.valueToSearch = null;
        },
        startTextAnimation(element) {
            const label = element.querySelector('label');
            if (!label) return;
            
            console.log('Iniciando animación en:', label.textContent); // Debug
            
            // Primero poner en negrita
            label.style.setProperty('font-weight', '700', 'important');
            label.style.setProperty('color', '#303133', 'important'); // Negro
            
            let isBlue = false;
            let animationInterval = setInterval(() => {
                if (isBlue) {
                    label.style.setProperty('color', '#303133', 'important'); // Negro
                    console.log('Cambiando a negro');
                } else {
                    label.style.setProperty('color', '#FF0000', 'important'); // Rojo
                    console.log('Cambiando a azul');
                }
                isBlue = !isBlue;
            }, 500); // Cambiar cada 1 segundo
            
            // Guardar el intervalo para poder detenerlo después
            element._animationInterval = animationInterval;
        },
        
        // Método para detener la animación
        stopTextAnimation(element) {
            const label = element.querySelector('label');
            if (!label) return;
            
            console.log('Deteniendo animación'); // Debug
            
            // Limpiar estilos
            label.style.removeProperty('font-weight');
            label.style.removeProperty('color');
            
            // Detener el intervalo
            if (element._animationInterval) {
                clearInterval(element._animationInterval);
                delete element._animationInterval;
            }
        },
        remoteMethod(query) {
            if (!query) {
                this.options = [];
                return;
            }

            this.options = [];
            this.tabPanesConfig.forEach((tab) => {
                tab.elements.forEach((element) => {
                    if (
                        element.label
                            .toLowerCase()
                            .includes(query.toLowerCase())
                    ) {
                        this.options.push({
                            icon: tab.icon,
                            label: element.label,
                            container: tab.label,
                            name: tab.name,
                            value: element.value,
                        });
                    }
                });
            });

            // this.options = this.options.slice(0, 6);
        },
        getAppConfigurationTaxoRole() {
            this.$http.get("/configurations/taxo-role").then((response) => {
                console.log(response.data);
            });
        },
        updateDiscountTypeId(discountTypeId) {
            console.log("envian", discountTypeId);
            this.form.global_discount_type_id = discountTypeId;
            this.discountTypeIdOld = discountTypeId;
        },
        checkDiscountAjustmentByIgvAffectation() {
            this.discountTypeId = this.form.global_discount_type_id.toString();

            this.$refs.globalDiscountType.blur();
            setTimeout(() => {
                this.showWarningDiscount = true;
            }, 150);
        },
        submitTimer() {
            clearTimeout(this.timer);
            this.timer = setTimeout(() => {
                this.submit();
            }, 1000);
        },

        fetchConfiguration() {
            this.$http.get("/api/configuration").then((response) => {
                this.showEditButton = response.data.show_edit_button;
                this.form.show_edit_button = response.data.show_edit_button;
            });
        },
        updateShowEditButton() {
            this.$http
                .post("/api/configuration/update-show-edit-button", {
                    show_edit_button: this.form.show_edit_button,
                })
                .then((response) => {
                    this.$message({
                        message: response.data.message,
                        type: "success",
                    });
                })
                .catch((error) => {
                    this.errors = error.response.data.errors;
                });
        },
        async removeMembrete() {
            this.form.background_image = null;
            await this.submit();
        },
        async removeOrderPurchaseLogo() {
            this.form.order_purchase_logo = null;
            await this.submit();
        },
        async removeImageHeader() {
            this.form.header_image = null;
            await this.submit();
        },
        ...mapActions(["loadConfiguration"]),
        events() {
            this.$eventHub.$on("submitFormConfigurations", (form) => {
                this.form = form;
                this.submit();
            });
        },
        successUpload(response, file, fileList) {
            if (response.success) {
                this.$message.success(response.message);
                this.getRecord();
                this.form[response.type] = response.name;
            } else {
                this.$message({
                    message: "Error al subir el archivo",
                    type: "error",
                });
            }
        },
        async getRecord() {
            await this.$http
                .get(`/${this.resource}/record`)
                .then((response) => {
                    if (response.data !== "") {
                        this.form = response.data.data;
                        if (
                            this.form.main_warehouse == null &&
                            this.warehouses.length > 0
                        ) {
                            this.form.main_warehouse = this.warehouses[0].id;
                        }
                    }
                    // console.log(this.placeholder)
                });
        },
        async loadTables() {
            await this.$http
                .get(`/${this.resource}/tables`)
                .then((response) => {
                    this.warehouses = response.data.warehouses;
                    this.affectation_igv_types =
                        response.data.affectation_igv_types;
                    this.global_discount_types =
                        response.data.global_discount_types;
                });
        },
        initForm() {
            this.errors = {};
            this.form = {
                main_warehouse: 1,
                send_auto: true,
                stock: true,
                cron: true,
                id: null,
                sunat_alternate_server: false,
                subtotal_account: null,
                decimal_quantity: null,
                amount_plastic_bag_taxes: 0.1,
                colums_grid_item: 4,
                affectation_igv_type_id: "10",
                global_discount_type_id: "03",
                terms_condition: null,
                terms_condition_dispatches: null,
                header_image: null,
                legend_footer: false,
                default_document_type_03: false,
                default_document_type_80: false,
                search_item_by_barcode: false,
                destination_sale: false,
                quotation_allow_seller_generate_sale: false,
                allow_edit_unit_price_to_seller: false,
                seller_can_create_product: false,
                seller_can_generate_sale_opportunities: false,
                seller_can_view_balance: true,
                finances: {},
                visual: {},
                show_ticket_80: true,
                show_ticket_58: false,
                show_ticket_50: false,
                update_document_on_dispaches: false,
                auto_send_dispatchs_to_sunat: true,
                is_pharmacy: false,
                active_warehouse_prices: false,
                search_item_by_series: false,
                change_free_affectation_igv: false,
                select_available_price_list: false,
                show_pdf_name: this.config.show_pdf_name,
                dispatches_address_text: this.config.dispatches_address_text,
                group_items_generate_document: false,
                enabled_global_igv_to_purchase:
                    this.config.enabled_global_igv_to_purchase,
                set_address_by_establishment: false,
                permission_to_edit_cpe: false,
                name_product_pdf_to_xml: false,
                detraction_amount_rounded_int: false,
                validate_purchase_sale_unit_price: false,
                show_logo_by_establishment: false,
                shipping_time_days: 0,
                customer_filter_by_seller: false,
                checked_global_igv_to_purchase: false,
                checked_update_purchase_price: false,
                set_global_purchase_currency_items: false,
                set_unit_price_dispatch_related_record: false,
                restrict_voided_send: false,
                shipping_time_days_voided: 0,
                enabled_tips_pos: false,
                legend_forest_to_xml: false,

                change_currency_item: false,
                enabled_advanced_records_search: false,
                change_decimal_quantity_unit_price_pdf: false,
                decimal_quantity_unit_price_pdf: false,
                separate_cash_transactions: false,
                order_cash_income: false,
                generate_order_note_from_quotation: false,
                list_items_by_warehouse: false,
                regex_password_user: false,
                enabled_remember_change_password: false,
                quantity_month_remember_change_password: 0,

                ticket_single_shipment: false,
                hide_pdf_view_documents: false,

                dashboard_sales: true,
                dashboard_products: false,
                dashboard_general: true,
                dashboard_clients: true,

                affect_all_documents: false,
                restrict_series_selection_seller: false,

                enabled_point_system: false,
                round_points_of_sale: false,
                point_system_sale_amount: 0,
                quantity_of_points: 0,

                show_complete_name_pos: false,
                enable_categories_products_view: false,
                restrict_seller_discount: false,
                sellers_discount_limit: 0,
                enabled_sales_agents: false,
                change_affectation_exonerated_igv: false,
                show_load_voucher: false,
                search_factory_code_items: false,
                register_series_invoice_xml: false,
                enable_discount_by_customer: false,
                enabled_dispatch_ticket_pdf: false,
                price_selected_add_product: false,
                restrict_sale_items_cpe: false,
                show_convert_cpe_pos: false,
                view_tutorials: false,
                label_item_color: false,
                chatboot: false,

                print_direct: false,
                multiple_boxes: false,
                print_commands: false,
                print_kitchen: false,
                num_column_kitchen: 4,
            };
        },
        UpdateFormPurchase(e) {
            //Añadir la variable para cada item en compra. No es posible pasar elemento form por vuex
            this.form.enabled_global_igv_to_purchase =
                this.config.enabled_global_igv_to_purchase;
            this.submit();
        },
        submitConfigPurchase() {
            this.submit();
        },
        changeDefaultDocumentTypeCot() {
            if (this.form.default_document_type_cot) {
                this.form.default_document_type_80 = false;
                this.form.default_document_type_03 = false;
            }
            this.submit();
        },
        changeDefaultDocumentType03() {
            if (this.form.default_document_type_03) {
                this.form.default_document_type_80 = false;
                this.form.default_document_type_cot = false;
            }
            this.submit();
        },
        changeDefaultDocumentType80() {
            if (this.form.default_document_type_80) {
                this.form.default_document_type_03 = false;
                this.form.default_document_type_cot = false;
            }
            this.submit();
        },
        submit() {
            this.loading_submit = true;
            if (this.form.add_margin_bottom == null) {
                this.form.add_margin_bottom = 0;
            }
            if (this.form.footer_margin == null) {
                this.form.footer_margin = 2;
            }
            this.$http
                .post(`/${this.resource}`, this.form)
                .then((response) => {
                    let data = response.data;
                    if (data.success) {
                        this.$message.success(data.message);
                    } else {
                        this.$message.error(data.message);
                    }
                    if (
                        data !== undefined &&
                        data.configuration !== undefined
                    ) {
                        this.$store.commit(
                            "setConfiguration",
                            data.configuration
                        );
                    }
                })
                .catch((error) => {
                    if (error.response.status === 422) {
                        this.errors = error.response.data.errors;
                    } else {
                        console.log(error);
                    }
                })
                .then(() => {
                    this.loading_submit = false;
                });
        },
        changeAmountPlasticBagTaxes() {
            this.loading_submit = true;

            this.$http
                .post(`/${this.resource}/icbper`, this.form)
                .then((response) => {
                    if (response.data.success) {
                        this.$message.success(response.data.message);
                    } else {
                        this.$message.error(response.data.message);
                    }
                })
                .catch((error) => {
                    if (error.response.status === 422) {
                        this.errors = error.response.data.errors;
                    } else {
                        console.log(error);
                    }
                })
                .then(() => {
                    this.loading_submit = false;
                });
        },
        errorUpload(error) {
            console.log(error);
            this.$message({
                message: "Error al subir el archivo",
                type: "error",
            });
        },
    },
};
