<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSprintEvaluationPointsTable extends Migration
{
    public function up()
    {
        Schema::create('sprint_evaluation_points', function (Blueprint $table) {
            $table->id();
            $table->foreignId('sprint_evaluation_id')->constrained()->onDelete('cascade');
            $table->enum('type', ['strength', 'weakness']);
            $table->text('description');
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('sprint_evaluation_points');
    }
}
