// import ProductImage from "../components/product_image.vue";
const ProductImage = () => import("../components/product_image.vue");
// import DiscountPermission from "./partials/discounts_permission_form.vue";
// const DiscountPermission = () => import("./partials/discounts_permission_form.vue");
// import DocumentFormItem from "./partials/item.vue";
// const DocumentFormItem = () => import("./partials/item.vue");
// import PersonForm from "../persons/form.vue";
// const PersonForm = () => import("../persons/form.vue");
import DocumentOptions from "../documents/partials/options.vue";
// const DocumentOptions = () => import("../documents/partials/options.vue");
// import ModalPlateNumber from "../components/modal_plate_number.vue";
// const ModalPlateNumber = () => import("../components/modal_plate_number.vue");
import {
    exchangeRate,
    functions,
    pointSystemFunctions,
    fnRestrictSaleItemsCpe,
    cash,
    advance,
} from "../../../mixins/functions";
import {
    calculateRowItem,
    calculateRowItemQuotation,
    showNamePdfOfDescription,
} from "../../../helpers/functions";
// import Logo from "../companies/logo.vue";
const Logo = () => import("../companies/logo.vue");
// import DocumentHotelForm from "../../../../../modules/BusinessTurn/Resources/assets/js/views/hotels/form.vue";
// const DocumentHotelForm = () => import("../../../../../modules/BusinessTurn/Resources/assets/js/views/hotels/form.vue");
// const DocumentTransportForm = () =>
//     import(
//         "../../../../../modules/BusinessTurn/Resources/assets/js/views/transports/form.vue"
//     );
// const DocumentDispatchForm = () =>
//     import(
//         "../../../../../modules/BusinessTurn/Resources/assets/js/views/transports/dispatch_form.vue"
//     );
import DocumentDetraction from "./partials/detraction.vue";
// const DocumentDetraction = () => import("./partials/detraction.vue");
import moment from "moment";
import { mapActions, mapState } from "vuex/dist/vuex.mjs";
import Keypress from "vue-keypress";
// import StoreItemSeriesIndex from "../Store/ItemSeriesIndex";
// const StoreItemSeriesIndex = () => import("../Store/ItemSeriesIndex");
// import DocumentReportCustomer from "./partials/report_customer.vue";
// const DocumentReportCustomer = () => import("./partials/report_customer.vue");
// import SetTip from "@components/SetTip.vue";
const SetTip = () => import("@components/SetTip.vue");
// import ResultExcelProducts from "@components/ResultExcelProducts.vue";
// const ResultExcelProducts = () => import("@components/ResultExcelProducts.vue");
// import WarehousesDetail from "./../items/partials/warehouses.vue";
// const WarehousesDetail = () => import("./../items/partials/warehouses.vue");
// import InvoiceParkedSalesDialog from "./partials/InvoiceParkedSalesDialog.vue";
// const InvoiceParkedSalesDialog = () => import("./partials/InvoiceParkedSalesDialog.vue");
// import InvoiceParkSaleDialog from "./partials/InvoiceParkSaleDialog.vue";
// const InvoiceParkSaleDialog = () => import("./partials/InvoiceParkSaleDialog.vue");
// import DocumentFormPreview from "./partials/preview.vue";
// const DocumentFormPreview = () => import("./partials/preview.vue");
export default {
    name: "DocumentInvoiceGenerate",
    props: [
        "type_quotation",
        "idUser",
        "establishment_auth",
        "typeUser",
        "configuration",
        "documentId",
        "is_integrate_system",
        "table",
        "tableId",
        "isUpdate",
        "authUser",
        "copy",
        "suscriptionames",
        "quotations_optional",
        "quotations_optional_value",
        "api_token",
    ],
    components: {
        // ModalPlateNumber,
        // DiscountPermissionForm,
        // DocumentFormPreview,
        // DiscountPermission,
        // StoreItemSeriesIndex,
        // DocumentFormItem,
        // PersonForm,
        DocumentOptions,
        Logo,
        // DocumentHotelForm,
        Keypress,
        DocumentDetraction,
        // DocumentTransportForm,
        // DocumentReportCustomer,
        SetTip,
        // ResultExcelProducts,
        // WarehousesDetail,
        // DocumentDispatchForm,
        ProductImage,
        // InvoiceParkedSalesDialog,
        // InvoiceParkSaleDialog,
    },
    mixins: [
        functions,
        exchangeRate,
        pointSystemFunctions,
        fnRestrictSaleItemsCpe,
        cash,
        advance,
    ],
    data() {
        return {
            user_default_document_types:[],
            dialogNewPerson:null,
            dialogPlateNumber:null,
            addItemDialog:null,
            channels: [],
            user_filters_set_items_type: false,
            user_filters_set_items: false,
            notes_credit: [],
            elementToAdjust: "total_taxed",
            timerPrepayment: null,
            timerFee: null,
            loading_check_discount_type: false,
            discounts_categories: [],
            discounts_specific: [],
            discounts_all: [],
            discounts_brands: [],
            discounts_specific_items: false,
            discountSelectedId: null,
            isAddingDiscount: false,
            discounts_manual: [],
            showDialogPreview: false,
            dialogPreview:null,
            discount_add_igv: true,
            showDialogImage: false,
            currentImage: null,
            timer: null,
            new_customer_address: null,
            new_customer_trade_name: null,
            advance_item_id: null,

            isEmiting: false,
            itemExtraPercentages: [],
            currentIndex: null,
            currentDiscounts: [],
            totalDiscountPercentage: 0,
            showDialogDiscountPermission: false,
            dialogDiscountPermission:null,
            dispatchers: [],
            lastInput: null,
            unit_types: [],
            form_item_quick: {},
            person_dispatchers: [],
            person_packers: [],
            totalQuantity: 0,
            hasPendingCredits: false,
            showDialogExchangeRate: false,
            // fromSaleNotePayed:false,
            colspan: 9,
            payed: false,
            showAll: true,
            activeNames: ["1"],
            is_amount_charge: true,
            cuotaNumber: 1,
            cuotaDays: 30,
            cuotaPeriods: [
                {
                    id: 1,
                    label: "15 días",
                    days: 15,
                },
                {
                    id: 2,
                    label: "30 días",
                    days: 30,
                },
                {
                    id: 3,
                    label: "45 días",
                    days: 45,
                },
                {
                    id: 4,
                    label: "60 días",
                    days: 60,
                },
            ],
            showDialogFormDispatch: false,
            dialogFormDispatch:null,
            warehousesDetail: [],
            item_unit_types: [],
            showWarehousesDetail: false,
            dialogWarehousesDetail:null,
            favorite_item_id: null,
            loading_search_favorite: false,
            loading_search_advance: false,
            pickerOptions: {
                disabledDate(time) {
                    return time.getTime() < Date.now();
                },
            },
            favoriteItems: [],
            advanceItems: [],
            isEditingItems: false,
            companies: [],
            hash: null,
            showResultExcelProducts: false,
            dialogResultExcelProducts:null,
            registered: 0,
            errors: [],
            split_base: true,
            loading: false,
            person_type_id: null,
            cash_id: null,
            children: [],
            monthsSelected: [],
            monthsCollege: [
                { value: 1, label: "Ene" },
                { value: 2, label: "Feb" },
                { value: 3, label: "Mar" },
                { value: 4, label: "Abr" },
                { value: 5, label: "May" },
                { value: 6, label: "Jun" },
                { value: 7, label: "Jul" },
                { value: 8, label: "Ago" },
                { value: 9, label: "Set" },
                { value: 10, label: "Oct" },
                { value: 11, label: "Nov" },
                { value: 12, label: "Dic" },
            ],
            datEmision: {
                disabledDate(time) {
                    return time.getTime() > moment();
                },
            },
            multiple: [
                {
                    keyCode: 78, // N
                    modifiers: ["altKey"],
                    preventDefault: true,
                },
                {
                    keyCode: 71, // g
                    modifiers: ["altKey"],
                    preventDefault: true,
                },
            ],
            monthCollege: [],
            collegeYear: 20,
            decimalQuantity: 2,
            focus_on_client: false,
            dateValid: false,
            input_person: {},
            showDialogDocumentDetraction: false,
            dialogDocumentDetraction:null,
            has_data_detraction: false,
            showDialogFormHotel: false,
            dialogFormHotel:null,
            showDialogFormTransport: false,
            dialogFormTransport:null,
            showDialogItemSeriesIndex: false,
            dialogItemSeriesIndex:null,
            is_client: false,
            recordItem: null,
            resource: "documents",
            showDialogAddItem: false,
            showDialogNewPerson: false,
            showDialogOptions: false,
            dialogOptions:null,
            loading_submit: false,
            // Progressive loading states
            dataLoaded: {
                company: false,
                documents: false,
                customers: false,
                items: false,
                complete: false
            },
            loadingProgress: 0,
            currentLoadingStep: 'Inicializando...',
            errors: {},
            form: {
                token_validated_for_discount: false,
            },
            document_types: [],
            currency_types: [],
            discount_types: [],
            charges_types: [],
            all_customers: [],
            business_turns: [],
            form_payment: {},
            document_types_guide: [],
            customers: [],
            sellers: [],
            company: null,
            document_type_03_filter: null,
            operation_types: [],
            establishments: [],
            all_establishments: [],
            payment_method_types: [],
            payment_method_types_credit: [],
            establishment: null,
            // all_series: [],
            // series: [],
            prepayment_documents: [],
            currency_type: {},
            documentNewId: null,
            prepayment_deduction: false,
            activePanel: 0,
            total_global_discount: 0,
            total_global_discount_aux: 0,
            total_global_charge: 0,
            loading_search: false,
            is_amount: true,
            enabled_discount_global: false,
            user: null,
            is_receivable: false,

            is_contingency: false,
            cat_payment_method_types: [],
            select_first_document_type_03: false,
            detraction_types: [],
            all_detraction_types: [],
            customer_addresses: [],
            payment_destinations: [],
            form_cash_document: {},
            enabled_payments: true,
            readonly_date_of_due: false,
            seller_class: "col-lg-6 pb-2",
            btnText: "Generar",
            payment_conditions: [],
            affectation_igv_types: [],
            total_discount_no_base: 0,
            show_has_retention: true,
            global_discount_types: [],
            global_discount_type: {},
            error_global_discount: false,
            headers_token: headers_token,
            showDialogReportCustomer: false,
            dialogReportCustomer: null,
            report_to_customer_id: null,
            retention_query_data: null,
            yearCollegeName: null,
            timer: null,
            showParkedInvoicesDialog: false,
            parkedInvoicesDialog: null,
            showParkInvoiceDialog: false,
            dialogParkInvoice:null,
            showDialogPlateNumber: false,
            plateNumberOptions: [],
            searchTimeout: null,
            item_unit_types: [],
            adjustmentCentElements: {},
            showAdjustmentCent: false,
        };
    },
    computed: {
        isDataFullyLoaded() {
            return this.dataLoaded.company &&
                   this.dataLoaded.documents &&
                   this.dataLoaded.customers &&
                   this.dataLoaded.items &&
                   this.dataLoaded.complete;
        },
        hasSetItems() {
            return this.form.items.some((item) => item.item.is_set);
        },
        disabledPurchaseOrder() {
            let toReturn =
                this.documentId &&
                this.configuration.purchase_orden_in_item_set &&
                this.hasSetItems;
            if (toReturn === 0) {
                return false;
            }
            return toReturn;
        },
        nc_payment_nv() {
            return this.configuration.nc_payment_nv;
        },
        glosa_term() {
            return this.configuration.glosa_term || "Glosa";
        },
        list_items_by_warehouse() {
            return this.configuration.item_unit_type_by_warehouse;
        },
        allPayments() {
            return this.form.payments.reduce(
                (acc, payment) => acc + Number(payment.payment),
                0
            );
        },
        difference() {
            let total = this.form.total;
            let payments = this.form.payments.reduce(
                (acc, payment) => acc + Number(payment.payment),
                0
            );
            return payments - total;
        },
        warningCertificateDue() {
            if (!this.certificateDue) return null;

            let now = moment();
            let certificateDue = moment(this.certificateDue);

            if (certificateDue.isSameOrBefore(now)) {
                return {
                    type: "danger",
                    message:
                        "EL CERTIFICADO DIGITAL VENCIÓ EL DÍA " +
                        certificateDue.format("DD/MM/YYYY"),
                };
            }

            let daysToExpire = certificateDue.diff(now, "days");
            if (daysToExpire <= 5) {
                return {
                    type: "warning",
                    message: `EL CERTIFICADO DIGITAL VENCERÁ EN ${daysToExpire} DÍA${
                        daysToExpire === 1 ? "" : "S"
                    }`,
                };
            }

            return null;
        },
        certificateDue() {
            if (this.configuration.multi_companies) {
                let company = this.companies.find(
                    (company) => company.id == this.form.company_id
                );
                if (company) {
                    return company.certificate_due;
                }
            }
            if (this.company) {
                return this.company.certificate_due;
            }
            return null;
        },
        isForSplit() {
            let customer = this.customers.find((customer) => {
                return customer.id == this.form.customer_id;
            });

            return (
                this.form.total > 700 &&
                customer &&
                customer.identity_document_type_id == "0"
            );
        },
        blockAddPayments() {
            return (
                this.form.payments.filter(
                    (payment) => payment.payment_destination_id === "advance"
                ).length > 0
            );
        },
        getDisplayedRows() {
            return this.showAll
                ? this.form.fee
                : this.form.fee.slice(0, 5).concat(this.form.fee.slice(-5));
        },
        isGeneratedFromExternal() {
            return (
                this.table != undefined &&
                this.table &&
                this.tableId != undefined &&
                this.tableId
            );
        },
        showLoadVoucher() {
            return (
                this.configuration.show_load_voucher && !this.isUpdateDocument
            );
        },
        isGlobalDiscountBase: function () {
            return this.configuration.global_discount_type_id === "02";
        },
        ...mapState(["config", "series", "all_series"]),
        credit_payment_metod: function () {
            return _.filter(this.payment_method_types_credit, { is_credit: 1 });
        },
        cash_payment_metod: function () {
            return _.filter(this.payment_method_types, { is_credit: 0 });
        },
        existDiscountsNoBase: function () {
            return this.total_discount_no_base > 0 ? true : false;
        },
        isUpdateDocument: function () {
            return this.documentId ? true : false;
        },
        isCreditPaymentCondition: function () {
            return ["02", "03"].includes(this.form.payment_condition_id);
        },
        detractionDecimalQuantity: function () {
            return this.configuration.detraction_amount_rounded_int ? 0 : 2;
        },
        isAutoPrint: function () {
            if (this.configuration) {
                return this.configuration.auto_print;
            }

            return false;
        },
        hidePreviewPdf: function () {
            if (this.configuration) {
                return this.configuration.hide_pdf_view_documents;
            }

            return false;
        },
    },

    async created() {
        this.showMessagePay();
        this.initQuickFormItem();
        if (this.configuration.show_item_stock) {
            this.colspan += 1;
        }
        if (this.configuration.show_item_discounts) {
            this.colspan += 1;
        }
        if (this.configuration.show_purchase_unit_price) {
            this.colspan += 1;
        }
        if (this.configuration.discount_unit_document) {
            this.colspan += 1;
        }
        if (this.configuration.type_discount) {
            this.colspan += 1;
        }

        this.loadConfiguration();

        this.$store.commit("setConfiguration", this.configuration);

        this.isEditingItems = this.configuration.edit_info_documents;
        let { decimal_quantity } = this.configuration;
        if (decimal_quantity) {
            this.decimalQuantity = decimal_quantity;
        }
        await this.initForm();
        this.searchRemoteItems();

        // Progressive loading implementation
        await this.loadDataProgressively();
        // this.default_document_type = response.data.document_id;
        // this.default_series_type = response.data.series_id;
        this.selectDocumentType();

        this.changeEstablishment();
        this.changeDocumentType();
        this.changeDestinationSale();
        this.changeCurrencyType();
        this.setDefaultDocumentType();
        this.changeDateOfIssue();
        this.setConfigGlobalDiscountType();
        await this.getPercentageIgv();

        this.$eventHub.$on("reloadDataPersons", (customer_id) => {
            this.reloadDataCustomers(customer_id);
        });
        this.$eventHub.$on("initInputPerson", () => {
            this.initInputPerson();
        });
        if (this.documentId) {
            this.btnText = this.isUpdate == true ? "Actualizar" : "Generar";
            this.loading_submit = true;

            await this.$http
                .get(`/documents/${this.documentId}/show`)
                .then((response) => {
                    if (!this.isUpdate) {
                        response.data.data.number = "#";
                    }
                    this.onSetFormData(response.data.data);
                })
                .finally(() => (this.loading_submit = false));
        }

        /*
         * #830
         */
        if (this.table) {
            await this.changeDateOfIssue();
            await this.$http
                .get(
                    `/store/record/${this.table}/${this.tableId}?type_quotation=${this.type_quotation}`
                )
                .then((response) => {
                    this.onSetFormData(response.data.data);
                })
                .finally(() => (this.loading_submit = false));
        }
        this.getUserFiltersSetItems();

        /*
         * #830
         */

        const itemsFromDispatches = localStorage.getItem("items");
        if (itemsFromDispatches) {
            const itemsParsed = JSON.parse(itemsFromDispatches);
            const items = itemsParsed.map((i) => i.item_id);
            const params = {
                items_id: items,
            };
            localStorage.removeItem("items");
            await this.$http
                .get("/documents/search-items", { params })
                .then((response) => {
                    const itemsResponse = response.data.items.map((i) => {
                        return this.setItemFromResponse(i, itemsParsed, true);
                    });
                    this.form.items = itemsResponse.map((i) => {
                        return calculateRowItem(
                            i,
                            this.form.currency_type_id,
                            this.form.exchange_rate_sale,
                            this.percentage_igv
                        );
                    });
                });
        }

        const itemsFromNotes = localStorage.getItem("itemsForNotes");
        if (itemsFromNotes) {
            const itemsParsed = JSON.parse(itemsFromNotes);
            const items = itemsParsed.map((i) => i.id);
            const params = {
                items_id: items,
            };
            localStorage.removeItem("itemsForNotes");
            await this.$http
                .get("/documents/search-items", { params })
                .then((response) => {
                    const itemsResponse = response.data.items.map((i) => {
                        return this.setItemFromResponse(i, itemsParsed);
                    });
                    this.form.items = itemsResponse.map((i) => {
                        return calculateRowItem(
                            i,
                            this.form.currency_type_id,
                            this.form.exchange_rate_sale,
                            this.percentage_igv
                        );
                    });
                });
        }

        //parse items from multiple sale notes not group
        this.processItemsForNotesNotGroup();

        const clientfromDispatchesOrNotes = localStorage.getItem("client");
        if (clientfromDispatchesOrNotes) {
            const client = JSON.parse(clientfromDispatchesOrNotes);
            if (client.identity_document_type_id == 1) {
                this.form.document_type_id = "03";
            } else if (client.identity_document_type_id == 6) {
                this.form.document_type_id = "01";
            }
            this.searchRemoteCustomers(client.number);
            this.form.customer_id = client.id;
            this.changeEstablishment();
            this.filterSeries();
            this.filterCustomers();
            this.changeCurrencyType();
            localStorage.removeItem("client");
        }

        if (this.configuration.show_channels_documents) {
            this.getChannels();
        }

        const dispatchesNumbersFromDispatches =
            localStorage.getItem("dispatches");
        if (dispatchesNumbersFromDispatches) {
            this.form.dispatches_relateds = JSON.parse(
                dispatchesNumbersFromDispatches
            );
            localStorage.removeItem("dispatches");
        }
        const notesNumbersFromNotes = localStorage.getItem("notes");
        if (notesNumbersFromNotes) {
            this.form.sale_notes_relateds = JSON.parse(notesNumbersFromNotes);
            localStorage.removeItem("notes");
        }
        this.startConnectionQzTray();
        if (this.copy == true) {
            this.form.id = null;
            this.form.number = "#";
            this.form.date_of_issue = moment().format("YYYY-MM-DD");
        }
        this.formatTooltip(20);

        // Preload item component to avoid loading delay when opening modal
        this.preloadItemComponent();
    },
    methods: {
        changeSeries(){
            if(this.configuration.seller_establishments_all){
                return;
            }
            let serie_id = this.form.series_id;
            let serie = this.series.find(serie => serie.id === serie_id);
            let establishment_id = serie.establishment_id;
            this.$nextTick(() => {
                this.form.establishment_id = establishment_id;
            });
        },
        seriesCritical(debugData = null){
            const postData = {
                debug_data: debugData
            };
            
            this.$http.post(`/${this.resource}/series-critical`, postData).then((response) => {
                this.$store.commit("setAllSeries", response.data.series);
                // console.log(response.data.series);

            });
        },
        // Progress management methods
        formatProgress(percentage) {
            return `${percentage}%`;
        },

        updateProgress(step, percentage, message) {
            this.loadingProgress = percentage;
            this.currentLoadingStep = message;
        },

        async loadDataProgressively() {
            try {
                // Step 1: Load CRITICAL data first (fast response)
                this.updateProgress('critical', 20, 'Cargando datos críticos...');
                // Remove main loading - let skeletons handle individual sections

                const criticalResponse = await this.$http.get(`/${this.resource}/tables-critical`);

                // Load critical data immediately
                this.user_default_document_types = criticalResponse.data.user_default_document_types || [];
                this.companies = criticalResponse.data.companies || [];
                this.establishments = criticalResponse.data.establishments || [];
                this.all_establishments = criticalResponse.data.all_establishments || [];
                this.company = criticalResponse.data.company;
                this.user = criticalResponse.data.user;
                this.document_types = criticalResponse.data.document_types_invoice || [];
                this.currency_types = criticalResponse.data.currency_types || [];
                this.operation_types = criticalResponse.data.operation_types || [];
                this.sellers = criticalResponse.data.sellers || [];
                this.document_type_03_filter = criticalResponse.data.document_type_03_filter;
                this.select_first_document_type_03 = criticalResponse.data.select_first_document_type_03;

                // Store critical series data
                this.$store.commit("setAllSeries", criticalResponse.data.series || []);
                // this.all_series = criticalResponse.data.series || [];

                // Set company defaults
                if (this.companies.length > 0) {
                    let companyDefault = this.companies.find((company) => company.default);
                    if (companyDefault) {
                        this.form.company_id = companyDefault.website_id;
                        // Call changeCompany without loading to avoid UI interference
                        this.changeCompany(true);
                    }
                }

                // Set form defaults from critical data
                this.form.establishment_id = this.establishments.length > 0 ? this.establishments[0].id : null;
                this.form.document_type_id = this.document_types.length > 0 ? this.document_types[0].id : null;
                this.form.operation_type_id = this.operation_types.length > 0 ? this.operation_types[0].id : null;
                this.form.seller_id = this.sellers.length > 0 ? this.idUser : null;

                // Initialize establishment after setting establishment_id
                this.changeEstablishment();

                this.updateProgress('critical', 50, 'Datos críticos cargados');
                this.dataLoaded.company = true;
                this.dataLoaded.documents = true;
                this.dataLoaded.customers = true; // Sellers are already loaded

                // Critical data loaded - form sections will show via dataLoaded flags
                // No need to control main loading anymore

                // Step 2: Load SECONDARY data in background (slower response)
                this.updateProgress('secondary', 75, 'Cargando datos adicionales...');

                // Start secondary data load in parallel
                const secondaryPromise = this.$http.get(`/${this.resource}/tables-secondary`);

                // Continue with critical initialization while secondary loads
                this.seller_class = this.user == "admin" ? "col-lg-4 pb-2" : "col-lg-6 pb-2";

                // Wait for secondary data
                const secondaryResponse = await secondaryPromise;

                // Load all secondary data
                this.all_customers = secondaryResponse.data.customers || [];
                this.unit_types = secondaryResponse.data.unit_types || [];
                this.discounts_categories = secondaryResponse.data.discounts_categories || [];
                this.discounts_brands = secondaryResponse.data.discounts_brands || [];
                this.discounts_specific = secondaryResponse.data.discounts_specific || [];
                this.discounts_all = secondaryResponse.data.discounts_all || [];
                this.discounts_specific_items = secondaryResponse.data.discounts_specific_items || false;
                this.discounts_manual = secondaryResponse.data.discounts_manual || [];
                this.dispatchers = secondaryResponse.data.dispatchers || [];
                this.person_packers = secondaryResponse.data.person_packers || [];
                this.person_dispatchers = secondaryResponse.data.person_dispatchers || [];
                this.document_types_guide = secondaryResponse.data.document_types_guide || [];
                this.discount_types = secondaryResponse.data.discount_types || [];
                this.charges_types = secondaryResponse.data.charge_types || [];
                this.payment_method_types = secondaryResponse.data.payment_method_types || [];
                this.payment_method_types_credit = secondaryResponse.data.payment_method_types_credit || [];
                this.enabled_discount_global = secondaryResponse.data.enabled_discount_global || false;
                this.is_client = secondaryResponse.data.is_client || false;
                this.payment_destinations = secondaryResponse.data.payment_destinations || [];
                this.payment_conditions = secondaryResponse.data.payment_conditions || [];
                this.global_discount_types = secondaryResponse.data.global_discount_types || [];
                this.affectation_igv_types = secondaryResponse.data.affectation_igv_types || [];
                this.business_turns = secondaryResponse.data.business_turns || [];

                // Initialize payments
                this.form.payments = [];
                this.clickAddPayment();

                this.dataLoaded.items = true;

                // Step 3: Finalization
                this.updateProgress('complete', 100, 'Configuración completada');
                this.dataLoaded.complete = true;

            } catch (error) {
                console.error('Error loading data:', error);
                this.updateProgress('error', 100, 'Error al cargar datos');
                // Error handling - individual sections will remain as skeletons
            }
        },
        async openDialogNewPerson(){
            if(!this.dialogNewPerson){
                this.loading = true;
                const module = await import("../persons/form.vue");
                this.dialogNewPerson = module.default;
                this.loading = false;
            }
            this.showDialogNewPerson = true;
        },
        async preloadItemComponent() {
            // Preload item component without showing loading to user
            if (!this.addItemDialog) {
                try {
                    const module = await import('./partials/item.vue');
                    this.addItemDialog = module.default;
                } catch (error) {
                    console.warn('Failed to preload item component:', error);
                }
            }
        },
        async openDialogPlateNumber(){
            if(!this.dialogPlateNumber){
                this.loading = true;
                const module = await import("../components/modal_plate_number.vue");
                this.dialogPlateNumber = module.default;
                this.loading = false;
            }
            this.showDialogPlateNumber = true;
        },
        getChannels() {
            this.$http.get("/channels/all-records").then((response) => {
                this.channels = response.data.data;
                const channelIdFromDispatches =
                    localStorage.getItem("channel_id");
                if (channelIdFromDispatches) {
                    this.$set(this.form, "channel_id", Number(channelIdFromDispatches));
                    localStorage.removeItem("channel_id");
                }
            });
        },
        selectedNoteCredit(index) {
            let payment = this.form.payments[index];
            let note_credit_id = payment.note_credit_id;
            let note = this.notes_credit.find(
                (note) => note.id === note_credit_id
            );
            if (note) {
                let total = note.total;
                this.$set(this.form.payments[index], "payment", total);
            }
        },
        searchRemoteNotesCredit(input) {
            clearTimeout(this.timer);
            this.timer = setTimeout(() => {
                this.loading_search = true;
                let parameters = `input=${input}`;
                this.$http
                    .get(`/documents/note/search-no-used?${parameters}`)
                    .then((response) => {
                        this.notes_credit = response.data;
                        this.loading_search = false;
                    });
            }, 500);
        },
        adjustmentCentToElement(type) {
            if (type == "+") {
                this.form[this.elementToAdjust] =
                    this.form[this.elementToAdjust] + 0.01;
                this.adjustmentCentElements = {
                    element: this.elementToAdjust,
                    type: type,
                };
            } else {
                this.form[this.elementToAdjust] =
                    this.form[this.elementToAdjust] - 0.01;
                this.adjustmentCentElements = {
                    element: this.elementToAdjust,
                    type: type,
                };
            }

            this.showAdjustmentCent = false;
            this.calculateJustTotals();
        },
        calculateJustTotals() {
            //(1 + this.percentage_igv);
            let total_taxed = this.form.total_taxed;
            let igv = this.percentage_igv;
            let total = this.form.total;
            let total_igv = this.form.total_igv;

            switch (this.elementToAdjust) {
                case "total_taxed":
                    total_igv = total_taxed * igv;
                    total = total_taxed + total_igv;
                    break;
                case "total_igv":
                    total_taxed = total_igv / igv;
                    total = total_taxed + total_igv;
                    break;
                case "total":
                    total_taxed = total / (1 + igv);
                    total_igv = total - total_taxed;
                    break;
            }

            this.form.total_taxed = total_taxed;
            this.form.total_igv = total_igv;
            this.form.total = total;
            this.setTotalDefaultPayment();
        },
        adjustmentCent() {
            this.showAdjustmentCent = true;
        },
        autoAdjustCentIfNeeded() {
            // Detecta si total, base o IGV terminan en .x9 o .x1 y ajusta automáticamente
            const fieldsToCheck = ['total', 'total_taxed', 'total_igv'];
            let adjusted = false;

            fieldsToCheck.forEach(field => {
                const value = this.form[field];
                if (value) {
                    const valueStr = value.toFixed(2);
                    const lastDigit = valueStr.charAt(valueStr.length - 1);
                    
                    let adjustment = 0;
                    let adjustmentType = null;
                    
                    // Si termina en 9, ajustar +0.01 (redondear hacia arriba)
                    if (lastDigit === '9') {
                        adjustment = 0.01;
                        adjustmentType = '+';
                    }
                    // Si termina en 1, ajustar -0.01 (redondear hacia abajo)
                    else if (lastDigit === '1') {
                        adjustment = -0.01;
                        adjustmentType = '-';
                    }
                    
                    // Aplicar ajuste si se detectó
                    if (adjustment !== 0) {
                        const newValue = _.round(Number(value) + adjustment, 2);
                        this.form[field] = newValue;
                        console.log(`Auto-ajuste aplicado en ${field}: ${value} → ${newValue} (${adjustmentType}0.01)`);

                        // Establecer el elemento a ajustar (necesario para calculateJustTotals)
                        this.elementToAdjust = field;

                        // Guardar el ajuste como lo hace adjustmentCentToElement
                        this.adjustmentCentElements = {
                            element: field,
                            type: adjustmentType
                        };
                        adjusted = true;
                    }
                }
            });

            // Llamar a calculateJustTotals para que el cambio sea visible
            if (adjusted) {
                this.calculateJustTotals();
            }
        },
        clickAddAdvanceItem() {
            let item = this.advanceItems.find(
                (item) => item.id == this.advance_item_id
            );
            if (!item) return;
            item.quantity = 1;
            this.changeItem(item);
            this.advance_item_id = null;
        },

        checkPerception() {
            if (
                this.form.perception &&
                this.form.perception.amount &&
                this.form.operation_type_id === "2001"
            ) {
                // Agregar leyenda si no existe
                const legendExists = this.form.legends.some(
                    (legend) => legend.code === "2000"
                );
                if (!legendExists) {
                    this.form.legends.push({
                        code: "2000",
                        value: "Comprobante de percepción",
                    });
                }
            } else {
                if (this.form.perception && this.form.perception.amount) {
                    this.form.perception = {};
                }
                this.form.legends = this.form.legends.filter(
                    (legend) => legend.code !== "2000"
                );
            }
        },
        checkPerceptionItems() {
            if (
                this.form.perception &&
                this.form.perception.amount &&
                this.form.operation_type_id == "2001"
            ) {
                let items = this.form.items;
                let currentPerceptionPercentage = null;

                for (let item of items) {
                    if (item.item.has_perception) {
                        if (currentPerceptionPercentage === null) {
                            currentPerceptionPercentage =
                                item.item.percentage_perception;
                        } else if (
                            currentPerceptionPercentage !==
                            item.item.percentage_perception
                        ) {
                            this.$message.error(
                                "Los productos afectos a percepción deben tener el mismo porcentaje"
                            );
                            return false;
                        }
                    }
                }
            }

            return true;
        },
        updateDays(index) {
            if (this.timerFee) {
                clearTimeout(this.timerFee);
            }
            this.timerFee = setTimeout(() => {
                let row = this.form.fee[index];
                let now = moment();

                let days = row.days;
                let due_date = now.add(days, "days");
                row.date = due_date.format("YYYY-MM-DD");
                this.form.fee[index] = row;
            }, 300);
        },

        async idExistsInDiscountTypeItems(id, discount_type_id) {
            try {
                this.loading_check_discount_type = true;
                let response = await this.$http.get(
                    `/discount-types/id-exists-in-discount-type-items/${id}/${discount_type_id}`
                );
                return response.data.exists;
            } catch (error) {
                return false;
            } finally {
                this.loading_check_discount_type = false;
            }
        },

        async checkDiscountType(index, discount_type_id) {
            let item = this.form.items[index];
            let item_id = item.item_id;
            let discountType = this.discounts_specific.find(
                (discount) => discount.id === discount_type_id
            );
            let exists = await this.idExistsInDiscountTypeItems(
                item_id,
                discount_type_id
            );
            if (exists) {
                let discountFullDescription = `${discountType.description} - ${discountType.discount_value}%`;
                let discountAmount =
                    (item.total * discountType.discount_value) / 100;
                return {
                    discountFullDescription,
                    discountAmount,
                };
            }
            return null;
        },
        removeDiscountType(index) {
            let item = this.form.items[index];
            let discountAmount = 0;
            this.form.items[index].aux_total_discount = discountAmount;
            this.form.items[index].type_discount = null;
            this.changeTotalDiscountItem(item, index);
        },
        addDiscountType(index) {
            if (!this.isAddingDiscount) return;
            let discountType = this.discounts_manual.find(
                (discount) => discount.id === this.discountSelectedId
            );
            let discountFullDescription = `${discountType.description} - ${discountType.discount_value}%`;
            let item = this.form.items[index];
            let total = Number(item.total);
            let discountAmount =
                (total * Number(discountType.discount_value)) / 100;
            this.form.items[index].aux_total_discount = discountAmount;
            this.form.items[index].type_discount = discountFullDescription;
            this.changeTotalDiscountItem(item, index);
        },
        selectDiscount(discount) {
            this.isAddingDiscount = true;
            this.discountSelectedId = discount.id;
            // this.form.discount_type_id = discount.id;
        },
        async preview(format) {
            this.loading_submit = true;
            let path = `/${this.resource}/preview`;
            let temp = this.form.payment_condition_id;
            if (this.form.payment_condition_id === "03")
                this.form.payment_condition_id = "02";
            let url = null;
            let original_format_pdf = this.form.actions.format_pdf;
            this.form.actions.format_pdf = format;
            try {
                let response = await this.$http.post(path, this.form, {
                    responseType: "blob",
                });
                const blob = new Blob([response.data], {
                    type: "application/pdf",
                });
                url = URL.createObjectURL(blob);
                if (temp === "03") this.form.payment_condition_id = "03";
            } catch (error) {
                if (temp === "03") this.form.payment_condition_id = "03";
            } finally {
                this.loading_submit = false;
                this.form.actions.format_pdf = original_format_pdf;
            }

            return url;
        },
        async validatePreview() {
            let errorSeries = false;
            _.forEach(this.form.items, (row) => {
                if (row.item.series_enabled) {
                    errorSeries =
                        parseFloat(row.quantity) !== row.item.lots.length;
                    return false;
                }
            });
            if (errorSeries && !this.form.has_prepayment) {
                this.$message.error("No se han seleccionado todas las series");
                return false;
            }

            if (!this.form.customer_id) {
                this.$message.error("Debe seleccionar cliente");
                return false;
            }

            if (this.form.show_terms_condition) {
                this.form.terms_condition = this.configuration.terms_condition_sale;
            }
            if (this.form.has_prepayment || this.prepayment_deduction) {
                let error_prepayment =
                    await this.validateAffectationTypePrepayment();
                if (!error_prepayment.success) {
                    this.$message.error(error_prepayment.message);
                    return false;
                }
            }

            if (this.is_receivable) {
                this.form.payments = [];
            } else {
                let validate = await this.validate_payments();
                if (
                    // validate.acum_total > parseFloat(this.form.total) ||
                    validate.error_by_item > 0
                ) {
                    this.$message.error(
                        "Los montos ingresados superan al monto a pagar o son incorrectos"
                    );
                    return false;
                }

                let validate_payment_destination =
                    await this.validatePaymentDestination();

                if (validate_payment_destination.error_by_item > 0) {
                    this.$message.error("El destino del pago es obligatorio");
                    return false;
                }
            }

            await this.deleteInitGuides();
            await this.asignPlateNumberToItems();

            let val_detraction = await this.validateDetraction();
            if (!val_detraction.success) {
                this.$message.error(val_detraction.message);
                return false;
            }

            if (!this.enabled_payments) {
                this.form.payments = [];
            }

            if (this.configuration.enabled_point_system) {
                const validate_exchange_points = this.validateExchangePoints();
                if (!validate_exchange_points.success) {
                    this.$message.error(validate_exchange_points.message);
                    return false;
                }
            }

            return true;
        },
        async openDialogPreview() {
            let validate = await this.validatePreview();
            if (!validate) {
                return;
            }
            if(!this.dialogPreview){
                this.loading = true;
                const module = await import("./partials/preview.vue");
                this.dialogPreview = module.default;
                this.loading = false;
            }
            this.showDialogPreview = true;
        },
        updateDiscountItems() {
            this.form.items.forEach((item, index) => {
                this.changeTotalDiscountItem(item, index);
            });
        },
        openImage(url) {
            this.currentImage = url;
            this.showDialogImage = true;
        },
        handleEnterDescription(row) {
            row.edit_description = false;
        },
        changeCustomerTradeName() {
            let customer_id = this.form.customer_id;
            if (!customer_id) return;
            if (this.timer) clearTimeout(this.timer);
            this.timer = setTimeout(async () => {
                await this.$http
                    .post("/persons/update-info", {
                        customer_id,
                        type: "trade_name",
                        trade_name: this.new_customer_trade_name,
                    })
                    .then((response) => {
                        if (response.data.success) {
                            this.$message.success(response.data.message);
                        }
                    })
                    .catch((error) => {
                        console.log(error);
                    });
            }, 750);
        },
        changeCustomerAddress() {
            let customer_id = this.form.customer_id;
            let address_id = this.form.customer_address_id;
            if (!customer_id) return;

            if (this.timer) clearTimeout(this.timer);
            this.timer = setTimeout(async () => {
                await this.$http
                    .post("/persons/update-info", {
                        customer_id,
                        address_id,
                        type: "address",
                        address: this.new_customer_address,
                    })
                    .then((response) => {
                        if (response.data.success) {
                            this.$message.success(response.data.message);
                        }
                    })
                    .catch((error) => {
                        console.log(error);
                    });
            }, 750);
        },
        foundDecimals(num) {
            if (num <= 700) {
                return 1;
            }

            let num1 = num * 0.5;
            if (num1 < 700) {
                return 0.5;
            }
            let num2 = num * 0.25;
            if (num2 < 700) {
                return 0.25;
            }
            return 0.1;
        },

        foundBigPart(unit_price) {
            // Calculamos cuántas divisiones necesitamos (redondeando hacia arriba)
            let numberOfParts = Math.ceil(unit_price / 650);

            // Calculamos el porcentaje base para cada parte
            let basePart = Math.floor((1 / numberOfParts) * 100) / 100;

            let parts = [];
            let remainingPercentage = 1;

            // Distribuimos las partes
            for (let i = 0; i < numberOfParts; i++) {
                if (i === numberOfParts - 1) {
                    // Última parte: agregamos lo que queda
                    parts.push(Math.round(remainingPercentage * 100) / 100);
                } else {
                    parts.push(basePart);
                    remainingPercentage -= basePart;
                }
            }

            return parts;
        },
        foundQuantity(unit_price) {
            let maxQuantity = Math.floor(700 / unit_price);
            return maxQuantity;
        },
        splitItems() {
            let results = [];
            let items = JSON.parse(JSON.stringify(this.form.items));
            for (let i = 0; i < items.length; i++) {
                let item = items[i];
                let quantity = item.quantity;
                let unit_price = item.unit_price;

                if (unit_price > 700) {
                    if (quantity == 1) {
                        let parts = this.foundBigPart(unit_price);
                        for (let j = 0; j < parts.length; j++) {
                            let total = unit_price * parts[j];
                            item.item.unit_price = unit_price;
                            let newItem = {
                                ...item,
                                quantity: parts[j],
                                total: total,
                                unit_price: unit_price,
                            };
                            newItem = calculateRowItem(
                                newItem,
                                this.form.currency_type_id,
                                this.form.exchange_rate_sale,
                                this.percentage_igv
                            );
                            results.push(newItem);
                            //   results.push({
                            //     id: item.id,
                            //     unit_price: unit_price,
                            //     quantity: parts[j],
                            //     total: total,
                            //   });
                        }
                    } else {
                        let decimal = this.foundDecimals(unit_price);
                        let newQuantity = quantity / decimal;
                        let newUnitPrice = unit_price * decimal;
                        for (let j = 0; j < newQuantity; j++) {
                            item.item.unit_price = newUnitPrice;
                            let newItem = {
                                ...item,
                                quantity: decimal,
                                total: decimal * newUnitPrice,
                                unit_price: newUnitPrice,
                            };

                            newItem = calculateRowItem(
                                newItem,
                                this.form.currency_type_id,
                                this.form.exchange_rate_sale,
                                this.percentage_igv
                            );

                            results.push(newItem);
                        }
                    }
                } else {
                    let countWhile = 0;

                    if (item.total > 700) {
                        let quantityR = quantity;
                        while (quantityR > 0) {
                            countWhile++;
                            if (countWhile > 1000) {
                                break;
                            }
                            let maxQuantity = this.foundQuantity(unit_price);
                            let groupQuantity = Math.min(
                                maxQuantity,
                                quantityR
                            );
                            let newItem = {
                                ...item,
                                quantity: groupQuantity,
                                total: groupQuantity * unit_price,
                            };

                            newItem = calculateRowItem(
                                newItem,
                                this.form.currency_type_id,
                                this.form.exchange_rate_sale,
                                this.percentage_igv
                            );

                            results.push(newItem);
                            quantityR -= groupQuantity;
                        }
                    } else {
                        results.push(item);
                    }
                }
            }
            return results;
        },
        groupItems() {
            let items = this.splitItems();
            items.sort((a, b) => {
                return b.total - a.total;
            });

            let results = [];
            let currentGroup = [];
            let currentTotal = 0;

            for (let i = 0; i < items.length; i++) {
                let item = items[i];

                if (currentTotal + item.total <= 700) {
                    currentGroup.push(item);
                    currentTotal += item.total;
                } else {
                    results.push(currentGroup);
                    currentGroup = [item];
                    currentTotal = item.total;
                }
            }

            if (currentGroup.length > 0) {
                results.push(currentGroup);
            }

            return results;
        },
        createSplitCode() {
            let code = Math.random().toString(36).substring(2, 15);
            let date = this.form.date_of_issue;
            let parts = date.split("-");
            let year = parts[0].substring(2);
            let month = parts[1];
            let day = parts[2];
            code = code + "-" + year + month + day;
            return code;
        },
        async checkSubmit() {
            
            if(this.form.series_id == null){
                const debugData = this.debugFilterSeries();
                this.seriesCritical(debugData);
                this.filterSeries();
                if(this.form.series_id == null){
                    this.$message.error("Debe seleccionar una serie");
                    return;
                }
                
            }
            if (
                this.form.exchange_rate_sale == null ||
                this.form.exchange_rate_sale == ""
            ) {
                this.form.exchange_rate_sale = 1;
            }
            if (
                this.form.currency_type_id == "USD" &&
                (this.form.exchange_rate_sale == 1 ||
                    this.form.exchange_rate_sale == null ||
                    this.form.exchange_rate_sale == "")
            ) {
                try {
                    await this.$confirm(
                        "¿El tipo de cambio es 1, desea continuar?",
                        "Advertencia",
                        {
                            confirmButtonText: "Aceptar",
                        }
                    );
                } catch (e) {
                    console.log("e", e);
                    return;
                }
            }
            if (this.isForSplit) {
                try {
                    let invoices = this.groupItems();
                    let code = this.createSplitCode();
                    await this.$confirm(
                        "Si la venta supera los S/ 700, es necesario identificar a tu cliente con su DNI. o ¿Prefieres generar múltiples documentos por montos menores a S/ 700?",
                        "¿Generar múltiples documentos?",
                        {
                            confirmButtonText: "Aplicar prorrateo",
                            cancelButtonText: "Seleccionar DNI",
                            type: "warning",
                        }
                    );
                    this.isEmiting = true;

                    for (const [index, invoice] of invoices.entries()) {
                        this.form.items = invoice;
                        this.calculateTotal();
                        if (index === invoices.length - 1) {
                            this.isEmiting = false;
                        }
                        this.form.split_code = code;
                        await this.submitAsync();
                    }

                    return;
                } catch (e) {
                    console.log(e);
                }
            } else {
                this.submit();
            }
        },
        showMessagePay() {
            this.$http("/msg-to-pay").then((response) => {
                let { data } = response;
                if (data.success) {
                    this.$message.error(data.message);
                }
            });
        },
        tokenValidated() {
            this.form.token_validated_for_discount = true;
            this.submit();
        },
        async fetchCoupons() {
            this.$http("/cupones/api/coupons")
                .then((response) => {
                    this.coupons = response.data;
                })
                .catch((err) => {
                    console.log("Error al traer los cupones");
                });

            // try {
            //     const response = await axios.get("/cupones/api/coupons");
            //     this.coupons = response.data;
            // } catch (error) {
            //     console.error("Error fetching coupons:", error);
            // }
        },
        getCouponName(couponId) {
            if (!this.coupons) return " ";
            const coupon = this.coupons.find(
                (coupon) => coupon.id === couponId
            );
            return coupon ? coupon.nombre : " ";
        },
        restoreDescription(row, index) {
            row.item.description = row.item.original_description;
            row.name_product_pdf = `<p>${row.item.description}</p>`;
            row.edit_description = false;
            this.form.items[index] = row;
        },
        changeDescriptionItem(row, index) {
            // setTimeout(() => {
            // row.edit_description = false;
            row.name_product_pdf = `<p>${row.item.description}</p>`;

            this.form.items[index] = row;
            // }, 1500);
        },
        clickChangeName(row, index) {
            if (!this.configuration.change_name_click_item) return;
            row.edit_description = true;
            this.form.items[index] = row;
        },
        initQuickFormItem() {
            this.form_item_quick = {
                unit_type_id: "NIU",
                sale_unit_price: 0,
                quantity: 1,
                currency_type_id: this.form.currency_type_id,
                description: "",
                purchase_affectation_igv_type_id: "10",
                purchase_unit_price: 0,
                sale_affectation_igv_type_id: "10",
                stock: 0,
                stock_min: 1,
                item_unit_types: [],
            };
        },
        validateQuickFormItem() {
            let { description, quantity, sale_unit_price, unit_type_id } =
                this.form_item_quick;
            if (
                !description ||
                !quantity ||
                !sale_unit_price ||
                !unit_type_id
            ) {
                this.$message({
                    type: "warning",
                    message: "Debe completar los campos requeridos.",
                });
                return false;
            }
            return true;
        },
        getItemByIdAndAdd(id, quantity) {
            this.$http
                .get(`/documents/search/item/${id}`)
                .then((response) => {
                    let { items } = response.data;
                    let [item] = items;
                    let itemToAdd = {
                        ...item,
                        quantity,
                    };
                    this.changeItem(itemToAdd);
                })
                .catch((error) => {
                    console.error(error);
                });
        },
        addItemQuick() {
            this.form_item_quick.currency_type_id = this.form.currency_type_id;
            this.$http
                .post("/items", this.form_item_quick)
                .then((response) => {
                    let { success, message, id } = response.data;
                    if (success) {
                        this.getItemByIdAndAdd(
                            id,
                            this.form_item_quick.quantity
                        );
                        this.$message({
                            type: "success",
                            message: message,
                        });
                    }
                })
                .catch((error) => {})
                .finally(() => {
                    this.initQuickFormItem();
                });
        },
        changePersonDispatcher() {},
        changePersonPacker() {},
        changeUnitDiscountItemRestore(item, index) {
            let quantity = item.item.original_quantity;
            if (!quantity) return;
            item.unit_discount = 0;
            item.quantity = quantity;
            this.form.items[index] = calculateRowItem(
                item,
                this.form.currency_type_id,
                this.form.exchange_rate_sale,
                this.percentage_igv
            );
            this.$forceUpdate();
            this.calculateTotal();
        },
        changeUnitDiscountItem(item, index) {
            if (this.timer) clearTimeout(this.timer);
            this.timer = setTimeout(() => {
                let original_quantity = Number(item.quantity);
                let discount_quantity = Number(item.unit_discount) || 0;
                let quantity = original_quantity - discount_quantity;
                if (quantity < 1) {
                    this.$message({
                        type: "warning",
                        message: "La cantidad no puede ser menor a 1",
                    });
                    item.quantity = original_quantity;
                    return;
                } else {
                    item.quantity = Number(quantity).toFixed(2);
                }
                this.form.items[index] = calculateRowItem(
                    item,
                    this.form.currency_type_id,
                    this.form.exchange_rate_sale,
                    this.percentage_igv
                );
                this.$forceUpdate();
                this.calculateTotal();
            }, 600);
        },
        changeStockItem(row, idx) {
            if (this.timer) clearTimeout(this.timer);
            this.timer = setTimeout(async () => {
                try {
                    this.loading = true;
                    await this.$confirm(
                        `¿Está seguro de cambiar el stock de ${row.item.description} ?`,
                        "Advertencia",
                        {
                            confirmButtonText: "Aceptar",
                            cancelButtonText: "Cancelar",
                            type: "warning",
                        }
                    );

                    let {
                        warehouse_id,
                        item: { id, stock },
                    } = row;
                    const response = await this.$http.post(
                        `/items/${id}/update-stock-item`,
                        {
                            warehouse_id,
                            stock,
                        }
                    );
                    if (response.data.success) {
                        this.$message({
                            type: "success",
                            message: response.data.message,
                        });
                    } else {
                        this.$message({
                            type: "error",
                            message:
                                "Ocurrió un error al actualizar el precio de compra.",
                        });
                    }
                } catch (e) {
                    row.item.stock = row.item.origin_stock;
                    this.form.items[idx] = row;
                } finally {
                    this.loading = false;
                }
            }, 650);
        },
        changePurchaseUnitPriceItem(row, idx) {
            if (this.timer) clearTimeout(this.timer);

            this.timer = setTimeout(async () => {
                try {
                    this.loading = true;
                    await this.$confirm(
                        `¿Está seguro de cambiar el precio de compra de ${row.item.description} ?`,
                        "Advertencia",
                        {
                            confirmButtonText: "Aceptar",
                            cancelButtonText: "Cancelar",
                            type: "warning",
                        }
                    );
                    let {
                        item: { purchase_unit_price, id },
                    } = row;
                    const response = await this.$http.post(
                        `/items/${id}/update-purchase-unit-price`,
                        {
                            purchase_unit_price,
                        }
                    );
                    if (response.data.success) {
                        this.$message({
                            type: "success",
                            message: response.data.message,
                        });
                    } else {
                        this.$message({
                            type: "error",
                            message:
                                "Ocurrió un error al actualizar el precio de compra.",
                        });
                    }
                } catch (e) {
                    row.item.purchase_unit_price =
                        row.item.origin_purchase_unit_price;
                    this.form.items[idx] = row;
                } finally {
                    this.loading = false;
                }
            }, 650);
        },
        changeCollapse() {},
        calculatePeriodDays(cuotas, days) {
            let date_of_issue = this.form.date_of_issue;
            if (!cuotas || !days) return;

            this.form.fee = [];
            let dates = [];
            let startDate = moment(date_of_issue).utcOffset(-300);

            for (let i = 0; i < cuotas; i++) {
                let date = startDate
                    .clone()
                    .add(days * (i + 1), "days")
                    .format("YYYY-MM-DD");

                dates.push(date);
            }
            this.form.fee = dates.map((d) => ({
                id: null,
                date: d,
                currency_type_id: this.form.currency_type_id,
                amount: 0,
            }));
            this.calculateFee();

            this.setLastDateDue();
        },
        setLastDateDue() {
            if (this.form.fee && this.form.fee.length > 0) {
                let fee = this.form.fee[this.form.fee.length - 1];
                this.form.date_of_due = fee.date;
            }
        },
        removeFeeds() {
            if (this.form.fee.length <= 1) return;
            this.form.fee = [this.form.fee[0]];
            this.calculateFee();
            this.setLastDateDue();
        },
        addFeeds() {
            this.calculatePeriodDays(this.cuotaNumber, this.cuotaDays);
        },
        clickAddFavoriteItem() {
            let item = this.favoriteItems.find(
                (item) => item.id == this.favorite_item_id
            );
            if (!item) return;
            item.quantity = 1;
            this.changeItem(item);
            this.favorite_item_id = null;
        },
        async clickWarehouseDetail(warehouses, item_unit_types) {
            this.warehousesDetail = warehouses;
            this.item_unit_types = item_unit_types;
            if(!this.dialogWarehousesDetail){
                //import("./../items/partials/warehouses.vue");
                this.loading = true;
                const module = await import("./../items/partials/warehouses.vue");
                this.dialogWarehousesDetail = module.default;
                this.loading = false;
            }
            this.showWarehousesDetail = true;
        },
        async searchRemoteItemsAdvance(input) {
            this.loading_search_advance = true;
            let parameters = `input=${input || ""}&advance=1`;

            await this.$http
                .get(`/${this.resource}/search-items?${parameters}`)
                .then((response) => {
                    this.advanceItems = response.data.items;
                    this.loading_search_advance = false;
                });
        },
        async searchRemoteItems(input) {
            this.loading_search_favorite = true;
            let parameters = `input=${input || ""}&favorite=1`;

            await this.$http
                .get(`/${this.resource}/search-items-favorite?${parameters}`)
                .then((response) => {
                    this.favoriteItems = response.data.items;
                    this.loading_search_favorite = false;
                });
        },
        changeTotalItem(item, index) {
            let total = item.total;
            let total_value = item.total_value;
            let quantity = item.quantity;
            let unit_value = total_value / quantity;
            let unit_price = total / quantity;
            item.item.unit_price = unit_price;
            this.form.items[index] = calculateRowItem(
                item,
                this.form.currency_type_id,
                this.form.exchange_rate_sale,
                this.percentage_igv
            );
            this.form.items[index].unit_value_edit = _.round(unit_value, 4);
            this.form.items[index].unit_price_edit = _.round(unit_price, 4);
            this.$forceUpdate();
            this.calculateTotal();
        },
        getItemsDiscountExtraPercentages() {
            let limit = parseFloat(this.configuration.sellers_discount_limit);
            let items = this.form.items.filter(
                (i) => i.discounts && i.discounts.length > 0
            );
            let results = items
                .map((i) => {
                    let percentage_discount = 0;

                    i.discounts.forEach((d) => {
                        percentage_discount += d.factor * 100;
                    });
                    return {
                        description: i.item.description,
                        percentage_discount,
                    };
                })
                .filter((i) => i.percentage_discount > limit);

            return results;
        },
        async validateRestrictSellerDiscount() {
            this.itemExtraPercentages = [];
            // if (
            //     this.configuration.restrict_seller_discount &&
            //     this.typeUser !== "admin"
            // ) {
            if (this.configuration.restrict_seller_discount) {
                // const all_percentages = this.getDiscountPercentages();
                const item_extra_percentages =
                    this.getItemsDiscountExtraPercentages();

                if (
                    item_extra_percentages.length > 0 &&
                    !this.form.token_validated_for_discount
                ) {
                    if(!this.dialogDiscountPermission){
                        this.loading = true;
                        const module = await import("./partials/discounts_permission_form.vue");
                        this.dialogDiscountPermission = module.default;
                        this.loading = false;
                    }
                    this.showDialogDiscountPermission = true;
                    this.itemExtraPercentages = item_extra_percentages;
                    return {
                        success: false,
                    };
                }

                // if (
                //     all_percentages >
                //         parseFloat(this.configuration.sellers_discount_limit) &&
                //     !this.form.token_validated_for_discount
                // ) {
                //     this.totalDiscountPercentage = _.round(all_percentages, 2);
                //     this.showDialogDiscountPermission = true;

                //     return {
                //         success: false,
                //     };
                // }
            }

            return {
                success: true,
            };
        },
        getDiscountPercentages() {
            let total = 0;
            this.form.items.forEach((item) => {
                if (item.discounts) {
                    item.discounts.forEach((discount) => {
                        total += discount.factor * 100;
                    });
                }
            });
            // if (this.form.discounts) {
            //     return _.sumBy(this.form.discounts, (discount) => {
            //         return discount.factor * 100;
            //     });
            // }

            // return 0;
            return total;
        },
        changeTotalDiscountItem(item, index) {
            this.currentIndex = index;
            let discount = this.addDiscountItem();
            item.total_discount = item.aux_total_discount;
            if (item.discounts && item.discounts.length > 0) {
                discount = item.discounts[0];
                discount.is_amount = true;
            }
            if (item.total_discount > 0) {
                if (
                    this.discount_add_igv &&
                    item.affectation_igv_type_id == "10"
                ) {
                    discount.amount =
                        item.total_discount / (1 + this.percentage_igv);
                } else {
                    discount.amount = item.total_discount;
                }
                item.discounts = [discount];
            } else {
                item.discounts = [];
            }
            // discount.amount = item.total_discount;
            // item.discounts = [discount];
            let row = calculateRowItem(
                item,
                this.form.currency_type_id,
                this.form.exchange_rate_sale,
                this.percentage_igv
            );
            row.type_discount = item.type_discount;
            row.aux_total_discount = item.aux_total_discount;
            row.unit_value_edit = _.round(row.unit_value, 4);
            row.unit_price_edit = _.round(row.unit_price, 4);
            row.original_discount = item.aux_total_discount;

            this.form.items[index] = row;
            this.$forceUpdate();
            this.calculateTotal();
        },
        addDiscountItem() {
            let type_id =
                this.configuration.global_discount_type_id == "02"
                    ? "00"
                    : "01";
            let discounts = [
                {
                    id: "00",
                    active: 1,
                    base: 1,
                    level: "item",
                    type: "discount",
                    description:
                        "Descuentos que afectan la base imponible del IGV/IVAP",
                },
                {
                    id: "01",
                    active: 1,
                    base: 0,
                    level: "item",
                    type: "discount",
                    description:
                        "Descuentos que no afectan la base imponible del IGV/IVAP",
                },
            ];
            let discount = {
                discount_type_id: type_id,
                discount_type: discounts.find((d) => d.id == type_id),
                description: null,
                percentage: 0,
                factor: 0,
                amount: 0,
                base: 0,
                is_amount: true,
                use_input_amount: true,
                is_split: false,
            };
            return discount;
        },
        calculateDiscountItem(form, total) {
            let { affectation_igv_type_id, has_igv } = form;

            let discount = total * (percentage / 100);
            let original_discount = discount;
            if (has_igv && affectation_igv_type_id == "10") {
                discount = discount / 1.18;
            }

            this.discounts[i].result = discount;

            total = total - discount;
            this.discounts[i].base = total;
            this.discounts[i].original_discount = original_discount;
            this.discounts[i].original_price = this.form.unit_price_value;
        },
        changeTotalValueItem(item, index) {
            if (this.timer) clearTimeout(this.timer);
            this.timer = setTimeout(() => {
                let has_igv = item.item.has_igv;
                let percentage_igv = item.percentage_igv;
                let total_value = item.total_value;
                let quantity = item.quantity;
                let unit_value = total_value / quantity;

                let unit_price = 0;
                if (has_igv) {
                    unit_price = unit_value * (1 + percentage_igv / 100);
                } else {
                    unit_price = unit_value;
                }
                item.item.unit_price = unit_price;
                this.form.items[index] = calculateRowItem(
                    item,
                    this.form.currency_type_id,
                    this.form.exchange_rate_sale,
                    this.percentage_igv
                );
                this.form.items[index].unit_value_edit = _.round(unit_value, 4);
                this.form.items[index].unit_price_edit = _.round(unit_price, 4);
                this.$forceUpdate();
                this.calculateTotal();
            }, 150);
        },
        changeUnitPriceItem(item, index) {
            let {
                percentage_igv,
                item: { has_igv },
                payment_conditions,
            } = item;
            let unit_value = 0;
            if (has_igv) {
                unit_value = item.unit_price_edit / (1 + percentage_igv / 100);
            } else {
                unit_value = item.unit_price_edit;
            }
            let unit_price = item.unit_price_edit;
            item.item.unit_price = unit_price;

            this.form.items[index] = calculateRowItem(
                item,
                this.form.currency_type_id,
                this.form.exchange_rate_sale,
                this.percentage_igv
            );
            this.form.items[index].payment_conditions = payment_conditions;
            this.form.items[index].unit_value_edit = _.round(unit_value, 4);
            this.form.items[index].unit_price_edit = _.round(unit_price, 4);
            this.$forceUpdate();
            this.calculateTotal();
        },
        changeUnitValueItem(item, index) {
            if (this.timer) clearTimeout(this.timer);
            this.timer = setTimeout(() => {
                let {
                    percentage_igv,
                    item: { has_igv },
                    unit_value_edit,
                } = item;
                let unit_price = 0;
                if (has_igv) {
                    unit_price = unit_value_edit * (1 + percentage_igv / 100);
                } else {
                    unit_price = unit_value_edit;
                }
                item.item.unit_price = unit_price;
                this.form.items[index] = calculateRowItem(
                    item,
                    this.form.currency_type_id,
                    this.form.exchange_rate_sale,
                    this.percentage_igv
                );
                this.form.items[index].unit_price_edit = _.round(unit_price, 4);
                this.form.items[index].unit_value_edit = _.round(
                    unit_value_edit,
                    4
                );
                this.$forceUpdate();
                this.calculateTotal();
            }, 150);
        },
        changeQuantityItem(item, index) {
            let quantity = item.quantity;
            if (quantity < 1) {
                this.$message({
                    type: "warning",
                    message: "La cantidad no puede ser menor a 1",
                });
                item.quantity = 1;
                return;
            }
            let row = calculateRowItem(
                item,
                this.form.currency_type_id,
                this.form.exchange_rate_sale,
                this.percentage_igv
            );
            row.unit_value_edit = _.round(row.unit_value, 4);
            row.unit_price_edit = _.round(row.unit_price, 4);

            this.form.items[index] = row;
            this.$forceUpdate();
            this.calculateTotal();
        },
        async changeCompany(skipLoading = false) {
            if (!this.form.company_id) return;
            try {
                if (!skipLoading) {
                    this.loading = true;
                }
                const response = await this.$http.get(
                    `/documents/tables-company/${this.form.company_id}`
                );

                if (response.status == 200) {
                    let { data } = response;
                    let { series, establishment, payment_destinations } = data;

                    // this.all_series = series;
                    this.form.establishment = establishment;
                    this.payment_destinations = payment_destinations;

                    this.$store.commit("setAllSeries", series);
                    this.filterSeries();
                    this.form.payments = [];
                    this.clickAddPayment();
                    // this.changeDestinationSale();
                }
            } catch (e) {
            } finally {
                if (!skipLoading) {
                    this.loading = false;
                }
            }
        },
        async uploadFileNewItems(event) {
            let file = event.target.files[0];

            let url = `/items/items-document-news`;
            //mandar el archivo por post a esa url
            let formData = new FormData();
            formData.append("file", file);
            formData.append("type", "documents");
            try {
                this.loading = true;
                const response = await this.$http.post(url, formData);

                if (response.status == 200) {
                    const {
                        data: { data },
                    } = response;
                    let items = data.items;

                    for (let i = 0; i < items.length; i++) {
                        const item = items[i];
                        this.changeItem(item);
                    }
                }

                //limpiar el input file
                event.target.value = "";
            } catch (e) {
            } finally {
                this.loading = false;
            }
        },
        async uploadFileItems(event) {
            this.errors = [];
            this.registered = 0;
            this.hash = null;
            let file = event.target.files[0];

            let url = `/items/items-document`;
            //mandar el archivo por post a esa url
            let formData = new FormData();
            formData.append("file", file);
            formData.append("type", "documents");
            try {
                this.loading = true;
                const response = await this.$http.post(url, formData);

                if (response.status == 200) {
                    const {
                        data: { data },
                    } = response;
                    let items = data.items;
                    this.registered = data.registered;
                    this.errors = data.errors;
                    this.hash = data.hash;
                    for (let i = 0; i < items.length; i++) {
                        const item = items[i];
                        this.changeItem(item);
                    }
                }
                if(!this.dialogResultExcelProducts){
                    const module = await  import("@components/ResultExcelProducts.vue");
                    this.dialogResultExcelProducts = module.default;
                }
                //limpiar el input file
                this.showResultExcelProducts = true;
                event.target.value = "";
            } catch (e) {
            } finally {
                this.loading = false;
            }
        },

        seeDetails(item) {
            let { id } = item;
            window.open(`/items/details/${id}`, "_blank");
        },
        splitBase() {
            let hasUnaffected = this.form.items.some(
                (i) => i.affectation_igv_type_id !== "10"
            );

            this.total_global_discount = this.total_global_discount_aux;
            if (this.total_global_discount == 0 || !this.is_amount) return;
            if (!hasUnaffected) {
                if (this.split_base) {
                    this.total_global_discount =
                        this.total_global_discount / 1.18;
                } else {
                    this.total_global_discount =
                        this.total_global_discount * 1.18;
                }
            }
            this.total_global_discount = Math.round(
                this.total_global_discount * 100
            );
            this.total_global_discount = this.total_global_discount / 100;
            this.calculateTotal();
        },
        async changeSeller() {
            let { seller_id } = this.form;
            await this.getCash(seller_id);
        },
        hasCashOpen() {
            let has_cash = this.payment_destinations.some(
                (payment_destination) => payment_destination.cash_id != null
            );
            if (has_cash) {
                this.cash_id = this.payment_destinations.find(
                    (payment_destination) => payment_destination.cash_id != null
                ).cash_id;
            }
            return has_cash;
        },
        formatRecords(records) {
            let result = records.map((record) => {
                let { period } = record;
                let date = moment(period);
                let month = Number(date.format("M"));
                let year = date.format("YYYY");
                let monthName = this.monthsCollege.find(
                    (m) => m.value == month
                ).label;

                return {
                    month: monthName,
                    value: month,
                    year: year,
                };
            });
            if (result.length > 0) {
                this.yearCollegeName = result[0].year;
                this.collegeYear = 20;
            }
            return result;
        },
        createItem(item) {
            let newItem = {};
            newItem.item = item;
            newItem.presentation = {};
            newItem.item_unit_types = item.item_unit_types;
            newItem.discounts = [];
            newItem.charges = [];
            newItem.unit_price_value = item.sale_unit_price;
            newItem.has_igv = item.has_igv;
            newItem.has_plastic_bag_taxes = item.has_plastic_bag_taxes;
            newItem.affectation_igv_type_id = item.sale_affectation_igv_type_id;
            let affectation_igv_type_id = newItem.affectation_igv_type_id;
            newItem.quantity = 1;

            newItem.has_isc = item.has_isc;
            newItem.percentage_isc = item.percentage_isc;
            newItem.system_isc_type_id = item.system_isc_type_id;
            let unit_price = newItem.unit_price_value;
            newItem.unit_price = unit_price;
            newItem.item.unit_price = unit_price;
            newItem.item.presentation = this.item_unit_type || {};
            newItem.affectation_igv_type = _.find(this.affectation_igv_types, {
                id: affectation_igv_type_id,
            });

            return newItem;
        },
        formatItems(item) {
            let oldItem = this.createItem(item);
            let row = calculateRowItem(oldItem, "PEN", 1, 0.18);

            this.addRow(row);
        },
        resetPlanSuscription() {
            this.form.child_id = null;
            this.form.items = [];
            this.calculateTotal();
        },
        async getItemPlanSuscription() {
            this.form.items = [];
            this.calculateTotal();
            let params = {
                parent_customer_id: this.form.customer_id,
                children_customer_id: this.form.child_id,
            };

            const response = await this.$http.post(
                `/suscription/client/itemPlan`,
                params
            );
            if (response.status == 200) {
                const { data } = response;
                let items = data.items;
                for (let i = 0; i < items.length; i++) {
                    const item = items[i];
                    this.formatItems(item.item);
                }
            }
        },
        getOpcionalName(key, defaultName) {
            if (
                this.suscriptionames &&
                this.suscriptionames[key] != undefined
            ) {
                return this.suscriptionames[key];
            }
            return defaultName;
        },
        clearMonthsSelected() {
            this.monthsSelected = [];
            this.monthCollege = [];
        },
        formatMonth(month) {
            return `${month.month}-${month.year.substring(2, 4)}`;
        },
        setMonths() {
            let months = this.monthsSelected.filter((month) => {
                return month.year == this.yearCollegeName;
            });
            this.monthCollege = months.map((month) => {
                return month.month;
            });
        },
        setMonth(month) {
            let monthSelected = {
                month: month.label,
                value: month.value,
                year: this.yearCollegeName,
            };
            //check if exist in array if is true remove else add
            let exist = this.monthsSelected.findIndex(
                (m) =>
                    m.month == monthSelected.month &&
                    m.year == monthSelected.year
            );
            if (exist >= 0) {
                this.monthsSelected.splice(exist, 1);
            } else {
                this.monthsSelected.push(monthSelected);
                this.sortMonthsSelected();
            }
        },
        sortMonthsSelected() {
            //sort by year and value(int)
            this.monthsSelected.sort((a, b) => {
                if (a.year > b.year) {
                    return 1;
                }
                if (a.year < b.year) {
                    return -1;
                }
                if (a.year == b.year) {
                    if (a.value > b.value) {
                        return 1;
                    }
                    if (a.value < b.value) {
                        return -1;
                    }
                }
                return 0;
            });
        },
        formatTooltip(value) {
            let idx = value / 20;
            let currentYear = moment().format("YYYY");
            let currentYearInt = parseInt(currentYear);
            let result = currentYear;
            switch (idx) {
                case 0:
                    result = `${currentYearInt - 1}`;
                    break;
                case 1:
                    result = `${currentYearInt}`;
                    break;
                case 2:
                    result = `${currentYearInt + 1}`;
                    break;
                case 3:
                    result = `${currentYearInt + 2}`;
                    break;
                default:
                    result = `${currentYearInt + 3}`;
                    break;
            }
            this.yearCollegeName = result;
            return result;
        },
        changeDataTip(tip) {
            if (tip) {
                this.form.worker_full_name_tips = tip.worker_full_name_tips;
                this.form.total_tips = tip.total_tips;
            }
        },
        onSuccessUploadVoucher(response, file, fileList, index) {
            if (response.success) {
                this.form.payments[index].filename = response.data.filename;
                this.form.payments[index].temp_path = response.data.temp_path;
                this.form.payments[index].file_list = fileList;
            } else {
                this.cleanFileListUploadVoucher(index);
                this.$message.error(response.message);
            }
        },
        cleanFileListUploadVoucher(index) {
            this.form.payments[index].file_list = [];
        },
        handleRemoveUploadVoucher(file, fileList, index) {
            this.form.payments[index].filename = null;
            this.form.payments[index].temp_path = null;
            this.cleanFileListUploadVoucher(index);
        },
        ...mapActions(["loadConfiguration"]),
        initForm() {
            this.initGroupItems();
            this.discount_add_igv = true;

            this.totalQuantity = 0;
            this.children = [];
            this.monthsSelected = [];
            this.monthCollege = [];
            this.collegeYear = 20;
            this.errors = {};

            this.split_base = true;
            this.form = {
                channel_id: null,
                related_document: null,
                plate_number_id: null,
                plate_info: {},
                total_original_discount: 0,
                token_validated_for_discount: false,
                dispatcher_id: null,
                person_packer_id: null,
                person_dispatcher_id: null,
                transport_dispatch: {},
                company_id: null,
                dispatch_ticket_pdf_quantity: 1,
                dispatch_ticket_pdf: false,
                child_id: null,
                no_stock: this.configuration.document_no_stock || false,
                establishment_id: null,
                document_type_id: null,
                series_id: null,
                seller_id: this.idUser,
                number: "#",
                date_of_issue: moment().format("YYYY-MM-DD"),
                time_of_issue: moment().format("HH:mm:ss"),
                customer_id: null,
                currency_type_id: this.configuration.currency_type_id,
                purchase_order: null,
                exchange_rate_sale: 0,
                total_prepayment: 0,
                total_charge: 0,
                total_discount: 0,
                total_exportation: 0,
                total_free: 0,
                total_taxed: 0,
                total_unaffected: 0,
                total_exonerated: 0,
                total_igv: 0,
                total_base_isc: 0,
                total_isc: 0,
                total_base_other_taxes: 0,
                total_other_taxes: 0,
                total_plastic_bag_taxes: 0,
                total_taxes: 0,
                total_value: 0,
                total: 0,
                subtotal: 0,
                total_igv_free: 0,
                operation_type_id: null,
                date_of_due: moment().format("YYYY-MM-DD"),
                items: [],
                charges: [],
                discounts: [],
                attributes: [],
                guides: [],
                coupons: [],

                payments: [],
                prepayments: [],
                legends: [],
                detraction: {},
                additional_information: null,
                plate_number: null,
                has_prepayment: false,
                affectation_type_prepayment: null,
                actions: {
                    format_pdf: "a4",
                },
                hotel: {},
                transport: {},
                customer_address_id: null,
                pending_amount_prepayment: 0,
                payment_method_type_id: null,
                show_terms_condition: true,
                terms_condition: "",
                payment_condition_id: "01",
                fee: [],
                total_pending_payment: 0,
                has_retention: false,
                retention: {},
                perception: {},
                quotation_id: null,
                quotations_optional: this.quotations_optional,
                quotations_optional_value: this.quotations_optional_value,
                worker_full_name_tips: null, //propinas
                total_tips: 0, //propinas
            };

            this.form_cash_document = {
                document_id: null,
                sale_note_id: null,
            };
            this.changeSeller();

            if (this.form.payments.length == 0) {
                this.clickAddPayment();
            }
            this.clickAddInitGuides();
            this.is_receivable = false;
            this.total_global_discount = 0;
            this.total_global_charge = 0;
            this.is_amount = true;
            this.prepayment_deduction = false;
            this.imageDetraction = {};
            this.$eventHub.$emit("eventInitForm");

            this.initInputPerson();

            if (!this.configuration.restrict_receipt_date) {
                this.datEmision = {};
            }

            this.enabled_payments = true;
            this.readonly_date_of_due = false;
            this.total_discount_no_base = 0;

            this.calculate_customer_accumulated_points = 0;
            this.total_exchange_points = 0;

            this.retention_query_data = null;

            this.$eventHub.$emit("eventInitTip");
        },
        startConnectionQzTray() {
            if (!qz.websocket.isActive() && this.isAutoPrint) {
                // startConnection();
            }
        },
        changeRowExchangePoints(row, index) {
            row.item.change_free_affectation_igv =
                !row.item.change_free_affectation_igv;
            row.item.used_points_for_exchange = row.item
                .change_free_affectation_igv
                ? this.getUsedPoints(row)
                : null;
            this.setTotalExchangePoints(); //in mixins
            this.changeRowFreeAffectationIgv(row, index);
        },
        async changeRowFreeAffectationIgv(row, index) {
            if (row.item.change_free_affectation_igv) {
                this.form.items[index].affectation_igv_type_id = "15";
                this.form.items[index].affectation_igv_type = await _.find(
                    this.affectation_igv_types,
                    { id: this.form.items[index].affectation_igv_type_id }
                );
            } else {
                this.form.items[index].affectation_igv_type_id =
                    this.form.items[
                        index
                    ].item.original_affectation_igv_type_id;
                this.form.items[index].affectation_igv_type = await _.find(
                    this.affectation_igv_types,
                    { id: this.form.items[index].affectation_igv_type_id }
                );
            }
            this.form.items[index] = await calculateRowItem(
                row,
                this.form.currency_type_id,
                this.form.exchange_rate_sale,
                this.percentage_igv
            );
            await this.calculateTotal();
        },
        async processItemsForNotesNotGroup() {
            let itemsNotGroupForNotes = localStorage.getItem(
                "itemsNotGroupForNotes"
            );

            if (itemsNotGroupForNotes) {
                let itemsParsed = JSON.parse(itemsNotGroupForNotes);

                // prepare - validate prop presentation and others
                this.form.items = await this.onPrepareItems(itemsParsed).map(
                    (element) => {
                        element.item.presentation = element.item.presentation
                            ? element.item.presentation
                            : [];
                        return element;
                    }
                );

                await this.calculateTotal();
                localStorage.removeItem("itemsNotGroupForNotes");
            }
        },
        setItemFromResponse(item, itemsParsed, sum_quantity = false) {
            /* Obtiene el igv del item, si no existe, coloca el gravado*/
            if (item.sale_affectation_igv_type !== undefined) {
                item.affectation_igv_type = item.sale_affectation_igv_type;
            } else {
                item.affectation_igv_type = {
                    active: 1,
                    description: "Gravado - Operación Onerosa",
                    exportation: 0,
                    free: 0,
                    id: "10",
                };
            }
            item.presentation = {};
            item.unit_price = item.sale_unit_price;
            item.item = {
                amount_plastic_bag_taxes: item.amount_plastic_bag_taxes,
                attributes: item.attributes,
                brand: item.brand,
                calculate_quantity: item.calculate_quantity,
                category: item.category,
                currency_type_id: item.currency_type_id,
                currency_type_symbol: item.currency_type_symbol,
                description: item.description,
                full_description: item.full_description,
                has_igv: item.has_igv,
                has_plastic_bag_taxes: item.has_plastic_bag_taxes,
                id: item.id,
                internal_id: item.internal_id,
                item_unit_types: item.item_unit_types,
                lots: item.lots,
                lots_enabled: item.lots_enabled,
                lots_group: item.lots_group,
                model: item.model,
                presentation: {},
                purchase_affectation_igv_type_id:
                    item.purchase_affectation_igv_type_id,
                purchase_unit_price: item.purchase_unit_price,
                sale_affectation_igv_type_id: item.sale_affectation_igv_type_id,
                sale_unit_price: item.sale_unit_price,
                series_enabled: item.series_enabled,
                stock: item.stock,
                unit_price: item.sale_unit_price,
                unit_type_id: item.unit_type_id,
                unit_type_symbol: item.unit_type_symbol,
                warehouses: item.warehouses,
            };
            item.IdLoteSelected = null;
            if (item.affectation_igv_type_id === undefined) {
                item.affectation_igv_type_id = item.affectation_igv_type.id;
                // item.affectation_igv_type_id = "10";
            }
            item.discounts = [];
            item.charges = [];
            item.item_id = item.id;
            item.unit_price_value = item.sale_unit_price;
            item.input_unit_price_value = item.sale_unit_price;

            item.quantity = 1;

            if (sum_quantity) {
                const quantity_from_item_response =
                    this.getQuantityFromItemResponse(item, itemsParsed);
                if (quantity_from_item_response > 0)
                    item.quantity = quantity_from_item_response;
            } else {
                let tempItem = itemsParsed.find(
                    (ip) => ip.item_id == item.id || ip.id == item.id
                );

                if (tempItem !== undefined) {
                    item.quantity = tempItem.quantity;
                }
            }
            const unit_price_form_item_response = this.getPriceFromItemResponse(
                item,
                itemsParsed
            );
            if (unit_price_form_item_response > 0)
                item.item.unit_price = unit_price_form_item_response;
            // item.quantity = itemsParsed.find(ip => ip.item_id == item.id).quantity;
            item.warehouse_id = null;

            return item;
        },
        getPriceFromItemResponse(item, itemsParsed) {
            const group_items = itemsParsed.filter(
                (ip) => ip.item_id == item.id || ip.id == item.id
            );

            let [row] = group_items;
            return parseFloat(row.unit_price);
        },
        getQuantityFromItemResponse(item, itemsParsed) {
            const group_items = itemsParsed.filter(
                (ip) => ip.item_id == item.id || ip.id == item.id
            );

            return _.sumBy(group_items, function (row) {
                return parseFloat(row.quantity);
            });
        },
        disabledSeries() {
            return (
                this.configuration.restrict_series_selection_seller &&
                this.typeUser !== "admin"
            );
        },
        setDefaultSerieByDocument() {
            if (this.authUser.multiple_default_document_types) {
                const default_document_type_serie = _.find(
                    this.authUser.default_document_types,
                    { document_type_id: this.form.document_type_id }
                );

                if (default_document_type_serie) {
                    const exist_serie = _.find(this.series, {
                        id: default_document_type_serie.series_id,
                    });
                    if (exist_serie)
                        this.form.series_id =
                            default_document_type_serie.series_id;
                }
            }
        },
        // #307 Ajuste para seleccionar automaticamente el tipo de comprobante y serie
        setDefaultDocumentType(from_function) {
            if (this.authUser.multiple_default_document_types) return;

            this.default_series_type = this.configuration.user.serie;
            this.default_document_type = this.configuration.user.document_id;
            // if (this.default_document_type === undefined) this.default_document_type = null;
            // if (this.default_series_type === undefined) this.default_series_type = null;

            let alt = _.find(this.document_types, {
                id: this.default_document_type,
            });
            if (this.default_document_type !== null && alt !== undefined) {
                this.form.document_type_id = this.default_document_type;
                this.changeDocumentType();
                alt = _.find(this.series, { id: this.default_series_type });
                if (this.default_series_type !== null && alt !== undefined) {
                    this.form.series_id = this.default_series_type;
                }
            }
        },
        async onSetFormData(data) {
            if (this.table && !this.is_integrate_system) {
                this.payed = data.total_canceled || data.paid;
            }
            let exchange_rate_sale = this.form.exchange_rate_sale;
            let exchange_rate_sale_today = parseFloat(
                this.form.exchange_rate_sale
            );
            this.lastInput = data?.customer?.search_telephone || this.lastInput;
            let exchange_rate_sale_sale = parseFloat(data.exchange_rate_sale);
            if (exchange_rate_sale_sale != exchange_rate_sale_today) {
                await this.$confirm(
                    `El tipo de cambio de la cotización es ${exchange_rate_sale_sale}, y el tipo de cambio de hoy es ${exchange_rate_sale_today}.`,
                    "TIPO DE CAMBIO",
                    {
                        confirmButtonText: `Cambiar a ${exchange_rate_sale_today}`,
                        cancelButtonText: `Mantener en ${exchange_rate_sale_sale}`,
                        type: "warning",
                    }
                )
                    .then(() => {
                        exchange_rate_sale = this.form.exchange_rate_sale;
                    })
                    .catch(() => {
                        exchange_rate_sale = data.exchange_rate_sale;
                    });
            }
            this.currency_type = await _.find(this.currency_types, {
                id: data.currency_type_id,
            });
            if (data.periods && data.periods.length > 0) {
                this.monthsSelected = this.formatRecords(data.periods);
                this.setMonths();
            }

            this.form.establishment_id = data.establishment_id;
            this.form.document_type_id = data.document_type_id;
            if (this.company.is_rus) {
                this.form.document_type_id = "03";
            }
            this.changeDocumentType();

            this.form.id = data.id;
            this.form.hash = data.hash;
            this.form.number = data.number;
            if (data.related_document) {
                let [related] = data.related_document;
                if (related) {
                    let { related_document } = related;
                    this.form.related_document = related_document;
                }
            }
            if (this.copy) {
                this.form.date_of_issue = moment().format("YYYY-MM-DD");
            } else {
                this.form.date_of_issue = moment(data.date_of_issue).format(
                    "YYYY-MM-DD"
                );
            }
            (this.form.quotations_optional = data.quotations_optional),
                (this.form.quotations_optional_value =
                    data.quotations_optional_value);
            this.form.time_of_issue = data.time_of_issue;
            this.form.customer_id = data.customer_id;
            this.changeCustomer();
            this.form.currency_type_id = data.currency_type_id;
            // this.form.exchange_rate_sale = data.exchange_rate_sale;
            this.form.exchange_rate_sale = exchange_rate_sale;
            this.form.external_id = data.external_id;
            this.form.reference_data = data.reference_data;
            this.form.dispatch_ticket_pdf = data.dispatch_ticket_pdf;
            this.form.dispatcher_id = data.dispatcher_id;
            this.form.person_packer_id = data.person_packer_id;
            this.form.person_dispatcher_id = data.person_dispatcher_id;
            this.form.dispatch_ticket_pdf_quantity =
                data.dispatch_ticket_pdf_quantity;
            this.form.filename = data.filename;
            this.form.group_id = data.group_id;
            this.form.perception = data.perception;
            this.form.note = data.note;
            this.form.plate_number = data.plate_number;
            // this.form.payments = data.payments;

            this.form.prepayments = data.prepayments || [];
            this.form.legends = [];
            // this.form.detraction = data.detraction;
            this.form.detraction = data.detraction ? data.detraction : {};
            this.form.sale_notes_relateds = data.sale_notes_relateds
                ? data.sale_notes_relateds
                : null;
            this.form.affectation_type_prepayment =
                data.affectation_type_prepayment;
            this.form.purchase_order = data.purchase_order;
            this.form.pending_amount_prepayment =
                data.pending_amount_prepayment || 0;
            this.form.payment_method_type_id = data.payment_method_type_id;
            this.form.charges = data.charges || [];
            this.form.sale_note_id = data.sale_note_id;

            this.form.discounts = this.prepareDataGlobalDiscount(data);
            // this.form.discounts = data.discounts || [];

            this.form.seller_id = data.seller_id;
            this.form.items = this.form.items.map((item) => {
                item.currency_type_id = this.form.currency_type_id;

                return item;
            });
            this.form.items = this.onPrepareItems(data.items);

            // this.form.series = data.series; //form.series no llena el selector
            if (this.table !== "quotations" && !this.company.is_rus) {
                this.$store.commit(
                    "setSeries",
                    this.onSetSeries(data.document_type_id, data.series)
                );
            }

            //
            // this.series = this.onSetSeries(data.document_type_id, data.series);
            this.form.state_type_id = data.state_type_id;
            this.form.total_discount = parseFloat(data.total_discount);
            this.form.total_exonerated = parseFloat(data.total_exonerated);
            this.form.total_exportation = parseFloat(data.total_exportation);
            this.form.total_free = parseFloat(data.total_free);
            this.form.total_igv = parseFloat(data.total_igv);
            this.form.total_isc = parseFloat(data.total_isc);
            this.form.total_base_isc = parseFloat(data.total_base_isc);
            this.form.total_base_other_taxes = parseFloat(
                data.total_base_other_taxes
            );
            this.form.total_other_taxes = parseFloat(data.total_other_taxes);
            this.form.total_plastic_bag_taxes = parseFloat(
                data.total_plastic_bag_taxes
            );
            this.form.total_prepayment = parseFloat(data.total_prepayment);
            this.form.total_taxed = parseFloat(data.total_taxed);
            this.form.total_taxes = parseFloat(data.total_taxes);
            this.form.total_unaffected = parseFloat(data.total_unaffected);
            this.form.total_value = parseFloat(data.total_value);
            this.form.total_charge = parseFloat(data.total_charge);
            this.total_global_charge = parseFloat(data.total_charge);
            this.form.total = parseFloat(data.total);
            this.form.subtotal = parseFloat(data.subtotal);
            this.form.total_igv_free = parseFloat(data.total_igv_free);
            if (!this.company.is_rus) {
                this.form.series_id = this.onSetSeriesId(
                    data.document_type_id,
                    data.series
                );
            }

            this.form.operation_type_id = data.invoice
                ? data.invoice.operation_type_id
                : data.operation_type_id;
            this.form.terms_condition = data.terms_condition || "";
            this.form.guides = data.guides || [];
            this.form.show_terms_condition = data.terms_condition
                ? true
                : false;
            this.form.attributes = [];
            this.form.customer = data.customer;
            this.form.has_prepayment = false;
            this.form.actions = {
                format_pdf: "a4",
            };
            this.form.hotel = {};
            this.form.transport = {};
            this.form.customer_address_id = null;
            this.form.type = "invoice";
            this.form.invoice = {
                operation_type_id: data.invoice
                    ? data.invoice.operation_type_id
                    : data.operation_type_id,
                date_of_due: data.invoice
                    ? data.invoice.date_of_due
                    : data.date_of_due,
            };
            // this.form.payment_condition_id = '01';

            let is_credit_installments = await _.find(data.fee, {
                payment_method_type_id: null,
            });
            this.form.payment_condition_id = is_credit_installments
                ? "03"
                : data.payment_condition_id;
            this.form.fee = data.fee;
            this.form.retention = data.retention;

            this.form.quotation_id = data.quotation_id;

            this.form.additional_information =
                this.onPrepareAdditionalInformation(
                    data.additional_information
                );

            if (
                (this.table == "quotations" &&
                    this.form.additional_information == "") ||
                this.form.additional_information == null
            ) {
                this.form.additional_information = data.description;
            }
            // this.form.additional_information = data.additional_information;
            // this.form.fee = [];
            this.prepareDataDetraction();
            this.prepareDataRetention();

            if (!data.guides) {
                this.clickAddInitGuides();
            }

            if (this.isGeneratedFromExternal) {
                this.preparePaymentsFee(data);
            }

            await this.reloadDataCustomers(this.form.customer_id);

            if (this.configuration.college) {
                this.children = [];
                this.form.child_id = null;
                await this.searchRemoteChildren("", true);
            }

            this.establishment = data.establishment;

            // this.changeDateOfIssue();
            // await this.filterCustomers();
            if (!this.is_integrate_system) {
                this.updateChangeDestinationSale();
            }

            this.prepareDataCustomer();

            if (data.website_id) {
                this.form.company_id = data.website_id;
                this.changeCompany();
            }
            this.calculateTotal();

            this.changeCurrencyType(false, false, this.table != "quotations");

            this.restoreGroupItems();
            if (data.plate_number_document) {
                let { plate_number_document } = data;

                this.searchRemotePlateNumbers(
                    plate_number_document.description
                ).then((response) => {
                    this.form.plate_number_id = plate_number_document.id;
                    this.changePlateNumber(this.form.plate_number_id);
                });
            }

            // this.currency_type = _.find(this.currency_types, {'id': this.form.currency_type_id})
        },
        preparePaymentsFee(data) {
            if (this.isCreditPaymentCondition) {
                // credito
                if (
                    this.form.payment_condition_id === "02" &&
                    this.form.fee.length === 0
                ) {
                    this.clickAddFeeNew();
                    const index = 0;
                    //this.readonly_date_of_due = true;

                    if(data.document_payment_method_type){
                        this.form.fee[index].payment_method_type_id =
                        data.document_payment_method_type.id;
                    this.form.fee[index].amount = _.sumBy(
                        data.data_payments_fee,
                        "payment"
                    );

                    this.changePaymentMethodType(index);
                    }
                }
            }
        },
        prepareDataGlobalDiscount(data) {
            const discounts = data.discounts
                ? Object.values(data.discounts)
                : [];

            if (discounts.length === 1) {
                if (
                    discounts[0].is_amount !== undefined &&
                    discounts[0].is_amount !== null
                ) {
                    this.is_amount = discounts[0].is_amount;
                }

                this.total_global_discount = this.is_amount
                    ? discounts[0].amount
                    : discounts[0].factor * 100;
            }

            return discounts;
        },
        async prepareDataCustomer() {
            this.customer_addresses = [];
            let customer = await _.find(this.customers, {
                id: this.form.customer_id,
            });
            this.customer_addresses = customer.addresses || [];

            this.form.customer_address_id = this.form.customer
                ? this.form.customer.address_id
                : null;

            if (customer.address) {
                this.customer_addresses.unshift({
                    id: null,
                    address: customer.address,
                });
            }
        },
        prepareDataRetention() {
            this.form.has_retention = !_.isEmpty(this.form.retention);

            if (this.form.has_retention) {
                this.setTotalPendingAmountRetention(this.form.retention.amount);

                this.retention_query_data = { ...this.form.retention };
            }
        },
        async prepareDataDetraction() {
            // this.has_data_detraction = (this.form.detraction) ? true : false
            this.has_data_detraction = !_.isEmpty(this.form.detraction);

            if (this.has_data_detraction) {
                let legend_value =
                    this.form.operation_type_id === "1001"
                        ? "Operación sujeta a detracción"
                        : "Operación Sujeta a Detracción - Servicios de Transporte - Carga";
                let legend = await _.find(this.form.legends, { code: "2006" });
                if (!legend)
                    this.form.legends.push({
                        code: "2006",
                        value: legend_value,
                    });
            }
        },
        updateChangeDestinationSale() {
            if (this.form.payment_condition_id == "01") {
                if (
                    this.configuration.destination_sale &&
                    this.payment_destinations.length > 0
                ) {
                    let cash = _.find(this.payment_destinations, {
                        id: "cash",
                    });
                    if (cash) {
                        if (this.form.payments[0] !== undefined) {
                            this.form.payments[0].payment_destination_id =
                                cash.id;
                        } else {
                            // this.form.payments.push({
                            //     payment_destination_id: cash.id, //genera error al editar cpe enviado desde api
                            // })
                        }
                    } else {
                        this.form.payment_destination_id =
                            this.payment_destinations[0].id;
                        this.form.payments[0].payment_destination_id =
                            this.payment_destinations[0].id;
                    }
                }
            }
        },
        onPrepareAdditionalInformation(data) {
            let obs = null;
            if (Array.isArray(data)) {
                if (data.length > 0) {
                    if (data[0] == "") {
                        return obs;
                    }
                }
                return data.join("|");
            }

            return data;
        },
        onPrepareItems(items) {
            return items.map((i) => {
                if (i.item.label_color == undefined && i.label_color) {
                    i.item.label_color = i.label_color;
                }
                if (this.table) {
                    i.currency_type_id = this.form.currency_type_id;
                    i.unit_price_value = Number(i.unit_value);
                    i.input_unit_price_value = i.item.has_igv
                        ? i.item.unit_price
                        : i.unit_value;
                } else {
                    i.unit_price_value = i.unit_value;
                    i.input_unit_price_value = i.item.has_igv
                        ? i.unit_price
                        : i.unit_value;
                }

                i.discounts = i.discounts ? Object.values(i.discounts) : [];
                let total_discount = 0;
                if (i.discounts.length > 0) {
                    total_discount = i.discounts.reduce((acc, discount) => {
                        return acc + Number(discount.amount);
                    }, 0);
                }
                i.total_discount = total_discount;
                // i.discounts = i.discounts || [];
                i.charges = i.charges || [];
                i.attributes = i.attributes || [];
                i.item.id = i.item_id;

                let groupId = i.item.groupId;
                if (groupId) {
                    i.item.inGroup = true;
                }
                i.item.original_quantity = i.quantity;
                i.additional_information = this.onPrepareAdditionalInformation(
                    i.additional_information
                );
                i.discounts_acc = i.discounts_acc
                    ? Object.values(i.discounts_acc)
                    : [];
                i.item = this.onPrepareIndividualItem(i);
                return i;
            });
        },
        onPrepareIndividualItem(data) {
            let new_item = data.item;
            new_item.sale_unit_price = data.unit_price;
            new_item.unit_price = data.unit_price;

            let currency_type = _.find(this.currency_types, {
                id: this.form.currency_type_id,
            });
            new_item.stock = data.stock;
            new_item.sale_affectation_igv_type_id =
                data.affectation_igv_type_id;
            let { discounts } = data;
            // let amount = 0;
            let { percentage_igv } = data;
            let amount = discounts.reduce((acc, discount) => {
                if (discount.discount_type_id == "00") {
                    return (
                        acc +
                        Number(discount.amount) * (percentage_igv / 100 + 1)
                    );
                } else {
                    return acc + Number(discount.amount);
                }
            }, 0);
            if (this.table) {
                let { exchange_rate_sale } = this.form;
                let currency_type_id = data.currency_type_id
                    ? data.currency_type_id
                    : new_item.currency_type_id;
                if (this.form.currency_type_id != currency_type_id) {
                    new_item.sale_unit_price =
                        (Number(new_item.unit_price) + amount) *
                        exchange_rate_sale;
                    new_item.unit_price =
                        (Number(new_item.unit_price) + amount) *
                        exchange_rate_sale;
                } else {
                    new_item.sale_unit_price =
                        Number(new_item.unit_price) + amount;
                    new_item.unit_price = Number(new_item.unit_price) + amount;
                }
            } else {
                new_item.sale_unit_price = Number(data.unit_price) + amount;

                new_item.unit_price = Number(data.unit_price) + amount;
            }
            new_item.currency_type_id = currency_type.id;
            new_item.currency_type_symbol = currency_type.symbol;
            return new_item;
        },
        onSetSeriesId(documentType, serie) {
            if(this.user_default_document_types.length > 0){
                const user_default_document_type = this.user_default_document_types.find(item => item.document_type_id === documentType);
                if(user_default_document_type){
                    return user_default_document_type.series_id;
                }
            }
            if (!this.all_series || this.all_series.length == 0) return null;
            if (
                this.all_series &&
                !Array.isArray(this.all_series) &&
                Object.values(this.all_series)
            ) {
                // this.all_series = Object.values(this.all_series);
                this.$store.commit(
                    "setAllSeries",
                    Object.values(this.all_series)
                );
            }
            const find = this.all_series.find(
                (s) => s.document_type_id == documentType && s.number == serie
            );
            if (find) {
                return find.id;
            }
            return null;
        },
        onSetSeries(documentType, serie) {
            if (
                this.all_series &&
                !Array.isArray(this.all_series) &&
                Object.values(this.all_series)
            ) {
                this.$store.commit(
                    "setAllSeries",
                    Object.values(this.all_series)
                );
            }
            if (!this.all_series || this.all_series.length == 0) return [];
            const find = this.all_series.find(
                (s) => s.document_type_id == documentType && s.number == serie
            );
            if (find) {
                return [find];
            }
            return [];
        },
        getPrepayment(index) {
            return _.find(this.prepayment_documents, {
                id: this.form.prepayments[index].document_id,
            });
        },
        inputAmountPrepayment(index) {
            let prepayment = this.getPrepayment(index);

            if (
                parseFloat(this.form.prepayments[index].amount) >
                parseFloat(prepayment.amount)
            ) {
                this.form.prepayments[index].amount = prepayment.amount;
                this.$message.error(
                    "El monto debe ser menor o igual al del anticipo"
                );
            }

            this.form.prepayments[index].total =
                this.form.prepayments[index].real_amount;

            this.form.prepayments[index].amount =
                this.form.affectation_type_prepayment == 10
                    ? _.round(
                          this.form.prepayments[index].real_amount /
                              (1 + this.percentage_igv),
                          2
                      )
                    : this.form.prepayments[index].real_amount;

        
            this.changeTotalPrepayment();
        },
        changeDestinationSale() {
            if (
                this.configuration.destination_sale &&
                this.payment_destinations.length > 0
            ) {
                let cash = _.find(this.payment_destinations, { id: "cash" });
                if (cash) {
                    this.form.payments[0].payment_destination_id = cash.id;
                } else {
                    this.form.payment_destination_id =
                        this.payment_destinations[0].id;
                    this.form.payments[0].payment_destination_id =
                        this.payment_destinations[0].id;
                }
            }
        },
        changePaymentDestination(index) {
            this.checkHasAdvance(index);
            this.$nextTick(() => {
                //    console.log(this.form.payments[index]);
                let payment = this.form.payments[index];
                this.form.payments[index] = payment;
            });
        },
        changeEnabledPayments() {
            // this.clickAddPayment()
            // this.form.date_of_due = this.form.date_of_issue
            // this.readonly_date_of_due = false
            // this.form.payment_method_type_id = null
        },
        changePaymentMethodTypeCredit(index) {
            let id = "09";
            if (
                this.form.fee[index] !== undefined &&
                this.form.fee[index].payment_method_type_id !== undefined
            ) {
                id = this.form.fee[index].payment_method_type_id;
            }
            let payment_method_type = _.find(this.payment_method_types_credit, {
                id: id,
            });
            if (payment_method_type && payment_method_type.number_days) {
                this.form.date_of_due = moment(this.form.date_of_issue)
                    .add(payment_method_type.number_days, "days")
                    .format("YYYY-MM-DD");
                this.enabled_payments = false;
                this.form.payment_method_type_id = payment_method_type.id;

                let date = moment(this.form.date_of_issue)
                    .add(payment_method_type.number_days, "days")
                    .format("YYYY-MM-DD");

                // let date = moment()
                //     .add(payment_method_type.number_days, 'days')
                //     .format('YYYY-MM-DD')

                if (this.form.fee !== undefined) {
                    for (let index = 0; index < this.form.fee.length; index++) {
                        this.form.fee[index].date = date;
                    }
                }
            } else if (payment_method_type && payment_method_type.id == "09") {
                this.form.payment_method_type_id = payment_method_type.id;
                this.form.date_of_due = this.form.date_of_issue;
                // this.form.payments = []
                this.enabled_payments = false;
            } else {
                this.form.date_of_due = this.form.date_of_issue;
                this.readonly_date_of_due = false;
                this.form.payment_method_type_id = null;
                this.enabled_payments = true;
            }
        },
        changePaymentMethodType(index) {
            let id = "01";
            if (
                this.form.payments[index] !== undefined &&
                this.form.payments[index].payment_method_type_id !== undefined
            ) {
                id = this.form.payments[index].payment_method_type_id;
            } else if (
                this.form.fee[index] !== undefined &&
                this.form.fee[index].payment_method_type_id !== undefined
            ) {
                id = this.form.fee[index].payment_method_type_id;
            }
            let payment_method_type = _.find(this.payment_method_types, {
                id: id,
            });
            let isNoteCredit =
                payment_method_type && payment_method_type.id == "NC";
            if (payment_method_type && payment_method_type.number_days) {
                this.form.date_of_due = moment(this.form.date_of_issue)
                    .add(payment_method_type.number_days, "days")
                    .format("YYYY-MM-DD");
                this.enabled_payments = false;
                this.form.payment_method_type_id = payment_method_type.id;

                let date = moment(this.form.date_of_issue)
                    .add(payment_method_type.number_days, "days")
                    .format("YYYY-MM-DD");

                // let date = moment()
                //     .add(payment_method_type.number_days, 'days')
                //     .format('YYYY-MM-DD')

                if (this.form.fee !== undefined) {
                    for (let index = 0; index < this.form.fee.length; index++) {
                        this.form.fee[index].date = date;
                    }
                }
            } else if (payment_method_type && payment_method_type.id == "09") {
                this.form.payment_method_type_id = payment_method_type.id;
                this.form.date_of_due = this.form.date_of_issue;
                // this.form.payments = []
                this.enabled_payments = false;
            } else {
                this.form.date_of_due = this.form.date_of_issue;
                this.readonly_date_of_due = false;
                this.form.payment_method_type_id = null;
                this.enabled_payments = true;
                if (isNoteCredit) {
                    this.$set(this.form.payments[index], "isNoteCredit", true);
                }
            }
        },
        selectDocumentType() {
            this.form.document_type_id = this.select_first_document_type_03
                ? "03"
                : "01";
            if (this.company.is_rus) {
                this.form.document_type_id = "03";
            }
        },
        async keyupCustomer() {
            if(!this.dialogNewPerson){
                this.loading = true;
                const module = await import("../persons/form.vue");
                this.dialogNewPerson = module.default;
                this.loading = false;
            }
            if (this.input_person.number) {
                if (!isNaN(parseInt(this.input_person.number))) {
                    switch (this.input_person.number.length) {
                        case 8:
                            this.input_person.identity_document_type_id = "1";
                            this.showDialogNewPerson = true;
                            break;

                        case 11:
                            this.input_person.identity_document_type_id = "6";
                            this.showDialogNewPerson = true;
                            break;
                        default:
                            this.input_person.identity_document_type_id = "6";
                            this.showDialogNewPerson = true;
                            break;
                    }
                }
            }
        },
        addDocumentDetraction(detraction) {
            this.form.detraction = detraction;
            // this.has_data_detraction = (detraction.pay_constancy || detraction.detraction_type_id || detraction.payment_method_id || (detraction.amount && detraction.amount >0)) ? true:false
            this.has_data_detraction = detraction
                ? detraction.has_data_detraction
                : false;

            this.changeDetractionType();
        },
        async clickAddItemInvoice() {
            // Component should be preloaded, but fallback if not
            if(!this.addItemDialog){
                await this.preloadItemComponent();
            }
            this.recordItem = null;
            this.showDialogAddItem = true;
        },
        getFormatUnitPriceRow(unit_price) {
            return _.round(unit_price, 6).toFixed(this.decimalQuantity);
            // return unit_price.toFixed(6)
        },
        discountGlobalPrepayment() {
            let global_discount = 0;
            let sum_total_prepayment = 0;

            this.form.prepayments.forEach((item) => {
                global_discount += parseFloat(item.amount);
                sum_total_prepayment += parseFloat(item.total);
            });

            // let base = (this.form.affectation_type_prepayment == 10) ? parseFloat(this.form.total_taxed):parseFloat(this.form.total_exonerated)
            let base = 0;

            switch (this.form.affectation_type_prepayment) {
                case 10:
                    base = parseFloat(this.form.total_taxed) + global_discount;
                    // base = parseFloat(this.form.total_taxed)
                    break;
                case 20:
                    base =
                        parseFloat(this.form.total_exonerated) +
                        global_discount;
                    break;
                case 30:
                    base =
                        parseFloat(this.form.total_unaffected) +
                        global_discount;
                    break;
            }

            let amount = _.round(global_discount, 2);
            let factor = _.round(amount / base, 5);

            this.form.total_prepayment = _.round(sum_total_prepayment, 2);
            // this.form.total_prepayment = _.round(global_discount, 2)

            if (this.form.affectation_type_prepayment == 10) {
                let discount = _.find(this.form.discounts, {
                    discount_type_id: "04",
                });

                if (global_discount > 0 && !discount) {
                    this.form.total_discount = _.round(amount, 2);
                    this.form.total_taxed = _.round(
                        this.form.total_taxed - amount,
                        2
                    );
                    this.form.total_igv = _.round(
                        this.form.total_taxed * this.percentage_igv,
                        2
                    );
                    this.form.total_taxes = _.round(this.form.total_igv, 2);
                    this.form.total = _.round(
                        this.form.total_taxed + this.form.total_taxes,
                        2
                    );

                    this.form.discounts.push({
                        discount_type_id: "04",
                        description:
                            "Descuentos globales por anticipos gravados que afectan la base imponible del IGV/IVAP",
                        factor: factor,
                        amount: amount,
                        base: base,
                    });
                } else {
                    let pos = this.form.discounts.indexOf(discount);

                    if (pos > -1) {
                        this.form.total_discount = _.round(amount, 2);
                        this.form.total_taxed = _.round(
                            this.form.total_taxed - amount,
                            2
                        );
                        this.form.total_igv = _.round(
                            this.form.total_taxed * this.percentage_igv,
                            2
                        );
                        this.form.total_taxes = _.round(this.form.total_igv, 2);
                        this.form.total = _.round(
                            this.form.total_taxed + this.form.total_taxes,
                            2
                        );

                        this.form.discounts[pos].base = base;
                        this.form.discounts[pos].amount = amount;
                        this.form.discounts[pos].factor = factor;
                    }
                }
            } else if (this.form.affectation_type_prepayment == 20) {
                let exonerated_discount = _.find(this.form.discounts, {
                    discount_type_id: "05",
                });

                this.form.total_discount = _.round(amount, 2);
                this.form.total_exonerated = _.round(
                    this.form.total_exonerated - amount,
                    2
                );
                this.form.total = this.form.total_exonerated;

                if (global_discount > 0 && !exonerated_discount) {
                    this.form.discounts.push({
                        discount_type_id: "05",
                        description:
                            "Descuentos globales por anticipos exonerados",
                        factor: factor,
                        amount: amount,
                        base: base,
                    });
                } else {
                    let position =
                        this.form.discounts.indexOf(exonerated_discount);

                    if (position > -1) {
                        this.form.discounts[position].base = base;
                        this.form.discounts[position].amount = amount;
                        this.form.discounts[position].factor = factor;
                    }
                }
            } else if (this.form.affectation_type_prepayment == 30) {
                let unaffected_discount = _.find(this.form.discounts, {
                    discount_type_id: "06",
                });

                this.form.total_discount = _.round(amount, 2);
                this.form.total_unaffected = _.round(
                    this.form.total_unaffected - amount,
                    2
                );
                this.form.total = this.form.total_unaffected;

                if (global_discount > 0 && !unaffected_discount) {
                    this.form.discounts.push({
                        discount_type_id: "06",
                        description:
                            "Descuentos globales por anticipos inafectos",
                        factor: factor,
                        amount: amount,
                        base: base,
                    });
                } else {
                    let position =
                        this.form.discounts.indexOf(unaffected_discount);
                    if (position > -1) {
                        this.form.discounts[position].base = base;
                        this.form.discounts[position].amount = amount;
                        this.form.discounts[position].factor = factor;
                    }
                }
            }
        },
        async changeDocumentPrepayment(index) {
            let prepayment = await _.find(this.prepayment_documents, {
                id: this.form.prepayments[index].document_id,
            });

            this.form.prepayments[index].number = prepayment.description;
            this.form.prepayments[index].document_type_id =
                prepayment.document_type_id;
            this.form.prepayments[index].amount = prepayment.amount;
            this.form.prepayments[index].total = prepayment.total;
            this.form.prepayments[index].real_amount = prepayment.real_amount;

            await this.changeTotalPrepayment();
        },
        clickAddPrepayment() {
            this.form.prepayments.push({
                document_id: null,
                number: null,
                document_type_id: null,
                amount: 0,
                total: 0,
            });

            this.changeTotalPrepayment();
        },
        clickRemovePrepayment(index) {
            this.form.prepayments.splice(index, 1);
            this.changeTotalPrepayment();
            if (this.form.prepayments.length == 0)
                this.deletePrepaymentDiscount();
        },
        async changePrepaymentDeduction() {
            this.form.prepayments = [];
            this.form.total_prepayment = 0;
            await this.deletePrepaymentDiscount();

            if (this.prepayment_deduction) {
                await this.initialValueATPrepayment();
                await this.changeTotalPrepayment();
                await this.getDocumentsPrepayment();
            } else {
                // this.form.total_prepayment = 0
                // await this.deletePrepaymentDiscount()
                this.cleanValueATPrepayment();
            }
        },
        setPendingAmount() {
            this.form.pending_amount_prepayment = this.form.has_prepayment
                ? this.form.total
                : 0;
        },
        initialValueATPrepayment() {
            this.form.affectation_type_prepayment = !this.form
                .affectation_type_prepayment
                ? 10
                : this.form.affectation_type_prepayment;
        },
        cleanValueATPrepayment() {
            this.form.affectation_type_prepayment = null;
        },
        changeHasPrepayment() {
            if (this.form.has_prepayment) {
                this.initialValueATPrepayment();
            } else {
                this.cleanValueATPrepayment();
            }

            this.setPendingAmount();
        },
        async changeAffectationTypePrepayment() {
            await this.initialValueATPrepayment();

            if (this.prepayment_deduction) {
                this.form.total_prepayment = 0;
                await this.deletePrepaymentDiscount();
                await this.changePrepaymentDeduction();
            }
        },
        async deletePrepaymentDiscount() {
            let discount = await _.find(this.form.discounts, {
                discount_type_id: "04",
            });
            let discount_exonerated = await _.find(this.form.discounts, {
                discount_type_id: "05",
            });
            let discount_unaffected = await _.find(this.form.discounts, {
                discount_type_id: "06",
            });

            let pos = this.form.discounts.indexOf(discount);
            if (pos > -1) {
                this.form.discounts.splice(pos, 1);
                this.changeTotalPrepayment();
            }

            let pos_exonerated =
                this.form.discounts.indexOf(discount_exonerated);
            if (pos_exonerated > -1) {
                this.form.discounts.splice(pos_exonerated, 1);
                this.changeTotalPrepayment();
            }

            let pos_unaffected =
                this.form.discounts.indexOf(discount_unaffected);
            if (pos_unaffected > -1) {
                this.form.discounts.splice(pos_unaffected, 1);
                this.changeTotalPrepayment();
            }
        },
        getDocumentsPrepayment() {
            if(!this.form.customer_id){
                return this.$message.error('No se puede obtener los documentos de anticipo sin un cliente');
            }
            this.$http
                .get(
                    `/${this.resource}/prepayments/${this.form.affectation_type_prepayment}/${this.form.customer_id}`
                )
                .then((response) => {
                    this.prepayment_documents = response.data;
                });
        },
        changeTotalPrepayment() {
            this.calculateTotal();
        },
        isActiveBussinessTurn(value) {
            return _.find(this.business_turns, { value: value }) ? true : false;
        },
        async visibleDialogReportCustomer() {
            if(!this.dialogReportCustomer){
                this.loading = true;
                const module = await import("./partials/report_customer.vue");
                this.dialogReportCustomer = module.default;
                this.loading = false;
            }
            this.report_to_customer_id = this.form.customer_id;
            this.showDialogReportCustomer = true;
        },
        async clickAddDocumentHotel() {
            if(!this.dialogFormHotel){
                this.loading = true;
                const module = await import("../../../../../modules/BusinessTurn/Resources/assets/js/views/hotels/form.vue");
                this.dialogFormHotel = module.default;
                this.loading = false;
            }
            this.showDialogFormHotel = true;
        },
        async clickAddDispatchTransport() {
            if(!this.dialogFormDispatch){
                this.loading = true;
                const module = await import(
                    "../../../../../modules/BusinessTurn/Resources/assets/js/views/transports/dispatch_form.vue"
                );
                this.dialogFormDispatch = module.default;
                this.loading = false;
            }
            this.showDialogFormDispatch = true;
        },
        async clickAddDocumentTransport() {
            if(!this.dialogFormTransport){
                this.loading = true;
                const module = await import(
                    "../../../../../modules/BusinessTurn/Resources/assets/js/views/transports/form.vue"
                );
                this.dialogFormTransport = module.default;
                this.loading = false;
            }
            this.showDialogFormTransport = true;

        },
        addDocumentHotel(hotel) {
            this.form.hotel = hotel;
        },
        addDocumentTransport(transport) {
            this.form.transport = transport;
        },
        addDispatchTransport(dispatch) {
            this.form.transport_dispatch = dispatch;
        },
        changeIsReceivable() {},
        clickAddPayment() {
            let id = "01";
            if (
                this.cash_payment_metod !== undefined &&
                this.cash_payment_metod[0] !== undefined
            ) {
                id = this.cash_payment_metod[0].id;
            }
            let total = 0;
            if (this.form.total !== undefined) {
                total = this.form.total;
            }
            this.form.date_of_due = moment().format("YYYY-MM-DD");
            this.form.payments.push({
                id: null,
                document_id: null,
                date_of_payment: moment().format("YYYY-MM-DD"),
                payment_method_type_id: this.getDefaultPaymentMethodType(),
                reference: null,
                payment_destination_id: this.getPaymentDestinationId(),
                payment: total,
                isNoteCredit: false,
                payment_received: true,
                filename: null,
                temp_path: null,
                file_list: [],
            });

            this.calculatePayments();
        },
        getPaymentDestinationId() {
            if (
                this.configuration.destination_sale &&
                this.payment_destinations.length > 0
            ) {
                let cash = _.find(this.payment_destinations, { id: "cash" });

                return cash ? cash.id : this.payment_destinations[0].id;
            }

            return null;
        },
        clickCancel(index) {
            this.form.payments.splice(index, 1);
            this.calculatePayments();
        },
        async ediItem(row, index) {
            row.indexi = index;
            this.recordItem = row;
            if (row.item.meter && row.item.meter > 0) {
                this.recordItem.unit_price /= row.item.meter;
                this.recordItem.input_unit_price_value /= row.item.meter;
            }
            this.showDialogAddItem = true;
        },
        async searchRemoteChildren(input, fromParent = false) {
            // if(!this.form.customer_id){
            //     this.$message({
            //         type: 'warning',
            //         message: 'Seleccione un cliente'
            //     });
            //     return false;
            // }
            if (input.length > 2 || fromParent) {
                this.loading_search = true;

                let parameters = {
                    column: "name",
                    users: "children",
                    isPharmacy: false,
                    value: input,
                    parent_id: this.form.customer_id,
                };

                const response = await this.$http.post(
                    `/suscription/client/records`,
                    {
                        ...parameters,
                    }
                );
                const { data } = response;
                this.children = data.data;
                if (fromParent && this.children.length != 0) {
                    let childFound = false;
                    if (this.documentId) {
                        const resp = await this.$http.get(
                            `/suscription/client/get_child_from_document/${this.documentId}/${this.form.customer_id}/01`
                        );

                        if (resp.data.success) {
                            this.form.child_id = resp.data.child_id;
                            this.getItemPlanSuscription();
                            childFound = true;
                        }
                    }
                    if (!childFound) {
                        this.form.child_id = this.children[0].id;
                        this.getItemPlanSuscription();
                    }
                }
                this.loading_search = false;
            }
        },
        searchRemoteCustomers(input) {
            clearTimeout(this.searchTimeout);

            this.searchTimeout = setTimeout(() => {
            if (input.length > 0) {
                this.loading_search = true;
                let parameters = `input=${input}&document_type_id=${this.form.document_type_id}&operation_type_id=${this.form.operation_type_id}`;
                this.lastInput = input;
                this.$http
                    .get(`/${this.resource}/search/customers-limit?${parameters}`)
                    .then((response) => {
                        this.customers = response.data.data;
                        this.loading_search = false;
                        this.input_person.number =
                            this.customers.length == 0 ? input : null;

                        /* if (this.customers.length == 0) {
                            this.filterCustomers()
                            this.input_person.number = input
                        } */
                    });
            } else {
                this.filterCustomers();
                this.input_person.number = null;
            }
        }, 750);
        },
        changeRetention() {
            if (this.form.has_retention) {
                let base = this.form.total;
                let percentage = _.round(
                    parseFloat(this.configuration.igv_retention_percentage) / 100,
                    5
                );
                let amount = _.round(base * percentage, 2);

                let amount_pen = amount;
                let amount_usd = _.round(
                    amount / this.form.exchange_rate_sale,
                    2
                );
                if (this.form.currency_type_id === "USD") {
                    amount_usd = amount;
                    amount_pen = _.round(
                        amount * this.form.exchange_rate_sale,
                        2
                    );
                }

                this.form.retention = {
                    base: base,
                    code: "62", //Código de Retención del IGV
                    amount: amount,
                    percentage: percentage,
                    currency_type_id: this.form.currency_type_id,
                    exchange_rate: this.form.exchange_rate_sale,
                    amount_pen: amount_pen,
                    amount_usd: amount_usd,
                };

                this.setDataVoucherRetention();
                this.setTotalPendingAmountRetention(amount);
            } else {
                this.form.retention = {};
                if (this.form.detraction) {
                    this.form.total_pending_payment =
                        this.form.total - this.form.detraction.amount;
                } else {
                    this.form.total_pending_payment = 0;
                }

                // this.form.total_pending_payment = 0;
                this.calculateAmountToPayments();
            }
        },
        setDataVoucherRetention() {
            if (this.isUpdateDocument && this.retention_query_data) {
                this.form.retention.voucher_date_of_issue =
                    this.retention_query_data.voucher_date_of_issue;
                this.form.retention.voucher_number =
                    this.retention_query_data.voucher_number;
                this.form.retention.voucher_amount =
                    this.retention_query_data.voucher_amount;
                this.form.retention.voucher_filename =
                    this.retention_query_data.voucher_filename;
            }
        },
        setTotalPendingAmountRetention(amount) {
            //monto neto pendiente aplica si la condicion de pago es credito
            this.form.total_pending_payment = ["02", "03"].includes(
                this.form.payment_condition_id
            )
                ? this.form.total - amount
                : 0;
            this.calculateAmountToPayments();
        },
        initInputPerson() {
            this.input_person = {
                number: null,
                identity_document_type_id: null,
            };
        },
        resetForm() {
            this.activePanel = 0;
            this.initForm();
            this.form.establishment_id =
                this.establishments.length > 0
                    ? this.establishments[0].id
                    : null;
            this.form.document_type_id =
                this.document_types.length > 0
                    ? this.document_types[0].id
                    : null;
            this.form.operation_type_id =
                this.operation_types.length > 0
                    ? this.operation_types[0].id
                    : null;
            this.form.seller_id = this.sellers.length > 0 ? this.idUser : null;
            this.selectDocumentType();
            this.changeEstablishment();
            this.changeDocumentType();
            this.changeDateOfIssue();
            this.changeCurrencyType();
        },
        async changeOperationType() {
            await this.filterCustomers();
            await this.setDataDetraction();
            if (this.form.operation_type_id == "2001") {
                this.calculateTotal();
            }
        },
        async setDataDetraction() {
        
            if (this.form.operation_type_id === "1001") {
                this.showDialogDocumentDetraction = true;
                let legend = await _.find(this.form.legends, { code: "2006" });
                if (!legend)
                    this.form.legends.push({
                        code: "2006",
                        value: "Operación sujeta a detracción",
                    });
                this.form.detraction.bank_account =
                    this.company.detraction_account;
            } else if (this.form.operation_type_id === "1004") {
                this.showDialogDocumentDetraction = true;
                let legend = await _.find(this.form.legends, { code: "2006" });
                if (!legend)
                    this.form.legends.push({
                        code: "2006",
                        value: "Operación Sujeta a Detracción - Servicios de Transporte - Carga",
                    });
                this.form.detraction.bank_account =
                    this.company.detraction_account;
            } else {
                _.remove(this.form.legends, { code: "2006" });
                this.form.detraction = {};
            }

            this.calculateAmountToPayments();
        },
        async changeDetractionType() {
            if (this.form.detraction) {
                let transport = this.form.operation_type_id == "1004";

                if (this.form.currency_type_id == "PEN") {
                    this.form.detraction.amount = _.round(
                        parseFloat(
                            transport
                                ? Math.max(
                                      this.form.detraction
                                          .reference_value_service,
                                      this.form.total
                                  )
                                : this.form.total
                        ) *
                            (parseFloat(this.form.detraction.percentage) / 100),
                        this.detractionDecimalQuantity
                    );

                    this.form.total_pending_payment =
                        this.form.total - this.form.detraction.amount;
                } else {
                    this.form.detraction.amount = _.round(
                        parseFloat(
                            transport
                                ? Math.max(this.form.detraction.reference_value_service, this.form.total)
                                : this.form.total
                        ) *
                            this.form.exchange_rate_sale *
                            (parseFloat(this.form.detraction.percentage) / 100),
                        this.detractionDecimalQuantity
                    );

                    this.form.total_pending_payment = _.round(
                        this.form.total -
                            this.form.detraction.amount /
                                this.form.exchange_rate_sale,
                        2
                    );
                }

                this.calculateAmountToPayments();
            }
        },
        calculateAmountToPayments() {
            this.calculatePayments();
            this.calculateFee();
        },
        validateDetraction() {
            if (["1001", "1004"].includes(this.form.operation_type_id)) {
                let detraction = this.form.detraction;

                let tot =
                    this.form.currency_type_id == "PEN"
                        ? this.form.total
                        : this.form.total * this.form.exchange_rate_sale;
                let total_restriction =
                    this.form.operation_type_id == "1001" ? 700 : 400;

                if (tot <= total_restriction)
                    return {
                        success: false,
                        message: `El importe de la operación debe ser mayor a S/ ${total_restriction}.00 o equivalente en USD`,
                    };

                if (!detraction.detraction_type_id)
                    return {
                        success: false,
                        message:
                            "El campo bien o servicio sujeto a detracción es obligatorio",
                    };

                if (!detraction.payment_method_id)
                    return {
                        success: false,
                        message:
                            "El campo método de pago - detracción es obligatorio",
                    };

                if (!detraction.bank_account)
                    return {
                        success: false,
                        message: "El campo cuenta bancaria es obligatorio",
                    };

                if (detraction.amount <= 0)
                    return {
                        success: false,
                        message:
                            "El campo total detracción debe ser mayor a cero",
                    };
            }

            return { success: true };
        },
        changeEstablishment() {
            this.establishment = _.find(this.all_establishments, {
                id: this.form.establishment_id,
            }) || null;
            this.filterSeries();
            this.selectDefaultCustomer();
        },

        async selectDefaultCustomer() {
            if (this.establishment.customer_id) {
                let temp_all_customers = this.all_customers;
                let temp_customers = this.customers;
                await this.$http
                    .get(
                        `/${this.resource}/search/customer/${this.establishment.customer_id}`
                    )
                    .then((response) => {
                        let data_customer = response.data.customers;
                        temp_all_customers = temp_all_customers.push(
                            ...data_customer
                        );
                        temp_customers = temp_customers.push(...data_customer);
                    });
                temp_all_customers = this.all_customers.filter(
                    (item, index, self) =>
                        index === self.findIndex((t) => t.id === item.id)
                );
                temp_customers = this.customers.filter(
                    (item, index, self) =>
                        index === self.findIndex((t) => t.id === item.id)
                );
                this.all_customers = temp_all_customers;
                this.customers = temp_customers;
                await this.filterCustomers();
                let alt = _.find(this.customers, {
                    id: this.establishment.customer_id,
                });

                if (alt !== undefined) {
                    this.form.customer_id = this.establishment.customer_id;
                    this.validateCustomerRetention(
                        alt.identity_document_type_id
                    );
                    let seller = this.sellers.find(
                        (element) => element.id == alt.seller_id
                    );
                    if (seller !== undefined) {
                        this.form.seller_id = seller.id;
                    }

                    this.setCustomerAccumulatedPoints(
                        alt.id,
                        this.configuration.enabled_point_system
                    );
                }
                this.changeCustomer();
            }
        },
        changeDocumentType() {
            this.validateDateOfIssue();
            this.filterSeries();
            this.cleanCustomer();
            this.filterCustomers();
            this.setDefaultSerieByDocument();
            if (this.configuration.college) {
                this.resetPlanSuscription();
            }
        },
        cleanCustomer() {
            this.form.customer_id = null;
        },
        dateValidError() {
            this.$message.error(
                `No puede seleccionar una fecha menor a ${this.configuration.shipping_time_days} día(s).`
            );
            this.dateValid = false;
        },
        validateDateOfIssue() {
            let minDate = moment().subtract(
                this.configuration.shipping_time_days,
                "days"
            );

            if (
                moment(this.form.date_of_issue) < minDate &&
                this.form.document_type_id === "01"
            ) {
                this.dateValidError();
            } else if (
                moment(this.form.date_of_issue) < minDate &&
                this.configuration.restrict_receipt_date
            ) {
                this.dateValidError();
            } else {
                this.dateValid = true;
            }
        },
        async changeDateOfIssue() {
            this.validateDateOfIssue();

            this.form.date_of_due = this.form.date_of_issue;
            let currency = this.currency_types.find(
                (element) => element.id == this.form.currency_type_id
            );
            try {
                await this.searchExchangeRateByDate(
                    this.form.date_of_issue
                ).then((response) => {
                    this.form.exchange_rate_sale = response;
                });
            } catch (e) {
                this.form.exchange_rate_sale = 1;
            }
            await this.getPercentageIgv();
            if (this.form.items) {
                this.changeCurrencyType();
            }
        },
        assignmentDateOfPayment() {
            this.form.payments.forEach((payment) => {
                payment.date_of_payment = this.form.date_of_issue;
            });
        },
        filterSeries() {
            this.form.series_id = null;
            let series = [];
            if (this.configuration.seller_establishments_all) {
                series = _.filter(this.all_series, {
                    document_type_id: this.form.document_type_id,
                    contingency: this.is_contingency,
                });
            } else {
                series = _.filter(this.all_series, {
                    establishment_id: this.form.establishment_id,
                    document_type_id: this.form.document_type_id,
                    contingency: this.is_contingency,
                });
            }
            if (
                this.form.document_type_id === this.configuration.user.document_id &&
                this.typeUser == "seller"
            ) {
                if (this.configuration.seller_establishments_all) {
                    series = _.filter(this.all_series, {
                        document_type_id: this.form.document_type_id,
                        contingency: this.is_contingency,
                    });
                } else {
                    series = _.filter(this.all_series, {
                        establishment_id: this.form.establishment_id,
                        document_type_id: this.form.document_type_id,
                        contingency: this.is_contingency,
                        id: this.configuration.user.serie,
                    });
                }
            }

            this.$store.commit("setSeries", series); 
            this.form.series_id =
                this.series.length > 0 ? this.series[0].id : null;
                if(this.series.length == 0 && this.form.series_id == null && series.length > 0){
                    this.form.series_id = series[0].id;
                }
            if (this.configuration.seller_establishments_all) {
                let serie = _.find(series, {
                    establishment_id: this.form.establishment_id,
                });
                if (serie) {
                    this.form.series_id = serie.id;
                }
            }

            if (this.configuration.multi_companies) {
                let [serie] = series;
                if (serie && serie.next_number) {
                    this.form.number = serie.next_number;
                }
            }
            if(this.user_default_document_types.length > 0){
                const user_default_document_type = this.user_default_document_types.find(item => item.document_type_id === this.form.document_type_id);
                if(user_default_document_type){
                    this.form.series_id = user_default_document_type.series_id;
                }
            }
        },
        debugFilterSeries() {
            // Filtrar series manualmente para debug
            let filtered_series = [];
            if (this.configuration.seller_establishments_all) {
                filtered_series = _.filter(this.all_series, {
                    document_type_id: this.form.document_type_id,
                    contingency: this.is_contingency,
                });
            } else {
                filtered_series = _.filter(this.all_series, {
                    establishment_id: this.form.establishment_id,
                    document_type_id: this.form.document_type_id,
                    contingency: this.is_contingency,
                });
            }
            
            // Debug para vendedores
            let seller_series = null;
            if (this.form.document_type_id === this.configuration.user.document_id && this.typeUser == "seller") {
                seller_series = [];
                if (this.configuration.seller_establishments_all) {
                    seller_series = _.filter(this.all_series, {
                        document_type_id: this.form.document_type_id,
                        contingency: this.is_contingency,
                    });
                } else {
                    seller_series = _.filter(this.all_series, {
                        establishment_id: this.form.establishment_id,
                        document_type_id: this.form.document_type_id,
                        contingency: this.is_contingency,
                        id: this.configuration.user.serie,
                    });
                }
            }
            
            // Crear objeto con todos los datos de debug
            const debugData = {
                form_data: {
                    establishment_id: this.form.establishment_id,
                    document_type_id: this.form.document_type_id,
                    series_id: this.form.series_id
                },
                states: {
                    is_contingency: this.is_contingency,
                    typeUser: this.typeUser
                },
                user_config: {
                    document_id: this.configuration.user.document_id,
                    serie: this.configuration.user.serie
                },
                configurations: {
                    seller_establishments_all: this.configuration.seller_establishments_all,
                    multi_companies: this.configuration.multi_companies
                },
                series_data: {
                    all_series: this.all_series,
                    series_filtered: this.series,
                    manual_filtered_series: filtered_series,
                    seller_series: seller_series
                },
                timestamp: new Date().toISOString()
            };
            
            return debugData;
        },
        filterCustomers() {
            if (
                ["0101", "1001", "1004"].includes(this.form.operation_type_id)
            ) {
                if (this.form.document_type_id === "01") {
                    if (!_.isNull(this.form.customer_id)) {
                        const cus = _.find(this.all_customers, {
                            id: this.form.customer_id,
                        });
                        if (cus && cus.identity_document_type_id !== "6") {
                            this.form.customer_id = null;
                        }
                    }

                    this.customers = _.filter(this.all_customers, {
                        identity_document_type_id: "6",
                    });
                } else {
                    if (this.document_type_03_filter) {
                        this.customers = _.filter(this.all_customers, (c) => {
                            return c.identity_document_type_id !== "6";
                        });
                    } else {
                        this.customers = this.all_customers;
                    }
                }
            } else {
                this.customers = this.all_customers;
            }
        },
        clickAddInitGuides() {
            this.form.guides.push(
                {
                    document_type_id: "09",
                    number: null,
                },
                {
                    document_type_id: "31",
                    number: null,
                }
            );
        },
        clickAddGuide() {
            if (!Array.isArray(this.form.guides)) {
                this.form.guides = Object.values(this.form.guides);
            }
            this.form.guides.push({
                document_type_id: null,
                number: null,
            });
        },
        clickRemoveGuide(index) {
            this.form.guides.splice(index, 1);
        },
        addRow(row) {
            row.unit_discount = 0;
            row.item.original_quantity = row.quantity;
            row.edit_description = false;
            row.item.original_description = row.item.description;
            row.item.origin_purchase_unit_price = row.item.purchase_unit_price;
            row.item.origin_stock = row.item.stock;
            if (this.configuration.edit_info_documents) {
                let { unit_value, unit_price } = row;
                row.unit_value_edit = _.round(unit_value, 4);
                row.unit_price_edit = _.round(unit_price, 4);
            }
            if (this.form.seller_id) {
                row.seller_id = this.form.seller_id;
            }
            let total_discount = 0;
            if (row.discounts && row.discounts.length != 0) {
                row.discounts.forEach((discount) => {
                    total_discount += discount.amount;
                });
            }
            row.total_discount = total_discount;
            row.aux_total_discount = 0;
            let index = null;

            if (this.recordItem) {
                this.form.items[this.recordItem.indexi] = row;
                index = this.recordItem.indexi;
                this.recordItem = null;

                if (this.configuration.enabled_point_system) {
                    this.setTotalExchangePoints();
                    this.recalculateUsedPointsForExchange(row);
                }
            } else {
                this.form.items.push(JSON.parse(JSON.stringify(row)));
                index = this.form.items.length - 1;
            }

            this.calculateTotal();

            if (this.configuration.type_discount) {
                this.checkAndSetDiscounts(index);
            }
        },
        async checkAndSetDiscounts(index) {
            let item = this.form.items[index];
            let total = item.total;
            let discountAmount = 0;
            let discountFullDescription = "";
            if (this.discounts_all.length > 0) {
                let discount_all = this.discounts_all[0];
                if (discount_all) {
                    discountFullDescription = `${discount_all.description} - ${discount_all.discount_value}%`;
                    discountAmount =
                        total * (discount_all.discount_value / 100);
                }
            }
            if (this.discounts_categories.length > 0) {
                let category_id = item.item.category_id;
                let discount_category = this.discounts_categories.find(
                    (discount) =>
                        discount.items.some(
                            (item) => item.category_id === category_id
                        )
                );
                if (discount_category) {
                    discountFullDescription = `${discount_category.description} - ${discount_category.discount_value}%`;
                    discountAmount =
                        total * (discount_category.discount_value / 100);
                }
            }
            if (this.discounts_brands.length > 0) {
                let brand_id = item.item.brand_id;

                let discount_brand = this.discounts_brands.find((discount) =>
                    discount.items.some((item) => item.brand_id === brand_id)
                );
                if (discount_brand) {
                    discountFullDescription = `${discount_brand.description} - ${discount_brand.discount_value}%`;
                    discountAmount =
                        total * (discount_brand.discount_value / 100);
                }
            }
            if (this.discounts_specific.length > 0) {
                let first_discount_specific = this.discounts_specific[0];
                let exists = await this.checkDiscountType(
                    index,
                    first_discount_specific.id
                );
                if (exists) {
                    discountAmount = exists.discountAmount;
                    discountFullDescription = exists.discountFullDescription;
                }
            }
            if (discountAmount > 0) {
                this.form.items[index].aux_total_discount = discountAmount;
                this.form.items[index].type_discount = discountFullDescription;
                this.changeTotalDiscountItem(item, index);
            }
        },
        clickRemoveItem(index) {
            let item = this.form.items[index];
            if (item.random_key) {
                this.form.items = this.form.items.filter((it, index) => {
                    return item.random_key !== it.depend_key;
                });
            }
            this.form.items.splice(index, 1);
            this.calculateTotal();

            if (this.configuration.enabled_point_system) this.setTotalExchangePoints();
        },
        async changeCurrencyType(
            getExchangeRate = false,
            not_rounding = true,
            noIsQuotation = true
        ) {
            this.currency_type = _.find(this.currency_types, {
                id: this.form.currency_type_id,
            });
            if (getExchangeRate) {
                var response = await this.searchExchangeRateByDate(
                    this.form.date_of_issue
                );
                this.form.exchange_rate_sale = response;
            }
            let items = [];

            this.form.items.forEach((row) => {
                if (this.table == "quotations") {
                    let calculate_row = calculateRowItemQuotation(
                        row,
                        this.form.currency_type_id,
                        this.form.exchange_rate_sale,
                        this.percentage_igv,
                        this.documentId !== null
                    );

                    calculate_row.discounts_acc = row.discounts_acc || [];

                    items.push(calculate_row);
                } else {
                    let calculate_row = calculateRowItem(
                        row,
                        this.form.currency_type_id,
                        this.form.exchange_rate_sale,
                        this.percentage_igv,
                        this.documentId !== null
                    );

                    calculate_row.discounts_acc = row.discounts_acc || [];
                    items.push(calculate_row);
                }
            });
            this.form.items = items;
            this.calculateTotal(not_rounding);

            if (this.isEditingItems) {
                for (let i = 0; i < this.form.items.length; i++) {
                    this.form.items[i].unit_value_edit =
                        this.form.items[i].unit_value;
                    this.form.items[i].unit_price_edit =
                        this.form.items[i].unit_price;
                }
            }
        },
        counTotalQuantityItems() {
            let {
                show_total_quantity_document,
                show_total_quantity_document_presentation,
            } = this.config;
            if (!show_total_quantity_document) return;
            let total = 0;
            this.form.items.forEach((row) => {
                let {
                    item: { presentation },
                } = row;
                if (
                    presentation &&
                    presentation.quantity_unit &&
                    show_total_quantity_document_presentation
                ) {
                    total +=
                        parseFloat(presentation.quantity_unit) *
                        parseFloat(row.quantity);
                } else {
                    total += parseFloat(row.quantity);
                }
            });
            this.totalQuantity = total;
        },
        calculateTotal(not_rounding = true) {
            let total_discount = 0;
            let total_charge = 0;
            let total_exportation = 0;
            let total_original_discount = 0;
            let total_taxed = 0;
            let total_exonerated = 0;
            let total_unaffected = 0;
            let total_free = 0;
            let total_igv = 0;
            let total_value = 0;
            let total = 0;
            let total_plastic_bag_taxes = 0;
            let total_perception = 0;
            let percentage_perception = 2;
            let total_perception_base = 0;
            this.total_discount_no_base = 0;

            let total_igv_free = 0;
            let total_base_isc = 0;
            let total_isc = 0;

            // let total_free_igv = 0

            this.form.items.forEach((row) => {
                total_discount += parseFloat(row.total_discount);
                total_original_discount += parseFloat(row.original_discount);
                total_charge += parseFloat(row.total_charge);

                if (row.affectation_igv_type_id === "10") {
                    // total_taxed += parseFloat(row.total_value)
                    if (row.total_value_without_rounding) {
                        total_taxed += parseFloat(
                            row.total_value_without_rounding
                        );
                    } else {
                        total_taxed += parseFloat(row.total_value);
                    }
                }

                if (
                    row.affectation_igv_type_id === "20" // 20,Exonerado - Operación Onerosa
                    // || row.affectation_igv_type_id === '21' // 21,Exonerado – Transferencia Gratuita
                ) {
                    // total_exonerated += parseFloat(row.total_value)

                    total_exonerated += row.total_value_without_rounding
                        ? parseFloat(row.total_value_without_rounding)
                        : parseFloat(row.total_value);
                }

                if (
                    row.affectation_igv_type_id === "30" || // 30,Inafecto - Operación Onerosa
                    row.affectation_igv_type_id === "31" || // 31,Inafecto – Retiro por Bonificación
                    row.affectation_igv_type_id === "32" || // 32,Inafecto – Retiro
                    row.affectation_igv_type_id === "33" || // 33,Inafecto – Retiro por Muestras Médicas
                    row.affectation_igv_type_id === "34" || // 34,Inafecto - Retiro por Convenio Colectivo
                    row.affectation_igv_type_id === "35" || // 35,Inafecto – Retiro por premio
                    row.affectation_igv_type_id === "36" // 36,Inafecto - Retiro por publicidad
                    // || row.affectation_igv_type_id === '37'  // 37,Inafecto - Transferencia gratuita
                ) {
                    total_unaffected += parseFloat(row.total_value);
                }

                if (row.affectation_igv_type_id === "40") {
                    total_exportation += parseFloat(row.total_value);
                }

                if (
                    [
                        "10",
                        // '20', '21',
                        "20",
                        "30",
                        "31",
                        "32",
                        "33",
                        "34",
                        "35",
                        "36",
                        "40",
                    ].indexOf(row.affectation_igv_type_id) < 0
                ) {
                    total_free += parseFloat(row.total_value);
                }

                if (
                    [
                        "10",
                        "20",
                        "21",
                        "30",
                        "31",
                        "32",
                        "33",
                        "34",
                        "35",
                        "36",
                        "40",
                    ].indexOf(row.affectation_igv_type_id) > -1
                ) {
                    // total_igv += parseFloat(row.total_igv)
                    // total += parseFloat(row.total)
                    if (row.total_igv_without_rounding) {
                        total_igv += parseFloat(row.total_igv_without_rounding);
                    } else {
                        total_igv += parseFloat(row.total_igv);
                    }

                    // row.total_value_without_rounding = total_value
                    // row.total_base_igv_without_rounding = total_base_igv
                    // row.total_igv_without_rounding = total_igv
                    // row.total_taxes_without_rounding = total_taxes
                    // row.total_without_rounding = total

                    if (row.total_without_rounding && not_rounding) {
                        total += parseFloat(row.total_without_rounding);
                    } else {
                        total += parseFloat(row.total);
                    }
                }

                if (!["21", "37"].includes(row.affectation_igv_type_id)) {
                    // total_value += parseFloat(row.total_value)
                    if (row.total_value_without_rounding) {
                        total_value += parseFloat(
                            row.total_value_without_rounding
                        );
                    } else {
                        total_value += parseFloat(row.total_value);
                    }
                }

                total_plastic_bag_taxes += parseFloat(
                    row.total_plastic_bag_taxes
                );

                if (
                    ["11", "12", "13", "14", "15", "16"].includes(
                        row.affectation_igv_type_id
                    )
                ) {
                    let unit_value = row.total_value / row.quantity;
                    let total_value_partial = unit_value * row.quantity;
                    // row.total_taxes = row.total_value - total_value_partial
                    row.total_taxes =
                        row.total_value -
                        total_value_partial +
                        parseFloat(row.total_plastic_bag_taxes); //sumar icbper al total tributos

                    row.total_igv =
                        total_value_partial * (row.percentage_igv / 100);
                    row.total_base_igv = total_value_partial;
                    total_value -= row.total_value;

                    total_igv_free += row.total_igv;
                    total += parseFloat(row.total); //se agrega suma al total para considerar el icbper
                }

                if (row.item.has_perception) {
                    let total_item =
                        parseFloat(row.unit_price) * parseFloat(row.quantity);
                    total_perception +=
                        total_item *
                        (parseFloat(row.item.percentage_perception) / 100);
                    total_perception_base += total_item;

                    percentage_perception = parseFloat(
                        row.item.percentage_perception
                    );
                }

                //sum discount no base
                this.total_discount_no_base +=
                    this.sumDiscountsNoBaseByItem(row);
                // isc
                total_isc += parseFloat(row.total_isc);
                total_base_isc += parseFloat(row.total_base_isc);
            });

            // isc
            this.form.total_base_isc = _.round(total_base_isc, 2);
            this.form.total_isc = _.round(total_isc, 2);

            this.form.total_igv_free = _.round(total_igv_free, 2);
            this.form.total_discount = _.round(total_discount, 2);
            this.form.total_original_discount = _.round(
                total_original_discount,
                2
            );
            this.form.total_exportation = _.round(total_exportation, 2);
            this.form.total_taxed = _.round(total_taxed, 2);
            this.form.total_exonerated = _.round(total_exonerated, 2);
            this.form.total_unaffected = _.round(total_unaffected, 2);
            this.form.total_free = _.round(total_free, 2);
            // this.form.total_igv = _.round(total_igv + total_free_igv, 2)
            this.form.total_igv = _.round(total_igv, 2);
            this.form.total_value = _.round(total_value, 2);
            // this.form.total_taxes = _.round(total_igv, 2)

            //impuestos (isc + igv + icbper)
            this.form.total_taxes = _.round(
                total_igv + total_isc + total_plastic_bag_taxes,
                2
            );

            this.form.total_plastic_bag_taxes = _.round(
                total_plastic_bag_taxes,
                2
            );
            this.form.subtotal = _.round(total, 2);
            this.form.total = _.round(total - this.total_discount_no_base, 2);
            // this.form.subtotal = _.round(total + this.form.total_plastic_bag_taxes, 2)
            // this.form.total = _.round(total + this.form.total_plastic_bag_taxes - this.total_discount_no_base, 2)

            if (this.enabled_discount_global) this.discountGlobal();
            if (this.prepayment_deduction) this.discountGlobalPrepayment();

            if (["1001", "1004"].includes(this.form.operation_type_id))
                this.changeDetractionType();

            if (this.form.has_retention) {
                this.changeRetention();
            }

            this.setTotalDefaultPayment();
            this.setPendingAmount();

            this.calculateFee();

            this.chargeGlobal();

            this.setTotalPointsBySale(this.config);
            this.counTotalQuantityItems();
            this.form.token_validated_for_discount = false;
            if (total_perception > 0) {
                this.form.perception = {
                    code: "51",
                    percentage: percentage_perception / 100,
                    amount: total_perception,
                    base: total_perception_base,
                };
            } else {
                this.form.perception = {};
            }
        },
        sumDiscountsNoBaseByItem(row) {
            let sum_discount_no_base = 0;

            if (row.discounts) {
                // if(row.discounts.length > 0){
                sum_discount_no_base = _.sumBy(
                    row.discounts,
                    function (discount) {
                        return discount.discount_type_id == "01"
                            ? discount.amount
                            : 0;
                    }
                );
                // }
            }

            return sum_discount_no_base;
        },
        setTotalDefaultPayment() {
            // if (this.form.payments.length > 0) {

            //     this.form.payments[0].payment = this.form.total
            // }
            this.calculatePayments();
        },
        chargeGlobal() {
            let base = parseFloat(this.form.total);

            if (this.configuration.active_allowance_charge) {
                let percentage_allowance_charge = parseFloat(
                    this.configuration.percentage_allowance_charge
                );
                this.total_global_charge = _.round(
                    base * (percentage_allowance_charge / 100),
                    2
                );
            }

            if (this.total_global_charge == 0) {
                this.deleteChargeGlobal();
                return;
            }
            let amount = 0;
            let factor = 0;
            if (this.is_amount_charge) {
                amount = parseFloat(this.total_global_charge);
                factor = _.round(amount / base, 5);
            } else {
                factor = parseFloat(this.total_global_charge) / 100;
                amount = factor * base;
            }
            // let base = this.form.total_taxed + amount

            let charge = _.find(this.form.charges, { charge_type_id: "50" });

            if (amount > 0 && !charge) {
                this.form.total_charge = _.round(amount, 2);
                this.form.total = _.round(
                    this.form.total + this.form.total_charge,
                    2
                );

                this.form.charges.push({
                    charge_type_id: "50",
                    description:
                        "Cargos globales que no afectan la base imponible del IGV/IVAP",
                    factor: factor,
                    amount: amount,
                    base: base,
                });
            } else {
                if (
                    this.form.charges &&
                    !Array.isArray(this.form.charges) &&
                    Object.values(this.form.charges)
                ) {
                    this.form.charges = Object.values(this.form.charges);
                }

                let pos = this.form.charges.indexOf(charge);

                if (pos > -1) {
                    this.form.total_charge = _.round(amount, 2);
                    this.form.total = _.round(
                        this.form.total + this.form.total_charge,
                        2
                    );

                    this.form.charges[pos].base = base;
                    this.form.charges[pos].amount = amount;
                    this.form.charges[pos].factor = factor;
                }
            }
        },
        deleteChargeGlobal() {
            if (
                this.form.charges &&
                !Array.isArray(this.form.charges) &&
                Object.values(this.form.charges)
            ) {
                this.form.charges = Object.values(this.form.charges);
            }

            let charge = _.find(this.form.charges, { charge_type_id: "50" });
            let index = this.form.charges.indexOf(charge);

            if (index > -1) {
                this.form.charges.splice(index, 1);
                this.form.total_charge = 0;
            }
        },
        changeTypeDiscount() {
            this.calculateTotal();
        },
        changeTotalGlobalDiscount() {
            this.total_global_discount = this.total_global_discount_aux;
            this.splitBase();
            this.calculateTotal();
        },
        deleteDiscountGlobal() {
            let discount = _.find(this.form.discounts, {
                discount_type_id: this.configuration.global_discount_type_id,
            });
            // let discount = _.find(this.form.discounts, {'discount_type_id': '03'})
            let index = this.form.discounts.indexOf(discount);

            if (index > -1) {
                this.form.discounts.splice(index, 1);
                this.form.total_discount = 0;
            }
        },
        setConfigGlobalDiscountType() {
            this.global_discount_type = _.find(this.global_discount_types, {
                id: this.configuration.global_discount_type_id,
            });
        },
        setGlobalDiscount(factor, amount, base) {
            this.form.discounts.push({
                discount_type_id: this.global_discount_type.id,
                description: this.global_discount_type.description,
                factor: factor,
                amount: amount,
                base: base,
                is_amount: this.is_amount,
                is_split: this.split_base,
            });
        },
        getTotalAffected() {
            let { items } = this.form;
            let total_affected = 0;
            items.forEach((item) => {
                if (item.affectation_igv_type_id === "10") {
                    total_affected += item.total_value;
                }
            });
            return total_affected;
        },
        getTotalInaffected() {
            let { items } = this.form;
            let total_inaffected = 0;
            items.forEach((item) => {
                if (item.affectation_igv_type_id !== "10") {
                    total_inaffected += item.total_value;
                }
            });
            return total_inaffected;
        },
        //  discountGlobal() {
        //     this.deleteDiscountGlobal();

        //     //input donde se ingresa monto o porcentaje
        //     let input_global_discount = parseFloat(this.total_global_discount);

        //     if (input_global_discount > 0) {
        //         const percentage_igv = 18;
        //         let base = this.isGlobalDiscountBase
        //             ? parseFloat(this.form.total_taxed)
        //             : parseFloat(this.form.total);
        //         let amount = 0;
        //         let factor = 0;

        //         if (this.is_amount) {
        //             amount = input_global_discount;
        //             factor = _.round(amount / base, 5);
        //         } else {
        //             factor = _.round(input_global_discount / 100, 5);
        //             amount = factor * base;
        //         }

        //         this.form.total_discount = _.round(amount, 2);

        //         // descuentos que afectan la bi
        //         if (this.isGlobalDiscountBase) {
        //             this.form.total_taxed = _.round(
        //                 base - this.form.total_discount,
        //                 2
        //             );
        //             this.form.total_value = this.form.total_taxed;
        //             this.form.total_igv = _.round(
        //                 this.form.total_taxed * (percentage_igv / 100),
        //                 2
        //             );

        //             //impuestos (isc + igv + icbper)
        //             this.form.total_taxes = _.round(
        //                 this.form.total_igv +
        //                     this.form.total_isc +
        //                     this.form.total_plastic_bag_taxes,
        //                 2
        //             );
        //             this.form.total = _.round(
        //                 this.form.total_taxed + this.form.total_taxes,
        //                 2
        //             );
        //             this.form.subtotal = this.form.total;

        //             if (this.form.total <= 0.05)
        //                 this.$message.error(
        //                     "El total debe ser mayor a 0, verifique el tipo de descuento asignado (Configuración/Avanzado/Contable)"
        //                 );
        //         }
        //         // descuentos que no afectan la bi
        //         else {
        //             this.form.total = _.round(this.form.total - amount, 2);
        //         }

        //         this.setGlobalDiscount(factor, _.round(amount, 2), base);
        //     }
        // },

        discountGlobal() {
            this.deleteDiscountGlobal();
            //input donde se ingresa monto o porcentaje
            let total_affected = this.getTotalAffected();

            let total_inaffected = this.getTotalInaffected();

            let input_global_discount = parseFloat(this.total_global_discount);
            if (input_global_discount > 0) {
                const percentage_igv = 18;

                let base = this.isGlobalDiscountBase
                    ? parseFloat(total_affected + total_inaffected)
                    : parseFloat(this.form.total);

                let amount = 0;
                let factor = 0;

                if (this.is_amount) {
                    amount = input_global_discount;
                    factor = _.round(amount / base, 5);
                } else {
                    factor = _.round(input_global_discount / 100, 5);
                    amount = factor * base;
                }

                this.form.total_discount = _.round(amount, 2);

                // descuentos que afectan la bi
                if (this.isGlobalDiscountBase) {
                    this.form.total_taxed = _.round(
                        total_affected - this.form.total_discount,
                        2
                    );
                    if (this.form.total_taxed < 0) {
                        this.form.total_taxed = 0;
                    }

                    //0.7
                    // this.form.total_value = this.form.total_taxed;
                    this.form.total_value =
                        total_affected +
                        total_inaffected -
                        this.form.total_discount;
                    this.form.total_igv = 0;
                    this.form.total_igv = _.round(
                        this.form.total_taxed * (percentage_igv / 100),
                        2
                    );
                    //0.126

                    //impuestos (isc + igv + icbper)
                    this.form.total_taxes = _.round(
                        this.form.total_igv +
                            this.form.total_isc +
                            this.form.total_plastic_bag_taxes,
                        2
                    );
                    //   this.form.total = _.round(
                    //     this.form.total_taxed + this.form.total_taxes,
                    //     2
                    // );

                    this.form.total = _.round(
                        total_affected +
                            total_inaffected -
                            this.form.total_discount +
                            this.form.total_taxes,
                        2
                    );
                    this.form.subtotal = this.form.total;

                    if (this.form.total <= 0.05)
                        this.$message.error(
                            "El total debe ser mayor a 0, verifique el tipo de descuento asignado (Configuración/Avanzado/Contable)"
                        );
                }
                // descuentos que no afectan la bi
                else {
                    this.form.total = _.round(this.form.total - amount, 2);
                }

                this.setGlobalDiscount(factor, _.round(amount, 2), base);

                // Ajuste automático para evitar totales terminados en .x9
                this.autoAdjustCentIfNeeded();
            }
        },
        async deleteInitGuides() {
            await _.remove(this.form.guides, { number: null });
        },
        async asignPlateNumberToItems() {
            if (this.form.plate_number) {
                await this.form.items.forEach((item) => {
                    let at = _.find(item.attributes, {
                        attribute_type_id: "5010",
                    });

                    if (!at) {
                        item.attributes.push({
                            attribute_type_id: "5010",
                            description: "Numero de Placa",
                            value: this.form.plate_number,
                            start_date: null,
                            end_date: null,
                            duration: null,
                        });
                    } else {
                        if (this.isUpdate) {
                            at.value = this.form.plate_number;
                        }
                    }
                });
            }
        },
        async validateAffectationTypePrepayment() {
            let not_equal_affectation_type = 0;

            await this.form.items.forEach((item) => {
                if (
                    item.affectation_igv_type_id !=
                    this.form.affectation_type_prepayment
                ) {
                    not_equal_affectation_type++;
                }
            });

            return {
                success: not_equal_affectation_type > 0 ? false : true,
                message:
                    "Los items deben tener tipo de afectación igual al seleccionado en el anticipo",
            };
        },
        validatePaymentDestination() {
            let error_by_item = 0;
            this.form.payments.forEach((item) => {
                if (!["05", "08", "09"].includes(item.payment_method_type_id)) {
                    if (item.payment_destination_id == null) error_by_item++;
                }
            });

            return {
                error_by_item: error_by_item,
            };
        },

        async submitAsync() {
            const validate_restrict_seller_discount =
                await this.validateRestrictSellerDiscount();

            if (!validate_restrict_seller_discount.success) return;

            let payWithAdvance = this.payWithAdvance();
            if (payWithAdvance) {
                let enoughAdvance = this.enoughAdvance();
                if (!enoughAdvance) {
                    return this.$message.error(
                        "El monto del anticipo no es suficiente para realizar la venta"
                    );
                }
            }
            if (this.configuration.multi_companies && !this.form.company_id) {
                this.$message.error("Debe seleccionar una empresa");
                return false;
            }
            if (this.configuration.enabled_dispatch_ticket_pdf) {
                this.form.dispatch_ticket_pdf = true;
            }
            if (!this.hasCashOpen()) {
                this.$message.error("Debe abrir caja para realizar la venta");
                return false;
            }
            //Validando las series seleccionadas
            if (this.configuration.college) {
                if (
                    (!this.form.child_id && this.monthsSelected.length == 0) ||
                    (this.form.child_id && this.monthsSelected.length > 0)
                ) {
                    this.form.months = this.monthsSelected;
                } else {
                    this.$message.error("Debe seleccionar un alumno y un mes");
                    return false;
                }
            }
            let errorSeries = false;
            _.forEach(this.form.items, (row) => {
                if (row.item.series_enabled) {
                    errorSeries =
                        parseFloat(row.quantity) !== row.item.lots.length;
                    return false;
                }
            });
            if (errorSeries && !this.form.has_prepayment) {
                this.$message.error("No se han seleccionado todas las series");
                return false;
            }

            if (this.form.show_terms_condition) {
                this.form.terms_condition = this.configuration.terms_condition_sale;
            }
            if (this.form.has_prepayment || this.prepayment_deduction) {
                let error_prepayment =
                    await this.validateAffectationTypePrepayment();
                if (!error_prepayment.success)
                    return this.$message.error(error_prepayment.message);
            }

            if (this.is_receivable) {
                this.form.payments = [];
            } else {
                let validate = await this.validate_payments();
                if (
                    validate.acum_total > parseFloat(this.form.total) ||
                    validate.error_by_item > 0
                ) {
                    return this.$message.error(
                        "Los montos ingresados superan al monto a pagar o son incorrectosz"
                    );
                }

                let validate_payment_destination =
                    await this.validatePaymentDestination();

                if (validate_payment_destination.error_by_item > 0) {
                    return this.$message.error(
                        "El destino del pago es obligatorio"
                    );
                }
            }
            this.form.payments = this.form.payments.map((payment) => {
                if (payment.payment_destination_id == "cash") {
                    let payment_destination = _.find(
                        this.payment_destinations,
                        { id: payment.payment_destination_id }
                    );
                    payment.user_id = payment_destination.user_id;
                }
                return payment;
            });
            await this.deleteInitGuides();
            await this.asignPlateNumberToItems();

            let val_detraction = await this.validateDetraction();
            if (!val_detraction.success)
                return this.$message.error(val_detraction.message);

            if (!this.enabled_payments) {
                this.form.payments = [];
            }

            // validacion sistema por puntos
            if (this.configuration.enabled_point_system) {
                const validate_exchange_points = this.validateExchangePoints();
                if (!validate_exchange_points.success)
                    return this.$message.error(
                        validate_exchange_points.message
                    );
            }
            // validacion sistema por puntos

            if (this.isGeneratedFromExternal) {
                // validacion restriccion de productos
                const validate_restrict_sale_items_cpe =
                    this.fnValidateRestrictSaleItemsCpe(this.form);
                if (!validate_restrict_sale_items_cpe.success)
                    return this.$message.error(
                        validate_restrict_sale_items_cpe.message
                    );
            }
            //

            //         return;
            this.loading_submit = true;
            if (this.configuration.search_by_phone) {
                this.setSearchTelephone(this.lastInput);
            }
            let path = `/${this.resource}`;
            if (this.isUpdate) {
                path = `/${this.resource}/${this.form.id}/update`;
            } else {
                this.form.time_of_issue = moment().format("HH:mm:ss");
            }
            let temp = this.form.payment_condition_id;
            // Condicion de pago Credito con cuota pasa a credito
            if (this.form.payment_condition_id === "03")
                this.form.payment_condition_id = "02";
            if (this.cash_id) {
                this.form.cash_id = this.cash_id;
            }
            if (this.configuration.multi_companies) {
                let serie = _.find(this.series, { id: this.form.series_id });
                this.form.series = serie.number;
            } else {
                delete this.form.establishment;
                delete this.form.series;
            }
            if (this.payed) {
                this.form.payments = [];
            }

            try {
                const response = await this.$http.post(path, this.form);
                if (response.data.success) {
                    if (!this.isEmiting) {
                        this.$eventHub.$emit("reloadDataItems", null);
                    }
                    let company_id = this.form.company_id;
                    if (!this.isEmiting) {
                        this.resetForm();
                    }
                    if (this.configuration.multi_companies) {
                        this.form.company_id = company_id;
                        this.changeCompany();
                    }
                    this.documentNewId = response.data.data.id;
                    if (!this.isEmiting) {
                        this.showOptionsDialog(response);
                    }

                    this.form_cash_document.document_id = response.data.data.id;

                    // this.savePaymentMethod();
                    if (payWithAdvance) {
                        this.form_cash_document.advance_id = payWithAdvance;
                        await this.saveAdvanceDocument();
                    } else {
                        await this.saveCashDocument();
                    }

                    this.autoPrintDocument();
                } else {
                    this.$message.error(response.data.message);
                }
            } catch (error) {
                if (error.response && error.response.status === 422) {
                    this.errors = error.response.data;
                } else {
                    this.$message.error(error.response.data.message);
                }
                if (temp === "03") this.form.payment_condition_id = "03";
            } finally {
                this.loading_submit = false;
                if (!this.isEmiting) {
                    this.setDefaultDocumentType();
                }
            }
        },
        async submit() {
            // this.form.total_value = 0;
            let series = this.all_series.find(series => series.id == this.form.series_id);
        
            if(series){
                this.form.series = series.number;
            }
            if(this.form.total_value == 0) {
                // console.log("total_value es 0");
                this.calculateTotal();
            }
    
            if (
                this.warningCertificateDue &&
                this.warningCertificateDue.type === "danger"
            ) {
                return this.$message.error(this.warningCertificateDue.message);
            }
            if (!this.validPurchaseOrden()) {
                return;
            }
            if (
                this.form.quotation_id &&
                this.type_quotation &&
                this.configuration
                    .split_quotation_to_document_services_and_not_services
            ) {
                this.form.type_quotation = this.type_quotation;
            }
            this.updateNameForGroupItems();
            const validate_restrict_seller_discount =
                await this.validateRestrictSellerDiscount();
                if (!validate_restrict_seller_discount.success) return;

            let payWithAdvance = this.payWithAdvance();
            if (payWithAdvance) {
                let enoughAdvance = this.enoughAdvance();
                if (!enoughAdvance) {
                    return this.$message.error(
                        "El monto del anticipo no es suficiente para realizar la venta"
                    );
                }
            }
            if (this.configuration.multi_companies && !this.form.company_id) {
                this.$message.error("Debe seleccionar una empresa");
                return false;
            }
            if (this.configuration.enabled_dispatch_ticket_pdf) {
                this.form.dispatch_ticket_pdf = true;
            }
            if (!this.hasCashOpen()) {
                this.$message.error("Debe abrir caja para realizar la venta");
                return false;
            }
            //Validando las series seleccionadas
            if (this.configuration.college) {
                if (
                    (!this.form.child_id && this.monthsSelected.length == 0) ||
                    (this.form.child_id && this.monthsSelected.length > 0)
                ) {
                    this.form.months = this.monthsSelected;
                } else {
                    this.$message.error("Debe seleccionar un alumno y un mes");
                    return false;
                }
            }
            let errorSeries = false;
            _.forEach(this.form.items, (row) => {
                if (row.item.series_enabled) {
                    errorSeries =
                        parseFloat(row.quantity) !== row.item.lots.length;
                    return false;
                }
            });
            if (errorSeries && !this.form.has_prepayment) {
                this.$message.error("No se han seleccionado todas las series");
                return false;
            }

            if (this.form.show_terms_condition) {
                this.form.terms_condition = this.configuration.terms_condition_sale;
            }
            if (this.form.has_prepayment || this.prepayment_deduction) {
                let error_prepayment =
                    await this.validateAffectationTypePrepayment();
                if (!error_prepayment.success)
                    return this.$message.error(error_prepayment.message);
            }

            if (this.is_receivable) {
                this.form.payments = [];
            } else {
                let validate = await this.validate_payments();
                if (
                    // validate.acum_total > parseFloat(this.form.total) ||
                    validate.error_by_item > 0
                ) {
                    return this.$message.error(
                        "Los montos ingresados superan al monto a pagar o son incorrectosx"
                    );
                }

                let validate_payment_destination =
                    await this.validatePaymentDestination();

                if (validate_payment_destination.error_by_item > 0) {
                    return this.$message.error(
                        "El destino del pago es obligatorio"
                    );
                }
            }
            this.form.payments = this.form.payments.map((payment) => {
                if (payment.payment_destination_id == "cash") {
                    let payment_destination = _.find(
                        this.payment_destinations,
                        { id: payment.payment_destination_id }
                    );
                    payment.user_id = payment_destination.user_id;
                }
                return payment;
            });
            await this.deleteInitGuides();
            await this.asignPlateNumberToItems();

            let val_detraction = await this.validateDetraction();
            if (!val_detraction.success)
                return this.$message.error(val_detraction.message);

            if (!this.enabled_payments) {
                this.form.payments = [];
            }

            // validacion sistema por puntos
            if (this.configuration.enabled_point_system) {
                const validate_exchange_points = this.validateExchangePoints();
                if (!validate_exchange_points.success)
                    return this.$message.error(
                        validate_exchange_points.message
                    );
            }
            // validacion sistema por puntos

            if (this.isGeneratedFromExternal) {
                // validacion restriccion de productos
                const validate_restrict_sale_items_cpe =
                    this.fnValidateRestrictSaleItemsCpe(this.form);
                if (!validate_restrict_sale_items_cpe.success)
                    return this.$message.error(
                        validate_restrict_sale_items_cpe.message
                    );
            }
            //
            if (
                this.configuration.no_payments_less_than_total &&
                this.form.payment_condition_id == "01"
            ) {
                let paymentTotal = this.form.payments.reduce(
                    (acc, payment) => acc + parseFloat(payment.payment),
                    0
                );
                if (paymentTotal < this.form.total) {
                    this.loading_submit = false;
                    this.$message.error(
                        "El monto de los pagos no puede ser menor al monto total"
                    );
                    return;
                }
            }
            //         return;
            this.loading_submit = true;
            if (this.configuration.check_reference) {
                const validate_reference = await this.checkReference(
                    this.form.payments
                );
                if (!validate_reference.success) {
                    this.loading_submit = false;
                    this.$message.error(validate_reference.message);
                    return;
                }
            }
            if (this.configuration.search_by_phone) {
                this.setSearchTelephone(this.lastInput);
            }
            let path = `/${this.resource}`;
            if (this.isUpdate) {
                path = `/${this.resource}/${this.form.id}/update`;
            } else {
                this.form.time_of_issue = moment().format("HH:mm:ss");
            }
            let temp = this.form.payment_condition_id;
            // Condicion de pago Credito con cuota pasa a credito
            if (this.form.payment_condition_id === "03")
                this.form.payment_condition_id = "02";
            if (this.cash_id) {
                this.form.cash_id = this.cash_id;
            }
            if (this.configuration.multi_companies) {
                let serie = _.find(this.series, { id: this.form.series_id });
                this.form.series = serie.number;
            } else {
                delete this.form.establishment;
                // delete this.form.series;
            }
            if (this.payed) {
                this.form.payments = [];
            }

            // if(this.form.subtotal == this.form.total && this.form.total_charge !== 0) {
            //     this.calculateTotal();
            // }
            // form.plate_info.km
            if (this.form.plate_info && this.form.plate_info.km) {
                this.form.km = this.form.plate_info.km;
            }
            this.checkPerception();
            if (!this.checkPerceptionItems()) {
                return;
            }
            if(this.form.total_value == 0) {
                this.calculateTotal();
            }
            if(this.form.series_id == null && !this.configuration.multi_companies) {
                this.$message.error("Debe seleccionar una serie");
                return;
            }

            this.$http
                .post(path, this.form)
                .then((response) => {
                    try {
                        if (response.data.success) {
                            this.$message.success("Comprobante emitido correctamente");
                            this.$eventHub.$emit("reloadDataItems", null);
                            let company_id = this.form.company_id;
                            if (!this.isEmiting) {
                                this.resetForm();
                            }
                            if (this.configuration.multi_companies) {
                                this.form.company_id = company_id;
                                this.changeCompany();
                            }
                            this.documentNewId = response.data.data.id;
                            if (!this.isEmiting) {
                                this.showOptionsDialog(response);
                            }

                            this.form_cash_document.document_id =
                                response.data.data.id;

                            // this.savePaymentMethod();
                            if (payWithAdvance) {
                                this.form_cash_document.advance_id =
                                    payWithAdvance;
                                this.saveAdvanceDocument();
                            } else {
                                this.saveCashDocument();
                            }

                            this.autoPrintDocument();
                        } else {
                            this.$message.error(response.data.message);
                        }
                    } catch (e) {
                        // location.href = "/documents";
                    }
                })
                .catch((error) => {
                    if (error.response.status === 422) {
                        this.errors = error.response.data;
                    } else if (error.response.status === 504) {
                        // Error de timeout - mostrar modal específico
                        if (typeof window.showTimeoutErrorModal === 'function') {
                            window.showTimeoutErrorModal();
                        } else {
                            this.$message({
                                message: "Error de tiempo de espera. Verifique en documentos si se creó correctamente.",
                                type: "warning",
                                duration: 8000,
                            });
                        }
                    } else {
                        // this.$message.error(error.response.data.message, 5000);
                        this.$message({
                            message: error.response.data.message,
                            type: "error",
                            duration: 5000,
                        });
                    }
                    if (temp === "03") this.form.payment_condition_id = "03";
                })
                .finally(() => {
                    this.loading_submit = false;

                    this.setDefaultDocumentType();
                });
        },
        async showOptionsDialog(response) {
            if (this.hidePreviewPdf) {
                const response_data = response.data.data;

                if (response_data.response.sent) {
                    this.$message.success(response_data.response.description);
                } else {
                    this.$message.success(
                        `Comprobante registrado: ${response_data.number_full}`
                    );
                }
            } else {
                
                this.showDialogOptions = true;
            }
        },
        autoPrintDocument() {
            if (this.isAutoPrint) {
                this.$http
                    .get(`/printticket/document/${this.documentNewId}/ticket`)
                    .then((response) => {
                        this.printTicket(response.data);
                    })
                    .catch((error) => {});
            }
        },
        printTicket(html_pdf) {
            if (html_pdf.length > 0) {
                const config = getUpdatedConfig();
                const opts = getUpdatedConfig();

                const printData = [
                    {
                        type: "html",
                        format: "plain",
                        data: html_pdf,
                        options: opts,
                    },
                ];

                qz.print(config, printData)
                    .then(() => {
                        this.$notify({
                            title: "",
                            message: "Impresión en proceso...",
                            type: "success",
                        });
                    })
                    .catch(displayError);
            }
        },

        saveCashDocument() {
            this.$http
                .post(`/cash/cash_document`, this.form_cash_document)
                .then((response) => {
                    if (!response.data.success) {
                        this.$message.error(response.data.message);
                    }
                })
                .catch((error) => {
                    console.error(error);
                });
        },
        validate_payments() {
            //eliminando items de pagos
            for (let index = 0; index < this.form.payments.length; index++) {
                if (parseFloat(this.form.payments[index].payment) === 0)
                    this.form.payments.splice(index, 1);
            }

            let error_by_item = 0;
            let acum_total = 0;

            this.form.payments.forEach((item) => {
                acum_total += parseFloat(item.payment);
                if (item.payment <= 0 || item.payment == null) error_by_item++;
            });

            return {
                error_by_item: error_by_item,
                acum_total: acum_total,
            };
        },
        close() {
            if (this.table) {
                location.href = `/${this.table}`;
            } else {
                location.href = this.is_contingency
                    ? `/contingencies`
                    : `/${this.resource}`;
            }
        },
        async reloadDataCustomers(customer_id) {
            await this.$http
                .get(`/${this.resource}/search/customer/${customer_id}`)
                .then((response) => {
                    this.$set(this, "customers", response.data.customers);
                    this.$nextTick(() => {
                        this.$set(this.form, "customer_id", customer_id);
                    });

                    this.setCustomerAccumulatedPoints(
                        customer_id,
                        this.configuration.enabled_point_system
                    );
                });
        },
        async getUnpaidDocuments() {
            await this.$http
                .get(`/${this.resource}/unpaid/${this.form.customer_id}`)
                .then((response) => {
                    let data = response.data;
                    this.hasPendingCredits = data.hasUnpaid;
                    // this.unpaid_documents = response.data.data;
                })
                .catch((error) => {});
        },
        async changeCustomer() {
            this.getAdvance(this.form.customer_id);
            this.person_type_id = null;
            this.customer_addresses = [];
            this.form.customer_address_id = null;

            let customer = _.find(this.customers, {
                id: this.form.customer_id,
            });
            if (customer) {
                await this.getUnpaidDocuments();
                this.person_type_id = customer.person_type_id;
                this.customer_addresses = customer.addresses;
                if (customer.address) {
                    this.customer_addresses.unshift({
                        id: null,
                        address: customer.address,
                    });
                }

                this.setCustomerAccumulatedPoints(
                    customer.id,
                    this.configuration.enabled_point_system
                );

                let seller = this.sellers.find(
                    (element) => element.id == customer.seller_id
                );
                if (seller !== undefined) {
                    this.form.seller_id = seller.id;
                }

                // retencion para clientes con ruc
                this.validateCustomerRetention(
                    customer.identity_document_type_id
                );
                let { auto_retention } = customer;
                this.form.has_retention = auto_retention;
                this.changeRetention();
                this.new_customer_trade_name = customer.trade_name;
            }

            if (this.customer_addresses.length > 0) {
                this.form.customer_address_id = this.customer_addresses[0].id;
                this.new_customer_address = this.customer_addresses[0].address;
            }
            if (this.configuration.college) {
                this.children = [];
                this.form.child_id = null;
                await this.searchRemoteChildren("", true);
            }
        },
        validateCustomerRetention(identity_document_type_id) {
            if (identity_document_type_id != "6") {
                if (this.form.has_retention) {
                    this.form.has_retention = false;
                    this.changeRetention();
                }

                this.show_has_retention = false;
            } else {
                this.show_has_retention = true;
            }
        },
        initDataPaymentCondition01() {
            this.readonly_date_of_due = false;
            this.enabled_payments = true;
            this.form.date_of_due = this.form.date_of_issue;
            this.form.payment_method_type_id = null;
        },
        changePaymentCondition() {
            this.form.fee = [];
            this.form.payments = [];
            if (this.form.payment_condition_id === "01") {
                this.clickAddPayment();
                this.initDataPaymentCondition01();
            }
            if (this.form.payment_condition_id === "02") {
                this.clickAddFeeNew();
                // this.readonly_date_of_due = true;
            }
            if (this.form.payment_condition_id === "03") {
                this.clickAddFee();
            }

            // if(this.isCreditPaymentCondition){
            // this.changeRetention()
            // }
            this.changeItemPriceCondition();
            if (!_.isEmpty(this.form.retention)) {
                this.setTotalPendingAmountRetention(this.form.retention.amount);
            }
        },
        changeItemPriceCondition() {
            if (!this.configuration.condition_payment_items) return;
            let { payment_condition_id } = this.form;
            let changed = false;
            this.form.items.forEach((item, idx) => {
                let { payment_conditions } = item;
                if (
                    payment_conditions != undefined &&
                    payment_conditions.length > 0
                ) {
                    let condition = payment_conditions.find(
                        (p) => p.payment_condition_id === payment_condition_id
                    );
                    if (condition) {
                        let price = condition.price;
                        price = parseFloat(price);
                        if (price <= 0) return;
                        item.unit_price_edit = price;
                        this.changeUnitPriceItem(item, idx);
                        changed = true;
                    }
                }
            });
            if (changed) {
                this.$message.warning(
                    "Se ha cambiado el precio de algunos productos"
                );
            }
        },
        clickAddFee() {
            this.form.date_of_due = moment().format("YYYY-MM-DD");
            this.form.fee.push({
                id: null,
                date: moment().add(this.cuotaDays, "days").format("YYYY-MM-DD"),
                currency_type_id: this.form.currency_type_id,
                amount: 0,
                days: this.cuotaDays,
            });
            this.calculateFee();
        },
        clickAddFeeNew() {
            let first = {
                id: "05",
                number_days: 0,
            };
            if (this.payment_method_types_credit[0] !== undefined) {
                first = this.payment_method_types_credit[0];
            }

            // let date = moment()
            //     .add(first.number_days, 'days')
            //     .format('YYYY-MM-DD')

            let date = moment(this.form.date_of_issue)
                .add(first.number_days, "days")
                .format("YYYY-MM-DD");

            this.form.date_of_due = date;
            this.form.fee.push({
                id: null,
                document_id: null,
                payment_method_type_id: first.id,
                date: date,
                currency_type_id: this.form.currency_type_id,
                amount: 0,
            });
            this.calculateFee();
        },
        clickRemoveFee(index) {
            this.form.fee.splice(index, 1);
            this.calculateFee();
        },
        calculatePayments() {
            let payment_count = this.form.payments.length;
            // let total = this.form.total;
            let total = this.getTotal();
            
            // Buscar elementos NC que no sean el último y que ya tengan payment
            let ncElementsWithPayment = this.form.payments
                .map((row, index) => ({ ...row, originalIndex: index }))
                .filter((row, index) => 
                    row.isNoteCredit && 
                    index !== this.form.payments.length - 1 && 
                    row.payment && 
                    row.payment > 0
                );

            
            // Calcular el total ya asignado a elementos NC
            let totalNcPayments = ncElementsWithPayment.reduce((sum, row) => sum + parseFloat(row.payment || 0), 0);
            
            // Calcular el resto que se debe distribuir
            let remainingTotal = total - totalNcPayments;
            
            // Contar elementos que no son NC con payment fijo
            let elementsToDistribute = this.form.payments.length - ncElementsWithPayment.length;
            
            let payment = totalNcPayments; // Empezar contando los pagos NC ya fijos
            let amount = elementsToDistribute > 0 ? _.round(remainingTotal / elementsToDistribute, 2) : 0;
            
            _.forEach(this.form.payments, (row, index) => {
                // Si es un elemento NC que no es el último y ya tiene payment, no modificarlo
                let isFixedNcElement = row.isNoteCredit && 
                                     index !== this.form.payments.length - 1 && 
                                     row.payment && 
                                     row.payment > 0;
                
                if (!isFixedNcElement) {
                    payment += amount;
                    if (total - payment < 0) {
                        amount = _.round(total - payment + amount, 2);
                    }
                    row.payment = amount;
                }
            });
        },
        calculateFee() {
            this.showAll = this.form.fee.length < 15;
            let fee_count = this.form.fee.length;
            // let total = this.form.total;
            let total = this.getTotal();

            let accumulated = 0;
            let amount = _.round(total / fee_count, 2);
            _.forEach(this.form.fee, (row) => {
                accumulated += amount;
                if (total - accumulated < 0) {
                    amount = _.round(total - accumulated + amount, 2);
                }
                row.amount = amount;
            });
        },
        getTotal() {
            let total_pay = this.form.total;
            if (this.form.has_retention) {
                total_pay -= this.form.retention.amount;
            }

            if (
                !_.isEmpty(this.form.detraction) &&
                this.form.total_pending_payment > 0
            ) {
                return this.form.total_pending_payment;
            }

            if (
                !_.isEmpty(this.form.retention) &&
                this.form.total_pending_payment > 0
            ) {
                return this.form.total_pending_payment;
            }

            return total_pay;
        },
        setDescriptionOfItem(item) {
            return showNamePdfOfDescription(item, this.configuration.show_pdf_name);
        },
        checkKeyWithAlt(e) {
            let code = e.event.code;
            if (this.showDialogOptions === true && code === "KeyN") {
                this.showDialogOptions = false;
            }

            if (
                code === "KeyG" && // key G
                !this.showDialogAddItem && // Modal hidden
                this.form.items.length > 0 && // with items
                this.focus_on_client === false // not client search
            ) {
                this.submit();
            }
        },
        checkKey(e) {
            let code = e.event.code;
            if (code === "F2") {
                //abrir el modal de agergar producto
                if (!this.showDialogAddItem) this.showDialogAddItem = true;
            }
            if (code === "Escape") {
                if (this.showDialogAddItem) this.showDialogAddItem = false;
            }
        },
        async openDialogLots(item) {
            this.recordItem = item;
            if(!this.dialogItemSeriesIndex){
                this.loading = true;
                const module = await import("../Store/ItemSeriesIndex");
                this.dialogItemSeriesIndex = module.default;
                this.loading = false;
            }
            this.showDialogItemSeriesIndex = true;
        },
        successItemSeries(series) {
            let itemIndex = _.findIndex(this.form.items, {
                item_id: this.recordItem.item_id,
            });
            this.form.items[itemIndex].item.lots = series;
        },
        showItemSeries(series) {
            return series.map((o) => o["series"]).join(", ");
        },
        async clickAddItem(form) {
            let extra = form.item.extra;

            let affectation_igv_type_id = form.affectation_igv_type_id;
            let unit_price = form.unit_price_value;
            if (form.has_igv === false) {
                if (
                    affectation_igv_type_id === "20" ||
                    affectation_igv_type_id === "21" ||
                    affectation_igv_type_id === "40"
                ) {
                } else {
                    unit_price =
                        form.unit_price_value * (1 + this.percentage_igv);
                }
            }

            form.input_unit_price_value = form.unit_price_value;

            form.unit_price = unit_price;
            form.item.unit_price = unit_price;
            form.item.presentation = {};

            form.affectation_igv_type = _.find(this.affectation_igv_types, {
                id: affectation_igv_type_id,
            });

            let row = calculateRowItem(
                form,
                this.form.currency_type_id,
                this.form.exchange_rate_sale,
                this.percentage_igv
            );
            row.update_price = form.update_price;
            row.meter = form.item.meter;
            row.item.name_product_pdf = row.name_product_pdf || "";

            row.item.extra = extra;

            this.addRow(row);
        },
        async changeItem(item) {
            let form = {};
            form.item = item;

            form.unit_price_value = form.item.sale_unit_price;
            form.meter = form.item.meter;

            form.has_igv = form.item.has_igv;
            form.has_plastic_bag_taxes = form.item.has_plastic_bag_taxes;
            form.affectation_igv_type_id =
                form.item.sale_affectation_igv_type_id;
            let affectation_igv_type = _.find(this.affectation_igv_types, {
                id: form.item.sale_affectation_igv_type_id,
            });
            if (affectation_igv_type == undefined) {
                form.affectation_igv_type_id = this.affectation_igv_types[0].id;
            }

            form.quantity = form.item.quantity;

            //asignar variables isc
            form.has_isc = form.item.has_isc;
            form.percentage_isc = form.item.percentage_isc;
            form.system_isc_type_id = form.item.system_isc_type_id;

            if (this.hasAttributes(form)) {
                form.item.attributes.forEach((row) => {
                    if (form.attributes === undefined) form.attributes = [];
                    form.attributes.push({
                        attribute_type_id: row.attribute_type_id,
                        description: row.description,
                        value: row.value,
                        start_date: row.start_date,
                        end_date: row.end_date,
                        duration: row.duration,
                    });
                });
            }

            form.lots_group = form.item.lots_group;
            // this.setExtraElements(this.form.item);
            // if (
            //     form.item_unit_types &&
            //     form.item_unit_types.length != 0
            // ) {
            //     this.changePrice(this.form.item_unit_type_id);
            // } else {
            //     await this.getLastPriceItem();
            // }
            if (
                form.item.name_product_pdf &&
                this.configuration.item_name_pdf_description
            ) {
                form.name_product_pdf = form.item.name_product_pdf;
            }

            // return form;
            this.clickAddItem(form);
        },
        hasAttributes(form) {
            if (
                form.item !== undefined &&
                form.item.attributes !== undefined &&
                form.item.attributes !== null &&
                form.item.attributes.length > 0
            ) {
                return true;
            }

            return false;
        },
        parkInvoice(reference) {
            const invoices = this.getParkedInvoices();
            const timestamp = moment().format("YYYY-MM-DD HH:mm:ss");
            const invoiceReference = `doc_${reference || timestamp}`;
            invoices[invoiceReference] = this.form;
            localStorage.setItem("parked_invoices", JSON.stringify(invoices));
            this.$message.success(
                `Factura aparcada con referencia: ${invoiceReference}`
            );
            this.resetForm();
        },
        getParkedInvoices() {
            const invoices = localStorage.getItem("parked_invoices");
            return invoices ? JSON.parse(invoices) : {};
        },
        restoreParkedInvoice(reference) {
            const invoices = this.getParkedInvoices();
            if (invoices[reference]) {
                this.form = invoices[reference];
                delete invoices[reference];
                localStorage.setItem(
                    "parked_invoices",
                    JSON.stringify(invoices)
                );
                this.$message.success(`Factura restaurada: ${reference}`);
            } else {
                this.$message.error(
                    `No se encontró la factura con referencia: ${reference}`
                );
            }
        },

        deleteParkedInvoice(reference) {
            const invoices = this.getParkedInvoices();
            if (invoices[reference]) {
                delete invoices[reference];
                localStorage.setItem(
                    "parked_invoices",
                    JSON.stringify(invoices)
                );
                this.$message.success(`Factura eliminada: ${reference}`);
            } else {
                this.$message.error(
                    `No se encontró la factura con referencia: ${reference}`
                );
            }
        },
        async openParkedInvoicesDialog() {
            if(!this.parkedInvoicesDialog){
                this.loading = true;
                const module = await import("./partials/InvoiceParkedSalesDialog.vue");
                this.parkedInvoicesDialog = module.default;
                this.loading = false;
            }
            this.showParkedInvoicesDialog = true;
        },
        async openParkInvoiceDialog() {
            if (this.form.items.length === 0) {
                return this.$message.warning("No hay items para aparcar");
            }
            // import("./partials/InvoiceParkSaleDialog.vue");
            if(!this.dialogParkInvoice){
                this.loading = true;
                const module = await import("./partials/InvoiceParkSaleDialog.vue");
                this.dialogParkInvoice = module.default;
                this.loading = false;
            }
            this.showParkInvoiceDialog = true;
        },
        async searchRemotePlateNumbers(query) {
            if (this.searchTimeout) clearTimeout(this.searchTimeout);

            this.searchTimeout = setTimeout(async () => {
                if (!query) {
                    this.plateNumberOptions = [];
                    return;
                }

                try {
                    const response = await this.$http.get(
                        `/plate-numbers/search`,
                        {
                            params: {
                                input: query,
                                limit: 10,
                            },
                        }
                    );

                    this.plateNumberOptions = response.data.data;
                } catch (error) {
                    console.error("Error searching plate numbers:", error);
                    this.$message.error("Error al buscar placas");
                }
            }, 300);
        },

        async changePlateNumber(plateNumberId) {
            if (!plateNumberId) {
                this.resetPlateInfo();
                return;
            }

            try {
                const response = await this.$http.get(
                    `/plate-numbers/details/${plateNumberId}`
                );
                const plateData = response.data.data;

                this.form.plate_info = {
                    brand: plateData.brand?.description || "",
                    model: plateData.model?.description || "",
                    color: plateData.color?.description || "",
                    year: plateData.year || "",
                    km: plateData.kms?.length
                        ? plateData.kms[0].description
                        : "0",
                };
            } catch (error) {
                console.error("Error loading plate details:", error);
                this.$message.error("Error al cargar detalles de la placa");
            }
        },

        onPlateNumberSaved(plateNumber) {
            this.searchRemotePlateNumbers(plateNumber.description).then(() => {
                this.form.plate_number_id = plateNumber.id;
                this.changePlateNumber(plateNumber.id);
            });
        },

        resetPlateInfo() {
            this.form.plate_number_id = null;
            this.form.plate_info = {
                brand: "",
                model: "",
                color: "",
                year: "",
                km: "",
            };
        },
    },
    mounted() {
        this.fetchCoupons();
        setTimeout(() => {
            let el = document.querySelector(
                ".el-select-dropdown.el-popper.el-select-favorites-items .el-scrollbar .el-select-dropdown__wrap.el-scrollbar__wrap"
            );
            if (el) {
                el.style.maxHeight = "450px";
            }
        }, 6000);
    },
};
