<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateEvaluationTemplatesTable extends Migration
{
    public function up()
    {
        Schema::create('evaluation_templates', function (Blueprint $table) {
            $table->id();
            $table->foreignId('management_id')->constrained()->onDelete('cascade');
            $table->enum('type', ['self', 'peer', 'cross']);
            $table->string('name');
            $table->timestamps();

            $table->unique(['management_id', 'type']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('evaluation_templates');
    }
}
