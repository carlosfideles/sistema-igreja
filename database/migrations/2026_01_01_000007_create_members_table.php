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
        Schema::create('members', function (Blueprint $table) {
            $table->id();
            $table->foreignId('church_id')->constrained('churches')->onDelete('restrict')->comment('Igreja à qual o membro pertence obrigatoriamente');
            $table->string('full_name')->comment('Nome completo de registro civil');
            $table->string('social_name')->nullable()->comment('Nome social ou preferencial');
            $table->string('cpf', 14)->nullable()->comment('CPF do membro');
            $table->string('rg', 20)->nullable()->comment('RG e órgão expedidor');
            $table->date('birth_date')->nullable()->comment('Data de nascimento');
            $table->enum('gender', ['M', 'F', 'outro'])->nullable()->comment('Gênero: M (Masculino), F (Feminino), outro');
            $table->enum('marital_status', ['solteiro', 'casado', 'viuvo', 'divorciado', 'uniao_estavel', 'outro'])->nullable()->comment('Estado civil');
            $table->string('phone', 20)->nullable()->comment('Telefone de contato principal');
            $table->string('whatsapp', 20)->nullable()->comment('Número com WhatsApp');
            $table->string('email')->nullable()->comment('E-mail pessoal');
            $table->string('zip_code', 10)->nullable()->comment('CEP residencial');
            $table->string('address')->nullable()->comment('Logradouro / Rua / Avenida');
            $table->string('number', 20)->nullable()->comment('Número residencial');
            $table->string('complement')->nullable()->comment('Complemento residencial');
            $table->string('neighborhood')->nullable()->comment('Bairro residencial');
            $table->string('city')->nullable()->comment('Cidade residencial');
            $table->string('state', 2)->nullable()->comment('UF residencial');
            $table->string('photo')->nullable()->comment('Caminho da foto do membro');
            $table->date('entry_date')->nullable()->comment('Data de entrada / batismo / adesão');
            $table->enum('status', ['ativo', 'inativo', 'transferido', 'disciplina', 'falecido'])->default('ativo')->comment('Situação eclesiástica do membro');
            $table->text('notes')->nullable()->comment('Observações do histórico do membro');
            $table->timestamps();
            $table->softDeletes();

            $table->index('church_id');
            $table->index('status');
            $table->index('full_name');
            $table->index('cpf');
            $table->index('entry_date');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('members');
    }
};
