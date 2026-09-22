<?php

namespace App\Services;

use App\Models\PlagiarismCheck;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\Response;

class PlagiarismExportService
{
    public function __construct(
        private DocumentPageRenderer $documentPageRenderer,
    ) {}

    public function buildExportResponse(
        PlagiarismCheck $check,
        string $highlightedText,
        string $downloadName
    ): Response {
        @ini_set('memory_limit', '1024M');
        @set_time_limit(0);

        $tempDir = storage_path('app/temp/exports/' . $check->id . '_' . time());
        File::ensureDirectoryExists($tempDir);

        $coverPath = $tempDir . DIRECTORY_SEPARATOR . 'cover.pdf';
        $reportPath = $tempDir . DIRECTORY_SEPARATOR . 'report.pdf';
        $mergedPath = $tempDir . DIRECTORY_SEPARATOR . 'merged.pdf';
        $sourceImagesManifest = $tempDir . DIRECTORY_SEPARATOR . 'source_images.json';

        $filePath = Storage::disk('public')->path($check->document->file_path);
        $pageImages = $this->documentPageRenderer->renderPages($filePath, $check->document->id);

        $this->renderPartialPdf('plagiarism.export_cover', compact('check'), $coverPath);
        $this->renderPartialPdf(
            'plagiarism.export_report',
            compact('check', 'highlightedText'),
            $reportPath
        );

        $sourcePdf = null;
        if ($pageImages !== []) {
            file_put_contents($sourceImagesManifest, json_encode([
                'pages' => array_values($pageImages),
            ]));
        } else {
            $sourcePdf = $this->documentPageRenderer->resolveSourcePdf($filePath, $check->document->id);
        }

        if (($pageImages !== [] || $sourcePdf) && $this->mergePdfs(
            $mergedPath,
            $coverPath,
            $reportPath,
            $pageImages !== [] ? $sourceImagesManifest : null,
            $sourcePdf
        )) {
            @unlink($coverPath);
            @unlink($reportPath);
            if (is_file($sourceImagesManifest)) {
                @unlink($sourceImagesManifest);
            }

            return response()->download($mergedPath, $downloadName)->deleteFileAfterSend(true);
        }

        Log::warning('PDF merge unavailable, falling back to single PDF export', [
            'check_id' => $check->id,
            'source_pdf' => $sourcePdf,
            'page_images' => count($pageImages),
        ]);

        @unlink($coverPath);
        @unlink($reportPath);
        if (is_file($sourceImagesManifest)) {
            @unlink($sourceImagesManifest);
        }

        return Pdf::loadView('plagiarism.export_pdf', [
            'check' => $check,
            'highlightedText' => $highlightedText,
            'pageImages' => $pageImages,
        ])
            ->setPaper('a4', 'portrait')
            ->setOptions([
                'isHtml5ParserEnabled' => true,
                'isRemoteEnabled' => true,
                'chroot' => base_path(),
            ])
            ->download($downloadName);
    }

    private function renderPartialPdf(string $view, array $data, string $outputPath): void
    {
        Pdf::loadView($view, $data)
            ->setPaper('a4', 'portrait')
            ->setOptions([
                'isHtml5ParserEnabled' => true,
                'isRemoteEnabled' => true,
                'chroot' => base_path(),
            ])
            ->save($outputPath);
    }

    private function mergePdfs(
        string $outputPath,
        string $coverPath,
        string $reportPath,
        ?string $sourceImagesManifest = null,
        ?string $sourcePath = null
    ): bool {
        $python = $this->findPythonBinary();
        $script = base_path('scripts/merge_pdfs.py');

        if (!$python || !is_file($script)) {
            return false;
        }

        if (!$sourceImagesManifest && !$sourcePath) {
            return false;
        }

        $maxPages = (int) env('PDF_PAGE_MAX', 200);
        $jpegQuality = (int) env('PDF_PAGE_JPEG_QUALITY', 85);

        $command = sprintf(
            '%s %s %s --cover %s --report %s --max-pages %d --jpeg-quality %d',
            escapeshellarg($python),
            escapeshellarg($script),
            escapeshellarg($outputPath),
            escapeshellarg($coverPath),
            escapeshellarg($reportPath),
            $maxPages,
            $jpegQuality
        );

        if ($sourceImagesManifest) {
            $command .= ' --source-images ' . escapeshellarg($sourceImagesManifest);
        } elseif ($sourcePath) {
            $command .= ' --source ' . escapeshellarg($sourcePath);
        }

        $command .= ' 2>&1';

        $output = shell_exec($command);
        if (!is_string($output) || trim($output) === '') {
            return false;
        }

        $decoded = json_decode(trim($output), true);
        if (!is_array($decoded) || !empty($decoded['error'])) {
            Log::warning('PDF merge failed', [
                'output' => $output,
                'error' => $decoded['error'] ?? 'invalid response',
            ]);

            return false;
        }

        return is_file($outputPath) && filesize($outputPath) > 0;
    }

    private function findPythonBinary(): ?string
    {
        $candidates = array_filter([
            env('PYTHON_PATH'),
            'C:\\laragon\\bin\\python\\python-3.13\\python.exe',
            'C:\\laragon\\bin\\python\\python-3.12\\python.exe',
            'python3',
            'python',
        ]);

        foreach ($candidates as $candidate) {
            if (in_array($candidate, ['python', 'python3'], true)) {
                return $candidate;
            }

            if (is_string($candidate) && is_file($candidate)) {
                return $candidate;
            }
        }

        return null;
    }
}
