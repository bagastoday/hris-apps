<?php

// database/migrations/2026_10_01_000002_change_attendance_photos_to_mediumtext.php

use App\Models\Attendance;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Ubah kolom foto ke MEDIUMTEXT agar mendukung penyimpanan biner Data URL (Base64)
        // Hal ini memungkinkan foto absensi tetap tampil utuh saat aplikasi dibuka dari laptop/perangkat lain
        // meskipun file fisik di folder storage lokal laptop awal belum tersinkronisasi.
        if (DB::getDriverName() === 'mysql') {
            DB::statement('ALTER TABLE attendances MODIFY check_in_photo MEDIUMTEXT NULL, MODIFY check_out_photo MEDIUMTEXT NULL');
        }

        // Migrasi data foto yang sudah ada di storage lokal laptop ke data URI
        try {
            $attendances = Attendance::all();
            foreach ($attendances as $attendance) {
                $dirty = false;
                if ($attendance->check_in_photo && ! str_starts_with($attendance->check_in_photo, 'data:image')) {
                    if (Storage::disk('public')->exists($attendance->check_in_photo)) {
                        $binary = Storage::disk('public')->get($attendance->check_in_photo);
                        if ($binary) {
                            $attendance->check_in_photo = 'data:image/jpeg;base64,' . base64_encode($binary);
                            $dirty = true;
                        }
                    }
                }

                if ($attendance->check_out_photo && ! str_starts_with($attendance->check_out_photo, 'data:image')) {
                    if (Storage::disk('public')->exists($attendance->check_out_photo)) {
                        $binary = Storage::disk('public')->get($attendance->check_out_photo);
                        if ($binary) {
                            $attendance->check_out_photo = 'data:image/jpeg;base64,' . base64_encode($binary);
                            $dirty = true;
                        }
                    }
                }

                if ($dirty) {
                    $attendance->save();
                }
            }
        } catch (\Throwable $e) {
            // Abaikan jika migrasi data gagal di environment tertentu
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (DB::getDriverName() === 'mysql') {
            DB::statement('ALTER TABLE attendances MODIFY check_in_photo VARCHAR(255) NULL, MODIFY check_out_photo VARCHAR(255) NULL');
        }
    }
};
