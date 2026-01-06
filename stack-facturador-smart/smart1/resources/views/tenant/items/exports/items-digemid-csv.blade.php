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
                        <td>Precio1</td>
                        <td>Precio2</td>



                    </tr>
                    @foreach ($records as $digemid)
                        <?php
                        
                        $cod_prod = $digemid->getCodDigemid();
                        $price = floatval($digemid->item->sale_unit_price);
                        $fraction = floatval($digemid->fracciones) * $price;
                        ?>
                        <tr>
                            <td>'{{$company_cod_digemid }}</td>
                            <td>{{ $cod_prod }}</td>
                            <td>{{number_format($fraction,2)}}</td>
                            <td>{{number_format($price,2)}}</td>
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
