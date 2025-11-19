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
        Schema::create('conversation_user', function (Blueprint $table) {
            $table->id();
            $table->foreignId('conversation_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->timestamp('read_at')->nullable()->comment('Last read message timestamp');
            $table->timestamp('joined_at')->useCurrent()->comment('When user joined conversation');
            $table->timestamps();

            // Unique constraint: user can only be in conversation once
            $table->unique(['conversation_id', 'user_id']);
            
            // Indexes for performance
            $table->index('user_id');
            $table->index('conversation_id');
            $table->index('read_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('conversation_user');
    }
};
