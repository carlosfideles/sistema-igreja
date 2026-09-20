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
        Schema::table('member_transfers', function (Blueprint $table) {
            $table->string('status', 20)->default('pending')->after('notes')->comment('Situação da solicitação: pending, approved, rejected');
            $table->string('type', 20)->default('transfer')->after('status')->comment('Tipo: transfer (transferência) ou inactivation (saída do ministério)');
            $table->foreignId('approved_by')->nullable()->after('type')->constrained('users')->onDelete('set null')->comment('Secretário Regional que autorizou a transferência');
            $table->timestamp('approved_at')->nullable()->after('approved_by')->comment('Data e hora da autorização');
            $table->foreignId('to_church_id')->nullable()->change();
            
            $table->index('status');
            $table->index('type');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('member_transfers', function (Blueprint $table) {
            $table->dropForeign(['approved_by']);
            $table->dropColumn(['status', 'type', 'approved_by', 'approved_at']);
        });
    }
};
