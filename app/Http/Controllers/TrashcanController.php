<?php

namespace App\Http\Controllers;

use App\Models\Marker;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class TrashcanController extends WebController
{
    const PAGESIZE = 20;

    const PROMPT = __CLASS__ . '@prompt';
    const RESTORE_SUCCESS = 1;

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
     * Render the trashcan page.
     * @param Request $request
     * @return JsonResponse|View
     */
    public function index(Request $request)
    {
        $user = Auth::user();
        $search = (string) $request->input('search', '');
        $offset = intval($request->input('offset', 0), 10);
        $query = $user->markers()->onlyTrashed()->orderBy('deleted_at', 'desc')->skip($offset)->take(self::PAGESIZE+1);

        if ($search !== '') {
            foreach (preg_split('/\s+/u', $search) as $fragment) {
                $query = $query->where(function($query) use ($fragment) {
                    $fragment = str_replace("_", "\\_", $fragment);
                    $fragment = str_replace("%", "\\%", $fragment);
                    $fragment = "%$fragment%";
                    $query->orWhere('url', 'like', $fragment);
                    $query->orWhere('description', 'like', $fragment);
                });
            }
        }

        $markers = $query->get();
        $data = [];

        $data['markers'] = $markers->slice(0, self::PAGESIZE);
        $data['search'] = $search;
        $data['offset'] = $offset;
        $data['urls'] = [];
        $data['urls']['self'] = url()->route('trashcan', self::normalizeTrashcanParameters($search, $offset));
        $data['urls']['head'] = null;
        $data['urls']['prev'] = null;
        $data['urls']['next'] = null;

        if ($offset > self::PAGESIZE) {
            $data['urls']['head'] = url()->route('trashcan', self::normalizeTrashcanParameters($search, 0));
            $data['urls']['prev'] = url()->route('trashcan', self::normalizeTrashcanParameters($search, $offset - self::PAGESIZE));
        } elseif ($offset > 0) {
            $data['urls']['head'] = url()->route('trashcan', self::normalizeTrashcanParameters($search, 0));
            $data['urls']['prev'] = url()->route('trashcan', self::normalizeTrashcanParameters($search, 0));
        }

        if (count($markers) > self::PAGESIZE) {
            $data['urls']['next'] = url()->route('trashcan', self::normalizeTrashcanParameters($search, $offset + self::PAGESIZE));
        }

        if ($this->ajax($request)) {
            return new JsonResponse($data, 200);
        } else {
            $session = $request->session();
            $data['prompt'] = $session->get(self::PROMPT, [ 'type' => self::EMPTY_MESSAGE ]);
            return view('trashcan', $data);
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
        $search = (string) $request->input('search', '');
        $offset = intval($request->input('offset', 0), 10);

        $result = [];
        $result['type'] = self::MISSING_ERROR;
        $result['target'] = null;

        try {
            if (($target = Marker::onlyTrashed()->find($id)) !== null) {
                $result['type'] = self::UNKNOWN_ERROR;
                $result['target'] = $target->attributesToArray();

                if ($type === 'restore') {
                    $target->restore();
                    $result['type'] = self::RESTORE_SUCCESS;
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
            return redirect()->route('trashcan', self::normalizeTrashcanParameters($search, $offset));
        }
    }
}
