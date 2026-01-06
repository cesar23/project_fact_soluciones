<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddCashIdSaleNoteDocuments extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // Schema::table('payment_method_types', function (Blueprint $table) {
        //     $table->boolean('is_digital')->default(false);
        //     $table->boolean('is_bank')->default(false);

        // });
        //categories
        Schema::table('documents', function (Blueprint $table) {
            $table->unsignedInteger('cash_id')->nullable();
            // $table->foreign('cash_id')->references('id')->on('cash');
        });

        Schema::table('sale_notes', function (Blueprint $table) {
            $table->unsignedInteger('cash_id')->nullable();
            // $table->foreign('cash_id')->references('id')->on('cash');
        });
        $cash = [
            'efectivo',
            'contado contraentrega',
            'contado',
        ];
        $bank = [
            'tarjeta de crédito',
            'tarjeta de débito',
            'transferencia',
            'tarjeta crédito visa',
        ];

        $credit = [
            "factura a 15 días",
            "factura a 45 días",
            "factura a 60 días",
            'factura a 30 días',
            'a 30 días',
            'crédito',
            'crédito 15 días',
        ];
        $digital = [
            'yape',
            'plin',
        ];



        $payment_method_types = \App\Models\Tenant\PaymentMethodType::all();
        foreach ($payment_method_types as $payment_method_type) {
            $description = mb_strtolower($payment_method_type->description);
            $payment_method_type->is_cash = false;
            $payment_method_type->is_bank = false;
            $payment_method_type->is_credit = false;
            $payment_method_type->is_digital = false;
            if (in_array($description, $cash)) {
                $payment_method_type->is_cash = true;
            } elseif (in_array($description, $bank)) {
                $payment_method_type->is_bank = true;
            } elseif (in_array($description, $credit)) {
                $payment_method_type->is_credit = true;
            } elseif (in_array($description, $digital)) {
                $payment_method_type->is_digital = true;
            }
            $payment_method_type->save();
        }
    }



    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        // Schema::table('payment_method_types', function (Blueprint $table) {
        //     $table->dropColumn('is_digital');
        //     $table->dropColumn('is_bank');
        // });
        Schema::table('documents', function (Blueprint $table) {
            $table->dropColumn('cash_id');
        });
        Schema::table('sale_notes', function (Blueprint $table) {
            $table->dropColumn('cash_id');
        });
    }
}
