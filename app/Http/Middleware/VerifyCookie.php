<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class VerifyCookie
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next)
    {
        $tokenName = config('auth.token_name');
        if ($request->hasCookie($tokenName)) {
            $token = $request->cookie($tokenName);
            $request->headers->set('Authorization', 'Bearer ' . $token);
        }
        return $next($request);
    }
}
