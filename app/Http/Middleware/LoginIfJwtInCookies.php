<?php

namespace App\Http\Middleware;

use Closure;
use JWTAuth;
use \Tymon\JWTAuth\Token;
use App\User;
use Auth;

class LoginIfJwtInCookies
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
        if(!Auth::check() && $cookie = $request->cookie('jwt')) {
            $token = new Token($cookie);
            $jwt = JWTAuth::decode($token);
            $user = User::find($jwt->get('sub'));
            if($user) {
                Auth::login($user);
            }
        }

        return $next($request);
    }
}
