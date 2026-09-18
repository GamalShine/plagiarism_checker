<?php

namespace Tests\Feature\Plagiarism;

use App\Models\Document;
use App\Models\PlagiarismCheck;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PlagiarismResultWordPreviewTest extends TestCase
{
    use RefreshDatabase;

    public function test_result_page_auto_activates_word_preview_for_docx_files(): void
    {
        $user = User::factory()->create();
        $user->markEmailAsVerified();

        $document = Document::create([
            'user_id' => $user->id,
            'title' => 'Sample Document',
            'file_path' => 'documents/sample.docx',
            'original_filename' => 'sample.docx',
            'type' => 'plagiarism',
            'status' => 'completed',
            'content' => 'This is a sample document.',
            'file_size' => 1000,
            'mime_type' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
        ]);

        $check = PlagiarismCheck::create([
            'document_id' => $document->id,
            'user_id' => $user->id,
            'status' => 'completed',
            'total_similarity' => 50,
            'sources_checked' => ['web'],
        ]);

        $response = $this->actingAs($user)->get(route('user.plagiarism.result', $check->id));

        $response->assertOk();
        $response->assertSee("if (fileExt === 'docx')", false);
        $response->assertSee('tabWord.click();', false);
    }
}
