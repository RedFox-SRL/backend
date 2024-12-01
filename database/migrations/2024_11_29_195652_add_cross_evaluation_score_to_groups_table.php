<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddCrossEvaluationScoreToGroupsTable extends Migration
{
    public function up()
    {
        Schema::table('groups', function (Blueprint $table) {
            $table->decimal('cross_evaluation_score', 5, 2)->nullable();
        });
    }

    public function down()
    {
        Schema::table('groups', function (Blueprint $table) {
            $table->dropColumn('cross_evaluation_score');
        });
    }
}