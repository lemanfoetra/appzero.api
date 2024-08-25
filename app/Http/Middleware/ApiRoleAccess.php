<?php

namespace App\Http\Middleware;

use Closure;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class ApiRoleAccess
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse)  $next
     * @return \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse
     */
    public function handle(Request $request, Closure $next, ...$keyRoute)
    {
        try {
            $method     = $request->method();
            $keyRoute   = $keyRoute[0];

            if (Auth::user() &&  $this->haveAccess($keyRoute, $method)) {
                return $next($request);
            }
            return response()->json([
                'message'   => 'ROLE_NOT_HAVE_ACCESS',
            ], 200);
        } catch (\Throwable $th) {
            return response()->json([
                'message'   => $th->getMessage(),
            ], 401);
        }
    }


    private function haveAccess($keyRoute, $method)
    {
        $apiModule = DB::table('api_modules')
            ->select(['id', 'id_menus'])
            ->where('method', strtoupper(trim($method)))
            ->where('key', $keyRoute)
            ->first();

        if (!isset($apiModule->id)) {
            throw new Exception("URL_NOT_LISTING");
        }

        if ($apiModule->id_menus == 0) {
            return true;
        }

        $access = DB::table('role_api_modules')
            ->select(['id'])
            ->where('id_api_module', $apiModule->id)
            ->where('id_roles', Auth::user()->id_role)
            ->first();

        if (!isset($access->id)) {
            throw new Exception('ROLE_NOT_HAVE_ACCESS_TO_THIS_API');
        }

        if (!empty($access->id)) {
            return true;
        }

        throw new Exception('ROLE_API_PROBLEM');
    }
}
