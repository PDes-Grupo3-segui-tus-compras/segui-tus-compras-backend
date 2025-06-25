<?php

namespace App\Http\Middleware;

use Closure;
use Prometheus\CollectorRegistry;
use Prometheus\Exception\MetricsRegistrationException;

class PrometheusMetricsMiddleware
{
    protected CollectorRegistry $registry;

    public function __construct(CollectorRegistry $registry)
    {
        $this->registry = $registry;
    }

    /**
     * @throws MetricsRegistrationException
     */
    public function handle($request, Closure $next)
    {
        $response = $next($request);

        $counter = $this->registry->getOrRegisterCounter(
            'http',
            'requests_total',
            'Count all HTTP requests',
            ['method', 'route', 'status_code']
        );

        $counter->inc([
            $request->getMethod(),
            $request->route()?->getName() ?? $request->path(),
            $response->getStatusCode()
        ]);

        return $response;
    }
}
