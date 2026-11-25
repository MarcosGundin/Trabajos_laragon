<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ControladorMiddleware
{
    public function handle(Request $request, Closure $next)
    {
        if (!Auth::check() || !Auth::user()->rol->nombre==='controlador') {
            abort(403, 'Acceso denegado. Solo los usuarios con rol controlador pueden acceder a esta página.');
        }

        return $next($request);
    }
}
