<?php

namespace App\Exceptions;

use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Illuminate\Http\Request;
use Throwable;
use Symfony\Component\HttpKernel\Exception\HttpException;

class Handler extends ExceptionHandler
{
    /**
     * The list of the inputs that are never flashed to the session on validation exceptions.
     *
     * @var array<int, string>
     */
    protected $dontFlash = [
        'current_password',
        'password',
        'password_confirmation',
    ];

    /**
     * Register the exception handling callbacks for the application.
     */
    public function register(): void
    {
        $this->renderable(function (HttpException $exception, Request $request) {
            if ($exception->getStatusCode() === 419 && $request->isMethod('post') && $request->routeIs('logout')) {
                return redirect()->route('dashboard')->with('error', 'Sesi keamanan berubah atau kedaluwarsa. Muat ulang halaman, lalu coba keluar kembali.');
            }
        });

        $this->reportable(function (Throwable $e) {
            //
        });
    }
}
