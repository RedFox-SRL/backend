<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCrossEvaluationsTable extends Migration
{
    public function up()
    {
        Schema::create('cross_evaluations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('evaluator_group_id')->constrained('groups');
            $table->foreignId('evaluated_group_id')->constrained('groups');
            $table->foreignId('management_id')->constrained('management');
            $table->foreignId('evaluation_template_id')->constrained('evaluation_templates');
            $table->boolean('is_completed')->default(false);
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('cross_evaluations');
    }
}
