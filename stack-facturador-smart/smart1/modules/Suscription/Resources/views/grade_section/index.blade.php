@extends('tenant.layouts.app')

@section('content')
    <div class="row">

        <div class="page-header pr-0">
            <h2><a href="/dashboard"><i class="fas fa-tachometer-alt"></i></a></h2>
            <ol class="breadcrumbs">
                @php
                    $name = 'Grados y secciones';
                    if (isset($suscriptionames)) {
                        if ($suscriptionames->grades) {
                            $name = $suscriptionames->grades;
                        }
                        if ($suscriptionames->sections) {
                            if ($suscriptionames->grades) {
                                $name = $suscriptionames->grades . ' y ' . $suscriptionames->sections;
                            } else {
                                $name = $suscriptionames->sections;
                            }
                        }
                    }
                @endphp
                <li class="active"><span>{{ $name }}</span></li>
            </ol>
        </div>

        @if (isset($suscriptionames))
            <div class="col-md-6 ui-sortable">
                <tenant-suscription-grades-index :suscriptionames="{{ $suscriptionames }}">
                </tenant-suscription-grades-index>
            </div>
            <div class="col-md-6 ui-sortable">
                <tenant-suscription-sections-index :suscriptionames="{{ $suscriptionames }}">
                </tenant-suscription-sections-index>
            </div>
        @else
            <div class="col-md-6 ui-sortable">
                <tenant-suscription-grades-index></tenant-suscription-grades-index>
            </div>
            <div class="col-md-6 ui-sortable">
                <tenant-suscription-sections-index>
                </tenant-suscription-sections-index>
            </div>
        @endif

    </div>
@endsection
