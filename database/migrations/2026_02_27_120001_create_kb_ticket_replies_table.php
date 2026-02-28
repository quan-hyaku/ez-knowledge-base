<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('kb_ticket_replies', function (Blueprint $table) {
            $table->id();
            $table->foreignId('kb_ticket_id')->constrained('kb_tickets')->cascadeOnDelete();
            $table->longText('body');
            $table->boolean('is_admin')->default(false);
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('kb_ticket_replies');
    }
};
