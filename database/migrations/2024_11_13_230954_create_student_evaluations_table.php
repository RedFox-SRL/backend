<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateStudentEvaluationsTable extends Migration
{
    public function up()
    {
        Schema::create('student_evaluations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('evaluation_period_id')->constrained()->onDelete('cascade');
            $table->foreignId('evaluator_id')->constrained('students')->onDelete('cascade');
            $table->foreignId('evaluated_id')->nullable()->constrained('students')->onDelete('cascade');
            $table->boolean('is_completed')->default(false);
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();

            $table->unique(['evaluation_period_id', 'evaluator_id', 'evaluated_id']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('student_evaluations');
    }
}
