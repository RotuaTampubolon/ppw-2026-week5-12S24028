<?php

use Illuminate\Auth\AuthenticationException;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        $middleware->validateCsrfTokens(except: ['soap/*']);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        $exceptions->shouldRenderJsonWhen(
            fn (Request $request, Throwable $e) => $request->is('api/*') || $request->expectsJson()
        );

        $problem = fn (Request $r, string $slug, string $title, int $status, string $detail, array $extra = []) =>
            response()->json(array_merge([
                'type'     => url("/docs/errors/{$slug}"),
                'title'    => $title,
                'status'   => $status,
                'detail'   => $detail,
                'instance' => $r->getRequestUri(),
            ], $extra), $status)->header('Content-Type', 'application/problem+json');

        $exceptions->render(function (ValidationException $e, Request $r) use ($problem) {
            if ($r->is('api/*')) {
                return $problem($r, 'validation-failed', 'Unprocessable Content', 422,
                    'Satu atau lebih atribut input tidak memenuhi aturan validasi.',
                    ['errors' => $e->errors()]);
            }
        });

        $exceptions->render(function (AuthenticationException $e, Request $r) use ($problem) {
            if ($r->is('api/*')) {
                return $problem($r, 'unauthenticated', 'Unauthorized', 401,
                    'Akses ditolak. Sertakan Bearer Token yang valid pada header Authorization.');
            }
        });

        $exceptions->render(function (NotFoundHttpException $e, Request $r) use ($problem) {
            if ($r->is('api/*')) {
                return $problem($r, 'endpoint-not-found', 'Not Found', 404,
                    'Alamat URI endpoint atau resource yang diminta tidak ditemukan.');
            }
        });
    })->create();