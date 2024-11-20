<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateEvaluationPeriodsTable extends Migration
{
    public function up()
    {
        Schema::create('evaluation_periods', function (Blueprint $table) {
            $table->id();
            $table->foreignId('sprint_id')->constrained()->onDelete('cascade');
            $table->foreignId('evaluation_template_id')->constrained()->onDelete('cascade');
            $table->enum('type', ['self', 'peer']);
            $table->timestamp('starts_at');
            $table->timestamp('ends_at');
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('evaluation_periods');
    }
}
