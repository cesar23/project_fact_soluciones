@foreach ($dataPaginate as $item)
    @php
        $columns_to_show = 'col-md-4';
        if ($columns == 2) {
            // $columns_to_show = 'col-md-6';
            $columns_to_show = '50%';
        }
        if ($columns == 3) {
            // $columns_to_show = 'col-md-4';
            $columns_to_show = '33.33%';
        }
        if ($columns == 4) {
            // $columns_to_show = 'col-md-3';
            $columns_to_show = '25%';
        }
        if ($columns == 5) {
            // $columns_to_show = 'col-md-2';
            $columns_to_show = '20%';
        }
        if ($columns == 6) {
            // $columns_to_show = 'col-md-2';
            $columns_to_show = '16.66%';
        }
        if ($agent->isMobile()) {
            $columns_to_show = '50%';
        }

    @endphp

    {{-- <div class="col-6 {{ \Route::currentRouteName() == 'tenant.restaurant.menu' ? 'col-md-3' : 'col-md-4' }}"> --}}
    <div style="width:{{ $columns_to_show }};padding:5px;">
        <div class="product product-style {{ stock($item, $configuration) ? 'productdisabled' : '' }}">
            <figure class="product-image-container">
                <a href="/ecommerce/item/{{ $item->id }}" class="product-image product-image-list">
                    <img src="{{ $item->image == 'imagen-no-disponible.jpg' ? asset('logo/imagen-no-disponible.jpg') : asset('storage/uploads/items/' . $item->image) }}"
                        class="image" alt="{{ $item->image }}">
                </a>
                <a href="{{ route('item_partial', ['id' => $item->id]) }}" class="btn-quickview">Vista Rápida</a>
                {{-- <span class="product-label label-sale">-20%</span> --}}
                @if (json_encode($item->is_new) == 1)
                    <span class="product-label label-hot">New</span>
                @endif
                @if (stock($item, $configuration))
                    <span class="product-label product-danger">AGOTADO</span>
                @endif
            </figure>
            <div class="product-details">
                <div class="ratings-container">
                    <div class="product-ratings">
                        <span class="ratings" style="width:0%"></span>
                    </div>
                </div>
                {{ $item->internal_id }}
                <h2 class="product-title">
                    <a href="/ecommerce/item/{{ $item->id }}">{{ $item->description }}</a>
                </h2>
                <div class="price-box">
                    <!-- <span class="old-price">S/ {{ number_format($item->sale_unit_price * 1.2, 2) }}</span> -->
                    <span class="product-price">{{ $item->currency_type['symbol'] }}
                        @php
                                $sale_unit_price = $item->sale_unit_price;
                            if ($item->item_unit_types) {
                                $price_store = $item->item_unit_types->where('default_price_store', 1)->first();
                                if ($price_store) {
                                    $price_default = $price_store->price_default;
                                    $prices = [$price_store->price1, $price_store->price2, $price_store->price3];
                                    $sale_unit_price = $prices[$price_default - 1];
                                }
                            } else {
                                if ($person_type_id && !$item->clientTypePrices->isEmpty()) {
                                    $sale_unit_price = $item->clientTypePrices
                                        ->where('person_type_id', $person_type_id)
                                        ->first()->price;
                                }
                            }

                        @endphp
                        {{ number_format($sale_unit_price, 2) }}</span>
                </div>
                <div class="product-action">
                    <a href="#" class="paction add-cart" data-product="{{ json_encode($item) }}"
                        data-person-type-id="{{ json_encode($person_type_id) }}" title="Add to Cart">
                        <span>Agregar a Carrito</span>
                    </a>
                </div>
            </div>
        </div>
    </div>
@endforeach

<?php

function stock($item, $config)
{
    if ($config) {
        $stock = 0;
        foreach ($item->warehouses as $key => $value) {
            $stock += $value->stock;
        }
        return $stock > 0 ? false : true;
    }
}
?>

<style>
    /* .product-style {
        border-style: solid;
        border-width: 1px;
        border-color: "#ddd";
        margin: 10px 1px;
    } */
    .product-image-list {
        max-height: 210px;
        min-height: 210px;
    }

    .image {
        max-height: 210px;
    }

    .product-danger {
        float: right;
        color: #fff;
        background-color: #dc3545;
        border-color: #dc3545;
    }

    .productdisabled {
        pointer-events: none;
        /* opacity: 0.7; */
    }
</style>
