<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddPercentagesToScoreConfigurationsTable extends Migration
{
    public function up()
    {
        Schema::table('score_configurations', function (Blueprint $table) {
            $table->float('sprint_teacher_percentage')->after('proposal_points');
            $table->float('sprint_self_percentage')->after('sprint_teacher_percentage');
            $table->float('sprint_peer_percentage')->after('sprint_self_percentage');
            $table->float('proposal_part_a_percentage')->after('sprint_peer_percentage');
            $table->float('proposal_part_b_percentage')->after('proposal_part_a_percentage');
        });
    }

    public function down()
    {
        Schema::table('score_configurations', function (Blueprint $table) {
            $table->dropColumn([
                'sprint_teacher_percentage',
                'sprint_self_percentage',
                'sprint_peer_percentage',
                'proposal_part_a_percentage',
                'proposal_part_b_percentage'
            ]);
        });
    }
}
