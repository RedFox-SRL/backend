<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

class CopyExistingTaskAssignments extends Migration
{
    public function up()
    {
        Schema::create('task_student', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('task_id');
            $table->unsignedBigInteger('student_id');
            $table->timestamps();

            $table->foreign('task_id')->references('id')->on('tasks')->onDelete('cascade');
            $table->foreign('student_id')->references('id')->on('students')->onDelete('cascade');
        });

        $tasks = DB::table('tasks')->whereNotNull('assigned_to')->get();
        foreach ($tasks as $task) {
            DB::table('task_student')->insert([
                'task_id' => $task->id,
                'student_id' => $task->assigned_to,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }

    public function down()
    {
        Schema::dropIfExists('task_student');
    }
}
