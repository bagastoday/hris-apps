<?php

// database/migrations/2026_09_29_152804_create_announcements_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('announcements', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('title');
            $table->text('content');
            $table->string('category')->default('umum'); // umum, libur, kebijakan, penting, event
            $table->string('badge_color')->default('blue'); // blue, emerald, amber, rose, purple
            $table->boolean('is_pinned')->default(false);
            $table->boolean('is_active')->default(true);
            $table->string('attachment')->nullable();
            $table->timestamp('published_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('announcements');
    }
};
