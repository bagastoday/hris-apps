<?php

namespace Tests\Feature;

use App\Models\ActivityLog;
use App\Models\Announcement;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AnnouncementAndActivityLogTest extends TestCase
{
    use RefreshDatabase;
    /**
     * Pastikan admin HR dapat mengakses halaman Activity Log.
     */
    public function test_hr_admin_can_view_activity_logs()
    {
        $admin = User::factory()->create([
            'role' => 'hr',
        ]);

        ActivityLog::record(
            action: 'create_test',
            description: 'Uji coba pencatatan audit log',
            subject: $admin
        );

        $response = $this->actingAs($admin)->get(route('activity-logs.index'));

        $response->assertStatus(200);
        $response->assertSee('Audit Log');
        $response->assertSee('Uji coba pencatatan audit log');
    }

    /**
     * Pastikan karyawan biasa diarahkan kembali saat mencoba mengakses audit log HR.
     */
    public function test_karyawan_cannot_view_admin_activity_logs()
    {
        $karyawan = User::factory()->create([
            'role' => 'karyawan',
        ]);

        $response = $this->actingAs($karyawan)->get(route('activity-logs.index'));
        $response->assertRedirect(route('karyawan.home'));
    }

    /**
     * Pastikan admin HR dapat membuat pengumuman baru.
     */
    public function test_hr_admin_can_create_announcement()
    {
        $admin = User::factory()->create([
            'role' => 'hr',
        ]);

        $response = $this->actingAs($admin)->post(route('announcements.store'), [
            'title' => 'Libur Nasional Idul Fitri',
            'content' => 'Kantor akan libur operasional mulai tanggal 1 hingga 5.',
            'category' => 'libur',
            'badge_color' => 'emerald',
            'is_pinned' => 1,
        ]);

        $response->assertRedirect(route('announcements.index'));
        $this->assertDatabaseHas('announcements', [
            'title' => 'Libur Nasional Idul Fitri',
            'category' => 'libur',
            'badge_color' => 'emerald',
            'is_pinned' => 1,
        ]);

        // Pastikan activity log tercatat
        $this->assertDatabaseHas('activity_logs', [
            'action' => 'create_announcement',
        ]);
    }

    /**
     * Pastikan admin HR dapat toggle pin pengumuman.
     */
    public function test_hr_admin_can_toggle_pin_announcement()
    {
        $admin = User::factory()->create(['role' => 'hr']);
        $announcement = Announcement::create([
            'user_id' => $admin->id,
            'title' => 'Aturan WFH Baru',
            'content' => 'Detail aturan WFH.',
            'category' => 'policy',
            'is_pinned' => false,
            'is_active' => true,
        ]);

        $response = $this->actingAs($admin)->post(route('announcements.toggle-pin', $announcement));
        $response->assertRedirect();

        $this->assertTrue((bool) $announcement->fresh()->is_pinned);
    }

    /**
     * Pastikan karyawan dapat mengakses papan pengumuman karyawan.
     */
    public function test_karyawan_can_view_announcements_board()
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $karyawan = User::factory()->create(['role' => 'karyawan']);

        Announcement::create([
            'user_id' => $admin->id,
            'title' => 'Info Medical Check Up',
            'content' => 'MCU tahunan akan diselenggarakan di RS Mitra.',
            'category' => 'info',
            'is_pinned' => true,
            'is_active' => true,
        ]);

        $response = $this->actingAs($karyawan)->get(route('karyawan.announcements'));
        $response->assertStatus(200);
        $response->assertSee('Papan Pengumuman');
        $response->assertSee('Info Medical Check Up');
    }
}
