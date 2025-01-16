<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ContentSecurityPolicyMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle($request, Closure $next)
    {
        $response = $next($request);

        // Define a strict and organized Content Security Policy
        $csp = implode('; ', [
            "default-src 'self'", // Default to only allow resources from the same origin
            "script-src 'self' https://cdnjs.cloudflare.com https://maxcdn.bootstrapcdn.com https://cdn.datatables.net/1.10.9/js/jquery.dataTables.min.js", // Allow scripts from trusted sources
            "style-src 'self' 'unsafe-inline' https://fonts.googleapis.com https://cdn.datatables.net", // Allow styles from trusted sources and inline styles
            "font-src 'self' https://fonts.gstatic.com", // Allow fonts from trusted sources
            "img-src 'self' data: https://www.google-analytics.com", // Allow images from the same origin, data URIs, and Google Analytics
            "connect-src 'self' https://api.example.com", // Allow XHR and WebSocket connections to trusted APIs
            "frame-src 'self'", // Restrict iframe embedding to the same origin
            "object-src 'none'", // Block all object, embed, and applet tags
            "base-uri 'self'", // Prevent the use of base URIs from other domains
            "form-action 'self'", // Restrict form submissions to the same origin
            "frame-ancestors 'self'", // Prevent other sites from embedding your site in an iframe
        ]);

        // Add the Content-Security-Policy header to the response
        $response->headers->set('Content-Security-Policy', $csp);

        // Optionally add other security headers
        $response->headers->set('X-Content-Type-Options', 'nosniff'); // Prevent MIME type sniffing
        $response->headers->set('X-Frame-Options', 'SAMEORIGIN'); // Prevent clickjacking
        $response->headers->set('X-XSS-Protection', '1; mode=block'); // Enable XSS protection in browsers
        $response->headers->set('Referrer-Policy', 'strict-origin-when-cross-origin'); // Control the referrer information sent with requests
        $response->headers->set('Permissions-Policy', 'geolocation=(), microphone=(), camera=()'); // Limit browser features

        return $response;
    }
}
