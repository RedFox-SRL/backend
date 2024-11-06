<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTemplateSectionsTable extends Migration
{
    public function up()
    {
        Schema::create('template_sections', function (Blueprint $table) {
            $table->id();
            $table->foreignId('evaluation_template_id')->constrained()->onDelete('cascade');
            $table->string('title');
            $table->integer('order');
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('template_sections');
    }
}
