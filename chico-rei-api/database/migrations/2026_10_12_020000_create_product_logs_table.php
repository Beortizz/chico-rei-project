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
        Schema::create('product_logs', function (Blueprint $table) {
            $table->id();
            // Sem foreign key: o log precisa sobreviver à exclusão do produto.
            $table->unsignedBigInteger('product_id')->index();
            $table->string('product_name');
            // Nulo quando a ação não vem de um usuário autenticado (seeder, importação).
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('action', 20)->index();
            $table->json('old_values')->nullable();
            $table->json('new_values')->nullable();
            $table->timestamp('created_at')->useCurrent();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('product_logs');
    }
};
