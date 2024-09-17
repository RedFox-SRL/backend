<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateManagementTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('management', function (Blueprint $table) {
            $table->id();
            $table->foreignId('teacher_id')->constrained('teachers')->onDelete('cascade');
            $table->string('code')->unique(); // Código único para que los estudiantes se unan
            $table->enum('semester', ['first', 'second']); // Semestre de la gestión
            $table->date('start_date'); // Fecha de inicio de la gestión
            $table->date('end_date');   // Fecha de fin de la gestión
            $table->integer('group_limit'); // Límite de estudiantes por grupo
            $table->boolean('is_code_active')->default(true); // Para activar/desactivar el código
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
        Schema::dropIfExists('management');
    }
}
