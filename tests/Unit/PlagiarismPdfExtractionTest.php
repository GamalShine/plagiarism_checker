<?php

namespace Tests\Unit;

use App\Services\PlagiarismService;
use PHPUnit\Framework\TestCase;
use ReflectionClass;
use ReflectionMethod;

class PlagiarismPdfExtractionTest extends TestCase
{
    public function test_pdf_text_validation_tolerates_invalid_utf8_bytes(): void
    {
        $service = (new ReflectionClass(PlagiarismService::class))->newInstanceWithoutConstructor();
        $method = new ReflectionMethod($service, 'isUsableExtractedText');
        $method->setAccessible(true);
        $text = str_repeat('Kalimat dokumen memiliki teks yang dapat dibaca. ', 12) . "\xFF";

        $this->assertTrue($method->invoke($service, $text, 'sample.pdf'));
    }

    public function test_pdf_text_normalization_removes_invalid_utf8_bytes_without_losing_text(): void
    {
        $service = (new ReflectionClass(PlagiarismService::class))->newInstanceWithoutConstructor();
        $method = new ReflectionMethod($service, 'normalizeExtractedText');
        $method->setAccessible(true);

        $normalized = $method->invoke($service, "Baris pertama.\xFF\r\nBaris kedua.");

        $this->assertStringContainsString('Baris pertama.', $normalized);
        $this->assertStringContainsString('Baris kedua.', $normalized);
        $this->assertTrue(mb_check_encoding($normalized, 'UTF-8'));
    }
}
