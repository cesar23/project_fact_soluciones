<div class="product-single-container product-single-default product-quick-view container">
    <div class="row">
        <div class="col-lg-6 col-md-6 product-single-gallery">
            <div class="product-slider-container product-item">
                <div class="product-single-carousel owl-carousel owl-theme">
                    <div class="product-item">
                        <img class="product-single-image" src="{{ asset('storage/uploads/items/' . $record->image) }}"
                            data-zoom-image="{{ asset('storage/uploads/items/' . $record->image) }}" />
                    </div>

                    @foreach ($record->images as $row)
                        <div class="product-item">
                            <img class="product-single-image" src="{{ asset('storage/uploads/items/' . $row->image) }}"
                                data-zoom-image="{{ asset('storage/uploads/items/' . $row->image) }}" />
                        </div>
                    @endforeach

                    <!--<div class="product-item">
                        <img class="product-single-image"
                            src="{{ asset('storage/uploads/items/' . $record->image_medium) }}"
                            data-zoom-image="{{ asset('storage/uploads/items/' . $record->image_medium) }}" />
                    </div> -->

                </div>

            </div>
            <div class="prod-thumbnail row owl-dots" id='carousel-custom-dots'>
                <div class="col-3 owl-dot">
                    <img src="{{ asset('storage/uploads/items/' . $record->image) }}" />
                </div>

                @foreach ($record->images as $row)
                    <div class="col-3 owl-dot">
                        <img src="{{ asset('storage/uploads/items/' . $row->image) }}" />
                    </div>
                @endforeach

                <!--<div class="col-3 owl-dot">
                    <img src="{{ asset('porto_ecommerce/ajax/assets/images/products/zoom/product-2.html') }}" />
                </div>
                <div class="col-3 owl-dot">
                    <img src="{{ asset('porto_ecommerce/ajax/assets/images/products/zoom/product-3.html') }}" />
                </div>
                <div class="col-3 owl-dot">
                    <img src="{{ asset('porto_ecommerce/ajax/assets/images/products/zoom/product-4.html') }}" />
                </div>-->
            </div>
        </div><!-- End .col-lg-7 -->

        <div class="col-lg-6 col-md-6">
            <div class="product-single-details">
                <h1 class="product-title">{{ $record->description }}</h1>

                <div class="ratings-container">
                    <div class="product-ratings">
                        <span class="ratings" style="width:60%"></span><!-- End .ratings -->
                    </div><!-- End .product-ratings -->

                    <a href="#" class="rating-link">( 6 Reviews )</a>
                </div><!-- End .product-container -->

                <div class="price-box">
                    <span class="old-price">{{ $record->currency_type['symbol'] }}
                        @php

                            $sale_unit_price = $record->sale_unit_price;
                            if($record->item_unit_types){
                                $price_store = $record->item_unit_types->where('default_price_store', 1)->first();
                                if($price_store){
                                    $price_default = $price_store->price_default;
                                    $prices = [$price_store->price1, $price_store->price2, $price_store->price3];
                                    $sale_unit_price = $prices[$price_default - 1];
                                }
                            }
                            if($person_type_id && !$record->clientTypePrices->isEmpty()){
                                $sale_unit_price = $record->clientTypePrices->where('person_type_id', $person_type_id)->first()->price;
                            }
                        @endphp
                        {{ number_format($sale_unit_price * 1.2, 2) }}</span>
                    <span class="product-price">{{ $record->currency_type['symbol'] }}
                        {{ number_format($sale_unit_price, 2) }}</span>
                </div><!-- End .price-box -->

                <div class="product-desc">
                    <p>{{ $record->name }}</p>
                </div><!-- End .product-desc -->

                <div class="product-action">
                    <div class="quantity-control mb-2">
                        {{-- <span class="text-muted">Inclusivo de todos los impuestos.</span> --}}
                        <div class="quantity-field">
                            <button class="btn-minus" type="button" onclick="decrementQuantity()">
                                <i class="fas fa-minus"></i>
                            </button>
                            <input type="text" class="quantity-input" id="quantity" value="1" min="1"
                                readonly>
                            <button class="btn-plus" type="button" onclick="incrementQuantity()">
                                <i class="fas fa-plus"></i>
                            </button>
                        </div>
                    </div>

                    <a href="#" onclick="cart_add('{{ json_encode($record) }}', $('#quantity').val(), {{ json_encode($person_type_id) }})"
                        class="paction add-cart" title="Add to Cart">
                        <span>Agregar a Carrito</span>
                    </a>

                    <div class="social-action-buttons mt-3">
                        @if ($record->video_url)
                            <a href="{{ $record->video_url }}" target="_blank" class="action-btn video-btn"
                                title="Ver Video">
                                <i class="fas fa-play"></i>
                            </a>
                        @endif

                        @if ($configuration->information_contact_phone)
                            @php
                                $phone = str_replace(' ', '', $configuration->information_contact_phone);
                            @endphp
                            <a href="tel:+{{ $phone }}" class="action-btn call-btn" title="Llamar">
                                <i class="fas fa-phone"></i>
                            </a>
                        @endif

                        @if ($configuration->phone_whatsapp)
                            @php
                                $whatsapp = str_replace(' ', '', $configuration->phone_whatsapp);
                            @endphp
                            <a href="https://wa.me/+51{{ $whatsapp }}" target="_blank"
                                class="action-btn whatsapp-btn" title="WhatsApp">
                                <i class="fab fa-whatsapp"></i>
                            </a>
                        @endif
                    </div>

                </div><!-- End .product-action -->

            </div><!-- End .product-single-details -->
        </div><!-- End .col-lg-5 -->
    </div><!-- End .row -->
</div><!-- End .product-single-container -->
