<div class="">
    <div class=" ">
        <table class="full-width">
            <?php

            use App\Models\Tenant\Company;
            use App\Models\Tenant\Configuration;
            use App\Models\Tenant\User;
            use Modules\Inventory\Models\Inventory;
            use Modules\Inventory\Models\InventoryTransfer;
            use Modules\Inventory\Models\Warehouse;
            use Illuminate\Support\Carbon;
            use Illuminate\Database\Eloquent\Collection;

            $motivo = !empty($data['motivo']) ? $data['motivo'] : null;
            /** @var Carbon $created_at */
            /** @var Warehouse $warehouse_to */
            /** @var Warehouse $warehouse_from */
            /** @var User $user */
            /** @var Collection|Inventory[] $inventories */

            $created_at = !empty($data['created_at']) ? $data['created_at'] : Carbon::now();
            $quantity = !empty($data['quantity']) ? $data['quantity'] : 0;

            $warehouse_from = !empty($data['warehouse_from']) ? $data['warehouse_from'] : new Warehouse();
            $warehouse_to = !empty($data['warehouse_to']) ? $data['warehouse_to'] : new Warehouse();
            $user = !empty($data['user']) ? $data['user'] : new User();
            $configuration = !empty($data['configuration']) ? $data['configuration'] : new Configuration();
            $company = !empty($data['company']) ? $data['company'] : new Company();
            
            $pdf = $pdf ?? false;


            ?>


            <thead>
            <tr>
                <th class="five-width text-center">ITEM</th>
                <!-- Correción <th class="ten-width text-left">CODIGO INTERNO</th> -->
                <th class="five-width text-left">CODIGO INTERNO</th>
                <!-- Correción <th class="fourteen-width text-left">DESCRIPCIÓN PRODUCTO</th> -->
                <th class="threeten-width text-left">DESCRIPCIÓN PRODUCTO</th>
                <th class="ten-width">UNIDAD</th>
                <th class="ten-width">CANTIDAD</th>
                <th class="ten-width">LOTE/SERIE</th>
                <!--        <th width="10%">SERIE</th>-->
            </tr>
            </thead>
            <tbody>
            @foreach ($inventories as $index => $inventory)
                <?php
                /** @var \Modules\Inventory\Models\Inventory $inventory */
                $item = $inventory->item;
                $itemCollection = $item->getCollectionData($configuration);

                $itemCollection['description'] = substr($itemCollection['description'], 0, 49);
                // $itemCollection['internal_id'] = substr($itemCollection['internal_id'], 0, 10);
                $itemCollection['unit_type_text'] = substr($itemCollection['unit_type_text'], 0, 10);

                $item_transfers = !empty($data['item_transfers']) ? $data['item_transfers'] : null;
                $lots = $item_transfers->filter(function($value) use ($item) {
                    return $value['item_id'] == $item->id;
                });

                $qty = $inventory->quantity;
                $lot_code = $inventory->lot_code;
                /*
                @todo BUSCAR DONDE SE GUARDA LA SERIE en modules/Inventory/Http/Controllers/TransferController.php 237
                */
                ?>
                <tr>
                    
                    <td class="celda text-center" style="font-size: 9px !important">{{$index + 1}}</td>
                    <td class="celda text-left" style="font-size: 9px !important;max-width: 120px;word-wrap: break-word; word-break: break-all; white-space: normal;">{{$itemCollection['internal_id']}}</td>
                    <td class="celda text-left" style="font-size: 9px !important">{{$itemCollection['description']}}</td>
                    <td class="celda" style="font-size: 9px !important">{{$itemCollection['unit_type_text']}}</td>
                    <td class="celda" style="font-size: 9px !important">{{$qty}}</td>
                    <td class="celda" style="font-size: 9px !important">
                        @foreach($lots as $lot)
                            {{ $lot['code'] }}
                            @if(!$loop->last)
                                <br>
                            @endif
                        @endforeach
                    </td>
                    <!--            <td>SERIE</td>-->
                </tr>
            @endforeach
        

            </tbody>
        </table>
        <table style="width: 100%; border: none; margin-top: 20px;">
            <tr>
                <td colspan="6" style="border: none; padding-top: 30px;">
                    <table style="width: 100%; border: none;">
                        <tr>
                            <td style="text-align: center; border: none; padding: 10px;">
                                <div style="border-bottom: 1px solid black; width: 150px; margin: 0 auto; margin-bottom: 5px;"></div>
                                <strong>Autorizado por</strong>
                            </td>
                            <td style="text-align: center; border: none; padding: 10px;">
                                <div style="border-bottom: 1px solid black; width: 150px; margin: 0 auto; margin-bottom: 5px;"></div>
                                <strong>Fecha</strong>
                            </td>
                            <td style="text-align: center; border: none; padding: 10px;">
                                <div style="border-bottom: 1px solid black; width: 150px; margin: 0 auto; margin-bottom: 5px;"></div>
                                <strong>Recibido por</strong>
                            </td>
                        </tr>
                    </table>
                </td>
            </tr>
        </table>
    </div>
</div>

