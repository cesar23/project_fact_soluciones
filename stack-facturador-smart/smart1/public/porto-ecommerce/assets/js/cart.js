function cart_add(data, quantity = 1, person_type_id = null) {
    try {

        console.log("person type id", person_type_id);
        let array = localStorage.getItem('products_cart');
        array = JSON.parse(array);

        let item = JSON.parse(data);
        let clientTypePrices = item.clientTypePrices;
        let sale_unit_price = item.sale_unit_price;
        let item_unit_types = item.item_unit_types;
        if(person_type_id && !clientTypePrices.isEmpty()){
            let clientTypePrice = clientTypePrices.find(x => x.person_type_id == person_type_id);
            if(clientTypePrice){
                sale_unit_price = clientTypePrice.price;
            }
        }
        if(item_unit_types){
            let price_store = item_unit_types.find(x => x.default_price_store == 1);
            if(price_store){
                let price_default = price_store.price_default;
                let prices = [price_store.price1, price_store.price2, price_store.price3];
                sale_unit_price = prices[price_default - 1];
            }
        }
        let found = array.find(x => x.id == item.id);

        if (!found) {
            item.quantity = parseInt(quantity);
            item.sale_unit_price = sale_unit_price;
            array.push(item);
            localStorage.setItem('products_cart', JSON.stringify(array));
            productsCartDropDown();

            jQuery('#moda-succes-add-product').modal('show');

            calculateTotalCart();

            $('#product_added').html(`
                <h1 class="product-title">${item.description}</h1>
                <div class="price-box">
                    <span class="product-price">S/ ${Number(sale_unit_price * item.quantity).toFixed(2)}</span>
                </div>
                <div class="product-desc">
                    <p>${item.name}</p>
                </div>
                <div class="product-quantity">
                    <span>Cantidad: ${item.quantity}</span>
                </div>`);

            $('#product_added_image').html(`<img src="/storage/uploads/items/${item.image_medium}" class="img" alt="product">`);
        }
        else {
            jQuery('#modal-already-product').modal('show');
        }

    } catch ({error}) {
        console.log(error);
    }
}

function productsCartDropDown() {
    jQuery(".dropdown-cart-products").empty();
    jQuery(".cart-count").empty();
    let count = 0;
    let array = localStorage.getItem('products_cart');
    array = JSON.parse(array);
    count = array.length;

    array.forEach(element => {
        if (!element.quantity) element.quantity = 1;
        
        jQuery(".dropdown-cart-products").append(`
            <div class="product">
                <div class="product-details">
                <h4 class="product-title">
                    <a href="$">${element.description}</a>
                </h4>
                <span class="cart-product-info">
                    <span class="cart-product-qty">${element.quantity}</span> x ${element.sale_unit_price}
                </span>
                </div>
                <figure class="product-image-container">
                    <a href="#" class="product-image">
                        <img alt="product" src="/storage/uploads/items/${element.image_small}" />
                    </a>
                    <a href="#" onclick="remove(${element.id})" class="btn-remove" title="Remove Product">
                        <i class="icon-cancel"></i>
                    </a>
                </figure>
            </div>`
        );
    });

    localStorage.setItem('products_cart', JSON.stringify(array));
    jQuery(".cart-count").append(count);
}

function calculateTotalCart()
{

	let array = localStorage.getItem('products_cart');
	array = JSON.parse(array);
	let total = 0;
	array.forEach(element => {
		total += parseFloat(element.sale_unit_price) * (element.quantity || 1);
	});

	$(".cart-total-price").empty();
    $(".cart-total-price").append(total.toFixed(2));


}

function logout()
{
	$.ajax({
		url: "/ecommerce/logout",
		method: 'get',
		headers: {
			'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
		},
		success: function (data) {
			location.reload()
		},
		error: function (error_data) {

		}
	});
}

function updateCartItemQuantity(productId, newQuantity) {
    let array = localStorage.getItem('products_cart');
    array = JSON.parse(array);
    
    let item = array.find(x => x.id == productId);
    if (item) {
        item.quantity = parseInt(newQuantity);
        localStorage.setItem('products_cart', JSON.stringify(array));
        calculateTotalCart();
    }
}
