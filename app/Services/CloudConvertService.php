<?php

namespace App\Services;

use Illuminate\Http\Client\PendingRequest;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use RuntimeException;
use Throwable;

class CloudConvertService
{
    public function isConfigured(): bool
    {
        return filled(config('services.cloudconvert.api_key'));
    }

    public function convertDocxToPdf(string $inputPath, string $outputPath): bool
    {
        if (! $this->isConfigured() || ! is_file($inputPath)) {
            return false;
        }

        $apiUrl = rtrim((string) config('services.cloudconvert.api_url'), '/');
        $apiKey = (string) config('services.cloudconvert.api_key');
        $jobId = null;

        try {
            $jobResponse = $this->apiRequest($apiUrl, $apiKey)
                ->post($apiUrl . '/jobs', [
                    'tag' => 'plagiarism-docx-' . hash('sha256', $inputPath . '|' . (string) filemtime($inputPath)),
                    'tasks' => [
                        'import-docx' => ['operation' => 'import/upload'],
                        'convert-pdf' => [
                            'operation' => 'convert',
                            'input' => 'import-docx',
                            'input_format' => 'docx',
                            'output_format' => 'pdf',
                            'filename' => 'converted.pdf',
                        ],
                        'export-pdf' => [
                            'operation' => 'export/url',
                            'input' => 'convert-pdf',
                        ],
                    ],
                ]);

            if (! $jobResponse->successful()) {
                throw new RuntimeException('CloudConvert job creation returned HTTP ' . $jobResponse->status());
            }

            $job = $jobResponse->json('data', []);
            $jobId = $job['id'] ?? null;
            if (! is_string($jobId) || $jobId === '') {
                throw new RuntimeException('CloudConvert did not return a job ID.');
            }

            $importTask = $this->findTask($job, 'import-docx');
            $uploadForm = $importTask['result']['form'] ?? null;
            if (! is_array($uploadForm) || empty($uploadForm['url']) || ! is_array($uploadForm['parameters'] ?? null)) {
                $job = $this->fetchJob($apiUrl, $apiKey, $jobId);
                $importTask = $this->findTask($job, 'import-docx');
                $uploadForm = $importTask['result']['form'] ?? null;
            }

            if (! is_array($uploadForm) || empty($uploadForm['url']) || ! is_array($uploadForm['parameters'] ?? null)) {
                throw new RuntimeException('CloudConvert did not provide the temporary upload form.');
            }

            $uploadStream = fopen($inputPath, 'rb');
            if ($uploadStream === false) {
                throw new RuntimeException('Unable to open the uploaded DOCX for conversion.');
            }

            try {
                $uploadResponse = Http::timeout((int) config('services.cloudconvert.request_timeout', 30))
                    ->attach('file', $uploadStream, basename($inputPath))
                    ->post((string) $uploadForm['url'], $uploadForm['parameters']);
            } finally {
                fclose($uploadStream);
            }

            if (! $uploadResponse->successful()) {
                throw new RuntimeException('CloudConvert file upload returned HTTP ' . $uploadResponse->status());
            }

            $job = $this->waitForJob($apiUrl, $apiKey, $jobId);
            if (($job['status'] ?? null) !== 'finished') {
                $failedTask = collect($job['tasks'] ?? [])->firstWhere('status', 'error');
                $message = $failedTask['message'] ?? $job['message'] ?? 'CloudConvert conversion did not finish successfully.';
                throw new RuntimeException((string) $message);
            }

            $exportTask = $this->findTask($job, 'export-pdf');
            $downloadUrl = $exportTask['result']['files'][0]['url'] ?? null;
            if (! is_string($downloadUrl) || $downloadUrl === '') {
                throw new RuntimeException('CloudConvert finished without returning a PDF download URL.');
            }

            $download = Http::timeout((int) config('services.cloudconvert.request_timeout', 30))->get($downloadUrl);
            if (! $download->successful() || $download->body() === '') {
                throw new RuntimeException('Unable to download the converted PDF from CloudConvert.');
            }

            if (file_put_contents($outputPath, $download->body()) === false || ! is_file($outputPath) || filesize($outputPath) === 0) {
                throw new RuntimeException('Unable to store the converted PDF.');
            }

            return true;
        } catch (Throwable $exception) {
            @unlink($outputPath);
            Log::warning('CloudConvert DOCX-to-PDF conversion failed; local converters will be tried.', [
                'job_id' => $jobId,
                'error' => $exception->getMessage(),
            ]);

            return false;
        } finally {
            if (is_string($jobId) && $jobId !== '') {
                try {
                    $this->apiRequest($apiUrl, $apiKey)->delete($apiUrl . '/jobs/' . rawurlencode($jobId));
                } catch (Throwable $exception) {
                    Log::notice('Unable to delete the completed CloudConvert job immediately.', [
                        'job_id' => $jobId,
                        'error' => $exception->getMessage(),
                    ]);
                }
            }
        }
    }

    private function apiRequest(string $apiUrl, string $apiKey): PendingRequest
    {
        return Http::withToken($apiKey)
            ->acceptJson()
            ->timeout((int) config('services.cloudconvert.request_timeout', 30));
    }

    private function fetchJob(string $apiUrl, string $apiKey, string $jobId): array
    {
        $response = $this->apiRequest($apiUrl, $apiKey)
            ->get($apiUrl . '/jobs/' . rawurlencode($jobId), ['include' => 'tasks']);

        if (! $response->successful()) {
            throw new RuntimeException('CloudConvert job status returned HTTP ' . $response->status());
        }

        return $response->json('data', []);
    }

    private function waitForJob(string $apiUrl, string $apiKey, string $jobId): array
    {
        $deadline = microtime(true) + max(10, (int) config('services.cloudconvert.max_wait_seconds', 240));
        $pollInterval = max(1, (int) config('services.cloudconvert.poll_interval_seconds', 3));

        do {
            $job = $this->fetchJob($apiUrl, $apiKey, $jobId);
            $status = $job['status'] ?? null;
            if (in_array($status, ['finished', 'error'], true)) {
                return $job;
            }

            if (microtime(true) >= $deadline) {
                throw new RuntimeException('CloudConvert conversion exceeded the configured wait time.');
            }

            sleep($pollInterval);
        } while (true);
    }

    private function findTask(array $job, string $name): array
    {
        foreach ($job['tasks'] ?? [] as $task) {
            if (($task['name'] ?? null) === $name) {
                return $task;
            }
        }

        throw new RuntimeException('CloudConvert job is missing task ' . $name . '.');
    }
}
