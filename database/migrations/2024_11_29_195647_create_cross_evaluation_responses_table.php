<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCrossEvaluationResponsesTable extends Migration
{
    public function up()
    {
        Schema::create('cross_evaluation_responses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('cross_evaluation_id')->constrained();
            $table->foreignId('template_criterion_id')->constrained();
            $table->integer('score');
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('cross_evaluation_responses');
    }
}
