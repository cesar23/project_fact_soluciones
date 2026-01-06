<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddGradeAndSectionTouserRelSuscriptionPlan extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('user_rel_suscription_plans', function (Blueprint $table) {
            if (!Schema::hasColumn('user_rel_suscription_plans', 'grade')) {
            $table->text('grade')->nullable()->comment('Grado designado - utilizado en matricula');
            }
            if (!Schema::hasColumn('user_rel_suscription_plans', 'section')) {
            $table->text('section')->nullable()->comment('Seccion designado - utilizado en matricula');
            }
        });
        Schema::table('sale_notes', function (Blueprint $table) {
            if (!Schema::hasColumn('sale_notes', 'grade')) {
            $table->text('grade')->nullable()->comment('Grado designado - utilizado en matricula');
            }
            if (!Schema::hasColumn('sale_notes', 'section')) {
            $table->text('section')->nullable()->comment('Seccion designado - utilizado en matricula');
            }
        });
        Schema::table('documents', function (Blueprint $table) {
            if (!Schema::hasColumn('documents', 'grade')) {
            $table->text('grade')->nullable()->comment('Grado designado - utilizado en matricula');
            }
            if (!Schema::hasColumn('documents', 'section')) {
            $table->text('section')->nullable()->comment('Seccion designado - utilizado en matricula');
            }
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('user_rel_suscription_plans', function (Blueprint $table) {
            if (Schema::hasColumn('user_rel_suscription_plans', 'grade')) {
            $table->dropColumn('grade');
            }
            if (Schema::hasColumn('user_rel_suscription_plans', 'section')) {
            $table->dropColumn('section');
            }
        });
        Schema::table('sale_notes', function (Blueprint $table) {
            if (Schema::hasColumn('sale_notes', 'grade')) {
            $table->dropColumn('grade');
            }
            if (Schema::hasColumn('sale_notes', 'section')) {
            $table->dropColumn('section');
            }
        });
        Schema::table('documents', function (Blueprint $table) {
            if (Schema::hasColumn('documents', 'grade')) {
            $table->dropColumn('grade');
            }
            if (Schema::hasColumn('documents', 'section')) {
            $table->dropColumn('section');
            }
        });

    }
}
