<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateProposalSubmissionsTable extends Migration
{
    public function up()
    {
        Schema::create('proposal_submissions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('group_id')->constrained()->onDelete('cascade');
            $table->string('part_a_file')->nullable();
            $table->string('part_b_file')->nullable();
            $table->dateTime('part_a_submitted_at')->nullable();
            $table->dateTime('part_b_submitted_at')->nullable();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('proposal_submissions');
    }
}
