<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddEvaluationFieldsToProposalSubmissionsTable extends Migration
{
    public function up()
    {
        Schema::table('proposal_submissions', function (Blueprint $table) {
            $table->integer('part_a_score')->nullable();
            $table->integer('part_b_score')->nullable();
        });
    }

    public function down()
    {
        Schema::table('proposal_submissions', function (Blueprint $table) {
            $table->dropColumn(['part_a_score', 'part_b_score']);
        });
    }
}
