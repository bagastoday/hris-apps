<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('employees', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('employee_code')->unique();
            $table->string('full_name');
            $table->string('nik')->unique();
            $table->string('email')->nullable()->unique();
            $table->string('phone')->nullable();
            $table->enum('gender', ['laki-laki', 'perempuan'])->nullable();
            $table->date('birth_date')->nullable();
            $table->date('join_date');
            $table->foreignId('department_id')->nullable()->constrained('departments')->nullOnDelete();
            $table->foreignId('position_id')->nullable()->constrained('positions')->nullOnDelete();
            $table->enum('employment_status', ['aktif', 'kontrak', 'magang', 'resign', 'cuti'])->default('aktif');
            $table->string('photo')->nullable();
            $table->text('address')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('employees');
    }
};