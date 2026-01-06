
import axios from "axios";
import LotsForm from "../../../../../../resources/js/views/tenant/items/partials/lots.vue"; 
import InputLotsWithoutStock from "../../../../../../resources/js/views/tenant/items/partials/lots_without_stock.vue";
import SizesForm from "../../../../../../resources/js/views/tenant/items/partials/sizes.vue";
import ExtraInfo from "../../../../../../resources/js/views/tenant/items/partials/extra_info";
import { mapActions, mapState } from "vuex";
import { functions } from "@mixins/functions";
import {
    ItemOptionDescription,
    ItemSlotTooltip,
} from "../../../../../../resources/js/views/tenant/items/../../../helpers/modal_item";
export default {
    mixins: [functions],
    props: [
        "configuration",
        "showDialog",
        "recordId",
        "external",
        "type",
        "pharmacy",
        "isReadonly",
        "isFoodDealer",
    ],
    components: {
        LotsForm,
        ExtraInfo,
        SizesForm,
        InputLotsWithoutStock,
    },
    computed: {
        ecommerceActive() {
            return this.activeName == "ecommerce";
        },
        ...mapState([
            "colors",
            "CatItemSize",
            "CatItemUnitsPerPackage",
            "CatItemMoldProperty",
            "CatItemUnitBusiness",
            "CatItemStatus",
            "CatItemPackageMeasurement",
            "CatItemMoldCavity",
            "CatItemProductFamily",
            "config",
        ]),
        isService: function () {
            // Tener en cuenta que solo oculta las pestañas para tipo servicio.
            if (this.form !== undefined) {
                // Es servicio por selección
                if (
                    this.form.unit_type_id !== undefined &&
                    this.form.unit_type_id === "ZZ"
                ) {
                    if (
                        this.activeName == "second" ||
                        this.activeName == "third" ||
                        this.activeName == "five"
                    ) {
                        this.activeName = "first";
                    }
                    return true;
                }
            }
            return false;
        },
        canSeeProduction: function () {
            if (this.config && this.config.production_app)
                return this.config.production_app;
            return false;
        },
        requireSupply: function () {
            if (this.form.is_for_production) {
                if (this.form.is_for_production == true) return true;
            }
            return false;
        },

        canShowExtraData: function () {
            if (
                this.config &&
                this.config.show_extra_info_to_item !== undefined
            ) {
                return this.config.show_extra_info_to_item;
            }
            return false;
        },
        showPharmaElement() {
            if (this.fromPharmacy === true) return true;
            if (this.config.is_pharmacy === true) return true;
            return false;
        },
        showPointSystem() {
            if (this.config) return this.config.enabled_point_system;

            return false;
        },
        showRestrictSaleItemsCpe() {
            if (this.config) return this.config.restrict_sale_items_cpe;

            return false;
        },
    },

    data() {
        return {
            loading: false,
            labelOptions: [],
            item_suplly_id: null,
            item_bonus_id: null,
            item_complements: false,
            payment_conditions: [
                {
                    payment_condition_id: "01",
                    name: "Contado",
                    price: 0,
                },
                {
                    payment_condition_id: "02",
                    name: "Crédito",
                    price: 0,
                },
            ],
            showDialogLotsWithoutStock: false,
            customerTypes: [],
            imageStyle: {
                objectFit: "cover",
                width: "200px",
                height: "200px",
            },
            selectedImage: null,
            selectedImageBrand: null,
            loading_digemid: false,
            loading_search: false,
            showDialogLots: false,
            showDialogSizes: false,
            form_category: {
                image: null,
                file: null,
                add: false,
                name: null,
                id: null,
            },
            form_brand: {
                image: null,
                file: null,
                add: false,
                name: null,
                id: null,
            },
            warehouses: [],
            items: [],
            loading_submit: false,
            showPercentagePerception: false,
            has_percentage_perception: false,
            percentage_perception: null,
            enabled_percentage_of_profit: false,
            titleDialog: null,
            resource: "items",
            errors: {
                id_cupones: null,
            },
            item_suplly: {},
            item_bonus: {},
            headers: headers_token,
            form: {
                is_input: true,
                item_supplies: [],
                is_for_production: false,
                has_bonus_item: false,
                id_cupones: null,
                bonus_items: [],
            },
            // configuration: {},
            unit_types: [],
            currency_types: [],
            coupons: [],
            system_isc_types: [],
            affectation_igv_types: [],
            categories: [],
            brands: [],

            accounts: [],
            show_has_igv: true,
            purchase_show_has_igv: true,
            have_account: false,
            item_unit_type: {
                id: null,
                unit_type_id: null,
                quantity_unit: 0,
                price1: 0,
                price2: 0,
                price3: 0,
                price_default: 2,
            },
            attribute_types: [],
            activeName: "first",
            fromPharmacy: false,
            inventory_configuration: null,
            digemidCodes: [],
            isClothesShoes: false,
            isMajolica: false,
            previousTab: null, // Para guardar la pestaña anterior
            suppliers: [],
        };
    },
    async created() {
        this.loadConfiguration();
        if (this.pharmacy !== undefined && this.pharmacy == true) {
            this.fromPharmacy = true;
        }
        await this.initForm();

        await this.$http.get(`/${this.resource}/tables`).then((response) => {
            let data = response.data;
            this.isClothesShoes = data.clothesShoes;
            this.digemidCodes = data.digemid_codes;
            this.unit_types = data.unit_types;
            this.accounts = data.accounts;
            this.currency_types = data.currency_types;
            this.system_isc_types = data.system_isc_types;
            this.affectation_igv_types = data.affectation_igv_types;
            this.warehouses = data.warehouses;
            this.customerTypes = data.customer_types;
            this.categories = data.categories;
            this.id_cupones = data.coupons;
            this.brands = data.brands;
            this.attribute_types = data.attribute_types;
            this.isMajolica = data.is_majolica;
            // this.config = data.configuration
            if (this.canShowExtraData) {
                this.$store.commit("setColors", data.colors);
                this.$store.commit("setCatItemSize", data.CatItemSize);
                this.$store.commit(
                    "setCatItemUnitsPerPackage",
                    data.CatItemUnitsPerPackage
                );
                this.$store.commit("setCatItemStatus", data.CatItemStatus);
                this.$store.commit(
                    "setCatItemMoldCavity",
                    data.CatItemMoldCavity
                );
                this.$store.commit(
                    "setCatItemMoldProperty",
                    data.CatItemMoldProperty
                );
                this.$store.commit(
                    "setCatItemUnitBusiness",
                    data.CatItemUnitBusiness
                );
                this.$store.commit(
                    "setCatItemPackageMeasurement",
                    data.CatItemPackageMeasurement
                );
                this.$store.commit(
                    "setCatItemProductFamily",
                    data.CatItemPackageMeasurement
                );
            }
            this.$store.commit("setConfiguration", data.configuration);

            this.loadConfiguration();
            this.form.sale_affectation_igv_type_id =
                this.affectation_igv_types.length > 0
                    ? this.affectation_igv_types[0].id
                    : null;
            this.form.purchase_affectation_igv_type_id =
                this.affectation_igv_types.length > 0
                    ? this.affectation_igv_types[0].id
                    : null;
            this.inventory_configuration = data.inventory_configuration;
        });

        this.$eventHub.$on("submitPercentagePerception", (data) => {
            this.form.percentage_perception = data;
            if (!this.form.percentage_perception)
                this.has_percentage_perception = false;
        });

        this.$eventHub.$on("reloadTables", () => {
            this.reloadTables();
        });

        await this.setDefaultConfiguration();
        await this.loadLabelOptions();
        if (this.isReadonly) {
            await this.create();
        }
    },
    mounted() {
        // Cargar los datos de los cupones cuando el componente se monta
        this.fetchCoupons();
        this.getSuppliers();
    },
    methods: {
        changeSupplier(){},
        searchRemoteSuppliers(input) {
            if (input.length > 0) {
                this.loading = true;
                let parameters = `input=${input}&document_type_id=&operation_type_id=`;

                this.$http
                    .get(`/documents/search/suppliers?${parameters}`)
                    .then((response) => {
                        this.$set(this, 'suppliers', response.data.suppliers)
                    })
                    .catch((error) => this.axiosError(error))
                    .finally(() => (this.loading = false));
            }
        },
        getSuppliers() {
            this.$http.get('/persons/suppliers/records').then((response) => {
                this.suppliers = response.data.data;
            });
        },
        submitForm(){
            this.activeName = "first";
            this.changeTab();
            this.submit();
        },
        changeTab() {
            
        
        },
        reloadDataItems(id) {
            this.$eventHub.$emit("reloadDataItems", id);
        },
        reloadData() {
            this.$eventHub.$emit("reloadData");
        },
        calculateTotalBonusItem(row) {
            console.log(row);
        },
        clickClone(index) {
            let item_unit_type = JSON.parse(
                JSON.stringify(this.form.item_unit_types[index])
            );
            item_unit_type.id = null;
            item_unit_type.factor_default = false;
            item_unit_type.default_price_store = false;
            item_unit_type.warehouse_id = null;
            this.form.item_unit_types.push(item_unit_type);
        },
        clickDeleteSupply(row, index) {
            let { id, individual_item_id } = row;
            if (individual_item_id) {
                try {
                    this.$confirm(
                        "¿Estás seguro de querer eliminar este insumo?",
                        "Eliminar",
                        {
                            confirmButtonText: "Confirmar",
                            cancelButtonText: "Cancelar",
                            type: "warning",
                        }
                    )
                        .then(() => {
                            this.$http
                                .delete(`/${this.resource}/item-supply/${id}`)
                                .then((response) => {
                                    if (response.data.success) {
                                        this.form.supplies.splice(index, 1);
                                    }
                                });
                        })
                        .catch(() => {
                            this.$message.error("Operación cancelada");
                        });
                } catch (error) {
                    console.log(error);
                }
            } else {
                this.form.supplies.splice(index, 1);
            }
        },
        validateUnitTypeRanges() {
            if (
                !this.form.item_unit_types ||
                this.form.item_unit_types.length === 0
            ) {
                return true;
            }

            // Verificar si algún item tiene rangos
            const hasAnyRange = this.form.item_unit_types.some(
                (unit) => unit.range_max || unit.range_min
            );

            for (let i = 0; i < this.form.item_unit_types.length; i++) {
                const unitType = this.form.item_unit_types[i];
                let price_default = unitType.price_default;
                let prices = [
                    unitType.price1,
                    unitType.price2,
                    unitType.price3,
                ];
                let price_default_value = prices[price_default - 1];

                if (price_default_value < 0.07) {
                    this.$message.error(
                        `Línea ${
                            i + 1
                        }: El precio por defecto no puede ser menor a 0.07`
                    );
                    return false;
                }
                // Si tiene rango máximo pero no mínimo
                if (unitType.range_max && !unitType.range_min) {
                    this.$message.error(
                        `Línea ${
                            i + 1
                        }: Si especifica un rango máximo, debe especificar también un rango mínimo`
                    );
                    return false;
                }

                if (unitType.range_max && unitType.range_min) {
                    if (
                        parseFloat(unitType.range_max) <=
                        parseFloat(unitType.range_min)
                    ) {
                        this.$message.error(
                            `Línea ${
                                i + 1
                            }: El rango máximo debe ser mayor que el rango mínimo`
                        );
                        return false;
                    }
                }

                // Si hay algún item con rangos, validar que todos tengan factor 1
                if (hasAnyRange && parseFloat(unitType.quantity_unit) !== 1) {
                    this.$message.error(
                        `Línea ${
                            i + 1
                        }: Cuando se utilizan rangos, todos las presentaciones deben tener factor 1`
                    );
                    return false;
                }
            }

            return true;
        },
        fetchCoupons() {
            axios
                .get("/cupones/api/coupons")
                .then((response) => {
                    this.coupons = response.data;
                })
                .catch((error) => {
                    console.error("Error al cargar los cupones:", error);
                });
        },
        async loadLabelOptions() {
            try {
                const response = await this.$http.get('/label_colors/options');
                this.labelOptions = response.data;
            } catch (error) {
                console.error("Error al cargar las opciones de etiquetas:", error);
            }
        },
        getContrastColor(hexcolor) {
            if (!hexcolor) return '#000000'
            
            hexcolor = hexcolor.replace('#', '')
            
            const r = parseInt(hexcolor.substr(0, 2), 16)
            const g = parseInt(hexcolor.substr(2, 2), 16)
            const b = parseInt(hexcolor.substr(4, 2), 16)
            
            const luminance = (0.299 * r + 0.587 * g + 0.114 * b) / 255
            
            return luminance > 0.5 ? '#000000' : '#ffffff'
        },

        changeHasBonusItem() {
            let { has_bonus_item } = this.form;
            if (!has_bonus_item) {
                this.form.bonus_items = [];
            }
        },
        clickDeleteBonusItem(idx) {
            this.form.bonus_items.splice(idx, 1);
        },
        changeBonusItem() {},
        addRowSize(sizes) {
            this.form.sizes = sizes;
        },
        clickSizes() {
            this.showDialogSizes = true;
        },
        openFileInputBrand() {
            this.$refs.fileInputBrand.click();
        },
        handleFileChangeBrand(event) {
            const file = event.target.files[0];

            const allowedTypes = ["image/jpeg", "image/png", "image/gif"];
            if (file && allowedTypes.includes(file.type)) {
                this.form_brand.file = file;
                const reader = new FileReader();
                reader.onload = () => {
                    this.selectedImageBrand = reader.result;
                };
                reader.readAsDataURL(file);
            } else {
                this.selectedImageBrand = null;
                this.$message.error(
                    "Por favor, seleccione un archivo de imagen válido (JPEG, PNG o GIF)."
                );
            }
        },
        openFileInput() {
            this.$refs.fileInput.click();
        },
        handleFileChange(event) {
            const file = event.target.files[0];
            const allowedTypes = ["image/jpeg", "image/png", "image/gif"];
            if (file && allowedTypes.includes(file.type)) {
                this.form_category.file = file;
                const reader = new FileReader();
                reader.onload = () => {
                    this.selectedImage = reader.result;
                };
                reader.readAsDataURL(file);
            } else {
                this.selectedImage = null;
                this.$message.error(
                    "Por favor, seleccione un archivo de imagen válido (JPEG, PNG o GIF)."
                );
            }
        },
        setDigemidData(data) {
            let info = this.digemidCodes.find((d) => d.cod_prod == data);
            if (info) {
                let { nom_titular, num_regsan, nom_prod } = info;
                this.form.laboratory = nom_titular;
                this.form.sanitary = num_regsan;
                this.form.name_digemid = nom_prod;
            } else {
                this.form.laboratory = null;
                this.form.sanitary = null;
                this.form.name_digemid = null;
            }
        },
        digemidInfo(data) {
            let html = "<p>Código del producto: " + data.cod_prod + "</p>";
            html += "<p>Nombre del producto: " + data.nom_prod + "</p>";
            html +=
                "<p>Nombre de la forma farmacéutica: " +
                data.nom_form_farm_simplif +
                "</p>";
            if (data.concent && data.concent != "." && data.concent != "-") {
                html += "<p>Concentración: " + data.concent + "</p>";
            }
            html += "<p>Fracciones: " + data.fracciones + "</p>";

            return html;
        },
        async searchRemoteDigemid(input) {
            input = input.trim();
            if (input.length > 2) {
                this.loading_digemid = true;
                const params = {
                    input: input,
                };
                try {
                    const response = await this.$http.get(
                        `/${this.resource}/codes-digemid/`,
                        { params }
                    );

                    this.digemidCodes = response.data.digemid_codes;
                } catch (e) {
                    console.log(e);
                } finally {
                    this.loading_digemid = false;
                }
            }
        },
        changeDefaultPriceStore(idx) {
            for (let i = 0; i < this.form.item_unit_types.length; i++) {
                this.form.item_unit_types[i].default_price_store = false;
            }
            this.form.item_unit_types[idx].default_price_store = true;
        },
        changeDefaultFactor(idx) {
            for (let i = 0; i < this.form.item_unit_types.length; i++) {
                this.form.item_unit_types[i].factor_default = false;
            }
            this.form.item_unit_types[idx].factor_default = true;
        },
        ...mapActions(["loadConfiguration"]),
        setDefaultConfiguration() {
            this.form.sale_affectation_igv_type_id = this.config
                ? this.config.affectation_igv_type_id
                : "10";
            this.form.purchase_affectation_igv_type_id = this.config
                ? this.config.purchase_affectation_igv_type_id
                : "10";
            if (this.form.purchase_affectation_igv_type_id == null) {
                this.form.purchase_affectation_igv_type_id = "10";
            }
    
            this.$http.get(`/configurations/record`).then((response) => {

                if(!this.recordId){
                    this.form.has_igv = response.data.data.include_igv;
                    this.form.purchase_has_igv = response.data.data.include_igv;
                }
                this.item_complements = response.data.data.item_complements;
                // this.$setStorage('configuration',response.data.data)
                this.$store.commit("setConfiguration", response.data.data);
                this.loadConfiguration();
            });
        },
        purchaseChangeIsc() {
            if (!this.form.purchase_has_isc) {
                this.form.purchase_system_isc_type_id = null;
                this.form.purchase_percentage_isc = 0;
            }
        },
        changeIsc() {
            if (!this.form.has_isc) {
                this.form.system_isc_type_id = null;
                this.form.percentage_isc = 0;
            }
        },
        clickAddAttribute() {
            this.form.attributes.push({
                attribute_type_id: null,
                description: null,
                value: null,
                start_date: null,
                end_date: null,
                duration: null,
            });
        },
        async reloadTables() {
            await this.$http
                .get(`/${this.resource}/tables`)
                .then((response) => {
                    this.unit_types = response.data.unit_types;
                    this.accounts = response.data.accounts;
                    this.currency_types = response.data.currency_types;
                    this.system_isc_types = response.data.system_isc_types;
                    this.affectation_igv_types =
                        response.data.affectation_igv_types;
                    this.warehouses = response.data.warehouses;
                    this.categories = response.data.categories;
                    this.coupons = response.coupons;
                    this.brands = response.data.brands;

                    this.form.sale_affectation_igv_type_id =
                        this.affectation_igv_types.length > 0
                            ? this.affectation_igv_types[0].id
                            : null;
                    this.form.purchase_affectation_igv_type_id =
                        this.affectation_igv_types.length > 0
                            ? this.affectation_igv_types[0].id
                            : null;
                });
        },
        changeLotsEnabled() {
            // if(!this.form.lots_enabled){
            //     this.form.lot_code = null
            //     this.form.lots = []
            // }
        },
        changeProductioTab() {},
        addRowLot(lots) {
            this.form.lots = lots;
        },
        clickLotcode() {
            // this.showDialogLots = true;
            this.showDialogLotsWithoutStock = true;
        },
        changeHaveAccount() {
            if (!this.have_account) this.form.account_id = null;
        },
        changeEnabledPercentageOfProfit() {
            // if(!this.enabled_percentage_of_profit) this.form.percentage_of_profit = 0
        },
        clickDelete(id) {
            this.$http
                .delete(`/${this.resource}/item-unit-type/${id}`)
                .then((res) => {
                    if (res.data.success) {
                        this.loadRecord();
                        this.$message.success(
                            "Se eliminó correctamente el registro"
                        );
                    }
                })
                .catch((error) => {
                    if (error.response.status === 500) {
                        this.$message.error("Error al intentar eliminar");
                    } else {
                        console.log(error.response.data.message);
                    }
                });
        },
        changeHasPerception() {
            if (!this.form.has_perception) {
                this.form.percentage_perception = null;
            }
        },
        clickAddRow() {
            this.form.item_unit_types.push({
                id: null,
                description: null,
                unit_type_id: "NIU",
                quantity_unit: 0,
                price1: 0,
                price2: 0,
                price3: 0,
                price_default: 2,
                barcode: null,
                factor_default: this.form.item_unit_types.length == 0,
                default_price_store: 2,
            });
        },
        clickCancel(index) {
            this.form.item_unit_types.splice(index, 1);
        },
        initForm() {
            (this.loading_submit = false), (this.errors = {});

            this.form = {
                supplier_id: null,
                payment_conditions: [
                    {
                        payment_condition_id: "01",
                        name: "Contado",
                        price: 0,
                    },
                    {
                        payment_condition_id: "02",
                        name: "Crédito",
                        price: 0,
                    },
                ],
                is_input: true,
                bonus_items: [],
                start: null,
                end: null,
                sizes: [],
                has_bonus_item: false,
                has_sizes: false,
                limit_sale_daily: 0,
                frequent: null,
                id: null,
                info_link: null,
                colors: [],
                item_type_id: "01",
                internal_id: null,
                item_code: null,
                item_code_gs1: null,
                description: null,
                name: null,
                second_name: null,
                unit_type_id: "NIU",
                currency_type_id: "PEN",
                sale_unit_price: 0,
                purchase_unit_price: 0,
                has_isc: false,
                system_isc_type_id: null,
                percentage_isc: 0,
                suggested_price: 0,
                sale_affectation_igv_type_id: null,
                purchase_affectation_igv_type_id: null,
                calculate_quantity: false,
                stock: 0,
                stock_min: 1,
                has_igv: true,
                has_perception: false,
                item_unit_types: [],
                percentage_of_profit: 0,
                percentage_perception: null,
                image: null,
                image_url: null,
                temp_path: null,
                is_set: false,
                account_id: null,
                category_id: null,
                brand_id: null,
                date_of_due: null,
                lot_code: null,
                line: null,
                lots_enabled: false,
                lots: [],
                attributes: [],
                series_enabled: false,
                purchase_has_igv: true,
                web_platform_id: null,
                has_plastic_bag_taxes: false,
                can_edit_price: false,
                item_warehouse_prices: [],
                item_customer_prices: [],
                item_supplies: [],

                purchase_has_isc: false,
                purchase_system_isc_type_id: null,
                purchase_percentage_isc: 0,
                subject_to_detraction: false,

                exchange_points: false,
                quantity_of_points: 0,
                factory_code: null,
                restrict_sale_cpe: false,
                shared: false,
                label_color_id: null,
            };

            this.show_has_igv = true;
            this.purchase_show_has_igv = true;
            this.enabled_percentage_of_profit = false;
        },
        onSuccess(response, file, fileList) {
            if (response.success) {
                this.form.image = response.data.filename;
                this.form.image_url = response.data.temp_image;
                this.form.temp_path = response.data.temp_path;
            } else {
                this.$message.error(response.message);
            }
        },
        changeAffectationIgvType() {
            let affectation_igv_type_exonerated = [
                20, 21, 30, 31, 32, 33, 34, 35, 36, 37,
            ];
            let is_exonerated = affectation_igv_type_exonerated.includes(
                parseInt(this.form.sale_affectation_igv_type_id)
            );

            if (is_exonerated) {
                console.log("aqui?");
                this.show_has_igv = false;
                this.form.has_igv = true;
            } else {
                this.show_has_igv = true;
            }
        },
        changePurchaseAffectationIgvType() {
            let affectation_igv_type_exonerated = [
                20, 21, 30, 31, 32, 33, 34, 35, 36, 37,
            ];
            let is_exonerated = affectation_igv_type_exonerated.includes(
                parseInt(this.form.purchase_affectation_igv_type_id)
            );

            if (is_exonerated) {
                this.purchase_show_has_igv = false;
                this.form.purchase_has_igv = true;
            } else {
                this.purchase_show_has_igv = true;
            }
        },
        resetForm() {
            this.payment_conditions = [
                {
                    payment_condition_id: "01",
                    name: "Contado",
                    price: 0,
                },
                {
                    payment_condition_id: "02",
                    name: "Crédito",
                    price: 0,
                },
            ];
            this.initForm();
            this.form.sale_affectation_igv_type_id =
                this.affectation_igv_types.length > 0
                    ? this.affectation_igv_types[0].id
                    : null;
            this.form.purchase_affectation_igv_type_id =
                this.affectation_igv_types.length > 0
                    ? this.affectation_igv_types[0].id
                    : null;
            this.setDefaultConfiguration();
        },
        async create() {
            // Resetear estado inicial
            this.item_suplly_id = null;
            this.item_bonus_id = null;
            this.previousTab = null;
            
            // Configurar pestaña inicial basado en si es edición o creación
                this.activeName = "first";
                this.previousTab = "first";
            
        
            this.titleDialog = this.recordId
                ? "Editar Producto"
                : "Nuevo Producto";

            if (this.isReadonly) {
                this.titleDialog = "Detalle Producto";
            }

            if (this.recordId) {
                await this.$http
                    .get(`/${this.resource}/record/${this.recordId}`)
                    .then((response) => {
                        this.form = response.data.data;
                        let { start, end } = this.form;
                        let { payment_conditions } = this.form;
                        
                        if (
                            !payment_conditions ||
                            payment_conditions.length === 0
                        ) {
                            this.form.payment_conditions =
                                this.payment_conditions;
                        } else if (payment_conditions.length === 1) {
                            let payment_condition = payment_conditions[0];
                            this.form.payment_conditions =
                                this.payment_conditions.map((pc) =>
                                    pc.payment_condition_id ===
                                    payment_condition.payment_condition_id
                                        ? payment_condition
                                        : pc
                                );
                        } else {
                            this.form.payment_conditions = payment_conditions;
                        }
                        if (start && end) {
                            this.form.start = start;
                            this.form.end = end;
                        }
                        this.form.item_unit_types =
                            this.form.item_unit_types.map((i) => ({
                                ...i,
                                factor_default: !!i.factor_default,
                                default_price_store: !!i.default_price_store,
                            }));
                        this.has_percentage_perception = this.form
                            .percentage_perception
                            ? true
                            : false;
                            this.changeAffectationIgvType();
                            this.changePurchaseAffectationIgvType();
                    });
            }else{
                this.form.unit_type_id = "ZZ";
            }

            this.setDataToItemWarehousePrices();
            this.setDataToItemCustomerPrices();

            if(this.form.unit_type_id == 'ZZ'){
                this.form.calculate_quantity = false;
            }
        },
        changeUnitType(){
            if(this.form.unit_type_id == 'ZZ'){
                this.form.calculate_quantity = false;
            }
        },
        setDataToItemCustomerPrices() {
            this.customerTypes.forEach((clientType) => {
                let item_customer_price = _.find(
                    this.form.item_customer_prices,
                    { person_type_id: clientType.id }
                );

                if (!item_customer_price) {
                    this.form.item_customer_prices.push({
                        id: null,
                        item_id: null,
                        person_type_id: clientType.id,
                        price: null,
                        description: clientType.description,
                    });
                }
            });

            this.form.item_customer_prices = _.orderBy(
                this.form.item_customer_prices,
                ["person_type_id"]
            );
        },
        setDataToItemWarehousePrices() {
            this.warehouses.forEach((warehouse) => {
        
                let restrict_stock_quantity = null;

                if (
                    this.form.warehouses &&
                    Array.isArray(this.form.warehouses)
                ) {
                    const warehouseItem = this.form.warehouses.find(
                        (w) => w.id === warehouse.id
                    );
                    if (warehouseItem) {
                        restrict_stock_quantity =
                            warehouseItem.restrict_stock_quantity;
                    }
                }
                // let restrict_stock_quantity = this.form.warehouses.find(
                //     (w) => w.id === warehouse.id
                // )
                //     ? this.form.warehouses.find((w) => w.id === warehouse.id)
                //           .restrict_stock_quantity
                //     : null;
                let item_warehouse_price = _.find(
                    this.form.item_warehouse_prices,
                    { warehouse_id: warehouse.id }
                );

                if (!item_warehouse_price) {
                    this.form.item_warehouse_prices.push({
                        id: null,
                        item_id: null,
                        warehouse_id: warehouse.id,
                        price: null,
                        description: warehouse.description,
                        restrict_stock_quantity,
                    });
                } else {
                    item_warehouse_price.restrict_stock_quantity =
                        restrict_stock_quantity;
                }
            });

            this.form.item_warehouse_prices = _.orderBy(
                this.form.item_warehouse_prices,
                ["warehouse_id"]
            );
        },
        loadRecord() {
            if (this.recordId) {
                this.$http
                    .get(`/${this.resource}/record/${this.recordId}`)
                    .then((response) => {
                        this.form = response.data.data;
                        this.changeAffectationIgvType();
                        this.changePurchaseAffectationIgvType();
                    })
                    .catch((error) => {
                        console.error("Error al cargar el registro:", error);
                    });
            }
        },
        calculatePercentageOfProfitBySale() {
            let difference =
                parseFloat(this.form.sale_unit_price) -
                parseFloat(this.form.purchase_unit_price);

            if (parseFloat(this.form.purchase_unit_price) === 0) {
                this.form.percentage_of_profit = 0;
            } else {
                if (this.enabled_percentage_of_profit)
                    this.form.percentage_of_profit =
                        (difference /
                            parseFloat(this.form.purchase_unit_price)) *
                        100;
            }
        },
        calculatePercentageOfProfitByPurchase() {
            if (this.form.percentage_of_profit === "") {
                this.form.percentage_of_profit = 0;
            }

            if (this.enabled_percentage_of_profit)
                this.form.sale_unit_price =
                    (this.form.purchase_unit_price *
                        (100 + parseFloat(this.form.percentage_of_profit))) /
                    100;
        },
        calculatePercentageOfProfitByPercentage() {
            if (this.form.percentage_of_profit === "") {
                this.form.percentage_of_profit = 0;
            }

            if (this.enabled_percentage_of_profit)
                this.form.sale_unit_price =
                    (this.form.purchase_unit_price *
                        (100 + parseFloat(this.form.percentage_of_profit))) /
                    100;
        },
        validateItemUnitTypes() {
            let error_by_item = 0;

            if (this.form.item_unit_types.length > 0) {
                this.form.item_unit_types.forEach((item) => {
                    if (parseFloat(item.quantity_unit) < 0.0001) {
                        error_by_item++;
                    }
                });
            }

            return error_by_item;
        },
        checkUnitValue() {
            if (this.configuration && !this.configuration.price_item_007) {
                let min = this.form.has_igv ? 0.07 : 0.06;
                if (this.form.sale_unit_price < min) {
                    this.$message.error(
                        "El precio de venta no puede ser menor a S/" + min
                    );
                    return false;
                }
            }
            return true;
        },
        validateItemUnitTypesByWarehouse() {
            if (
                !this.configuration ||
                !this.configuration.item_unit_type_by_warehouse
            )
                return true;
            let error_by_item = 0;
            let seen = new Set();

            if (this.form.item_unit_types.length > 0) {
                this.form.item_unit_types.forEach((item) => {
                    const key = `${item.description}-${item.unit_type_id}-${item.warehouse_id}`;
                    if (seen.has(key)) {
                        error_by_item++;
                    }
                    seen.add(key);
                });
            }

            if (error_by_item > 0) {
                this.$message.error(
                    "No pueden existir registros duplicados con la misma descripción, tipo de unidad y almacén"
                );
                return false;
            }
            return true;
        },
        async submit() {
            if (!this.validateItemUnitTypesByWarehouse()) return;
            if (!this.validateUnitTypeRanges()) return;
            const stock = parseInt(this.form.stock);
            if (isNaN(stock)) {
                return this.$message.error(
                    "Stock Inicial debe ser un número entero."
                );
            }

            if (this.validateItemUnitTypes() > 0)
                return this.$message.error(
                    "El campo factor no puede ser menor a 0.0001"
                );

            if (this.fromPharmacy === true) {
                if (this.form.cod_digemid === null) {
                    return this.$message.error("Debe haber un codigo DIGEMID");
                }
                if (this.form.sanitary === null) {
                    return this.$message.error(
                        "Debe haber un Registro Sanitario"
                    );
                }
            }
            if (this.form.has_perception && !this.form.percentage_perception)
                return this.$message.error("Ingrese un porcentaje");
            if (this.form.has_perception && this.form.percentage_perception) {
                let valid = ["1", "2", "0.5"];
                if (!valid.includes(this.form.percentage_perception))
                    return this.$message.error(
                        "Ingrese un porcentaje valido de percepción: (1, 2, 0.5)"
                    );
            }

            if (this.form.lots_enabled && stock > 0) {
                if (!this.form.lot_code)
                    return this.$message.error("Código de lote es requerido");

                if (!this.form.date_of_due)
                    return this.$message.error(
                        "Fecha de vencimiento es requerido si lotes esta habilitado."
                    );
            }

            if (!this.recordId && this.form.series_enabled) {
                if (this.form.lots.length > this.form.stock)
                    return this.$message.error(
                        "La cantidad de series registradas es superior al stock"
                    );

                if (this.form.lots.length != this.form.stock)
                    return this.$message.error(
                        "La cantidad de series registradas son diferentes al stock"
                    );
            }

            if (this.form.has_isc) {
                if (this.form.percentage_isc <= 0)
                    return this.$message.error(
                        "El porcentaje isc debe ser mayor a 0"
                    );
            }

            if (this.form.purchase_has_isc) {
                if (this.form.purchase_percentage_isc <= 0)
                    return this.$message.error(
                        "El porcentaje isc debe ser mayor a 0 (Compras)"
                    );
            }
            console.log(
                "🚀 ~ file: form.vue:2943 ~ submit ~ this.form:",
                this.form
            );

            this.loading_submit = true;

            await this.$http
                .post(`/${this.resource}-input`, this.form)
                .then((response) => {
                    console.log(response.data);
                    if (response.data.success) {
                        this.$message.success(response.data.message);
                        if (this.external) {
                            this.$eventHub.$emit(
                                "reloadDataItems",
                                response.data.id
                            );
                        } else {
                            this.$eventHub.$emit("reloadData");
                        }
                        this.close();
                    } else {
                        this.$message.error(response.data.message);
                    }
                })
                .catch((error) => {
                    if (error.response.status === 422) {
                        this.errors = error.response.data;
                    } else {
                        console.log(error);
                        this.$message.error(error.response.data.message);
                    }
                })
                .then(() => {
                    this.loading_submit = false;
                });
        },
        close() {
            if (this.isReadonly) return;
            this.resetForm();
            // Usar $nextTick para asegurar que el DOM esté actualizado
        
            // Resetear el historial de pestañas
            this.previousTab = null;
            this.activeName = "first";
            this.$emit("update:showDialog", false);
        },
        changeHasIsc() {
            this.form.system_isc_type_id = null;
            this.form.percentage_isc = 0;
            this.form.suggested_price = 0;
        },
        changeSystemIscType() {
            if (this.form.system_isc_type_id !== "03") {
                this.form.suggested_price = 0;
            }
        },
        saveCategory() {
            this.form_category.add = false;
            this.form_category.image = this.selectedImage;
            const formData = new FormData();
            formData.append("image", this.form_category.file);
            formData.append("name", this.form_category.name);

            this.$http
                .post(`/categories`, formData, {
                    headers: {
                        "Content-Type": "multipart/form-data",
                    },
                })
                .then((response) => {
                    if (response.data.success) {
                        this.$message.success(response.data.message);
                        this.categories.push(response.data.data);
                        this.form_category.name = null;
                        this.selectedImage = null;
                    } else {
                        this.$message.error("No se guardaron los cambios");
                    }
                })
                .catch((error) => {});
        },
        saveBrand() {
            this.form_brand.add = false;
            this.form_brand.image = this.selectedImageBrand;
            const formData = new FormData();
            formData.append("image", this.form_brand.file);
            formData.append("name", this.form_brand.name);
            this.$http
                .post(`/brands`, formData, {
                    headers: {
                        "Content-Type": "multipart/form-data",
                    },
                })
                .then((response) => {
                    if (response.data.success) {
                        this.$message.success(response.data.message);
                        this.brands.push(response.data.data);
                        this.form_brand.name = null;
                        this.selectedImageBrand = null;
                    } else {
                        this.$message.error("No se guardaron los cambios");
                    }
                })
                .catch((error) => {});
        },
        changeAttributeType(index) {
            let attribute_type_id =
                this.form.attributes[index].attribute_type_id;
            let attribute_type = _.find(this.attribute_types, {
                id: attribute_type_id,
            });
            this.form.attributes[index].description =
                attribute_type.description;
        },
        clickRemoveAttribute(index) {
            this.form.attributes.splice(index, 1);
        },
        async searchRemoteItems(input) {
            if (input.length > 2) {
                this.loading_search = true;
                const params = {
                    input: input,
                    search_by_barcode: this.search_item_by_barcode ? 1 : 0,
                    production: 1,
                };
                await this.$http
                    .get(`/${this.resource}/search-items/`, { params })
                    .then((response) => {
                        this.items = response.data.items;
                        this.loading_search = false;
                        // this.enabledSearchItemsBarcode()
                        // this.enabledSearchItemBySeries()
                        if (this.items.length == 0) {
                            // this.filterItems()
                        }
                    });
            } else {
                // await this.filterItems()
            }
        },
        getItems() {
            this.$http.get(`/${this.resource}/item/tables`).then((response) => {
                this.items = response.data.items;
            });
        },
        changeItem() {
            // this.getItems();
            this.item_suplly = _.find(this.items, { id: this.item_suplly_id });
            this.item_bonus = _.find(this.items, { id: this.item_bonus_id });
            /*
            this.form.unit_price = this.item_suplly.sale_unit_price;

            this.lots = this.item_suplly.lots

            this.form.has_igv = this.item_suplly.has_igv;

            this.form.affectation_igv_type_id = this.item_suplly.sale_affectation_igv_type_id;
            this.form.quantity = 1;
            this.item_unit_types = this.item_suplly.item_unit_types;

            (this.item_unit_types.length > 0) ? this.has_list_prices = true : this.has_list_prices = false;
            */
        },
        focusSelectItem() {
            this.$refs.selectSearchNormal.$el
                .getElementsByTagName("input")[0]
                .focus();
        },

        ItemSlotTooltipView(item) {
            return ItemSlotTooltip(item);
        },
        ItemOptionDescriptionView(item) {
            return ItemOptionDescription(item);
        },
        clickAddBonusItem() {
            // item_supplies
            if (this.form.bonus_items === undefined) this.form.bonus_items = [];
            let item = this.item_bonus;
            if (item === null) return false;
            if (item === undefined) return false;
            if (item.id === undefined) return false;
            this.items = [];
            this.item_bonus = {};

            item.item_id = this.form.id;
            //item.individual_item_id = item.id
            item.item_bonus_id = item.id;
            item.item_bonus = {
                description: item.description,
            };
            //item.individual_item = item
            item.quantity = 1;
            //if(isNaN(item.quantity)) item.quantity = 0 ;
            console.log(JSON.stringify(item));
            this.form.bonus_items.push(item);
            this.changeItem();
        },
        clickAddSupply() {
            // item_supplies
            if (this.form.supplies === undefined) this.form.supplies = [];
            let item = this.item_suplly;
            if (item === null) return false;
            if (item === undefined) return false;
            if (item.id === undefined) return false;
            this.items = [];
            this.item_suplly = {};

            item.item_id = this.form.id;
            //item.individual_item_id = item.id
            item.individual_item_id = item.id;
            item.individual_item = {
                description: item.description,
            };
            //item.individual_item = item
            // item.quantity = 0
            //if(isNaN(item.quantity)) item.quantity = 0 ;
            this.form.supplies.push(item);
            this.changeItem();
        },
    },
};
