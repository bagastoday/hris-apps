<?php

// database/migrations/2026_09_29_155509_create_tickets_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::dropIfExists('tickets');
        Schema::create('tickets', function (Blueprint $table) {
            $table->id();
            $table->string('ticket_code', 50)->unique();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('assigned_to')->nullable()->constrained('users')->nullOnDelete();
            $table->string('title', 200);
            $table->string('category', 50)->default('fasilitas'); // fasilitas, payroll, bpjs, kebijakan, it_support, pengaduan, lainnya
            $table->string('priority', 30)->default('sedang'); // rendah, sedang, tinggi, darurat
            $table->string('status', 30)->default('open'); // open, in_progress, resolved, closed
            $table->text('description');
            $table->string('attachment')->nullable();
            $table->boolean('is_anonymous')->default(false);
            $table->timestamp('resolved_at')->nullable();
            $table->timestamp('closed_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tickets');
    }
};
