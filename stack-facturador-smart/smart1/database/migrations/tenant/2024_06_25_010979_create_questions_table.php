<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateQuestionsTable extends Migration
{
    public function up()
    {
        Schema::create('survey_questions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('survey_section_id')->constrained()->onDelete('cascade');
            $table->text('question_text');
            $table->enum('question_type', ['date','text', 'number', 'multiple_choice', 'boolean','single_choice','interval']);
            $table->boolean('allow_custom_option')->default(false);
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('survey_questions');
    }
}
