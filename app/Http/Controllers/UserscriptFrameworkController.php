<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class UserscriptFrameworkController extends WebController
{
    /**
     * Return the framework used by userscripts.
     * @return View
     */
    public function __invoke(): View
    {
        $view = view('userscript_framework');
        $response = new Response($view, 200);
        $response->header('Content-Type', 'application/javascript');
        return $response;
    }
}
