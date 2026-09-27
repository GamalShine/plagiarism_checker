<?php

namespace Tests\Feature;

use App\Models\ContactReport;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class HelpReportTest extends TestCase
{
    use RefreshDatabase;

    public function test_public_help_form_saves_report_and_selected_channel(): void
    {
        $response = $this->postJson(route('help.reports.store'), [
            'name' => 'Ayu Mahasiswa',
            'email' => 'ayu@example.com',
            'category' => 'Bug atau error',
            'message' => 'Tombol cek tidak merespons saat ditekan.',
            'channel' => 'whatsapp',
        ]);

        $response->assertCreated()
            ->assertJsonPath('message', 'Laporan berhasil disimpan.');

        $this->assertDatabaseHas('contact_reports', [
            'name' => 'Ayu Mahasiswa',
            'email' => 'ayu@example.com',
            'category' => 'Bug atau error',
            'message' => 'Tombol cek tidak merespons saat ditekan.',
            'channel' => 'whatsapp',
        ]);
    }

    public function test_help_form_rejects_unknown_channels(): void
    {
        $response = $this->postJson(route('help.reports.store'), [
            'name' => 'Ayu Mahasiswa',
            'email' => 'ayu@example.com',
            'category' => 'Bug atau error',
            'message' => 'Tombol cek tidak merespons saat ditekan.',
            'channel' => 'other',
        ]);

        $response->assertUnprocessable()
            ->assertJsonValidationErrors('channel');

        $this->assertDatabaseCount('contact_reports', 0);
    }

    public function test_regular_send_button_saves_report_for_admin_only(): void
    {
        $response = $this->postJson(route('help.reports.store'), [
            'name' => 'Ayu Mahasiswa',
            'email' => 'ayu@example.com',
            'category' => 'Kendala teknis',
            'message' => 'Halaman pemeriksaan tidak bisa dibuka.',
            'channel' => 'admin',
        ]);

        $response->assertCreated();
        $this->assertDatabaseHas('contact_reports', [
            'email' => 'ayu@example.com',
            'channel' => 'admin',
        ]);
    }

    public function test_admin_can_view_submitted_reports(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        ContactReport::create([
            'name' => 'Ayu Mahasiswa',
            'email' => 'ayu@example.com',
            'category' => 'Bug atau error',
            'message' => 'Tombol cek tidak merespons saat ditekan.',
            'channel' => 'email',
        ]);

        $response = $this->actingAs($admin)->get(route('admin.reports.index', ['channel' => 'email']));

        $response->assertOk()
            ->assertSee('Ayu Mahasiswa')
            ->assertSee('Bug atau error')
            ->assertSee('Tombol cek tidak merespons saat ditekan.')
            ->assertSee('Laporan');
    }

    public function test_regular_user_cannot_view_admin_reports(): void
    {
        $user = User::factory()->create(['role' => 'user']);

        $this->actingAs($user)
            ->get(route('admin.reports.index'))
            ->assertForbidden();
    }

    public function test_admin_can_filter_reports_by_website_email_and_whatsapp(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        foreach (['admin' => 'Website report', 'email' => 'Email report', 'whatsapp' => 'WhatsApp report'] as $channel => $message) {
            ContactReport::create([
                'name' => 'Test Reporter',
                'email' => 'reporter@example.com',
                'category' => 'Pertanyaan',
                'message' => $message,
                'channel' => $channel,
            ]);
        }

        foreach (['admin' => 'Website', 'email' => 'Email', 'whatsapp' => 'WhatsApp'] as $channel => $label) {
            $response = $this->actingAs($admin)
                ->get(route('admin.reports.index', ['channel' => $channel]));

            $response->assertOk()
                ->assertSee($label)
                ->assertSee($label . ' report');

            foreach (['Website', 'Email', 'WhatsApp'] as $otherLabel) {
                if ($otherLabel !== $label) {
                    $response->assertDontSee($otherLabel . ' report');
                }
            }
        }
    }
}