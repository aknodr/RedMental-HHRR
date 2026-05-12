<?php

namespace App\Http\Middleware;

use App\Models\AuditLog;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * HIPAA-style request audit. Records every authenticated request together
 * with the user, client tenant, route, and a sanitized payload.
 *
 * Logging happens in `terminate()` so it never blocks the response and a
 * write failure cannot break the page.
 */
class AuditAccess
{
    /** Routes we never log (high-volume / no PHI). */
    private const SKIP_ROUTE_PREFIXES = [
        'livewire',          // future
        'horizon',           // future
        '_debugbar',
    ];

    /** Form fields scrubbed before they reach the audit table. */
    private const REDACTED_FIELDS = ['password', 'password_confirmation', 'admin_password', 'admin_password_confirmation', 'ssn', 'remember', '_token'];

    public function handle(Request $request, Closure $next): Response
    {
        return $next($request);
    }

    public function terminate(Request $request, Response $response): void
    {
        try {
            if (! $request->user()) return;

            $route = $request->route();
            $name  = $route?->getName() ?? '';
            foreach (self::SKIP_ROUTE_PREFIXES as $skip) {
                if (str_starts_with($name, $skip)) return;
            }

            // Skip asset/health pings
            $url = $request->path();
            if (in_array($url, ['up', 'favicon.ico'], true)) return;

            $action = match ($request->method()) {
                'GET'    => 'VIEW',
                'POST'   => 'CREATE',
                'PUT'    => 'UPDATE',
                'PATCH'  => 'UPDATE',
                'DELETE' => 'DELETE',
                default  => strtoupper($request->method()),
            };

            $payload = $request->except(self::REDACTED_FIELDS);
            // Truncate huge fields
            array_walk_recursive($payload, function (&$v) {
                if (is_string($v) && strlen($v) > 500) $v = substr($v, 0, 497) . '...';
            });

            AuditLog::create([
                'user_id'    => $request->user()->id,
                'client_id'  => $request->user()->client_id,
                'action'     => $action,
                'resource'   => $name ?: $request->path(),
                'method'     => $request->method(),
                'url'        => substr($request->fullUrl(), 0, 500),
                'ip_address' => $request->ip(),
                'user_agent' => substr((string) $request->userAgent(), 0, 500),
                'payload'    => $action === 'VIEW' ? null : $payload,
            ]);
        } catch (\Throwable $e) {
            // Never break the response because audit failed
        }
    }
}
