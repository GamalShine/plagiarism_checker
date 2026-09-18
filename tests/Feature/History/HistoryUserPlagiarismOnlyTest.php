<?php

namespace Tests\Feature\History;

use App\Models\History;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class HistoryUserPlagiarismOnlyTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_history_only_shows_plagiarism_check_activities(): void
    {
        $user = User::factory()->create();
        $user->markEmailAsVerified();

        History::create([
            'user_id' => $user->id,
            'activity_type' => 'plagiarism_check',
            'description' => 'Cek plagiarisme selesai',
            'metadata' => ['check_id' => 1],
            'icon' => '🔍',
            'color' => '#4ECDC4',
        ]);

        History::create([
            'user_id' => $user->id,
            'activity_type' => 'journal_generate',
            'description' => 'Jurnal dibuat',
            'metadata' => ['journal_id' => 2],
            'icon' => '📄',
            'color' => '#A8E6CF',
        ]);

        $response = $this->actingAs($user)->get(route('user.history.index'));

        $response->assertOk();
        $response->assertSee('Cek plagiarisme selesai');
        $response->assertDontSee('Jurnal dibuat');
    }
}
