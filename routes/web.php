<?php

use Illuminate\Support\Facades\Route;
use Prometheus\RenderTextFormat;
use Prometheus\CollectorRegistry;

Route::get('/metrics', function () {
    $registry = app(CollectorRegistry::class);
    $renderer = new RenderTextFormat();
    $result = $renderer->render($registry->getMetricFamilySamples());

    return response($result, 200)->header('Content-Type', RenderTextFormat::MIME_TYPE);
});
