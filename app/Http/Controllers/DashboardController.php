<?php

namespace App\Http\Controllers;

use App\Models\History;
use App\Models\PlagiarismCheck;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(): View
    {
        $user = auth()->user();

        // Statistics
        $totalChecks = PlagiarismCheck::where('user_id', $user->id)->where('status', 'completed')->count();
        $avgSimilarity = PlagiarismCheck::where('user_id', $user->id)->where('status', 'completed')->avg('total_similarity') ?? 0;
        $totalJournals = $user->journals()->count();
        $totalImprovements = $user->improvements()->where('status', 'completed')->count();

        // Recent checks
        $recentChecks = PlagiarismCheck::where('user_id', $user->id)
            ->with('document')
            ->latest()
            ->take(5)
            ->get();

        // Recent history
        $recentHistory = History::where('user_id', $user->id)
            ->latest()
            ->take(8)
            ->get();

        // Monthly trend data (last 6 months)
        $trendData = PlagiarismCheck::where('user_id', $user->id)
            ->where('status', 'completed')
            ->where('created_at', '>=', now()->subMonths(6))
            ->select(
                DB::raw('MONTH(created_at) as month'),
                DB::raw('YEAR(created_at) as year'),
                DB::raw('AVG(total_similarity) as avg_similarity'),
                DB::raw('COUNT(*) as total')
            )
            ->groupBy('year', 'month')
            ->orderBy('year')
            ->orderBy('month')
            ->get();

        // Format trend for chart
        $months = [];
        $similarities = [];
        $counts = [];

        $monthNames = ['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Agu', 'Sep', 'Okt', 'Nov', 'Des'];

        for ($i = 5; $i >= 0; $i--) {
            $date = now()->subMonths($i);
            $monthNum = (int) $date->format('n');
            $yearNum = (int) $date->format('Y');

            $found = $trendData->first(fn($d) => $d->month == $monthNum && $d->year == $yearNum);
            $months[] = $monthNames[$monthNum - 1];
            $similarities[] = $found ? round($found->avg_similarity, 1) : 0;
            $counts[] = $found ? $found->total : 0;
        }

        return view('dashboard', compact(
            'totalChecks', 'avgSimilarity', 'totalJournals', 'totalImprovements',
            'recentChecks', 'recentHistory', 'months', 'similarities', 'counts'
        ));
    }
}
