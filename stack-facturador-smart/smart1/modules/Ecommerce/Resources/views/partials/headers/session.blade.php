<style>
    .btn-logout {
        font-size: 25px;
        margin-left: 6%;
    }
</style>

<div class="dropdown cart-dropdown" style="margin-left: 9px;">

    @guest
        <div class="header-contact d-flex align-items-center w-100 justify-content-center">
            <a class="login-link" href="{{ route('tenant_ecommerce_login') }}"
            style="margin-top: 5px;"
            ><strong
                    style="font-size: 16px;">LOGIN</strong></a>
        </div>
    @else
        <a href="#" class="dropdown-toggle" role="button" data-toggle="dropdown" aria-haspopup="true"
            aria-expanded="false" data-display="static">
            <i class="icon-user fa-2x text-white"></i> </a>
        <div class="dropdown-menu">
            <div class="dropdownmenu-wrapper">
                @php
                    $user = Auth::user();
                @endphp
                <div class="dropdown-cart-total d-flex flex-column align-items-center">
                    <div class="d-flex align-items-center">
                        <span>{{ $user->email }} </span>
                        <a href="#" role="menuitem" class="btn-logout" data-toggle="tooltip" data-placement="bottom"
                            title="Cerrar Session" onclick="event.preventDefault(); logout();">
                            <i class="fas fa-power-off"></i>
                        </a>
                    </div>
                    <div class="d-flex align-items-center">
                        <span style="text-transform: none; white-space: nowrap;">App móvil</span>
                        <a href="/users/download-qr-virtual-store/{{ $user->id }}" target="_blank" role="menuitem"
                            class="btn-logout"
                            data-toggle="tooltip" data-placement="bottom" title="Descargar la app móvil">
                            <i class="fas fa-mobile-alt"></i>
                        </a>
                    </div>

                </div>

            </div>
        </div>

    @endguest

</div>
