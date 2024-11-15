<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateEvaluationResponsesTable extends Migration
{
    public function up()
    {
        Schema::create('evaluation_responses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_evaluation_id')->constrained()->onDelete('cascade');
            $table->foreignId('template_criterion_id')->constrained()->onDelete('cascade');
            $table->integer('score')->unsigned();
            $table->timestamps();

            $table->unique(['student_evaluation_id', 'template_criterion_id']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('evaluation_responses');
    }
}
