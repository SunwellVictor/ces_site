<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ForceHttps
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Only enforce HTTPS in production environment
        if (app()->environment('production')) {
            // Redirect HTTP to HTTPS
            if (!$request->secure() && !$request->header('X-Forwarded-Proto') === 'https') {
                return redirect()->secure($request->getRequestUri(), 301);
            }
        }

        $response = $next($request);

        // Add HSTS header in production for enhanced security
        if (app()->environment('production') && $request->secure()) {
            $response->headers->set(
                'Strict-Transport-Security',
                'max-age=31536000; includeSubDomains; preload'
            );
        }

        return $response;
    }
}
