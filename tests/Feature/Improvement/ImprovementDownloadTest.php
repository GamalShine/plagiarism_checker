<?php

namespace Tests\Feature\Improvement;

use App\Models\Document;
use App\Models\Improvement;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\Process\Process;
use Tests\TestCase;
use ZipArchive;

class ImprovementDownloadTest extends TestCase
{
    use RefreshDatabase;

    private function createRealDocx(string $path, array $paragraphs): void
    {
        $python = 'C:\\Users\\gamal\\AppData\\Local\\Programs\\Python\\Python310\\python.exe';
        $script = "from docx import Document; d = Document();\n";
        foreach ($paragraphs as $paragraph) {
            $script .= "d.add_paragraph(\"" . str_replace('"', '\\"', $paragraph) . "\")\n";
        }
        $script .= "d.save(r'" . str_replace('\\', '\\\\', $path) . "')\n";

        $process = new Process([$python, '-c', $script]);
        $process->run();

        $this->assertTrue($process->isSuccessful(), 'Unable to create a valid DOCX fixture: ' . $process->getErrorOutput());
    }

    public function test_it_downloads_a_patched_copy_without_overwriting_the_original_docx(): void
    {
        Storage::fake('public');

        $user = User::factory()->create();

        $docPath = 'documents/multi-run.docx';
        $sourceDocx = tempnam(sys_get_temp_dir(), 'multi_run_docx_') . '.docx';
        $this->createRealDocx($sourceDocx, ['Original text.', 'Second paragraph stays the same.']);

        Storage::disk('public')->put($docPath, file_get_contents($sourceDocx));
        unlink($sourceDocx);

        $document = Document::create([
            'user_id' => $user->id,
            'title' => 'multi-run',
            'file_path' => $docPath,
            'original_filename' => 'multi-run.docx',
            'type' => 'improvement',
            'status' => 'completed',
            'content' => "Original text.\n\nSecond paragraph stays the same.",
            'file_size' => 1024,
            'mime_type' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
        ]);

        $improvement = Improvement::create([
            'user_id' => $user->id,
            'document_id' => $document->id,
            'original_content' => "Original text.\n\nSecond paragraph stays the same.",
            'improved_content' => "Revised text.\n\nSecond paragraph stays the same.",
            'status' => 'completed',
            'mode' => 'manual',
        ]);

        $this->actingAs($user);

        $controller = app(\App\Http\Controllers\ImprovementController::class);
        $response = $controller->download($improvement);

        $this->assertSame(200, $response->getStatusCode());

        $originalXml = file_get_contents(Storage::disk('public')->path($docPath));
        $originalZip = new ZipArchive();
        $this->assertTrue($originalZip->open(Storage::disk('public')->path($docPath)) === true);
        $originalDocumentXml = $originalZip->getFromName('word/document.xml');
        $originalZip->close();

        $this->assertStringContainsString('Original text.', $originalDocumentXml);
        $this->assertStringNotContainsString('Revised text.', $originalDocumentXml);

        $downloadedPath = $response->getFile()->getPathname();
        $patchedZip = new ZipArchive();
        $this->assertTrue($patchedZip->open($downloadedPath) === true);
        $patchedXml = $patchedZip->getFromName('word/document.xml');
        $patchedZip->close();

        $this->assertStringContainsString('Revised text.', $patchedXml);
        $this->assertStringContainsString('Second paragraph stays the same.', $patchedXml);
        $this->assertStringNotContainsString('<w:t></w:t>', $patchedXml);
    }

    public function test_it_keeps_the_original_docx_unchanged_when_improvements_are_applied(): void
    {
        Storage::fake('public');

        $user = User::factory()->create();

        $docPath = 'documents/applied-original.docx';
        $sourceDocx = tempnam(sys_get_temp_dir(), 'applied_docx_') . '.docx';
        $this->createRealDocx($sourceDocx, ['Original sentence needs revision.', 'Second sentence stays unchanged.']);

        Storage::disk('public')->put($docPath, file_get_contents($sourceDocx));
        unlink($sourceDocx);

        $document = Document::create([
            'user_id' => $user->id,
            'title' => 'applied-original',
            'file_path' => $docPath,
            'original_filename' => 'applied-original.docx',
            'type' => 'improvement',
            'status' => 'completed',
            'content' => "Original sentence needs revision.\n\nSecond sentence stays unchanged.",
            'file_size' => 1024,
            'mime_type' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
        ]);

        $improvement = Improvement::create([
            'user_id' => $user->id,
            'document_id' => $document->id,
            'original_content' => "Original sentence needs revision.\n\nSecond sentence stays unchanged.",
            'improved_content' => "Rewritten sentence now reads better.\n\nSecond sentence stays unchanged.",
            'status' => 'completed',
            'mode' => 'manual',
            'suggestions' => [
                [
                    'index' => 0,
                    'original' => 'Original sentence needs revision.',
                    'suggestion' => 'Rewritten sentence now reads better.',
                ],
            ],
        ]);

        $this->actingAs($user);

        $response = app(\App\Http\Controllers\ImprovementController::class)->apply(new \Illuminate\Http\Request(['mode' => 'all']), $improvement);

        $this->assertSame(302, $response->getStatusCode());

        $zip = new ZipArchive();
        $this->assertTrue($zip->open(Storage::disk('public')->path($docPath)) === true);
        $xml = $zip->getFromName('word/document.xml');
        $zip->close();

        $this->assertStringContainsString('Original sentence needs revision.', $xml);
        $this->assertStringNotContainsString('Rewritten sentence now reads better.', $xml);
    }

    public function test_it_downloads_a_repaired_copy_without_replacing_the_original_document_content(): void
    {
        Storage::fake('public');

        $user = User::factory()->create();

        $docPath = 'documents/original-sample.docx';
        $sourceDocx = tempnam(sys_get_temp_dir(), 'sample_docx_') . '.docx';
        $this->createRealDocx($sourceDocx, ['This is the original text.', 'This paragraph is being improved.']);

        Storage::disk('public')->put($docPath, file_get_contents($sourceDocx));
        unlink($sourceDocx);

        $document = Document::create([
            'user_id' => $user->id,
            'title' => 'original-sample',
            'file_path' => $docPath,
            'original_filename' => 'original-sample.docx',
            'type' => 'improvement',
            'status' => 'completed',
            'content' => "This is the original text.\n\nThis paragraph is being improved.",
            'file_size' => 1024,
            'mime_type' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
        ]);

        $improvement = Improvement::create([
            'user_id' => $user->id,
            'document_id' => $document->id,
            'original_content' => "This is the original text.\n\nThis paragraph is being improved.",
            'improved_content' => "This is the improved text.\n\nThis paragraph has been revised for originality.",
            'status' => 'completed',
            'mode' => 'manual',
            'suggestions' => [
                [
                    'index' => 0,
                    'original' => 'This is the original text.',
                    'suggestion' => 'This is the improved text.',
                ],
                [
                    'index' => 1,
                    'original' => 'This paragraph is being improved.',
                    'suggestion' => 'This paragraph has been revised for originality.',
                ],
            ],
        ]);

        $this->actingAs($user);

        $controller = app(\App\Http\Controllers\ImprovementController::class);
        $response = $controller->download($improvement);

        $this->assertSame(200, $response->getStatusCode());
        $this->assertStringContainsString('original-sample', $response->headers->get('Content-Disposition'));

        $zip = new ZipArchive();
        $this->assertTrue($zip->open(Storage::disk('public')->path($docPath)) === true);
        $originalXml = $zip->getFromName('word/document.xml');
        $zip->close();

        $this->assertStringContainsString('This is the original text.', $originalXml);
        $this->assertStringContainsString('This paragraph is being improved.', $originalXml);
        $this->assertStringNotContainsString('This is the improved text.', $originalXml);

        $downloadedPath = $response->getFile()->getPathname();
        $patchedZip = new ZipArchive();
        $this->assertTrue($patchedZip->open($downloadedPath) === true);
        $patchedXml = $patchedZip->getFromName('word/document.xml');
        $patchedZip->close();

        $this->assertStringContainsString('This is the improved text.', $patchedXml);
        $this->assertStringContainsString('This paragraph has been revised for originality.', $patchedXml);
        $this->assertStringNotContainsString('This is the original text.', $patchedXml);
    }

    public function test_it_replaces_multiple_revised_sentences_within_the_same_paragraph_without_touching_other_text(): void
    {
        Storage::fake('public');

        $user = User::factory()->create();

        $docPath = 'documents/same-paragraph-replacements.docx';
        $sourceDocx = tempnam(sys_get_temp_dir(), 'same_paragraph_') . '.docx';
        $this->createRealDocx($sourceDocx, ['This is the first sentence. This is the second sentence. This is the third sentence.']);

        Storage::disk('public')->put($docPath, file_get_contents($sourceDocx));
        unlink($sourceDocx);

        $document = Document::create([
            'user_id' => $user->id,
            'title' => 'same-paragraph-replacements',
            'file_path' => $docPath,
            'original_filename' => 'same-paragraph-replacements.docx',
            'type' => 'improvement',
            'status' => 'completed',
            'content' => 'This is the first sentence. This is the second sentence. This is the third sentence.',
            'file_size' => 1024,
            'mime_type' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
        ]);

        $improvement = Improvement::create([
            'user_id' => $user->id,
            'document_id' => $document->id,
            'original_content' => 'This is the first sentence. This is the second sentence. This is the third sentence.',
            'improved_content' => 'This is the revised first sentence. This is the second sentence. This is the revised third sentence.',
            'status' => 'completed',
            'mode' => 'manual',
            'suggestions' => [
                ['index' => 0, 'original' => 'This is the first sentence.', 'suggestion' => 'This is the revised first sentence.'],
                ['index' => 2, 'original' => 'This is the third sentence.', 'suggestion' => 'This is the revised third sentence.'],
            ],
        ]);

        $this->actingAs($user);

        $response = app(\App\Http\Controllers\ImprovementController::class)->download($improvement);
        $this->assertSame(200, $response->getStatusCode());

        $patchedZip = new ZipArchive();
        $this->assertTrue($patchedZip->open($response->getFile()->getPathname()) === true);
        $patchedXml = $patchedZip->getFromName('word/document.xml');
        $patchedZip->close();

        $this->assertStringContainsString('This is the revised first sentence.', $patchedXml);
        $this->assertStringContainsString('This is the second sentence.', $patchedXml);
        $this->assertStringContainsString('This is the revised third sentence.', $patchedXml);
        $this->assertStringNotContainsString('This is the first sentence. This is the second sentence. This is the third sentence.', $patchedXml);
    }

    public function test_it_patch_replacements_across_multiple_runs_without_destroying_run_structure(): void
    {
        Storage::fake('public');

        $user = User::factory()->create();

        $docPath = 'documents/multi-run-replacements.docx';
        $sourceDocx = tempnam(sys_get_temp_dir(), 'multi_run_replace_') . '.docx';
        $this->createRealDocx($sourceDocx, ['This is the original text.']);

        $zip = new ZipArchive();
        $this->assertTrue($zip->open($sourceDocx) === true);
        $xml = $zip->getFromName('word/document.xml');
        $xml = preg_replace(
            '/<w:p><w:r><w:t>This is the original text\.<\/w:t><\/w:r><\/w:p>/',
            '<w:p><w:r><w:t>This is </w:t></w:r><w:r><w:t>the original text.</w:t></w:r></w:p>',
            $xml
        );
        $zip->addFromString('word/document.xml', $xml);
        $zip->close();

        Storage::disk('public')->put($docPath, file_get_contents($sourceDocx));
        unlink($sourceDocx);

        $document = Document::create([
            'user_id' => $user->id,
            'title' => 'multi-run-replacements',
            'file_path' => $docPath,
            'original_filename' => 'multi-run-replacements.docx',
            'type' => 'improvement',
            'status' => 'completed',
            'content' => 'This is the original text.',
            'file_size' => 1024,
            'mime_type' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
        ]);

        $improvement = Improvement::create([
            'user_id' => $user->id,
            'document_id' => $document->id,
            'original_content' => 'This is the original text.',
            'improved_content' => 'This is the revised text.',
            'status' => 'completed',
            'mode' => 'manual',
            'suggestions' => [
                ['index' => 0, 'original' => 'This is the original text.', 'suggestion' => 'This is the revised text.'],
            ],
        ]);

        $this->actingAs($user);

        $response = app(\App\Http\Controllers\ImprovementController::class)->download($improvement);
        $this->assertSame(200, $response->getStatusCode());

        $patchedZip = new ZipArchive();
        $this->assertTrue($patchedZip->open($response->getFile()->getPathname()) === true);
        $patchedXml = $patchedZip->getFromName('word/document.xml');
        $patchedZip->close();

        $this->assertStringContainsString('This is the revised text.', $patchedXml);
        $this->assertStringNotContainsString('This is the original text.', $patchedXml);
        $this->assertStringNotContainsString('<w:t></w:t>', $patchedXml);
        $this->assertStringContainsString('<w:r>', $patchedXml);
    }
}
