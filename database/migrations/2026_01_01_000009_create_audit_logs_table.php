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
        Schema::create('audit_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete()->comment('Usuário que realizou a ação');
            $table->foreignId('church_id')->nullable()->constrained('churches')->nullOnDelete()->comment('Igreja de contexto da ação se aplicável');
            $table->string('action', 50)->comment('Ação realizada: created, updated, deleted, login, logout, transfer, password_change, permission_change');
            $table->string('module', 50)->comment('Módulo afetado: members, churches, users, auth, settings, reports, audit');
            $table->string('entity_type')->nullable()->comment('Classe do modelo afetado (ex: App\\Models\\Member)');
            $table->unsignedBigInteger('entity_id')->nullable()->comment('ID do registro afetado');
            $table->text('description')->nullable()->comment('Descrição textual clara da ação para o relatório de auditoria');
            $table->json('old_values')->nullable()->comment('Estado dos atributos antes da modificação');
            $table->json('new_values')->nullable()->comment('Estado dos atributos após a modificação');
            $table->string('ip_address', 45)->nullable()->comment('Endereço IP do cliente');
            $table->text('user_agent')->nullable()->comment('Navegador e dispositivo do cliente');
            $table->timestamp('created_at')->useCurrent()->comment('Data e hora exata do registro de auditoria');

            $table->index('user_id');
            $table->index('church_id');
            $table->index('action');
            $table->index('module');
            $table->index(['entity_type', 'entity_id']);
            $table->index('created_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('audit_logs');
    }
};
