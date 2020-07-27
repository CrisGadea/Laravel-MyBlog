<?php

namespace App\Http\Middleware;

use App\Helpers\JwtAuth;
use Closure;

class ApiAuthMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        // Comprobar si el usuario esta identificado
        $token = $request->header('Authorization');
        $jwtAuth = new JwtAuth();
        $checktoken = $jwtAuth->checkToken($token);

        if ($checktoken) {
            return $next($request);
        }else{
            $data = [
                'code' => 400,
                'status' => 'error',
                'message' => 'User was not identified'
            ];
            return response()->json($data,$data['code']);
        }

    }
}
