<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('kb_ticket_replies', function (Blueprint $table) {
            $table->string('message_id')->nullable()->after('user_id')->index();
        });
    }

    public function down(): void
    {
        Schema::table('kb_ticket_replies', function (Blueprint $table) {
            $table->dropIndex('kb_ticket_replies_message_id_index');
            $table->dropColumn('message_id');
        });
    }
};
