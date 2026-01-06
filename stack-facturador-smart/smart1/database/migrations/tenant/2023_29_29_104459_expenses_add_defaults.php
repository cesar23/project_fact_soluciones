<?php

use App\Models\Tenant\Person;
use Illuminate\Database\Migrations\Migration;
use Modules\Expense\Models\ExpenseType;

class ExpensesAddDefaults extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $checkIfExistExpense = ExpenseType::where('description', 'GASTO POR DEFECTO')->first();
        if(!$checkIfExistExpense){
            $expense = new ExpenseType();
            $expense->description = 'GASTO POR DEFECTO';
            $expense->save();
        }
        $checkIfExistExpense = ExpenseType::where('description', 'RETIRO DE EFECTIVO')->first();
        if(!$checkIfExistExpense){
            $expense = new ExpenseType();
            $expense->description = 'RETIRO DE EFECTIVO';
            $expense->save();
        }
        
        $checkIfExistExpense = Person::whereType('suppliers')->where('name', 'Proveedores - Varios')->first();
        if(!$checkIfExistExpense){
            $person = new Person();
            $person->name = 'Proveedores - Varios';
            $person->identity_document_type_id = '0';
            $person->number = '78888887';
            $person->type = 'suppliers';
            $person->country_id = 'PE';
            $person->save();
        }

        $checkIfExistExpense = Person::whereType('suppliers')->where('name', 'Retiros de efectivo')->first();
        if(!$checkIfExistExpense){
            $person = new Person();
            $person->name = 'Retiros de efectivo';
            $person->identity_document_type_id = '0';
            $person->number = '87777778';
            $person->type = 'suppliers';
            $person->country_id = 'PE';
            $person->save();
        }

        
        
     
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
