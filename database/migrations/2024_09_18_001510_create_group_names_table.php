<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateGroupNamesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('group_names', function (Blueprint $table) {
            $table->id();
            $table->string('short_name')->unique()->nullable();
            $table->string('long_name')->unique()->nullable();
            $table->string('management')->nullable();
            $table->string('teacher')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('group_names');
    }
}
