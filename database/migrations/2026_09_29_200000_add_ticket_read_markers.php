<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('tickets', function (Blueprint $table) {
            $table->unsignedBigInteger('team_last_read_reply_id')->nullable();
            $table->unsignedBigInteger('employee_last_read_reply_id')->default(0);
        });

        DB::statement('
            UPDATE tickets
            SET team_last_read_reply_id = COALESCE(
                    (SELECT MAX(id) FROM ticket_replies WHERE ticket_replies.ticket_id = tickets.id),
                    0
                ),
                employee_last_read_reply_id = COALESCE(
                    (SELECT MAX(id) FROM ticket_replies WHERE ticket_replies.ticket_id = tickets.id),
                    0
                )
        ');
    }

    public function down(): void
    {
        Schema::table('tickets', function (Blueprint $table) {
            $table->dropColumn(['team_last_read_reply_id', 'employee_last_read_reply_id']);
        });
    }
};
