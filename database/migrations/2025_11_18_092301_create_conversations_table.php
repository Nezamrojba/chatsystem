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
        Schema::create('conversations', function (Blueprint $table) {
            $table->id();
            $table->string('title')->nullable()->comment('Optional conversation title');
            $table->string('type')->default('private')->comment('Type: private, group');
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete()->comment('Conversation creator');
            $table->timestamp('last_message_at')->nullable()->index()->comment('Last message timestamp for sorting');
            $table->timestamps();
            $table->softDeletes();

            // Indexes for performance
            $table->index('created_at');
            $table->index('type');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('conversations');
    }
};
