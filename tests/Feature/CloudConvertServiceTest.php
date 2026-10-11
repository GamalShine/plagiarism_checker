<?php

namespace Tests\Feature;

use App\Services\CloudConvertService;
use App\Services\DocumentPageRenderer;
use Illuminate\Support\Facades\Http;
use Mockery;
use Tests\TestCase;

class CloudConvertServiceTest extends TestCase
{
    public function test_it_uploads_docx_converts_downloads_pdf_and_deletes_cloud_job(): void
    {
        config([
            'services.cloudconvert.api_key' => 'test-cloudconvert-key',
            'services.cloudconvert.api_url' => 'https://api.cloudconvert.test/v2',
            'services.cloudconvert.max_wait_seconds' => 10,
            'services.cloudconvert.poll_interval_seconds' => 1,
        ]);

        Http::fake(function ($request) {
            $url = $request->url();

            if ($url === 'https://api.cloudconvert.test/v2/jobs' && $request->method() === 'POST') {
                return Http::response(['data' => [
                    'id' => 'job-123',
                    'status' => 'processing',
                    'tasks' => [[
                        'name' => 'import-docx',
                        'operation' => 'import/upload',
                        'result' => [
                            'form' => [
                                'url' => 'https://upload.cloudconvert.test/form',
                                'parameters' => ['token' => 'upload-token'],
                            ],
                        ],
                    ]],
                ]]);
            }

            if ($url === 'https://upload.cloudconvert.test/form') {
                return Http::response('', 204);
            }

            if (str_starts_with($url, 'https://api.cloudconvert.test/v2/jobs/job-123') && $request->method() === 'GET') {
                return Http::response(['data' => [
                    'id' => 'job-123',
                    'status' => 'finished',
                    'tasks' => [[
                        'name' => 'export-pdf',
                        'operation' => 'export/url',
                        'status' => 'finished',
                        'result' => ['files' => [[
                            'filename' => 'converted.pdf',
                            'url' => 'https://download.cloudconvert.test/converted.pdf',
                        ]]],
                    ]],
                ]]);
            }

            if ($url === 'https://download.cloudconvert.test/converted.pdf') {
                return Http::response('%PDF-1.7 converted', 200);
            }

            if ($url === 'https://api.cloudconvert.test/v2/jobs/job-123' && $request->method() === 'DELETE') {
                return Http::response('', 204);
            }

            return Http::response(['message' => 'unexpected request'], 500);
        });

        $inputPath = tempnam(sys_get_temp_dir(), 'cloudconvert_input_');
        $outputPath = tempnam(sys_get_temp_dir(), 'cloudconvert_output_');
        file_put_contents($inputPath, 'fake docx bytes');

        try {
            $this->assertTrue(app(CloudConvertService::class)->convertDocxToPdf($inputPath, $outputPath));
            $this->assertSame('%PDF-1.7 converted', file_get_contents($outputPath));
            Http::assertSent(fn ($request) => $request->url() === 'https://upload.cloudconvert.test/form'
                && $request->method() === 'POST'
                && str_starts_with(implode('', $request->header('Content-Type')), 'multipart/form-data'));
            Http::assertSent(fn ($request) => $request->url() === 'https://api.cloudconvert.test/v2/jobs/job-123'
                && $request->method() === 'DELETE');
        } finally {
            @unlink($inputPath);
            @unlink($outputPath);
        }
    }

    public function test_conversion_is_skipped_when_api_key_is_not_configured(): void
    {
        config(['services.cloudconvert.api_key' => null]);
        Http::fake();

        $inputPath = tempnam(sys_get_temp_dir(), 'cloudconvert_input_');
        $outputPath = tempnam(sys_get_temp_dir(), 'cloudconvert_output_');
        file_put_contents($inputPath, 'fake docx bytes');

        try {
            $this->assertFalse(app(CloudConvertService::class)->convertDocxToPdf($inputPath, $outputPath));
            Http::assertNothingSent();
        } finally {
            @unlink($inputPath);
            @unlink($outputPath);
        }
    }

    public function test_configured_cloudconvert_failure_does_not_silently_fallback_to_reflowed_docx_pdf(): void
    {
        $converter = Mockery::mock(CloudConvertService::class);
        $converter->shouldReceive('isConfigured')->once()->andReturn(true);
        $converter->shouldReceive('convertDocxToPdf')->once()->andReturn(false);
        $this->app->instance(CloudConvertService::class, $converter);

        $inputPath = tempnam(sys_get_temp_dir(), 'cloudconvert_input_') . '.docx';
        file_put_contents($inputPath, 'DOCX fixture');

        try {
            $renderer = new DocumentPageRenderer();
            $method = new \ReflectionMethod($renderer, 'convertDocxToPdf');
            $method->setAccessible(true);

            $this->expectException(\RuntimeException::class);
            $this->expectExceptionMessage('Konversi DOCX ke PDF melalui CloudConvert gagal');
            $method->invoke($renderer, $inputPath, sys_get_temp_dir());
        } finally {
            @unlink($inputPath);
        }
    }
}
