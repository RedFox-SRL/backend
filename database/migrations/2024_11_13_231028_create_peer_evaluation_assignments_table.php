<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePeerEvaluationAssignmentsTable extends Migration
{
    public function up()
    {
        Schema::create('peer_evaluation_assignments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('evaluation_period_id')->constrained()->onDelete('cascade');
            $table->foreignId('evaluator_id')->constrained('students')->onDelete('cascade');
            $table->foreignId('evaluated_id')->constrained('students')->onDelete('cascade');
            $table->timestamps();

            $table->unique(['evaluation_period_id', 'evaluator_id', 'evaluated_id']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('peer_evaluation_assignments');
    }
}
