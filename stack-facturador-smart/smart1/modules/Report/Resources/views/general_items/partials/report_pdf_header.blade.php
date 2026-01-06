<?php
$document_type_id = $document_type_id ?? null;
$type = $type ?? 'sale';
$plus = $plus ?? 4;
$multi_companies = $configuration->multi_companies;
?>

<head>
    <style>
        .break-word {
            word-wrap: break-word;
        }
    </style>
</head>
<th>FECHA DE EMISIÓN</th>
@if ($multi_companies)
    <th>EMPRESA</th>
@endif
<th class="break-word">USUARIO/VENDEDOR</th>
@if ($document_type_id != '80' && $type == 'sale')
    <th class="break-word">DIST</th>
    <th class="break-word">DPTO</th>
    <th class="break-word">PROV</th>
@endif
<th class="break-word">SERIE</th>
<th class="break-word">NÚMERO</th>
@if ($type == 'sale')
    <th class="break-word">ORDEN DE COMPRA</th>
    <th class="break-word">PLATAFORMA</th>
@endif
<th class="break-word">DOC ENTIDAD TIPO DNI RUC</th>
<th class="break-word">DOC ENTIDAD NÚMERO</th>
<th class="break-word">DENOMINACIÓN ENTIDAD</th>
<th class="break-word">ZONA</th>
<th class="break-word">MONEDA</th>
<th class="break-word">UNIDAD DE MEDIDA</th>
<th class="break-word">MARCA</th>
<th class="break-word">DESCRIPCIÓN</th>
{{-- @if ($type == 'sale')
    se comenta porque genera inconsistencia en columnas
    <th style="width:{{$plus+1}}%;">MODELO</th>
@endif --}}
<th class="break-word">CATEGORÍA</th>
<th class="break-word">CANTIDAD</th>
<th class="break-word">PRECIO UNITARIO</th>

@if ($type == 'purchase')
    <th class="break-word"> TIPO DE ISC</th>
    <th class="break-word"> ISC</th>
@endif

<th class="break-word"> TOTAL</th>
    @if ($type == 'sale')
<th class="break-word">TOTAL COMPRA</th>
<th class="break-word">GANANCIA</th>
@endif
