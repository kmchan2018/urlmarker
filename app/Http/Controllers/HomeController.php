<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class HomeController extends WebController
{
    const PAGESIZE = 20;

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
     * Render the home page.
     * @return View
     */
    public function show(): View
    {
        $user = Auth::user();
        $markers = $user->markers()->orderBy('created_at', 'desc')->take(self::PAGESIZE)->get();

        return view('home', [
            'markers' => $markers,
        ]);
    }

    /**
     * Redirect to the home page.
     * @return RedirectResponse
     */
    public function redirect(): RedirectResponse
    {
        return redirect('home');
    }
}
