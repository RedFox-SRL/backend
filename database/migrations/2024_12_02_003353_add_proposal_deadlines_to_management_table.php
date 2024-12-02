<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddProposalDeadlinesToManagementTable extends Migration
{
    public function up()
    {
        Schema::table('management', function (Blueprint $table) {
            $table->dateTime('proposal_part_a_deadline')->nullable();
            $table->dateTime('proposal_part_b_deadline')->nullable();
        });
    }

    public function down()
    {
        Schema::table('management', function (Blueprint $table) {
            $table->dropColumn(['proposal_part_a_deadline', 'proposal_part_b_deadline']);
        });
    }
}
