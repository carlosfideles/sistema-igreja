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
            $table->boolean('local_notified')->default(false)->after('approved_at')->comment('Indica se o secretário local já visualizou a notificação de aprovação/recusa');
            $table->index('local_notified');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('member_transfers', function (Blueprint $table) {
            $table->dropIndex(['local_notified']);
            $table->dropColumn('local_notified');
        });
    }
};
