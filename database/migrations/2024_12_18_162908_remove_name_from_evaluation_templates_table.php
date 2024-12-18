<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class RemoveNameFromEvaluationTemplatesTable extends Migration
{
    public function up()
    {
        Schema::table('evaluation_templates', function (Blueprint $table) {
            $table->dropColumn('name');
        });
    }

    public function down()
    {
        Schema::table('evaluation_templates', function (Blueprint $table) {
            $table->string('name');
        });
    }
}
