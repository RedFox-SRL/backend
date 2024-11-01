<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddEvaluatorIdToWeeklyEvaluationsTable extends Migration
{
    public function up()
    {
        Schema::table('weekly_evaluations', function (Blueprint $table) {
            $table->foreignId('evaluator_id')->constrained('users')->onDelete('cascade')->after('sprint_id');
        });
    }

    public function down()
    {
        Schema::table('weekly_evaluations', function (Blueprint $table) {
            $table->dropForeign(['evaluator_id']);
            $table->dropColumn('evaluator_id');
        });
    }
}
