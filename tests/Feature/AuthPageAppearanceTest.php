<?php

namespace Tests\Feature;

use Tests\TestCase;

class AuthPageAppearanceTest extends TestCase
{
    public function test_login_page_uses_a_light_background_and_renders(): void
    {
        $this->assertSame('Asia/Jakarta', config('app.timezone'));

        $this->get(route('login'))
            ->assertOk()
            ->assertSee('from-sky-50 via-white to-indigo-50')
            ->assertSee('TalentaCore');
    }

    public function test_change_password_page_uses_a_light_background_and_renders(): void
    {
        $this->get(route('password.change'))
            ->assertOk()
            ->assertSee('from-sky-50 via-white to-indigo-50')
            ->assertSee('Ubah Password')
            ->assertSee('name="login"', false)
            ->assertSee('w-full px-4 py-3 rounded-xl border border-slate-200 bg-white text-sm', false)
            ->assertSee('Kembali')
            ->assertSee('window.history.back()', false)
            ->assertSee('inline-flex items-center justify-center rounded-xl border border-slate-200 bg-white', false)
            ->assertSee('overflow-x-hidden', false)
            ->assertDontSee('Kembali ke Login')
            ->assertDontSee('← Kembali')
            ->assertDontSee('class="...class yang sudah ada..."', false);
    }

    public function test_employee_portal_uses_a_realtime_clock_fixed_to_wib(): void
    {
        $this->assertStringContainsString(
            "timeZone: 'Asia/Jakarta'",
            file_get_contents(resource_path('views/karyawan/home.blade.php'))
        );

        $this->assertStringContainsString(
            'id="admin-live-clock"',
            file_get_contents(resource_path('views/layouts/admin.blade.php'))
        );
    }
}
