<?php

namespace Tests\Feature\Plagiarism;

use App\Models\Document;
use App\Models\PlagiarismCheck;
use App\Models\PlagiarismHighlight;
use App\Models\PlagiarismSource;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PlagiarismResultSourceNavigationTest extends TestCase
{
    use RefreshDatabase;

    public function test_result_page_marks_each_source_and_highlight_for_click_to_scroll_navigation(): void
    {
        $user = User::factory()->create();

        $document = Document::create([
            'user_id' => $user->id,
            'title' => 'Sample Document',
            'file_path' => 'documents/sample.pdf',
            'original_filename' => 'sample.pdf',
            'type' => 'plagiarism',
            'status' => 'completed',
            'content' => "This is a sample sentence.\nThis is another sentence.",
            'file_size' => 1000,
            'mime_type' => 'application/pdf',
        ]);

        $check = PlagiarismCheck::create([
            'document_id' => $document->id,
            'user_id' => $user->id,
            'status' => 'completed',
            'total_similarity' => 50,
            'sources_checked' => ['web'],
        ]);

        $source = PlagiarismSource::create([
            'plagiarism_check_id' => $check->id,
            'source_name' => 'web',
            'source_label' => 'Website Source',
            'similarity_score' => 50,
            'title' => 'Example Source Title',
            'url' => 'https://example.com',
            'color_code' => '#ff0000',
        ]);

        PlagiarismHighlight::create([
            'plagiarism_check_id' => $check->id,
            'plagiarism_source_id' => $source->id,
            'original_text' => 'This is a sample sentence',
            'matched_text' => 'This is a sample sentence',
            'color_code' => '#ff0000',
            'start_position' => 0,
            'end_position' => 27,
            'match_percentage' => 80,
        ]);

        $response = $this->actingAs($user)->get(route('user.plagiarism.result', $check->id));

        $response->assertOk();
        $response->assertSee('data-source-id="' . $source->id . '"', false);
        $response->assertSee('data-source-index="1"', false);
        $response->assertSee('mark.t-highlight', false);
    }

    public function test_similarity_percentages_and_turnitin_percentage_calculation(): void
    {
        $user = User::factory()->create();

        $document = Document::create([
            'user_id' => $user->id,
            'title' => 'Sample Document',
            'file_path' => 'documents/sample.pdf',
            'original_filename' => 'sample.pdf',
            'type' => 'plagiarism',
            'status' => 'completed',
            'content' => str_repeat('Sample word sentence here. ', 300),
            'file_size' => 1000,
            'mime_type' => 'application/pdf',
        ]);

        $check = PlagiarismCheck::create([
            'document_id' => $document->id,
            'user_id' => $user->id,
            'status' => 'completed',
            'total_similarity' => 29,
            'matched_sentences' => 29,
            'total_sentences' => 100,
            'sources_checked' => ['web'],
        ]);

        $source = PlagiarismSource::create([
            'plagiarism_check_id' => $check->id,
            'source_name' => 'web',
            'source_label' => 'Website Source',
            'similarity_score' => 29,
            'title' => 'Example Source Title',
            'url' => 'https://example.com',
            'color_code' => '#ff0000',
        ]);

        PlagiarismHighlight::create([
            'plagiarism_check_id' => $check->id,
            'plagiarism_source_id' => $source->id,
            'original_text' => str_repeat('word ', 15),
            'matched_text' => str_repeat('word ', 15),
            'color_code' => '#ff0000',
            'start_position' => 0,
            'end_position' => 200,
            'match_percentage' => 80,
        ]);

        $this->assertSame(29, $check->fresh()->total_similarity);
        $this->assertSame(15, $source->matched_words);
        $this->assertSame('1%', $source->turnitin_percentage);
    }
}
