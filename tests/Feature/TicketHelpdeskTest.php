<?php

// tests/Feature/TicketHelpdeskTest.php

namespace Tests\Feature;

use App\Models\ActivityLog;
use App\Models\Department;
use App\Models\Employee;
use App\Models\FinanceTransaction;
use App\Models\Leave;
use App\Models\PayrollItem;
use App\Models\PayrollRun;
use App\Models\Position;
use App\Models\Ticket;
use App\Models\TicketReply;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
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

        $this->actingAs($karyawan)
            ->get(route('karyawan.tickets.create'))
            ->assertOk()
            ->assertSee('Formulir Tiket Bantuan');

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

    protected function fakeImage(string $name = 'test.jpg'): UploadedFile
    {
        $pngBase64 = 'iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAADUlEQVR42mNk+M9QDwADhgGAWjR9awAAAABJRU5ErkJggg==';
        return UploadedFile::fake()->createWithContent($name, base64_decode($pngBase64));
    }

    public function test_karyawan_can_create_finance_ticket()
    {
        $karyawan = $this->createKaryawanUser();
        Storage::fake('public');
        Storage::fake('local');

        $response = $this->actingAs($karyawan)->post(route('karyawan.tickets.store'), [
            'title' => 'Pengajuan Reimburse',
            'category' => 'reimburse',
            'priority' => 'sedang',
            'description' => 'Mohon diproses penggantian biaya perjalanan dinas.',
            'reimbursement_amount' => 150000,
            'attachment' => $this->fakeImage('bukti-bensin.jpg'),
        ]);

        $ticket = Ticket::where('title', 'Pengajuan Reimburse')->firstOrFail();

        $this->assertSame('finance', $ticket->assigned_team);
        $this->assertSame('pending', $ticket->reimbursement_status);
        $this->assertSame('150000.00', $ticket->reimbursement_amount);
        $this->assertNotNull($ticket->attachment);
        $response->assertRedirect(route('karyawan.tickets.show', $ticket));
        $response->assertSessionHas('success', "Tiket kamu berhasil dibuat dengan nomor referensi #{$ticket->ticket_code} dan diteruskan ke tim Finance.");
    }

    public function test_finance_approval_posts_pending_expense_without_changing_ticket_status(): void
    {
        $karyawan = $this->createKaryawanUser();
        Storage::fake('public');
        Storage::fake('local');
        $this->actingAs($karyawan)->post(route('karyawan.tickets.store'), [
            'title' => 'Reimburse bensin cabang',
            'category' => 'reimburse',
            'priority' => 'sedang',
            'description' => 'Penggantian bensin untuk perjalanan kerja ke kantor cabang.',
            'reimbursement_amount' => 185000,
            'attachment' => $this->fakeImage('bukti-bensin.png'),
        ])->assertRedirect();
        $ticket = Ticket::where('title', 'Reimburse bensin cabang')->firstOrFail();
        $finance = User::factory()->create(['role' => 'finance']);

        $this->actingAs($finance)
            ->get(route('finance.reimbursements'))
            ->assertOk()
            ->assertSee('Reimburse bensin cabang')
            ->assertSee('185.000');

            #make a route for post and get database

        $this->post(route('finance.reimbursements.decision', $ticket), [
            'decision' => 'approved',
        ])->assertRedirect(route('finance.reimbursements', ['status' => 'approved']));

        $transaction = FinanceTransaction::where('category', 'reimbursement')->firstOrFail();
        $this->assertDatabaseHas('tickets', [
            'id' => $ticket->id,
            'reimbursement_status' => 'approved',
            'finance_transaction_id' => $transaction->id,
            'status' => 'open',
        ]);
        $this->assertDatabaseHas('finance_transactions', [
            'id' => $transaction->id,
            'type' => 'expense',
            'status' => 'pending',
            'employee_id' => $karyawan->employee->id,
            'amount' => 185000,
        ]);
        Storage::disk('local')->assertExists($ticket->attachment);
        $this->actingAs($karyawan)->get(route('tickets.reimbursements.proof', $ticket))
            ->assertOk()
            ->assertHeader('content-disposition', 'inline');
        $this->actingAs($finance)->get(route('tickets.reimbursements.proof', $ticket))->assertOk();
        $this->actingAs($this->createKaryawanUser('Karyawan Lain'))
            ->get(route('tickets.reimbursements.proof', $ticket))
            ->assertForbidden();

        $this->actingAs($finance);
        $this->post(route('tickets.update-status', $ticket), [
            'status' => 'closed',
            'priority' => 'sedang',
        ])->assertRedirect();
        $this->assertDatabaseHas('tickets', [
            'id' => $ticket->id,
            'status' => 'closed',
            'reimbursement_status' => 'approved',
        ]);

        $transactionPeriod = \Carbon\Carbon::parse($transaction->fresh()->transaction_date)->format('Y-m');
        $this->get(route('finance.transactions', ['period' => $transactionPeriod]))
            ->assertOk()
            ->assertViewHas('period', $transactionPeriod)
            ->assertViewHas('transactions', fn ($transactions) => $transactions->contains('id', $transaction->id))
            ->assertSee('Reimburse bensin cabang')
            ->assertSee('Belum dibayar');
    }

    public function test_rejected_reimbursement_requires_a_reason_and_does_not_create_cash_transaction(): void
    {
        $karyawan = $this->createKaryawanUser();
        Storage::fake('public');
        Storage::fake('local');
        $this->actingAs($karyawan)->post(route('karyawan.tickets.store'), [
            'title' => 'Reimburse parkir kantor cabang',
            'category' => 'reimburse',
            'priority' => 'sedang',
            'description' => 'Penggantian biaya parkir saat bertugas ke kantor cabang.',
            'reimbursement_amount' => 25000,
            'attachment' => $this->fakeImage('bukti-parkir.jpg'),
        ])->assertRedirect();
        $ticket = Ticket::where('title', 'Reimburse parkir kantor cabang')->firstOrFail();
        $finance = User::factory()->create(['role' => 'finance']);

        $this->actingAs($finance)
            ->from(route('finance.reimbursements'))
            ->post(route('finance.reimbursements.decision', $ticket), ['decision' => 'rejected'])
            ->assertRedirect(route('finance.reimbursements'))
            ->assertSessionHasErrors('review_note');
        $this->assertSame('pending', $ticket->fresh()->reimbursement_status);

        $this->post(route('finance.reimbursements.decision', $ticket), [
            'decision' => 'rejected',
            'review_note' => 'Bukti tidak terbaca, mohon unggah ulang.',
        ])->assertRedirect(route('finance.reimbursements', ['status' => 'rejected']));

        $this->assertDatabaseHas('tickets', [
            'id' => $ticket->id,
            'reimbursement_status' => 'rejected',
            'reimbursement_review_note' => 'Bukti tidak terbaca, mohon unggah ulang.',
            'finance_transaction_id' => null,
        ]);
        $this->assertDatabaseCount('finance_transactions', 0);
    }

    public function test_reimbursement_ticket_requires_amount_and_image_proof(): void
    {
        $karyawan = $this->createKaryawanUser();

        $this->actingAs($karyawan)
            ->from(route('karyawan.tickets.create'))
            ->post(route('karyawan.tickets.store'), [
                'title' => 'Penggantian biaya bensin',
                'category' => 'reimburse',
                'priority' => 'sedang',
                'description' => 'Pengeluaran bensin perjalanan dinas ke kantor cabang.',
            ])
            ->assertRedirect(route('karyawan.tickets.create'))
            ->assertSessionHasErrors(['reimbursement_amount', 'attachment']);

        $this->assertDatabaseMissing('tickets', ['title' => 'Penggantian biaya bensin']);
    }

    /**
     * Fitur kirim tiket anonim telah ditiadakan sehingga tiket selalu tercatat atas nama pelapor asli.
     */
    public function test_karyawan_cannot_create_anonymous_ticket()
    {
        $karyawan = $this->createKaryawanUser();

        $this->actingAs($karyawan)->post(route('karyawan.tickets.store'), [
            'title' => 'Laporan Kendala Fasilitas',
            'category' => 'pengaduan',
            'priority' => 'tinggi',
            'description' => 'Terdapat kendala fasilitas pendingin ruangan yang perlu diperbaiki segera.',
            'is_anonymous' => 1,
        ]);

        $ticket = Ticket::where('title', 'Laporan Kendala Fasilitas')->first();
        $this->assertNotNull($ticket);
        $this->assertFalse((bool) $ticket->is_anonymous);
        $this->assertEquals($karyawan->name, $ticket->display_author);
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
        $detail->assertSee('HR Admin');
        $detail->assertDontSee('admin-live-clock');
    }

    public function test_profile_title_uses_admin_role_or_employee_position()
    {
        $hr = User::factory()->create(['role' => 'hr']);
        $this->assertSame('HR Admin', $hr->display_title);

        $finance = User::factory()->create(['role' => 'finance']);
        $this->assertSame('Finance Admin', $finance->display_title);

        $karyawan = $this->createKaryawanUser();
        $karyawan->employee->position->update(['name' => 'Custody']);

        $this->assertSame('Custody', $karyawan->display_title);
    }

    public function test_finance_and_hr_only_access_tickets_for_their_team()
    {
        $hr = User::factory()->create(['role' => 'hr']);
        $finance = User::factory()->create(['role' => 'finance']);
        $karyawan = $this->createKaryawanUser();

        $financeTicket = Ticket::create([
            'ticket_code' => Ticket::generateTicketCode(),
            'user_id' => $karyawan->id,
            'title' => 'Koreksi Slip Gaji',
            'category' => 'payroll',
            'priority' => 'sedang',
            'status' => 'open',
            'description' => 'Mohon pengecekan komponen gaji bulan ini.',
        ]);
        $hrTicket = Ticket::create([
            'ticket_code' => Ticket::generateTicketCode(),
            'user_id' => $karyawan->id,
            'title' => 'Perbaikan Data Karyawan',
            'category' => 'data_karyawan',
            'priority' => 'sedang',
            'status' => 'open',
            'description' => 'Ada kesalahan pada data alamat karyawan.',
        ]);

        $financeInbox = $this->actingAs($finance)->get(route('tickets.index'));
        $financeInbox->assertOk()->assertSee('Koreksi Slip Gaji')->assertDontSee('Perbaikan Data Karyawan');
        $this->get(route('tickets.show', $financeTicket))->assertOk();
        $this->get(route('tickets.show', $hrTicket))->assertForbidden();

        $hrInbox = $this->actingAs($hr)->get(route('tickets.index'));
        $hrInbox->assertOk()->assertSee('Perbaikan Data Karyawan')->assertDontSee('Koreksi Slip Gaji');
        $this->get(route('tickets.show', $financeTicket))->assertForbidden();
    }

    public function test_new_ticket_shows_simple_unread_label_until_ticket_is_opened()
    {
        $hr = User::factory()->create(['role' => 'hr']);
        $karyawan = $this->createKaryawanUser();
        $ticket = Ticket::create([
            'ticket_code' => Ticket::generateTicketCode(),
            'user_id' => $karyawan->id,
            'title' => 'Koreksi Data Pegawai',
            'category' => 'data_karyawan',
            'priority' => 'sedang',
            'status' => 'open',
            'description' => 'Mohon koreksi nomor telepon pada data pegawai.',
        ]);

        $this->actingAs($hr)
            ->get(route('tickets.index'))
            ->assertOk()
            ->assertSee('1 tiket baru')
            ->assertDontSee('ticket-alert-modal')
            ->assertSee('Pesan baru');
        $this->assertDatabaseHas('tickets', ['id' => $ticket->id]);

        $this->get(route('tickets.show', $ticket))->assertOk();
        $this->assertSame(0, $ticket->fresh()->team_last_read_reply_id);

        $this->get(route('tickets.index'))
            ->assertOk()
            ->assertDontSee('1 tiket baru')
            ->assertDontSee('Pesan baru');
    }

    public function test_action_counts_appear_on_hr_and_finance_menus_and_finance_dashboard()
    {
        $hr = User::factory()->create(['role' => 'hr']);
        $employeeUser = $this->createKaryawanUser();
        $employee = $employeeUser->employee;
        Leave::create([
            'employee_id' => $employee->id,
            'leave_type' => 'cuti_tahunan',
            'start_date' => now()->addDay()->toDateString(),
            'end_date' => now()->addDays(2)->toDateString(),
            'total_days' => 2,
            'reason' => 'Keperluan keluarga',
            'status' => 'pending',
        ]);

        $this->actingAs($hr)
            ->get(route('leaves.index'))
            ->assertOk()
            ->assertSee('1 perlu diproses');

        $finance = User::factory()->create(['role' => 'finance']);
        $draft = PayrollRun::create([
            'period' => now()->format('Y-m'),
            'status' => 'draft',
            'prepared_by' => $finance->id,
        ]);
        $processed = PayrollRun::create([
            'period' => now()->subMonth()->format('Y-m'),
            'status' => 'processed',
            'prepared_by' => $finance->id,
        ]);
        PayrollItem::create([
            'payroll_run_id' => $processed->id,
            'employee_id' => $employee->id,
            'employee_code' => $employee->employee_code,
            'employee_name' => $employee->full_name,
            'base_salary' => 1000000,
            'net_pay' => 1000000,
            'payment_status' => 'unpaid',
        ]);

        $this->actingAs($finance)
            ->get(route('finance.index'))
            ->assertOk()
            ->assertSee('2 tindakan')
            ->assertSee('1 draft perlu diperiksa')
            ->assertSee('1 pembayaran belum dicatat');
    }

    public function test_unread_reply_badges_clear_only_when_recipient_opens_ticket()
    {
        $hr = User::factory()->create(['role' => 'hr']);
        $karyawan = $this->createKaryawanUser();
        $ticket = Ticket::create([
            'ticket_code' => Ticket::generateTicketCode(),
            'user_id' => $karyawan->id,
            'title' => 'Perbaikan Data Rekening',
            'category' => 'data_karyawan',
            'priority' => 'sedang',
            'status' => 'open',
            'description' => 'Data rekening payroll perlu diperbarui.',
        ]);

        $this->actingAs($hr)->get(route('tickets.show', $ticket));
        $this->actingAs($karyawan)->post(route('karyawan.tickets.reply', $ticket), [
            'message' => 'Saya sudah mengirimkan data rekening yang benar.',
        ])->assertRedirect();

        $this->actingAs($hr)->get(route('tickets.index'))
            ->assertOk()
            ->assertSee('Pesan baru');
        $this->get(route('tickets.show', $ticket))->assertOk();
        $this->assertNotNull($ticket->fresh()->team_last_read_reply_id);

        $this->actingAs($hr)->post(route('tickets.reply', $ticket), [
            'message' => 'Data sudah kami perbarui, terima kasih.',
            'change_status' => 'in_progress',
        ])->assertRedirect();

        $this->actingAs($karyawan)->get(route('karyawan.tickets'))
            ->assertOk()
            ->assertSee('Balasan baru');
        $this->get(route('karyawan.tickets.show', $ticket))->assertOk();
        $this->assertSame(
            $ticket->replies()->max('id'),
            $ticket->fresh()->employee_last_read_reply_id
        );
        $this->get(route('karyawan.tickets'))
            ->assertOk()
            ->assertDontSee('Balasan baru');
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
