<?php

namespace App\Http\Controllers;

use Exception;
use PDOException;
use App\Models\Reset;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class UserController extends WebController
{
    const PAGESIZE = 20;

    const PROMPT = __CLASS__ . '@prompt';
    const ACTIVATION_SUCCESS = 1;
    const SUSPENSION_SUCCESS = 2;
    const RESTORATION_SUCCESS = 3;
    const TERMINATION_SUCCESS = 4;
    const PROMOTION_SUCCESS = 5;
    const DEMOTION_SUCCESS = 6;
    const ISSUE_SUCCESS = 7;

    /**
     * Construct a new instance of the controller. It activates both "auth"
     * and "can:admin" middlewares such that routes in this class are only
     * available to authenticated users with admin role.
     */
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('can:admin');
    }

    /**
     * Render the index page.
     * @param Request $request
     * @return JsonResponse|View
     */
    public function index(Request $request)
    {
        $offset = intval($request->input('offset', 0), 10);
        $users = User::orderBy('created_at', 'asc')->skip($offset)->take(self::PAGESIZE+1)->get();

        $data = [];
        $data['users'] = $users->slice(0, self::PAGESIZE);
        $data['offset'] = $offset;
        $data['urls'] = [];
        $data['urls']['self'] = url()->route('users', self::normalizeUsersParameters($offset));
        $data['urls']['head'] = null;
        $data['urls']['prev'] = null;
        $data['urls']['next'] = null;

        if ($offset > self::PAGESIZE) {
            $data['urls']['head'] = url()->route('users');
            $data['urls']['prev'] = url()->route('users', self::normalizeUsersParameters($offset - self::PAGESIZE));
        } elseif ($offset > 0) {
            $data['urls']['head'] = url()->route('users');
            $data['urls']['prev'] = url()->route('users');
        }

        if (count($users) > self::PAGESIZE) {
            $data['urls']['next'] = url()->route('users', self::normalizeUsersParameters($offset + self::PAGESIZE));
        }

        if ($this->ajax($request)) {
            return new JsonResponse($data, 200);
        } else {
            $session = $request->session();
            $data['prompt'] = $session->get(self::PROMPT, [ 'type' => self::EMPTY_MESSAGE ]);
            return view('users', $data);
        }
    }

    /**
     * Handle incoming update requests.
     * @param Request $request
     * @param int $id
     * @return JsonResponse|RedirectResponse
     */
    public function update(Request $request, $id)
    {
        $type = $request->input('type', '');
        $offset = intval($request->input('offset', 0), 10);

        $result = [];
        $result['type'] = self::MISSING_ERROR;
        $result['target'] = null;

        try {
            if (($target = User::find($id)) !== null) {
                $result['type'] = self::UNKNOWN_ERROR;
                $result['target'] = $target->attributesToArray();

                if ($type === 'activate' && $target->status === User::CREATED) {
                    $target->status = User::ACTIVE;
                    $target->save();
                    $result['type'] = self::ACTIVATION_SUCCESS;
                    $result['target'] = $target->attributesToArray();
                } elseif ($type === 'suspend' && $target->status === User::ACTIVE) {
                    $target->status = User::SUSPENDED;
                    $target->save();
                    $result['type'] = self::SUSPENSION_SUCCESS;
                    $result['target'] = $target->attributesToArray();
                } elseif ($type === 'restore' && $target->status === User::SUSPENDED) {
                    $target->status = User::ACTIVE;
                    $target->save();
                    $result['type'] = self::RESTORATION_SUCCESS;
                    $result['target'] = $target->attributesToArray();
                } elseif ($type === 'issue' && $target->status === User::ACTIVE) {
                    Reset::where('email', $target->name)->delete();
                    $next = new Reset();
                    $next->email = $target->name;
                    $next->token = Reset::generateToken();
                    $next->save();
                    $result['type'] = self::ISSUE_SUCCESS;
                    $result['target'] = $next->attributesToArray();
                } elseif ($type === 'terminate' && $target->status === User::CREATED) {
                    $target->status = User::TERMINATED;
                    $target->save();
                    $result['type'] = self::TERMINATION_SUCCESS;
                    $result['target'] = $target->attributesToArray();
                } elseif ($type === 'terminate' && $target->status === User::ACTIVE) {
                    $target->status = User::TERMINATED;
                    $target->save();
                    $result['type'] = self::TERMINATION_SUCCESS;
                    $result['target'] = $target->attributesToArray();
                } elseif ($type === 'terminate' && $target->status === User::SUSPENDED) {
                    $target->status = User::TERMINATED;
                    $target->save();
                    $result['type'] = self::TERMINATION_SUCCESS;
                    $result['target'] = $target->attributesToArray();
                } elseif ($type === 'promote' && $target->role === User::NORMAL) {
                    $target->role = User::ADMIN;
                    $target->save();
                    $result['type'] = self::PROMOTION_SUCCESS;
                    $result['target'] = $target->attributesToArray();
                } elseif ($type === 'demote' && $target->role === User::ADMIN) {
                    $target->role = User::NORMAL;
                    $target->save();
                    $result['type'] = self::DEMOTION_SUCCESS;
                    $result['target'] = $target->attributesToArray();
                } else {
                    $result['type'] = self::CONFLICT_ERROR;
                }
            }
        } catch (PDOException $ex) {
            $result['type'] = self::BACKEND_ERROR;
        } catch (Exception $ex) {
            $result['type'] = self::UNKNOWN_ERROR;
        }

        if ($this->ajax($request)) {
            if ($result['type'] >= 0) {
                return new JsonResponse($result, 200);
            } elseif ($result['type'] === self::MISSING_ERROR) {
                return new JsonResponse($result, 404);
            } elseif ($result['type'] === self::CONFLICT_ERROR) {
                return new JsonResponse($result, 400);
            } else {
                return new JsonResponse($result, 500);
            }
        } else {
            $session = $request->session();
            $session->flash(self::PROMPT, $result);
            return redirect()->route('users', self::normalizeUsersParameters($offset));
        }
    }
}
