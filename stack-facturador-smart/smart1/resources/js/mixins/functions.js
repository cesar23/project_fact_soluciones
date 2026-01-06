export const functions = {
    data() {
        return {
            showDialogCashesForSuperadmin:false,
            users_open_cash:[],
            unit_types_f:[],
            loading_search_exchange_rate: false,
            loading_search: false,
            percentage_igv: 0.18,
            blockingAdd: false,
            stock_net: null,
            items_with_restrictions: [],
            items_indivuals: [],
            items_indivuals_with_error: [],
            groupsItems: [],
            colorsGroup: [
                "#f56c6c",
                "#e6a23c",
                "#5cb87a",
                "#1989fa",
                "#6f7ad3",
                "#434343",
                "#909399",
                "#c0c4cc",
                "#c9e2f3",
                "#e9eef3",
                "#f4f4f5",
            ],
        };
    },
    created() {
        if(this.unit_types_f.length == 0){
            this.getUnitTypes();
        }
    },
    computed: {
        showKit() {
            return this.configuration && this.configuration.kit_pdf;
        },
        existActiveGroup() {
            return this.groupsItems.some((group) => group.selected);
        },
    },
    methods: {

        async checkCustomerFieldToCreateSaleNote(customer_id){
            try{

                const response = await this.$http.get('/customers/check-customer-field-to-create-sale-note/'+customer_id);
                let data = response.data;
                let pass = true;
                if(data.success){
                    this.$message.success(data.message);
                }else{
                    this.$message.error(data.message);
                    pass = false;
                }
                return pass;
            }catch(error){
                console.log(error);
                this.$message.error('Error al verificar el campo del cliente');
                return false;
            }
        },
        async getUserFiltersSetItems() {
            if(!this.configuration.show_filters_set_items_for_users){
                return;
            }
            console.log("haciendo peticion");
            const response = await this.$http.get('/users/get-filters-set-items');
            let data = response.data;
            this.user_filters_set_items = data.filter_active == 1 ? true : false;
            this.user_filters_set_items_type = data.filter_name == 'pack' ? true : false;

        },
        async changeUserFiltersSetItems(value) {
            const response = await this.$http.post('/users/change-filters-set-items', {
                filter_active: value
            });
            if (response.data.success) {
                this.$message.success(response.data.message);
            } else {
                this.$message.error(response.data.message);
            }
            return response.data;
        },
    
        async changeUserFiltersSetItemsType(value) {
            const response = await this.$http.post('/users/change-filters-set-items-type', {
                filter_name: value
            });
            if (response.data.success) {
                this.$message.success(response.data.message);
            } else {
                this.$message.error(response.data.message);
            }
            return response.data;
        },
        getUnitTypes() {
            let name = this.$options.name;
            if(name == undefined || !name.includes("Invoice")){
                return;
            }
            console.log(this.$options.name, this.$options.__file);
            this.$http.get("/unit_types/records").then((response) => {

                this.unit_types_f = response.data.data;
            });
        },
        getSelectSymbol(unit_type_id) {
            const unit_type = this.unit_types_f.find((unit_type) => unit_type.id === unit_type_id);
            return unit_type ? unit_type.symbol : unit_type_id;
        },
        setDecimalQuantityStock(number) {
            let configuration = this.configuration || this.config;
            if (typeof number === 'string') {
                let parsedNumber = parseFloat(number);
                if (!isNaN(parsedNumber)) {
                    return parsedNumber.toFixed(configuration.stock_decimal);
                }
            } else if (typeof number === 'number') {
                return number.toFixed(configuration.stock_decimal);
            }   
            console.log("el numero", number);
            return number;
        },
        setDecimalQuantity(number) {
            let configuration = this.configuration || this.config;
            if (typeof number === 'string') {
                let parsedNumber = parseFloat(number);
                if (!isNaN(parsedNumber)) {
                    return parsedNumber.toFixed(configuration.decimal_quantity);
                }
            } else if (typeof number === 'number') {
                return number.toFixed(configuration.decimal_quantity);
            }   
            return number;
        },
        validPurchaseOrden() {
            if (this.configuration.purchase_orden_in_item_set) {
                let existItemSet = this.form.items.some(
                    (item) => item.item.is_set
                );
                if (existItemSet) {
                    let purchase_order = this.form.purchase_order;
                    if (!purchase_order) {
                        this.$message.error("Debe ingresar la orden de compra");
                        return false;
                    } else {
                        this.form.purchase_orden = purchase_order.trim();
                    }
                }
            }
            return true;
        },
        getDefaultPaymentMethodType() {
            let payment_condition_id = "01";
            let exist = this.payment_method_types.find(
                (payment_method_type) =>
                    payment_method_type.id === payment_condition_id
            );
            if (exist) {
                return payment_condition_id;
            } else {
                console.log(this.payment_method_types, " en mixin");
                if (this.payment_method_types.length > 0) {
                    return this.payment_method_types[0].id;
                }
            }
            return null;
        },
        checkUnitTypeRange() {
            const { configuration, form } = this;

            // Validación inicial de configuración y datos
            if (
                !configuration?.range_item_unit_type ||
                !form?.item_unit_types?.length
            ) {
                console.log(
                    "Linea 1234: configuración o tipos de unidad no disponibles",
                    {
                        hasConfig: configuration?.range_item_unit_type,
                        hasTypes: form?.item_unit_types,
                    }
                );
                return;
            }

            const quantity = Number(form.quantity);
            const item_unit_types = form.item_unit_types;
            const current_unit_type_id = form.item_unit_type_id; // Obtener el ID actual

            // Verificar si algún tipo tiene rangos definidos
            const hasRanges = item_unit_types.some(
                (type) => type.range_min || type.range_max
            );
            if (!hasRanges) {
                console.log("Linea 1234: ningún tipo tiene rangos definidos");
                return;
            }

            console.log("Linea 1234: cantidad a evaluar:", quantity);
            console.log(
                "Linea 1234: tipos de unidad disponibles:",
                item_unit_types
            );

            // Encontrar el tipo de unidad correspondiente al rango
            const matching_unit_type = this.findMatchingUnitType(
                item_unit_types,
                quantity
            );

            if (
                matching_unit_type &&
                matching_unit_type.id !== current_unit_type_id
            ) {
                this.changePrice(matching_unit_type.id);
            } else {
                console.log(
                    "Linea 1234: no se encontró unidad de medida diferente para el rango"
                );
            }
        },

        findMatchingUnitType(types, quantity) {
            // Ordenar tipos por range_min
            const sortedTypes = [...types].sort(
                (a, b) => Number(a.range_min) - Number(b.range_min)
            );

            return sortedTypes.find((current, index) => {
                if (!current.range_min) return false;

                const min = Number(current.range_min);
                const max = current.range_max
                    ? Number(current.range_max)
                    : sortedTypes[index + 1]?.range_min
                    ? Number(sortedTypes[index + 1].range_min)
                    : Infinity;

                return quantity >= min && quantity <= max;
            });
        },
        getDecimalStock() {
            let configuration = this.configuration || this.config;
            if (configuration) {
                return configuration.stock_decimal;
            }
            return 4;
        },
        decimalStock(value) {
            let configuration = this.configuration || this.config;

            if (configuration) {
                return Number(value).toFixed(configuration.stock_decimal);
            }
            return value;
        },
        async checkReference(payments = []) {
            if (payments.length === 0) {
                return {
                    success: true,
                    message: "Referencia válida",
                };
            }
            const references = payments
                .map((payment) => payment.reference)
                .filter((reference) => reference !== null)
                .filter((reference) => reference !== "");
            if (references.length === 0) {
                return {
                    success: true,
                    message: "Referencia válida",
                };
            }
            const response = await this.$http.post("/check-reference", {
                references,
            });

            if (response.data.success) {
                return {
                    success: true,
                    message: "Referencia válida",
                };
            }
            let message = response.data.message;
            return {
                success: false,
                message: message,
            };
        },
        descriptionGroup(id) {
            let selectedGroup = this.groupsItems.find(
                (group) => group.selected
            );

            if (!selectedGroup) {
                return "Primero seleccione un kit";
            }

            if (id) {
                let group = this.groupsItems.find((group) => group.id === id);
                if (group) {
                    return `Remover de ${group.name}`;
                }
            }
            return `Agregar a ${selectedGroup.name}`;
        },
        initGroupItems() {
            this.groupsItems = [];
        },
        returnColorGroup(groupId) {
            const group = this.groupsItems.find((group) => {
                return group.id === groupId;
            });
            return group ? group.color : null;
        },
        getActiveGroupId() {
            const selectedGroup = this.groupsItems.find(
                (group) => group.selected
            );
            return selectedGroup ? selectedGroup.id : null;
        },
        addToGroup(idx) {
            let groupId = this.getActiveGroupId();

            if (!groupId) {
                this.$message.error("Debe seleccionar un kit");
                this.form.items[idx].item.inGroup = false;
                return;
            }
            let { item } = this.form.items[idx];
            if (item.inGroup) {
                let group = this.groupsItems.find(
                    (group) => group.id === groupId
                );

                this.form.items[idx].item.groupId = groupId;
                this.form.items[idx].item.groupColor = group.color;
                this.form.items[idx].item.groupName = group.name;
            } else {
                delete this.form.items[idx].item.groupId;
                delete this.form.items[idx].item.groupColor;
                delete this.form.items[idx].item.groupName;
            }

            console.log(this.groupsItems);
        },
        changeGroupName(group) {
            const groupItem = this.groupsItems.find(
                (item) => item.id === group.id
            );
            groupItem.name = group.name;

            // this.$forceUpdate();
        },
        updateNameForGroupItems() {
            this.form.items = this.form.items.map((item) => {
                let group = this.groupsItems.find(
                    (group) => group.id === item.item.groupId
                );
                console.log("1 ", this.groupsItems);
                console.log("2 ", item.item.groupId);
                if (item.item.groupId) {
                    item.item.groupName = group.name;
                }
                return item;
            });
        },
        getNewColor() {
            const usedColors = this.groupsItems.map((group) => group.color);
            return this.colorsGroup.find(
                (color) => !usedColors.includes(color)
            );
        },
        checkItemsAndGroups() {
            let items = this.form.items.length;
            let groups = this.groupsItems.length;
            if (items === 0) {
                this.$message.error("Debe agregar al menos un item");
                return false;
            }
            if (items == groups) {
                this.$message.error(
                    "Para agregar un kit debe agregar un item más"
                );
                return false;
            }

            return true;
        },
        removeGroupItem(idx) {
            let groupId = this.groupsItems[idx].id;
            this.form.items.forEach((item) => {
                if (item.item.groupId == groupId) {
                    item.item.inGroup = false;
                }
                delete item.item.groupId;
                delete item.item.groupColor;
                delete item.item.groupName;
            });
            this.groupsItems.splice(idx, 1);
        },
        addGroupItem() {
            if (!this.checkItemsAndGroups()) {
                return;
            }
            this.groupsItems = this.groupsItems.map((group) => {
                group.selected = false;
                return group;
            });
            const newId =
                this.groupsItems.length === 0
                    ? 1
                    : this.groupsItems[this.groupsItems.length - 1].id + 1;
            this.groupsItems.push({
                id: newId,
                name: `Kit ${newId}`,
                color: this.getNewColor(),
                selected: this.groupsItems.length === 0,
            });
        },
        restoreGroupItems() {
            const uniqueIds = new Set();

            this.groupsItems = this.form.items
                .filter((item) => item.item.groupId)
                .reduce((acc, item) => {
                    if (
                        item.item.groupId &&
                        !uniqueIds.has(item.item.groupId)
                    ) {
                        uniqueIds.add(item.item.groupId);
                        acc.push({
                            id: item.item.groupId,
                            name: item.item.groupName,
                            color: item.item.groupColor,
                            selected: true,
                        });
                    }
                    return acc;
                }, []);
        },
        selectGroupItem(idx) {
            console.log("🚀 ~ selectGroupItem ~ idx:", idx);
            this.groupsItems.forEach((group) => {
                group.selected = false;
            });
            this.groupsItems[idx].selected = true;
            console.log(
                "🚀 ~ selectGroupItem ~ this.groupsItems:",
                this.groupsItems
            );
        },
        copyToClipboard(internalId) {
            const el = document.createElement("textarea");
            el.value = internalId;
            document.body.appendChild(el);
            el.select();
            document.execCommand("copy");
            document.body.removeChild(el);
            this.$message.success("Copiado al portapapeles");
        },

        checkQuantitySet(quantity) {
            this.items_indivuals_with_error = [];
            let quantity_set = parseFloat(quantity);
            this.items_indivuals.forEach((item) => {
                let quantity_item = parseFloat(item.quantity);
                let quantity_total = quantity_item * quantity_set;

                let item_with_restrictions = this.items_with_restrictions.find(
                    (item_with_restrictions) => {
                        return item_with_restrictions.item_id == item.item_id;
                    }
                );
                console.log(
                    "🚀 ~ this.items_indivuals.forEach ~ item_with_restrictions:",
                    item_with_restrictions
                );
                if (item_with_restrictions) {
                    if (
                        item_with_restrictions.item_stock_net != null &&
                        item_with_restrictions.item_stock_net < quantity_total
                    ) {
                        this.items_indivuals_with_error.push(item);
                    }
                }
            });
            console.log(
                "🚀 ~ checkQuantitySet ~ this.items_indivuals_with_error:",
                this.items_indivuals_with_error
            );
        },
        checkRestrictStock(item) {
            this.items_indivuals_with_error = [];
            this.stock_net = null;
            this.items_with_restrictions = [];
            this.items_indivuals = [];
            if (!item) {
                return;
            }
            let itemId = item.id;
            console.log("🚀 ~ item ~ item:", item);
            this.blockingAdd = true;
            this.$http
                .get(`/check-restrict-stock/${itemId}`)
                .then((response) => {
                    let res = response.data;
                    if (!res.success) {
                        this.$message.error(res.message);
                    } else {
                        let {
                            item_stock_net,
                            is_set,
                            is_bonus,
                            bonus_items,
                            items_with_restrictions,
                        } = res;
                        if (is_set) {
                            this.items_indivuals = item.set_items;
                            this.items_with_restrictions =
                                items_with_restrictions;
                        } else if (is_bonus) {
                            this.items_indivuals = bonus_items;
                            this.items_with_restrictions =
                                items_with_restrictions;
                        } else {
                            this.stock_net = item_stock_net;
                        }
                    }
                })
                .catch((error) => {
                    console.log(error);
                })
                .finally(() => {
                    this.blockingAdd = false;
                });
        },
        setSearchTelephone(input) {
            if (!input) {
                return;
            }
            let { customer_id } = this.form;
            let customer = null;
            if (this.customers) {
                customer = this.customers.find((customer) => {
                    return customer.id == customer_id;
                });
            } else {
                customer = this.customer;
            }
            let { telephones, telephone } = customer;
            let all_telephones = [...telephones];
            if (telephone) {
                all_telephones.push(telephone);
            }
            let telephone_found = all_telephones.find((telephone) => {
                return telephone.includes(input);
            });

            if (telephone_found) {
                this.form.search_telephone = telephone_found;
            } else {
                delete this.form.search_telephone;
            }
        },
        searchExchangeRate() {
            return new Promise((resolve) => {
                this.loading_search_exchange_rate = true;
                this.$http
                    .post(`/services/exchange_rate`, this.form)
                    .then((response) => {
                        let res = response.data;
                        if (res.success) {
                            this.data = res.data;
                            this.form.buy = res.data[this.form.cur_date].buy;
                            this.form.sell = res.data[this.form.cur_date].sell;
                            this.$message.success(res.message);
                        } else {
                            this.$message.error(res.message);
                            this.loading_search_exchange_rate = false;
                        }
                        resolve();
                    })
                    .catch((error) => {
                        console.log(error.response);
                        this.loading_search_exchange_rate = false;
                    })
                    .then(() => {
                        this.loading_search_exchange_rate = false;
                    });
            });
        },

        searchServiceNumber() {
            return new Promise((resolve) => {
                this.loading_search = true;
                let identity_document_type_name = "";
                if (this.form.identity_document_type_id === "6") {
                    identity_document_type_name = "ruc";
                }
                if (this.form.identity_document_type_id === "1") {
                    identity_document_type_name = "dni";
                }
                this.$http
                    .get(
                        `/services/${identity_document_type_name}/${this.form.number}`
                    )
                    .then((response) => {
                        console.log(response.data);
                        let res = response.data;
                        if (res.success) {
                            this.form.name = res.data.name;
                            this.form.trade_name = res.data.trade_name;
                            this.form.address = res.data.address;
                            this.form.department_id = res.data.department_id;
                            this.form.province_id = res.data.province_id;
                            this.form.district_id = res.data.district_id;
                            this.form.phone = res.data.phone;
                        } else {
                            this.$message.error(res.message);
                        }
                        resolve();
                    })
                    .catch((error) => {
                        console.log(error.response);
                    })
                    .then(() => {
                        this.loading_search = false;
                    });
            });
        },
        async getPercentageIgv() {
            await this.$http
                .post(`/store/get_igv`, {
                    establishment_id: this.form.establishment_id,
                    date: this.form.date_of_issue,
                })
                .then((response) => {
                    this.percentage_igv = response.data;
                });
        },
        async getPercentageIgvWithParams(establishment_id, date_of_issue) {
            await this.$http
                .post(`/store/get_igv`, {
                    establishment_id: establishment_id,
                    date: date_of_issue,
                })
                .then((response) => {
                    this.percentage_igv = response.data;
                });
        },
    },
};
export const advance = {
    methods: {
        saveAdvanceDocument() {
            this.$http
                .post(`/advances/advance_document`, this.form_cash_document)
                .then((response) => {
                    if (!response.data.success) {
                        this.$message.error(response.data.message);
                    }
                })
                .catch((error) => console.log(error));
        },
        enoughAdvance(base = "form") {
            let advance = this.payment_destinations.find(
                (payment) => payment.id == "advance"
            );
            let [payment] = this[base].payments;

            let final_balance = advance.final_balance;
            let payment_amount = payment.payment;

            return final_balance >= payment_amount;
        },
        payWithAdvanceDocument(personProp = "customer_id") {
            let result = undefined;
            if (this.document.payments.length == 1) {
                let [payment] = this.document.payments;
                if (payment.payment_destination_id == "advance") {
                    let advance = this.payment_destinations.find(
                        (payment) => payment.id == "advance"
                    );
                    let person_id = undefined;
                    if (this.document[personProp] != undefined) {
                        person_id = this.document[personProp];
                    }

                    this.document.payments[0].person_id = person_id;
                    result = advance.advance_id;
                }
            }
            this.form_cash_document.advance_id = null;
            return result;
        },
        payWithAdvance(personProp = "customer_id") {
            let result = undefined;
            if (this.form.payments.length == 1) {
                let [payment] = this.form.payments;
                if (payment.payment_destination_id == "advance") {
                    let advance = this.payment_destinations.find(
                        (payment) => payment.id == "advance"
                    );
                    let person_id = undefined;
                    if (this.form[personProp] != undefined) {
                        person_id = this.form[personProp];
                    } else if (
                        this.document &&
                        this.document[personProp] != undefined
                    ) {
                        person_id = this.document[personProp];
                    }

                    this.form.payments[0].person_id = person_id;
                    result = advance.advance_id;
                }
            }
            this.form_cash_document.advance_id = null;
            return result;
        },
        removeAdvanceFromDestinations() {
            this.payment_destinations = this.payment_destinations.filter(
                (payment) => payment.id !== "advance"
            );
        },
        checkHasAdvance(idx) {
            if (this.form.payments.length > 1) {
                let payment = this.form.payments[idx];
                let payment_destination_id = payment.payment_destination_id;
                if (payment_destination_id === "advance") {
                    this.$message({
                        showClose: true,
                        type: "warning",
                        message:
                            "No se puede seleccionar 'adelanto' en una forma de pago diferente a las demás.",
                    });
                    //elige otro destino pero que no sea el que tenga el id "advance"
                    let other = this.payment_destinations.find(
                        (payment) => payment.id !== "advance"
                    );
                    this.form.payments[idx].payment_destination_id = other
                        ? other.id
                        : other;

                    return false;
                }
            }
            return true;
        },
        async getAdvance(personId) {
            if (!personId) return;
            this.removeAdvanceFromDestinations();
            const response = await this.$http(
                `/advances/get-advance/${personId}`
            );
            if (response.status === 200) {
                let { data } = response;
                let { success } = data;
                if (success) {
                    this.payment_destinations.unshift(data);
                    let [payment_destination] = this.payment_destinations;
                    this.form.payments.map((payment) => {
                        payment.payment_destination_id = payment_destination.id;
                    });
                } else {
                    this.payment_destinations =
                        this.payment_destinations.filter((payment) => {
                            return payment.id !== "advance";
                        });

                    this.form.payments.map((payment) => {
                        payment.payment_destination_id =
                            this.payment_destinations[0].id;
                    });
                }
            }
        },
        async getAdvanceThen(personId) {
            this.removeAdvanceFromDestinations();
            await this.$http(`/advances/get-advance/${personId}`).then(
                (response) => {
                    if (response.status === 200) {
                        let { data } = response;
                        let { success } = data;
                        if (success) {
                            this.payment_destinations.unshift(data);
                            let [payment_destination] =
                                this.payment_destinations;
                            this.form.payments.map((payment) => {
                                payment.payment_destination_id =
                                    payment_destination.id;
                            });
                        } else {
                            this.payment_destinations =
                                this.payment_destinations.filter((payment) => {
                                    return payment.id !== "advance";
                                });

                            this.form.payments.map((payment) => {
                                payment.payment_destination_id =
                                    this.payment_destinations[0].id;
                            });
                            this.$forceUpdate();
                        }
                    }
                }
            );
        },
    },
};
export const cash = {
    methods: {
        async getCash(user_id) {
            let { admin_seller_cash } = this.configuration;
            console.log(admin_seller_cash);
            if (!admin_seller_cash || this.typeUser != "admin") return;
            const response = await this.$http.get("/cash/get_cash/" + user_id);
            let error = false;
            if (response.status === 200) {
                const { data } = response;
                if (data && data.length > 0) {
                    this.payment_destinations = data;
                }
                this.$message({
                    showClose: true,
                    type: "success",
                    message: "Este comprobante será destinado al vendedor",
                });
                this.form.user_id = user_id;
            } else {
                error = true;
            }
            if (error) {
                this.$message({
                    showClose: true,
                    type: "warning",
                    message: "El vendedor no tiene cajas aperturadas",
                });
                this.form.payments.map((payment) => {
                    payment.payment_destination_id = null;
                });
            }
        },
    },
};
export const exchangeRate = {
    methods: {
        async searchExchangeRateByDate(exchange_rate_date) {
            let currency = undefined;
            if (this.currency_types) {
                currency = this.currency_types.find(
                    (currency) => currency.id === this.form.currency_type_id
                );
            }
            if (currency && currency.id !== "PEN" && currency.id !== "USD") {
                let response = await this.$http.get(
                    `/exchange_currency/${exchange_rate_date}/${currency.id}`
                );
                let success = response.data.success;
                if (!success) {
                    this.$message.error(response.data.message);
                }
                return parseFloat(response.data.sale);
            } else {
                try {
                    let response = await this.$http.get(
                        `/services/exchange/${exchange_rate_date}`
                    );
                    return parseFloat(response.data.sale);
                } catch (error) {
                    if (currency.id === "USD") {
                        let response = await this.$http.get(
                            `/exchange_currency/${exchange_rate_date}/${currency.id}`
                        );
                        return parseFloat(response.data.sale);
                    }
                }
            }
        },
    },
};

export const serviceNumber = {
    data() {
        return {
            loading_search: false,
        };
    },
    methods: {
        filterProvince() {
            this.form.province_id = null;
            this.form.district_id = null;
            this.filterProvinces();
        },
        filterProvinces() {
            this.provinces = this.all_provinces.filter((f) => {
                return f.department_id === this.form.department_id;
            });
        },
        filterDistrict() {
            this.form.district_id = null;
            this.filterDistricts();
        },
        filterDistricts() {
            this.districts = this.all_districts.filter((f) => {
                return f.province_id === this.form.province_id;
            });
        },
        async searchServiceNumberByType() {
            if (this.form.number === "") {
                this.$message.error("Ingresar el número a buscar");
                return;
            }
            let identity_document_type_name = "";
            if (this.form.identity_document_type_id === "6") {
                identity_document_type_name = "ruc";
            }
            if (this.form.identity_document_type_id === "1") {
                identity_document_type_name = "dni";
            }
            this.loading_search = true;
            let response = await this.$http.get(
                `/services/${identity_document_type_name}/${this.form.number}`
            );
            if (response.data.success) {
                let data = response.data.data;
                this.form.name = data.name;
                this.form.trade_name = data.trade_name;
                this.form.address = data.address;
                this.form.location_id = data.location_id;
                // this.form.department_id = data.department_id
                // this.form.province_id = data.province_id
                // this.form.district_id = data.district_id
                this.form.phone = data.phone;
                // this.filterProvinces()
                // this.filterDistricts()
            } else {
                this.$message.error(response.data.message);
            }
            this.loading_search = false;
        },
        async searchServiceNumber() {
            if (this.form.number === "") {
                this.$message.error("Ingresar el número a buscar");
                return;
            }
            this.loading_search = true;
            let response = await this.$http.get(
                `/services/ruc/${this.form.number}`
            );
            if (response.data.success) {
                let data = response.data.data;
                this.form.name = data.name;
                this.form.trade_name = data.trade_name;
            } else {
                this.$message.error(response.data.message);
            }
            this.loading_search = false;
        },
    },
};

// Funciones para payments - fee
// Usado en:
// purchases
export const fnPaymentsFee = {
    data() {
        return {};
    },
    methods: {
        initDataPaymentCondition() {
            this.readonly_date_of_due = false;
            this.form.date_of_due = this.form.date_of_issue;
        },
        calculatePayments() {
            let payment_count = this.form.payments.length;
            let total = this.form.total;

            let payment = 0;
            let amount = _.round(total / payment_count, 2);

            _.forEach(this.form.payments, (row) => {
                payment += amount;
                if (total - payment < 0) {
                    amount = _.round(total - payment + amount, 2);
                }
                row.payment = amount;
            });
        },
        clickAddFee() {
            this.form.date_of_due = moment().format("YYYY-MM-DD");
            this.form.fee.push({
                id: null,
                date: moment().format("YYYY-MM-DD"),
                currency_type_id: this.form.currency_type_id,
                amount: 0,
            });
            this.calculateFee();
        },
        clickAddFeeNew() {
            let firstCreditPayment = null;

            if (this.creditPaymentMethod.length > 0) {
                firstCreditPayment = this.creditPaymentMethod[0];
            }

            let date = moment(this.form.date_of_issue)
                .add(firstCreditPayment.number_days, "days")
                .format("YYYY-MM-DD");

            this.form.date_of_due = date;

            this.form.fee.push({
                id: null,
                purchase_id: null,
                payment_method_type_id: firstCreditPayment.id,
                date: date,
                currency_type_id: this.form.currency_type_id,
                amount: 0,
            });

            this.calculateFee();
        },
        calculateFee() {
            let fee_count = this.form.fee.length;
            let total = this.form.total;

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
        clickRemoveFee(index) {
            this.form.fee.splice(index, 1);
            this.calculateFee();
        },
    },
};

// Funciones para asignar series por usuario para multiples tipos de documentos
// Usado en:
// purchases
export const setDefaultSeriesByMultipleDocumentTypes = {
    data() {
        return {};
    },
    methods: {
        generalDisabledSeries() {
            if (this.authUser === undefined) return false;

            return (
                this.configuration.restrict_series_selection_seller &&
                this.authUser.type !== "admin"
            );
        },
        generalSetDefaultSerieByDocumentType(document_type_id) {
            if (this.authUser !== undefined) {
                if (this.authUser.multiple_default_document_types) {
                    const default_document_type_serie = _.find(
                        this.authUser.default_document_types,
                        { document_type_id: document_type_id }
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
            }
        },
    },
};

// funciones para sistema por puntos
// Usado en:
// invoice_generate.vue
// pos/payment.vue

export const pointSystemFunctions = {
    data() {
        return {
            customer_accumulated_points: 0,
            calculate_customer_accumulated_points: 0,
            total_exchange_points: 0,
            total_points_by_sale: 0,
        };
    },
    methods: {
        setTotalPointsBySale(configuration) {
            if (configuration && configuration.enabled_point_system) {
                const calculate_points =
                    (this.form.total / configuration.point_system_sale_amount) *
                    configuration.quantity_of_points;
                this.total_points_by_sale = configuration.round_points_of_sale
                    ? parseInt(calculate_points)
                    : _.round(calculate_points, 2);
                // this.total_points_by_sale = _.round((this.form.total / configuration.point_system_sale_amount) * configuration.quantity_of_points, 2)
            }
        },
        recalculateUsedPointsForExchange(row) {
            if (row.item.exchanged_for_points)
                row.item.used_points_for_exchange = this.getUsedPoints(row);
        },
        async setCustomerAccumulatedPoints(customer_id, enabled_point_system) {
            if (enabled_point_system) {
                await this.$http
                    .get(`/persons/accumulated-points/${customer_id}`)
                    .then((response) => {
                        this.customer_accumulated_points = response.data;
                        this.calculate_customer_accumulated_points =
                            response.data; //para calculos
                        this.calculateNewPoints();
                    });
            }
        },
        setTotalExchangePoints() {
            this.total_exchange_points = this.getTotalExchangePointsItems();
            this.calculateNewPoints();
        },
        hasPointsAvailable() {
            return this.calculate_customer_accumulated_points >= 0;
        },
        calculateNewPoints() {
            this.calculate_customer_accumulated_points =
                this.customer_accumulated_points - this.total_exchange_points;
        },
        validateExchangePoints() {
            if (!this.hasPointsAvailable()) {
                return {
                    success: false,
                    message: `El total de puntos a canjear excede los puntos acumulados: ${this.calculate_customer_accumulated_points} puntos`,
                };
            }

            return {
                success: true,
            };
        },
        getExchangePointDescription(row) {
            return `¿Desea canjearlo por ${this.getUsedPoints(row)} puntos?`;
        },
        getUsedPoints(row) {
            return _.round(row.item.quantity_of_points * row.quantity, 2);
        },
        getTotalExchangePointsItems() {
            return _.sumBy(this.form.items, (row) => {
                return row.item.exchanged_for_points
                    ? this.getUsedPoints(row)
                    : 0;
            });
        },
    },
};

// funciones para descuentos globales
// Usado en:
// tenant\purchases\form.vue
// resources\js\components\secondary\ListRestrictItems.vue

export const operationsForDiscounts = {
    data() {
        return {
            global_discount_types: [],
            global_discount_type: {},
            is_amount: true,
            total_global_discount: 0,
        };
    },
    computed: {
        isGlobalDiscountBase() {
            return this.config.global_discount_type_id === "02";
        },
    },
    methods: {
        deleteDiscountGlobal() {
            let discount = _.find(this.form.discounts, {
                discount_type_id: this.config.global_discount_type_id,
            });
            let index = this.form.discounts.indexOf(discount);

            if (index > -1) {
                this.form.discounts.splice(index, 1);
                this.form.total_discount = 0;
            }
        },
        discountGlobal(param_percentage_igv = null) {
            this.deleteDiscountGlobal();

            //input donde se ingresa monto o porcentaje
            let input_global_discount = parseFloat(this.total_global_discount);

            if (input_global_discount > 0) {
                const percentage_igv = param_percentage_igv
                    ? param_percentage_igv
                    : this.percentage_igv * 100;
                let base = this.isGlobalDiscountBase
                    ? parseFloat(this.form.total_taxed)
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
                        base - this.form.total_discount,
                        2
                    );
                    this.form.total_value = this.form.total_taxed;
                    this.form.total_igv = _.round(
                        this.form.total_taxed * (percentage_igv / 100),
                        2
                    );

                    //impuestos (isc + igv + icbper)
                    let total_plastic_bag_taxes = this.form
                        .total_plastic_bag_taxes
                        ? this.form.total_plastic_bag_taxes
                        : 0;

                    this.form.total_taxes = _.round(
                        this.form.total_igv +
                            this.form.total_isc +
                            total_plastic_bag_taxes,
                        2
                    );
                    this.form.total = _.round(
                        this.form.total_taxed + this.form.total_taxes,
                        2
                    );
                    this.form.subtotal = this.form.total;

                    if (this.form.total <= 0 && this.total_global_discount > 0)
                        this.$message.error(
                            "El total debe ser mayor a 0, verifique el tipo de descuento asignado (Configuración/Avanzado/Contable)"
                        );
                }
                // descuentos que no afectan la bi
                else {
                    this.form.total = _.round(this.form.total - amount, 2);
                }
                console.log('amount', amount);
                this.setGlobalDiscount(factor, _.round(amount, 2), base);
                this.autoAdjustCentIfNeeded();
            }
        },
        autoAdjustCentIfNeeded() {
            // Detecta si total, base o IGV terminan en .x9 y ajusta automáticamente +0.01
            const fieldsToCheck = ['total', 'total_taxed', 'total_igv'];
            let adjusted = false;

            fieldsToCheck.forEach(field => {
                const value = this.form[field];
                if (value) {
                    const valueStr = value.toFixed(2);
                    const lastDigit = valueStr.charAt(valueStr.length - 1);

                    // Si termina en 9, ajustar +0.01
                    if (lastDigit === '9') {
                        this.form[field] = _.round(Number(value) + 0.01, 2);

                        // Establecer el elemento a ajustar (necesario para calculateJustTotals)
                        this.elementToAdjust = field;

                        // Guardar el ajuste como lo hace adjustmentCentToElement
                        this.adjustmentCentElements = {
                            element: field,
                            type: '+'
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
        splitBase() {
            let hasUnaffected = this.form.items.some(
                (i) => i.affectation_igv_type_id !== "10"
            );
            if (!hasUnaffected) {
                this.total_global_discount = this.total_global_discount_aux;
                if (this.total_global_discount == 0 || !this.is_amount) return;
                    this.total_global_discount =
                        this.total_global_discount / 1.18;
                    
            }
            this.total_global_discount = Math.round(
                this.total_global_discount * 100
            );
            this.total_global_discount = this.total_global_discount / 100;
            console.log('this.total_global_discount', this.total_global_discount);
        },
    
        changeTypeDiscount() {
            this.splitBase();
            this.calculateTotal();
        },
        changeTotalGlobalDiscount() {
            this.total_global_discount = this.total_global_discount_aux;
            this.splitBase();
            this.calculateTotal();
        },
        setConfigGlobalDiscountType() {
            this.global_discount_type = _.find(this.global_discount_types, {
                id: this.config.global_discount_type_id,
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
            });
        },
    },
};

// funciones para restriccion de productos

// Usado en:
// resources\js\components\secondary\ListRestrictItems.vue
// modules\Order\Resources\assets\js\views\order_notes\partials\options.vue
// resources\js\views\tenant\documents\invoice_generate.vue
// resources\js\views\tenant\sale_notes\partials\option_documents.vue

export const fnRestrictSaleItemsCpe = {
    data() {
        return {};
    },
    computed: {
        fnApplyRestrictSaleItemsCpe() {
            if (this.configuration)
                return this.configuration.restrict_sale_items_cpe;

            return false;
        },
    },
    methods: {
        fnValidateRestrictSaleItemsCpe(form) {
            if (this.fnApplyRestrictSaleItemsCpe) {
                let errors_restricted = 0;

                form.items.forEach((row) => {
                    if (
                        this.fnIsRestrictedForSale(
                            row.item,
                            form.document_type_id
                        )
                    )
                        errors_restricted++;
                });

                if (errors_restricted > 0)
                    return this.fnGetObjectResponse(
                        false,
                        "No puede generar el comprobante, tiene productos restringidos."
                    );
            }

            return this.fnGetObjectResponse();
        },
        fnCheckIsInvoice(document_type_id) {
            return ["01", "03"].includes(document_type_id);
        },
        fnIsRestrictedForSale(item, document_type_id) {
            return (
                this.fnApplyRestrictSaleItemsCpe &&
                this.fnCheckIsInvoice(document_type_id) &&
                item != undefined &&
                item.restrict_sale_cpe
            );
        },
        fnGetObjectResponse(success = true, message = null) {
            return {
                success: success,
                message: message,
            };
        },
    },
};
