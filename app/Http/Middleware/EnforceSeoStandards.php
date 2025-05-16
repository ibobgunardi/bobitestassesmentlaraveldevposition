<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class EnforceSeoStandards
{
    public function handle(Request $request, Closure $next)
    {
        $response = $next($request);

        // Force HTTPS in production for SEO benefits
        if (!$request->secure() && app()->environment('production')) {
            return redirect()->secure($request->getRequestUri());
        }

        // Handle trailing slashes consistently (choose one approach)
        $path = $request->path();
        if ($path !== '/' && substr($path, -1) === '/') {
            return redirect(rtrim($path, '/'), 301);
        }

        $host = $request->getHost();
        // Remove 'www' for SEO benefits
        if (str_starts_with($host, 'www.')) {
            return redirect()->to('https://' . str_replace('www.', '', $host) . $request->getRequestUri());
        }
        //  Lowercase URLs (avoid case-sensitive duplicates):
        if (preg_match('/[A-Z]/', $request->getRequestUri())) {
            return redirect(strtolower($request->getRequestUri()), 301);
        }
        return $response;
    }
}
