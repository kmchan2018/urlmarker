<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class LoginController extends WebController
{
    const PROMPT = __CLASS__ . '@prompt';
    const PREFILL = __CLASS__ . '@prefill';

    /**
     * Construct a new instance of the controller. It activates the "guest"
     * middleware to ensure that routes defined in this class redirects all
     * authenticated users to the main landing page.
     */
    public function __construct()
    {
        $this->middleware('guest');
    }

    /**
     * Show the login page to the user.
     * @param Request $request
     * @return View
     */
    public function show(Request $request): View
    {
        $session = $request->session();
        $prompt = $session->get(self::PROMPT, [ 'type' => self::EMPTY_MESSAGE ]);
        $prefill = $session->get(self::PREFILL, [ 'name' => '' ]);

        return view('login', [
            'prompt' => $prompt,
            'prefill' => $prefill,
        ]);
    }

    /**
     * Authenticate the user with the credentials given in the login form.
     * @param Request $request
     * @return RedirectResponse
     */
    public function handle(Request $request): RedirectResponse
    {
        $name = (string) $request->input('name', '');
        $password = (string) $request->input('password', '');

        $auth = [];
        $auth['name'] = $name;
        $auth['password'] = $password;
        $auth['status'] = User::ACTIVE;

        $result = [];
        $result['type'] = self::UNKNOWN_ERROR;

        $prefill = [];
        $prefill['name'] = $name;

        if (Auth::attempt($auth, true)) {
            $result['type'] = self::ACTION_SUCCESS;
        } else {
            $result['type'] = self::ACTION_ERROR;
        }

        if ($result['type'] < 0) {
            $session = $request->session();
            $session->flash(self::PROMPT, $result);
            $session->flash(self::PREFILL, $prefill);
            return redirect()->route('login');
        } else {
            return redirect()->route('home');
        }
    }
}
