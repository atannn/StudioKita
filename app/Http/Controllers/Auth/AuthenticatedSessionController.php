<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class AuthenticatedSessionController extends Controller
{
    /**
     * Display the login view.
     */
    public function create(): View
    {
        return view('auth.login');
    }

    /**
     * Display the developer login view.
     */
    public function createDeveloper(): View
    {
        return view('auth.developer-login');
    }

    /**
     * Handle an incoming authentication request.
     */
    public function store(LoginRequest $request): \Illuminate\Http\RedirectResponse
    {
        $request->authenticate();
        $request->session()->regenerate();
    
        $user = $request->user();
        $loginAs = $request->input('login_as');
    
        $ownerRoles = ['owner'];

        // Jika login sebagai owner, validasi bahwa user memang owner/developer
        if ($loginAs === 'owner') {
            if (!in_array($user->role, $ownerRoles, true)) {
                Auth::logout();
                return redirect()->route('login', ['as' => 'owner'])
                    ->withErrors(['email' => 'Akun ini bukan akun Owner. Silakan login sebagai Customer.']);
            }
            return redirect('/owner/dashboard');
        }

        if ($loginAs === 'developer') {
            if ($user->role !== 'developer') {
                Auth::logout();
                return redirect()->route('developer.login')
                    ->withErrors(['email' => 'Akun ini bukan akun Developer.']);
            }
            return redirect()->route('developer.dashboard');
        }
    
        // Jika login sebagai customer tapi user adalah owner/developer, beri pilihan
        if (in_array($user->role, $ownerRoles, true) && !$loginAs) {
            Auth::logout();
            return redirect()->route('login')
                ->withErrors(['email' => 'Akun ini adalah akun Owner. Silakan login sebagai Owner.']);
        }
    
        // CUSTOMER: kembali ke halaman yang dia mau (booking studio),
        // kalau tidak ada, ke beranda studio
        return redirect()->intended('/studios');
    }
    






    /**
     * Destroy an authenticated session.
     */
    public function destroy(Request $request): RedirectResponse
    {
        Auth::guard('web')->logout();

        $request->session()->invalidate();

        $request->session()->regenerateToken();

        return redirect('/');
    }
}
