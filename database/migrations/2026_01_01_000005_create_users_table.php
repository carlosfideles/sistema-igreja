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
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('name')->comment('Nome completo do usuário / secretário');
            $table->string('email')->unique()->comment('E-mail institucional de login');
            $table->string('password')->comment('Hash seguro da senha (bcrypt)');
            $table->string('cpf', 14)->nullable()->unique()->comment('CPF formatado ou dígitos');
            $table->string('phone', 20)->nullable()->comment('Telefone celular / WhatsApp do usuário');
            $table->string('photo')->nullable()->comment('Caminho da foto de perfil');
            $table->enum('status', ['active', 'inactive', 'blocked'])->default('active')->comment('Status da conta: active, inactive, blocked');
            $table->foreignId('role_id')->nullable()->constrained('roles')->onDelete('set null')->comment('Papel/Função do usuário');
            $table->enum('scope', ['REGIONAL', 'LOCAL'])->default('LOCAL')->comment('Nível de abrangência: REGIONAL (vê todas as igrejas) ou LOCAL (vê apenas autorizadas)');
            $table->dateTime('last_login_at')->nullable()->comment('Data e hora do último acesso ao sistema');
            $table->rememberToken();
            $table->timestamps();
            $table->softDeletes();

            $table->index('status');
            $table->index('scope');
            $table->index('role_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('users');
    }
};
