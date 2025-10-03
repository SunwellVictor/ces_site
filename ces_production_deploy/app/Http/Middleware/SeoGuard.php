<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Services\Meta;

class SeoGuard
{
    /**
     * Private routes that should have noindex
     */
    protected array $privateRoutes = [
        'admin.*',
        'account.*',
        'dashboard',
        'profile.*',
        'downloads.*',
        'cart.*',
        'checkout.*',
        'login',
        'register',
        'password.*',
        'verification.*',
        'webhooks.*',
    ];

    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Check if this is a private route that should be noindexed
        // We need to do this BEFORE generating the response so the meta tags are available during view rendering
        if ($this->isPrivateRoute($request)) {
            $this->addNoindexMeta();
        }

        $response = $next($request);

        return $response;
    }

    /**
     * Check if the response is HTML
     */
    protected function isHtmlResponse(Response $response): bool
    {
        $contentType = $response->headers->get('Content-Type', '');
        return str_contains($contentType, 'text/html') || empty($contentType);
    }

    /**
     * Check if the current route is private
     */
    protected function isPrivateRoute(Request $request): bool
    {
        $routeName = $request->route()?->getName();
        
        if (!$routeName) {
            return false;
        }

        foreach ($this->privateRoutes as $pattern) {
            if (fnmatch($pattern, $routeName)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Add noindex meta tag for private pages
     */
    protected function addNoindexMeta(): void
    {
        if (app()->bound(Meta::class)) {
            $meta = app(Meta::class);
            $meta->setRobots('noindex, nofollow');
        }
    }
}
