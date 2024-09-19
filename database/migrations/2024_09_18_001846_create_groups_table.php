<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateGroupsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('groups', function (Blueprint $table) {
            $table->id();
            $table->foreignId('creator_id')->constrained('students')->onDelete('set null');
            $table->foreignId('management_id')->constrained('management')->onDelete('cascade');
            $table->string('code')->unique();
            $table->string('short_name');
            $table->string('long_name');
            $table->string('contact_email');
            $table->string('contact_phone');
            $table->string('logo')->nullable();
            $table->boolean('is_code_active')->default(true);
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
        Schema::dropIfExists('groups');
    }
}
