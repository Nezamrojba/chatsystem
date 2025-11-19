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
        Schema::create('messages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('conversation_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->text('body')->nullable()->comment('Message content (text messages)');
            $table->string('type')->default('text')->comment('Message type: text, voice, image, file');
            $table->string('voice_note_path')->nullable()->comment('Voice note file path stored in database');
            $table->integer('voice_note_duration')->nullable()->comment('Voice note duration in seconds');
            $table->json('metadata')->nullable()->comment('Additional message data (file paths, phone info, etc.)');
            $table->boolean('is_edited')->default(false)->comment('Whether message was edited');
            $table->timestamp('edited_at')->nullable()->comment('When message was last edited');
            $table->timestamps();
            $table->softDeletes();

            // Indexes for performance
            $table->index(['conversation_id', 'created_at']);
            $table->index('user_id');
            $table->index('type');
            $table->index('voice_note_path');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('messages');
    }
};
