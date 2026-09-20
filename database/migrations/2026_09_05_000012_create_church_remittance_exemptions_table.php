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
        Schema::create('church_remittance_exemptions', function (Blueprint $table) {
            $table->id();

            $table->foreignId('church_id')
                ->constrained('churches')
                ->cascadeOnDelete()
                ->comment('Congregação beneficiada pela isenção');

            $table->enum('type', ['temporary', 'permanent'])
                ->comment('Tipo de isenção: temporary = Temporária (N meses), permanent = Permanente');

            $table->unsignedTinyInteger('months_count')
                ->nullable()
                ->comment('Quantidade de meses (apenas para isenção temporária)');

            $table->date('starts_at')
                ->comment('Data de início da isenção (primeiro dia do mês)');

            $table->date('ends_at')
                ->nullable()
                ->comment('Data de término calculada para isenção temporária. Null para isenção permanente.');

            $table->foreignId('granted_by')
                ->constrained('users')
                ->comment('1º Secretário Regional que concedeu a isenção');

            $table->text('notes')
                ->nullable()
                ->comment('Justificativa/observação sobre a isenção concedida');

            $table->foreignId('revoked_by')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete()
                ->comment('Usuário que revogou a isenção (se aplicável)');

            $table->timestamp('revoked_at')
                ->nullable()
                ->comment('Data/hora da revogação da isenção');

            $table->timestamps();

            $table->index('church_id');
            $table->index('type');
            $table->index(['starts_at', 'ends_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('church_remittance_exemptions');
    }
};
