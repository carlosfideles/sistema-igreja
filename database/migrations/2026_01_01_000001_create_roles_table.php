<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('roles', function (Blueprint $table) {
            $table->id();
            $table->string('name')->comment('Nome do papel: 1º Secretário, 2º Secretário, 3º Secretário, Administrador');
            $table->string('slug')->unique()->comment('Identificador slug: primeiro_secretario, segundo_secretario, terceiro_secretario, admin');
            $table->text('description')->nullable()->comment('Descrição das atribuições do papel');
            $table->unsignedTinyInteger('level')->default(1)->comment('Nível hierárquico (ex: 1 = 1º Secretário, 2 = 2º Secretário, 3 = 3º Secretário)');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('roles');
    }
};
