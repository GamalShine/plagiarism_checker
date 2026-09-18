<?php

namespace App\Services;

use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use PhpOffice\PhpWord\IOFactory;
use PhpOffice\PhpWord\Settings;

class DocumentPageRenderer
{
    private const MAX_PAGES = 200;

    public function renderPages(string $filePath, int $documentId): array
    {
        $sourcePdf = $this->resolveSourcePdf($filePath, $documentId);
        if (!$sourcePdf) {
            return [];
        }

        return $this->renderPdfPages($sourcePdf, $documentId, $filePath);
    }

    public function resolveSourcePdf(string $filePath, int $documentId): ?string
    {
        if (!is_file($filePath)) {
            return null;
        }

        $extension = strtolower(pathinfo($filePath, PATHINFO_EXTENSION));
        if ($extension === 'pdf') {
            return $filePath;
        }

        if ($extension !== 'docx') {
            return null;
        }

        $cacheDir = $this->cacheDirectory($documentId, $filePath);
        $cachedPdf = $cacheDir . DIRECTORY_SEPARATOR . 'source.pdf';

        if (is_file($cachedPdf) && filesize($cachedPdf) > 0) {
            return $cachedPdf;
        }

        File::ensureDirectoryExists($cacheDir);

        $pdfPath = $this->convertDocxToPdf($filePath, $cacheDir);
        if (!$pdfPath) {
            return null;
        }

        if ($pdfPath !== $cachedPdf) {
            @copy($pdfPath, $cachedPdf);
        }

        return is_file($cachedPdf) ? $cachedPdf : $pdfPath;
    }

    public function resolveSourcePdfWithoutShell(string $filePath, int $documentId, array $highlights = []): ?string
    {
        if (! is_file($filePath)) {
            return null;
        }

        $extension = strtolower(pathinfo($filePath, PATHINFO_EXTENSION));
        if ($extension === 'pdf') {
            return $filePath;
        }

        if ($extension !== 'docx') {
            return null;
        }

        $cacheDir = $this->cacheDirectory($documentId, $filePath);
        $highlightedCacheKey = md5(json_encode(array_map(
            fn ($highlight) => [
                'text' => (string) ($highlight->original_text ?? ''),
                'color' => (string) ($highlight->color_code ?? $highlight->source?->color_code ?? '#FFF3A3'),
            ],
            $highlights,
        ),
            JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES,
        ));
        $cachedPdf = $highlights !== []
            ? $cacheDir . DIRECTORY_SEPARATOR . 'highlighted-source-' . $highlightedCacheKey . '.pdf'
            : $cacheDir . DIRECTORY_SEPARATOR . 'phpword-source.pdf';

        if (is_file($cachedPdf) && filesize($cachedPdf) > 0) {
            return $cachedPdf;
        }

        File::ensureDirectoryExists($cacheDir);

        $pdfPath = null;
        if (function_exists('shell_exec')) {
            if (PHP_OS_FAMILY === 'Windows' && $highlights !== []) {
                $pdfPath = $this->convertDocxWithWordHighlights($filePath, $cacheDir, $highlights);
                if ($pdfPath) {
                    if ($pdfPath !== $cachedPdf) {
                        @copy($pdfPath, $cachedPdf);
                    }

                    return is_file($cachedPdf) ? $cachedPdf : $pdfPath;
                }

                return null;
            }

            foreach ([
                fn () => $this->convertDocxWithMicrosoftWord($filePath, $cacheDir),
                fn () => $this->convertDocxWithLibreOffice($filePath, $cacheDir),
                fn () => $this->convertDocxWithBrowser($filePath, $cacheDir),
            ] as $convert) {
                $pdfPath = $convert();
                if ($pdfPath) {
                    break;
                }
            }
        }

        $pdfPath ??= $this->convertDocxWithPhpWord($filePath, $cacheDir);

        if ($pdfPath && $pdfPath !== $cachedPdf && is_file($pdfPath)) {
            @copy($pdfPath, $cachedPdf);
        }

        return is_file($cachedPdf) && filesize($cachedPdf) > 0 ? $cachedPdf : null;
    }

    private function convertDocxWithWordHighlights(string $filePath, string $cacheDir, array $highlights): ?string
    {
        $script = base_path('scripts/docx_to_highlighted_pdf.ps1');
        if (! is_file($script)) {
            return null;
        }

        $outputPath = $cacheDir . DIRECTORY_SEPARATOR . 'highlighted-source.pdf';
        $highlightsPath = $cacheDir . DIRECTORY_SEPARATOR . 'highlights.json';
        @unlink($outputPath);
        file_put_contents($highlightsPath, json_encode(array_map(
            fn ($highlight) => [
                'text' => $highlight->original_text,
                'color' => $highlight->color_code ?? $highlight->source?->color_code ?? '#FFF3A3',
            ],
            $highlights,
        ), JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));

        $command = sprintf(
            'powershell -NoProfile -ExecutionPolicy Bypass -File %s -InputPath %s -OutputPath %s -HighlightsPath %s 2>&1',
            escapeshellarg($script),
            escapeshellarg($filePath),
            escapeshellarg($outputPath),
            escapeshellarg($highlightsPath),
        );

        $output = shell_exec($command);
        if (is_file($outputPath) && filesize($outputPath) > 0) {
            return $outputPath;
        }

        Log::warning('DOCX highlight conversion failed', [
            'file' => $filePath,
            'highlight_count' => count($highlights),
            'output' => $output,
        ]);

        return null;
    }

    private function renderPdfPages(string $pdfPath, int $documentId, string $originalFilePath): array
    {
        $cacheDir = $this->cacheDirectory($documentId, $originalFilePath);
        $manifestPath = $cacheDir . DIRECTORY_SEPARATOR . 'manifest.json';

        if (is_file($manifestPath)) {
            $cached = json_decode((string) file_get_contents($manifestPath), true);
            if (is_array($cached) && $this->cachedImagesExist($cached)) {
                return $cached;
            }
        }

        File::ensureDirectoryExists($cacheDir);

        $images = $this->convertPdfToImages($pdfPath, $cacheDir);
        if ($images !== []) {
            file_put_contents($manifestPath, json_encode($images));
        }

        return $images;
    }

    private function cacheDirectory(int $documentId, string $filePath): string
    {
        $dpi = (int) env('PDF_PAGE_DPI', 120);
        $jpegQuality = (int) env('PDF_PAGE_JPEG_QUALITY', 85);
        $hash = md5(implode('|', [
            $filePath,
            (string) (filemtime($filePath) ?: 0),
            (string) (filesize($filePath) ?: 0),
            (string) $dpi,
            (string) $jpegQuality,
            'native-v2',
            'word-v2-colored-highlights',
        ]));

        return Storage::disk('local')->path("document-previews/{$documentId}/{$hash}");
    }

    private function cachedImagesExist(array $images): bool
    {
        foreach ($images as $item) {
            $path = is_array($item) ? ($item['path'] ?? '') : $item;
            if (!is_string($path) || !is_file($path)) {
                return false;
            }
        }

        return $images !== [];
    }

    private function convertDocxToPdf(string $filePath, string $cacheDir): ?string
    {
        foreach ([
            fn () => $this->convertDocxWithMicrosoftWord($filePath, $cacheDir),
            fn () => $this->convertDocxWithLibreOffice($filePath, $cacheDir),
            fn () => $this->convertDocxWithBrowser($filePath, $cacheDir),
            fn () => $this->convertDocxWithPhpWord($filePath, $cacheDir),
        ] as $convert) {
            $pdf = $convert();
            if ($pdf) {
                return $pdf;
            }
        }

        return null;
    }

    private function convertDocxWithMicrosoftWord(string $filePath, string $cacheDir): ?string
    {
        if (PHP_OS_FAMILY !== 'Windows' || ! function_exists('shell_exec')) {
            return null;
        }

        if (!filter_var(env('WORD_COM_ENABLED', true), FILTER_VALIDATE_BOOL)) {
            return null;
        }

        $script = base_path('scripts/docx_to_pdf.ps1');
        if (!is_file($script)) {
            return null;
        }

        $outputPath = $cacheDir . DIRECTORY_SEPARATOR . 'converted.pdf';

        $command = sprintf(
            'powershell -NoProfile -ExecutionPolicy Bypass -File %s -InputPath %s -OutputPath %s 2>&1',
            escapeshellarg($script),
            escapeshellarg($filePath),
            escapeshellarg($outputPath)
        );

        shell_exec($command);

        if (!is_file($outputPath) || filesize($outputPath) <= 0) {
            Log::warning('DOCX to PDF conversion via Microsoft Word failed', [
                'file' => $filePath,
            ]);

            return null;
        }

        return $outputPath;
    }

    private function convertDocxWithLibreOffice(string $filePath, string $cacheDir): ?string
    {
        if (! function_exists('shell_exec')) {
            return null;
        }

        $binary = $this->findLibreOfficeBinary();
        if (!$binary) {
            return null;
        }

        $command = sprintf(
            '%s --headless --nologo --nofirststartwizard --convert-to pdf --outdir %s %s 2>&1',
            escapeshellarg($binary),
            escapeshellarg($cacheDir),
            escapeshellarg($filePath)
        );

        shell_exec($command);

        $expected = $cacheDir . DIRECTORY_SEPARATOR . pathinfo($filePath, PATHINFO_FILENAME) . '.pdf';
        if (is_file($expected) && filesize($expected) > 0) {
            return $expected;
        }

        $generated = glob($cacheDir . DIRECTORY_SEPARATOR . '*.pdf') ?: [];
        foreach ($generated as $candidate) {
            if (is_file($candidate) && filesize($candidate) > 0) {
                return $candidate;
            }
        }

        return null;
    }

    private function convertDocxWithPhpWord(string $filePath, string $cacheDir): ?string
    {
        try {
            @ini_set('memory_limit', '1024M');
            @set_time_limit(0);

            Settings::setPdfRendererName(Settings::PDF_RENDERER_DOMPDF);
            Settings::setPdfRendererPath(base_path('vendor/dompdf/dompdf'));

            $phpWord = IOFactory::load($filePath);
            $outputPath = $cacheDir . DIRECTORY_SEPARATOR . 'converted.pdf';
            IOFactory::createWriter($phpWord, 'PDF')->save($outputPath);

            return is_file($outputPath) && filesize($outputPath) > 0 ? $outputPath : null;
        } catch (\Throwable $e) {
            Log::warning('DOCX to PDF conversion failed', [
                'file' => $filePath,
                'error' => $e->getMessage(),
            ]);

            return null;
        }
    }

    private function convertDocxWithBrowser(string $filePath, string $cacheDir): ?string
    {
        if (! function_exists('shell_exec')) {
            return null;
        }

        $browser = $this->findBrowserBinary();
        if (!$browser) {
            return null;
        }

        try {
            @ini_set('memory_limit', '2048M');
            @set_time_limit(0);

            $phpWord = IOFactory::load($filePath);
            $htmlPath = $cacheDir . DIRECTORY_SEPARATOR . 'document.html';
            IOFactory::createWriter($phpWord, 'HTML')->save($htmlPath);

            $pdfPath = $cacheDir . DIRECTORY_SEPARATOR . 'converted.pdf';
            $url = 'file:///' . str_replace('\\', '/', $htmlPath);

            $command = sprintf(
                '%s --headless --disable-gpu --no-pdf-header-footer --print-to-pdf=%s %s 2>&1',
                escapeshellarg($browser),
                escapeshellarg($pdfPath),
                escapeshellarg($url)
            );

            shell_exec($command);

            return is_file($pdfPath) && filesize($pdfPath) > 0 ? $pdfPath : null;
        } catch (\Throwable $e) {
            Log::warning('DOCX to PDF browser conversion failed', [
                'file' => $filePath,
                'error' => $e->getMessage(),
            ]);

            return null;
        }
    }

    private function convertPdfToImages(string $pdfPath, string $cacheDir): array
    {
        $pythonImages = $this->convertPdfWithPython($pdfPath, $cacheDir);
        if ($pythonImages !== []) {
            return $pythonImages;
        }

        return $this->convertPdfWithPdftoppm($pdfPath, $cacheDir);
    }

    private function convertPdfWithPython(string $pdfPath, string $cacheDir): array
    {
        if (! function_exists('shell_exec')) {
            return [];
        }

        $python = $this->findPythonBinary();
        $script = base_path('scripts/pdf_to_images.py');

        if (!$python || !is_file($script)) {
            return [];
        }

        $dpi = (int) env('PDF_PAGE_DPI', 120);
        $jpegQuality = (int) env('PDF_PAGE_JPEG_QUALITY', 85);
        $maxPages = (int) env('PDF_PAGE_MAX', self::MAX_PAGES);

        $command = sprintf(
            '%s %s %s %s --dpi %d --jpeg-quality %d --max-pages %d 2>&1',
            escapeshellarg($python),
            escapeshellarg($script),
            escapeshellarg($pdfPath),
            escapeshellarg($cacheDir),
            $dpi,
            $jpegQuality,
            $maxPages
        );

        $output = shell_exec($command);
        $resultFile = $cacheDir . DIRECTORY_SEPARATOR . 'conversion_result.json';

        if (is_file($resultFile)) {
            $decoded = json_decode((string) file_get_contents($resultFile), true);
        } elseif (is_string($output) && trim($output) !== '') {
            $decoded = json_decode(trim($output), true);
        } else {
            return [];
        }
        if (!is_array($decoded)) {
            Log::warning('PDF to image conversion returned invalid JSON', ['output' => $output]);
            return [];
        }

        if (!empty($decoded['error'])) {
            Log::warning('PDF to image conversion failed', ['error' => $decoded['error']]);
            return [];
        }

        $pages = array_values(array_filter(
            $decoded['pages'] ?? [],
            fn ($page) => is_array($page) && is_string($page['path'] ?? null) && is_file($page['path'])
        ));

        if ($pages !== []) {
            return $pages;
        }

        $images = array_values(array_filter(
            $decoded['images'] ?? [],
            fn ($path) => is_string($path) && is_file($path)
        ));

        return array_map(
            fn (string $path) => ['path' => $path],
            $images
        );
    }

    private function convertPdfWithPdftoppm(string $pdfPath, string $cacheDir): array
    {
        if (! function_exists('shell_exec')) {
            return [];
        }

        $binary = $this->findPdftoppmBinary();
        if (!$binary) {
            return [];
        }

        $prefix = $cacheDir . DIRECTORY_SEPARATOR . 'page';
        $dpi = (int) env('PDF_PAGE_DPI', 120);

        $command = sprintf(
            '%s -png -r %d -f 1 -l %d %s %s 2>&1',
            escapeshellarg($binary),
            $dpi,
            self::MAX_PAGES,
            escapeshellarg($pdfPath),
            escapeshellarg($prefix)
        );

        shell_exec($command);

        $images = glob($cacheDir . DIRECTORY_SEPARATOR . 'page*.png') ?: [];
        natsort($images);

        return array_map(
            fn (string $path) => ['path' => $path],
            array_values($images)
        );
    }

    private function findPythonBinary(): ?string
    {
        $candidates = array_filter([
            env('PYTHON_PATH'),
            'C:\\laragon\\bin\\python\\python-3.13\\python.exe',
            'C:\\laragon\\bin\\python\\python-3.12\\python.exe',
            base_path('.venv/bin/python'),
            base_path('venv/bin/python'),
            '/usr/bin/python3',
            '/usr/local/bin/python3',
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

    private function findLibreOfficeBinary(): ?string
    {
        $candidates = array_filter([
            env('LIBREOFFICE_PATH'),
            'C:\\Program Files\\LibreOffice\\program\\soffice.exe',
            'C:\\Program Files (x86)\\LibreOffice\\program\\soffice.exe',
            'soffice',
        ]);

        foreach ($candidates as $candidate) {
            if ($candidate === 'soffice') {
                return $candidate;
            }

            if (is_string($candidate) && is_file($candidate)) {
                return $candidate;
            }
        }

        return null;
    }

    private function findPdftoppmBinary(): ?string
    {
        $candidates = array_filter([
            env('PDFTOPPM_PATH'),
            'C:\\laragon\\bin\\git\\mingw64\\bin\\pdftoppm.exe',
            'pdftoppm',
        ]);

        foreach ($candidates as $candidate) {
            if ($candidate === 'pdftoppm') {
                return $candidate;
            }

            if (is_string($candidate) && is_file($candidate)) {
                return $candidate;
            }
        }

        return null;
    }

    private function findBrowserBinary(): ?string
    {
        $candidates = array_filter([
            env('BROWSER_PDF_PATH'),
            'C:\\Program Files (x86)\\Microsoft\\Edge\\Application\\msedge.exe',
            'C:\\Program Files\\Microsoft\\Edge\\Application\\msedge.exe',
            'C:\\Program Files\\Google\\Chrome\\Application\\chrome.exe',
            'C:\\Program Files (x86)\\Google\\Chrome\\Application\\chrome.exe',
        ]);

        foreach ($candidates as $candidate) {
            if (is_string($candidate) && is_file($candidate)) {
                return $candidate;
            }
        }

        return null;
    }
}
