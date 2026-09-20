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
        Schema::create('financial_transactions', function (Blueprint $table) {
            $table->id();

            $table->foreignId('church_id')
                ->constrained('churches')
                ->cascadeOnDelete()
                ->comment('Congregação responsável pelo lançamento');

            $table->enum('type', ['entry', 'exit'])
                ->comment('Tipo: entry = Entrada (receita), exit = Saída (despesa)');

            $table->enum('category', ['dizimo', 'oferta', 'outros', 'despesa'])
                ->comment('Categoria: dizimo, oferta, outros (para entradas); despesa (para saídas)');

            $table->decimal('amount', 10, 2)
                ->comment('Valor do lançamento em reais (R$)');

            $table->text('description')
                ->nullable()
                ->comment('Descrição livre (obrigatória para oferta sem membro, outros e despesa)');

            $table->foreignId('member_id')
                ->nullable()
                ->constrained('members')
                ->nullOnDelete()
                ->comment('Membro vinculado ao lançamento (obrigatório para dízimo, opcional para oferta)');

            $table->date('competence_date')
                ->comment('Data de competência/referência do lançamento (ex: primeiro dia do mês do dízimo)');

            $table->date('transaction_date')
                ->comment('Data efetiva em que o valor foi recebido/pago');

            $table->foreignId('created_by')
                ->constrained('users')
                ->comment('Secretário que registrou o lançamento');

            $table->timestamps();

            // Índices para performance em consultas de extrato e relatórios
            $table->index('church_id');
            $table->index('type');
            $table->index('category');
            $table->index('competence_date');
            $table->index('transaction_date');
            $table->index('member_id');
            $table->index('created_by');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('financial_transactions');
    }
};
