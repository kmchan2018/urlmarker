<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\View\View;

class PasswordUpdateController extends WebController
{
    const PROMPT = __CLASS__ . '@prompt';
    const AUTH_ERROR = -1;
    const EMPTY_PASSWORD_ERROR = -2;
    const WEAK_PASSWORD_ERROR = -3;
    const MISMATCH_PASSWORD_ERROR = -4;

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
     * Render the password update page.
     * @param Request $request
     * @return View
     */
    public function show(Request $request): View
    {
        $session = $request->session();
        $prompt = $session->get(self::PROMPT, [ 'type' => self::EMPTY_MESSAGE ]);

        return view('password_update', [
            'prompt' => $prompt,
        ]);
    }

    /**
     * Handle the password update request.
     * @param Request $request
     * @return RedirectResponse
     */
    public function handle(Request $request): RedirectResponse
    {
        $user = Auth::user();
        $current = $request->input('current', '');
        $incoming = $request->input('incoming', '');
        $repeat = $request->input('repeat', '');

        $result = [];
        $result['type'] = self::UNKNOWN_ERROR;
        $result['target'] = $user->attributesToArray();

        if ($incoming === '') {
            $result['type'] = self::EMPTY_PASSWORD_ERROR;
        } else if (hash_equals($incoming, $repeat) === false) {
            $result['type'] = self::MISMATCH_PASSWORD_ERROR;
        } else if (Hash::check($current, $user->password) === false) {
            $result['type'] = self::AUTH_ERROR;
        } else {
            try {
                $user->password = Hash::make($incoming);
                $user->save();
                $result['type'] = self::ACTION_SUCCESS;
                $result['target'] = $user->attributesToArray();
                Auth::logout();
            } catch (PDOException $ex) {
                $result['type'] = self::BACKEND_ERROR;
            } catch (Exception $ex) {
                $result['type'] = self::UNKNOWN_ERROR;
            }
        }

        if ($result['type'] < 0) {
            $session = $request->session();
            $session->flash(self::PROMPT, $result);
            return redirect()->route('password_update');
        } else {
            return redirect()->route('login');
        }
    }
}
