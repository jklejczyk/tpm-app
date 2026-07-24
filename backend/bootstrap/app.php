<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use App\Exceptions\WorkOrderNotFound;
use Illuminate\Http\Request;
use Tpm\WorkOrder\Exception\IllegalStateTransition;
use Tpm\WorkOrder\Exception\MissingHoldReason;
use Tpm\WorkOrder\Exception\MissingResolution;
use Tpm\WorkOrder\Exception\UnauthorizedTransition;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        // API is JSON-only: unauthenticated requests get 401, never a redirect to a
        // (non-existent) login page — even from a browser that sends Accept: text/html.
        $middleware->redirectGuestsTo(
            fn (Request $request) => $request->is('api/*') ? null : '/login',
        );
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->shouldRenderJsonWhen(
            fn (Request $request) => $request->is('api/*'),
        );

        $exceptions->render(fn (WorkOrderNotFound $e) => response()->json(['message' => $e->getMessage()], 404));
        $exceptions->render(fn (UnauthorizedTransition $e) => response()->json(['message' => $e->getMessage()], 403));
        $exceptions->render(fn (IllegalStateTransition $e) => response()->json(['message' => $e->getMessage()], 422));
        $exceptions->render(fn (MissingHoldReason $e) => response()->json(['message' => $e->getMessage()], 422));
        $exceptions->render(fn (MissingResolution $e) => response()->json(['message' => $e->getMessage()], 422));
    })->create();
