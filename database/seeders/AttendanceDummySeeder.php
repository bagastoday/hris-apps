<?php

namespace Database\Seeders;

use App\Models\Attendance;
use App\Models\Employee;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Storage;

class AttendanceDummySeeder extends Seeder
{
    public function run(): void
    {
        // Variasi data supaya semua kondisi tombol bisa dicoba
        $samples = [
            ['status' => 'hadir',     'in' => '07:52:00', 'out' => '17:03:00'], // foto masuk + pulang
            ['status' => 'terlambat', 'in' => '08:47:00', 'out' => '17:00:00'],
            ['status' => 'hadir',     'in' => '07:45:00', 'out' => null],       // baru foto masuk
            ['status' => 'izin',      'in' => null,       'out' => null],       // tanpa foto -> tanda "—"
        ];

        Storage::disk('public')->makeDirectory('attendances');

        foreach (Employee::active()->orderBy('id')->get() as $i => $emp) {
            $s = $samples[$i % count($samples)];

            // Cari data hari ini pakai whereDate (cocok dengan format tanggal yang tersimpan)
            $attendance = Attendance::where('employee_id', $emp->id)
                ->whereDate('date', today())
                ->first() ?? new Attendance(['employee_id' => $emp->id, 'date' => today()]);

            $attendance->fill([
                'check_in'        => $s['in'],
                'check_out'       => $s['out'],
                'check_in_photo'  => $s['in'] ? $this->makePhoto($emp, 'MASUK', $s['in']) : null,
                'check_out_photo' => $s['out'] ? $this->makePhoto($emp, 'PULANG', $s['out']) : null,
                'status'          => $s['status'],
                'notes'           => $s['status'] === 'izin' ? 'Keperluan keluarga' : null,
            ])->save();
        }
    }

    // Bikin gambar placeholder sederhana (berisi nama, label, dan jam)
    private function makePhoto(Employee $emp, string $label, string $time): ?string
    {
        if (!function_exists('imagecreatetruecolor')) {
            return null;
        }

        $img = imagecreatetruecolor(300, 400);
        $bg = imagecolorallocate($img, 30 + ($emp->id * 40) % 120, 80 + ($emp->id * 55) % 100, 160);
        imagefilledrectangle($img, 0, 0, 300, 400, $bg);

        $white = imagecolorallocate($img, 255, 255, 255);
        imagestring($img, 5, 20, 150, strtoupper(substr($emp->full_name, 0, 24)), $white);
        imagestring($img, 5, 20, 180, "FOTO {$label}", $white);
        imagestring($img, 5, 20, 210, $time, $white);

        ob_start();
        imagejpeg($img, null, 85);
        $data = ob_get_clean();
        imagedestroy($img);

        $path = 'attendances/dummy-' . $emp->id . '-' . strtolower($label) . '.jpg';
        Storage::disk('public')->put($path, $data);

        return $path;
    }
}