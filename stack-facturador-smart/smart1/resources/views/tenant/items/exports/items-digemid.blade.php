<!DOCTYPE html>
<html lang="es">

<head>
</head>

<body>
    @if (!empty($records))
        <div class="">
            <div class=" ">
                <table class="table" width="100%">
                    <tr>
                        <td>CodEstab</td>
                        <td>CodProd</td>
                        <td>NomProd</td>
                        <td>Concent</td>
                        <td>NomFormFarm</td>
                        <td>NomFormFarmSimplif</td>
                        <td>Presentac</td>
                        <td>Fracciones</td>
                        <td>FecVctoRegSanitario</td>
                        <td>NumRegSan</td>
                        <td>NomTitular</td>

                        @for ($i = 0; $i < $max_price; $i++)
                            <td>Precio {{ $i + 1 }}</td>
                        @endfor
                    </tr>
                    @foreach ($records as $digemid)
                        <?php
                        /** @var \Modules\Digemid\Models\CatDigemid $digemid */
                        $cod_prod = $digemid->getCodDigemid();
                        $nom_prod = $digemid->nom_prod;
                        $precios = $digemid->getArrayPrices();
                        $concent = $digemid->concent;
                        $nom_form_farm = $digemid->nom_form_farm;
                        $nom_form_farm_simplif = $digemid->nom_form_farm_simplif;
                        $presentac = $digemid->presentac;
                        $fracciones = $digemid->fracciones;
                        $fec_vcto_reg_sanitario = $digemid->fec_vcto_reg_sanitario;
                        $num_reg_san = $digemid->num_reg_san;
                        $nom_titular = $digemid->nom_titular;
                        ?>
                        <tr>
                            <td>{{ $company_cod_digemid }}</td>
                            <td>{{ $cod_prod }}</td>
                            <td>{{ $nom_prod }}</td>
                            <td>{{$concent}}</td>
                            <td>{{$nom_form_farm}}</td>
                            <td>{{$nom_form_farm_simplif}}</td>
                            <td>{{$presentac}}</td>
                            <td>{{$fracciones}}</td>
                            <td>{{$fec_vcto_reg_sanitario}}</td>
                            <td>{{$num_reg_san}}</td>
                            <td>{{$nom_titular}}</td>
                            @for ($i = 0; $i < $max_price; $i++)
                                <td>{{ isset($precios[$i]) ? $precios[$i] : '0,00' }}</td>
                            @endfor

                        </tr>
                    @endforeach
                </table>
            </div>
        </div>
    @else
        <div>
            <p>No se encontraron registros.</p>
        </div>
    @endif
</body>

</html>
