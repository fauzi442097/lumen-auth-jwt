<?php

namespace App\Http\Middleware;

use App\Helper\ApiResponse;
use Closure;
use Illuminate\Http\Request;
use Tymon\JWTAuth\Exceptions\TokenExpiredException;
use Tymon\JWTAuth\Exceptions\TokenInvalidException;
use Tymon\JWTAuth\Exceptions\JWTException;
use Tymon\JWTAuth\Facades\JWTAuth;
use Illuminate\Http\Response;

class JwtMiddleware
{

    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse)  $next
     * @return \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse
     */
    public function handle(Request $request, Closure $next)
    {
        $token = $request->bearerToken();
        if (is_null($token)) {
            return ApiResponse::badRequest('Authorization Token not found');
        }

        [$valid, $jwtPayload] = $this->isTokenValid($token);
        if (!$valid) {
            return ApiResponse::badRequest('Token is Invalid');
        }

        if (time() > $jwtPayload->exp) {
            return ApiResponse::badRequest('Token is Expired');
        }

        if (is_null($request->session()->get('token'))) {
            return ApiResponse::unauthorized('Unauthorized. Please login again');
        }

        return $next($request);
    }

    public function isTokenValid($token)
    {
        if (substr_count($token, '.') != 2) {
            return [false, null];
        }

        $tokenParts = explode(".", $token);
        $tokenHeader = base64_decode($tokenParts[0]);
        $tokenPayload = base64_decode($tokenParts[1]);
        $jwtHeader = json_decode($tokenHeader);
        $jwtPayload = json_decode($tokenPayload);

        if (!is_object($jwtPayload)) {
            return [false, null];
        }

        if (!isset(
            $jwtPayload->iss,
            $jwtPayload->iat,
            $jwtPayload->exp,
            $jwtPayload->nbf,
            $jwtPayload->jti,
            $jwtPayload->name
        )) {
            return [false, null];
        }

        if ($jwtPayload->name != 'admin') {
            return [false, null];
        }

        return [true, $jwtPayload];
    }
}
