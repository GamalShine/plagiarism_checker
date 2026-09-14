<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Link;
use App\Models\PlagiarismCheck;
use App\Models\User;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(): View
    {
        $stats = [
            'users' => User::count(),
            'admins' => User::where('role', 'admin')->count(),
            'links' => Link::count(),
            'checks' => PlagiarismCheck::where('status', 'completed')->count(),
        ];

        $recentUsers = User::latest()->take(5)->get();
        $recentLinks = Link::with('creator')->latest()->take(5)->get();

        return view('admin.dashboard', compact('stats', 'recentUsers', 'recentLinks'));
    }
}
