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
        Schema::create('permissions', function (Blueprint $table) {
            $table->id();
            $table->string('name')->comment('Nome amigável da permissão: Visualizar Membros, Cadastrar Igrejas');
            $table->string('slug')->unique()->comment('Identificador chave: dashboard.view, churches.view, members.create, etc.');
            $table->string('module')->comment('Módulo pertencente: dashboard, churches, members, secretaries, reports, audit, settings');
            $table->text('description')->nullable()->comment('Descrição detalhada do que a permissão autoriza');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('permissions');
    }
};
