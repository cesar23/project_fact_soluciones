<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

class InserInfoUnitMeasure extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        DB::table("unit_measure")->delete();
        $data = [
            ['id' => 1, 'code' => 'ZZ', 'description' => 'Servicio', 'symbol' => 'SERV'],
            ['id' => 2, 'code' => 'BX', 'description' => 'Caja', 'symbol' => 'CAJ'],
            ['id' => 3, 'code' => 'GLL', 'description' => 'Galon', 'symbol' => 'GL'],
            ['id' => 4, 'code' => 'GRM', 'description' => 'Gramos', 'symbol' => 'GR'],
            ['id' => 5, 'code' => 'KGM', 'description' => 'Kilos', 'symbol' => 'KL'],
            ['id' => 6, 'code' => 'LTR', 'description' => 'Litro', 'symbol' => 'LT'],
            ['id' => 7, 'code' => 'MTR', 'description' => 'Metro', 'symbol' => 'M'],
            ['id' => 8, 'code' => 'FOT', 'description' => 'Pies', 'symbol' => 'PIE'],
            ['id' => 9, 'code' => 'INH', 'description' => 'Pulgadas', 'symbol' => 'INCH'],
            ['id' => 10, 'code' => 'NIU', 'description' => 'Unidad', 'symbol' => 'UND'],
            ['id' => 11, 'code' => 'YRD', 'description' => 'Yarda', 'symbol' => 'YD'],
            ['id' => 12, 'code' => 'HUR', 'description' => 'Hora', 'symbol' => 'HR'],
            ['id' => 13, 'code' => 'TNE', 'description' => 'Toneladas', 'symbol' => 'TNL'],
            ['id' => 14, 'code' => 'DZN', 'description' => 'Docena', 'symbol' => 'DOC'],
            ['id' => 15, 'code' => 'QD', 'description' => 'Cuarto de docena', 'symbol' => '1/4 DOC'],
            ['id' => 16, 'code' => 'PK', 'description' => 'Paquete', 'symbol' => 'PQT'],
            ['id' => 17, 'code' => 'MTQ', 'description' => 'Metro cúbico', 'symbol' => 'M3'],
            ['id' => 18, 'code' => 'HD', 'description' => 'Media docena', 'symbol' => '1/2 DOC'],
            ['id' => 19, 'code' => 'PR', 'description' => 'Par', 'symbol' => 'PAR'],
            ['id' => 20, 'code' => 'JG', 'description' => 'Jarra', 'symbol' => 'JARR'],
            ['id' => 21, 'code' => 'JR', 'description' => 'Frasco', 'symbol' => 'FCO'],
            ['id' => 22, 'code' => 'KT', 'description' => 'Kit', 'symbol' => 'KIT'],
            ['id' => 23, 'code' => 'CH', 'description' => 'Envase', 'symbol' => 'ENV'],
            ['id' => 24, 'code' => 'AV', 'description' => 'Capsula', 'symbol' => 'CAPS'],
            ['id' => 25, 'code' => 'CT', 'description' => 'Carton', 'symbol' => 'CTON'],
            ['id' => 26, 'code' => 'CY', 'description' => 'Cilindro', 'symbol' => 'CIL'],
            ['id' => 27, 'code' => 'BE', 'description' => 'Fardo', 'symbol' => 'FARD'],
            ['id' => 28, 'code' => 'BG', 'description' => 'Bolsa', 'symbol' => 'BOLS'],
            ['id' => 29, 'code' => 'BJ', 'description' => 'Balde', 'symbol' => 'BALD'],
            ['id' => 30, 'code' => 'SET', 'description' => 'Juego', 'symbol' => 'JGO'],
            ['id' => 31, 'code' => 'BLL', 'description' => 'Barril', 'symbol' => 'BRL'],
            ['id' => 32, 'code' => 'RM', 'description' => 'Resma', 'symbol' => 'RESM'],
            ['id' => 33, 'code' => 'BO', 'description' => 'Botellas', 'symbol' => 'BOT'],
            ['id' => 34, 'code' => 'SA', 'description' => 'Saco', 'symbol' => 'SCO'],
            ['id' => 35, 'code' => 'BT', 'description' => 'Tornillo', 'symbol' => 'TORN'],
            ['id' => 36, 'code' => 'C62', 'description' => 'Piezas', 'symbol' => 'PZ'],
            ['id' => 37, 'code' => 'U2', 'description' => 'Tableta o blister', 'symbol' => 'BLIST'],
            ['id' => 38, 'code' => 'CA', 'description' => 'Latas', 'symbol' => 'LT'],
            ['id' => 39, 'code' => 'CEN', 'description' => 'Centenar o ciento', 'symbol' => 'CTO'],
            ['id' => 40, 'code' => 'CMT', 'description' => 'Centimetro', 'symbol' => 'CM'],
            ['id' => 41, 'code' => 'CMK', 'description' => 'Centimetro cuadrado', 'symbol' => 'CM3'],
            ['id' => 42, 'code' => 'CMQ', 'description' => 'Centimetro cubico', 'symbol' => 'CM2'],
            ['id' => 43, 'code' => 'DZP', 'description' => 'Docena de paquetes', 'symbol' => 'DOC2'],
            ['id' => 44, 'code' => 'FTK', 'description' => 'Pies cuadrados', 'symbol' => 'PIE2'],
            ['id' => 45, 'code' => 'FTQ', 'description' => 'Pies cubicos', 'symbol' => 'PIE3'],
            ['id' => 46, 'code' => 'GLI', 'description' => 'Galon ingles', 'symbol' => 'GL'],
            ['id' => 47, 'code' => 'HT', 'description' => 'Media hora', 'symbol' => '1/2 H'],
            ['id' => 48, 'code' => 'KTM', 'description' => 'Kilometro', 'symbol' => 'KM'],
            ['id' => 49, 'code' => 'KWH', 'description' => 'Kilovatio hora', 'symbol' => 'KWxH'],
            ['id' => 50, 'code' => 'MWH', 'description' => 'Megavatio hora', 'symbol' => 'MWxH'],
            ['id' => 51, 'code' => 'LBR', 'description' => 'Libras', 'symbol' => 'LB'],
            ['id' => 52, 'code' => 'LEF', 'description' => 'Hoja', 'symbol' => 'HOJA'],
            ['id' => 53, 'code' => 'MGM', 'description' => 'Miligramos', 'symbol' => 'MG'],
            ['id' => 54, 'code' => 'MIL', 'description' => 'Millar', 'symbol' => 'MIL'],
            ['id' => 55, 'code' => 'MLT', 'description' => 'Mililitro', 'symbol' => 'ML'],
            ['id' => 56, 'code' => 'MMT', 'description' => 'Milimetro', 'symbol' => 'ML'],
            ['id' => 57, 'code' => 'MMK', 'description' => 'Milimetro cuadrado', 'symbol' => 'ML2'],
            ['id' => 58, 'code' => 'MMQ', 'description' => 'Milimetro cubico', 'symbol' => 'ML3'],
            ['id' => 59, 'code' => 'MTK', 'description' => 'Metro cuadrado', 'symbol' => 'M2'],
            ['id' => 60, 'code' => 'ONZ', 'description' => 'Onzas', 'symbol' => 'ONZ'],
            ['id' => 61, 'code' => 'PF', 'description' => 'Paletas', 'symbol' => 'PAL'],
            ['id' => 62, 'code' => 'PG', 'description' => 'Placas', 'symbol' => 'PLAC'],
            ['id' => 63, 'code' => 'RD', 'description' => 'Varilla', 'symbol' => 'VAR'],
            ['id' => 64, 'code' => 'RL', 'description' => 'Carrete', 'symbol' => 'CRR'],
            ['id' => 65, 'code' => 'SEC', 'description' => 'Segundo', 'symbol' => 'SEG'],
            ['id' => 66, 'code' => 'ST', 'description' => 'Pliego', 'symbol' => 'PLGO'],
            ['id' => 67, 'code' => 'TU', 'description' => 'Tubos', 'symbol' => 'TB'],
            ['id' => 68, 'code' => 'UM', 'description' => 'Millon', 'symbol' => 'MILL'],
        ];

        // Imprimir el array para verificar la estructura

        DB::table("unit_measure")->insert(
            $data
        );
    }



    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
 
    }
}
