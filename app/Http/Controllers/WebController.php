<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class WebController extends Controller
{
    /**
     * Type code for empty result message.
     */
    const EMPTY_MESSAGE = 0;

    /**
     * Type code for generic operation success message.
     */
    const ACTION_SUCCESS = 255;

    /**
     * Type code for generic resource missing error message. It is used to
     * indicate when target resource does not exist.
     */
    const MISSING_ERROR = -251;

    /**
     * Type code for generic resource conflict error message. It is used to
     * indicate conflicting updates to a resource.
     */
    const CONFLICT_ERROR = -252;

    /**
     * Type code for generic operation error message.
     */
    const ACTION_ERROR = -253;

    /**
     * Type code for backend error message.
     */
    const BACKEND_ERROR = -254;

    /**
     * Type code for unknown error message.
     */
    const UNKNOWN_ERROR = -255;

    /**
     * Check if the request comes from javascript or not.
     * @param Request $request
     * @return bool
     */
    final protected function ajax(Request $request)
    {
        if ($request->ajax()) {
            return true;
        } elseif ($request->pjax()) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * Normalize markers route parameters.
     * @param string $search
     * @param int $offset
     * @return array
     */
    public static function normalizeMarkersParameters(string $search, int $offset): array
    {
        $parameters = [];

        if ($search !== '') {
            $parameters['search'] = $search;
        }

        if ($offset > 0) {
            $parameters['offset'] = $offset;
        }

        return $parameters;
    }

    /**
     * Normalize trashcan route parameters.
     * @param string $search
     * @param int $offset
     * @return array
     */
    public static function normalizeTrashcanParameters(string $search, int $offset): array
    {
        $parameters = [];

        if ($search !== '') {
            $parameters['search'] = $search;
        }

        if ($offset > 0) {
            $parameters['offset'] = $offset;
        }

        return $parameters;
    }

    /**
     * Normalize users route parameters.
     * @param int $offset
     * @return array
     */
    public static function normalizeUsersParameters(int $offset): array
    {
        if ($offset > 0) {
            return [ 'offset' => $offset ];
        } else {
            return [];
        }
    }

    /**
     * Normalize invites route parameters.
     * @param int $offset
     * @return array
     */
    public static function normalizeInvitesParameters(int $offset): array
    {
        if ($offset > 0) {
            return [ 'offset' => $offset ];
        } else {
            return [];
        }
    }

    /**
     * Normalize resets route parameters.
     * @param int $offset
     * @return array
     */
    public static function normalizeResetsParameters(int $offset): array
    {
        if ($offset > 0) {
            return [ 'offset' => $offset ];
        } else {
            return [];
        }
    }
}
