<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Tabela de Funções Exercidas
        if (!Schema::hasTable('functions')) {
            Schema::create('functions', function (Blueprint $table) {
                $table->id();
                $table->string('name')->unique();
                $table->boolean('is_active')->default(true);
                $table->timestamps();
            });
        }

        // Tabela pivô de relacionamento N:N entre Membros e Funções
        if (!Schema::hasTable('member_function')) {
            Schema::create('member_function', function (Blueprint $table) {
                $table->id();
                $table->foreignId('member_id')->constrained('members')->onDelete('cascade');
                $table->foreignId('function_id')->constrained('functions')->onDelete('cascade');
                $table->timestamps();

                $table->unique(['member_id', 'function_id']);
            });
        }

        // Registro nativo padrão: "1º Secretário"
        if (Schema::hasTable('functions')) {
            $now = now();
            DB::table('functions')->updateOrInsert(
                ['name' => '1º Secretário'],
                ['is_active' => true, 'created_at' => $now, 'updated_at' => $now]
            );
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('member_function');
        Schema::dropIfExists('functions');
    }
};
