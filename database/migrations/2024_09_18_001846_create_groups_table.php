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
            $table->string('code')->unique(); // Código único para invitar estudiantes
            $table->string('nombre_corto');
            $table->string('nombre_largo');
            $table->string('correo_contacto');
            $table->string('telefono_contacto');
            $table->string('logo')->nullable(); // Opcional
            $table->boolean('is_code_active')->default(true); // Controla si el código está activo
            $table->integer('max_miembros'); // Máximo número de miembros basado en la configuración del docente
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
