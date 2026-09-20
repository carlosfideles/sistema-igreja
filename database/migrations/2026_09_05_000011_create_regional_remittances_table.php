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
        Schema::create('regional_remittances', function (Blueprint $table) {
            $table->id();

            $table->foreignId('church_id')
                ->constrained('churches')
                ->cascadeOnDelete()
                ->comment('Congregação que deve realizar o repasse');

            $table->date('reference_month')
                ->comment('Primeiro dia do mês de referência do repasse (ex: 2026-09-01 = Setembro/2026)');

            $table->decimal('total_entries', 12, 2)
                ->default(0)
                ->comment('Total de entradas do mês (base de cálculo)');

            $table->decimal('remittance_amount', 12, 2)
                ->default(0)
                ->comment('Valor do repasse de 10% (calculado automaticamente)');

            $table->enum('status', ['pending', 'paid', 'overdue', 'exempted'])
                ->default('pending')
                ->comment('Status: pending = Pendente, paid = Confirmado, overdue = Em Atraso, exempted = Isento');

            $table->foreignId('confirmed_by')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete()
                ->comment('1º Secretário Regional que confirmou o recebimento');

            $table->timestamp('confirmed_at')
                ->nullable()
                ->comment('Data/hora da confirmação do recebimento pelo Regional');

            $table->text('notes')
                ->nullable()
                ->comment('Observações sobre o repasse');

            $table->timestamps();

            // Índice único: uma congregação só pode ter um registro por mês
            $table->unique(['church_id', 'reference_month']);
            $table->index('status');
            $table->index('reference_month');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('regional_remittances');
    }
};
