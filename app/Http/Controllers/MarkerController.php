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

class MarkerController extends WebController
{
    const PAGESIZE = 20;

    const PROMPT = __CLASS__ . '@prompt';
    const CREATION_SUCCESS = 1;
    const DELETION_SUCCESS = 2;
    const EMPTY_URL_ERROR = -1;

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
     * Render the marker page.
     * @param Request $request
     * @return JsonResponse|View
     */
    public function index(Request $request)
    {
        $user = Auth::user();
        $search = (string) $request->input('search', '');
        $offset = intval($request->input('offset', 0), 10);
        $query = $user->markers()->orderBy('created_at', 'desc')->skip($offset)->take(self::PAGESIZE+1);

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
        $data['urls']['self'] = url()->route('markers', self::normalizeMarkersParameters($search, $offset));
        $data['urls']['head'] = null;
        $data['urls']['prev'] = null;
        $data['urls']['next'] = null;

        if ($offset > self::PAGESIZE) {
            $data['urls']['head'] = url()->route('markers', self::normalizeMarkersParameters($search, 0));
            $data['urls']['prev'] = url()->route('markers', self::normalizeMarkersParameters($search, $offset - self::PAGESIZE));
        } elseif ($offset > 0) {
            $data['urls']['head'] = url()->route('markers', self::normalizeMarkersParameters($search, 0));
            $data['urls']['prev'] = url()->route('markers', self::normalizeMarkersParameters($search, 0));
        }

        if (count($markers) > self::PAGESIZE) {
            $data['urls']['next'] = url()->route('markers', self::normalizeMarkersParameters($search, $offset + self::PAGESIZE));
        }

        if ($this->ajax($request)) {
            return new JsonResponse($data, 200);
        } else {
            $session = $request->session();
            $data['prompt'] = $session->get(self::PROMPT, [ 'type' => self::EMPTY_MESSAGE ]);
            return view('markers', $data);
        }
    }

    /**
     * Handle the create request.
     * @param Request $request
     * @return JsonResponse|RedirectResponse
     */
    public function create(Request $request)
    {
        $user = Auth::user();
        $url = $request->input('url', '');
        $description = $request->input('notes', null);
        $route = $request->input('route', 'markers');
        $search = (string) $request->input('search', '');
        $offset = intval($request->input('offset', 0), 10);

        $model = [];
        $model['url'] = $url;
        $model['description'] = $description;
        $model['handler'] = 'Website';

        $result = [];
        $result['type'] = self::EMPTY_MESSAGE;

        if ($url === '') {
            $result['type'] = self::EMPTY_URL_ERROR;
        } else {
            DB::transaction(function() use ($user, $url, $model, &$result) {
                $current = $user->markers()->withTrashed()->where('url', $url)->lockForUpdate()->first();

                if ($current === null) {
                    $target = $user->markers()->create($model);
                    $result['type'] = self::CREATION_SUCCESS;
                    $result['target'] = $target->attributesToArray();
                } elseif ($current->trashed()) {
                    $current->forceDelete();
                    $target = $user->markers()->create($model);
                    $result['type'] = self::CREATION_SUCCESS;
                    $result['target'] = $target->attributesToArray();
                } else {
                    $result['type'] = self::CONFLICT_ERROR;
                }
            });
        }

        if ($this->ajax($request)) {
            if ($result['type'] >= 0) {
                return new JsonResponse($result, 200);
            } elseif ($result['type'] === self::CONFLICT_ERROR) {
                return new JsonResponse($result, 400);
            } else {
                return new JsonResponse($result, 500);
            }
        } else {
            $session = $request->session();
            $session->flash(self::PROMPT, $result);

            if ($route === 'markers') {
                return redirect()->route('markers', self::normalizeMarkersParameters($search, $offset));
            } elseif ($route === 'trashcan') {
                return redirect()->route('trashcan', self::normalizeTrashcanParameters($search, $offset));
            } elseif ($route === 'home') {
                return redirect()->route('home');
            } else {
                return redirect()->route('markers');
            }
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
            if (($target = Marker::find($id)) !== null) {
                $result['type'] = self::UNKNOWN_ERROR;
                $result['target'] = $target->attributesToArray();

                if ($type === 'trash') {
                    $target->delete();
                    $result['type'] = self::DELETION_SUCCESS;
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
            return redirect()->route('markers', self::normalizeMarkersParameters($search, $offset));
        }
    }
}
