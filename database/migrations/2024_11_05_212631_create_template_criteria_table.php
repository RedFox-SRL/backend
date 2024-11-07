<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTemplateCriteriaTable extends Migration
{
    public function up()
    {
        Schema::create('template_criteria', function (Blueprint $table) {
            $table->id();
            $table->foreignId('template_section_id')->constrained()->onDelete('cascade');
            $table->string('name');
            $table->text('description')->nullable();
            $table->integer('order');
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('template_criteria');
    }
}
