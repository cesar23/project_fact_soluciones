<style>
    .vl {
        border-left: 2px solid black;
        height: 100%;
        margin-left: 30%;
    }
    
    /* Estilos para el modal de login/registro */
    #login_register_modal .modal-body {
        padding: 20px;
    }
    
    #login_register_modal .form-group {
        margin-bottom: 12px;
    }
    
    #login_register_modal .title {
        margin-bottom: 10px;
    }
    
    #login_register_modal .alert {
        margin-bottom: 12px;
        padding: 8px;
    }
    
    #login_register_modal button.btn.btn-primary {
        margin-top: 5px;
        padding: 6px;
    }
    
    #login_register_modal .col-md-5 {
        padding: 0 10px;
    }
</style>
@php
    $configuration_ecommerce = \App\Models\Tenant\ConfigurationEcommerce::first();

@endphp
<div class="footer-middle">
    <div class="container">
        <div class="footer-ribbon">
            Contáctanos
        </div><!-- End .footer-ribbon -->
        <div class="row">
            <div class="col-lg-4">
                <div class="widget">
                    <h4 class="widget-title">Ubicación</h4>
                    <ul class="contact-info">
                        <li>
                            <span class="contact-info-label">Dirección:</span>
                            {{ $information->information_contact_address }}
                        </li>
                        <li>
                            <span class="contact-info-label">Teléfono:</span> <a href="tel:">
                                {{ $information->information_contact_phone }}</a>
                        </li>
                    </ul>
                </div><!-- End .widget -->
            </div><!-- End .col-lg-3 -->


            <div class="col-md-4">
                <div class="widget">
                    <h4 class="widget-title">Enlaces de interés</h4>
                    <div class="row">
                        <div class="col-sm-6 col-md-5">
                            <ul class="links">
                                <li><a href="{{ route('tenant.ecommerce.index') }}">Inicio</a></li>
                                <li><a href="{{ route('tenant_detail_cart') }}">Ver Carrito</a></li>
                                @guest
                                    <li><a href="{{ route('tenant_ecommerce_login') }}" class="login-link">Login</a></li>
                                @else
                                    <li><a role="menuitem" href="{{ route('logout') }}" class="login-link"
                                            onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
                                            Salir
                                        </a></li>
                                    <form id="logout-form" action="{{ route('logout') }}" method="POST"
                                        style="display: none;">
                                        @csrf
                                    </form>
                                @endguest
                            </ul>
                        </div>
                        <div class="col-sm-6 col-md-5">
                            {{-- <ul class="links">
                                <li><a href="{{ route('tenant_detail_cart') }}">Ver Carrito</a></li>
                                <li><a href="#">Ver Perfil</a></li>
                                @guest
                                <li><a href="{{route('tenant_ecommerce_login')}}" class="login-link">Login</a></li>
                                @else
                                <li><a role="menuitem" href="{{ route('logout') }}" class="login-link" onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
                                    Salir
                                </a></li>
                                <form id="logout-form" action="{{ route('logout') }}" method="POST" style="display: none;">
                                    @csrf
                                </form>
                                @endguest
                            </ul> --}}
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-md-4">
                <div class="widget">
                    <h4 class="widget-title">Redes Sociales</h4>

                    <div class="social-icons">
                        @if ($information->link_facebook)
                            <a href="{{ $information->link_facebook }}" class="social-icon" target="_blank"><i
                                    class="icon-facebook"></i></a>
                        @endif

                        @if ($information->link_twitter)
                            <a href="{{ $information->link_twitter }}" class="social-icon" target="_blank"><i
                                    class="icon-twitter"></i></a>
                        @endif

                        @if ($information->link_youtube)
                            <a href="{{ $information->link_youtube }}" class="social-icon" target="_blank"><i
                                    class="fab fa-youtube"></i></a>
                        @endif

                    </div>
                </div>
            </div>

        </div>
    </div>
</div>

<div class="container">
    <div class="footer-bottom  justify-content-between">
        @if ($configuration_ecommerce->copyrigth_text)
            <p class="footer-copyright">{{ $configuration_ecommerce->copyrigth_text }}</p>
        @else
            <p class="footer-copyright">Facturador Smart. &copy; 2025. Todos los Derechos Reservados</p>
        @endif
        {{-- <p class="footer-copyright">Facturador Smart &copy; {{ now()->year }}. Todos los Derechos Reservados</p> --}}
        <div class="d-flex text-end">
            @php
                $url = 'https://enlinea.indecopi.gob.pe/guiaconsumomype/detalle-libro-reclamaciones';
                if ($configuration_ecommerce->url_complaints) {
                    $url = $configuration_ecommerce->url_complaints;
                }
            @endphp

            <a href="{{ strpos($url, 'https') === 0 ? $url : 'https://' . $url }}" target="_blank">
                @if ($configuration_ecommerce->image_complaints)
                    @php
                        $image_payment_methods =
                            'storage/uploads/ecommerce/' . $configuration_ecommerce->image_complaints;
                    @endphp
                    <img src="{{ asset($image_payment_methods) }}" alt="payment methods" class=""
                        style="max-width: 350px; max-height: 80px;">
                @else
                    <img src="{{ asset('images/fondos/image_complaints.jpg') }}" alt="complaints"
                        class="footer-payments" style="max-width: 350px; max-height: 80px;">
                @endif
            </a>

            @if ($configuration_ecommerce->image_payment_methods)
                @php
                    $image_payment_methods =
                        'storage/uploads/ecommerce/' . $configuration_ecommerce->image_payment_methods;
                @endphp
                <img src="{{ asset($image_payment_methods) }}" alt="payment methods" class="footer-payments"
                    style="max-width: 350px; max-height: 80px;">
            @else
                <img src="{{ asset('images/fondos/payments.jpeg') }}" alt="payment methods" class="footer-payments"
                    style="max-width: 350px; max-height: 80px;">
            @endif
        </div>

    </div><!-- End .footer-bottom -->
</div><!-- End .container -->

@if ($information->phone_whatsapp)
    @if (strlen($information->phone_whatsapp) > 0)
        <a class='ws-flotante' href='https://wa.me/{{ $information->phone_whatsapp }}' target="BLANK"
            style="background-image: url('{{ asset('logo/ws.png') }}'); background-size: 70px; background-repeat: no-repeat;"></a>
    @endif
@endif

<div class="modal fade" id="moda-succes-add-product" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel"
    aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <!--<div class="modal-header ">
                  <h5 class="modal-title" id="exampleModalLabel"></h5>
                  <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                      <span aria-hidden="true">&times;</span>
                  </button>
              </div>-->
            <div class="modal-body">

                <div class="alert alert-success" role="alert">
                    <i class="icon-ok"></i> Tu producto se agregó al carrito
                </div>
                <div class="row">
                    <div id="product_added_image" class="col-md-4">


                    </div>
                    <div class="col-md-8">
                        <div id="product_added" class="product-single-details">

                        </div>
                    </div>

                </div>
            </div>
            <div class="modal-footer">
                <a href="{{ route('tenant_detail_cart') }}" class="btn btn-primary text-white">Ir a Carrito</a>
                <button type="button" class="btn btn-warning" data-dismiss="modal">Seguir Comprando</button>
            </div>
        </div>
    </div>
</div>
<div class="modal fade" id="modal-already-product" tabindex="-1" role="dialog"
    aria-labelledby="exampleModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">

            <div class="modal-body">

                <div style="font-size: 2em;" class="alert alert-warning" role="alert">
                    <i class="fas fa-exclamation"></i> Tu Producto ya está agregado al carrito.
                </div>
            </div>
            <div class="modal-footer">
                <a href="{{ route('tenant_detail_cart') }}" class="btn btn-primary text-white">Ir al Carrito</a>
                <button type="button" class="btn btn-warning" data-dismiss="modal">Seguir Comprando</button>
            </div>
        </div>
    </div>
</div>



<div class="modal fade" id="login_register_modal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel"
    aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable" role="document">
        <div class="modal-content">
            <div class="modal-body">
                <div class="container-fluid">
                    <div class="row">
                        <div class="col-md-5">
                            <h4 class="title mb-2">INGRESAR A TU CUENTA</h4>

                            <div id="msg_login" class="alert alert-danger" role="alert">
                                Usuario o Contraseña Incorrectos.
                            </div>

                            <form action="#" id="form_login">
                                <div class="form-group">
                                    <label for="email">Correo Electronico:</label>
                                    <input type="email" required class="form-control" id="email"
                                        placeholder="Ingrese correo electronico" name="email">
                                </div>
                                <div class="form-group">
                                    <label for="pwd">Contraseña:</label>
                                    <input type="password" required class="form-control" id="pwd"
                                        placeholder="Ingrese contraseña" name="password">
                                </div>

                                <button type="submit" class="btn btn-primary">Ingresar</button>
                            </form>
                        </div>
                        <div class="col-md-1 text-center mt-4">
                            <div class="vl"></div>
                        </div>
                        <div class="col-md-5">
                            <h4 class="title mb-2">Nuevo Registro</h4>
                            <div id="msg_register" class="alert alert-danger" role="alert">
                                <p id="msg_register_p"></p>
                            </div>

                            <form autocomplete="off" action="#" id="form_register">
                                <div class="form-group">
                                    <label for="email">Nombres:</label>
                                    <input type="text" required autocomplete="off" class="form-control"
                                        id="name_reg" placeholder="Ingrese nombre" name="name">
                                </div>
                                <div class="form-group">
                                    <label for="email">Correo Electronico:</label>
                                    <input type="email" required autocomplete="off" class="form-control"
                                        id="email_reg" placeholder="Ingrese correo electronico" name="email">
                                </div>
                                <div class="form-group">
                                    <label for="pwd">Contraseña:</label>
                                    <input type="password" required autocomplete="off" class="form-control"
                                        id="pwd_reg" placeholder="Ingrese contraseña" name="pswd">
                                </div>
                                <div class="form-group">
                                    <label for="pwd">Repita la Contraseña:</label>
                                    <input type="password" required autocomplete="off" class="form-control"
                                        id="pwd_repeat_reg" placeholder="Repita contraseña" name="pswd_rpt">
                                </div>

                                <button type="submit" class="btn btn-primary">Registrarse</button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
    <script type="text/javascript" src="{{ asset('porto-ecommerce/assets/js/cart.js') }}"></script>
    <script type="text/javascript">
        matchPassword();
        submitLogin();
        submitRegister();


        function matchPassword() {
            var password = document.getElementById("pwd_reg"),
                confirm_password = document.getElementById("pwd_repeat_reg");

            function validatePassword() {
                if (password.value != confirm_password.value) {
                    confirm_password.setCustomValidity("El Password no coincide.");
                } else {
                    confirm_password.setCustomValidity('');
                }
            }

            password.onchange = validatePassword;
            confirm_password.onkeyup = validatePassword;
        }

        function submitLogin() {
            $('#msg_login').hide();

            $('#form_login').submit(function(e) {
                e.preventDefault()
                $.ajax({
                    type: "POST",
                    dataType: 'JSON',
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    },
                    url: "{{ route('tenant_ecommerce_login') }}",
                    data: $(this).serialize(),
                    success: function(data) {
                        if (data.success) {
                            location.reload();
                        } else {
                            $('#msg_login').show();
                        }
                    },
                    error: function(error_data) {
                        console.log(error_data)
                    }
                });
            })

        }

        function submitRegister() {
            $('#msg_register').hide();

            $('#form_register').submit(function(e) {
                e.preventDefault()
                $.ajax({
                    type: "POST",
                    dataType: 'JSON',
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    },
                    url: "{{ route('tenant_ecommerce_store_user') }}",
                    data: $(this).serialize(),
                    success: function(data) {
                        if (data.success) {
                            location.reload();
                        } else {
                            $('#msg_register').show();
                            $('#msg_register_p').text(data.message)
                        }
                    },
                    error: function(error_data) {
                        console.log(error_data)
                    }
                });
            })
        }
    </script>
@endpush
