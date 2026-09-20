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
        Schema::create('member_transfers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('member_id')->constrained('members')->onDelete('cascade')->comment('Membro que foi transferido');
            $table->foreignId('from_church_id')->constrained('churches')->onDelete('restrict')->comment('Igreja de origem');
            $table->foreignId('to_church_id')->constrained('churches')->onDelete('restrict')->comment('Igreja de destino');
            $table->foreignId('transferred_by')->constrained('users')->onDelete('restrict')->comment('Secretário / Usuário que efetuou a transferência');
            $table->timestamp('transferred_at')->useCurrent()->comment('Data e hora exata da transferência');
            $table->string('reason')->nullable()->comment('Motivo da transferência (ex: Mudança de endereço, designação ministerial)');
            $table->text('notes')->nullable()->comment('Observações adicionais da transferência');
            $table->timestamps();

            $table->index('member_id');
            $table->index('from_church_id');
            $table->index('to_church_id');
            $table->index('transferred_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('member_transfers');
    }
};
