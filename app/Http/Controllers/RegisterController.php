<?php

namespace App\Http\Controllers;

use Exception;
use PDOException;
use App\Models\Invite;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\View\View;

class RegisterController extends WebController
{
    const PROMPT = __CLASS__ . '@prompt';
    const PREFILL = __CLASS__ . '@prefill';
    const EMPTY_CODE_ERROR = -1;
    const INVALID_CODE_ERROR = -2;
    const EMPTY_NAME_ERROR = -3;
    const USED_NAME_ERROR = -4;
    const EMPTY_PASSWORD_ERROR = -5;
    const WEAK_PASSWORD_ERROR = -6;
    const MISMATCH_PASSWORD_ERROR = -7;

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
        $prefill = $session->get(self::PREFILL, [ 'code' => '', 'name' => '' ]);

        return view('register', [
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
        $code = (string) $request->input('code', '');
        $name = (string) $request->input('name', '');
        $password = (string) $request->input('password', '');
        $confirm = (string) $request->input('confirm', '');

        $auth = [];
        $auth['name'] = $name;
        $auth['password'] = $password;
        $auth['status'] = User::ACTIVE;

        $result = [];
        $result['type'] = self::UNKNOWN_ERROR;

        $prefill = [];
        $prefill['code'] = $code;
        $prefill['name'] = $name;

        if ($code === '') {
            $result['type'] = self::EMPTY_CODE_ERROR;
        } elseif ($name === '') {
            $result['type'] = self::EMPTY_NAME_ERROR;
        } elseif ($password === '') {
            $result['type'] = self::EMPTY_PASSWORD_ERROR;
        } elseif (hash_equals($password, $confirm) === false) {
            $result['type'] = self::MISMATCH_PASSWORD_ERROR;
        } else {
            try {
                DB::transaction(function () use ($code, $name, $password, &$result) {
                    $invite = Invite::where('code', $code)->lockForUpdate()->first();
                    $user = User::where('name', $name)->first();

                    if ($invite === null) {
                        $result['type'] = self::INVALID_CODE_ERROR;
                    } elseif ($invite->expired !== false) {
                        $result['type'] = self::INVALID_CODE_ERROR;
                    } elseif ($user !== null) {
                        $result['type'] = self::USED_NAME_ERROR;
                    } else {
                        $user = new User;
                        $user->name = $name;
                        $user->password = Hash::make($password);
                        $user->role = User::NORMAL;
                        $user->status = User::ACTIVE;

                        $user->save();
                        $invite->delete();

                        $result['type'] = self::ACTION_SUCCESS;
                    }
                });
            } catch (PDOException $ex) {
                $result['type'] = self::BACKEND_ERROR;
            } catch (Exception $ex) {
                $result['type'] = self::UNKNOWN_ERROR;
            }
        }

        if ($result['type'] < 0) {
            $session = $request->session();
            $session->flash(self::PROMPT, $result);
            $session->flash(self::PREFILL, $prefill);
            return redirect()->route('register');
        } elseif (Auth::attempt($auth, true) === false) {
            return redirect()->route('login');
        } else {
            return redirect()->route('home');
        }
    }
}
