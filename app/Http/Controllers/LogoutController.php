<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;

class LogoutController extends WebController
{
    /**
     * Construct a new instance of the controller. It activates the "auth"
     * middleware so that routes in this class are only available to
     * authenticated users.
     */
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Logout the user from the system.
     * @return RedirectResponse
     */
    public function handle(Request $request): RedirectResponse
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect()->route('login');
    }
}
