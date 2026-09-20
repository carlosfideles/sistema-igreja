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
        Schema::create('churches', function (Blueprint $table) {
            $table->id();
            $table->string('name')->comment('Nome da congregação / igreja');
            $table->string('code', 50)->nullable()->unique()->comment('Código identificador único da igreja (ex: IG-CENTRAL, IG-001)');
            $table->string('cnpj', 20)->nullable()->comment('CNPJ da congregação se houver');
            $table->string('phone', 20)->nullable()->comment('Telefone fixo ou principal');
            $table->string('email')->nullable()->comment('E-mail institucional da congregação');
            $table->string('zip_code', 10)->nullable()->comment('CEP');
            $table->string('address')->nullable()->comment('Logradouro / Rua / Avenida');
            $table->string('number', 20)->nullable()->comment('Número do imóvel');
            $table->string('complement')->nullable()->comment('Complemento (ex: Sala 1, Lote 5)');
            $table->string('neighborhood')->nullable()->comment('Bairro / Setor');
            $table->string('city')->comment('Cidade / Região Administrativa');
            $table->string('state', 2)->default('DF')->comment('UF (ex: DF, GO)');
            $table->string('responsible_name')->nullable()->comment('Nome do pastor ou dirigente responsável');
            $table->date('foundation_date')->nullable()->comment('Data de fundação da congregação');
            $table->enum('status', ['active', 'inactive'])->default('active')->comment('Situação da igreja: active ou inactive');
            $table->string('logo')->nullable()->comment('Caminho da imagem/foto/logo da congregação');
            $table->text('notes')->nullable()->comment('Observações adicionais');
            $table->timestamps();
            $table->softDeletes();

            $table->index('status');
            $table->index(['city', 'state']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('churches');
    }
};
