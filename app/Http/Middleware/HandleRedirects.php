<?php

namespace App\Http\Middleware;

use App\Models\Redirect;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Redirect 301/302 untuk URL lama yang dipetakan di tabel redirects.
 *
 * Berjalan setelah routing: hanya mencegat respons 404 pada request GET,
 * sehingga tidak mengganggu rute yang masih hidup. Pemetaan dikelola
 * langsung di database (lihat docs/database/data-dictionary.md).
 */
class HandleRedirects
{
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        if (! $request->isMethod('get') || $response->getStatusCode() !== 404) {
            return $response;
        }

        $path = '/'.ltrim($request->path(), '/');

        $redirect = Redirect::where('from_path', $path)->first();

        if ($redirect === null) {
            return $response;
        }

        return redirect($redirect->to_path, $redirect->status_code === 302 ? 302 : 301);
    }
}
