<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateScoreConfigurationsTable extends Migration
{
    public function up()
    {
        Schema::create('score_configurations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('management_id')->constrained()->onDelete('cascade');
            $table->integer('sprint_points');
            $table->integer('cross_evaluation_points');
            $table->integer('proposal_points');
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('score_configurations');
    }
}
