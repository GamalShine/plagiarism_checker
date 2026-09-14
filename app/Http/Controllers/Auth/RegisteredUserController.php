<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Support\HomeRoute;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules;
use Illuminate\Validation\ValidationException;

class RegisteredUserController extends Controller
{
    /**
     * Display the registration view.
     */
    public function create()
    {
        $packageKey = request()->query('package') ?: session('selected_package');

        if (! $packageKey || ! config("plans.{$packageKey}")) {
            return redirect()->route('welcome')->with('package_required_alert', true);
        }

        if ($packageKey && config("plans.{$packageKey}")) {
            session(['selected_package' => $packageKey]);
        }

        return view('auth.register');
    }

    /**
     * Handle an incoming registration request.
     *
     * @throws ValidationException
     */
    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:'.User::class],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
        ]);

        $email = strtolower(trim($request->email));
        $name = Str::before($email, '@');

        $user = User::create([
            'name' => $name,
            'email' => $email,
            'password' => Hash::make($request->password),
            'pending_package_key' => session('selected_package'),
        ]);

        event(new Registered($user));

        Auth::login($user);

        $packageKey = session()->pull('selected_package');

        return $packageKey && config("plans.{$packageKey}")
            ? redirect()->route('user.payment.package', $packageKey)
            : redirect(HomeRoute::for($user));
    }
}
