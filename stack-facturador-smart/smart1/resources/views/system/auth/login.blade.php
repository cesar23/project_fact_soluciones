@extends('system.layouts.auth')

@section('content')
<?php
use App\Models\System\Configuration;
$configuration = Configuration::first();
 
 ?>
<div id="root" class="h-100">
    <!-- Background Start -->
    <div class="fixed-background"></div>
    <!-- Background End -->
    <div class="container-fluid p-0 h-100 position-relative">
        <div class="row g-0 h-100">
            <!-- Left Side Start -->
            <div class="offset-0 col-12 d-none d-lg-flex offset-md-1 col-lg h-lg-100">
                <div class="min-h-100 d-flex align-items-center">
                    <div class="w-100 w-lg-75 w-xxl-50">

                    </div>
                </div>
            </div>
            <!-- Left Side End -->

            <!-- Right Side Start -->
            <div class="col-12 col-lg-auto h-100 pb-4 px-4 pt-0 p-lg-0">
                <div class="sw-lg-70 min-h-100 bg-foreground-login d-flex justify-content-center align-items-center  py-5 full-page-content-right-border">
                    <div class="sw-lg-50 px-5">
                        <div class="sh-11">
                          <a href="javascript:void(0)">
                            <?php
                            $logo = $configuration->logo !=null ? "storage/uploads/logos/{$configuration->logo}" : "logo/logo-blue-light.png" 
                            ?>
                             <img src="{{$logo}}" height="70px">
                        </a>
                        </div>
                        <div class="bg-card-login">

                            <div class="mb-5">
                                <h2 class="cta-1 mb-0 text-primary text-center text-white">Inicio de Sesion</h2>
                            </div>
                            <div>
                                <form id="resetForm" class="tooltip-end-bottom" method="POST" action="{{ route('login') }}">
                                    @csrf
                                    <div class="mb-3 filled">
                                        <i data-cs-icon="email"></i>
                                        <input id="email" type="email" placeholder="Correo Electronico" name="email" class="form-control" value="{{ old('email') }}">
                                        @if ($errors->has('email'))
                                        <label class="error">
                                            <strong>{{ $errors->first('email') }}</strong>
                                        </label>
                                        @endif
                                    </div>
                                    <div class="mb-3 filled">
                                        <i data-cs-icon="lock"></i>
                                        <input name="password" type="password" placeholder="Contraseña" class="form-control">
                                        @if ($errors->has('password'))
                                        <label class="error">
                                            <strong>{{ $errors->first('password') }}</strong>
                                        </label>
                                        @endif
                                    </div>
                                    <button type="submit" class="btn btn-lg btn-primary">Ingresar</button>
                                </form>
                            </div>
                        </div>

                    </div>
                </div>
            </div>
            <!-- Right Side End -->
        </div>
    </div>
</div>
<div
          class="modal fade modal-right scroll-out-negative"
          id="settings"
          data-bs-backdrop="true"
          tabindex="-1"
          role="dialog"
          aria-labelledby="settings"
          aria-hidden="true"
        >
          <div class="modal-dialog modal-dialog-scrollable full" role="document">
            <div class="modal-content">
              <div class="modal-header">
                <h5 class="modal-title">Ajustes</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
              </div>

              <div class="modal-body">
                <div class="scroll-track-visible">
                  <div class="mb-5" id="color">
                    <label class="mb-3 d-inline-block form-label">Color</label>
                    <div class="row d-flex g-3 justify-content-between flex-wrap mb-3">
                      <a href="#" class="flex-grow-1 w-50 option col" data-value="light-blue" data-parent="color">
                        <div class="card rounded-md p-3 mb-1 no-shadow color">
                          <div class="blue-light"></div>
                        </div>
                        <div class="text-muted text-part">
                          <span class="text-extra-small align-middle">AZUL CLARO</span>
                        </div>
                      </a>
                      <a href="#" class="flex-grow-1 w-50 option col" data-value="dark-blue" data-parent="color">
                        <div class="card rounded-md p-3 mb-1 no-shadow color">
                          <div class="blue-dark"></div>
                        </div>
                        <div class="text-muted text-part">
                          <span class="text-extra-small align-middle">AZUL OSCURO</span>
                        </div>
                      </a>
                    </div>

                    <div class="row d-flex g-3 justify-content-between flex-wrap mb-3">
                      <a href="#" class="flex-grow-1 w-50 option col" data-value="light-red" data-parent="color">
                        <div class="card rounded-md p-3 mb-1 no-shadow color">
                          <div class="red-light"></div>
                        </div>
                        <div class="text-muted text-part">
                          <span class="text-extra-small align-middle">ROJO CLARO</span>
                        </div>
                      </a>
                      <a href="#" class="flex-grow-1 w-50 option col" data-value="dark-red" data-parent="color">
                        <div class="card rounded-md p-3 mb-1 no-shadow color">
                          <div class="red-dark"></div>
                        </div>
                        <div class="text-muted text-part">
                          <span class="text-extra-small align-middle">ROJO OSCURO</span>
                        </div>
                      </a>
                    </div>

                    <div class="row d-flex g-3 justify-content-between flex-wrap mb-3">
                      <a href="#" class="flex-grow-1 w-50 option col" data-value="light-green" data-parent="color">
                        <div class="card rounded-md p-3 mb-1 no-shadow color">
                          <div class="green-light"></div>
                        </div>
                        <div class="text-muted text-part">
                          <span class="text-extra-small align-middle">VERDE CLARO</span>
                        </div>
                      </a>
                      <a href="#" class="flex-grow-1 w-50 option col" data-value="dark-green" data-parent="color">
                        <div class="card rounded-md p-3 mb-1 no-shadow color">
                          <div class="green-dark"></div>
                        </div>
                        <div class="text-muted text-part">
                          <span class="text-extra-small align-middle">VERDE OSCURO</span>
                        </div>
                      </a>
                    </div>

                    <div class="row d-flex g-3 justify-content-between flex-wrap mb-3">
                      <a href="#" class="flex-grow-1 w-50 option col" data-value="light-purple" data-parent="color">
                        <div class="card rounded-md p-3 mb-1 no-shadow color">
                          <div class="purple-light"></div>
                        </div>
                        <div class="text-muted text-part">
                          <span class="text-extra-small align-middle">MORADO CLARO</span>
                        </div>
                      </a>
                      <a href="#" class="flex-grow-1 w-50 option col" data-value="dark-purple" data-parent="color">
                        <div class="card rounded-md p-3 mb-1 no-shadow color">
                          <div class="purple-dark"></div>
                        </div>
                        <div class="text-muted text-part">
                          <span class="text-extra-small align-middle">MORADO OSCURO</span>
                        </div>
                      </a>
                    </div>

                    <div class="row d-flex g-3 justify-content-between flex-wrap mb-3">
                      <a href="#" class="flex-grow-1 w-50 option col" data-value="light-pink" data-parent="color">
                        <div class="card rounded-md p-3 mb-1 no-shadow color">
                          <div class="pink-light"></div>
                        </div>
                        <div class="text-muted text-part">
                          <span class="text-extra-small align-middle">ROSADO CLARO</span>
                        </div>
                      </a>
                      <a href="#" class="flex-grow-1 w-50 option col" data-value="dark-pink" data-parent="color">
                        <div class="card rounded-md p-3 mb-1 no-shadow color">
                          <div class="pink-dark"></div>
                        </div>
                        <div class="text-muted text-part">
                          <span class="text-extra-small align-middle">ROSADO OSCURO</span>
                        </div>
                      </a>
                    </div>
                  </div>

                  <div class="mb-5" id="navcolor">
                    <label class="mb-3 d-inline-block form-label">NAVEGACIÓN</label>
                    <div class="row d-flex g-3 justify-content-between flex-wrap">
                      <a href="#" class="flex-grow-1 w-33 option col" data-value="default" data-parent="navcolor">
                        <div class="card rounded-md p-3 mb-1 no-shadow">
                          <div class="figure figure-primary top"></div>
                          <div class="figure figure-secondary bottom"></div>
                        </div>
                        <div class="text-muted text-part">
                          <span class="text-extra-small align-middle">POR DEFECTO</span>
                        </div>
                      </a>
                      <a href="#" class="flex-grow-1 w-33 option col" data-value="light" data-parent="navcolor">
                        <div class="card rounded-md p-3 mb-1 no-shadow">
                          <div class="figure figure-secondary figure-light top"></div>
                          <div class="figure figure-secondary bottom"></div>
                        </div>
                        <div class="text-muted text-part">
                          <span class="text-extra-small align-middle">CLARO</span>
                        </div>
                      </a>
                      <a href="#" class="flex-grow-1 w-33 option col" data-value="dark" data-parent="navcolor">
                        <div class="card rounded-md p-3 mb-1 no-shadow">
                          <div class="figure figure-muted figure-dark top"></div>
                          <div class="figure figure-secondary bottom"></div>
                        </div>
                        <div class="text-muted text-part">
                          <span class="text-extra-small align-middle">OSCURO</span>
                        </div>
                      </a>
                    </div>
                  </div>

                  <div class="mb-5" id="placement">
                    <label class="mb-3 d-inline-block form-label">UBICACIÓN DE MENU  </label>
                    <div class="row d-flex g-3 justify-content-between flex-wrap">
                      <a href="#" class="flex-grow-1 w-50 option col" data-value="horizontal" data-parent="placement">
                        <div class="card rounded-md p-3 mb-1 no-shadow">
                          <div class="figure figure-primary top"></div>
                          <div class="figure figure-secondary bottom"></div>
                        </div>
                        <div class="text-muted text-part">
                          <span class="text-extra-small align-middle">HORIZONTAL</span>
                        </div>
                      </a>
                      <a href="#" class="flex-grow-1 w-50 option col" data-value="vertical" data-parent="placement">
                        <div class="card rounded-md p-3 mb-1 no-shadow">
                          <div class="figure figure-primary left"></div>
                          <div class="figure figure-secondary right"></div>
                        </div>
                        <div class="text-muted text-part">
                          <span class="text-extra-small align-middle">VERTICAL</span>
                        </div>
                      </a>
                    </div>
                  </div>

                  <div class="mb-5" id="behaviour">
                    <label class="mb-3 d-inline-block form-label">Comportamiento de Menu</label>
                    <div class="row d-flex g-3 justify-content-between flex-wrap">
                      <a href="#" class="flex-grow-1 w-50 option col" data-value="pinned" data-parent="behaviour">
                        <div class="card rounded-md p-3 mb-1 no-shadow">
                          <div class="figure figure-primary left large"></div>
                          <div class="figure figure-secondary right small"></div>
                        </div>
                        <div class="text-muted text-part">
                          <span class="text-extra-small align-middle">EXTENDIDO</span>
                        </div>
                      </a>
                      <a href="#" class="flex-grow-1 w-50 option col" data-value="unpinned" data-parent="behaviour">
                        <div class="card rounded-md p-3 mb-1 no-shadow">
                          <div class="figure figure-primary left"></div>
                          <div class="figure figure-secondary right"></div>
                        </div>
                        <div class="text-muted text-part">
                          <span class="text-extra-small align-middle">CONTRA</span>
                        </div>
                      </a>
                    </div>
                  </div>

                  <div class="mb-5" id="layout">
                    <label class="mb-3 d-inline-block form-label">Disposición</label>
                    <div class="row d-flex g-3 justify-content-between flex-wrap">
                      <a href="#" class="flex-grow-1 w-50 option col" data-value="fluid" data-parent="layout">
                        <div class="card rounded-md p-3 mb-1 no-shadow">
                          <div class="figure figure-primary top"></div>
                          <div class="figure figure-secondary bottom"></div>
                        </div>
                        <div class="text-muted text-part">
                          <span class="text-extra-small align-middle">Completo</span>
                        </div>
                      </a>
                      <a href="#" class="flex-grow-1 w-50 option col" data-value="boxed" data-parent="layout">
                        <div class="card rounded-md p-3 mb-1 no-shadow">
                          <div class="figure figure-primary top"></div>
                          <div class="figure figure-secondary bottom small"></div>
                        </div>
                        <div class="text-muted text-part">
                          <span class="text-extra-small align-middle">En caja</span>
                        </div>
                      </a>
                    </div>
                  </div>

                  <div class="mb-5" id="radius">
                    <label class="mb-3 d-inline-block form-label">Bordes</label>
                    <div class="row d-flex g-3 justify-content-between flex-wrap">
                      <a href="#" class="flex-grow-1 w-33 option col" data-value="rounded" data-parent="radius">
                        <div class="card rounded-md radius-rounded p-3 mb-1 no-shadow">
                          <div class="figure figure-primary top"></div>
                          <div class="figure figure-secondary bottom"></div>
                        </div>
                        <div class="text-muted text-part">
                          <span class="text-extra-small align-middle">Redondeado</span>
                        </div>
                      </a>
                      <a href="#" class="flex-grow-1 w-33 option col" data-value="standard" data-parent="radius">
                        <div class="card rounded-md radius-regular p-3 mb-1 no-shadow">
                          <div class="figure figure-primary top"></div>
                          <div class="figure figure-secondary bottom"></div>
                        </div>
                        <div class="text-muted text-part">
                          <span class="text-extra-small align-middle">Estándar</span>
                        </div>
                      </a>
                      <a href="#" class="flex-grow-1 w-33 option col" data-value="flat" data-parent="radius">
                        <div class="card rounded-md radius-flat p-3 mb-1 no-shadow">
                          <div class="figure figure-primary top"></div>
                          <div class="figure figure-secondary bottom"></div>
                        </div>
                        <div class="text-muted text-part">
                          <span class="text-extra-small align-middle">Recto</span>
                        </div>
                      </a>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>
      
        </div>
        <div class="position-absolute bottom-0 end-0 p-3">
          @php
              $commit = trim(shell_exec('git log -1 --format="%H"'));
              $commitDate = trim(shell_exec('git log -1 --format="%ci"'));
              $daysDiff = \Carbon\Carbon::parse($commitDate)->diffInDays(\Carbon\Carbon::now());
              
          @endphp
            @if($daysDiff > 30)
            <p class="text-danger  mb-0" style="font-size: 12px; font-weight: bolder;">Actualice su sistema</p>
        @endif
          <p class="text-muted small mb-0">{{ substr($commit, 0, 7) }}</p>
          <p class="text-muted small mb-0">{{ \Carbon\Carbon::parse($commitDate)->format('d/m/Y H:i:s') }}</p>
        
      </div>
@endsection