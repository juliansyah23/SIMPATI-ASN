<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        // Apache (reverse proxy) di host mengakses container lewat 127.0.0.1,
        // tapi dari sudut pandang container koneksi itu di-NAT lewat gateway
        // Docker (172.18.0.1). 10.6.0.4 adalah gateway reverse proxy pusat
        // BRIN (DC Pusdatin) yang melakukan terminasi TLS di depan Apache
        // kita dan meneruskan X-Forwarded-Proto: https. Tanpa mempercayai
        // ketiganya, Laravel akan menghasilkan URL/redirect/form action
        // dengan skema http:// walau diakses via https://, sehingga browser
        // memblokir submit sebagai mixed content / insecure form.
        $middleware->trustProxies(at: [
            '127.0.0.1',
            '172.18.0.1',
            '10.6.0.4',
        ]);

        $middleware->alias([
            'admin' => \App\Http\Middleware\EnsureUserIsAdmin::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();
