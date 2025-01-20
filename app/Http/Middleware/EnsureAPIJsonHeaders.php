<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureAPIJsonHeaders
{
    /**
     * Handle an incoming request.
     *
     * @param \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response) $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Ensure that the path matches /api/* routes
        if ($this->isApiRoute($request)) {
            // If the Accept header is not set or is incorrect, set it to application/json
            if (!$request->headers->has('Accept') || !$this->isAcceptJson($request)) {
                $request->headers->set('Accept', 'application/json');
            }
        }

        return $next($request);
    }

    /**
     * Check if the request path starts with 'api/'
     *
     * @param Request $request
     * @return bool
     */
    private function isApiRoute(Request $request): bool
    {
        return str_starts_with($request->path(), 'api/');
    }

    /**
     * Check if the request's Accept header is application/json
     *
     * @param Request $request
     * @return bool
     */
    private function isAcceptJson(Request $request): bool
    {
        return $request->header('Accept') === 'application/json';
    }
}
