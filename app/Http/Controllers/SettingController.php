<?php

namespace App\Http\Controllers;

use App\Models\UserSetting;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class SettingController extends Controller
{
    public function index(): View
    {
        $user = auth()->user();
        $settings = UserSetting::firstOrCreate(
            ['user_id' => $user->id],
            [
                'dark_mode' => false,
                'email_notifications' => true,
                'default_sources' => ['web', 'google_scholar', 'openalex', 'crossref', 'crossref_posted', 'publications'],
                'elsevier_enabled' => false,
            ]
        );

        $layout = str_starts_with(request()->route()?->getName() ?? '', 'admin.') ? 'layouts.admin' : 'layouts.user';

        return view('settings.index', array_merge(compact('settings'), ['layout' => $layout]));
    }

    public function update(Request $request): RedirectResponse
    {
        $user = auth()->user();

        $data = $request->validate([
            'name' => ['nullable', 'string', 'max:255'],
            'email' => ['nullable', 'email', 'max:255', Rule::unique('users')->ignore($user->id)],
            'password' => ['nullable', 'string', 'min:8', 'confirmed'],
            'dark_mode' => ['nullable', 'boolean'],
            'email_notifications' => ['nullable', 'boolean'],
            'serpapi_key' => 'nullable|string|max:255',
            'elsevier_api_key' => 'nullable|string|max:255',
            'elsevier_enabled' => 'nullable|boolean',
            'google_cse_key' => 'nullable|string|max:255',
            'google_cse_id' => 'nullable|string|max:255',
            'default_sources' => 'nullable|array',
            'default_sources.*' => 'in:web,google_scholar,elsevier,openalex,crossref,crossref_posted,publications',
        ]);

        if (! empty($data['name'])) {
            $user->name = $data['name'];
        }

        if (! empty($data['email'])) {
            $user->email = $data['email'];
        }

        if (! empty($data['password'])) {
            $user->password = bcrypt($data['password']);
        }

        $user->save();

        $settings = UserSetting::firstOrCreate(
            ['user_id' => $user->id],
            [
                'dark_mode' => false,
                'email_notifications' => true,
                'default_sources' => ['web', 'google_scholar', 'openalex', 'crossref', 'crossref_posted', 'publications'],
            ]
        );

        $settings->update([
            'dark_mode' => $request->boolean('dark_mode'),
            'email_notifications' => $request->boolean('email_notifications'),
            'serpapi_key' => $request->input('serpapi_key'),
            'elsevier_api_key' => $request->input('elsevier_api_key'),
            'elsevier_enabled' => $request->boolean('elsevier_enabled'),
            'google_cse_key' => $request->input('google_cse_key'),
            'google_cse_id' => $request->input('google_cse_id'),
            'default_sources' => $request->input('default_sources', $settings->default_sources ?? []),
        ]);

        return back()->with('success', 'Pengaturan berhasil disimpan!');
    }

    public function toggleDarkMode(Request $request): \Illuminate\Http\JsonResponse
    {
        $user = auth()->user();
        $settings = UserSetting::firstOrCreate(['user_id' => $user->id]);
        $settings->update(['dark_mode' => !$settings->dark_mode]);

        return response()->json(['dark_mode' => $settings->dark_mode]);
    }
}
