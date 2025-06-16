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
        Schema::create('chat_messages', function (Blueprint $table) {
            $table->id();
            $table->enum('type', ['message', 'activity'])->default('message');
            $table->foreignId('chat_id')->constrained()->onDelete('cascade');
            $table->unsignedInteger('user_id');
            $table->text('message')->nullable();
            $table->unsignedInteger('replied_to_message_id')->nullable();
            $table->boolean('is_updated')->default(false);
            $table->boolean('is_forwarded')->default(false);
            $table->softDeletes();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('chat_messages');
    }
};
