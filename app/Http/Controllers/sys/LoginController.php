<?php

namespace App\Http\Controllers\sys;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\AuthenticateRequest;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Redirector;
use Illuminate\Support\Facades\Auth;

class LoginController extends Controller
{
    public function viewLogin(): Factory|View|Application
    {
        return view('auth.login');
    }

    public function authenticate(AuthenticateRequest $request): RedirectResponse
    {
        if (Auth::attempt(['nickname'=>$request->validated('nickname'), 'password'=>$request->validated('password')])) {
            $request->session()->regenerate();

            return redirect()->route('backend.view.botControlCenter');
        }

        return redirect()->back()->withErrors([
            'error'=>'Name oder Passwort falsch',
        ]);
    }

    public function logout(Request $request): Redirector|Application|RedirectResponse
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('public.view.login');
    }
}
