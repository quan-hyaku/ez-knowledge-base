<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('kb_tickets', function (Blueprint $table) {
            $table->foreignId('user_id')->nullable()->after('status')->constrained('users')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('kb_tickets', function (Blueprint $table) {
            $table->dropConstrainedForeignId('user_id');
        });
    }
};
