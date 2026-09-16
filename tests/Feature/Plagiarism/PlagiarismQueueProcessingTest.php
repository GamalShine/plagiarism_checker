<?php

namespace Tests\Feature\Plagiarism;

use App\Jobs\ProcessPlagiarismCheck;
use App\Models\Payment;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class PlagiarismQueueProcessingTest extends TestCase
{
    use RefreshDatabase;

    public function test_payment_confirmation_dispatches_background_processing_job(): void
    {
        Queue::fake();
        Storage::fake('local');
        Storage::fake('public');

        $user = User::factory()->create([
            'role' => 'admin',
            'email_verified_at' => now(),
        ]);
        $this->withoutMiddleware([
            \Illuminate\Auth\Middleware\EnsureEmailIsVerified::class,
            \App\Http\Middleware\EnsurePackagePaymentCompleted::class,
        ]);

        $paymentPath = 'temp_payments/test-upload.txt';
        Storage::disk('local')->put($paymentPath, "This is a sample text document for plagiarism checking. It contains several sentences and is long enough to be processed normally.\n\nThis sentence is included to help verify the queue flow and document extraction logic.");

        $payment = Payment::create([
            'user_id' => $user->id,
            'order_id' => 'PC-QUEUE-TEST-' . time(),
            'temp_file_path' => $paymentPath,
            'original_filename' => 'test-upload.txt',
            'sources' => ['web', 'google_scholar'],
            'amount' => 10000,
            'status' => 'pending',
        ]);

        $response = $this->actingAs($user)->post(route('user.payment.confirm', $payment->order_id));

        $response->assertRedirect();
        $payment->refresh();
        $this->assertNotNull($payment->plagiarism_check_id);

        Queue::assertPushed(ProcessPlagiarismCheck::class, function (ProcessPlagiarismCheck $job) use ($payment) {
            return $job->checkId === $payment->plagiarism_check_id;
        });
    }

    public function test_selected_chapters_are_forwarded_to_the_processing_job(): void
    {
        $job = new ProcessPlagiarismCheck(42, ['1', '3']);

        $this->assertSame(42, $job->checkId);
        $this->assertSame(['1', '3'], $job->chapters);
    }

    public function test_selected_chapters_are_used_to_filter_document_text(): void
    {
        $service = app(\App\Services\PlagiarismService::class);
        $method = new \ReflectionMethod($service, 'filterByChapters');
        $method->setAccessible(true);

        $text = "BAB I\nParagraf satu\n\nBAB II\nParagraf dua\n\nBAB III\nParagraf tiga\n";
        $filtered = $method->invoke($service, $text, ['1']);

        $this->assertStringContainsString('BAB I', $filtered);
        $this->assertStringNotContainsString('BAB II', $filtered);
        $this->assertStringNotContainsString('BAB III', $filtered);
    }

    public function test_selected_chapter_filter_does_not_fall_back_to_the_full_document(): void
    {
        $service = app(\App\Services\PlagiarismService::class);
        $method = new \ReflectionMethod($service, 'filterByChapters');
        $method->setAccessible(true);

        $text = "Pendahuluan tanpa heading yang dikenali.\nIsi dokumen penuh.\n";
        $filtered = $method->invoke($service, $text, ['4']);

        $this->assertSame('', $filtered);
    }

    public function test_numeric_heading_1_sections_are_filtered_before_plagiarism_check(): void
    {
        $service = app(\App\Services\PlagiarismService::class);
        $method = new \ReflectionMethod($service, 'filterByChapters');
        $method->setAccessible(true);

        $text = "1. Pendahuluan\nParagraf awal.\n\n2. Metodologi\nParagraf metode.\n\n3. Kesimpulan\nParagraf akhir.\n";
        $filtered = $method->invoke($service, $text, ['1', '3']);

        $this->assertStringContainsString('1. PENDAHULUAN', $filtered);
        $this->assertStringContainsString('3. KESIMPULAN', $filtered);
        $this->assertStringNotContainsString('2. METODOLOGI', $filtered);
    }

    public function test_section_parsing_ignores_invalid_utf8_bytes(): void
    {
        $service = app(\App\Services\PlagiarismService::class);
        $method = new \ReflectionMethod($service, 'filterByChapters');
        $method->setAccessible(true);

        $text = "BAB I\nParagraf awal.\n\nBAB II\nParagraf dua.\n";
        $text = str_replace("Paragraf awal.", "Paragraf\xFFawal.", $text);

        $filtered = $method->invoke($service, $text, ['1']);

        $this->assertStringContainsString('BAB I', $filtered);
        $this->assertStringNotContainsString('BAB II', $filtered);
    }

    public function test_lightweight_pdf_export_flag_disables_heavy_source_rendering(): void
    {
        $service = app(\App\Services\PlagiarismExportService::class);

        putenv('PDF_LIGHTWEIGHT=false');
        $_ENV['PDF_LIGHTWEIGHT'] = 'false';

        $this->assertFalse($service->shouldUseLightweightPdfExport());

        putenv('PDF_LIGHTWEIGHT');
        unset($_ENV['PDF_LIGHTWEIGHT']);
    }

    public function test_export_service_builds_highlighted_source_pdf_for_document_export(): void
    {
        $service = app(\App\Services\PlagiarismExportService::class);

        $this->assertTrue(method_exists($service, 'buildHighlightedSourcePdf'));
    }
}
