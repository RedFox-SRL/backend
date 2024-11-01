<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTaskWeeklyEvaluationTable extends Migration
{
    public function up()
    {
        Schema::create('task_weekly_evaluation', function (Blueprint $table) {
            $table->id();
            $table->foreignId('task_id')->constrained()->onDelete('cascade');
            $table->foreignId('weekly_evaluation_id')->constrained()->onDelete('cascade');
            $table->text('comments')->nullable();
            $table->unsignedTinyInteger('satisfaction_level');
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('task_weekly_evaluation');
    }
}
