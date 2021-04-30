<?php

namespace App\Http\Controllers;

use Exception;
use PDOException;
use App\Models\Marker;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class ActionController extends Controller
{
    const ACTIVE_SECTION = 'active';
    const REMOVED_SECTION = 'removed';
    const LIMIT = 25;

    /**
     * Handle incoming actions.
     * @param Request $request
     * @return JsonResponse
     */
    public function __invoke(Request $request): JsonResponse
    {
        $user = Auth::user();
        $actions = $request->input('actions', []);
        $payload = [];

        if ($user === null) {
            $payload['accepted'] = false;
            $payload['error'] = 'authentication error';
        } else if ($this->validateActions($actions) === false) {
            $payload['accepted'] = false;
            $payload['error'] = 'malformed request';
        } else {
            $status = [];
            $payload['accepted'] = true;
            $payload['error'] = null;
            $payload['results'] = [];

            foreach ($actions as $action) {
                $id = $action['_id'];
                $type = $action['_type'];
                $depends = $action['_depends'] ?? [];
                $proceed = true;

                foreach ($depends as $depend) {
                    if ($status[$depend] === false) {
                        $proceed = false;
                        break;
                    }
                }

                if ($proceed) {
                    $result = $this->executeAction($id, $type, $action);
                    $status[$id] = $result['success'];
                    $payload['results'][] = $result;
                } else {
                    $result = [ '_id' => $id, '_type' => $type, 'success' => false, 'code' => 400, 'error' => 'skipped' ];
                    $status[$id] = $result['success'];
                    $payload['results'][] = $result;
                }
            }
        }

        return new JsonResponse($payload, 200);
    }

    /**
     * Execute the given action and return the result.
     * @param string $id
     * @param string $type
     * @param array $action
     * @return array
     */
    private function executeAction(string $id, string $type, array $action): array
    {
        switch ($type) {
            case 'check': return $this->executeCheckAction($id, $type, $action);
            case 'list': return $this->executeListAction($id, $type, $action);
            case 'create': return $this->executeCreateAction($id, $type, $action);
            case 'remove-by-id': return $this->executeRemoveByIdAction($id, $type, $action);
            case 'remove-by-url': return $this->executeRemoveByUrlAction($id, $type, $action);
            case 'restore-by-id': return $this->executeRestoreByIdAction($id, $type, $action);
            case 'restore-by-url': return $this->executeRestoreByUrlAction($id, $type, $action);
        }

        return [
            '_id' => $id,
            '_type' => $type,
            'success' => false,
            'status' => 404,
            'error' => "unknown type $type",
        ];
    }

    /**
     * Execute the given check action and return the result.
     * @param string $id
     * @param string $type
     * @param array $action
     * @return array
     */
    private function executeCheckAction(string $id, string $type, array $action): array
    {
        $result = [];
        $result['_id'] = $id;
        $result['_type'] = $type;
        $result['success'] = false;
        $result['status'] = 500;
        $result['error'] = 'unknown error';

        if ($this->validateCheckAction($action) === false) {
            $result['success'] = false;
            $result['status'] = 400;
            $result['error'] = 'malformed action';
        } else {
            try {
                $user = Auth::user();
                $markers = $user->markers()->whereIn('url', $action['urls'])->get();

                $result['success'] = true;
                $result['status'] = 200;
                $result['error'] = null;
                $result['urls'] = array_fill_keys($action['urls'], false);

                foreach ($markers as $marker) {
                    $url = $marker['url'];
                    $result['urls'][$url] = true;
                }

            } catch (PDOException $ex) {
                $result['success'] = false;
                $result['statuss'] = 500;
                $result['error'] = 'internal error';
            } catch (Exception $ex) {
                $result['success'] = false;
                $result['statuss'] = 500;
                $result['error'] = 'internal error';
            }
        }

        return $result;
    }

    /**
     * Execute the given list action and return the result.
     * @param string $id
     * @param string $type
     * @param array $action
     * @return array
     */
    private function executeListAction(string $id, string $type, array $action): array
    {
        $result = [];
        $result['_id'] = $id;
        $result['_type'] = $type;
        $result['success'] = false;
        $result['status'] = 500;
        $result['error'] = 'unknown error';

        if ($this->validateListAction($action) === false) {
            $result['success'] = false;
            $result['status'] = 400;
            $result['error'] = 'malformed action';
        } else {
            $section = $action['section'];
            $filter = ($action['filter'] !== '' ? $action['filter'] : null);
            $offset = $action['offset'];
            $limit = $action['limit'];

            $user = Auth::user();
            $query = $user->markers()->orderBy('created_at', 'desc')->skip($offset)->take($limit+1);

            if ($section === 'removed') {
                $query = $query->onlyTrashed();
            }

            if ($filter !== null) {
                foreach (preg_split('/\s+/u', $filter) as $fragment) {
                    $query = $query->where(function($query) use ($fragment) {
                        $fragment = str_replace("_", "\\_", $fragment);
                        $fragment = str_replace("%", "\\%", $fragment);
                        $fragment = "%$fragment%";
                        $query->orWhere('url', 'like', $fragment);
                        $query->orWhere('description', 'like', $fragment);
                    });
                }
            }

            try {
                $markers = $query->get();

                $result['success'] = true;
                $result['status'] = 200;
                $result['error'] = null;
                $result['section'] = $section;
                $result['filter'] = $filter;
                $result['offset'] = $offset;
                $result['limit'] = $limit;
                $result['more'] = ($markers->count() > $limit);
                $result['markers'] = $markers->slice(0, $limit)->toArray();

            } catch (PDOException $ex) {
                $result['success'] = false;
                $result['statuss'] = 500;
                $result['error'] = 'internal error';
                $result['debug'] = $ex->getMessage();
            } catch (Exception $ex) {
                $result['success'] = false;
                $result['statuss'] = 500;
                $result['error'] = 'internal error';
                $result['debug'] = $ex->getMessage();
            }
        }

        return $result;
    }

    /**
     * Execute the given create action and return the result.
     * @param string $id
     * @param string $type
     * @param array $action
     * @return array
     */
    private function executeCreateAction(string $id, string $type, array $action): array
    {
        $result = [];
        $result['_id'] = $id;
        $result['_type'] = $type;
        $result['success'] = false;
        $result['status'] = 500;
        $result['error'] = 'unknown error';

        if ($this->validateCreateAction($action) === false) {
            $result['success'] = false;
            $result['status'] = 400;
            $result['error'] = 'malformed action';
        } else {
            $user = Auth::user();
            $url = $action['url'];
            $description = $action['description'];
            $handler = $action['handler'];

            try {
                DB::transaction(function() use ($user, $url, $description, $handler, &$result) {
                    $current = $user->markers()->withTrashed()->where('url', $url)->first();
                    $data = [ 'url' => $url, 'description' => $description, 'handler' => $handler ];

                    if ($current === null) {
                        $incoming = $user->markers()->create($data);

                        $result['success'] = true;
                        $result['status'] = 200;
                        $result['error'] = null;
                        $result['target'] = $incoming->toArray();

                    } else if ($current->trashed()) {
                        $current->forceDelete();
                        $incoming = $user->markers()->create($data);

                        $result['success'] = true;
                        $result['status'] = 200;
                        $result['error'] = null;
                        $result['target'] = $incoming->toArray();

                    } else {
                        $result['success'] = false;
                        $result['status'] = 409;
                        $result['error'] = sprintf('marker for url %s already exists', $url);
                    }
                });
            } catch (PDOException $ex) {
                $result['success'] = false;
                $result['statuss'] = 500;
                $result['error'] = 'internal error';
                $result['debug'] = $ex->getMessage();
            } catch (Exception $ex) {
                $result['success'] = false;
                $result['statuss'] = 500;
                $result['error'] = 'internal error';
                $result['debug'] = $ex->getMessage();
            }
        }

        return $result;
    }

    /**
     * Execute the given remove by ID action and return the result.
     * @param string $id
     * @param string $type
     * @param array $action
     * @return array
     */
    private function executeRemoveByIdAction(string $id, string $type, array $action): array
    {
        $result = [];
        $result['_id'] = $id;
        $result['_type'] = $type;
        $result['success'] = false;
        $result['status'] = 500;
        $result['error'] = 'unknown error';

        if ($this->validateByIdAction($action) === false) {
            $result['success'] = false;
            $result['status'] = 400;
            $result['error'] = 'malformed action';
        } else {
            $id = $action['id'];

            try {
                $target = Auth::user()->markers()->findOrFail($id);
                $target->delete();

                $result['success'] = true;
                $result['status'] = 200;
                $result['error'] = null;
                $result['target'] = $target->toArray();

            } catch (ModelNotFoundException $ex) {
                $result['success'] = false;
                $result['statuss'] = 404;
                $result['error'] = sprintf('marker with id %d not found', $id);
            } catch (PDOException $ex) {
                $result['success'] = false;
                $result['statuss'] = 500;
                $result['error'] = 'internal error';
            } catch (Exception $ex) {
                $result['success'] = false;
                $result['statuss'] = 500;
                $result['error'] = 'internal error';
            }
        }

        return $result;
    }

    /**
     * Execute the given remove by URL action and return the result.
     * @param string $id
     * @param string $type
     * @param array $action
     * @return array
     */
    private function executeRemoveByUrlAction(string $id, string $type, array $action): array
    {
        $result = [];
        $result['_id'] = $id;
        $result['_type'] = $type;
        $result['success'] = false;
        $result['status'] = 500;
        $result['error'] = 'unknown error';

        if ($this->validateByUrlAction($action) === false) {
            $result['success'] = false;
            $result['status'] = 400;
            $result['error'] = 'malformed action';
        } else {
            $url = $action['url'];

            try {
                $target = Auth::user()->markers()->where('url', $url)->firstOrFail();
                $target->delete();

                $result['success'] = true;
                $result['status'] = 200;
                $result['error'] = null;
                $result['target'] = $target->toArray();

            } catch (ModelNotFoundException $ex) {
                $result['success'] = false;
                $result['statuss'] = 404;
                $result['error'] = sprintf('marker with url %s not found', $url);
            } catch (PDOException $ex) {
                $result['success'] = false;
                $result['statuss'] = 500;
                $result['error'] = 'internal error';
            } catch (Exception $ex) {
                $result['success'] = false;
                $result['statuss'] = 500;
                $result['error'] = 'internal error';
            }
        }

        return $result;
    }

    /**
     * Execute the given restore by ID action and return the result.
     * @param string $id
     * @param string $type
     * @param array $action
     * @return array
     */
    private function executeRestoreByIdAction(string $id, string $type, array $action): array
    {
        $result = [];
        $result['_id'] = $id;
        $result['_type'] = $type;
        $result['success'] = false;
        $result['status'] = 500;
        $result['error'] = 'unknown error';

        if ($this->validateByIdAction($action) === false) {
            $result['status'] = 400;
            $result['error'] = 'malformed action';
        } else {
            $id = $action['id'];

            try {
                $target = Auth::user()->markers()->onlyTrashed()->findOrFail($id);
                $target->restore();

                $result['success'] = true;
                $result['status'] = 200;
                $result['error'] = null;
                $result['target'] = $target->toArray();

            } catch (ModelNotFoundException $ex) {
                $result['success'] = false;
                $result['statuss'] = 404;
                $result['error'] = sprintf('marker with id %d not found', $id);
            } catch (PDOException $ex) {
                $result['success'] = false;
                $result['statuss'] = 500;
                $result['error'] = 'internal error';
            } catch (Exception $ex) {
                $result['success'] = false;
                $result['statuss'] = 500;
                $result['error'] = 'internal error';
            }
        }

        return $result;
    }

    /**
     * Execute the given restore by URL action and return the result.
     * @param string $id
     * @param string $type
     * @param array $action
     * @return array
     */
    private function executeRestoreByUrlAction(string $id, string $type, array $action): array
    {
        $result = [];
        $result['_id'] = $id;
        $result['_type'] = $type;
        $result['success'] = false;
        $result['status'] = 500;
        $result['error'] = 'unknown error';

        if ($this->validateByUrlAction($action) === false) {
            $result['status'] = 400;
            $result['error'] = 'malformed action';
        } else {
            $url = $action['url'];

            try {
                $target = Auth::user()->markers()->onlyTrashed()->where('url', $url)->firstOrFail();
                $target->restore();

                $result['success'] = true;
                $result['status'] = 200;
                $result['error'] = null;
                $result['target'] = $target->toArray();

            } catch (ModelNotFoundException $ex) {
                $result['success'] = false;
                $result['statuss'] = 404;
                $result['error'] = sprintf('marker with url %s not found', $url);
            } catch (PDOException $ex) {
                $result['success'] = false;
                $result['statuss'] = 500;
                $result['error'] = 'internal error';
            } catch (Exception $ex) {
                $result['success'] = false;
                $result['statuss'] = 500;
                $result['error'] = 'internal error';
            }
        }

        return $result;
    }

    /**
     * Validate if the input is a valid list of actions.
     * @param mixed $actions
     * @return bool
     */
    private function validateActions($actions): bool
    {
        if (is_array($actions) === false) {
            return false;
        } else {
            $idlookup = [];

            foreach ($actions as $action) {
                if (is_array($action) === false) {
                    return false;
                } else if ($this->hasStringEntry($action, '_id') === false) {
                    return false;
                } else if ($this->hasStringEntry($action, '_type') === false) {
                    return false;
                } else if ($this->hasEnumArrayEntry($action, '_depends', $idlookup, true) === false) {
                    return false;
                } else {
                    $idlookup[] = $action['_id'];
                }
            }

            return true;
        }
    }

    /**
     * Validate if the input is a valid check action.
     * @param mixed $action
     * @return bool
     */
    private function validateCheckAction($action): bool
    {
        if (is_array($action) === false) {
            return false;
        } else if ($this->hasArrayEntry($action, 'urls', 'is_string', false) === false) {
            return false;
        } else {
            return true;
        }
    }

    /**
     * Validate if the input is a valid list action.
     * @param mixed $action
     * @return bool
     */
    private function validateListAction($action): bool
    {
        if (is_array($action) === false) {
            return false;
        } else if ($this->hasEnumEntry($action, 'section', [ self::ACTIVE_SECTION, self::REMOVED_SECTION ]) === false) {
            return false;
        } else if ($this->hasStringEntry($action, 'filter', true) === false) {
            return false;
        } else if ($this->hasIntEntry($action, 'offset') === false) {
            return false;
        } else if ($this->hasIntEntry($action, 'limit') === false) {
            return false;
        } else if ($action['offset'] < 0) {
            return false;
        } else if ($action['limit'] <= 0) {
            return false;
        } else {
            return true;
        }
    }

    /**
     * Validate if the input is a valid create action.
     * @param mixed $action
     * @return bool
     */
    private function validateCreateAction($action): bool
    {
        if (is_array($action) === false) {
            return false;
        } else if ($this->hasStringEntry($action, 'url') === false) {
            return false;
        } else if ($this->hasStringEntry($action, 'description', true) === false) {
            return false;
        } else if ($this->hasStringEntry($action, 'handler') === false) {
            return false;
        } else {
            return true;
        }
    }

    /**
     * Validate if the input is a valid XXX by ID action.
     * @param mixed $action
     * @return bool
     */
    private function validateByIdAction($action): bool
    {
        if (is_array($action) === false) {
            return false;
        } else if ($this->hasIntEntry($action, 'id') === false) {
            return false;
        } else {
            return true;
        }
    }

    /**
     * Validate if the input is a valid XXX by URL action.
     * @param mixed $action
     * @return bool
     */
    private function validateByUrlAction($action): bool
    {
        if (is_array($action) === false) {
            return false;
        } else if ($this->hasStringEntry($action, 'url') === false) {
            return false;
        } else {
            return true;
        }
    }

    /**
     * @param array $input
     * @param string $key
     * @param bool $nullable
     * @return bool
     */
    private function hasIntEntry(array $input, string $key, bool $nullable = false): bool
    {
        if (array_key_exists($key, $input) === false) {
            return $nullable;
        } else if (is_null($input[$key]) === true) {
            return $nullable;
        } else if (is_int($input[$key]) === false) {
            return false;
        } else {
            return true;
        }
    }

    /**
     * @param array $input
     * @param string $key
     * @param bool $nullable
     * @return bool
     */
    private function hasStringEntry(array $input, string $key, bool $nullable = false): bool
    {
        if (array_key_exists($key, $input) === false) {
            return $nullable;
        } else if (is_null($input[$key]) === true) {
            return $nullable;
        } else if (is_string($input[$key]) === false) {
            return false;
        } else {
            return true;
        }
    }

    /**
     * @param array $input
     * @param string $key
     * @param array $candidates
     * @param bool $nullable
     * @return bool
     */
    private function hasEnumEntry(array $input, string $key, array $candidates, bool $nullable = false): bool
    {
        if (array_key_exists($key, $input) === false) {
            return $nullable;
        } else if (is_null($input[$key]) === true) {
            return $nullable;
        } else if (in_array($input[$key], $candidates, true) === false) {
            return false;
        } else {
            return true;
        }
    }

    /**
     * @param array $input
     * @param string $key
     * @param callable $validator
     * @param bool $nullable
     * @return bool
     */
    private function hasArrayEntry(array $input, string $key, callable $validator, bool $nullable = false): bool
    {
        if (array_key_exists($key, $input) === false) {
            return $nullable;
        } else if (is_null($input[$key]) === true) {
            return $nullable;
        } else if (is_array($input[$key]) === false) {
            return false;
        } else {
            foreach ($input[$key] as $item) {
                if ($validator($item) === false) {
                    return false;
                }
            }

            return true;
        }
    }

    /**
     * @param array $input
     * @param string $key
     * @param array $candidates
     * @param bool $nullable
     * @return bool
     */
    private function hasEnumArrayEntry(array $input, string $key, array $candidates, bool $nullable = false): bool
    {
        if (array_key_exists($key, $input) === false) {
            return $nullable;
        } else if (is_null($input[$key]) === true) {
            return $nullable;
        } else if (is_array($input[$key]) === false) {
            return false;
        } else {
            foreach ($input[$key] as $item) {
                if (in_array($item, $candidates, true) === false) {
                    return false;
                }
            }

            return true;
        }
    }
}
