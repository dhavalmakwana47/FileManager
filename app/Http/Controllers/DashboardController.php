<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;

class DashboardController extends Controller implements HasMiddleware
{

    public static function middleware(): array
    {
        return [
            new Middleware('permission_check:Dashboard,view', only: ['index', 'show']),
        ];
    }

    public function index()
    {
        return view('app.dashboard');
    }
}
