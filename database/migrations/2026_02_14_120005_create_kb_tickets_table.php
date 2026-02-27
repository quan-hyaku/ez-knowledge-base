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
        Schema::create('kb_tickets', function (Blueprint $table) {
            $table->id();
            $table->string('subject');
            $table->string('category')->nullable();
            $table->string('urgency')->default('medium');
            $table->longText('description');
            $table->string('name')->nullable();
            $table->string('email')->nullable();
            $table->string('status')->default('open');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('kb_tickets');
    }
};
