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
        Schema::create('member_ecclesiastical_histories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('member_id')->constrained('members')->onDelete('cascade')->comment('Membro relacionado');
            $table->text('description')->comment('Descrição do registro ou mudança eclesiástica');
            $table->timestamp('recorded_at')->useCurrent()->comment('Data e hora do registro');
            $table->foreignId('created_by')->nullable()->constrained('users')->onDelete('set null')->comment('Usuário que realizou o registro');
            $table->timestamps();

            $table->index('member_id');
            $table->index('recorded_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('member_ecclesiastical_histories');
    }
};
