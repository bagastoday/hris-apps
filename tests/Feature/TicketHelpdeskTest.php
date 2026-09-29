<?php

// tests/Feature/TicketHelpdeskTest.php

namespace Tests\Feature;

use App\Models\ActivityLog;
use App\Models\Department;
use App\Models\Employee;
use App\Models\Position;
use App\Models\Ticket;
use App\Models\TicketReply;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TicketHelpdeskTest extends TestCase
{
    use RefreshDatabase;

    private function createKaryawanUser(string $name = 'Budi Santoso'): User
    {
        $dept = Department::firstOrCreate(['code' => 'OPS'], ['name' => 'Operasional', 'is_active' => true]);
        $pos = Position::firstOrCreate(['name' => 'Staf'], ['department_id' => $dept->id, 'is_active' => true]);

        $user = User::create([
            'name' => $name,
            'email' => strtolower(str_replace(' ', '.', $name)) . '@talenta.local',
            'password' => bcrypt('password123'),
            'role' => 'karyawan',
        ]);

        Employee::create([
            'user_id' => $user->id,
            'employee_code' => 'EMP-' . rand(1000, 9999),
            'nik' => 'NIK-' . rand(1000, 9999),
            'full_name' => $name,
            'department_id' => $dept->id,
            'position_id' => $pos->id,
            'join_date' => now()->toDateString(),
            'employment_status' => 'aktif',
        ]);

        return $user;
    }

    /**
     * Karyawan dapat membuat tiket bantuan baru.
     */
    public function test_karyawan_can_create_ticket()
    {
        $karyawan = $this->createKaryawanUser();

        $response = $this->actingAs($karyawan)->post(route('karyawan.tickets.store'), [
            'title' => 'AC Ruang Meeting Tidak Dingin',
            'category' => 'fasilitas',
            'priority' => 'sedang',
            'description' => 'AC di ruang meeting lantai 2 mengeluarkan udara panas sejak kemarin sore.',
            'is_anonymous' => 0,
        ]);

        $ticket = Ticket::where('title', 'AC Ruang Meeting Tidak Dingin')->first();
        $this->assertNotNull($ticket);
        $this->assertStringStartsWith('TKT-', $ticket->ticket_code);
        $this->assertEquals('open', $ticket->status);
        $this->assertEquals($karyawan->id, $ticket->user_id);

        $response->assertRedirect(route('karyawan.tickets.show', $ticket));

        // Pastikan activity log tercatat
        $this->assertDatabaseHas('activity_logs', [
            'action' => 'create_ticket',
        ]);
    }

    /**
     * Karyawan dapat membuat pengaduan secara anonim (whistleblowing).
     */
    public function test_karyawan_can_create_anonymous_ticket()
    {
        $karyawan = $this->createKaryawanUser();

        $this->actingAs($karyawan)->post(route('karyawan.tickets.store'), [
            'title' => 'Laporan Pelanggaran SOP Pengadaan',
            'category' => 'pengaduan',
            'priority' => 'tinggi',
            'description' => 'Terdapat indikasi markup harga perlengkapan kantor yang tidak sesuai standar.',
            'is_anonymous' => 1,
        ]);

        $ticket = Ticket::where('title', 'Laporan Pelanggaran SOP Pengadaan')->first();
        $this->assertNotNull($ticket);
        $this->assertTrue((bool) $ticket->is_anonymous);
        $this->assertEquals('Anonim (Pengaduan Rahasia)', $ticket->display_author);
    }

    /**
     * HR Admin dapat melihat daftar seluruh tiket bantuan dan detailnya.
     */
    public function test_hr_admin_can_view_all_tickets()
    {
        $hr = User::factory()->create(['role' => 'hr']);
        $karyawan = $this->createKaryawanUser();

        $ticket = Ticket::create([
            'ticket_code' => Ticket::generateTicketCode(),
            'user_id' => $karyawan->id,
            'title' => 'Kartu BPJS Belum Dikirim',
            'category' => 'bpjs',
            'priority' => 'sedang',
            'status' => 'open',
            'description' => 'Mohon info kapan fisik kartu BPJS Ketenagakerjaan bisa diambil.',
        ]);

        $response = $this->actingAs($hr)->get(route('tickets.index'));
        $response->assertStatus(200);
        $response->assertSee('Kartu BPJS Belum Dikirim');
        $response->assertSee($ticket->ticket_code);

        $detail = $this->actingAs($hr)->get(route('tickets.show', $ticket));
        $detail->assertStatus(200);
        $detail->assertSee('Mohon info kapan fisik kartu');
    }

    /**
     * HR Admin dapat mengubah status tiket dan mengirimkan balasan.
     */
    public function test_hr_admin_can_reply_and_update_status()
    {
        $hr = User::factory()->create(['role' => 'hr']);
        $karyawan = $this->createKaryawanUser();

        $ticket = Ticket::create([
            'ticket_code' => Ticket::generateTicketCode(),
            'user_id' => $karyawan->id,
            'title' => 'Jaringan Wifi Lantai 3 Lambat',
            'category' => 'it_support',
            'priority' => 'sedang',
            'status' => 'open',
            'description' => 'Koneksi lemot saat meeting online.',
        ]);

        // HR merespons & mengubah status ke in_progress
        $response = $this->actingAs($hr)->post(route('tickets.reply', $ticket), [
            'message' => 'Halo Budi, router sudah kami restart. Mohon dicoba kembali.',
            'change_status' => 'in_progress',
        ]);

        $response->assertRedirect();
        $this->assertEquals('in_progress', $ticket->fresh()->status);

        $this->assertDatabaseHas('ticket_replies', [
            'ticket_id' => $ticket->id,
            'user_id' => $hr->id,
            'is_admin_reply' => 1,
        ]);
    }

    /**
     * Karyawan dapat membalas percakapan di tiket miliknya.
     */
    public function test_karyawan_can_reply_to_own_ticket()
    {
        $karyawan = $this->createKaryawanUser();

        $ticket = Ticket::create([
            'ticket_code' => Ticket::generateTicketCode(),
            'user_id' => $karyawan->id,
            'title' => 'Koreksi Jam Masuk',
            'category' => 'lainnya',
            'priority' => 'rendah',
            'status' => 'in_progress',
            'description' => 'Ada kendala finger print pagi tadi.',
        ]);

        $response = $this->actingAs($karyawan)->post(route('karyawan.tickets.reply', $ticket), [
            'message' => 'Baik, sudah lancar sekarang. Terima kasih!',
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('ticket_replies', [
            'ticket_id' => $ticket->id,
            'user_id' => $karyawan->id,
            'is_admin_reply' => 0,
        ]);
    }

    /**
     * Karyawan lain tidak dapat membuka tiket orang lain.
     */
    public function test_karyawan_cannot_access_other_employee_ticket()
    {
        $karyawan1 = $this->createKaryawanUser('Pegawai Satu');
        $karyawan2 = $this->createKaryawanUser('Pegawai Dua');

        $ticket = Ticket::create([
            'ticket_code' => Ticket::generateTicketCode(),
            'user_id' => $karyawan1->id,
            'title' => 'Slip Gaji Bulan Lalu',
            'category' => 'payroll',
            'priority' => 'sedang',
            'status' => 'open',
            'description' => 'Ada selisih nominal tunjangan.',
        ]);

        $response = $this->actingAs($karyawan2)->get(route('karyawan.tickets.show', $ticket));
        $response->assertStatus(403);
    }
}
