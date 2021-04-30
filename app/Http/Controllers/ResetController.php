<?php

namespace App\Http\Controllers;

use Exception;
use PDOException;
use App\Models\Reset;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class ResetController extends WebController
{
    const PAGESIZE = 20;

    const PROMPT = __CLASS__ . '@prompt';
    const DELETION_SUCCESS = 1;

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
        $resets = Reset::orderBy('created_at', 'desc')->skip($offset)->take(self::PAGESIZE+1)->get();

        $data = [];
        $data['resets'] = $resets->slice(0, self::PAGESIZE);
        $data['offset'] = $offset;
        $data['urls'] = [];
        $data['urls']['self'] = url()->route('resets', self::normalizeResetsParameters($offset));
        $data['urls']['head'] = null;
        $data['urls']['prev'] = null;
        $data['urls']['next'] = null;

        if ($offset > self::PAGESIZE) {
            $data['urls']['head'] = url()->route('resets');
            $data['urls']['prev'] = url()->route('resets', self::normalizeResetsParameters($offset - self::PAGESIZE));
        } elseif ($offset > 0) {
            $data['urls']['head'] = url()->route('resets');
            $data['urls']['prev'] = url()->route('resets');
        }

        if (count($resets) > self::PAGESIZE) {
            $data['urls']['next'] = url()->route('resets', self::normalizeResetsParameters($offset + self::PAGESIZE));
        }

        if ($this->ajax($request)) {
            return new JsonResponse($data, 200);
        } else {
            $session = $request->session();
            $data['prompt'] = $session->get(self::PROMPT, [ 'type' => self::EMPTY_MESSAGE ]);
            return view('resets', $data);
        }
    }

    /**
     * Handle incoming delete requests.
     * @param Request $request
     * @param int $id
     * @return JsonResponse|RedirectResponse
     */
    public function delete(Request $request, $id)
    {
        $offset = intval($request->input('offset', 0), 10);

        $result = [];
        $result['type'] = self::MISSING_ERROR;
        $result['target'] = null;

        try {
            if (($target = Reset::find($id)) !== null) {
                $target->delete();
                $result['type'] = self::DELETION_SUCCESS;
                $result['target'] = $target->attributesToArray();
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
            return redirect()->route('resets', self::normalizeResetsParameters($offset));
        }
    }
}
