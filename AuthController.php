<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AuthController extends Controller
{
    public function check_credential(Request $request)
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required'],
        ]);

        $check_user = User::where('email', $request->email)
            ->whereNotNull('email_verified_at')
            ->whereNotNull('remember_token')
            ->first();

        if (Auth::attempt($credentials) && !empty($check_user)) {
            $request->session()->regenerate();
            return true;
        }

        return false;
    }
    public function login(Request $request)
    {
        if ($request->method() === "GET") {
            return view('auth.login');
        }
        
        if ($request->method() === "POST" && $this->check_credential($request) == true) {
            return to_route('cryptography.index');
        }

        return redirect('/');
    }

    public function logout(Request $request): RedirectResponse
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect('/');
    }
}
